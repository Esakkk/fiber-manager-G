<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function handle(Request $request)
    {
        $action = $request->query('action');

        switch ($action) {
            case 'login':
                if ($request->isMethod('post')) {
                    return $this->login($request);
                }
                return response()->json(['error' => 'Method not allowed'], 405);
            
            case 'logout':
                if ($request->isMethod('post')) {
                    return $this->logout($request);
                }
                return response()->json(['error' => 'Method not allowed'], 405);

            case 'me':
                if ($request->isMethod('get')) {
                    return $this->me($request);
                }
                return response()->json(['error' => 'Method not allowed'], 405);

            default:
                return response()->json(['error' => 'Invalid action'], 400);
        }
    }

    protected function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return response()->json(['error' => 'Username dan password harus diisi'], 400);
        }

        $user = User::where('username', $credentials['username'])->first();

        if ($user && Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true])) {
            $request->session()->regenerate();

            // Update last login timestamp
            $user->update(['last_login' => now()]);

            // Log successful attempt
            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip() ?? 'unknown',
                'user_agent' => $request->userAgent() ?? 'unknown',
                'status' => 'success',
            ]);

            return response()->json([
                'message' => 'Login berhasil',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                ],
            ]);
        }

        // Log failed attempt if user exists
        if ($user) {
            LoginLog::create([
                'user_id' => $user->id,
                'ip_address' => $request->ip() ?? 'unknown',
                'user_agent' => $request->userAgent() ?? 'unknown',
                'status' => 'failed',
            ]);
        }

        return response()->json(['error' => 'Username atau password salah'], 401);
    }

    protected function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Logout berhasil']);
    }

    protected function me(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'full_name' => $user->full_name,
                    'role' => $user->role,
                ],
            ]);
        }

        return response()->json(['error' => 'Not logged in'], 401);
    }
}
