<?php

namespace App\Http\Controllers;

use App\Models\Odc;
use App\Models\Pop;
use App\Models\Olt;
use App\Models\Pon;
use App\Models\PonPort;
use App\Models\OdcOdpConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OdcController extends Controller
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $method = $request->method();
        $id = $request->query('id') ? (int)$request->query('id') : null;
        $action = $request->query('action');

        switch ($method) {
            case 'GET':
                if ($request->has('sources')) {
                    return $this->getAvailableSources();
                } elseif ($id && $request->has('ports')) {
                    return $this->getODCPorts($id);
                } elseif ($id) {
                    return $this->getODC($id);
                }
                return $this->getAllODC();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createODC($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updateODC($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deleteODC($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllODC()
    {
        $odcs = Odc::orderBy('created_at', 'desc')->get()->map(function ($odc) {
            $odc->connected_odps = $odc->connections()->count();
            
            // Resolve source names
            $pop = Pop::find($odc->source_id);
            $olt = Olt::find($odc->olt_id);
            $pon = Pon::find($odc->pon_id);
            
            $odc->source_pop_name = $pop ? $pop->name : null;
            $odc->source_olt_name = $olt ? $olt->name : null;
            $odc->source_pon_card = $pon ? $pon->card_number : null;
            $odc->source_pon_name = $pon ? $pon->name : null;
            $odc->source_port_number = $odc->pon_port_number;

            $pathParts = [];
            if ($pop) $pathParts[] = $pop->name;
            if ($olt) $pathParts[] = $olt->name;
            if ($pon) $pathParts[] = "PON " . $pon->card_number;
            if ($odc->pon_port_number) $pathParts[] = "Port " . $odc->pon_port_number;

            $odc->source_path = implode(' → ', $pathParts);
            $odc->photos = $odc->photos()->orderBy('is_primary', 'desc')->get()->map(function ($photo) {
                $photo->url = 'uploads/odc/' . $photo->filename;
                return $photo;
            });

            return $odc;
        });

        return response()->json($odcs);
    }

    protected function getODC($id)
    {
        $odc = Odc::find($id);

        if ($odc) {
            $odc->connected_odps = $odc->connections()->count();

            $pop = Pop::find($odc->source_id);
            $olt = Olt::find($odc->olt_id);
            $pon = Pon::find($odc->pon_id);

            $odc->source_pop_id = $odc->source_id;
            $odc->source_pop_name = $pop ? $pop->name : null;
            $odc->source_olt_id = $odc->olt_id;
            $odc->source_olt_name = $olt ? $olt->name : null;
            $odc->source_pon_id = $odc->pon_id;
            $odc->source_pon_card = $pon ? $pon->card_number : null;
            $odc->source_pon_name = $pon ? $pon->name : null;
            $odc->source_port_number = $odc->pon_port_number;

            $pathParts = [];
            if ($pop) $pathParts[] = $pop->name;
            if ($olt) $pathParts[] = $olt->name;
            if ($pon) $pathParts[] = "PON " . $pon->card_number;
            if ($odc->pon_port_number) $pathParts[] = "Port " . $odc->pon_port_number;

            $odc->source_path = implode(' → ', $pathParts);

            // Fetch connected ODPs
            $odc->connected_odps_list = OdcOdpConnection::where('odc_id', $id)
                ->join('odp', 'odc_odp_connections.odp_id', '=', 'odp.id')
                ->select('odp.id', 'odp.name', 'odc_odp_connections.port_number')
                ->get();

            $odc->used_ports = count($odc->connected_odps_list);

            $odc->photos = $odc->photos()->orderBy('is_primary', 'desc')->get()->map(function ($photo) {
                $photo->url = 'uploads/odc/' . $photo->filename;
                return $photo;
            });

            return response()->json($odc);
        }

        return response()->json(['error' => 'ODC not found'], 404);
    }

    protected function getODCPorts($odc_id)
    {
        $usedPorts = OdcOdpConnection::where('odc_id', $odc_id)
            ->join('odp', 'odc_odp_connections.odp_id', '=', 'odp.id')
            ->select('odc_odp_connections.odp_id', 'odp.name as odp_name', 'odc_odp_connections.port_number')
            ->get();

        $odc = Odc::find($odc_id);
        $capacity = $odc ? $odc->capacity : 8;

        $usedPortMap = [];
        foreach ($usedPorts as $port) {
            $usedPortMap[$port->port_number] = [
                'odp_id' => $port->odp_id,
                'odp_name' => $port->odp_name,
            ];
        }

        $ports = [];
        for ($i = 1; $i <= $capacity; $i++) {
            if (isset($usedPortMap[$i])) {
                $ports[] = [
                    'port_number' => $i,
                    'status' => 'used',
                    'odp_id' => $usedPortMap[$i]['odp_id'],
                    'odp_name' => $usedPortMap[$i]['odp_name'],
                ];
            } else {
                $ports[] = [
                    'port_number' => $i,
                    'status' => 'available',
                    'odp_id' => null,
                    'odp_name' => null,
                ];
            }
        }

        return response()->json($ports);
    }

    protected function getAvailableSources()
    {
        $sources = [
            'pops' => Pop::orderBy('name')->select('id', 'name', 'code', 'location')->get(),
            'olts' => Olt::with('pop')->get()->map(function ($olt) {
                return [
                    'id' => $olt->id,
                    'name' => $olt->name,
                    'model' => $olt->model,
                    'pop_id' => $olt->pop_id,
                    'pop_name' => $olt->pop ? $olt->pop->name : null,
                ];
            }),
            'pons' => Pon::where('status', 'active')->with(['olt', 'olt.pop'])->get()->map(function ($pon) {
                return [
                    'id' => $pon->id,
                    'card_number' => $pon->card_number,
                    'pon_name' => $pon->name,
                    'port_count' => $pon->port_count,
                    'olt_id' => $pon->olt_id,
                    'olt_name' => $pon->olt ? $pon->olt->name : null,
                    'pop_id' => $pon->olt ? $pon->olt->pop_id : null,
                    'pop_name' => ($pon->olt && $pon->olt->pop) ? $pon->olt->pop->name : null,
                ];
            }),
        ];

        return response()->json($sources);
    }

    protected function createODC(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Missing required fields: name, lat, lng'], 400);
        }

        if (!isset($data['pon_id']) || !isset($data['pon_port_number'])) {
            return response()->json(['error' => 'ODC harus terhubung ke PON Card dan Port tertentu'], 400);
        }

        try {
            DB::beginTransaction();

            $ponPort = PonPort::where('pon_id', $data['pon_id'])
                ->where('port_number', $data['pon_port_number'])
                ->where('status', 'available')
                ->first();

            if (!$ponPort) {
                return response()->json(['error' => 'Port PON sudah tidak tersedia'], 400);
            }

            $pon = Pon::with('olt')->find($data['pon_id']);
            if (!$pon || !$pon->olt) {
                return response()->json(['error' => 'PON atau OLT tidak valid'], 400);
            }

            $olt = $pon->olt;
            $pop_id = $olt->pop_id;

            $odc = Odc::create([
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location' => $data['location'] ?? '',
                'capacity' => $data['capacity'] ?? 8,
                'used_ports' => 0,
                'description' => $data['description'] ?? '',
                'source_type' => 'pon',
                'source_id' => $pop_id,
                'pon_id' => $data['pon_id'],
                'pon_port_number' => $data['pon_port_number'],
                'olt_id' => $olt->id,
            ]);

            $ponPort->update([
                'status' => 'used',
                'target_odc_id' => $odc->id,
            ]);

            DB::commit();
            return response()->json([
                'id' => $odc->id,
                'message' => 'ODC created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updateODC(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $odc = Odc::find($id);
        if (!$odc) {
            return response()->json(['error' => 'ODC not found'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $fillableData = [];
            $portChanged = false;
            $oldPonId = $odc->pon_id;
            $oldPonPortNum = $odc->pon_port_number;

            $allowed = ['name', 'lat', 'lng', 'location', 'capacity', 'description', 'path_coordinates'];
            foreach ($allowed as $field) {
                if (isset($data[$field])) {
                    $fillableData[$field] = $data[$field];
                }
            }

            if (isset($data['pon_id']) && isset($data['pon_port_number'])) {
                $newPonId = (int)$data['pon_id'];
                $newPonPortNum = (int)$data['pon_port_number'];

                if ($newPonId !== (int)$oldPonId || $newPonPortNum !== (int)$oldPonPortNum) {
                    $newPort = PonPort::where('pon_id', $newPonId)
                        ->where('port_number', $newPonPortNum)
                        ->where('status', 'available')
                        ->first();

                    if (!$newPort) {
                        return response()->json(['error' => 'Port PON tidak tersedia atau tidak ditemukan'], 400);
                    }

                    $pon = Pon::with('olt')->find($newPonId);
                    if (!$pon || !$pon->olt) {
                        return response()->json(['error' => 'PON tidak valid'], 400);
                    }

                    $fillableData['pon_id'] = $newPonId;
                    $fillableData['pon_port_number'] = $newPonPortNum;
                    $fillableData['olt_id'] = $pon->olt_id;
                    $fillableData['source_id'] = $pon->olt->pop_id;
                    $fillableData['source_type'] = 'pon';

                    $portChanged = true;
                }
            }

            if (empty($fillableData)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            $odc->update($fillableData);

            if ($portChanged) {
                // Free old port
                PonPort::where('pon_id', $oldPonId)
                    ->where('port_number', $oldPonPortNum)
                    ->update([
                        'status' => 'available',
                        'target_odc_id' => null,
                    ]);

                // Bind new port
                PonPort::where('pon_id', $data['pon_id'])
                    ->where('port_number', $data['pon_port_number'])
                    ->update([
                        'status' => 'used',
                        'target_odc_id' => $id,
                    ]);
            }

            DB::commit();
            return response()->json(['message' => 'ODC updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deleteODC($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $odc = Odc::find($id);
        if (!$odc) {
            return response()->json(['error' => 'ODC not found'], 404);
        }

        try {
            DB::beginTransaction();

            if ($odc->pon_id && $odc->pon_port_number) {
                PonPort::where('pon_id', $odc->pon_id)
                    ->where('port_number', $odc->pon_port_number)
                    ->update([
                        'status' => 'available',
                        'target_odc_id' => null,
                    ]);
            }

            // Delete ODP connections
            OdcOdpConnection::where('odc_id', $id)->delete();

            // Delete ODC
            $odc->delete();

            DB::commit();
            return response()->json(['message' => 'ODC deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
