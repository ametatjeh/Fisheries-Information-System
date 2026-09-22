<?php

namespace Tests\Feature\Master;

use App\Models\District;
use App\Models\Fisherman;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $viewerUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');
    }

    public function test_wilayah_index_page_can_be_rendered_for_authorized_user(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('master.wilayah.index'));

        $response->assertOk();
        $response->assertSee('Master Data Wilayah');
        $response->assertSee('Aceh Barat');
    }

    public function test_wilayah_index_page_is_forbidden_for_unauthorized_user(): void
    {
        $response = $this->actingAs($this->viewerUser)
            ->get(route('master.wilayah.index'));

        $response->assertForbidden();
    }

    public function test_province_store_validates_and_creates_record(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('master.wilayah.province.store'), [
                'code' => '12',
                'name' => 'Sumatera Utara',
            ]);

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'provinsi']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('provinces', [
            'code' => '12',
            'name' => 'Sumatera Utara',
        ]);
    }

    public function test_province_store_fails_on_duplicate_code(): void
    {
        Province::create(['code' => '11', 'name' => 'Aceh']);

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.wilayah.province.store'), [
                'code' => '11',
                'name' => 'Aceh Duplikat',
            ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_province_cannot_be_deleted_if_it_has_regencies(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.province.destroy', $province));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'provinsi']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('provinces', ['id' => $province->id]);
    }

    public function test_unreferenced_province_can_be_deleted(): void
    {
        $province = Province::create(['code' => '99', 'name' => 'Provinsi Kosong']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.province.destroy', $province));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'provinsi']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('provinces', ['id' => $province->id]);
    }

    public function test_regency_store_validates_and_creates_record(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.wilayah.regency.store'), [
                'province_id' => $province->id,
                'code' => '11.02',
                'name' => 'Aceh Tenggara',
                'type' => 'kabupaten',
            ]);

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'kabupaten']));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('regencies', [
            'code' => '11.02',
            'name' => 'Aceh Tenggara',
            'type' => 'kabupaten',
        ]);
    }

    public function test_regency_cannot_be_deleted_if_it_has_districts(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        District::create(['regency_id' => $regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.regency.destroy', $regency));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'kabupaten']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('regencies', ['id' => $regency->id]);
    }

    public function test_district_cannot_be_deleted_if_it_has_villages(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        Village::create(['district_id' => $district->id, 'code' => '11.01.01.2001', 'name' => 'Ujong Baroh']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.district.destroy', $district));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'kecamatan']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('districts', ['id' => $district->id]);
    }

    public function test_village_cannot_be_deleted_if_referenced_by_fisherman(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        $village = Village::create(['district_id' => $district->id, 'code' => '11.01.01.2001', 'name' => 'Ujong Baroh']);

        Fisherman::create([
            'nik' => '1101011111110099',
            'name' => 'Nelayan Wilayah Uji',
            'gender' => 'L',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'district_id' => $district->id,
            'village_id' => $village->id,
            'fisher_type' => 'pemilik',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.village.destroy', $village));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'desa']));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('villages', ['id' => $village->id]);
    }

    public function test_unreferenced_village_can_be_deleted(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        $village = Village::create(['district_id' => $district->id, 'code' => '11.01.01.9999', 'name' => 'Desa Tanpa Nelayan']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.wilayah.village.destroy', $village));

        $response->assertRedirect(route('master.wilayah.index', ['tab' => 'desa']));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('villages', ['id' => $village->id]);
    }

    public function test_cascading_api_endpoints_return_correct_json(): void
    {
        $province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        $village = Village::create(['district_id' => $district->id, 'code' => '11.01.01.2001', 'name' => 'Ujong Baroh', 'postal_code' => '23611']);

        // API Regencies
        $resReg = $this->actingAs($this->adminUser)
            ->getJson(route('master.wilayah.api.regencies', $province));
        $resReg->assertOk();
        $resReg->assertJsonFragment(['name' => 'Aceh Barat', 'code' => '11.01']);

        // API Districts
        $resDist = $this->actingAs($this->adminUser)
            ->getJson(route('master.wilayah.api.districts', $regency));
        $resDist->assertOk();
        $resDist->assertJsonFragment(['name' => 'Johan Pahlawan', 'code' => '11.01.01']);

        // API Villages
        $resVill = $this->actingAs($this->adminUser)
            ->getJson(route('master.wilayah.api.villages', $district));
        $resVill->assertOk();
        $resVill->assertJsonFragment(['name' => 'Ujong Baroh', 'code' => '11.01.01.2001', 'postal_code' => '23611']);
    }
}
