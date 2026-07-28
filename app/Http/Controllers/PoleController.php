<?php

namespace App\Http\Controllers;

use App\Models\Pole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PoleController extends Controller
{
    public function handle(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        $method = $request->method();
        $id = $request->query('id') ? (int)$request->query('id') : null;

        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->getPole($id);
                }
                return $this->getAllPole();

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->createPole($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->updatePole($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deletePole($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllPole()
    {
        $poles = Pole::orderBy('created_at', 'desc')->get();
        return response()->json($poles);
    }

    protected function getPole($id)
    {
        $pole = Pole::find($id);
        if ($pole) {
            return response()->json($pole);
        }
        return response()->json(['error' => 'Pole not found'], 404);
    }

    protected function createPole(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Missing required fields: name, lat, lng'], 400);
        }

        try {
            $pole = Pole::create([
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location' => $data['location'] ?? '',
                'description' => $data['description'] ?? '',
                'jenis_tiang' => $data['jenis_tiang'] ?? null,
            ]);

            return response()->json(['id' => $pole->id, 'message' => 'Pole created successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function updatePole(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'Missing pole ID'], 400);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Missing required fields: name, lat, lng'], 400);
        }

        try {
            $pole = Pole::find($id);
            if (!$pole) {
                return response()->json(['error' => 'Pole not found'], 404);
            }

            $pole->update([
                'name' => $data['name'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'location' => $data['location'] ?? '',
                'description' => $data['description'] ?? '',
                'jenis_tiang' => $data['jenis_tiang'] ?? null,
            ]);

            return response()->json(['id' => $id, 'message' => 'Pole updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deletePole($id)
    {
        if (!$id) {
            return response()->json(['error' => 'Missing pole ID'], 400);
        }

        try {
            $pole = Pole::find($id);
            if ($pole) {
                $pole->delete();
            }
            return response()->json(['id' => $id, 'message' => 'Pole deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
