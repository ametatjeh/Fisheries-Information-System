<?php

namespace Tests\Feature\Output;

use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Wppnri;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GisSpatialAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_gis_endpoint_returns_all_spatial_layers(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Aceh Selatan', 'type' => 'kabupaten']);

        $site = LandingSite::create([
            'code' => 'TEST-PORT-1',
            'name' => 'Pelabuhan Perikanan Test',
            'site_type' => 'PPI',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.5866,
            'longitude' => 95.3262,
            'is_active' => true,
        ]);

        $wpp = Wppnri::create([
            'code' => '571',
            'name' => 'WPPNRI 571',
            'is_active' => true,
        ]);

        $fg = FishingGround::create([
            'name' => 'Gosong Karang Weh',
            'code' => 'FG-WEH',
            'wppnri_id' => $wpp->id,
            'latitude' => 5.8500,
            'longitude' => 95.2000,
            'is_active' => true,
        ]);

        $gear = FishingGear::create([
            'code' => 'PS-01',
            'name' => 'Pukat Cincin',
            'category' => 'jaring_lingkar',
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Nelayan Sejahtera',
            'gross_tonnage' => 15,
            'homeport_site_id' => $site->id,
            'primary_gear_id' => $gear->id,
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-GIS-001',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'wppnri_id' => $wpp->id,
            'primary_gear_id' => $gear->id,
            'departure_date' => now()->subDays(3),
            'validation_status' => 'validated',
        ]);

        $species = Species::create([
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'fao_code' => 'SKJ',
            'is_active' => true,
        ]);

        // Effort 1: Valid coordinate
        $effort1 = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 1,
            'setting_date' => now()->subDays(3),
            'duration_hours' => 3.5,
            'latitude_setting' => 5.7500,
            'longitude_setting' => 95.1200,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort1->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 450.5,
        ]);

        // Effort 2: NULL coordinate (MUST NOT be returned in efforts layer)
        $effort2 = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 2,
            'setting_date' => now()->subDays(2),
            'duration_hours' => 2.0,
            'latitude_setting' => null,
            'longitude_setting' => null,
        ]);

        $response = $this->actingAs($user)->getJson('/gis/data');

        $response->assertOk();
        $response->assertJsonStructure([
            'center' => ['lat', 'lng'],
            'ports',
            'fishing_grounds',
            'efforts',
            'vessels',
            'logbooks',
            'wpp_analysis',
            'counts' => ['ports', 'fishing_grounds', 'efforts', 'vessels', 'logbooks'],
        ]);

        $data = $response->json();

        // Check ports layer
        $this->assertCount(1, $data['ports']);
        $this->assertEquals('Pelabuhan Perikanan Test', $data['ports'][0]['name']);

        // Check fishing grounds layer
        $this->assertCount(1, $data['fishing_grounds']);
        $this->assertEquals('Gosong Karang Weh', $data['fishing_grounds'][0]['name']);

        // Check efforts layer: only 1 valid effort (NULL coordinate excluded)
        $this->assertCount(1, $data['efforts']);
        $this->assertEquals($effort1->id, $data['efforts'][0]['id']);
        $this->assertEquals(5.75, $data['efforts'][0]['lat_setting']);
        $this->assertEquals(450.5, $data['efforts'][0]['catch_kg']);
        $this->assertCount(1, $data['efforts'][0]['species_list']);
        $this->assertEquals('Cakalang', $data['efforts'][0]['species_list'][0]['name']);

        // Check vessels layer
        $this->assertCount(1, $data['vessels']);
        $this->assertEquals('KM Nelayan Sejahtera', $data['vessels'][0]['name']);
        $this->assertEquals('Pelabuhan Perikanan Test', $data['vessels'][0]['homeport']);
    }

    public function test_wpp_analysis_correctly_groups_mapped_and_unmapped(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Aceh Selatan', 'type' => 'kabupaten']);

        $site = LandingSite::create([
            'code' => 'PORT-WPP-TEST',
            'name' => 'Pelabuhan WPP Test',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.5,
            'longitude' => 95.3,
            'is_active' => true,
        ]);

        $wpp = Wppnri::create(['code' => '572', 'name' => 'WPPNRI 572', 'is_active' => true]);

        $gear = FishingGear::create(['code' => 'LL-01', 'name' => 'Rawai', 'category' => 'pancing', 'is_active' => true]);
        $vessel = Vessel::create(['name' => 'KM Samudera', 'gross_tonnage' => 20, 'homeport_site_id' => $site->id, 'is_active' => true]);
        $species = Species::create(['scientific_name' => 'Thunnus albacares', 'local_name_id' => 'Madidihang', 'is_active' => true]);

        // Trip 1: With WPP 572
        $trip1 = FishingTrip::create([
            'trip_number' => 'TRIP-WPP-572',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'wppnri_id' => $wpp->id,
            'departure_date' => now()->subDays(5),
            'validation_status' => 'validated',
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip1->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 1200.0,
        ]);

        // Trip 2: WITHOUT WPP (wppnri_id NULL) -> Must appear under "Tidak Terpetakan"
        $trip2 = FishingTrip::create([
            'trip_number' => 'TRIP-NO-WPP',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'wppnri_id' => null,
            'departure_date' => now()->subDays(4),
            'validation_status' => 'validated',
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip2->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 800.0,
        ]);

        $response = $this->actingAs($user)->getJson('/gis/data');
        $response->assertOk();

        $wppAnalysis = collect($response->json('wpp_analysis'));

        // Check WPP 572 entry
        $wpp572 = $wppAnalysis->firstWhere('code', '572');
        $this->assertNotNull($wpp572);
        $this->assertEquals(1200.0, $wpp572['catch_kg']);
        $this->assertEquals(1, $wpp572['trips']);

        // Check "Tidak Terpetakan" entry
        $unmapped = $wppAnalysis->firstWhere('code', '-');
        $this->assertNotNull($unmapped);
        $this->assertEquals('Tidak Terpetakan (WPP Belum Diisi)', $unmapped['name']);
        $this->assertEquals(800.0, $unmapped['catch_kg']);
        $this->assertEquals(1, $unmapped['trips']);
    }

    public function test_gis_filters_by_multiple_criteria(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency1 = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Banda Aceh', 'type' => 'kota']);
        $regency2 = Regency::create(['province_id' => $province->id, 'code' => '1102', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $site1 = LandingSite::create([
            'code' => 'PORT-FILTER-1',
            'name' => 'TPI Lampulo',
            'province_id' => $province->id,
            'regency_id' => $regency1->id,
            'latitude' => 5.58,
            'longitude' => 95.32,
            'is_active' => true,
        ]);

        $site2 = LandingSite::create([
            'code' => 'PORT-FILTER-2',
            'name' => 'TPI Meulaboh',
            'province_id' => $province->id,
            'regency_id' => $regency2->id,
            'latitude' => 4.14,
            'longitude' => 96.12,
            'is_active' => true,
        ]);

        $gear1 = FishingGear::create(['code' => 'G-01', 'name' => 'Pukat Cincin', 'category' => 'jaring_lingkar', 'is_active' => true]);
        $gear2 = FishingGear::create(['code' => 'G-02', 'name' => 'Pancing Handline', 'category' => 'pancing', 'is_active' => true]);

        $vessel1 = Vessel::create(['name' => 'KM 1', 'gross_tonnage' => 10, 'homeport_site_id' => $site1->id, 'primary_gear_id' => $gear1->id, 'is_active' => true]);
        $vessel2 = Vessel::create(['name' => 'KM 2', 'gross_tonnage' => 5, 'homeport_site_id' => $site2->id, 'primary_gear_id' => $gear2->id, 'is_active' => true]);

        // Filter by gear_id = $gear1->id
        $response = $this->actingAs($user)->getJson("/gis/data?gear_id={$gear1->id}");
        $response->assertOk();
        $vessels = $response->json('vessels');
        $this->assertCount(1, $vessels);
        $this->assertEquals('KM 1', $vessels[0]['name']);

        // Filter by landing_site_id = $site2->id
        $responseSite = $this->actingAs($user)->getJson("/gis/data?landing_site_id={$site2->id}");
        $responseSite->assertOk();
        $ports = $responseSite->json('ports');
        $this->assertCount(1, $ports);
        $this->assertEquals('TPI Meulaboh', $ports[0]['name']);
    }
}
