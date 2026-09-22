<?php

namespace Tests\Feature\DataCollection;

use App\Models\BiologicalMeasurement;
use App\Models\FishCatch;
use App\Models\Fisherman;
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
use App\Models\Vessel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingCatchSamplingTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $enumeratorUser;

    protected User $viewerUser;

    protected LandingSite $landingSite;

    protected FishingTrip $trip;

    protected FishingEffort $effort;

    protected Species $activeSpecies;

    protected Species $inactiveSpecies;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->enumeratorUser = User::factory()->create();
        $this->enumeratorUser->assignRole('enumerator');

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $this->landingSite = LandingSite::create([
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'code' => 'TPI-01',
            'name' => 'TPI Meulaboh',
            'type' => 'PPI',
            'is_active' => true,
        ]);

        $captain = Fisherman::create([
            'nik' => '1101011010850001',
            'name' => 'Panglima Laot Johan',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM Samudera',
            'registration_number' => 'REG-12345',
            'owner_name' => 'Haji Abdullah',
            'gross_tonnage' => 30,
            'status' => 'active',
        ]);

        $this->trip = FishingTrip::create([
            'trip_number' => 'TRIP-202609-001',
            'vessel_id' => $vessel->id,
            'captain_id' => $captain->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(2),
            'return_date' => now(),
            'validation_status' => 'validated',
        ]);

        $gear = FishingGear::create([
            'code' => 'PS-01',
            'name' => 'Pukat Cincin',
            'category' => 'jaring_lingkar',
            'source' => 'LOCAL',
            'is_active' => true,
        ]);

        $this->effort = FishingEffort::create([
            'fishing_trip_id' => $this->trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_time' => now()->subHours(10),
            'hauling_time' => now()->subHours(6),
            'duration_hours' => 4.0,
        ]);

        $this->activeSpecies = Species::create([
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'indonesian_name' => 'Cakalang',
            'is_active' => true,
        ]);

        $this->inactiveSpecies = Species::create([
            'fao_code' => 'DIS',
            'scientific_name' => 'Species Inactiva',
            'indonesian_name' => 'Ikan Tidak Aktif',
            'is_active' => false,
        ]);
    }

    // =========================================================================
    // 1. LANDING & LANDING ITEM TESTS
    // =========================================================================

    public function test_viewer_cannot_access_landings(): void
    {
        $response = $this->actingAs($this->viewerUser)->get(route('landings.index'));
        $response->assertStatus(403);
    }

    public function test_enumerator_can_view_landings_index(): void
    {
        $response = $this->actingAs($this->enumeratorUser)->get(route('landings.index'));
        $response->assertStatus(200);
    }

    public function test_can_create_landing_with_valid_items(): void
    {
        $payload = [
            'landing_number' => 'LND-TEST-001',
            'fishing_trip_id' => $this->trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => now()->format('Y-m-d H:i:s'),
            'buyer_count' => 5,
            'notes' => 'Pendaratan lancar',
            'items' => [
                [
                    'fish_species_id' => $this->activeSpecies->id,
                    'weight_kg' => 150.50,
                    'price_per_kg' => 25000,
                    'fish_count' => 60,
                    'quality_grade' => 'A',
                ],
            ],
        ];

        $response = $this->actingAs($this->enumeratorUser)->post(route('landings.store'), $payload);
        $response->assertRedirect(route('landings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('landings', [
            'landing_number' => 'LND-TEST-001',
            'fishing_trip_id' => $this->trip->id,
            'landing_site_id' => $this->landingSite->id,
            'total_weight_kg' => 150.50,
            'total_value_rp' => 150.50 * 25000,
        ]);

        $this->assertDatabaseHas('landing_items', [
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 150.50,
            'price_per_kg' => 25000,
        ]);
    }

    public function test_landing_creation_rejects_inactive_species_or_invalid_weight(): void
    {
        $payload = [
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => now()->format('Y-m-d H:i:s'),
            'items' => [
                [
                    'fish_species_id' => $this->inactiveSpecies->id,
                    'weight_kg' => 0, // invalid weight
                    'price_per_kg' => 10000,
                ],
            ],
        ];

        $response = $this->actingAs($this->enumeratorUser)->post(route('landings.store'), $payload);
        $response->assertSessionHasErrors(['items.0.fish_species_id', 'items.0.weight_kg']);
    }

    public function test_can_update_landing(): void
    {
        $landing = Landing::create([
            'landing_number' => 'LND-UPD-001',
            'fishing_trip_id' => $this->trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => now(),
            'buyer_count' => 3,
        ]);

        $response = $this->actingAs($this->enumeratorUser)->put(route('landings.update', $landing), [
            'fishing_trip_id' => $this->trip->id,
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => now()->format('Y-m-d H:i:s'),
            'buyer_count' => 10,
            'notes' => 'Catatan revisi',
        ]);

        $response->assertRedirect(route('landings.index'));
        $this->assertDatabaseHas('landings', [
            'id' => $landing->id,
            'buyer_count' => 10,
            'notes' => 'Catatan revisi',
        ]);
    }

    public function test_can_delete_landing(): void
    {
        $landing = Landing::create([
            'landing_number' => 'LND-DEL-001',
            'landing_site_id' => $this->landingSite->id,
            'landing_date' => now(),
        ]);

        $item = LandingItem::create([
            'landing_id' => $landing->id,
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 50,
            'price_per_kg' => 20000,
            'total_price' => 1000000,
        ]);

        $response = $this->actingAs($this->enumeratorUser)->delete(route('landings.destroy', $landing));
        $response->assertRedirect(route('landings.index'));

        $this->assertDatabaseMissing('landings', ['id' => $landing->id]);
        $this->assertDatabaseMissing('landing_items', ['id' => $item->id]);
    }

    // =========================================================================
    // 2. CATCH TESTS
    // =========================================================================

    public function test_viewer_cannot_access_catches(): void
    {
        $response = $this->actingAs($this->viewerUser)->get(route('catches.index'));
        $response->assertStatus(403);
    }

    public function test_can_create_catch_with_valid_species_and_weight(): void
    {
        $payload = [
            'fishing_trip_id' => $this->trip->id,
            'fishing_effort_id' => $this->effort->id,
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 125.75,
            'fish_count' => 45,
            'catch_status' => 'target',
            'notes' => 'Tangkapan utama',
        ];

        $response = $this->actingAs($this->enumeratorUser)->post(route('catches.store'), $payload);
        $response->assertRedirect(route('catches.index'));

        $this->assertDatabaseHas('catches', [
            'fishing_trip_id' => $this->trip->id,
            'fishing_effort_id' => $this->effort->id,
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 125.75,
            'catch_status' => 'target',
        ]);
    }

    public function test_catch_rejects_inactive_species_and_negative_weight(): void
    {
        $payload = [
            'fishing_trip_id' => $this->trip->id,
            'fish_species_id' => $this->inactiveSpecies->id,
            'weight_kg' => -10,
            'catch_status' => 'target',
        ];

        $response = $this->actingAs($this->enumeratorUser)->post(route('catches.store'), $payload);
        $response->assertSessionHasErrors(['fish_species_id', 'weight_kg']);
    }

    public function test_catch_rejects_effort_from_different_trip(): void
    {
        // Trip kedua dengan effort berbeda
        $otherTrip = FishingTrip::create([
            'trip_number' => 'TRIP-OTHER',
            'vessel_id' => $this->trip->vessel_id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now(),
            'validation_status' => 'validated',
        ]);

        $otherEffort = FishingEffort::create([
            'fishing_trip_id' => $otherTrip->id,
            'fishing_gear_id' => $this->effort->fishing_gear_id,
            'duration_hours' => 2.0,
        ]);

        $payload = [
            'fishing_trip_id' => $this->trip->id,
            'fishing_effort_id' => $otherEffort->id, // Tidak cocok dengan trip!
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 50,
            'catch_status' => 'target',
        ];

        $response = $this->actingAs($this->enumeratorUser)->post(route('catches.store'), $payload);
        $response->assertSessionHasErrors('fishing_effort_id');
    }

    public function test_can_update_and_delete_catch(): void
    {
        $catch = FishCatch::create([
            'fishing_trip_id' => $this->trip->id,
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 80,
            'catch_status' => 'target',
        ]);

        $response = $this->actingAs($this->enumeratorUser)->put(route('catches.update', $catch), [
            'fishing_trip_id' => $this->trip->id,
            'fish_species_id' => $this->activeSpecies->id,
            'weight_kg' => 95.5,
            'catch_status' => 'bycatch',
        ]);

        $response->assertRedirect(route('catches.index'));
        $this->assertDatabaseHas('catches', [
            'id' => $catch->id,
            'weight_kg' => 95.5,
            'catch_status' => 'bycatch',
        ]);

        $deleteResponse = $this->actingAs($this->enumeratorUser)->delete(route('catches.destroy', $catch));
        $deleteResponse->assertRedirect(route('catches.index'));
        $this->assertDatabaseMissing('catches', ['id' => $catch->id]);
    }

    // =========================================================================
    // 3. SAMPLING PLAN & SAMPLE TESTS
    // =========================================================================

    public function test_viewer_cannot_access_sampling(): void
    {
        $response = $this->actingAs($this->viewerUser)->get(route('analysis.sampling.index'));
        $response->assertStatus(403);
    }

    public function test_can_create_sampling_plan(): void
    {
        $payload = [
            'code' => 'SMP-TEST-001',
            'title' => 'Program Sampling Riset',
            'landing_site_id' => $this->landingSite->id,
            'target_species_id' => $this->activeSpecies->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-10-01',
            'target_sample_size' => 100,
            'sampling_method' => 'random',
            'status' => 'active',
            'notes' => 'Riset Cakalang',
        ];

        $response = $this->actingAs($this->adminUser)->post(route('analysis.sampling.plans.store'), $payload);
        $response->assertRedirect(route('analysis.sampling.index', ['tab' => 'plans']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sampling_plans', [
            'code' => 'SMP-TEST-001',
            'target_species_id' => $this->activeSpecies->id,
        ]);
    }

    public function test_sampling_plan_with_samples_cannot_be_deleted(): void
    {
        $plan = SamplingPlan::create([
            'code' => 'SMP-PROTECT',
            'title' => 'Rencana Terlindungi',
            'landing_site_id' => $this->landingSite->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-30',
            'target_sample_size' => 50,
            'sampling_method' => 'stratified',
            'status' => 'active',
        ]);

        Sample::create([
            'sample_code' => 'SMP-BATCH-001',
            'sampling_plan_id' => $plan->id,
            'landing_site_id' => $this->landingSite->id,
            'sample_date' => '2026-09-15',
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('analysis.sampling.plans.destroy', $plan));
        $response->assertRedirect(route('analysis.sampling.index', ['tab' => 'plans']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('sampling_plans', ['id' => $plan->id]);
    }

    public function test_can_create_and_protect_sample(): void
    {
        $sample = Sample::create([
            'sample_code' => 'SMP-MEAS-001',
            'landing_site_id' => $this->landingSite->id,
            'sample_date' => '2026-09-10',
            'total_specimens' => 0,
            'total_weight_kg' => 0,
        ]);

        // Simpan pengukuran biologis
        $measurementPayload = [
            'fish_species_id' => $this->activeSpecies->id,
            'fork_length_cm' => 38.5,
            'weight_gram' => 850.0,
            'sex' => 'female',
            'gonad_maturity_stage' => 3,
        ];

        $response = $this->actingAs($this->adminUser)->post(
            route('analysis.sampling.samples.specimens.store', $sample),
            $measurementPayload
        );
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('biological_measurements', [
            'sample_id' => $sample->id,
            'fish_species_id' => $this->activeSpecies->id,
            'fork_length_cm' => 38.5,
            'weight_gram' => 850.0,
        ]);

        // Sample harus ter-update specimen count dan weight
        $sample->refresh();
        $this->assertEquals(1, $sample->total_specimens);
        $this->assertEquals(0.85, (float) $sample->total_weight_kg);

        // Delete sample harus ditolak karena memiliki pengukuran biologis
        $deleteSampleResponse = $this->actingAs($this->adminUser)->delete(route('analysis.sampling.samples.destroy', $sample));
        $deleteSampleResponse->assertRedirect(route('analysis.sampling.index', ['tab' => 'samples']));
        $deleteSampleResponse->assertSessionHas('error');

        $this->assertDatabaseHas('samples', ['id' => $sample->id]);

        // Hapus pengukuran biologis
        $measurement = BiologicalMeasurement::where('sample_id', $sample->id)->first();
        $delMeasResponse = $this->actingAs($this->adminUser)->delete(route('analysis.sampling.measurements.destroy', $measurement));
        $delMeasResponse->assertSessionHas('success');

        $sample->refresh();
        $this->assertEquals(0, $sample->total_specimens);
        $this->assertEquals(0.0, (float) $sample->total_weight_kg);

        // Setelah kosong, sample dapat dihapus
        $deleteEmptyResponse = $this->actingAs($this->adminUser)->delete(route('analysis.sampling.samples.destroy', $sample));
        $deleteEmptyResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
    }
}
