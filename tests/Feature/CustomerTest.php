<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Odp;
use App\Models\OdpPort;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = User::create([
            'username' => 'admin',
            'full_name' => 'Administrator',
            'email' => 'admin@fiber.net',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_customer_crud_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // 1. Create Customer
        $response = $this->postJson('/api/customers.php', [
            'name' => 'Budi Santoso',
            'lat' => -6.2088,
            'lng' => 106.8456,
            'onu_number' => 'ZTEGC1234567',
            'modem_type' => 'ZTE F609',
            'address' => 'Jl. Kebon Sirih No. 12',
            'phone' => '081234567890',
            'description' => 'Pelanggan baru komplek A'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['id', 'message']);

        $customerId = $response->json('id');

        // 2. Get Single Customer
        $getResponse = $this->getJson("/api/customers.php?id={$customerId}");
        $getResponse->assertStatus(200)
                   ->assertJsonPath('name', 'Budi Santoso')
                   ->assertJsonPath('onu_number', 'ZTEGC1234567');

        // 3. Update Customer
        $updateResponse = $this->putJson("/api/customers.php?id={$customerId}", [
            'name' => 'Budi Santoso Updated',
            'lat' => -6.2088,
            'lng' => 106.8456,
            'onu_number' => 'ZTEGC9999999',
            'modem_type' => 'ZTE F609',
        ]);

        $updateResponse->assertStatus(200)
                      ->assertJsonPath('message', 'Data pelanggan berhasil diperbarui');

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'name' => 'Budi Santoso Updated',
            'onu_number' => 'ZTEGC9999999'
        ]);
    }

    public function test_customer_connect_and_disconnect_endpoints(): void
    {
        $this->actingAs($this->adminUser);

        // Create ODP
        $odp = Odp::create([
            'name' => 'ODP-SIRIH-01',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Sirih St.',
            'total_ports' => 8,
            'available_ports' => 8
        ]);

        // Create ODP ports (since Controller does this, but we can seed it)
        for ($i = 1; $i <= 8; $i++) {
            OdpPort::create([
                'odp_id' => $odp->id,
                'port_number' => $i,
                'status' => 'available'
            ]);
        }

        // Create Customer
        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'lat' => -6.2088,
            'lng' => 106.8456,
            'onu_number' => 'ZTEGC1234567',
            'modem_type' => 'ZTE F609',
        ]);

        // 1. Connect Customer to ODP Port 3
        $connectResponse = $this->postJson('/api/customers.php?action=connect', [
            'customer_id' => $customer->id,
            'odp_id' => $odp->id,
            'port_number' => 3
        ]);

        $connectResponse->assertStatus(200)
                       ->assertJsonPath('message', 'Pelanggan berhasil disambungkan ke ODP');

        // Verify database state
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'odp_id' => $odp->id,
            'port_number' => 3
        ]);

        $this->assertDatabaseHas('odp_ports', [
            'odp_id' => $odp->id,
            'port_number' => 3,
            'status' => 'used',
            'target' => 'Budi Santoso',
            'onu_number' => 'ZTEGC1234567'
        ]);

        $this->assertEquals(7, Odp::find($odp->id)->available_ports);

        // 2. Disconnect Customer
        $disconnectResponse = $this->postJson('/api/customers.php?action=disconnect', [
            'customer_id' => $customer->id
        ]);

        $disconnectResponse->assertStatus(200)
                          ->assertJsonPath('message', 'Koneksi pelanggan berhasil diputuskan');

        // Verify database state after disconnect
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'odp_id' => null,
            'port_number' => null
        ]);

        $this->assertDatabaseHas('odp_ports', [
            'odp_id' => $odp->id,
            'port_number' => 3,
            'status' => 'available',
            'target' => null
        ]);

        $this->assertEquals(8, Odp::find($odp->id)->available_ports);
    }

    public function test_customer_connect_with_prefixed_ids(): void
    {
        $this->actingAs($this->adminUser);

        // Create ODP
        $odp = Odp::create([
            'name' => 'ODP-SIRIH-02',
            'lat' => -6.2,
            'lng' => 106.8,
            'location' => 'Sirih St.',
            'total_ports' => 8,
            'available_ports' => 8
        ]);

        // Create ODP ports
        for ($i = 1; $i <= 8; $i++) {
            OdpPort::create([
                'odp_id' => $odp->id,
                'port_number' => $i,
                'status' => 'available'
            ]);
        }

        // Create Customer
        $customer = Customer::create([
            'name' => 'Budi Prefixed',
            'lat' => -6.2088,
            'lng' => 106.8456,
            'onu_number' => 'ZTEGC1111111',
            'modem_type' => 'ZTE F609',
        ]);

        // Connect Customer to ODP Port 4 using prefixed IDs
        $connectResponse = $this->postJson('/api/customers.php?action=connect', [
            'customer_id' => "CUSTOMER_{$customer->id}",
            'odp_id' => "ODP_{$odp->id}",
            'port_number' => 4
        ]);

        $connectResponse->assertStatus(200)
                       ->assertJsonPath('message', 'Pelanggan berhasil disambungkan ke ODP');

        // Verify database state
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'odp_id' => $odp->id,
            'port_number' => 4
        ]);

        $this->assertDatabaseHas('odp_ports', [
            'odp_id' => $odp->id,
            'port_number' => 4,
            'status' => 'used',
            'target' => 'Budi Prefixed'
        ]);

        // Disconnect using prefixed ID
        $disconnectResponse = $this->postJson('/api/customers.php?action=disconnect', [
            'customer_id' => "CUSTOMER_{$customer->id}"
        ]);

        $disconnectResponse->assertStatus(200)
                          ->assertJsonPath('message', 'Koneksi pelanggan berhasil diputuskan');
    }
}
