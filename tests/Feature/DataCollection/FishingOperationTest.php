<?php

namespace Tests\Feature\DataCollection;

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
use App\Models\Wppnri;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingOperationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $enumeratorUser;

    protected User $viewerUser;

    protected Vessel $activeVessel;

    protected Vessel $inactiveVessel;

    protected Fisherman $captain;

    protected LandingSite $landingSite;

    protected FishingGear $fishingGear;

    protected Wppnri $wpp;

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

        $this->captain = Fisherman::create([
            'nik' => '1101011010850001',
            'name' => 'Panglima Laot Johan',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $this->fishingGear = FishingGear::create([
            'code' => 'PS-01',
            'name' => 'Pukat Cincin',
            'category' => 'jaring_lingkar',
            'source' => 'LOCAL',
            'is_active' => true,
        ]);

        $this->activeVessel = Vessel::create([
            'name' => 'KM. Samudera Raya',
            'registration_number' => 'GT. 15 No. 01',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15,
            'owner_id' => $this->captain->id,
            'primary_gear_id' => $this->fishingGear->id,
            'is_active' => true,
        ]);

        $this->inactiveVessel = Vessel::create([
            'name' => 'KM. Kapal Rusak',
            'registration_number' => 'GT. 10 No. 99',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'owner_id' => $this->captain->id,
            'is_active' => false,
        ]);

        $this->landingSite = LandingSite::create([
            'code' => 'PPN-MEULABOH',
            'name' => 'PPN Meulaboh',
            'site_type' => 'PPN',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'is_active' => true,
        ]);

        $this->wpp = Wppnri::create([
            'code' => '572',
            'name' => 'WPPNRI 572 - Samudera Hindia Sebelah Barat Sumatera',
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. FISHING TRIP TESTS
    // =========================================================================

    public function test_trips_index_page_can_be_rendered_for_authorized_users(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('trips.index'));
        $response->assertOk();
        $response->assertSee('Trip Penangkapan');

        // Enumerator / Petugas Lapangan also has access.trips
        $responseEnum = $this->actingAs($this->enumeratorUser)->get(route('trips.index'));
        $responseEnum->assertOk();
    }

    public function test_trips_index_is_forbidden_for_unauthorized_user(): void
    {
        $response = $this->actingAs($this->viewerUser)->get(route('trips.index'));
        $response->assertForbidden();
    }

    public function test_trip_store_validates_active_vessel_and_creates_trip(): void
    {
        $payload = [
            'vessel_id' => $this->activeVessel->id,
            'captain_id' => $this->captain->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(2)->format('Y-m-d'),
            'return_date' => now()->format('Y-m-d'),
            'crew_count' => 5,
            'fuel_consumption_liters' => 350,
            'ice_consumption_kg' => 500,
            'primary_gear_id' => $this->fishingGear->id,
            'wppnri_id' => $this->wpp->id,
            'validation_status' => 'submitted',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('trips.store'), $payload);

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_trips', [
            'vessel_id' => $this->activeVessel->id,
            'wppnri_id' => $this->wpp->id,
            'fma_code' => '572', // Auto synchronized
            'validation_status' => 'submitted',
        ]);
    }

    public function test_trip_store_fails_when_vessel_is_inactive(): void
    {
        $payload = [
            'vessel_id' => $this->inactiveVessel->id, // Inactive vessel!
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->format('Y-m-d'),
            'crew_count' => 3,
            'validation_status' => 'draft',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('trips.store'), $payload);

        $response->assertSessionHasErrors(['vessel_id']);
    }

    public function test_trip_quick_validate_action(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-VAL-01',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(1),
            'crew_count' => 4,
            'validation_status' => 'submitted',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patch(route('trips.validate', $trip), ['action' => 'validate']);

        $response->assertSessionHas('success');
        $trip->refresh();
        $this->assertSame('validated', $trip->validation_status);
        $this->assertNotNull($trip->validated_at);
        $this->assertEquals($this->adminUser->id, $trip->validated_by);
    }

    public function test_trip_cannot_be_deleted_if_it_has_fishing_efforts(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-DEL-PROTECT-01',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(2),
            'crew_count' => 4,
            'validation_status' => 'validated',
        ]);

        FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->fishingGear->id,
            'setting_number' => 1,
            'duration_hours' => 3.5,
            'setting_count' => 1,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('trips.destroy', $trip));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_trips', ['id' => $trip->id]);
    }

    public function test_unreferenced_trip_can_be_deleted(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-EMPTY-01',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(1),
            'crew_count' => 2,
            'validation_status' => 'draft',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('trips.destroy', $trip));

        $response->assertRedirect(route('trips.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('fishing_trips', ['id' => $trip->id]);
    }

    // =========================================================================
    // 2. FISHING EFFORT TESTS
    // =========================================================================

    public function test_efforts_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('efforts.index'));
        $response->assertOk();
        $response->assertSee('Upaya Penangkapan Ikan');
    }

    public function test_effort_store_validates_and_creates_record(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-EFF-01',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(1),
            'crew_count' => 3,
            'validation_status' => 'validated',
        ]);

        $payload = [
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->fishingGear->id,
            'setting_number' => 1,
            'duration_hours' => 4.25,
            'setting_count' => 1,
            'latitude_setting' => 4.1234567,
            'longitude_setting' => 96.1234567,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('efforts.store'), $payload);

        $response->assertRedirect(route('efforts.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_efforts', [
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->fishingGear->id,
            'duration_hours' => 4.25,
        ]);
    }

    public function test_effort_store_fails_on_negative_duration_or_invalid_coordinates(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-EFF-INV',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now(),
            'crew_count' => 2,
            'validation_status' => 'draft',
        ]);

        // Negative duration
        $response = $this->actingAs($this->adminUser)
            ->post(route('efforts.store'), [
                'fishing_trip_id' => $trip->id,
                'fishing_gear_id' => $this->fishingGear->id,
                'setting_number' => 1,
                'duration_hours' => -2.5,
                'setting_count' => 1,
            ]);
        $response->assertSessionHasErrors(['duration_hours']);

        // Invalid latitude
        $resCoord = $this->actingAs($this->adminUser)
            ->post(route('efforts.store'), [
                'fishing_trip_id' => $trip->id,
                'fishing_gear_id' => $this->fishingGear->id,
                'setting_number' => 1,
                'setting_count' => 1,
                'latitude_setting' => 95.0, // > 90
            ]);
        $resCoord->assertSessionHasErrors(['latitude_setting']);
    }

    public function test_effort_cannot_be_deleted_if_it_has_catches(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-EFF-CATCH',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(2),
            'crew_count' => 4,
            'validation_status' => 'validated',
        ]);

        $effort = FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $this->fishingGear->id,
            'setting_number' => 1,
            'duration_hours' => 3.0,
            'setting_count' => 1,
        ]);

        $species = Species::create([
            'asfis_species_id' => 101,
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'english_name' => 'Skipjack tuna',
            'indonesian_name' => 'Cakalang',
            'is_active' => true,
        ]);

        FishCatch::create([
            'fishing_trip_id' => $trip->id,
            'fishing_effort_id' => $effort->id,
            'fish_species_id' => $species->id,
            'weight_kg' => 250.5,
            'fish_count' => 120,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('efforts.destroy', $effort));

        $response->assertRedirect(route('efforts.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_efforts', ['id' => $effort->id]);
    }

    // =========================================================================
    // 3. LOGBOOK TESTS
    // =========================================================================

    public function test_logbooks_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('logbooks.index'));
        $response->assertOk();
        $response->assertSee('Buku Catatan Harian Kapal');
    }

    public function test_logbook_store_and_destroy(): void
    {
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-LOG-01',
            'vessel_id' => $this->activeVessel->id,
            'departure_site_id' => $this->landingSite->id,
            'departure_date' => now()->subDays(1),
            'crew_count' => 4,
            'validation_status' => 'validated',
        ]);

        $payload = [
            'fishing_trip_id' => $trip->id,
            'log_date' => now()->subDays(1)->format('Y-m-d'),
            'weather_condition' => 'cerah',
            'wave_height_meters' => 1.2,
            'activity_description' => 'Navigasi menuju fishing ground',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('logbooks.store'), $payload);

        $response->assertRedirect(route('logbooks.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('logbooks', [
            'fishing_trip_id' => $trip->id,
            'weather_condition' => 'cerah',
        ]);

        $logbook = Logbook::where('fishing_trip_id', $trip->id)->firstOrFail();
        $resDel = $this->actingAs($this->adminUser)
            ->delete(route('logbooks.destroy', $logbook));

        $resDel->assertRedirect(route('logbooks.index'));
        $resDel->assertSessionHas('success');

        $this->assertDatabaseMissing('logbooks', ['id' => $logbook->id]);
    }

    public function test_logbooks_card_3_inputs_standardized_placeholders_and_no_duplicate_labels(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('logbooks.index'));
        $response->assertOk();

        // 1. Search placeholder
        $response->assertSee('placeholder="Cari Aktivitas, Kapal, atau No. Trip"', false);

        // 2. Weather condition placeholder in select
        $response->assertSee('<option value="">Kondisi Cuaca</option>', false);

        // 3. Fishing Trips placeholder in select
        $response->assertSee('<option value="">Fishing Trips</option>', false);

        // 4. No duplicate visible labels outside fields
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Cari Aktivitas, Kapal, atau No. Trip</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Kondisi Cuaca</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Trip Penangkapan</label>', false);

        // 5. Search function works
        $searchRes = $this->actingAs($this->adminUser)->get(route('logbooks.index', ['search' => 'Navigasi']));
        $searchRes->assertOk();

        // 6. Filter function works
        $filterRes = $this->actingAs($this->adminUser)->get(route('logbooks.index', ['weather_condition' => 'cerah']));
        $filterRes->assertOk();

        // 7. Pagination works
        $pageRes = $this->actingAs($this->adminUser)->get(route('logbooks.index', ['per_page' => 10, 'page' => 1]));
        $pageRes->assertOk();
    }

    public function test_efforts_inputs_standardized_placeholders_and_no_duplicate_labels(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('efforts.index'));
        $response->assertOk();

        // 1. Search placeholder
        $response->assertSee('placeholder="Cari Trip, Kapal, atau Alat Tangkap"', false);

        // 2. Fishing Gear placeholder in select
        $response->assertSee('<option value="">Alat Penangkapan Ikan</option>', false);

        // 3. Fishing Trip placeholder in select
        $response->assertSee('<option value="">Trip Penangkapan</option>', false);

        // 4. No duplicate visible labels outside fields
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Cari Trip, Kapal, atau Alat Tangkap</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Alat Penangkapan Ikan</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Trip Penangkapan</label>', false);

        // 5. Search function works
        $searchRes = $this->actingAs($this->adminUser)->get(route('efforts.index', ['search' => 'Pancing']));
        $searchRes->assertOk();

        // 6. Filter function works
        $filterRes = $this->actingAs($this->adminUser)->get(route('efforts.index', ['fishing_gear_id' => $this->fishingGear->id]));
        $filterRes->assertOk();

        // 7. Pagination and sorting work
        $pageRes = $this->actingAs($this->adminUser)->get(route('efforts.index', ['per_page' => 10, 'page' => 1, 'sort' => 'setting_date', 'direction' => 'desc']));
        $pageRes->assertOk();
    }

    public function test_catches_inputs_standardized_placeholders_and_no_duplicate_labels(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('catches.index'));
        $response->assertOk();

        // 1. Search placeholder
        $response->assertSee('placeholder="Cari Ikan, Nama Lokal, atau Trip"', false);

        // 2. Catch status placeholder in select
        $response->assertSee('<option value="">Pilih Status Tangkapan</option>', false);

        // 3. Fish group placeholder in select
        $response->assertSee('<option value="">Pilih Kelompok Ikan</option>', false);

        // 4. No duplicate visible labels outside fields
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Cari Ikan, Nama Lokal, atau Trip</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Status Tangkapan</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Kelompok Ikan</label>', false);

        // 5. Search function works
        $searchRes = $this->actingAs($this->adminUser)->get(route('catches.index', ['search' => 'Tongkol']));
        $searchRes->assertOk();

        // 6. Filter function works
        $filterRes = $this->actingAs($this->adminUser)->get(route('catches.index', ['catch_status' => 'target']));
        $filterRes->assertOk();

        // 7. Pagination works
        $pageRes = $this->actingAs($this->adminUser)->get(route('catches.index', ['per_page' => 10, 'page' => 1]));
        $pageRes->assertOk();
    }

    public function test_landings_inputs_standardized_placeholders_and_no_duplicate_labels(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('landings.index'));
        $response->assertOk();

        // 1. Search placeholder
        $response->assertSee('placeholder="Cari No. Manifest, Kapal, atau Trip"', false);

        // 2. Landing site placeholder in select
        $response->assertSee('<option value="">Pilih Pelabuhan / TPI</option>', false);

        // 3. No duplicate visible labels outside fields
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Cari No. Manifest, Kapal, atau Trip</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Pelabuhan / TPI Pendaratan</label>', false);
        $response->assertDontSee('<label class="block text-xs font-medium text-slate-600 mb-1">Tanggal Pendaratan Dari</label>', false);

        // 4. Search function works
        $searchRes = $this->actingAs($this->adminUser)->get(route('landings.index', ['search' => 'LND']));
        $searchRes->assertOk();

        // 5. Filter function works
        $filterRes = $this->actingAs($this->adminUser)->get(route('landings.index', ['landing_site_id' => $this->landingSite->id]));
        $filterRes->assertOk();

        // 6. Pagination works
        $pageRes = $this->actingAs($this->adminUser)->get(route('landings.index', ['per_page' => 10, 'page' => 1]));
        $pageRes->assertOk();
    }
}
