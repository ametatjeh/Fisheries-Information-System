<?php

namespace Tests\Feature;

use App\Models\BiologicalMeasurement;
use App\Models\CatchEstimation;
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
use App\Models\SamplingPlan;
use App\Models\Species;
use App\Models\User;
use App\Models\ValidationLog;
use App\Models\Vessel;
use App\Models\Wppnri;
use App\Services\AdvancedStatisticService;
use App\Services\CatchEstimationService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STAGE 20.2–20.8 — Fisheries Statistics Engine Master Test
 *
 * Tests the complete statistics pipeline:
 * 20.2 Fishing Effort
 * 20.3 Catch & Production
 * 20.4 CPUE
 * 20.5 Sampling
 * 20.6 Estimation
 * 20.7 Validation
 * 20.8 Statistics API
 */
class FisheriesStatisticsEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $authorizedUser;

    protected User $unauthorizedUser;

    protected Province $province;

    protected Regency $regency;

    protected LandingSite $landingSite;

    protected Vessel $vessel;

    protected FishingGear $gear;

    protected FishingGear $gear2;

    protected Species $species;

    protected Species $species2;

    protected Wppnri $wpp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->province = Province::firstOrCreate(['code' => '11'], ['name' => 'Aceh']);
        $this->regency = Regency::firstOrCreate(
            ['code' => '1101'],
            ['province_id' => $this->province->id, 'name' => 'Aceh Barat', 'type' => 'kabupaten']
        );
        $this->landingSite = LandingSite::firstOrCreate(
            ['code' => 'SITE-ENGINE-01'],
            [
                'name' => 'TPI Lampulo',
                'site_type' => 'PPS',
                'province_id' => $this->province->id,
                'regency_id' => $this->regency->id,
                'latitude' => 5.582,
                'longitude' => 95.319,
                'is_active' => true,
            ]
        );
        $this->vessel = Vessel::firstOrCreate(
            ['registration_number' => 'REG-ENGINE-001'],
            [
                'name' => 'KM Statistik Aceh',
                'vessel_type' => 'Motor',
                'gross_tonnage' => 25,
                'homeport_site_id' => $this->landingSite->id,
                'is_active' => true,
            ]
        );
        $this->gear = FishingGear::firstOrCreate(
            ['code' => 'PS-ENGINE'],
            [
                'name' => 'Pukat Cincin Pelagis',
                'name_id' => 'Pukat Cincin',
                'category' => 'Surrounding Nets',
                'is_active' => true,
            ]
        );
        $this->gear2 = FishingGear::firstOrCreate(
            ['code' => 'GL-ENGINE'],
            [
                'name' => 'Gill Net',
                'name_id' => 'Jaring Insang',
                'category' => 'Gill Nets',
                'is_active' => true,
            ]
        );
        $this->species = Species::firstOrCreate(
            ['fao_code' => 'SKJ-E'],
            [
                'scientific_name' => 'Katsuwonus pelamis',
                'local_name_id' => 'Cakalang',
                'indonesian_name' => 'Cakalang',
                'family' => 'Scombridae',
                'is_active' => true,
            ]
        );
        $this->species2 = Species::firstOrCreate(
            ['fao_code' => 'YFT-E'],
            [
                'scientific_name' => 'Thunnus albacares',
                'local_name_id' => 'Madidihang',
                'indonesian_name' => 'Tuna Sirip Kuning',
                'family' => 'Scombridae',
                'is_active' => true,
            ]
        );
        $this->wpp = Wppnri::firstOrCreate(
            ['code' => '572'],
            [
                'name' => 'Samudera Hindia Barat Sumatera',
                'description' => 'WPP-NRI 572',
                'is_active' => true,
            ]
        );

        $this->authorizedUser = User::factory()->create();
        $this->authorizedUser->givePermissionTo('access.statistics');

        $this->unauthorizedUser = User::factory()->create();
    }

    /**
     * Creates a complete fishing data chain: Trip → Effort → Catch → Landing.
     *
     * @return array{trip: FishingTrip, effort: FishingEffort, catch: FishCatch, landing: Landing, landingItem: LandingItem}
     */
    protected function createFishingDataChain(
        float $effortHours = 5.0,
        float $catchKg = 500.0,
        float $landingKg = 480.0,
        ?FishingGear $gear = null,
        ?Species $species = null,
        string $departureDate = '2026-09-10',
        string $landingDate = '2026-09-12',
    ): array {
        $gear ??= $this->gear;
        $species ??= $this->species;
        $pricePerKg = 20000.0;

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-ENG-'.fake()->unique()->numerify('###'),
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->landingSite->id,
            'landing_site_id' => $this->landingSite->id,
            'departure_date' => Carbon::parse($departureDate),
            'return_date' => Carbon::parse($landingDate),
            'crew_count' => 6,
            'primary_gear_id' => $gear->id,
            'wppnri_id' => $this->wpp->id,
            'validation_status' => 'validated',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 1,
            'setting_date' => Carbon::parse($departureDate),
            'duration_hours' => $effortHours,
            'setting_count' => 1,
        ]);

        $catch = FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $species->id,
            'weight_kg' => $catchKg,
            'catch_status' => 'target',
        ]);

        $landing = Landing::create([
            'landing_number' => 'LND-ENG-'.fake()->unique()->numerify('###'),
            'fishing_trip_id' => $trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => Carbon::parse($landingDate),
            'total_weight_kg' => $landingKg,
            'total_value_rp' => $landingKg * $pricePerKg,
        ]);

        $landingItem = LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $species->id,
            'weight_kg' => $landingKg,
            'price_per_kg' => $pricePerKg,
            'total_price' => $landingKg * $pricePerKg,
        ]);

        return compact('trip', 'effort', 'catch', 'landing', 'landingItem');
    }

    // =========================================================================
    // 20.2 — FISHING EFFORT
    // =========================================================================

    public function test_fishing_effort_kpi_with_no_data_returns_zeros(): void
    {
        $service = app(AdvancedStatisticService::class);
        $kpi = $service->getActivityKpi(['year' => 2099]);

        $this->assertEquals(0, $kpi['total_trips']['value']);
        $this->assertEquals(0, $kpi['active_vessels']['value']);
        $this->assertEquals(0, $kpi['fishing_efforts']['value']);
        $this->assertEquals(0.0, $kpi['total_effort_hours']['value']);
        $this->assertFalse($kpi['total_trips']['has_data']);
    }

    public function test_fishing_effort_kpi_with_gear_filter(): void
    {
        // Chain with gear1 (5 hours)
        $this->createFishingDataChain(effortHours: 5.0, gear: $this->gear);
        // Chain with gear2 (8 hours)
        $this->createFishingDataChain(effortHours: 8.0, gear: $this->gear2);

        $service = app(AdvancedStatisticService::class);

        // Filter by gear1 only
        $kpi = $service->getActivityKpi(['year' => 2026, 'gear_id' => $this->gear->id]);
        $this->assertEquals(1, $kpi['fishing_efforts']['value']);
        $this->assertEquals(5.0, $kpi['total_effort_hours']['value']);

        // Filter by gear2 only
        $kpi2 = $service->getActivityKpi(['year' => 2026, 'gear_id' => $this->gear2->id]);
        $this->assertEquals(1, $kpi2['fishing_efforts']['value']);
        $this->assertEquals(8.0, $kpi2['total_effort_hours']['value']);
    }

    public function test_fishing_effort_chart_monthly_distribution(): void
    {
        $this->createFishingDataChain(departureDate: '2026-03-10', landingDate: '2026-03-12');
        $this->createFishingDataChain(departureDate: '2026-03-15', landingDate: '2026-03-17');
        $this->createFishingDataChain(departureDate: '2026-06-05', landingDate: '2026-06-07');

        $service = app(AdvancedStatisticService::class);
        $chart = $service->getFishingEffortChart(['year' => 2026]);

        $this->assertTrue($chart['has_data']);
        $this->assertArrayHasKey('labels', $chart);
        $this->assertArrayHasKey('hours_data', $chart);
        $this->assertArrayHasKey('settings_data', $chart);
    }

    // =========================================================================
    // 20.3 — CATCH & PRODUCTION
    // =========================================================================

    public function test_catch_and_landing_are_distinct_data_sources(): void
    {
        // Catch = 500 kg, Landing = 480 kg (tare difference)
        $this->createFishingDataChain(catchKg: 500.0, landingKg: 480.0);

        $service = app(AdvancedStatisticService::class);
        $kpi = $service->getCatchKpi(['year' => 2026, 'month' => 9]);

        // Observed Catch ≠ Landing Production
        $this->assertEquals(500.0, $kpi['total_catch_physical']['value']);
        $this->assertEquals(480.0, $kpi['total_landing']['value']);
        $this->assertNotEquals(
            $kpi['total_catch_physical']['value'],
            $kpi['total_landing']['value'],
            'Catch and Landing must be distinct data sources'
        );
    }

    public function test_production_trend_returns_monthly_series(): void
    {
        $this->createFishingDataChain(departureDate: '2026-03-10', landingDate: '2026-03-12');
        $this->createFishingDataChain(departureDate: '2026-06-10', landingDate: '2026-06-12');

        $service = app(AdvancedStatisticService::class);
        $trend = $service->getProductionTrend(['year' => 2026]);

        $this->assertTrue($trend['has_data']);
        $this->assertArrayHasKey('labels', $trend);
        $this->assertArrayHasKey('datasets', $trend);
        // Should have monthly labels
        $this->assertNotEmpty($trend['labels']);
    }

    public function test_species_breakdown_respects_filter(): void
    {
        $this->createFishingDataChain(catchKg: 300.0, species: $this->species);
        $this->createFishingDataChain(catchKg: 200.0, species: $this->species2);

        $service = app(AdvancedStatisticService::class);

        // Unfiltered: should see both species
        $allSpecies = $service->getCatchBySpecies(['year' => 2026]);
        $this->assertTrue($allSpecies['has_data']);
        $this->assertGreaterThanOrEqual(2, count($allSpecies['items']));

        // Filtered by species1
        $filtered = $service->getCatchKpi(['year' => 2026, 'species_id' => $this->species->id]);
        $this->assertEquals(300.0, $filtered['total_catch_physical']['value']);
    }

    // =========================================================================
    // 20.4 — CPUE
    // =========================================================================

    public function test_cpue_formula_catch_divided_by_effort_hours(): void
    {
        // 400 kg / 8 hours = 50.0 kg/jam
        $this->createFishingDataChain(catchKg: 400.0, effortHours: 8.0);

        $service = app(AdvancedStatisticService::class);
        $kpi = $service->getCatchKpi(['year' => 2026, 'month' => 9]);

        $this->assertEquals(50.0, $kpi['cpue']['value']);
        $this->assertEquals('kg/jam', $kpi['cpue']['unit']);
    }

    public function test_cpue_zero_effort_hours_returns_zero(): void
    {
        $service = app(AdvancedStatisticService::class);
        $kpi = $service->getCatchKpi(['year' => 2099]);

        $this->assertEquals(0, $kpi['cpue']['value']);
    }

    public function test_cpue_trend_monthly_series(): void
    {
        $this->createFishingDataChain(
            catchKg: 300.0,
            effortHours: 6.0,
            departureDate: '2026-03-10',
            landingDate: '2026-03-12'
        );
        $this->createFishingDataChain(
            catchKg: 500.0,
            effortHours: 10.0,
            departureDate: '2026-06-10',
            landingDate: '2026-06-12'
        );

        $service = app(AdvancedStatisticService::class);
        $cpueTrend = $service->getCpueTrend(['year' => 2026]);

        $this->assertTrue($cpueTrend['has_data']);
        $this->assertArrayHasKey('labels', $cpueTrend);
        $this->assertArrayHasKey('cpue_data', $cpueTrend);
        $this->assertArrayHasKey('catch_data', $cpueTrend);
        $this->assertArrayHasKey('effort_data', $cpueTrend);
    }

    // =========================================================================
    // 20.5 — SAMPLING
    // =========================================================================

    public function test_sampling_plan_creates_with_specimens(): void
    {
        $plan = SamplingPlan::create([
            'code' => 'SMP-PLAN-ENG-001',
            'title' => 'Sampling Pelagis Sep 2026',
            'landing_site_id' => $this->landingSite->id,
            'target_species_id' => $this->species->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'target_sample_size' => 10,
            'sampling_method' => 'stratified',
            'status' => 'active',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-ENG-001',
            'sampling_plan_id' => $plan->id,
            'landing_site_id' => $this->landingSite->id,
            'sample_date' => '2026-09-08',
            'total_specimens' => 3,
            'total_weight_kg' => 5.25,
        ]);

        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $this->species->id,
            'specimen_number' => 1,
            'fork_length_cm' => 35.5,
            'total_length_cm' => 38.0,
            'weight_gram' => 1750.0,
            'sex' => 'female',
            'gonad_maturity_stage' => 3,
        ]);

        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $this->species->id,
            'specimen_number' => 2,
            'fork_length_cm' => 32.0,
            'total_length_cm' => 34.5,
            'weight_gram' => 1400.0,
            'sex' => 'male',
            'gonad_maturity_stage' => 2,
        ]);

        BiologicalMeasurement::create([
            'sample_id' => $sample->id,
            'fish_species_id' => $this->species->id,
            'specimen_number' => 3,
            'fork_length_cm' => 40.2,
            'total_length_cm' => 43.0,
            'weight_gram' => 2100.0,
            'sex' => 'female',
            'gonad_maturity_stage' => 4,
        ]);

        $this->assertCount(3, $sample->biologicalMeasurements);
        $this->assertEquals('stratified', $plan->sampling_method);
        $this->assertEquals($this->landingSite->id, $sample->landing_site_id);
    }

    public function test_length_frequency_distribution_aggregation(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-FREQ-001',
            'landing_site_id' => $this->landingSite->id,
            'sample_date' => '2026-09-15',
        ]);

        foreach ([28.0, 30.5, 32.0, 34.5, 36.0] as $i => $length) {
            BiologicalMeasurement::create([
                'sample_id' => $sample->id,
                'fish_species_id' => $this->species->id,
                'specimen_number' => $i + 1,
                'fork_length_cm' => $length,
                'weight_gram' => $length * 50,
            ]);
        }

        $service = app(AdvancedStatisticService::class);
        $lengthFreq = $service->getLengthFrequency(['year' => 2026]);

        $this->assertTrue($lengthFreq['has_data']);
        $this->assertEquals(5, $lengthFreq['valid_count']);
        $this->assertEquals(28.0, $lengthFreq['min_length']);
        $this->assertEquals(36.0, $lengthFreq['max_length']);
    }

    // =========================================================================
    // 20.6 — ESTIMATION
    // =========================================================================

    public function test_estimation_raising_factor_bounds(): void
    {
        $service = app(CatchEstimationService::class);

        // Valid raising factor (within 1.0–15.0)
        $estimation = $service->storeEstimation([
            'regency_id' => $this->regency->id,
            'landing_site_id' => $this->landingSite->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'year' => 2026,
            'month' => 9,
            'sampled_catch_kg' => 500.0,
            'raising_factor' => 2.5,
            'estimated_catch_kg' => 1250.0,
            'estimated_effort_trips' => 10,
        ]);

        $this->assertInstanceOf(CatchEstimation::class, $estimation);
        $this->assertEquals(2.5, $estimation->raising_factor);

        // Raising factor < 1.0 should throw
        $this->expectException(\InvalidArgumentException::class);
        $service->storeEstimation([
            'regency_id' => $this->regency->id,
            'year' => 2026,
            'month' => 9,
            'sampled_catch_kg' => 100.0,
            'raising_factor' => 0.5,
            'estimated_catch_kg' => 50.0,
        ]);
    }

    public function test_estimation_reconciliation_with_observed_catch(): void
    {
        // Create observed data: 2 trips, 600 kg total catch
        $this->createFishingDataChain(catchKg: 300.0, effortHours: 5.0);
        $this->createFishingDataChain(catchKg: 300.0, effortHours: 5.0);

        $estService = app(CatchEstimationService::class);
        $estimations = $estService->calculateEstimation(2026, 9);

        $this->assertNotEmpty($estimations);

        // Each estimation should have raising_factor >= 1.0
        foreach ($estimations as $est) {
            $this->assertGreaterThanOrEqual(1.0, $est['raising_factor']);
            $this->assertGreaterThanOrEqual($est['sampled_catch_kg'], $est['estimated_catch_kg']);
        }
    }

    // =========================================================================
    // 20.7 — VALIDATION
    // =========================================================================

    public function test_validation_status_transition_workflow(): void
    {
        $data = $this->createFishingDataChain();
        $trip = $data['trip'];

        // Start as validated, transition to submitted
        $this->assertEquals('validated', $trip->validation_status);

        $validatorUser = User::factory()->create();
        $validatorUser->givePermissionTo('access.validation');

        $response = $this->actingAs($validatorUser)->patch(
            route('analysis.validation.update-status', $trip),
            [
                'validation_status' => 'rejected',
                'notes' => 'Data crew tidak lengkap',
                'rejection_reason' => 'Crew count missing',
            ]
        );

        $response->assertRedirect(route('analysis.validation.index'));
        $trip->refresh();
        $this->assertEquals('rejected', $trip->validation_status);
    }

    public function test_validation_log_audit_trail(): void
    {
        $data = $this->createFishingDataChain();
        $trip = $data['trip'];

        $validatorUser = User::factory()->create();
        $validatorUser->givePermissionTo('access.validation');

        // Perform status change: validated → draft
        $this->actingAs($validatorUser)->patch(
            route('analysis.validation.update-status', $trip),
            ['validation_status' => 'draft', 'notes' => 'Perlu revisi']
        );

        // Check audit log was created
        $log = ValidationLog::where('fishing_trip_id', $trip->id)->latest()->first();
        $this->assertNotNull($log);
        $this->assertEquals('validated', $log->from_status);
        $this->assertEquals('draft', $log->to_status);
        $this->assertEquals($validatorUser->id, $log->validator_id);
    }

    // =========================================================================
    // 20.8 — STATISTICS API
    // =========================================================================

    public function test_statistics_api_requires_auth(): void
    {
        $this->getJson('/api/statistics/kpi')->assertUnauthorized();
        $this->getJson('/api/statistics/production')->assertUnauthorized();
        $this->getJson('/api/statistics/trend')->assertUnauthorized();
        $this->getJson('/api/statistics/cpue')->assertUnauthorized();
    }

    public function test_statistics_api_requires_permission(): void
    {
        $response = $this->actingAs($this->unauthorizedUser)->get('/api/statistics/kpi');
        $response->assertForbidden();
    }

    public function test_statistics_api_kpi_returns_json(): void
    {
        $this->createFishingDataChain(catchKg: 300.0, effortHours: 6.0);

        $response = $this->actingAs($this->authorizedUser)->getJson('/api/statistics/kpi?year=2026&month=9');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'period',
            'data' => [
                'activity' => ['total_trips', 'active_vessels', 'fishing_efforts', 'total_effort_hours'],
                'catch' => ['total_catch_physical', 'total_landing', 'cpue'],
                'operational',
            ],
        ]);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('data.activity.total_trips.value', 1);
        $this->assertEquals(300.0, $response->json('data.catch.total_catch_physical.value'));
    }

    public function test_statistics_api_production_returns_json(): void
    {
        $this->createFishingDataChain(landingKg: 400.0);

        $response = $this->actingAs($this->authorizedUser)->getJson('/api/statistics/production?year=2026');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'data' => [
                'metrics' => [
                    'total_landing_kg',
                    'total_landing_value_rp',
                    'total_observed_catch_kg',
                ],
            ],
        ]);
        $response->assertJsonPath('status', 'ok');
    }

    public function test_statistics_api_trend_returns_json(): void
    {
        $this->createFishingDataChain(departureDate: '2026-03-10', landingDate: '2026-03-12');

        $response = $this->actingAs($this->authorizedUser)->getJson('/api/statistics/trend?year=2026');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'period',
            'data' => ['has_data', 'labels', 'datasets'],
        ]);
    }

    public function test_statistics_api_cpue_returns_json(): void
    {
        $this->createFishingDataChain(catchKg: 200.0, effortHours: 4.0);

        $response = $this->actingAs($this->authorizedUser)->getJson('/api/statistics/cpue?year=2026');

        $response->assertOk();
        $response->assertJsonStructure([
            'status',
            'period',
            'data' => ['has_data'],
        ]);
    }

    public function test_statistics_api_kpi_with_filters(): void
    {
        $this->createFishingDataChain(catchKg: 250.0, gear: $this->gear);
        $this->createFishingDataChain(catchKg: 150.0, gear: $this->gear2);

        $response = $this->actingAs($this->authorizedUser)
            ->getJson("/api/statistics/kpi?year=2026&gear_id={$this->gear->id}");

        $response->assertOk();
        $this->assertEquals(250.0, $response->json('data.catch.total_catch_physical.value'));
    }
}
