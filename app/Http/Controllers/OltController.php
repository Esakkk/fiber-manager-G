<?php

namespace App\Http\Controllers;

use App\Models\Olt;
use App\Models\OltPort;
use App\Models\Pon;
use App\Models\PonPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OltController extends Controller
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
                if ($id && $action === 'pons') {
                    return $this->getOLTPONs($id);
                } elseif ($id) {
                    return $this->getOLT($id);
                }
                return $this->getAllOLT();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createOLT($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updateOLT($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deleteOLT($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllOLT()
    {
        $olts = Olt::with('pop:id,name')->orderBy('created_at', 'desc')->get()->map(function ($olt) {
            $olt->pop_name = $olt->pop ? $olt->pop->name : null;
            return $olt;
        });

        return response()->json($olts);
    }

    protected function getOLT($id)
    {
        $olt = Olt::find($id);

        if ($olt) {
            $olt->pop_name = $olt->pop ? $olt->pop->name : null;
            $olt->pop_location = $olt->pop ? $olt->pop->location : null;

            $ports = $olt->ports()->orderBy('port_number')->get()->toArray();

            if (empty($ports)) {
                $totalPorts = $olt->total_ports ?? 16;
                $ports = [];
                for ($i = 1; $i <= $totalPorts; $i++) {
                    $ports[] = [
                        'port_number' => $i,
                        'status' => 'available',
                        'target_odc_id' => null,
                        'description' => null,
                    ];
                }
            }

            $olt->ports = $ports;
            return response()->json($olt);
        }

        return response()->json(['error' => 'OLT not found'], 404);
    }

    protected function getOLTPONs($olt_id)
    {
        $pons = Pon::where('olt_id', $olt_id)->orderBy('card_number')->get();

        foreach ($pons as $pon) {
            $pon->total_ports = $pon->ports()->count();
            $pon->used_ports = $pon->ports()->where('status', 'used')->count();
            $pon->available_ports = $pon->ports()->where('status', 'available')->count();
            $pon->ports = $pon->ports()->orderBy('port_number')->get();
        }

        return response()->json($pons);
    }

    protected function createOLT(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['name']) || !isset($data['pop_id'])) {
            return response()->json(['error' => 'Missing required fields: name, pop_id'], 400);
        }

        try {
            DB::beginTransaction();

            $totalPorts = isset($data['total_ports']) ? (int)$data['total_ports'] : 16;
            $totalPONs = isset($data['total_pon_ports']) ? (int)$data['total_pon_ports'] : 4;

            $olt = Olt::create([
                'pop_id' => $data['pop_id'],
                'name' => $data['name'],
                'model' => $data['model'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'management_port' => $data['management_port'] ?? 22,
                'total_ports' => $totalPorts,
                'total_pon_ports' => $totalPONs,
                'location' => $data['location'] ?? '',
                'description' => $data['description'] ?? '',
            ]);

            // Create OLT Ports
            for ($i = 1; $i <= $totalPorts; $i++) {
                OltPort::create([
                    'olt_id' => $olt->id,
                    'port_number' => $i,
                    'status' => 'available',
                ]);
            }

            // Auto create PON cards
            $portsPerPON = ceil($totalPorts / $totalPONs);
            for ($i = 1; $i <= $totalPONs; $i++) {
                $pon = Pon::create([
                    'olt_id' => $olt->id,
                    'card_number' => $i,
                    'name' => "PON Card $i",
                    'port_count' => $portsPerPON,
                    'status' => 'active',
                ]);

                // Create ports for this PON
                for ($j = 1; $j <= $portsPerPON; $j++) {
                    PonPort::create([
                        'pon_id' => $pon->id,
                        'port_number' => $j,
                        'status' => 'available',
                    ]);
                }
            }

            DB::commit();
            return response()->json([
                'id' => $olt->id,
                'message' => 'OLT created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updateOLT(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $olt = Olt::find($id);
        if (!$olt) {
            return response()->json(['error' => 'OLT not found'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $fillableData = [];
            $allowed = ['name', 'model', 'ip_address', 'management_port', 'total_ports', 'location', 'description'];
            foreach ($allowed as $field) {
                if (isset($data[$field])) {
                    $fillableData[$field] = $data[$field];
                }
            }

            if (empty($fillableData)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            // Handle adding ports if total_ports increases
            if (isset($data['total_ports'])) {
                $newTotal = (int)$data['total_ports'];
                $oldTotal = (int)$olt->total_ports;

                if ($newTotal > $oldTotal) {
                    for ($i = $oldTotal + 1; $i <= $newTotal; $i++) {
                        OltPort::create([
                            'olt_id' => $id,
                            'port_number' => $i,
                            'status' => 'available',
                        ]);
                    }
                }
            }

            $olt->update($fillableData);

            DB::commit();
            return response()->json(['message' => 'OLT updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deleteOLT($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $olt = Olt::find($id);
        if (!$olt) {
            return response()->json(['error' => 'OLT not found'], 404);
        }

        $olt->delete();

        return response()->json(['message' => 'OLT deleted successfully']);
    }

    // Handles PUT request for olt-port.php
    public function updatePort(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        if (Auth::user()->role === 'viewer') {
            return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['olt_id']) || !isset($data['port_number'])) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        $oltId = (int)$data['olt_id'];
        $portNumber = (int)$data['port_number'];

        $port = OltPort::where('olt_id', $oltId)->where('port_number', $portNumber)->first();

        if (!$port) {
            // Auto-create port entry if it doesn't exist yet but total_ports permits it
            $olt = Olt::find($oltId);
            if ($olt && $portNumber <= $olt->total_ports) {
                $port = OltPort::create([
                    'olt_id' => $oltId,
                    'port_number' => $portNumber,
                    'status' => 'available',
                ]);
            } else {
                return response()->json(['error' => 'Port not found'], 404);
            }
        }

        $port->update([
            'status' => $data['status'] ?? 'available',
            'target_odc_id' => (isset($data['target_odc_id']) && $data['target_odc_id'] !== '') ? (int)$data['target_odc_id'] : null,
            'description' => $data['description'] ?? null,
        ]);

        return response()->json(['message' => 'Port updated successfully']);
    }
}
