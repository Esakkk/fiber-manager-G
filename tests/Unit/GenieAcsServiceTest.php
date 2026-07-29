<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GenieAcsService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GenieAcsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    public function test_get_acs_data_returns_default_when_onu_null()
    {
        $service = new GenieAcsService();
        $result = $service->getAcsData(null);

        $this->assertEquals([
            'acs_status' => 'offline',
            'acs_last_inform' => null,
            'acs_ip' => null,
            'acs_rx_power' => null,
        ], $result);
    }

    public function test_get_acs_data_resolves_and_caches_device_info()
    {
        $mockDevices = [
            [
                '_id' => '00259E-ZTEG12345678',
                '_lastInform' => now()->subMinutes(2)->toIso8601String(),
                '_ip' => '192.168.101.50',
                'DeviceID' => [
                    'SerialNumber' => 'ZTEG12345678'
                ],
                'InternetGatewayDevice' => [
                    'X_ZTE-COM_OpticalInfo' => [
                        'RxPower' => [
                            '_value' => '-22.50'
                        ]
                    ]
                ]
            ],
            [
                '_id' => 'HWTC87654321',
                '_lastInform' => now()->subMinutes(15)->toIso8601String(), // offline (>10 min)
                '_ip' => '192.168.101.60',
                'DeviceID' => [
                    'SerialNumber' => 'HWTC87654321'
                ],
                'InternetGatewayDevice' => [
                    'WANDevice' => [
                        '1' => [
                            'X_GponInterafceConfig' => [
                                'RXPower' => [
                                    '_value' => '-2450' // should format to -24.5
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                '_id' => 'TR181DEVICE',
                '_lastInform' => now()->subMinutes(1)->toIso8601String(),
                '_ip' => '192.168.101.70',
                'DeviceID' => [
                    'SerialNumber' => 'TR181DEVICE'
                ],
                'Device' => [
                    'Optical' => [
                        'Interface' => [
                            '1' => [
                                'RxPower' => [
                                    '_value' => '100' // microwatts: 100 uW = -10 dBm
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        Http::fake([
            '*/devices/*' => Http::response($mockDevices, 200)
        ]);

        $service = new GenieAcsService();

        // 1. Test ZTE Online and optical power mapping
        $zteData = $service->getAcsData('ZTEG12345678');
        $this->assertEquals('online', $zteData['acs_status']);
        $this->assertEquals('192.168.101.50', $zteData['acs_ip']);
        $this->assertEquals(-22.5, $zteData['acs_rx_power']);
        $this->assertNotNull($zteData['acs_last_inform']);

        // 2. Test Huawei Offline and -2450 formatting
        $hwData = $service->getAcsData('HWTC87654321');
        $this->assertEquals('offline', $hwData['acs_status']);
        $this->assertEquals('192.168.101.60', $hwData['acs_ip']);
        $this->assertEquals(-24.5, $hwData['acs_rx_power']);

        // 3. Test TR-181 microwatts formatting (100 uW -> -10 dBm)
        $tr181Data = $service->getAcsData('TR181DEVICE');
        $this->assertEquals('online', $tr181Data['acs_status']);
        $this->assertEquals(-10, $tr181Data['acs_rx_power']);

        // 4. Test caching (Http fake should only be called once)
        Http::fake([
            '*/devices/*' => Http::response([], 200) // If it refetches, it gets empty
        ]);
        $cachedZteData = $service->getAcsData('ZTEG12345678');
        $this->assertEquals('online', $cachedZteData['acs_status']); // Still loaded from cache
    }
}
