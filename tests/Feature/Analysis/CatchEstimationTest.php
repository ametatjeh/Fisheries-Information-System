<?php

namespace Tests\Feature\Analysis;

use App\Models\CatchEstimation;
use App\Models\FishCatch;
use App\Models\Fisherman;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use App\Services\CatchEstimationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CatchEstimationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $viewerUser;

    protected LandingSite $landingSite;

    protected FishingGear $gear;

    protected Species $species;

    protected Regency $regency;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $this->regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $this->landingSite = LandingSite::create([
            'province_id' => $province->id,
            'regency_id' => $this->regency->id,
            'code' => 'TPI-ACH-01',
            'name' => 'TPI Ujong Baroh',
            'type' => 'PPI',
            'is_active' => true,
        ]);

        $this->gear = FishingGear::create([
            'code' => 'PS-01',
            'name' => 'Pukat Cincin Pelagis',
            'category' => 'jaring_lingkar',
            'source' => 'LOCAL',
            'is_active' => true,
        ]);

        $this->species = Species::create([
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'is_active' => true,
        ]);

        $captain = Fisherman::create([
            'nik' => '1101011010850001',
            'name' => 'Panglima Laot',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $vessel1 = Vessel::create([
            'name' => 'KM. Samudera 01',
            'registration_number' => 'REG-001',
            'owner_name' => 'Haji Abdullah',
            'gross_tonnage' => 30,
            'status' => 'active',
        ]);

        $vessel2 = Vessel::create([
            'name' => 'KM. Samudera 02',
            'registration_number' => 'REG-002',
            'owner_name' => 'Haji Ibrahim',
            'gross_tonnage' => 25,
            'status' => 'active',
        ]);

        // Trip 1 (dengan tangkapan / sampled)
        $trip1 = FishingTrip::create([
            'trip_number' => 'TRIP-EST-01',
            'vessel_id' => $vessel1->id,
            'captain_id' => $captain->id,
            'departure_site_id' => $this->landingSite->id,
            'landing_site_id' => $this->landingSite->id,
            'departure_date' => '2026-05-10 06:00:00',
            'return_date' => '2026-05-12 18:00:00',
            'validation_status' => 'validated',
        ]);

        $effort1 = FishingEffort::create([
            'fishing_trip_id' => $trip1->id,
            'fishing_gear_id' => $this->gear->id,
            'setting_time' => '2026-05-10 08:00:00',
            'hauling_time' => '2026-05-10 14:00:00',
            'duration_hours' => 6.0,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip1->id,
            'fishing_effort_id' => $effort1->id,
            'fish_species_id' => $this->species->id,
            'weight_kg' => 200.0,
            'fish_count' => 100,
            'catch_status' => 'target',
        ]);

        // Trip 2 (trip aktif populasi tanpa sampling tangkapan)
        FishingTrip::create([
            'trip_number' => 'TRIP-EST-02',
            'vessel_id' => $vessel2->id,
            'captain_id' => $captain->id,
            'departure_site_id' => $this->landingSite->id,
            'landing_site_id' => $this->landingSite->id,
            'departure_date' => '2026-05-15 06:00:00',
            'return_date' => '2026-05-17 18:00:00',
            'validation_status' => 'validated',
        ]);

        FishingEffort::create([
            'fishing_trip_id' => 2,
            'fishing_gear_id' => $this->gear->id,
            'duration_hours' => 5.0,
        ]);
    }

    public function test_viewer_cannot_access_estimations(): void
    {
        $response = $this->actingAs($this->viewerUser)->get(route('analysis.estimations.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_estimations_index(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('analysis.estimations.index'));
        $response->assertStatus(200);
        $response->assertSee('Estimasi Tangkapan');
    }

    public function test_service_calculates_estimation_and_raising_factor_accurately(): void
    {
        $service = app(CatchEstimationService::class);
        $results = $service->calculateEstimation(2026, 5, $this->landingSite->id, $this->gear->id, $this->species->id);

        $this->assertCount(1, $results);
        $stratum = $results->first();

        // Sampled: 200 kg dari 1 trip. Populasi trip aktif: 2 trip.
        // Raising factor = 2 / 1 = 2.0. Estimated catch = 200 * 2.0 = 400 kg.
        $this->assertEquals(200.0, $stratum['sampled_catch_kg']);
        $this->assertEquals(1, $stratum['sampled_trips']);
        $this->assertEquals(2, $stratum['population_trips']);
        $this->assertEquals(2.0000, $stratum['raising_factor']);
        $this->assertEquals(400.0, $stratum['estimated_catch_kg']);
        $this->assertEquals(200.0, $stratum['cpue']); // 400 kg / 2 trips
    }

    public function test_can_generate_and_store_draft_estimation_via_controller(): void
    {
        $response = $this->actingAs($this->adminUser)->post(route('analysis.estimations.generate'), [
            'year' => 2026,
            'month' => 5,
            'landing_site_id' => $this->landingSite->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('catch_estimations', [
            'year' => 2026,
            'month' => 5,
            'landing_site_id' => $this->landingSite->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'sampled_catch_kg' => 200.0,
            'estimated_catch_kg' => 400.0,
        ]);

        $estimation = CatchEstimation::where('year', 2026)->where('month', 5)->first();
        $this->assertEquals('draft', $estimation->status);
    }

    public function test_validation_workflow_transitions(): void
    {
        $estimation = CatchEstimation::create([
            'regency_id' => $this->regency->id,
            'landing_site_id' => $this->landingSite->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'year' => 2026,
            'month' => 5,
            'sampled_catch_kg' => 200.0,
            'raising_factor' => 1.5,
            'estimated_catch_kg' => 300.0,
            'estimated_effort_trips' => 10,
            'cpue' => 30.0,
            'notes' => '[STATUS:draft]',
        ]);

        $this->assertEquals('draft', $estimation->status);

        // Update status to validated
        $response = $this->actingAs($this->adminUser)->patch(
            route('analysis.estimations.update-status', $estimation),
            ['status' => 'validated', 'notes' => 'Disetujui oleh Kepala Seksi']
        );

        $response->assertSessionHas('success');
        $this->assertEquals('validated', $estimation->fresh()->status);

        // Update status to rejected
        $rejectResponse = $this->actingAs($this->adminUser)->patch(
            route('analysis.estimations.update-status', $estimation),
            ['status' => 'rejected', 'notes' => 'Perlu peninjauan ulang data sampel']
        );

        $rejectResponse->assertSessionHas('success');
        $this->assertEquals('rejected', $estimation->fresh()->status);
    }

    public function test_delete_protection_prevents_deleting_validated_estimation(): void
    {
        $estimation = CatchEstimation::create([
            'regency_id' => $this->regency->id,
            'landing_site_id' => $this->landingSite->id,
            'fish_species_id' => $this->species->id,
            'fishing_gear_id' => $this->gear->id,
            'year' => 2026,
            'month' => 5,
            'sampled_catch_kg' => 200.0,
            'raising_factor' => 1.5,
            'estimated_catch_kg' => 300.0,
            'notes' => '[STATUS:validated]',
        ]);

        // Percobaan hapus data validated harus ditolak
        $response = $this->actingAs($this->adminUser)->delete(route('analysis.estimations.destroy', $estimation));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('catch_estimations', ['id' => $estimation->id]);

        // Ubah ke status draft, lalu hapus
        $estimation->status = 'draft';
        $estimation->save();

        $delResponse = $this->actingAs($this->adminUser)->delete(route('analysis.estimations.destroy', $estimation));
        $delResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('catch_estimations', ['id' => $estimation->id]);
    }

    public function test_compliance_rules_reject_negative_or_abnormal_values(): void
    {
        $service = app(CatchEstimationService::class);

        $badEstimation = CatchEstimation::create([
            'regency_id' => $this->regency->id,
            'year' => 2026,
            'month' => 5,
            'sampled_catch_kg' => -50.0, // Negatif!
            'raising_factor' => 1.2,
            'estimated_catch_kg' => -60.0,
            'notes' => '[STATUS:draft]',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $service->updateValidationStatus($badEstimation, 'validated');
    }
}
