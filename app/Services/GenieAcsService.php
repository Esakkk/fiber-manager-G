<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenieAcsService
{
    protected $url;
    protected $timeout;

    public function __construct()
    {
        $this->url = config('services.genieacs.url', 'http://192.168.101.37:7557');
        $this->timeout = config('services.genieacs.timeout', 3);
    }

    /**
     * Fetch all devices from GenieACS and cache the result.
     *
     * @return array Array of devices keyed by Serial Number
     */
    public function getCachedDevices()
    {
        return Cache::remember('genieacs_devices', 15, function () {
            try {
                // Projection to retrieve only necessary fields for optimization
                $projectionFields = [
                    'DeviceID.SerialNumber',
                    '_lastInform',
                    '_ip',
                    // Standard TR-181
                    'Device.Optical.Interface.1.RxPower',
                    'Device.Optical.Interface.1.OpticalSignalLevel',
                    // Huawei
                    'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.RXPower',
                    'InternetGatewayDevice.WANDevice.1.X_HW_GPON_DiagnosticInfo.RxPower',
                    // ZTE
                    'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.RxPower',
                    'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.RxOptPower',
                    'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.OpticalInfo.RxPower',
                    'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_OpticalInfo.RxPower',
                    'Device.X_ZTE-COM_OpticalInfo.RxPower',
                ];

                $projection = implode(',', $projectionFields);
                $apiUrl = rtrim($this->url, '/') . '/devices/?projection=' . urlencode($projection);

                $response = Http::timeout($this->timeout)->get($apiUrl);

                if ($response->failed()) {
                    Log::warning('GenieACS request failed: ' . $response->status());
                    return [];
                }

                $devices = $response->json();
                if (!is_array($devices)) {
                    return [];
                }

                $mapped = [];
                foreach ($devices as $device) {
                    $serial = $this->getSerialNumber($device);
                    if ($serial) {
                        $mapped[strtoupper($serial)] = $device;
                    }
                }

                return $mapped;
            } catch (\Exception $e) {
                Log::error('Error fetching GenieACS devices: ' . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Map a record (like Customer or OdpPort) with GenieACS status, IP, last inform, and Rx power.
     *
     * @param string|null $onuNumber
     * @return array
     */
    public function getAcsData($onuNumber)
    {
        $default = [
            'acs_status' => 'offline',
            'acs_last_inform' => null,
            'acs_ip' => null,
            'acs_rx_power' => null,
        ];

        if (!$onuNumber) {
            return $default;
        }

        $devices = $this->getCachedDevices();
        $key = strtoupper(trim($onuNumber));

        if (!isset($devices[$key])) {
            return $default;
        }

        $device = $devices[$key];

        // Determine if online based on _lastInform threshold (10 minutes)
        $lastInformStr = $this->getNestedValue($device, '_lastInform');
        $isOnline = false;
        $formattedLastInform = null;

        if ($lastInformStr) {
            try {
                $lastInform = new \DateTime($lastInformStr);
                // Set timezone to app timezone or default
                $lastInform->setTimezone(new \DateTimeZone(config('app.timezone', 'UTC')));
                $formattedLastInform = $lastInform->format('Y-m-d H:i:s');
                
                $diff = time() - $lastInform->getTimestamp();
                if ($diff <= 600) { // 10 minutes = 600 seconds
                    $isOnline = true;
                }
            } catch (\Exception $e) {
                Log::warning('Failed parsing _lastInform: ' . $lastInformStr);
            }
        }

        // Get IP Address
        $ip = $this->getNestedValue($device, '_ip');

        // Extract Optical Rx Power
        $rxPower = $this->extractRxPower($device);

        return [
            'acs_status' => $isOnline ? 'online' : 'offline',
            'acs_last_inform' => $formattedLastInform,
            'acs_ip' => $ip,
            'acs_rx_power' => $rxPower,
        ];
    }

    protected function getSerialNumber($device)
    {
        $serial = $this->getNestedValue($device, 'DeviceID.SerialNumber');
        if (!$serial) {
            $serial = $this->getNestedValue($device, 'InternetGatewayDevice.DeviceInfo.SerialNumber');
        }
        if (!$serial) {
            $serial = $this->getNestedValue($device, 'Device.DeviceInfo.SerialNumber');
        }
        if (!$serial) {
            $id = $this->getNestedValue($device, '_id');
            if ($id && strpos($id, '-') !== false) {
                $parts = explode('-', $id);
                $serial = end($parts);
            }
        }
        return $serial ? trim($serial) : null;
    }

    protected function extractRxPower($device)
    {
        $paths = [
            'Device.Optical.Interface.1.RxPower',
            'Device.Optical.Interface.1.OpticalSignalLevel',
            'InternetGatewayDevice.WANDevice.1.X_GponInterafceConfig.RXPower',
            'InternetGatewayDevice.WANDevice.1.X_HW_GPON_DiagnosticInfo.RxPower',
            'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.RxPower',
            'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.RxOptPower',
            'InternetGatewayDevice.X_ZTE-COM_OpticalInfo.OpticalInfo.RxPower',
            'InternetGatewayDevice.WANDevice.1.X_ZTE-COM_OpticalInfo.RxPower',
            'Device.X_ZTE-COM_OpticalInfo.RxPower',
        ];

        foreach ($paths as $path) {
            $val = $this->getDeviceParam($device, $path);
            if ($val !== null && $val !== '') {
                return $this->formatRxPower($val);
            }
        }

        return null;
    }

    protected function getDeviceParam($device, $path)
    {
        $val = $this->getNestedValue($device, $path);
        if (is_array($val)) {
            if (isset($val['_value'])) {
                return $val['_value'];
            }
            if (isset($val['value'])) {
                return $val['value'];
            }
        }
        if (is_object($val)) {
            if (isset($val->{'_value'})) {
                return $val->{'_value'};
            }
            if (isset($val->value)) {
                return $val->value;
            }
        }
        return is_scalar($val) ? $val : null;
    }

    protected function getNestedValue($data, $path)
    {
        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } elseif (is_object($data) && isset($data->$key)) {
                $data = $data->$key;
            } else {
                return null;
            }
        }
        return $data;
    }

    protected function formatRxPower($val)
    {
        if ($val === null || $val === '') {
            return null;
        }

        $num = floatval($val);
        if ($num === 0.0) {
            return null;
        }

        // Standard TR-181 says RxPower is in microwatts (uW). 
        if ($num > 0) {
            if ($num >= 1 && $num <= 10000) {
                return round(10 * log10($num / 1000), 2);
            }
        }

        // Negative values representation handling
        if ($num < 0) {
            if ($num <= -5000) {
                return round($num / 1000, 2);
            }
            if ($num <= -100) {
                return round($num / 100, 2);
            }
        }

        return round($num, 2);
    }
}
