<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Odp;
use App\Models\OdpPort;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
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
                if ($id) {
                    return $this->getCustomer($id);
                }
                return $this->getAllCustomers($request);

            case 'POST':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                if ($action === 'connect') {
                    return $this->connectToOdp($request);
                } elseif ($action === 'disconnect') {
                    return $this->disconnectFromOdp($request);
                }
                return $this->createCustomer($request);

            case 'PUT':
                if (Auth::user()->role === 'viewer') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                if ($action === 'connect') {
                    return $this->connectToOdp($request, $id);
                } elseif ($action === 'disconnect') {
                    return $this->disconnectFromOdp($request, $id);
                }
                return $this->updateCustomer($request, $id);

            case 'DELETE':
                if (Auth::user()->role !== 'admin') {
                    return response()->json(['error' => 'Forbidden', 'message' => 'Anda tidak memiliki akses untuk operasi ini'], 403);
                }
                return $this->deleteCustomer($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllCustomers(Request $request)
    {
        $query = Customer::orderBy('created_at', 'desc');

        if ($request->query('status') === 'unconnected') {
            $query->whereNull('odp_id');
        }

        $customers = $query->get()->map(function ($c) {
            if ($c->odp_id) {
                $odp = Odp::find($c->odp_id);
                $c->odp_name = $odp ? $odp->name : null;
            } else {
                $c->odp_name = null;
            }
            return $c;
        });

        return response()->json($customers);
    }

    protected function getCustomer($id)
    {
        $customer = Customer::find($id);

        if ($customer) {
            if ($customer->odp_id) {
                $odp = Odp::find($customer->odp_id);
                $customer->odp_name = $odp ? $odp->name : null;
            } else {
                $customer->odp_name = null;
            }
            return response()->json($customer);
        }

        return response()->json(['error' => 'Customer not found'], 404);
    }

    protected function createCustomer(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['name']) || !isset($data['lat']) || !isset($data['lng'])) {
            return response()->json(['error' => 'Nama dan koordinat pelanggan harus diisi'], 400);
        }

        $customer = Customer::create([
            'name' => $data['name'],
            'lat' => $data['lat'],
            'lng' => $data['lng'],
            'onu_number' => $data['onu_number'] ?? null,
            'modem_type' => $data['modem_type'] ?? null,
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'description' => $data['description'] ?? null,
            'odp_id' => null,
            'port_number' => null,
            'path_coordinates' => null,
        ]);

        return response()->json([
            'id' => $customer->id,
            'message' => 'Pelanggan berhasil ditambahkan',
        ]);
    }

    protected function updateCustomer(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID Pelanggan diperlukan'], 400);
        }

        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        try {
            DB::beginTransaction();

            $updateData = [];
            if (isset($data['name'])) $updateData['name'] = $data['name'];
            if (isset($data['lat'])) $updateData['lat'] = $data['lat'];
            if (isset($data['lng'])) $updateData['lng'] = $data['lng'];
            if (isset($data['onu_number'])) $updateData['onu_number'] = $data['onu_number'];
            if (isset($data['modem_type'])) $updateData['modem_type'] = $data['modem_type'];
            if (isset($data['address'])) $updateData['address'] = $data['address'];
            if (isset($data['phone'])) $updateData['phone'] = $data['phone'];
            if (isset($data['description'])) $updateData['description'] = $data['description'];
            if (isset($data['path_coordinates'])) {
                $updateData['path_coordinates'] = is_array($data['path_coordinates']) ? json_encode($data['path_coordinates']) : $data['path_coordinates'];
            }

            $customer->update($updateData);

            // Jika pelanggan terhubung ke ODP, sinkronkan data ke tabel odp_ports
            if ($customer->odp_id && $customer->port_number) {
                $port = OdpPort::where('odp_id', $customer->odp_id)
                    ->where('port_number', $customer->port_number)
                    ->first();

                if ($port) {
                    $port->update([
                        'target' => $customer->name,
                        'lat' => $customer->lat,
                        'lng' => $customer->lng,
                        'onu_number' => $customer->onu_number,
                        'modem_type' => $customer->modem_type,
                        'description' => $customer->description,
                        'path_coordinates' => $customer->path_coordinates,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['message' => 'Data pelanggan berhasil diperbarui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function connectToOdp(Request $request, $id = null)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        $customerId = $id ?: ($data['customer_id'] ?? null);
        $odpId = $data['odp_id'] ?? null;
        $portNumber = $data['port_number'] ?? null;

        if (!$customerId || !$odpId || !$portNumber) {
            return response()->json(['error' => 'Customer ID, ODP ID, dan Nomor Port diperlukan'], 400);
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
        }

        try {
            DB::beginTransaction();

            // Cek apakah port ODP tersedia
            $port = OdpPort::where('odp_id', $odpId)
                ->where('port_number', $portNumber)
                ->first();

            if (!$port) {
                DB::rollBack();
                return response()->json(['error' => 'Port ODP tidak ditemukan'], 404);
            }

            if ($port->status !== 'available') {
                DB::rollBack();
                return response()->json(['error' => 'Port ODP sudah terpakai atau dalam pemeliharaan'], 400);
            }

            // Jika pelanggan sudah terhubung ke ODP lain, putuskan dulu
            if ($customer->odp_id && $customer->port_number) {
                $oldPort = OdpPort::where('odp_id', $customer->odp_id)
                    ->where('port_number', $customer->port_number)
                    ->first();
                if ($oldPort) {
                    $oldPort->update([
                        'status' => 'available',
                        'target' => null,
                        'lat' => null,
                        'lng' => null,
                        'onu_number' => null,
                        'modem_type' => null,
                        'description' => null,
                        'path_coordinates' => null,
                    ]);
                    $this->updateOdpAvailablePorts($customer->odp_id);
                }
            }

            // Hubungkan ke port baru
            $customer->update([
                'odp_id' => $odpId,
                'port_number' => $portNumber,
                'path_coordinates' => isset($data['path_coordinates']) ? (is_array($data['path_coordinates']) ? json_encode($data['path_coordinates']) : $data['path_coordinates']) : null
            ]);

            $port->update([
                'status' => 'used',
                'target' => $customer->name,
                'lat' => $customer->lat,
                'lng' => $customer->lng,
                'onu_number' => $customer->onu_number,
                'modem_type' => $customer->modem_type,
                'description' => $customer->description,
                'path_coordinates' => $customer->path_coordinates,
            ]);

            $this->updateOdpAvailablePorts($odpId);

            DB::commit();
            return response()->json(['message' => 'Pelanggan berhasil disambungkan ke ODP']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function disconnectFromOdp(Request $request, $id = null)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        $customerId = $id ?: ($data['customer_id'] ?? null);
        if (!$customerId) {
            return response()->json(['error' => 'Customer ID diperlukan'], 400);
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
        }

        if (!$customer->odp_id || !$customer->port_number) {
            return response()->json(['error' => 'Pelanggan memang belum terhubung ke ODP mana pun'], 400);
        }

        try {
            DB::beginTransaction();

            $odpId = $customer->odp_id;
            $portNumber = $customer->port_number;

            // Putuskan koneksi di port ODP
            $port = OdpPort::where('odp_id', $odpId)
                ->where('port_number', $portNumber)
                ->first();

            if ($port) {
                $port->update([
                    'status' => 'available',
                    'target' => null,
                    'lat' => null,
                    'lng' => null,
                    'onu_number' => null,
                    'modem_type' => null,
                    'description' => null,
                    'path_coordinates' => null,
                ]);
                $this->updateOdpAvailablePorts($odpId);
            }

            // Putuskan di sisi pelanggan
            $customer->update([
                'odp_id' => null,
                'port_number' => null,
                'path_coordinates' => null,
            ]);

            DB::commit();
            return response()->json(['message' => 'Koneksi pelanggan berhasil diputuskan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    protected function deleteCustomer($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID Pelanggan diperlukan'], 400);
        }

        $customer = Customer::find($id);
        if (!$customer) {
            return response()->json(['error' => 'Pelanggan tidak ditemukan'], 404);
        }

        try {
            DB::beginTransaction();

            // Jika terhubung ke ODP, bebaskan port ODP dulu
            if ($customer->odp_id && $customer->port_number) {
                $port = OdpPort::where('odp_id', $customer->odp_id)
                    ->where('port_number', $customer->port_number)
                    ->first();
                if ($port) {
                    $port->update([
                        'status' => 'available',
                        'target' => null,
                        'lat' => null,
                        'lng' => null,
                        'onu_number' => null,
                        'modem_type' => null,
                        'description' => null,
                        'path_coordinates' => null,
                    ]);
                    $this->updateOdpAvailablePorts($customer->odp_id);
                }
            }

            $customer->delete();

            DB::commit();
            return response()->json(['message' => 'Pelanggan berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function updateOdpAvailablePorts($odpId)
    {
        $availableCount = OdpPort::where('odp_id', $odpId)->where('status', 'available')->count();
        Odp::where('id', $odpId)->update(['available_ports' => $availableCount]);
    }
}
