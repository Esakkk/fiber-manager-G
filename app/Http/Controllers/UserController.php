<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function handle(Request $request)
    {
        // Require admin role
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Silakan login terlebih dahulu'], 401);
        }

        if (Auth::user()->role !== 'admin') {
            return response()->json(['error' => 'Forbidden', 'message' => 'Hanya admin yang dapat mengelola user'], 403);
        }

        $method = $request->method();
        $id = $request->query('id') ? (int)$request->query('id') : null;
        $action = $request->query('action');

        switch ($method) {
            case 'GET':
                if ($id) {
                    return $this->getUser($id);
                }
                return $this->getAllUsers();

            case 'POST':
                return $this->createUser($request);

            case 'PUT':
                if ($action === 'reset-password') {
                    return $this->resetPassword($request, $id);
                }
                return $this->updateUser($request, $id);

            case 'DELETE':
                return $this->deleteUser($id);

            default:
                return response()->json(['error' => 'Method not allowed'], 405);
        }
    }

    protected function getAllUsers()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return response()->json($users);
    }

    protected function getUser($id)
    {
        $user = User::find($id);

        if ($user) {
            return response()->json($user);
        }

        return response()->json(['error' => 'User not found'], 404);
    }

    protected function createUser(Request $request)
    {
        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['username']) || empty($data['password']) || empty($data['full_name'])) {
            return response()->json(['error' => 'Username, password, dan nama lengkap harus diisi'], 400);
        }

        if (strlen($data['username']) < 3) {
            return response()->json(['error' => 'Username minimal 3 karakter'], 400);
        }

        if (strlen($data['password']) < 6) {
            return response()->json(['error' => 'Password minimal 6 karakter'], 400);
        }

        $exists = User::where('username', $data['username'])->exists();
        if ($exists) {
            return response()->json(['error' => 'Username sudah digunakan'], 400);
        }

        $user = User::create([
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'full_name' => $data['full_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'] ?? 'operator',
            'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            'notes' => $data['notes'] ?? null,
        ]);

        return response()->json(['id' => $user->id, 'message' => 'User berhasil ditambahkan']);
    }

    protected function updateUser(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (isset($data['username'])) {
            $exists = User::where('username', $data['username'])->where('id', '!=', $id)->exists();
            if ($exists) {
                return response()->json(['error' => 'Username sudah digunakan'], 400);
            }
        }

        // Prevent changing own role away from admin
        if (Auth::id() == $id && isset($data['role']) && $data['role'] !== 'admin') {
            return response()->json(['error' => 'Anda tidak dapat mengubah role sendiri'], 400);
        }

        $fillableData = [];
        $allowedFields = ['username', 'full_name', 'email', 'phone', 'role', 'is_active', 'notes'];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                if ($field === 'is_active') {
                    fillableData[$field] = filter_var($data[$field], FILTER_VALIDATE_BOOLEAN);
                } else {
                    fillableData[$field] = $data[$field];
                }
            }
        }

        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                return response()->json(['error' => 'Password minimal 6 karakter'], 400);
            }
            $fillableData['password'] = Hash::make($data['password']);
        }

        if (empty($fillableData)) {
            return response()->json(['error' => 'No fields to update'], 400);
        }

        $user->update($fillableData);

        return response()->json(['message' => 'User berhasil diupdate']);
    }

    protected function resetPassword(Request $request, $id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        $data = $request->json()->all();
        if (empty($data)) {
            $data = $request->all();
        }

        if (empty($data['new_password'])) {
            return response()->json(['error' => 'Password baru harus diisi'], 400);
        }

        if (strlen($data['new_password']) < 6) {
            return response()->json(['error' => 'Password minimal 6 karakter'], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        return response()->json(['message' => 'Password berhasil direset']);
    }

    protected function deleteUser($id)
    {
        if (!$id) {
            return response()->json(['error' => 'ID is required'], 400);
        }

        if (Auth::id() == $id) {
            return response()->json(['error' => 'Anda tidak dapat menghapus akun sendiri'], 400);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
