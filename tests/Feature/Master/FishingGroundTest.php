<?php

namespace Tests\Feature\Master;

use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Wppnri;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingGroundTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_fishing_grounds(): void
    {
        $response = $this->get('/master/fishing-grounds');
        $response->assertRedirect('/login');
    }

    public function test_user_without_permission_cannot_access_fishing_grounds(): void
    {
        $user = User::factory()->create();
        $user->assignRole('petugas-lapangan'); // petugas-lapangan does not have access.master

        $response = $this->actingAs($user)->get('/master/fishing-grounds');
        $response->assertForbidden();
    }

    public function test_admin_can_view_fishing_grounds_index(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->get('/master/fishing-grounds');
        $response->assertOk();
        $response->assertSee('Master Daerah Penangkapan Ikan (Fishing Ground)');
    }

    public function test_admin_can_create_fishing_ground(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $wpp = Wppnri::create([
            'code' => '571',
            'name' => 'WPPNRI 571',
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'Perairan Karang Barat Pulo Weh',
            'code' => 'FG-WEH-01',
            'wppnri_id' => $wpp->id,
            'latitude' => 5.8672000,
            'longitude' => 95.2589000,
            'description' => 'Habitat karang tubir ikan pelagis besar',
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->post('/master/fishing-grounds', $payload);

        $response->assertRedirect('/master/fishing-grounds');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_grounds', [
            'name' => 'Perairan Karang Barat Pulo Weh',
            'code' => 'FG-WEH-01',
            'wppnri_id' => $wpp->id,
            'is_active' => true,
        ]);
    }

    public function test_fishing_ground_validation_rules(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Invalid latitude (> 90) and longitude (> 180)
        $invalidPayload = [
            'name' => '', // required
            'latitude' => 95.0, // max 90
            'longitude' => 200.0, // max 180
            'wppnri_id' => 99999, // non existent
        ];

        $response = $this->actingAs($admin)->post('/master/fishing-grounds', $invalidPayload);
        $response->assertSessionHasErrors(['name', 'latitude', 'longitude', 'wppnri_id']);
    }

    public function test_admin_can_update_fishing_ground(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Nama Lama',
            'code' => 'FG-OLD',
            'latitude' => 5.5,
            'longitude' => 95.3,
            'is_active' => true,
        ]);

        $updatePayload = [
            'name' => 'Nama Baru',
            'code' => 'FG-NEW',
            'latitude' => 5.6,
            'longitude' => 95.4,
            'description' => 'Updated desc',
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->put("/master/fishing-grounds/{$ground->id}", $updatePayload);

        $response->assertRedirect('/master/fishing-grounds');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_grounds', [
            'id' => $ground->id,
            'name' => 'Nama Baru',
            'code' => 'FG-NEW',
        ]);
    }

    public function test_fishing_ground_cannot_be_deleted_if_linked_to_trips(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Daerah Terkunci',
            'code' => 'FG-LOCKED',
            'is_active' => true,
        ]);

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $site = LandingSite::create([
            'code' => 'TEST-PORT',
            'name' => 'Pelabuhan Uji',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.5,
            'longitude' => 95.3,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Uji Coba',
            'gross_tonnage' => 10,
            'homeport_site_id' => $site->id,
            'is_active' => true,
        ]);

        // Create trip linked to this fishing ground
        FishingTrip::create([
            'trip_number' => 'TRIP-LOCK-001',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'departure_date' => now()->subDays(2),
            'fishing_ground_id' => $ground->id,
            'validation_status' => 'draft',
        ]);

        $response = $this->actingAs($admin)->delete("/master/fishing-grounds/{$ground->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('fishing_grounds', ['id' => $ground->id]);
    }

    public function test_fishing_ground_can_be_deleted_if_no_trips_linked(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Daerah Bebas',
            'code' => 'FG-FREE',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/master/fishing-grounds/{$ground->id}");

        $response->assertRedirect('/master/fishing-grounds');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('fishing_grounds', ['id' => $ground->id]);
    }

    public function test_toggle_status_flips_is_active(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Toggle Status Test',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch("/master/fishing-grounds/{$ground->id}/toggle-status");
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('fishing_grounds', ['id' => $ground->id, 'is_active' => false]);

        $response = $this->actingAs($admin)->patch("/master/fishing-grounds/{$ground->id}/toggle-status");
        $this->assertDatabaseHas('fishing_grounds', ['id' => $ground->id, 'is_active' => true]);
    }

    public function test_admin_can_create_fishing_ground_with_empty_strings_from_browser_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $payload = [
            'name' => 'Perairan Kosong Koordinat',
            'code' => '',
            'wppnri_id' => '',
            'latitude' => '',
            'longitude' => '',
            'description' => '',
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->post('/master/fishing-grounds', $payload);

        $response->assertRedirect('/master/fishing-grounds');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_grounds', [
            'name' => 'Perairan Kosong Koordinat',
            'code' => null,
            'wppnri_id' => null,
            'latitude' => null,
            'longitude' => null,
            'description' => null,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_fishing_ground_with_empty_strings_from_browser_form(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Nama Awal',
            'latitude' => 5.5,
            'longitude' => 95.3,
            'is_active' => true,
        ]);

        $updatePayload = [
            'name' => 'Nama Diubah Tanpa Koordinat',
            'code' => '',
            'wppnri_id' => '',
            'latitude' => '',
            'longitude' => '',
            'description' => '',
            'is_active' => '1',
        ];

        $response = $this->actingAs($admin)->put("/master/fishing-grounds/{$ground->id}", $updatePayload);

        $response->assertRedirect('/master/fishing-grounds');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_grounds', [
            'id' => $ground->id,
            'name' => 'Nama Diubah Tanpa Koordinat',
            'latitude' => null,
            'longitude' => null,
        ]);
    }

    public function test_fishing_grounds_index_contains_status_confirmation_modal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $ground = FishingGround::create([
            'name' => 'Perairan Ulee Lheue',
            'code' => 'FG-UL-01',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get('/master/fishing-grounds');

        $response->assertOk();
        $response->assertSee('openStatusModal', false);
        $response->assertSee('showStatusModal', false);
        $response->assertSee('Nonaktifkan Daerah Penangkapan?');
        $response->assertSee('Aktifkan Daerah Penangkapan?');
        $response->assertSee('Ya, Nonaktifkan');
        $response->assertSee('Ya, Aktifkan');
    }
}
