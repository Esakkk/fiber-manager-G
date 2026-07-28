<?php

namespace App\Http\Controllers;

use App\Models\OltPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OltPortController extends Controller
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

        switch ($method) {
            case 'PUT':
                return $this->updateOLTPort($request);
            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function updateOLTPort(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (!isset($data['olt_id']) || !isset($data['port_number'])) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        $oltId = (int)$data['olt_id'];
        $portNumber = (int)$data['port_number'];

        try {
            $port = OltPort::where('olt_id', $oltId)->where('port_number', $portNumber)->first();
            if (!$port) {
                return response()->json(['error' => 'OLT port not found'], 404);
            }

            $status = $data['status'] ?? 'available';
            $targetOdc = (isset($data['target_odc_id']) && $data['target_odc_id'] !== '' && $data['target_odc_id'] !== null) ? (int)$data['target_odc_id'] : null;
            $description = isset($data['description']) && $data['description'] !== '' ? $data['description'] : null;

            $port->update([
                'status' => $status,
                'target_odc_id' => $targetOdc,
                'description' => $description
            ]);

            return response()->json(['message' => 'Port updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }
}
