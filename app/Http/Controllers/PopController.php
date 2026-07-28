<?php

namespace App\Http\Controllers;

use App\Models\Pop;
use App\Models\Olt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PopController extends Controller
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
                if ($id && $action === 'olts') {
                    return $this->getPOPOLTs($id);
                } elseif ($id) {
                    return $this->getPOP($id);
                }
                return $this->getAllPOP();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createPOP($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updatePOP($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deletePOP($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllPOP()
    {
        // Select pop with count of olts
        $pops = Pop::orderBy('created_at', 'desc')->get()->map(function ($pop) {
            $pop->olt_count = $pop->olts()->count();
            return $pop;
        });

        return response()->json($pops);
    }

    protected function getPOP($id)
    {
        $pop = Pop::find($id);

        if ($pop) {
            return response()->json($pop);
        }

        return response()->json(['error' => 'POP not found'], 404);
    }

    protected function getPOPOLTs($pop_id)
    {
        $olts = Olt::where('pop_id', $pop_id)->orderBy('name')->get();

        foreach ($olts as $olt) {
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

            // Calculate stats
            $usedCount = 0;
            $availableCount = 0;
            $maintenanceCount = 0;

            foreach ($ports as $port) {
                if ($port['status'] === 'used') {
                    $usedCount++;
                } elseif ($port['status'] === 'available') {
                    $availableCount++;
                } elseif ($port['status'] === 'maintenance') {
                    $maintenanceCount++;
                }
            }

            $olt->used_ports = $usedCount;
            $olt->available_ports = $availableCount;
            $olt->maintenance_ports = $maintenanceCount;
        }

        return response()->json($olts);
    }

    protected function createPOP(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Missing required fields: name, lat, lng'], 400);
        }

        $pop = Pop::create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'location' => $data['location'] ?? '',
            'address' => $data['address'] ?? '',
            'description' => $data['description'] ?? '',
        ]);

        return response()->json([
            'id' => $pop->id,
            'message' => 'POP created successfully',
        ]);
    }

    protected function updatePOP(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $pop = Pop::find($id);
        if (!$pop) {
            return response()->json(['error' => 'POP not found'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        $fillableData = [];
        $allowed = ['name', 'code', 'lat', 'lng', 'location', 'address', 'description'];
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fillableData[$field] = $data[$field];
            }
        }

        if (empty($fillableData)) {
            return response()->json(['error' => 'No fields to update'], 400);
        }

        $pop->update($fillableData);

        return response()->json(['message' => 'POP updated successfully']);
    }

    protected function deletePOP($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $pop = Pop::find($id);
        if (!$pop) {
            return response()->json(['error' => 'POP not found'], 404);
        }

        // Hapus all related OLTs (cascade will trigger deletions or we can handle it)
        // Note: The database table olt cascade is set in foreignId constraint in migrations,
        // but let's make sure it deletes properly.
        $pop->delete();

        return response()->json(['message' => 'POP deleted successfully']);
    }
}
