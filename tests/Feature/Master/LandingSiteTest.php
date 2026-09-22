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
use App\Models\Wppnri;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingSiteTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

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

        $this->viewerUser = User::factory()->create();
        $this->viewerUser->assignRole('viewer');

        $this->province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $this->regency = Regency::create(['province_id' => $this->province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $this->district = District::create(['regency_id' => $this->regency->id, 'code' => '11.01.01', 'name' => 'Johan Pahlawan']);
        $this->village = Village::create(['district_id' => $this->district->id, 'code' => '11.01.01.2001', 'name' => 'Ujong Baroh']);
    }

    public function test_landing_site_index_can_be_rendered_for_authorized_user(): void
    {
        LandingSite::create([
            'code' => 'PPN-MEULABOH',
            'name' => 'PPN Meulaboh',
            'site_type' => 'PPN',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('master.landing-sites.index'));

        $response->assertOk();
        $response->assertSee('Master Data Landing Site');
        $response->assertSee('PPN Meulaboh');
    }

    public function test_landing_site_index_is_forbidden_for_unauthorized_user(): void
    {
        $response = $this->actingAs($this->viewerUser)
            ->get(route('master.landing-sites.index'));

        $response->assertForbidden();
    }

    public function test_landing_site_store_validates_and_creates_record(): void
    {
        $payload = [
            'code' => 'PPI-PADANG-SEURAHET',
            'name' => 'PPI Padang Seurahet',
            'site_type' => 'PPI',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'district_id' => $this->district->id,
            'village_id' => $this->village->id,
            'address' => 'Jl. Samudera No. 45',
            'latitude' => 4.1456000,
            'longitude' => 96.1234000,
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.landing-sites.store'), $payload);

        $response->assertRedirect(route('master.landing-sites.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('landing_sites', [
            'code' => 'PPI-PADANG-SEURAHET',
            'name' => 'PPI Padang Seurahet',
            'site_type' => 'PPI',
            'is_active' => true,
        ]);
    }

    public function test_landing_site_store_fails_on_duplicate_code(): void
    {
        LandingSite::create([
            'code' => 'TPI-EXISTING',
            'name' => 'TPI Lama',
            'site_type' => 'TPI',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->post(route('master.landing-sites.store'), [
                'code' => 'TPI-EXISTING',
                'name' => 'TPI Baru Duplikat',
                'site_type' => 'TPI',
                'province_id' => $this->province->id,
                'regency_id' => $this->regency->id,
            ]);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_landing_site_update_successfully(): void
    {
        $site = LandingSite::create([
            'code' => 'TPI-UPDATE-01',
            'name' => 'TPI Sebelum Update',
            'site_type' => 'TPI',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->put(route('master.landing-sites.update', $site), [
                'code' => 'TPI-UPDATE-01',
                'name' => 'TPI Sesudah Update',
                'site_type' => 'PPI',
                'province_id' => $this->province->id,
                'regency_id' => $this->regency->id,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('master.landing-sites.index'));
        $response->assertSessionHas('success');

        $site->refresh();
        $this->assertSame('TPI Sesudah Update', $site->name);
        $this->assertSame('PPI', $site->site_type);
    }

    public function test_landing_site_toggle_status(): void
    {
        $site = LandingSite::create([
            'code' => 'TPI-TOGGLE',
            'name' => 'TPI Uji Toggle',
            'site_type' => 'TPI',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->patch(route('master.landing-sites.toggle-status', $site));

        $response->assertSessionHas('success');
        $site->refresh();
        $this->assertFalse($site->is_active);

        // Toggle back
        $this->actingAs($this->adminUser)
            ->patch(route('master.landing-sites.toggle-status', $site));

        $site->refresh();
        $this->assertTrue($site->is_active);
    }

    public function test_landing_site_cannot_be_deleted_if_used_as_vessel_homeport(): void
    {
        $site = LandingSite::create([
            'code' => 'PPN-HOMEPORT',
            'name' => 'PPN Pelindung Armada',
            'site_type' => 'PPN',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        Vessel::create([
            'name' => 'KM. Pelindung Pangkalan',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 20,
            'homeport_site_id' => $site->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.landing-sites.destroy', $site));

        $response->assertRedirect(route('master.landing-sites.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('landing_sites', ['id' => $site->id]);
    }

    public function test_landing_site_cannot_be_deleted_if_referenced_by_fishing_trips(): void
    {
        $site = LandingSite::create([
            'code' => 'PPP-TRIP',
            'name' => 'PPP Operasi Trip',
            'site_type' => 'PPP',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM. Trip Tester',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15,
            'is_active' => true,
        ]);

        FishingTrip::create([
            'trip_number' => 'TRIP-SITE-01',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(2),
            'crew_count' => 5,
            'validation_status' => 'validated',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.landing-sites.destroy', $site));

        $response->assertRedirect(route('master.landing-sites.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('landing_sites', ['id' => $site->id]);
    }

    public function test_unreferenced_landing_site_can_be_deleted(): void
    {
        $site = LandingSite::create([
            'code' => 'DEL-SITE-01',
            'name' => 'Pangkalan Kosong',
            'site_type' => 'TPI',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('master.landing-sites.destroy', $site));

        $response->assertRedirect(route('master.landing-sites.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('landing_sites', ['id' => $site->id]);
    }

    public function test_wppnri_and_trip_relationship(): void
    {
        $wpp = Wppnri::create([
            'code' => '571',
            'name' => 'WPPNRI 571 - Selat Malaka',
            'is_active' => true,
        ]);

        $site = LandingSite::create([
            'code' => 'SITE-WPP-TEST',
            'name' => 'Pelabuhan Uji WPP',
            'site_type' => 'PPP',
            'province_id' => $this->province->id,
            'regency_id' => $this->regency->id,
            'is_active' => true,
        ]);

        $vessel = Vessel::create([
            'name' => 'KM. WPP Penjelajah',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 30,
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-WPP-01',
            'vessel_id' => $vessel->id,
            'wppnri_id' => $wpp->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(4),
            'crew_count' => 6,
            'validation_status' => 'validated',
        ]);

        $trip->refresh();
        $this->assertNotNull($trip->wppnri);
        $this->assertSame('571', $trip->wppnri->code);
        $this->assertTrue($wpp->fishingTrips->contains($trip));
    }
}
