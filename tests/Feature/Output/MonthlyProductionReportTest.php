<?php

namespace Tests\Feature\Output;

use App\Models\CatchEstimation;
use App\Models\FishCatch;
use App\Models\Fisherman;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use App\Models\MonthlyProductionStatistic;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Models\VesselType;
use App\Models\Wppnri;
use App\Services\MonthlyProductionService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonthlyProductionReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $viewerUser;

    protected User $unauthorizedUser;

    protected Wppnri $wpp;

    protected LandingSite $site;

    protected Species $species;

    protected FishingGear $gear;

    protected Vessel $vessel;

    protected Fisherman $captain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');

        $this->unauthorizedUser = User::factory()->create(); // no role / permissions

        // Seed basic operational entities
        $province = Province::first() ?? Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::first() ?? Regency::create(['province_id' => $province->id, 'name' => 'Banda Aceh', 'code' => '1171']);
        $this->wpp = Wppnri::first() ?? Wppnri::create(['code' => '571', 'name' => 'WPPNRI 571']);
        $this->site = LandingSite::first() ?? LandingSite::create([
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'name' => 'PPS Lampulo',
            'code' => 'LMP',
            'is_active' => true,
        ]);
        $this->species = Species::first() ?? Species::create([
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'indonesian_name' => 'Cakalang',
            'fao_code' => 'SKJ',
        ]);
        $this->gear = FishingGear::first() ?? FishingGear::create([
            'name' => 'Purse Seine',
            'code' => 'PS',
            'category' => 'jaring_lingkar',
            'gear_type' => 'Pukat Cincin',
            'is_active' => true,
        ]);

        $vesselType = VesselType::first() ?? VesselType::create(['name' => 'Kapal Motor', 'code' => 'KM']);
        $this->captain = Fisherman::first() ?? Fisherman::create([
            'nik' => '1101011010850001',
            'name' => 'Panglima Laot',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $this->vessel = Vessel::first() ?? Vessel::create([
            'name' => 'KM Bahari 01',
            'registration_number' => 'REG-001',
            'vessel_type_id' => $vesselType->id,
            'owner_id' => $this->captain->id,
            'is_active' => true,
        ]);

        // Create Fishing Trip & Effort with Catch (Observed Catch)
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-2026-TEST',
            'vessel_id' => $this->vessel->id,
            'captain_id' => $this->captain->id,
            'departure_site_id' => $this->site->id,
            'landing_site_id' => $this->site->id,
            'primary_gear_id' => $this->gear->id,
            'wppnri_id' => $this->wpp->id,
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'validation_status' => 'validated',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_number' => 1,
            'setting_date' => '2026-03-02 06:00:00',
            'hauling_date' => '2026-03-02 12:00:00',
            'duration_hours' => 6,
            'setting_count' => 1,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 1500.00,
            'fish_count' => 300,
            'catch_status' => 'target',
        ]);

        // Create Landing & Landing Items (Landing Production)
        $landing = Landing::create([
            'landing_number' => 'LND-2026-TEST',
            'fishing_trip_id' => $trip->id,
            'landing_site_id' => $this->site->id,
            'landing_date' => '2026-03-05',
            'total_weight_kg' => 1400.00,
            'total_value_rp' => 35000000.00,
            'buyer_count' => 3,
        ]);

        LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 1400.00,
            'price_per_kg' => 25000.00,
            'total_price' => 35000000.00,
        ]);

        // Create Catch Estimation (Estimated Production)
        CatchEstimation::create([
            'regency_id' => $regency->id,
            'landing_site_id' => $this->site->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'year' => 2026,
            'month' => 3,
            'sampled_catch_kg' => 1400.00,
            'raising_factor' => 1.25,
            'estimated_catch_kg' => 1750.00,
            'estimated_effort_trips' => 1,
            'cpue' => 1400.00,
            'variance' => 0.05,
            'notes' => '[STATUS:validated] Test estimation',
        ]);

        // Create Monthly Production Statistic record (Official Statistic)
        MonthlyProductionStatistic::create([
            'regency_id' => $regency->id,
            'landing_site_id' => $this->site->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'year' => 2026,
            'month' => 3,
            'total_volume_kg' => 1400.00,
            'total_value_rp' => 35000000.00,
            'average_price_per_kg' => 25000.00,
            'total_active_vessels' => 1,
            'total_trips' => 1,
        ]);
    }

    public function test_service_aggregates_monthly_production_safely(): void
    {
        $service = app(MonthlyProductionService::class);

        $summary = $service->getProductionSummary(['year' => 2026, 'month' => 3]);

        $this->assertEquals(1400.00, $summary['metrics']['total_landing_kg']);
        $this->assertEquals(35000000.00, $summary['metrics']['total_landing_value_rp']);
        $this->assertEquals(1500.00, $summary['metrics']['total_observed_catch_kg']);
        $this->assertEquals(1750.00, $summary['metrics']['total_estimated_production_kg']);
        $this->assertEquals(1400.00, $summary['metrics']['total_monthly_stat_kg']);

        // Check WPP breakdown handles WPP name properly
        $this->assertNotEmpty($summary['by_wpp']);
        $this->assertEquals('WPPNRI 571', $summary['by_wpp'][0]->wpp_name);
        $this->assertEquals(1400.00, (float) $summary['by_wpp'][0]->volume_kg);
    }

    public function test_service_handles_unmapped_wpp_gracefully(): void
    {
        // Add trip without WPP
        $tripNoWpp = FishingTrip::create([
            'trip_number' => 'TRIP-NOWPP',
            'vessel_id' => $this->vessel->id,
            'captain_id' => $this->captain->id,
            'departure_site_id' => $this->site->id,
            'landing_site_id' => $this->site->id,
            'primary_gear_id' => $this->gear->id,
            'wppnri_id' => null,
            'departure_date' => '2026-03-01',
            'return_date' => '2026-03-05',
            'validation_status' => 'validated',
        ]);

        $landingNoWpp = Landing::create([
            'landing_number' => 'LND-NOWPP',
            'fishing_trip_id' => $tripNoWpp->id,
            'landing_site_id' => $this->site->id,
            'landing_date' => '2026-03-06',
            'total_weight_kg' => 500.00,
            'total_value_rp' => 10000000.00,
            'buyer_count' => 1,
        ]);

        LandingItem::create([
            'landing_id' => $landingNoWpp->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 500.00,
            'price_per_kg' => 20000.00,
            'total_price' => 10000000.00,
        ]);

        $service = app(MonthlyProductionService::class);
        $summary = $service->getProductionSummary(['year' => 2026, 'month' => 3]);

        $wppNames = collect($summary['by_wpp'])->pluck('wpp_name')->toArray();
        $this->assertContains('Tidak Terpetakan', $wppNames);
        $this->assertContains('WPPNRI 571', $wppNames);
    }

    public function test_unauthorized_user_cannot_access_reports(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get('/reports?type=monthly');

        $response->assertForbidden();
    }

    public function test_authorized_user_can_view_monthly_production_report(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/reports?type=monthly&year=2026&month=3');

        $response->assertOk();
        $response->assertSee('Rekapitulasi Bulanan');
        $response->assertSee('Wilayah WPP');
        $response->assertSee('PPS Lampulo');
        $response->assertSee('WPPNRI 571');
        $response->assertSee('Cakalang');
        $response->assertSee('Purse Seine');
        $response->assertSee('1.400,0'); // Volume format
    }

    public function test_monthly_report_filters_by_wpp(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/reports?type=monthly&year=2026&month=3&wppnri_id='.$this->wpp->id);

        $response->assertOk();
        $response->assertSee('WPPNRI 571');
    }

    public function test_monthly_production_can_be_exported_to_excel_csv(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/reports/export?type=monthly&year=2026&month=3');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('content-type'));
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('laporan_monthly_', (string) $response->headers->get('content-disposition'));

        // Stream output check
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('WPP', $content);
        $this->assertStringContainsString('Status Validasi', $content);
        $this->assertStringContainsString('PPS Lampulo', $content);
        $this->assertStringContainsString('WPPNRI 571', $content);
        $this->assertStringContainsString('Cakalang', $content);
    }

    public function test_monthly_production_print_preview(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/reports/print?type=monthly&year=2026&month=3&wppnri_id='.$this->wpp->id);

        $response->assertOk();
        $response->assertSee('Mode Pratinjau Dokumen Cetak');
        $response->assertSee('Wilayah WPP');
        $response->assertSee('WPPNRI 571');
        $response->assertSee('PPS Lampulo');
        $response->assertSee('Cakalang');
    }

    public function test_statistics_dashboard_displays_all_four_production_dimensions(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/analysis/statistics?year=2026&month=3');

        $response->assertOk();
        $response->assertSee('Total Catch');
        $response->assertSee('Total Effort');
        $response->assertSee('CPUE (Laju Tangkap)');
        $response->assertSee('Production');
        $response->assertSee('Nilai Omzet');
        $response->assertSee('Estimasi Produksi'); // Card 6
        $response->assertSee('Distribusi Produksi &amp; Tangkapan per Wilayah WPP-NRI', false);
        $response->assertSee('Panduan Metodologi &amp; Definisi Semantik Metrik Perikanan', false);
    }
}
