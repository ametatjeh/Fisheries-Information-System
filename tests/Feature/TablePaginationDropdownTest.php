<?php

namespace Tests\Feature;

use App\Models\FishCatch;
use App\Models\Fisherman;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Logbook;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TablePaginationDropdownTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $landingSite = LandingSite::create(['province_id' => $province->id, 'regency_id' => $regency->id, 'code' => 'LS01', 'name' => 'Pelabuhan Meulaboh', 'site_type' => 'PPI', 'is_active' => true]);
        $gear = FishingGear::create(['code' => 'G01', 'name' => 'Pancing Ulur', 'category' => 'pancing', 'source' => 'LOCAL', 'is_active' => true]);
        $captain = Fisherman::create(['province_id' => $province->id, 'name' => 'Panglima Laot', 'nik' => '1234567890123456', 'regency_id' => $regency->id, 'is_active' => true]);
        $vessel = Vessel::create(['name' => 'KM Samudra 01', 'vessel_type' => 'motor_tempel', 'gross_tonnage' => 5, 'is_active' => true]);
        $species = Species::create(['fao_code' => 'SKJ', 'scientific_name' => 'Katsuwonus pelamis', 'local_name_id' => 'Cakalang', 'is_active' => true]);

        for ($i = 1; $i <= 30; $i++) {
            $trip = FishingTrip::create([
                'trip_number' => 'TRIP-2026-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'vessel_id' => $vessel->id,
                'captain_id' => $captain->id,
                'primary_gear_id' => $gear->id,
                'departure_site_id' => $landingSite->id,
                'departure_date' => now()->subDays($i)->toDateString(),
                'validation_status' => 'draft',
            ]);

            Logbook::create([
                'fishing_trip_id' => $trip->id,
                'log_date' => now()->subDays($i)->toDateString(),
                'activity_type' => 'setting',
            ]);

            $effort = FishingEffort::create([
                'fishing_trip_id' => $trip->id,
                'fishing_gear_id' => $gear->id,
                'setting_date' => now()->subDays($i)->toDateString(),
            ]);

            FishCatch::create([
                'fishing_trip_id' => $trip->id,
                'fishing_effort_id' => $effort->id,
                'fish_species_id' => $species->id,
                'weight_kg' => 100 + $i,
            ]);
        }
    }

    public function test_trips_table_has_pagination_dropdown_and_respects_per_page(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('trips.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
        $response->assertSee('text-white bg-ocean-600', false);
    }

    public function test_logbooks_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('logbooks.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_efforts_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('efforts.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_catches_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('catches.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_landings_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('landings.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_validation_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('analysis.validation.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_sampling_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('analysis.sampling.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_estimations_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('analysis.estimations.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_users_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('admin.users.index', ['per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }

    public function test_reports_table_has_pagination_dropdown(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('reports.index', ['type' => 'catches', 'per_page' => 10]));
        $response->assertOk();
        $response->assertSee('Tampilkan');
        $response->assertSee('baris per halaman');
        $response->assertSee('name="per_page"', false);
    }
}
