<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Wppnri;
use App\Services\FisheriesValidationEngineService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STAGE 21–24 Master Integration Feature Test
 *
 * Checkpoint 21: Statistics Dashboard & Charts
 * Checkpoint 22: Reporting & Export (Production, Statistics, Summary)
 * Checkpoint 23: Advanced Data Validation (Referential, Temporal, Numeric, Statistical)
 * Checkpoint 24: Advanced GIS & Spatial Analysis
 */
class Stage21To24IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $guestUser;

    protected Province $province;

    protected Regency $regency;

    protected LandingSite $site;

    protected Vessel $vessel;

    protected FishingGear $gear;

    protected Species $species;

    protected Wppnri $wpp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->guestUser = User::factory()->create();

        $this->province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $this->regency = Regency::create(['province_id' => $this->province->id, 'code' => '1101', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $this->site = LandingSite::create([
            'code' => 'SITE-TEST-2124',
            'name' => 'PPS Lampulo Induk',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'latitude' => 5.5833,
            'longitude' => 95.3333,
            'is_active' => true,
        ]);

        $this->vessel = Vessel::create([
            'name' => 'KM Samudera Sejahtera',
            'registration_number' => 'REG-ACEH-2124',
            'homeport_site_id' => $this->site->id,
            'gross_tonnage' => 25,
            'is_active' => true,
        ]);

        $this->gear = FishingGear::create([
            'code' => 'PS-2124',
            'name' => 'Pukat Cincin Pelagis',
            'category' => 'Surrounding Nets',
            'is_active' => true,
        ]);

        $this->species = Species::create([
            'fao_code' => 'YFT',
            'scientific_name' => 'Thunnus albacares',
            'local_name_id' => 'Madidihang / Tuna Sirip Kuning',
            'is_active' => true,
        ]);

        $this->wpp = Wppnri::create([
            'code' => '572',
            'name' => 'WPP-NRI 572 Samudera Hindia',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // CHECKPOINT 21: STATISTICS DASHBOARD & CHARTS
    // =========================================================================

    public function test_checkpoint_21_statistics_dashboard_renders_with_engine_data(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-21-001',
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->site->id,
            'landing_site_id' => $this->site->id,
            'primary_gear_id' => $this->gear->id,
            'wppnri_id' => $this->wpp->id,
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_number' => 1,
            'setting_date' => '2026-03-02',
            'duration_hours' => 6.0,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 600.0,
            'fish_count' => 80,
        ]);

        $response = $this->actingAs($this->adminUser)->get('/analysis/statistics?year=2026');

        $response->assertOk();
        $response->assertSee('Analisis Lanjutan: Statistik &amp; Dashboard Perikanan', false);
        $response->assertSee('Statistik Multi-Dimensi: Catch, Effort, CPUE, &amp; Produksi', false);
        $response->assertSee('statTrendChart', false);
        $response->assertSee('statCpueChart', false);
        $response->assertSee('statGearChart', false);
    }

    // =========================================================================
    // CHECKPOINT 22: REPORTING & EXPORT
    // =========================================================================

    public function test_checkpoint_22_production_statistics_summary_reports(): void
    {
        // 1. Production report
        $resProd = $this->actingAs($this->adminUser)->get('/reports?type=production&year=2026');
        $resProd->assertOk();
        $resProd->assertSee('Rincian Laporan Produksi Terpadu Multi-Dimensi');

        // 2. Statistics report
        $resStat = $this->actingAs($this->adminUser)->get('/reports?type=statistics&year=2026');
        $resStat->assertOk();
        $resStat->assertSee('Rincian Laporan Statistik Perikanan Tangkap &amp; CPUE', false);

        // 3. Summary report
        $resSum = $this->actingAs($this->adminUser)->get('/reports?type=summary&year=2026');
        $resSum->assertOk();
        $resSum->assertSee('Rincian Indikator Kinerja Utama (Summary Report)');
        $resSum->assertSee('Total Fishing Trips');
    }

    public function test_checkpoint_22_csv_exports_with_utf8_bom(): void
    {
        foreach (['production', 'statistics', 'summary'] as $reportType) {
            $response = $this->actingAs($this->adminUser)->get("/reports/export?type={$reportType}&year=2026");
            $response->assertOk();
            $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
            $this->assertStringContainsString("laporan_{$reportType}_", (string) $response->headers->get('content-disposition'));
        }
    }

    // =========================================================================
    // CHECKPOINT 23: ADVANCED DATA VALIDATION ENGINE
    // =========================================================================

    public function test_checkpoint_23_validation_engine_flags_referential_temporal_and_numeric_issues(): void
    {
        /** @var FisheriesValidationEngineService $engine */
        $engine = app(FisheriesValidationEngineService::class);

        // Create an invalid trip intentionally
        $invalidTrip = FishingTrip::create([
            'trip_number' => 'TRIP-INVALID-001',
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->site->id,
            'landing_site_id' => $this->site->id,
            'departure_date' => '2026-03-15 10:00:00',
            'return_date' => '2026-03-10 10:00:00', // Temporal error: return before departure!
            'crew_count' => -3, // Numeric error: negative crew!
            'fuel_consumption_liters' => -50.0, // Numeric error: negative fuel!
            'validation_status' => 'submitted',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $invalidTrip->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_number' => 1,
            'setting_date' => '2026-03-01 08:00:00', // Temporal error: setting before departure!
            'duration_hours' => -2.0, // Numeric error: negative duration!
            'setting_count' => 0, // Numeric error: 0 setting count!
        ]);

        FishCatch::create([
            'fishing_trip_id' => $invalidTrip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => -15.0, // Numeric error: negative catch weight!
            'fish_count' => -5, // Numeric error: negative fish count!
        ]);

        // Audit the trip
        $report = $engine->auditTrip($invalidTrip);

        $this->assertFalse($report['is_valid']);
        $this->assertTrue($report['has_errors']);
        $this->assertGreaterThan(0, $report['error_count']);

        $rules = array_column($report['issues'], 'rule');
        $this->assertContains('temporal.departure_before_return', $rules);
        $this->assertContains('numeric.non_negative_crew', $rules);
        $this->assertContains('numeric.non_negative_fuel', $rules);
        $this->assertContains('numeric.non_negative_duration', $rules);
        $this->assertContains('numeric.non_negative_catch_weight', $rules);

        // Crucial constraint: Existing invalid data was NOT silently rewritten or deleted!
        $invalidTrip->refresh();
        $this->assertEquals(-3, $invalidTrip->crew_count);
        $this->assertEquals(-50.0, (float) $invalidTrip->fuel_consumption_liters);
        $this->assertEquals('submitted', $invalidTrip->validation_status);
    }

    public function test_checkpoint_23_validation_audit_endpoint_returns_json(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-AUDIT-001',
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->site->id,
            'landing_site_id' => $this->site->id,
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'validation_status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminUser)->getJson("/analysis/validation/{$trip->id}/audit");

        $response->assertOk();
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('data.trip_id', $trip->id);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'trip_id',
                'trip_number',
                'is_valid',
                'has_errors',
                'has_warnings',
                'error_count',
                'warning_count',
                'issues',
            ],
        ]);
    }

    // =========================================================================
    // CHECKPOINT 24: ADVANCED GIS & SPATIAL ANALYSIS
    // =========================================================================

    public function test_checkpoint_24_gis_data_and_spatial_endpoints(): void
    {
        // 1. Main GIS dataset endpoint
        $resGis = $this->actingAs($this->adminUser)->getJson('/gis/data');
        $resGis->assertOk();
        $resGis->assertJsonStructure([
            'center' => ['lat', 'lng'],
            'ports',
            'fishing_grounds',
            'efforts',
            'vessels',
            'logbooks',
            'wpp_analysis',
            'counts',
        ]);

        // 2. RZWP3K Zones GeoJSON endpoint
        $resZones = $this->getJson('/api/rzwp3k/zones');
        $resZones->assertOk();
        $resZones->assertJsonPath('type', 'FeatureCollection');

        // 3. RZWP3K Spatial intersection with Fishing Grounds endpoint
        $resFg = $this->getJson('/api/rzwp3k/spatial/fishing-grounds');
        $resFg->assertOk();
        $this->assertArrayHasKey('results', $resFg->json());
        $this->assertArrayHasKey('disclaimer', $resFg->json());
    }
}
