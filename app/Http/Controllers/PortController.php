<?php

namespace App\Http\Controllers;

use App\Models\Odp;
use App\Models\OdpPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortController extends Controller
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        if (Auth::user()->role === 'viewer') {
            return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
        }

        $method = $request->method();
        $odp_id = $request->query('odp_id') ? (int)$request->query('odp_id') : null;
        $port_number = $request->query('port') ? (int)$request->query('port') : null;

        switch ($method) {
            case 'PUT':
                return $this->updatePort($request, $odp_id, $port_number);
            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function updatePort(Request $request, $odp_id, $port_number)
    {
        if (!$odp_id || !$port_number) {
            return response()->json(['error' => 'ODP ID and port number are required'], 400);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            $port = OdpPort::where('odp_id', $odp_id)->where('port_number', $port_number)->first();
            if (!$port) {
                return response()->json(['error' => 'Port not found'], 404);
            }

            $updateFields = [];
            if (isset($data['status'])) $updateFields['status'] = $data['status'];
            if (isset($data['target'])) $updateFields['target'] = $data['target'];
            if (isset($data['connection_type'])) $updateFields['connection_type'] = $data['connection_type'];
            if (isset($data['target_port'])) $updateFields['target_port'] = $data['target_port'];
            if (isset($data['lat'])) $updateFields['lat'] = $data['lat'];
            if (isset($data['lng'])) $updateFields['lng'] = $data['lng'];
            if (isset($data['onu_number'])) $updateFields['onu_number'] = $data['onu_number'];
            if (isset($data['modem_type'])) $updateFields['modem_type'] = $data['modem_type'];
            if (isset($data['description'])) $updateFields['description'] = $data['description'];
            if (isset($data['path_coordinates'])) {
                $updateFields['path_coordinates'] = is_array($data['path_coordinates']) ? json_encode($data['path_coordinates']) : $data['path_coordinates'];
            }

            if (empty($updateFields)) {
                return response()->json(['error' => 'No fields to update'], 400);
            }

            $port->update($updateFields);

            // Update available_ports in ODP table only if status changed
            if (isset($data['status'])) {
                $availableCount = OdpPort::where('odp_id', $odp_id)->where('status', 'available')->count();
                Odp::where('id', $odp_id)->update(['available_ports' => $availableCount]);
            }

            return response()->json(['message' => 'Port updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
