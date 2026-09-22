<?php

namespace Tests\Feature\Analysis;

use App\Models\BiologicalMeasurement;
use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Sample;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Wppnri;
use App\Services\AdvancedStatisticService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvancedStatisticDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_guest_cannot_access_dashboard_or_statistics(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/analysis/statistics')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard Eksekutif &amp; Analisis Terpadu', false);
        $response->assertSee('Total Fishing Trip');
        $response->assertSee('Total Tangkapan Fisik');
        $response->assertSee('chart.umd.min.js', false);
        $response->assertSee('dashboardTrendChart', false);
    }

    public function test_user_with_permission_can_access_advanced_statistics(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('access.statistics');

        $response = $this->actingAs($user)->get('/analysis/statistics');

        $response->assertOk();
        $response->assertSee('Analisis Lanjutan: Statistik &amp; Dashboard Perikanan', false);
        $response->assertSee('Chart 1: Tren Produksi &amp; Tangkapan Bulanan', false);
        $response->assertSee('Chart 5: Distribusi Frekuensi Panjang', false);
        $response->assertSee('chart.umd.min.js', false);
        $response->assertSee('statTrendChart', false);
    }

    public function test_activity_and_catch_kpi_lineage_and_separation(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $site = LandingSite::create([
            'code' => 'SITE-1',
            'name' => 'TPI Lampulo',
            'site_type' => 'PPS',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.58,
            'longitude' => 95.32,
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Nelayan Maju',
            'registration_number' => 'REG-101',
            'vessel_type' => 'Motor',
            'gross_tonnage' => 20,
            'homeport_site_id' => $site->id,
            'is_active' => true,
        ]);

        $gear = FishingGear::create([
            'code' => 'PS-1',
            'name' => 'Pukat Cincin',
            'name_id' => 'Pukat Cincin Pelagis',
            'category' => 'Surrounding Nets',
            'is_active' => true,
        ]);

        $wpp = Wppnri::create([
            'code' => '571',
            'name' => 'WPP-NRI 571',
            'is_active' => true,
        ]);

        $species = Species::create([
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'family' => 'Scombridae',
            'is_active' => true,
        ]);

        // Trip
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-2026-001',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'departure_date' => '2026-03-10',
            'return_date' => '2026-03-12',
            'primary_gear_id' => $gear->id,
            'wppnri_id' => $wpp->id,
            'crew_count' => 10,
        ]);

        // Effort 1: 5 hours
        $effort1 = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 1,
            'setting_date' => '2026-03-10',
            'duration_hours' => 5.0,
        ]);

        // Catch 1: 500 kg linked to effort
        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort1->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 500.0,
            'fish_count' => 120,
        ]);

        // Effort 2: 5 hours
        $effort2 = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 2,
            'setting_date' => '2026-03-11',
            'duration_hours' => 5.0,
        ]);

        // Catch 2: 300 kg linked to effort
        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort2->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 300.0,
            'fish_count' => 80,
        ]);

        // Catch 3: 200 kg unlinked legacy catch at trip level
        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => null,
            'fish_species_id' => $species->id,
            'weight_kg' => 200.0,
            'fish_count' => 50,
        ]);

        // Landing: 850 kg at TPI Lampulo
        $landing = Landing::create([
            'landing_number' => 'LND-2026-001',
            'fishing_trip_id' => $trip->id,
            'landing_site_id' => $site->id,
            'vessel_id' => $vessel->id,
            'landing_date' => '2026-03-12',
            'total_weight_kg' => 850.0,
            'total_value_rp' => 17000000.0,
        ]);

        LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 850.0,
            'unit_price' => 20000.0,
            'total_price' => 17000000.0,
        ]);

        $service = app(AdvancedStatisticService::class);
        $filters = ['year' => 2026];

        $activity = $service->getActivityKpi($filters);
        $catch = $service->getCatchKpi($filters);

        // 1. Verify Activity KPIs
        $this->assertEquals(1, $activity['total_trips']['value']);
        $this->assertEquals(1, $activity['active_vessels']['value']);
        $this->assertEquals(2, $activity['fishing_efforts']['value']);
        $this->assertEquals(10.0, $activity['total_effort_hours']['value']);

        // 2. Verify Catch KPIs (Physical Total = 500 + 300 + 200 = 1000 kg)
        $this->assertEquals(1000.0, $catch['total_catch_physical']['value']);
        // Effort-linked Catch = 500 + 300 = 800 kg
        $this->assertEquals(800.0, $catch['total_catch_effort_linked']['value']);
        // Landing Production = 850 kg
        $this->assertEquals(850.0, $catch['total_landing']['value']);
        $this->assertEquals(17000000.0, $catch['total_landing_value']['value']);

        // 3. Verify CPUE ratio of sums (800 kg / 10 hours = 80.0 kg/jam)
        $this->assertEquals(80.0, $catch['cpue']['value']);
    }

    public function test_length_frequency_distribution_and_transparent_wpp_null(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '1101', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $site = LandingSite::create([
            'code' => 'SITE-TEST-2',
            'name' => 'TPI Ulee Lheue',
            'site_type' => 'PPI',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'latitude' => 5.56,
            'longitude' => 95.28,
            'is_active' => true,
        ]);

        $species = Species::create([
            'fao_code' => 'YFT',
            'scientific_name' => 'Thunnus albacares',
            'local_name_id' => 'Madidihang / Tuna Sirip Kuning',
            'family' => 'Scombridae',
            'is_active' => true,
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-2026-001',
            'landing_site_id' => $site->id,
            'sample_date' => '2026-04-15',
            'sample_type' => 'Biological',
        ]);

        // 3 biological measurements: 32.5 cm, 34.0 cm, 37.8 cm
        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $species->id,
            'specimen_number' => 1,
            'fork_length_cm' => 32.5,
            'total_weight_g' => 650,
        ]);
        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $species->id,
            'specimen_number' => 2,
            'fork_length_cm' => 34.0,
            'total_weight_g' => 720,
        ]);
        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $species->id,
            'specimen_number' => 3,
            'fork_length_cm' => 37.8,
            'total_weight_g' => 980,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Ulee Lheue',
            'registration_number' => 'REG-102',
            'vessel_type' => 'Motor',
            'gross_tonnage' => 15,
            'homeport_site_id' => $site->id,
            'is_active' => true,
        ]);

        // Trip with WPP NULL
        $unmappedTrip = FishingTrip::create([
            'trip_number' => 'TRIP-UNMAPPED-1',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'landing_site_id' => $site->id,
            'departure_date' => '2026-04-10',
            'return_date' => '2026-04-11',
            'wppnri_id' => null, // WPP NULL
        ]);

        FishCatch::create([
            'fishing_trip_id' => $unmappedTrip->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 450.0,
        ]);

        $service = app(AdvancedStatisticService::class);
        $lengthFreq = $service->getLengthFrequency(['year' => 2026]);
        $wppChart = $service->getCatchByWpp(['year' => 2026]);

        // Verify Length Frequency
        $this->assertTrue($lengthFreq['has_data']);
        $this->assertEquals(3, $lengthFreq['valid_count']);
        $this->assertEquals(32.5, $lengthFreq['min_length']);
        $this->assertEquals(37.8, $lengthFreq['max_length']);

        // Verify WPP Transparency: unmapped trip is labeled "Tidak Terpetakan"
        $this->assertTrue($wppChart['has_data']);
        $unmappedItem = collect($wppChart['items'])->firstWhere('wpp_name', 'Tidak Terpetakan');
        $this->assertNotNull($unmappedItem);
        $this->assertEquals(450.0, $unmappedItem['catch_kg']);
    }

    public function test_multi_dimensional_cross_analysis_and_empty_filter(): void
    {
        $service = app(AdvancedStatisticService::class);

        // Empty filter test (Year 2099)
        $emptyFilters = ['year' => 2099];
        $emptyKpi = $service->getCatchKpi($emptyFilters);
        $emptyTrend = $service->getProductionTrend($emptyFilters);
        $emptyMulti = $service->getMultiDimensionalAnalysis($emptyFilters);

        $this->assertFalse($emptyKpi['total_catch_physical']['has_data']);
        $this->assertEquals(0, $emptyKpi['total_catch_physical']['value']);
        $this->assertFalse($emptyTrend['has_data']);
        $this->assertFalse($emptyMulti['has_data']);
    }

    public function test_unauthorized_user_without_permission_cannot_access_statistics(): void
    {
        $user = User::factory()->create(); // user without 'access.statistics'

        $response = $this->actingAs($user)->get('/analysis/statistics');
        $response->assertForbidden();
    }

    public function test_combined_filters_and_malicious_input_sanitization(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('access.statistics');

        // Test combined filters
        $response = $this->actingAs($user)->get('/analysis/statistics?year=2026&month=3&wppnri_id=1&gear_id=1&species_id=1');
        $response->assertOk();

        // Test malicious parameter (SQL injection attempt / XSS)
        $maliciousResponse = $this->actingAs($user)->get('/analysis/statistics?year=2026%27+OR+1%3D1--&month=%3Cscript%3Ealert(1)%3C%2Fscript%3E');
        $maliciousResponse->assertOk();
    }
}
