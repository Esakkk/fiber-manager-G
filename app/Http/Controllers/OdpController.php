<?php

namespace App\Http\Controllers;

use App\Models\Odp;
use App\Models\OdpPort;
use App\Models\OdcOdpConnection;
use App\Models\Odc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdpController extends Controller
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $method = $request->method();
        $idStr = $request->query('id');
        if ($idStr && is_string($idStr)) {
            $idStr = preg_replace('/^(CUSTOMER_|ODP_|ODC_|POLE_)/', '', $idStr);
        }
        $id = ($idStr !== null && $idStr !== '') ? (int)$idStr : null;

        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->getODP($id);
                }
                return $this->getAllODP();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createODP($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updateODP($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deleteODP($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllODP()
    {
        $odps = Odp::with([
            'source',
            'ports' => function ($q) {
                $q->orderBy('port_number');
            },
            'photos' => function ($q) {
                $q->orderBy('is_primary', 'desc')->orderBy('created_at', 'asc');
            }
        ])->orderBy('created_at', 'desc')->get()->map(function ($odp) {
            $odp->source_name = $odp->source ? $odp->source->name : null;

            $ports = $odp->ports;
            $odp->ports = $ports;

            $available = 0;
            foreach ($ports as $port) {
                if ($port->status === 'available') {
                    $available++;
                }
            }
            $odp->available_ports = $available;

            $odp->photos = $odp->photos->map(function ($photo) {
                $photo->url = 'uploads/odp/' . $photo->filename;
                return $photo;
            });

            return $odp;
        });

        return response()->json($odps);
    }

    protected function getODP($id)
    {
        $odp = Odp::find($id);

        if ($odp) {
            $sourceName = null;
            if ($odp->source_type === 'odc') {
                $odc = Odc::find($odp->source_id);
                $sourceName = $odc ? $odc->name : null;
            } elseif ($odp->source_type === 'odp') {
                $odpSource = Odp::find($updatedODP->source_id ?? $odp->source_id);
                $sourceName = $odpSource ? $odpSource->name : null;
            }
            $odp->source_name = $sourceName;

            $ports = $odp->ports()->orderBy('port_number')->get();
            $odp->ports = $ports;

            $available = 0;
            foreach ($ports as $port) {
                if ($port->status === 'available') {
                    $available++;
                }
            }
            $odp->available_ports = $available;

            $odp->photos = $odp->photos()->orderBy('is_primary', 'desc')->orderBy('created_at', 'asc')->get()->map(function ($photo) {
                $photo->url = 'uploads/odp/' . $photo->filename;
                return $photo;
            });

            return response()->json($odp);
        }

        return response()->json(['error' => 'ODP not found'], 404);
    }

    protected function createODP(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        try {
            DB::beginTransaction();

            // Validasi port belum terpakai jika source_type = 'odc'
            $sourceId = isset($data['source_id']) ? $this->cleanId($data['source_id']) : null;
            if (($data['source_type'] ?? '') === 'odc' && !empty($sourceId) && !empty($data['port_number_in_odc'])) {
                $exists = OdcOdpConnection::where('odc_id', $sourceId)
                    ->where('port_number', $data['port_number_in_odc'])
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json(['error' => 'Port ODC ' . $data['port_number_in_odc'] . ' sudah digunakan oleh ODP lain'], 400);
                }
            }

            // Insert ODP
            $odp = Odp::create([
                'name' => $data['name'],
                'source_id' => $sourceId,
                'source_type' => $data['source_type'] ?? null,
                'port_number_in_odc' => $data['port_number_in_odc'] ?? null,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location' => $data['location'] ?? '',
                'total_ports' => $data['total_ports'] ?? 8,
                'available_ports' => $data['total_ports'] ?? 8,
                'description' => $data['description'] ?? '',
                'path_coordinates' => isset($data['path_coordinates']) ? (is_array($data['path_coordinates']) ? json_encode($data['path_coordinates']) : $data['path_coordinates']) : null
            ]);

            $total_ports = $data['total_ports'] ?? 8;

            // Create ports
            for ($i = 1; $i <= $total_ports; $i++) {
                OdpPort::create([
                    'odp_id' => $odp->id,
                    'port_number' => $i,
                    'status' => 'available'
                ]);
            }

            // If connected to ODC, create connection
            if (!empty($sourceId) && ($data['source_type'] ?? '') === 'odc' && !empty($data['port_number_in_odc'])) {
                OdcOdpConnection::create([
                    'odc_id' => $sourceId,
                    'odp_id' => $odp->id,
                    'port_number' => $data['port_number_in_odc']
                ]);
                $this->updateODCUsedPorts($sourceId);
            }

            DB::commit();
            return response()->json(['id' => $odp->id, 'message' => 'ODP created successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updateODP(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $odp = Odp::find($id);
            if (!$odp) {
                DB::rollBack();
                return response()->json(['error' => 'ODP not found'], 404);
            }

            $oldSourceId = $odp->source_id;
            $oldSourceType = $odp->source_type;
            $oldPortNumber = $odp->port_number_in_odc;
            $oldTotalPorts = $odp->total_ports;

            $newSourceId = isset($data['source_id']) ? $this->cleanId($data['source_id']) : null;
            // Validasi port baru jika berubah
            if (isset($data['source_type']) && $data['source_type'] === 'odc' &&
                $newSourceId && isset($data['port_number_in_odc']) &&
                $data['port_number_in_odc'] &&
                ($oldSourceId != $newSourceId || $oldPortNumber != $data['port_number_in_odc'])) {

                $exists = OdcOdpConnection::where('odc_id', $newSourceId)
                    ->where('port_number', $data['port_number_in_odc'])
                    ->where('odp_id', '!=', $id)
                    ->exists();

                if ($exists) {
                    DB::rollBack();
                    return response()->json(['error' => 'Port ODC ' . $data['port_number_in_odc'] . ' sudah digunakan oleh ODP lain'], 400);
                }
            }

            // Update fields
            $updateData = [];
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['source_id'])) $updateData['source_id'] = $newSourceId ?: null;
            if (isset($data['source_type'])) $updateData['source_type'] = $data['source_type'] ?: null;
            if (isset($data['port_number_in_odc'])) $updateData['port_number_in_odc'] = $data['port_number_in_odc'] ?: null;
            if (isset($data['lat'])) $updateData['lat'] = $data['lat'];
            if (isset($data['lng'])) $updateData['lng'] = $data['lng'];
            if (isset($data['location'])) $updateData['location'] = $data['location'];
            if (isset($data['total_ports'])) $updateData['total_ports'] = $data['total_ports'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['path_coordinates'])) {
                $updateData['path_coordinates'] = is_array($data['path_coordinates']) ? json_encode($data['path_coordinates']) : $data['path_coordinates'];
            }

            if (!empty($updateData)) {
                $odp->update($updateData);
            }

            // Handle perubahan jumlah port
            if (isset($data['total_ports'])) {
                $newTotalPorts = (int)$data['total_ports'];
                if ($newTotalPorts > $oldTotalPorts) {
                    for ($i = $oldTotalPorts + 1; $i <= $newTotalPorts; $i++) {
                        OdpPort::create([
                            'odp_id' => $id,
                            'port_number' => $i,
                            'status' => 'available'
                        ]);
                    }
                } elseif ($newTotalPorts < $oldTotalPorts) {
                    OdpPort::where('odp_id', $id)
                        ->where('port_number', '>', $newTotalPorts)
                        ->where('status', 'available')
                        ->delete();
                }
            }

            // Handle ODC connection changes
            if ($oldSourceId && $oldSourceType === 'odc') {
                OdcOdpConnection::where('odc_id', $oldSourceId)->where('odp_id', $id)->delete();
                $this->updateODCUsedPorts($oldSourceId);
            }

            if (isset($data['source_type']) && $data['source_type'] === 'odc' && $newSourceId && isset($data['port_number_in_odc']) && $data['port_number_in_odc']) {
                OdcOdpConnection::create([
                    'odc_id' => $newSourceId,
                    'odp_id' => $id,
                    'port_number' => $data['port_number_in_odc']
                ]);
                $this->updateODCUsedPorts($newSourceId);
            }

            // Update available_ports
            $this->updateODPAvailablePorts($id);

            DB::commit();

            // Return updated ODP
            $updatedODP = Odp::find($id);
            $sourceName = null;
            if ($updatedODP->source_type === 'odc') {
                $odc = Odc::find($updatedODP->source_id);
                $sourceName = $odc ? $odc->name : null;
            } elseif ($updatedODP->source_type === 'odp') {
                $odpSource = Odp::find($updatedODP->source_id);
                $sourceName = $odpSource ? $odpSource->name : null;
            }
            $updatedODP->source_name = $sourceName;

            $ports = $updatedODP->ports()->orderBy('port_number')->get();
            $updatedODP->ports = $ports;

            $available = 0;
            foreach ($ports as $port) {
                if ($port->status === 'available') {
                    $available++;
                }
            }
            $updatedODP->available_ports = $available;

            return response()->json([
                'message' => 'ODP updated successfully',
                'data' => $updatedODP
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deleteODP($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        try {
            DB::beginTransaction();

            $odp = Odp::find($id);
            if ($odp && $odp->source_id && $odp->source_type === 'odc') {
                OdcOdpConnection::where('odp_id', $id)->delete();
                $this->updateODCUsedPorts($odp->source_id);
            }

            if ($odp) {
                $odp->delete();
            }

            DB::commit();
            return response()->json(['message' => 'ODP deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updateODPAvailablePorts($odp_id)
    {
        $count = OdpPort::where('odp_id', $odp_id)->where('status', 'available')->count();
        Odp::where('id', $odp_id)->update(['available_ports' => $count]);
    }

    protected function updateODCUsedPorts($odc_id)
    {
        $count = OdcOdpConnection::where('odc_id', $odc_id)->count();
        Odc::where('id', $odc_id)->update(['used_ports' => $count]);
    }

    private function cleanId($id)
    {
        if ($id === null || $id === '') {
            return null;
        }
        return (int) preg_replace('/^(CUSTOMER_|ODP_|ODC_|POLE_)/', '', (string)$id);
    }
}
