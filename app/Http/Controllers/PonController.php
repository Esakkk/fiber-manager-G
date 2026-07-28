<?php

namespace App\Http\Controllers;

use App\Models\Pon;
use App\Models\PonPort;
use App\Models\Odc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PonController extends Controller
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
                if ($id && $action === 'ports') {
                    return $this->getPONPorts($id);
                } elseif ($id) {
                    return $this->getPON($id);
                }
                return $this->getAllPON();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createPON($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updatePON($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deletePON($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllPON()
    {
        $pons = Pon::with(['olt', 'olt.pop'])->get()->map(function ($pon) {
            $pon->olt_name = $pon->olt ? $pon->olt->name : null;
            $pon->pop_name = ($pon->olt && $pon->olt->pop) ? $pon->olt->pop->name : null;
            return $pon;
        });

        return response()->json($pons);
    }

    protected function getPON($id)
    {
        $pon = Pon::with(['olt', 'olt.pop'])->find($id);

        if ($pon) {
            $pon->olt_name = $pon->olt ? $pon->olt->name : null;
            $pon->pop_id = $pon->olt ? $pon->olt->pop_id : null;
            $pon->pop_name = ($pon->olt && $pon->olt->pop) ? $pon->olt->pop->name : null;
            return response()->json($pon);
        }

        return response()->json(['error' => 'PON not found'], 404);
    }

    protected function getPONPorts($pon_id)
    {
        $ports = PonPort::where('pon_id', $pon_id)->orderBy('port_number')->get();

        if ($ports->isEmpty()) {
            $pon = Pon::find($pon_id);
            $portCount = $pon ? $pon->port_count : 8;

            $ports = [];
            for ($i = 1; $i <= $portCount; $i++) {
                $ports[] = [
                    'port_number' => $i,
                    'status' => 'available',
                    'target_odc_id' => null,
                    'odc_name' => null,
                    'description' => null,
                ];
            }
            return response()->json($ports);
        }

        $portsData = $ports->map(function ($port) {
            $port->odc_name = $port->targetOdc ? $port->targetOdc->name : null;
            return $port;
        });

        return response()->json($portsData);
    }

    protected function createPON(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['olt_id']) || !isset($data['card_number'])) {
            return response()->json(['error' => 'Missing required fields: olt_id, card_number'], 400);
        }

        try {
            DB::beginTransaction();

            $portCount = $data['port_count'] ?? 8;

            $pon = Pon::create([
                'olt_id' => $data['olt_id'],
                'card_number' => $data['card_number'],
                'name' => $data['name'] ?? "PON Card {$data['card_number']}",
                'port_count' => $portCount,
                'status' => $data['status'] ?? 'active',
                'description' => $data['description'] ?? '',
            ]);

            // Auto create ports
            for ($i = 1; $i <= $portCount; $i++) {
                PonPort::create([
                    'pon_id' => $pon->id,
                    'port_number' => $i,
                    'status' => 'available',
                ]);
            }

            DB::commit();
            return response()->json([
                'id' => $pon->id,
                'message' => 'PON created successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updatePON(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $pon = Pon::find($id);
        if (!$pon) {
            return response()->json(['error' => 'PON not found'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $fillableData = [];
            $allowed = ['olt_id', 'card_number', 'name', 'port_count', 'status', 'description'];
            foreach ($allowed as $field) {
                if (isset($data[$field])) {
                    $fillableData[$field] = $data[$field];
                }
            }

            if (empty($fillableData)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            $oldPortCount = (int)$pon->port_count;
            $pon->update($fillableData);

            // Handle changes in port count
            if (isset($data['port_count'])) {
                $newPortCount = (int)$data['port_count'];
                if ($newPortCount > $oldPortCount) {
                    for ($i = $oldPortCount + 1; $i <= $newPortCount; $i++) {
                        PonPort::create([
                            'pon_id' => $id,
                            'port_number' => $i,
                            'status' => 'available',
                        ]);
                    }
                } elseif ($newPortCount < $oldPortCount) {
                    // Only delete available ports above the new count
                    PonPort::where('pon_id', $id)
                        ->where('port_number', '>', $newPortCount)
                        ->where('status', 'available')
                        ->delete();
                }
            }

            DB::commit();
            return response()->json(['message' => 'PON updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deletePON($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $pon = Pon::find($id);
        if (!$pon) {
            return response()->json(['error' => 'PON not found'], 404);
        }

        $pon->delete();

        return response()->json(['message' => 'PON deleted successfully']);
    }

    // Handles PUT request for pon-port.php
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

        if (!isset($data['pon_id']) || !isset($data['port_number'])) {
            return response()->json(['error' => 'Missing required fields: pon_id, port_number'], 400);
        }

        $ponId = (int)$data['pon_id'];
        $portNumber = (int)$data['port_number'];

        $port = PonPort::where('pon_id', $ponId)->where('port_number', $portNumber)->first();

        if (!$port) {
            return response()->json(['error' => 'PON port not found'], 404);
        }

        $status = $data['status'] ?? 'available';
        $targetOdc = (isset($data['target_odc_id']) && $data['target_odc_id'] !== '' && $data['target_odc_id'] !== null) ? (int)$data['target_odc_id'] : null;
        $description = (isset($data['description']) && $data['description'] !== '') ? $data['description'] : null;

        $port->update([
            'status' => $status,
            'target_odc_id' => $targetOdc,
            'description' => $description,
        ]);

        return response()->json(['message' => 'Port updated successfully']);
    }
}
