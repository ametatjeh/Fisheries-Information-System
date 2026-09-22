<?php

namespace Tests\Feature\Master;

use App\Models\District;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VesselTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('viewer');
    }

    public function test_vessel_index_page_can_be_rendered_for_authorized_user(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('master.vessels.index'));

        $response->assertOk();
        $response->assertSee('Master Data Kapal Penangkap Ikan (Vessels)');
        $response->assertSee('Katalog Armada Kapal Penangkap Ikan');
        $response->assertSee('Tambah Kapal');
    }

    public function test_vessel_index_page_is_forbidden_for_unauthorized_user(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('master.vessels.index'));

        $response->assertForbidden();
    }

    public function test_vessel_search_and_filters(): void
    {
        $vessel1 = Vessel::create([
            'name' => 'KM. Bahari Sejahtera',
            'registration_number' => 'GT. 15 No. 123/Bda',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15.5,
            'is_active' => true,
        ]);

        $vessel2 = Vessel::create([
            'name' => 'Perahu Samudera Indah',
            'registration_number' => 'GT. 3 No. 456/Bda',
            'vessel_type' => 'motor_tempel',
            'gross_tonnage' => 2.8,
            'is_active' => false,
        ]);

        // Search by name
        $resSearch = $this->actingAs($this->adminUser)->get(route('master.vessels.index', ['search' => 'Bahari']));
        $resSearch->assertOk();
        $resSearch->assertSee('KM. Bahari Sejahtera');
        $resSearch->assertDontSee('Perahu Samudera Indah');

        // Filter by vessel_type
        $resType = $this->actingAs($this->adminUser)->get(route('master.vessels.index', ['vessel_type' => 'motor_tempel']));
        $resType->assertOk();
        $resType->assertSee('Perahu Samudera Indah');
        $resType->assertDontSee('KM. Bahari Sejahtera');

        // Filter by status active
        $resStatus = $this->actingAs($this->adminUser)->get(route('master.vessels.index', ['status' => '1']));
        $resStatus->assertOk();
        $resStatus->assertSee('KM. Bahari Sejahtera');
        $resStatus->assertDontSee('Perahu Samudera Indah');
    }

    public function test_vessel_store_validates_and_creates_record(): void
    {
        $payload = [
            'name' => 'KM. Mina Baruna 01',
            'registration_number' => 'GT. 25 No. 888/Bda',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 24.5,
            'length' => 18.2,
            'width' => 4.5,
            'depth' => 2.1,
            'engine_power_hp' => 220,
            'engine_brand' => 'Yanmar',
            'build_year' => 2021,
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.vessels.store'), $payload);

        $response->assertRedirect(route('master.vessels.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('vessels', [
            'name' => 'KM. Mina Baruna 01',
            'registration_number' => 'GT. 25 No. 888/Bda',
            'gross_tonnage' => 24.5,
            'is_active' => 1,
        ]);
    }

    public function test_vessel_store_fails_on_duplicate_registration_number(): void
    {
        Vessel::create([
            'name' => 'KM. Kapal Lama',
            'registration_number' => 'REG-12345',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => true,
        ]);

        $payload = [
            'name' => 'KM. Kapal Baru',
            'registration_number' => 'REG-12345',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 12,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.vessels.store'), $payload);

        $response->assertSessionHasErrors(['registration_number']);
    }

    public function test_vessel_store_fails_on_negative_gross_tonnage(): void
    {
        $payload = [
            'name' => 'KM. Kapal Minus',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => -5,
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.vessels.store'), $payload);

        $response->assertSessionHasErrors(['gross_tonnage']);
    }

    public function test_vessel_update_successfully_updates_record(): void
    {
        $vessel = Vessel::create([
            'name' => 'KM. Awal Mula',
            'registration_number' => 'GT. 10 No. 001',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => true,
        ]);

        $updatePayload = [
            'name' => 'KM. Awal Mula Reborn',
            'registration_number' => 'GT. 10 No. 001', // Keeps existing reg number without self-collision
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 12.5,
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('master.vessels.update', $vessel), $updatePayload);

        $response->assertRedirect(route('master.vessels.index'));
        $response->assertSessionHas('success');

        $vessel->refresh();
        $this->assertEquals('KM. Awal Mula Reborn', $vessel->name);
        $this->assertEquals(12.5, (float) $vessel->gross_tonnage);
    }

    public function test_vessel_toggle_status_flips_is_active(): void
    {
        $vessel = Vessel::create([
            'name' => 'KM. Toggle Test',
            'registration_number' => 'REG-TOGGLE',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15,
            'is_active' => true,
        ]);

        $this->assertTrue($vessel->is_active);

        $response = $this->actingAs($this->adminUser)
            ->patch(route('master.vessels.toggle-status', $vessel));

        $response->assertSessionHas('success');

        $vessel->refresh();
        $this->assertFalse($vessel->is_active);

        // Toggle back
        $this->actingAs($this->adminUser)
            ->patch(route('master.vessels.toggle-status', $vessel));

        $vessel->refresh();
        $this->assertTrue($vessel->is_active);
    }

    protected function createDummyLandingSite(): LandingSite
    {
        $province = Province::firstOrCreate(['code' => '11'], ['name' => 'Aceh']);
        $regency = Regency::firstOrCreate(['code' => '11.01'], ['province_id' => $province->id, 'name' => 'Aceh Barat']);
        $district = District::firstOrCreate(['code' => '11.01.01'], ['regency_id' => $regency->id, 'name' => 'Johan Pahlawan']);
        $village = Village::firstOrCreate(['code' => '11.01.01.2001'], ['district_id' => $district->id, 'name' => 'Ujong Baroh']);

        return LandingSite::create([
            'code' => 'TEST-'.uniqid(),
            'name' => 'Pelabuhan Uji',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'district_id' => $district->id,
            'village_id' => $village->id,
            'is_active' => true,
        ]);
    }

    public function test_vessel_cannot_be_deleted_if_it_has_fishing_trips(): void
    {
        $vessel = Vessel::create([
            'name' => 'KM. Trip Protect',
            'registration_number' => 'REG-PROTECT',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 20,
            'is_active' => true,
        ]);

        $site = $this->createDummyLandingSite();

        // Attach a fishing trip to the vessel
        FishingTrip::create([
            'trip_number' => 'TRIP-TEST-001',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(3),
            'crew_count' => 5,
            'validation_status' => 'validated',
        ]);

        // Attempt delete
        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.vessels.destroy', $vessel));

        $response->assertRedirect(route('master.vessels.index'));
        $response->assertSessionHas('error');

        // Vessel must NOT be deleted
        $this->assertDatabaseHas('vessels', ['id' => $vessel->id]);
    }

    public function test_vessel_without_trips_can_be_deleted(): void
    {
        $vessel = Vessel::create([
            'name' => 'KM. Delete Me',
            'registration_number' => 'REG-DEL',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 5,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.vessels.destroy', $vessel));

        $response->assertRedirect(route('master.vessels.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('vessels', ['id' => $vessel->id]);
    }

    public function test_inactive_vessel_behavior_with_fishing_trips(): void
    {
        $activeVessel = Vessel::create([
            'name' => 'KM. Aktif',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => true,
        ]);

        $inactiveVessel = Vessel::create([
            'name' => 'KM. Nonaktif',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => false,
        ]);

        $site = $this->createDummyLandingSite();

        // Historical trip for inactive vessel
        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-HISTORICAL-01',
            'vessel_id' => $inactiveVessel->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(10),
            'crew_count' => 4,
            'validation_status' => 'validated',
        ]);

        // 1. Inactive vessel is excluded from new trip vessel selection query
        $selectableVessels = Vessel::where('is_active', true)->pluck('id');
        $this->assertTrue($selectableVessels->contains($activeVessel->id));
        $this->assertFalse($selectableVessels->contains($inactiveVessel->id));

        // 2. Historical trip can still access the inactive vessel via relation
        $trip->refresh();
        $this->assertNotNull($trip->vessel);
        $this->assertEquals('KM. Nonaktif', $trip->vessel->name);
        $this->assertFalse($trip->vessel->is_active);
    }
}
