<?php

namespace Tests\Feature\Analysis;

use App\Models\BiologicalMeasurement;
use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingGround;
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
use App\Models\Vessel;
use App\Models\Wppnri;
use App\Services\AdvancedStatisticService;
use App\Services\CatchEstimationService;
use App\Services\MonthlyProductionService;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class StatisticsFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected User $statUser;

    protected Province $province;

    protected Regency $regency;

    protected LandingSite $landingSite;

    protected Vessel $vessel;

    protected FishingGear $gear;

    protected Species $species;

    protected Wppnri $wpp;

    protected FishingGround $fishingGround;

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
            ['code' => 'SITE-TEST-01'],
            [
                'name' => 'TPI Lampulo Banda Aceh',
                'site_type' => 'PPS',
                'province_id' => $this->province->id,
                'regency_id' => $this->regency->id,
                'latitude' => 5.582,
                'longitude' => 95.319,
                'is_active' => true,
            ]
        );

        $this->vessel = Vessel::firstOrCreate(
            ['registration_number' => 'REG-ACEH-001'],
            [
                'name' => 'KM Samudera Aceh',
                'vessel_type' => 'Motor',
                'gross_tonnage' => 25,
                'homeport_site_id' => $this->landingSite->id,
                'is_active' => true,
            ]
        );

        $this->gear = FishingGear::firstOrCreate(
            ['code' => 'PS-01'],
            [
                'name' => 'Purse Seine Pelagis',
                'name_id' => 'Pukat Cincin',
                'category' => 'Surrounding Nets',
                'is_active' => true,
            ]
        );

        $this->species = Species::firstOrCreate(
            ['fao_code' => 'KAW'],
            [
                'scientific_name' => 'Euthynnus affinis',
                'local_name_id' => 'Tongkol Komo',
                'indonesian_name' => 'Tongkol Komo',
                'english_name' => 'Kawakawa',
                'family' => 'Scombridae',
                'is_active' => true,
            ]
        );

        $this->wpp = Wppnri::firstOrCreate(
            ['code' => '571'],
            [
                'name' => 'Selat Malaka dan Laut Andaman',
                'description' => 'Wilayah Pengelolaan Perikanan 571',
                'is_active' => true,
            ]
        );

        $this->fishingGround = FishingGround::firstOrCreate(
            ['name' => 'Perairan Meulaboh ZEE'],
            [
                'wppnri_id' => $this->wpp->id,
                'regency_id' => $this->regency->id,
                'latitude' => 4.15,
                'longitude' => 96.12,
                'description' => 'Daerah Penangkapan Pelagis',
                'is_active' => true,
            ]
        );

        $this->statUser = User::factory()->create();
        $this->statUser->givePermissionTo('access.statistics');
    }

    /**
     * 20.1.1 & 20.1.2: Relational Chain Audit (Trip -> Effort -> Catch -> Landing)
     */
    public function test_relational_chain_integrity_from_trip_to_catch_and_landing(): void
    {
        // 1. Create Trip
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-2026-001',
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->landingSite->id,
            'landing_site_id' => $this->landingSite->id,
            'departure_date' => Carbon::parse('2026-09-10 06:00:00'),
            'return_date' => Carbon::parse('2026-09-12 18:00:00'),
            'crew_count' => 6,
            'fuel_consumption_liters' => 250.50,
            'ice_consumption_kg' => 500.00,
            'primary_gear_id' => $this->gear->id,
            'wppnri_id' => $this->wpp->id,
            'fishing_ground_id' => $this->fishingGround->id,
            'validation_status' => 'validated',
        ]);

        $this->assertInstanceOf(Vessel::class, $trip->vessel);
        $this->assertInstanceOf(LandingSite::class, $trip->departureSite);
        $this->assertInstanceOf(LandingSite::class, $trip->landingSite);
        $this->assertInstanceOf(FishingGear::class, $trip->primaryGear);
        $this->assertInstanceOf(Wppnri::class, $trip->wppnri);
        $this->assertInstanceOf(FishingGround::class, $trip->fishingGround);

        // 2. Create Effort
        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_number' => 1,
            'setting_date' => Carbon::parse('2026-09-10 10:00:00'),
            'hauling_date' => Carbon::parse('2026-09-10 14:00:00'),
            'duration_hours' => 4.00,
            'setting_count' => 1,
            'net_length_meters' => 300.00,
            'latitude_setting' => 5.6000000,
            'longitude_setting' => 95.3500000,
            'latitude_hauling' => 5.6200000,
            'longitude_hauling' => 95.3800000,
        ]);

        $this->assertInstanceOf(FishingTrip::class, $effort->fishingTrip);
        $this->assertInstanceOf(FishingGear::class, $effort->fishingGear);

        // 3. Create Catch
        $catch = FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 320.50,
            'fish_count' => 150,
            'catch_status' => 'target',
        ]);

        $this->assertInstanceOf(FishingTrip::class, $catch->fishingTrip);
        $this->assertInstanceOf(FishingEffort::class, $catch->fishingEffort);
        $this->assertInstanceOf(Species::class, $catch->species);

        // 4. Create Landing
        $landing = Landing::create([
            'landing_number' => 'LND-2026-001',
            'fishing_trip_id' => $trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => Carbon::parse('2026-09-12 19:00:00'),
            'total_weight_kg' => 320.50,
            'total_value_rp' => 6410000.00,
            'buyer_count' => 3,
        ]);

        $landingItem = LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 320.50,
            'fish_count' => 150,
            'price_per_kg' => 20000.00,
            'total_price' => 6410000.00,
            'quality_grade' => 'A',
        ]);

        $this->assertInstanceOf(Landing::class, $landingItem->landing);
        $this->assertInstanceOf(Species::class, $landingItem->species);
        $this->assertCount(1, $trip->catches);
        $this->assertCount(1, $trip->landings);
        $this->assertCount(1, $trip->fishingEfforts);
    }

    /**
     * 20.1.3: Period & Measurement Unit Consistency
     */
    public function test_period_and_measurement_unit_consistency(): void
    {
        $service = app(AdvancedStatisticService::class);

        // Test filter normalization
        $normalized = $service->normalizeFilters([
            'year' => '2026',
            'month' => '9',
            'gear_id' => (string) $this->gear->id,
        ]);

        $this->assertEquals(2026, $normalized['year']);
        $this->assertEquals(9, $normalized['month']);
        $this->assertEquals('September 2026', $normalized['period_label']);

        // Date range normalization
        $dateNormalized = $service->normalizeFilters([
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-15',
        ]);
        $this->assertEquals('2026-09-01', $dateNormalized['start_date']);
        $this->assertEquals('2026-09-15', $dateNormalized['end_date']);
        $this->assertStringContainsString('01 Sep 2026 - 15 Sep 2026', $dateNormalized['period_label']);
    }

    /**
     * 20.1.4: Lineage & Distinct Data Aggregation (Observed Catch vs Landing vs Estimation)
     */
    public function test_statistical_lineage_and_distinct_aggregations(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-LIN-001',
            'vessel_id' => $this->vessel->id,
            'departure_site_id' => $this->landingSite->id,
            'landing_site_id' => $this->landingSite->id,
            'departure_date' => Carbon::parse('2026-09-05 08:00:00'),
            'return_date' => Carbon::parse('2026-09-06 18:00:00'),
            'primary_gear_id' => $this->gear->id,
            'wppnri_id' => $this->wpp->id,
            'validation_status' => 'validated',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_number' => 1,
            'setting_date' => Carbon::parse('2026-09-05 10:00:00'),
            'duration_hours' => 5.0,
            'setting_count' => 1,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 500.00,
            'catch_status' => 'target',
        ]);

        $landing = Landing::create([
            'landing_number' => 'LND-LIN-001',
            'fishing_trip_id' => $trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => Carbon::parse('2026-09-06 20:00:00'),
            'total_weight_kg' => 480.00, // Small tare/sorting difference in landing
            'total_value_rp' => 9600000.00,
        ]);

        LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 480.00,
            'price_per_kg' => 20000.00,
            'total_price' => 9600000.00,
        ]);

        $statService = app(AdvancedStatisticService::class);
        $filters = ['year' => 2026, 'month' => 9];

        $catchKpi = $statService->getCatchKpi($filters);
        $activityKpi = $statService->getActivityKpi($filters);

        // 1. Observed Catch
        $this->assertEquals(500.00, $catchKpi['total_catch_physical']['value']);
        $this->assertEquals(0.50, $catchKpi['total_catch_physical']['ton']);

        // 2. Landing Production
        $this->assertEquals(480.00, $catchKpi['total_landing']['value']);
        $this->assertEquals(9600000.00, $catchKpi['total_landing_value']['value']);

        // 3. Activity
        $this->assertEquals(1, $activityKpi['total_trips']['value']);
        $this->assertEquals(1, $activityKpi['active_vessels']['value']);
        $this->assertEquals(1, $activityKpi['fishing_efforts']['value']);
        $this->assertEquals(5.0, $activityKpi['total_effort_hours']['value']);

        // 4. CPUE Foundation (500kg / 5 hours = 100 kg/hour)
        $this->assertEquals(100.00, $catchKpi['cpue']['value']);
        $this->assertEquals('kg/jam', $catchKpi['cpue']['unit']);
    }

    /**
     * 20.1.5: Sampling, Stratified Estimation & Monthly Production Sync
     */
    public function test_sampling_estimation_and_monthly_production_integration(): void
    {
        // 1. Create Sampling Plan & Sample
        $plan = SamplingPlan::create([
            'code' => 'SMP-PLAN-2026-001',
            'title' => 'Sampling Pelagis Banda Aceh Sep 2026',
            'landing_site_id' => $this->landingSite->id,
            'target_species_id' => $this->species->id,
            'start_date' => Carbon::parse('2026-09-01'),
            'end_date' => Carbon::parse('2026-09-30'),
            'target_sample_size' => 10,
            'sampling_method' => 'stratified',
            'status' => 'active',
        ]);

        $sample = Sample::create([
            'sample_code' => 'SMP-2026-001',
            'sampling_plan_id' => $plan->id,
            'landing_site_id' => $this->landingSite->id,
            'sample_date' => Carbon::parse('2026-09-08'),
            'total_specimens' => 2,
            'total_weight_kg' => 3.50,
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

        $this->assertCount(1, $sample->biologicalMeasurements);

        // 2. Test CatchEstimationService
        $estService = app(CatchEstimationService::class);
        $estimations = $estService->calculateEstimation(2026, 9);
        $this->assertInstanceOf(Collection::class, $estimations);

        // 3. Test MonthlyProductionService sync
        $prodService = app(MonthlyProductionService::class);
        $syncedCount = $prodService->syncMonthlyStatistics(2026, 9);
        $this->assertIsInt($syncedCount);
    }
}
