<?php

namespace Tests\Feature\Master;

use App\Models\District;
use App\Models\FisherGroup;
use App\Models\Fisherman;
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

class FishermanTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $petugasUser;

    protected User $viewerUser;

    protected Province $province;

    protected Regency $regency;

    protected District $district;

    protected Village $village;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->petugasUser = User::factory()->create();
        $this->petugasUser->assignRole('petugas-lapangan');

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');

        $this->province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $this->regency = Regency::create(['province_id' => $this->province->id, 'code' => '11.01', 'name' => 'Aceh Barat']);
        $this->district = District::create(['regency_id' => $this->regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        $this->village = Village::create(['district_id' => $this->district->id, 'code' => '11.01.01.2001', 'name' => 'Ujong Baroh']);
    }

    public function test_fisherman_index_can_be_rendered_for_authorized_users(): void
    {
        // Admin
        $responseAdmin = $this->actingAs($this->adminUser)->get(route('master.fishermen.index'));
        $responseAdmin->assertOk();
        $responseAdmin->assertSee('Master Data Nelayan');
        $responseAdmin->assertSee('Tambah Data Nelayan');

        // Petugas Lapangan
        $responsePetugas = $this->actingAs($this->petugasUser)->get(route('master.fishermen.index'));
        $responsePetugas->assertOk();
    }

    public function test_fisherman_index_is_forbidden_for_unauthorized_users(): void
    {
        $responseViewer = $this->actingAs($this->viewerUser)->get(route('master.fishermen.index'));
        $responseViewer->assertForbidden();
    }

    public function test_fisherman_search_and_filters(): void
    {
        $fisher1 = Fisherman::create([
            'nik' => '1101011001850001',
            'kusuka_number' => 'KUSUKA-001',
            'name' => 'Baharuddin Yusuf',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'pemilik',
            'is_active' => true,
        ]);

        $fisher2 = Fisherman::create([
            'nik' => '1101012002900002',
            'kusuka_number' => 'KUSUKA-002',
            'name' => 'Siti Nurhaliza',
            'gender' => 'P',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nelayan_tanpa_perahu',
            'is_active' => false,
        ]);

        // Search by name
        $resSearch = $this->actingAs($this->adminUser)->get(route('master.fishermen.index', ['search' => 'Baharuddin']));
        $resSearch->assertOk();
        $resSearch->assertSee('Baharuddin Yusuf');
        $resSearch->assertDontSee('Siti Nurhaliza');

        // Filter by fisher_type
        $resType = $this->actingAs($this->adminUser)->get(route('master.fishermen.index', ['fisher_type' => 'nelayan_tanpa_perahu']));
        $resType->assertOk();
        $resType->assertSee('Siti Nurhaliza');
        $resType->assertDontSee('Baharuddin Yusuf');

        // Filter by status active
        $resStatus = $this->actingAs($this->adminUser)->get(route('master.fishermen.index', ['status' => '1']));
        $resStatus->assertOk();
        $resStatus->assertSee('Baharuddin Yusuf');
        $resStatus->assertDontSee('Siti Nurhaliza');
    }

    public function test_fisherman_store_validates_and_creates_record(): void
    {
        $group = FisherGroup::create([
            'code' => 'KUB-01',
            'name' => 'KUB Maju Bersama',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
        ]);

        $payload = [
            'nik' => '1101011505880005',
            'kusuka_number' => 'KUSUKA-1101-2024-0005',
            'name' => 'Teuku Iskandar Muda',
            'gender' => 'L',
            'birth_place' => 'Meulaboh',
            'birth_date' => '1988-05-15',
            'phone' => '081234567890',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'district_id' => $this->district->id,
            'village_id' => $this->village->id,
            'address' => 'Gampong Padang Seurahet No. 12',
            'fisher_group_id' => $group->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.fishermen.store'), $payload);

        $response->assertRedirect(route('master.fishermen.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishers', [
            'nik' => '1101011505880005',
            'name' => 'Teuku Iskandar Muda',
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => 1,
        ]);
    }

    public function test_fisherman_store_fails_on_duplicate_nik(): void
    {
        Fisherman::create([
            'nik' => '1101011111110001',
            'name' => 'Nelayan Awal',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'pemilik',
        ]);

        $payload = [
            'nik' => '1101011111110001', // Duplicate NIK
            'name' => 'Nelayan Duplikat',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'abk',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.fishermen.store'), $payload);

        $response->assertSessionHasErrors(['nik']);
    }

    public function test_fisherman_store_fails_on_duplicate_kusuka(): void
    {
        Fisherman::create([
            'nik' => '1101011111110002',
            'kusuka_number' => 'KUSUKA-UNIQUE-01',
            'name' => 'Nelayan KUSUKA',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'pemilik',
        ]);

        $payload = [
            'nik' => '1101011111110003',
            'kusuka_number' => 'KUSUKA-UNIQUE-01', // Duplicate KUSUKA
            'name' => 'Nelayan Lain',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'abk',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.fishermen.store'), $payload);

        $response->assertSessionHasErrors(['kusuka_number']);
    }

    public function test_fisherman_store_fails_on_invalid_gender_or_type(): void
    {
        $payload = [
            'nik' => '1101011111110004',
            'name' => 'Nelayan Validasi',
            'gender' => 'X', // Invalid gender
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nelayan_sultan', // Invalid type
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.fishermen.store'), $payload);

        $response->assertSessionHasErrors(['gender', 'fisher_type']);
    }

    public function test_fisherman_update_successfully_updates_record(): void
    {
        $fisher = Fisherman::create([
            'nik' => '1101011111110005',
            'kusuka_number' => 'KUSUKA-UPDATE-01',
            'name' => 'Nelayan Sebelum Update',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'abk',
            'is_active' => true,
        ]);

        $updatePayload = [
            'nik' => '1101011111110005', // Keeping same NIK without collision
            'kusuka_number' => 'KUSUKA-UPDATE-01',
            'name' => 'Nelayan Sesudah Update',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nahkoda_jurumudi', // Promoted to nahkoda
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('master.fishermen.update', $fisher), $updatePayload);

        $response->assertRedirect(route('master.fishermen.index'));
        $response->assertSessionHas('success');

        $fisher->refresh();
        $this->assertEquals('Nelayan Sesudah Update', $fisher->name);
        $this->assertEquals('nahkoda_jurumudi', $fisher->fisher_type);
    }

    public function test_fisherman_toggle_status_flips_is_active(): void
    {
        $fisher = Fisherman::create([
            'nik' => '1101011111110006',
            'name' => 'Nelayan Toggle',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'pemilik',
            'is_active' => true,
        ]);

        $this->assertTrue($fisher->is_active);

        $this->actingAs($this->adminUser)
            ->patch(route('master.fishermen.toggle-status', $fisher));

        $fisher->refresh();
        $this->assertFalse($fisher->is_active);

        // Toggle back
        $this->actingAs($this->adminUser)
            ->patch(route('master.fishermen.toggle-status', $fisher));

        $fisher->refresh();
        $this->assertTrue($fisher->is_active);
    }

    public function test_fisherman_cannot_be_deleted_if_they_own_vessel(): void
    {
        $owner = Fisherman::create([
            'nik' => '1101011111110007',
            'name' => 'Pemilik Kapal Baruna',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'pemilik',
            'is_active' => true,
        ]);

        Vessel::create([
            'name' => 'KM. Baruna Bahari',
            'owner_id' => $owner->id,
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.fishermen.destroy', $owner));

        $response->assertRedirect(route('master.fishermen.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishers', ['id' => $owner->id]);
    }

    public function test_fisherman_cannot_be_deleted_if_they_captain_trip(): void
    {
        $captain = Fisherman::create([
            'nik' => '1101011111110008',
            'name' => 'Nahkoda Legendaris',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM. Trip Test',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15,
            'is_active' => true,
        ]);

        $site = LandingSite::create([
            'code' => 'SITE-'.uniqid(),
            'name' => 'Pelabuhan Kapten',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'district_id' => $this->district->id,
            'village_id' => $this->village->id,
            'is_active' => true,
        ]);

        FishingTrip::create([
            'trip_number' => 'TRIP-CAPT-001',
            'vessel_id' => $vessel->id,
            'captain_id' => $captain->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(5),
            'crew_count' => 3,
            'validation_status' => 'validated',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.fishermen.destroy', $captain));

        $response->assertRedirect(route('master.fishermen.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishers', ['id' => $captain->id]);
    }

    public function test_fisherman_without_vessels_or_trips_can_be_deleted(): void
    {
        $unlinkedFisher = Fisherman::create([
            'nik' => '1101011111110009',
            'name' => 'Nelayan Bebas',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'abk',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.fishermen.destroy', $unlinkedFisher));

        $response->assertRedirect(route('master.fishermen.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('fishers', ['id' => $unlinkedFisher->id]);
    }

    public function test_inactive_fisherman_behavior_with_trips_and_vessels(): void
    {
        $inactiveFisher = Fisherman::create([
            'nik' => '1101011111110010',
            'name' => 'Nelayan Pensiun',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => false,
        ]);

        $activeFisher = Fisherman::create([
            'nik' => '1101011111110011',
            'name' => 'Nelayan Aktif',
            'gender' => 'L',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'fisher_type' => 'nahkoda_jurumudi',
            'is_active' => true,
        ]);

        // 1. Only active fishers appear in new trip captain selection
        $activeCaptains = Fisherman::where('is_active', true)->pluck('id');
        $this->assertTrue($activeCaptains->contains($activeFisher->id));
        $this->assertFalse($activeCaptains->contains($inactiveFisher->id));

        // 2. Historical trip can still access the inactive captain relation
        $vessel = Vessel::create([
            'name' => 'KM. Kapal Pensiun',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => true,
        ]);

        $site = LandingSite::create([
            'code' => 'SITE-'.uniqid(),
            'name' => 'Pelabuhan Pensiun',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'district_id' => $this->district->id,
            'village_id' => $this->village->id,
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-PENSIUN-01',
            'vessel_id' => $vessel->id,
            'captain_id' => $inactiveFisher->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(20),
            'crew_count' => 3,
            'validation_status' => 'validated',
        ]);

        $trip->refresh();
        $this->assertNotNull($trip->captain);
        $this->assertEquals('Nelayan Pensiun', $trip->captain->name);
        $this->assertFalse($trip->captain->is_active);
    }
}
