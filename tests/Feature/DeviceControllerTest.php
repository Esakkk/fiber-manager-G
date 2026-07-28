<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Pop;
use App\Models\Olt;
use App\Models\Pon;
use App\Models\PonPort;
use App\Models\Odc;
use App\Models\Odp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create an admin user for authenticated requests
        $this->adminUser = User::create([
            'username' => 'admin',
            'full_name' => 'Administrator',
            'email' => 'admin@fiber.net',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_pop_controller_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // Create a POP
        $response = $this->postJson('/api/pop.php', [
            'name' => 'POP Test Jaringan',
            'code' => 'POP-TEST',
            'lat' => -6.200000,
            'lng' => 106.816666,
            'location' => 'Jakarta',
            'address' => 'Sudirman St. 12',
            'description' => 'POP untuk unit testing'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'message']);

        $popId = $response->json('id');

        // Get single POP
        $getResponse = $this->getJson("/api/pop.php?id={$popId}");
        $getResponse->assertStatus(200)
                   ->assertJsonPath('name', 'POP Test Jaringan');

        // Get all POPs
        $getAllResponse = $this->getJson('/api/pop.php');
        $getAllResponse->assertStatus(200)
                      ->assertJsonCount(1);
    }

    public function test_olt_controller_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // Create a POP first (location is required)
        $pop = Pop::create([
            'name' => 'POP Olt',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Gd. Cyber',
        ]);

        // Create OLT
        $response = $this->postJson('/api/olt.php', [
            'pop_id' => $pop->id,
            'name' => 'OLT Test',
            'brand' => 'ZTE',
            'model' => 'C300',
            'total_ports' => 16,
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Gd. Cyber 2',
            'description' => 'OLT unit testing'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'message']);

        $oltId = $response->json('id');

        // Get OLT
        $getResponse = $this->getJson("/api/olt.php?id={$oltId}");
        $getResponse->assertStatus(200)
                   ->assertJsonPath('name', 'OLT Test');

        // Get all OLTs
        $getAllResponse = $this->getJson('/api/olt.php');
        $getAllResponse->assertStatus(200)
                      ->assertJsonCount(1);
    }

    public function test_odc_controller_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // Setup the physical structure: Pop -> Olt -> Pon -> PonPort
        $pop = Pop::create([
            'name' => 'POP ODC',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Kebon Jeruk',
        ]);

        $olt = Olt::create([
            'pop_id' => $pop->id,
            'name' => 'OLT ODC',
            'total_ports' => 16,
        ]);

        $pon = Pon::create([
            'olt_id' => $olt->id,
            'card_number' => 1,
            'port_count' => 8,
            'status' => 'active',
        ]);

        $ponPort = PonPort::create([
            'pon_id' => $pon->id,
            'port_number' => 1,
            'status' => 'available',
        ]);

        // Create ODC
        $response = $this->postJson('/api/odc.php', [
            'name' => 'ODC Test 1',
            'code' => 'ODC-T01',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Blok M',
            'capacity' => 144,
            'pon_id' => $pon->id,
            'pon_port_number' => 1,
            'description' => 'ODC unit testing'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'message']);

        $odcId = $response->json('id');

        // Get ODC
        $getResponse = $this->getJson("/api/odc.php?id={$odcId}");
        $getResponse->assertStatus(200)
                   ->assertJsonPath('name', 'ODC Test 1');

        // Get all ODCs
        $getAllResponse = $this->getJson('/api/odc.php');
        $getAllResponse->assertStatus(200)
                      ->assertJsonCount(1);
    }

    public function test_odp_controller_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // Create ODP
        $response = $this->postJson('/api/odp.php', [
            'name' => 'ODP Test 1',
            'code' => 'ODP-T01',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Fatmawati',
            'total_ports' => 8,
            'description' => 'ODP unit testing'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'message']);

        $odpId = $response->json('id');

        // Get ODP
        $getResponse = $this->getJson("/api/odp.php?id={$odpId}");
        $getResponse->assertStatus(200)
                   ->assertJsonPath('name', 'ODP Test 1');

        // Get all ODPs
        $getAllResponse = $this->getJson('/api/odp.php');
        $getAllResponse->assertStatus(200)
                      ->assertJsonCount(1);
    }
}
