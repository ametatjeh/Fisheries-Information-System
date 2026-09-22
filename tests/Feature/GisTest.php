<?php

namespace Tests\Feature;

use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Logbook;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Wppnri;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/gis');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_with_permission_can_view_gis_index(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/gis');

        $response->assertOk();
        $response->assertSee('Peta Geospasial Perikanan Tangkap');
        $response->assertSee('fisheries-map');
    }

    public function test_gis_data_endpoint_returns_json(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/gis/data');

        $response->assertOk();
        $response->assertJsonStructure([
            'center' => ['lat', 'lng'],
            'ports',
            'efforts',
            'logbooks',
            'counts' => ['ports', 'efforts', 'logbooks'],
        ]);
    }

    public function test_gis_supports_filtering(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this
            ->actingAs($user)
            ->get('/gis?gear_id=1&start_date=2026-01-01&end_date=2026-12-31');

        $response->assertOk();

        $apiResponse = $this
            ->actingAs($user)
            ->getJson('/gis/data?gear_id=1');

        $apiResponse->assertOk();
        $apiResponse->assertJsonStructure(['counts']);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        $user = User::factory()->create();
        $user->assignRole('verifikator'); // verifikator only has access.validation

        $response = $this
            ->actingAs($user)
            ->get('/gis');

        $response->assertForbidden();
    }

    public function test_guest_can_access_gis_data_endpoint_without_authentication(): void
    {
        $response = $this->getJson('/gis/data');

        $response->assertOk();
        $response->assertJsonStructure([
            'center' => ['lat', 'lng'],
            'ports',
            'landing_sites',
            'fishing_grounds',
            'master_fishing_grounds',
            'efforts',
            'fishing_efforts',
            'vessels',
            'homeports',
            'logbooks',
            'logbook_points',
            'wpp_analysis',
            'counts' => [
                'ports',
                'landing_sites',
                'fishing_grounds',
                'master_fishing_grounds',
                'efforts',
                'fishing_efforts',
                'vessels',
                'homeports',
                'logbooks',
                'logbook_points',
            ],
        ]);
    }

    public function test_gis_data_distinguishes_layers_and_excludes_null_effort_coordinates(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create([
            'province_id' => $province->id,
            'code' => '1171',
            'name' => 'Kota Banda Aceh',
            'type' => 'kota',
        ]);

        $port = LandingSite::create([
            'name' => 'TPI Ulee Lheue Test',
            'code' => 'TPI-TEST',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.5562,
            'longitude' => 95.2921,
            'is_active' => true,
        ]);

        $wpp = Wppnri::create([
            'code' => '572',
            'name' => 'WPPNRI 572',
            'is_active' => true,
        ]);

        $gear = FishingGear::create([
            'code' => 'PS-01',
            'name' => 'Purse Seine Test',
            'category' => 'jaring_lingkar',
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Aceh Samudera',
            'gross_tonnage' => 30,
            'homeport_site_id' => $port->id,
            'primary_gear_id' => $gear->id,
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-TEST-GIS-01',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $port->id,
            'landing_site_id' => $port->id,
            'wppnri_id' => $wpp->id,
            'primary_gear_id' => $gear->id,
            'departure_date' => now()->subDays(2),
            'validation_status' => 'validated',
        ]);

        // Effort 1: Valid coordinate
        FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 1,
            'setting_date' => now()->subDays(2),
            'duration_hours' => 3.0,
            'latitude_setting' => 5.6120,
            'longitude_setting' => 95.1500,
        ]);

        // Effort 2: NULL coordinate (MUST NOT be in efforts coordinates)
        FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 2,
            'setting_date' => now()->subDays(2),
            'duration_hours' => 2.0,
            'latitude_setting' => null,
            'longitude_setting' => null,
        ]);

        // Logbook point
        Logbook::create([
            'fishing_trip_id' => $trip->id,
            'log_date' => now()->subDays(2),
            'latitude' => 5.6000,
            'longitude' => 95.1800,
            'weather_condition' => 'cerah',
        ]);

        // Master Fishing Ground without coordinates (must not fabricate coordinates)
        FishingGround::create([
            'name' => 'Perairan Pulo Aceh Test Master',
            'code' => 'FG-TEST-01',
            'wppnri_id' => $wpp->id,
            'latitude' => null,
            'longitude' => null,
            'is_active' => true,
        ]);

        $response = $this->getJson('/gis/data');
        $response->assertOk();

        $data = $response->json();

        // 1. Effort: only 1 valid coordinate returned
        $this->assertCount(1, $data['efforts']);
        $this->assertEquals(5.6120, $data['efforts'][0]['lat_setting']);
        $this->assertEquals('Fishing Effort', $data['efforts'][0]['layer_label']);

        // 2. Port layer
        $this->assertCount(1, $data['ports']);
        $this->assertEquals('TPI Ulee Lheue Test', $data['ports'][0]['name']);
        $this->assertEquals('Pelabuhan / TPI', $data['ports'][0]['layer_label']);

        // 3. Vessel / Homeport layer (not live tracking)
        $this->assertCount(1, $data['vessels']);
        $this->assertEquals('KM Aceh Samudera', $data['vessels'][0]['name']);
        $this->assertEquals('Homeport Kapal', $data['vessels'][0]['layer_label']);
        $this->assertStringContainsString('Bukan Live Tracking', $data['vessels'][0]['note']);

        // 4. Logbook layer (historical)
        $this->assertCount(1, $data['logbooks']);
        $this->assertEquals('Titik Logbook Historis', $data['logbooks'][0]['layer_label']);
        $this->assertStringContainsString('Bukan Posisi Real-time', $data['logbooks'][0]['note']);

        // 5. Master Fishing Ground: 0 in mapped layer (no fake coordinates), 1 in master reference
        $this->assertCount(0, $data['fishing_grounds']);
        $this->assertCount(1, $data['master_fishing_grounds']);
        $this->assertFalse($data['master_fishing_grounds'][0]['has_coordinates']);
        $this->assertNull($data['master_fishing_grounds'][0]['lat']);
        $this->assertNull($data['master_fishing_grounds'][0]['lng']);
    }
}
