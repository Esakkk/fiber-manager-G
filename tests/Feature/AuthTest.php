<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    public function test_auth_me_returns_401_when_not_logged_in(): void
    {
        $response = $this->getJson('/api/auth.php?action=me');

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Not logged in']);
    }

    public function test_login_validation_fails_with_empty_credentials(): void
    {
        $response = $this->postJson('/api/auth.php?action=login', [
            'username' => '',
            'password' => '',
        ]);

        $response->assertStatus(400)
                 ->assertJson(['error' => 'Username dan password harus diisi']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/auth.php?action=login', [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
                 ->assertJson(['error' => 'Username atau password salah']);
    }

    public function test_login_succeeds_with_correct_credentials_and_me_returns_user_info(): void
    {
        // Find or create user
        $user = User::where('username', 'admin')->first();
        if (!$user) {
            $user = User::create([
                'username' => 'admin',
                'full_name' => 'Administrator',
                'email' => 'admin@fiber.net',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'is_active' => true,
            ]);
        }

        $response = $this->postJson('/api/auth.php?action=login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Login berhasil')
                 ->assertJsonPath('user.username', 'admin');

        // Verify session persists and 'me' action returns logged in user
        $meResponse = $this->getJson('/api/auth.php?action=me');
        $meResponse->assertStatus(200)
                  ->assertJsonPath('user.username', 'admin');
    }
}
