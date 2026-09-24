<?php

namespace Tests\Feature\Master;

use App\Models\District;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Vessel;
use App\Models\Village;
use Database\Seeders\FishingGearIsscfgCanonicalSeeder;
use Database\Seeders\FishingGearSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FishingGearIsscfgTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->seed(FishingGearSeeder::class);
        $this->seed(FishingGearIsscfgCanonicalSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('super-admin');
    }

    public function test_gear_index_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('master.gears.index'));

        $response->assertOk();
        $response->assertSee('Master Data Alat Tangkap');
        $response->assertSee('PS-01');
        $response->assertSee('01.1');
        $response->assertSee('PS');
        $response->assertSee('Pukat Langgar');
    }

    public function test_12_local_gears_preserve_exact_ids_and_mappings(): void
    {
        $expected = [
            1 => ['code' => 'PS-01', 'isscfg_code' => '01.1', 'abbr' => 'PS'],
            2 => ['code' => 'PS-02', 'isscfg_code' => '01.1', 'abbr' => 'PS'],
            3 => ['code' => 'LL-01', 'isscfg_code' => '09.32', 'abbr' => 'LLD'],
            4 => ['code' => 'LL-02', 'isscfg_code' => '09.31', 'abbr' => 'LLS'],
            5 => ['code' => 'HL-01', 'isscfg_code' => '09.1', 'abbr' => 'LHP'],
            6 => ['code' => 'TL-01', 'isscfg_code' => '09.5', 'abbr' => 'LTL'],
            7 => ['code' => 'GN-01', 'isscfg_code' => '07.2', 'abbr' => 'GND'],
            8 => ['code' => 'GN-02', 'isscfg_code' => '07.1', 'abbr' => 'GNS'],
            9 => ['code' => 'TN-01', 'isscfg_code' => '07.5', 'abbr' => 'GTR'],
            10 => ['code' => 'BS-01', 'isscfg_code' => '02.1', 'abbr' => 'SB'],
            11 => ['code' => 'TR-01', 'isscfg_code' => '08.2', 'abbr' => 'FPO'],
            12 => ['code' => 'LN-01', 'isscfg_code' => '05.2', 'abbr' => 'LNB'],
        ];

        foreach ($expected as $id => $exp) {
            $gear = FishingGear::findOrFail($id);
            $this->assertEquals($exp['code'], $gear->code);
            $this->assertEquals($exp['isscfg_code'], $gear->isscfg_code);
            $this->assertEquals($exp['abbr'], $gear->standard_abbreviation);
            $this->assertEquals('LOCAL', $gear->source);
        }
    }

    public function test_seeder_is_idempotent_and_does_not_duplicate(): void
    {
        $countBefore = FishingGear::count();
        $this->seed(FishingGearIsscfgCanonicalSeeder::class);
        $countAfter = FishingGear::count();

        $this->assertEquals($countBefore, $countAfter);
    }

    public function test_search_by_local_code_and_isscfg_code(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['search' => 'LL-01']));
        $response->assertOk();
        $response->assertSee('LL-01');
        $response->assertSee('09.32');
        $response->assertSee('LLD');

        $responseFao = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['search' => '03.12']));
        $responseFao->assertOk();
        $responseFao->assertSee('03.12');
        $responseFao->assertSee('OTB');
    }

    public function test_filter_by_source(): void
    {
        $responseLocal = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['source' => 'LOCAL']));
        $responseLocal->assertOk();
        $responseLocal->assertViewHas('gears', fn ($gears) => $gears->contains('code', 'PS-01') && ! $gears->contains('code', 'FAO-03.12'));

        $responseFao = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['source' => 'FAO_ISSCFG']));
        $responseFao->assertOk();
        $responseFao->assertViewHas('gears', fn ($gears) => $gears->contains('code', 'FAO-01.1') && ! $gears->contains('code', 'PS-01'));
    }

    public function test_filter_by_status(): void
    {
        // Deactivate one gear
        $gear = FishingGear::find(1);
        $gear->update(['is_active' => false]);

        $responseActive = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['status' => 'active']));
        $responseActive->assertOk();
        $responseActive->assertViewHas('gears', fn ($gears) => ! $gears->contains('id', 1));

        $responseInactive = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['status' => 'inactive']));
        $responseInactive->assertOk();
        $responseInactive->assertViewHas('gears', fn ($gears) => $gears->contains('id', 1));
    }

    public function test_bulk_activate_and_deactivate(): void
    {
        $ids = [1, 2, 3];

        // Bulk deactivate
        $responseDeactivate = $this->actingAs($this->user)
            ->patch(route('master.gears.bulk-status'), [
                'ids' => $ids,
                'action' => 'deactivate',
            ]);
        $responseDeactivate->assertRedirect();
        $responseDeactivate->assertSessionHas('success');

        foreach ($ids as $id) {
            $this->assertDatabaseHas('fishing_gears', [
                'id' => $id,
                'is_active' => false,
            ]);
        }

        // Bulk activate
        $responseActivate = $this->actingAs($this->user)
            ->patch(route('master.gears.bulk-status'), [
                'ids' => $ids,
                'action' => 'activate',
            ]);
        $responseActivate->assertRedirect();
        $responseActivate->assertSessionHas('success');

        foreach ($ids as $id) {
            $this->assertDatabaseHas('fishing_gears', [
                'id' => $id,
                'is_active' => true,
            ]);
        }
    }

    public function test_bulk_status_validation_fails_on_empty_ids_or_invalid_action(): void
    {
        $response = $this->actingAs($this->user)
            ->patch(route('master.gears.bulk-status'), [
                'ids' => [],
                'action' => 'activate',
            ]);
        $response->assertSessionHasErrors(['ids']);

        $responseInvalidAction = $this->actingAs($this->user)
            ->patch(route('master.gears.bulk-status'), [
                'ids' => [1],
                'action' => 'delete_all',
            ]);
        $responseInvalidAction->assertSessionHasErrors(['action']);
    }

    public function test_pagination_and_query_string_preservation(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['per_page' => 10, 'search' => 'Pukat']));

        $response->assertOk();
        $response->assertSee('per_page=10');
        $response->assertSee('search=Pukat');
    }

    public function test_canonical_master_data_exact_counts_and_conversions(): void
    {
        $this->assertSame(76, FishingGear::where('source', 'FAO_ISSCFG')->count());
        $this->assertSame(12, FishingGear::where('source', 'LOCAL')->count());
        $this->assertSame(88, FishingGear::count());

        // Zero 20.0 and exactly one 99.9
        $this->assertSame(0, FishingGear::where('isscfg_code', '20.0')->count());
        $this->assertSame(1, FishingGear::where('isscfg_code', '99.9')->count());

        $rec99 = FishingGear::where('isscfg_code', '99.9')->first();
        $this->assertSame(23, $rec99->id);
        $this->assertSame('NK', $rec99->standard_abbreviation);
        $this->assertSame('Gear not known', $rec99->name_en);

        // Check 03.14 and 03.15 corrections
        $r14 = FishingGear::where('isscfg_code', '03.14')->first();
        $this->assertSame('OTP', $r14->standard_abbreviation);
        $this->assertSame('Multiple bottom otter trawls', $r14->name_en);

        $r15 = FishingGear::where('isscfg_code', '03.15')->first();
        $this->assertSame('PTB', $r15->standard_abbreviation);
        $this->assertSame('Bottom pair trawls', $r15->name_en);

        // Check 18 new codes are present
        $newCodes = [
            '01.9', '02.9', '03.19', '03.29', '03.3', '03.9', '04.3', '04.9',
            '06.9', '07.9', '09.39', '10.2', '10.3', '10.4', '10.5', '10.6', '10.7', '10.8',
        ];
        $this->assertSame(18, FishingGear::where('source', 'FAO_ISSCFG')->whereIn('isscfg_code', $newCodes)->count());
    }

    protected function createDummyLandingSite(): LandingSite
    {
        $province = Province::firstOrCreate(['code' => '11'], ['name' => 'Aceh']);
        $regency = Regency::firstOrCreate(['code' => '11.01'], ['province_id' => $province->id, 'name' => 'Aceh Barat']);
        $district = District::firstOrCreate(['code' => '11.01.01'], ['regency_id' => $regency->id, 'name' => 'Johan Pahlawan']);
        $village = Village::firstOrCreate(['code' => '11.01.01.2001'], ['district_id' => $district->id, 'name' => 'Ujong Baroh']);

        return LandingSite::create([
            'code' => 'SITE-'.uniqid(),
            'name' => 'Pelabuhan Uji',
            'province_id' => $province->id,
            'regency_id' => $regency->id,
            'district_id' => $district->id,
            'village_id' => $village->id,
            'is_active' => true,
        ]);
    }

    public function test_gear_routes_are_forbidden_for_unauthorized_user(): void
    {
        $regularUser = User::factory()->create();
        $regularUser->assignRole('viewer');

        $response = $this->actingAs($regularUser)->get(route('master.gears.index'));
        $response->assertForbidden();

        $responseStore = $this->actingAs($regularUser)->post(route('master.gears.store'), [
            'code' => 'UNAUTH-01',
            'name' => 'Alat Tak Berizin',
            'category' => 'pancing',
            'source' => 'LOCAL',
        ]);
        $responseStore->assertForbidden();
    }

    public function test_gear_store_validates_and_creates_record(): void
    {
        $payload = [
            'code' => 'TEST-GEAR-01',
            'name' => 'Jaring Insang Hanyut Modifikasi',
            'name_en' => 'Modified Drift Gillnet',
            'category' => 'jaring_insang',
            'source' => 'LOCAL',
            'description' => 'Alat tangkap uji coba operasional',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('master.gears.store'), $payload);

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('fishing_gears', [
            'code' => 'TEST-GEAR-01',
            'name' => 'Jaring Insang Hanyut Modifikasi',
            'category' => 'jaring_insang',
            'source' => 'LOCAL',
            'is_active' => true,
        ]);
    }

    public function test_gear_store_fails_on_duplicate_code(): void
    {
        $payload = [
            'code' => 'PS-01', // Existing code
            'name' => 'Jaring Duplikat',
            'category' => 'jaring_lingkar',
            'source' => 'LOCAL',
        ];

        $response = $this->actingAs($this->user)
            ->post(route('master.gears.store'), $payload);

        $response->assertSessionHasErrors(['code']);
    }

    public function test_gear_update_successfully_updates_record(): void
    {
        $gear = FishingGear::where('code', 'PS-01')->firstOrFail();

        $payload = [
            'code' => 'PS-01', // Keep existing
            'name' => 'Pukat Cincin Modifikasi Aceh',
            'category' => 'jaring_lingkar',
            'source' => 'LOCAL',
            'is_active' => '1',
        ];

        $response = $this->actingAs($this->user)
            ->put(route('master.gears.update', $gear), $payload);

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('success');

        $gear->refresh();
        $this->assertSame('Pukat Cincin Modifikasi Aceh', $gear->name);
    }

    public function test_gear_toggle_status_flips_is_active(): void
    {
        $gear = FishingGear::where('code', 'PS-01')->firstOrFail();
        $this->assertTrue($gear->is_active);

        $response = $this->actingAs($this->user)
            ->patch(route('master.gears.toggle-status', $gear));

        $response->assertSessionHas('success');
        $gear->refresh();
        $this->assertFalse($gear->is_active);

        // Toggle back
        $this->actingAs($this->user)
            ->patch(route('master.gears.toggle-status', $gear));

        $gear->refresh();
        $this->assertTrue($gear->is_active);
    }

    public function test_gear_cannot_be_deleted_if_referenced_by_vessel(): void
    {
        $gear = FishingGear::where('code', 'PS-01')->firstOrFail();

        Vessel::create([
            'name' => 'KM. Pelindung Gear',
            'registration_number' => 'REG-GEAR-01',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 15,
            'primary_gear_id' => $gear->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('master.gears.destroy', $gear));

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_gears', ['id' => $gear->id]);
    }

    public function test_gear_cannot_be_deleted_if_referenced_by_fishing_trip(): void
    {
        $gear = FishingGear::where('code', 'PS-02')->firstOrFail();
        $site = $this->createDummyLandingSite();

        $vessel = Vessel::create([
            'name' => 'KM. Trip Gear',
            'registration_number' => 'REG-GEAR-02',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 10,
            'is_active' => true,
        ]);

        FishingTrip::create([
            'trip_number' => 'TRIP-GEAR-01',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'primary_gear_id' => $gear->id,
            'departure_date' => now()->subDays(5),
            'crew_count' => 4,
            'validation_status' => 'validated',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('master.gears.destroy', $gear));

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_gears', ['id' => $gear->id]);
    }

    public function test_gear_cannot_be_deleted_if_referenced_by_fishing_effort(): void
    {
        $gear = FishingGear::where('code', 'LL-01')->firstOrFail();
        $site = $this->createDummyLandingSite();

        $vessel = Vessel::create([
            'name' => 'KM. Effort Gear',
            'registration_number' => 'REG-GEAR-03',
            'vessel_type' => 'kapal_motor',
            'gross_tonnage' => 12,
            'is_active' => true,
        ]);

        $trip = FishingTrip::create([
            'trip_number' => 'TRIP-GEAR-02',
            'vessel_id' => $vessel->id,
            'departure_site_id' => $site->id,
            'departure_date' => now()->subDays(3),
            'crew_count' => 3,
            'validation_status' => 'validated',
        ]);

        FishingEffort::create([
            'fishing_trip_id' => $trip->id,
            'fishing_gear_id' => $gear->id,
            'setting_number' => 1,
            'setting_date' => now()->subDays(2),
            'duration_hours' => 4.5,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('master.gears.destroy', $gear));

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_gears', ['id' => $gear->id]);
    }

    public function test_gear_cannot_be_deleted_if_it_has_children(): void
    {
        $parent = FishingGear::create([
            'code' => 'PARENT-01',
            'name' => 'Kategori Induk Uji',
            'category' => 'lainnya',
            'source' => 'LOCAL',
            'level' => 1,
        ]);

        $child = FishingGear::create([
            'code' => 'CHILD-01',
            'name' => 'Sub Jenis Uji',
            'category' => 'lainnya',
            'source' => 'LOCAL',
            'parent_id' => $parent->id,
            'level' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('master.gears.destroy', $parent));

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('fishing_gears', ['id' => $parent->id]);
    }

    public function test_unreferenced_gear_can_be_deleted(): void
    {
        $gear = FishingGear::create([
            'code' => 'DEL-GEAR-01',
            'name' => 'Alat Tangkap Siap Hapus',
            'category' => 'lainnya',
            'source' => 'LOCAL',
        ]);

        $response = $this->actingAs($this->user)
            ->delete(route('master.gears.destroy', $gear));

        $response->assertRedirect(route('master.gears.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('fishing_gears', ['id' => $gear->id]);
    }

    public function test_gears_table_displays_number_column_and_does_not_display_id_column_and_continues_across_pages(): void
    {
        // Pastikan ada minimal 12 gears
        $existingCount = FishingGear::count();
        if ($existingCount < 12) {
            for ($i = $existingCount; $i < 12; $i++) {
                FishingGear::create([
                    'code' => 'PAGE-GEAR-'.$i,
                    'name' => 'Alat Tangkap Halaman '.$i,
                    'category' => 'lainnya',
                    'source' => 'LOCAL',
                    'is_active' => true,
                ]);
            }
        }

        // Halaman 1 dengan per_page = 10
        $responsePage1 = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['per_page' => 10, 'page' => 1]));

        $responsePage1->assertOk();
        // Kolom '#' ada di header
        $responsePage1->assertSee('<th class="py-3.5 px-3 w-10 text-center text-white">#</th>', false);
        // Kolom 'ID' tidak ada di header
        $responsePage1->assertDontSee('<th class="py-3.5 px-2 w-12 text-center font-mono text-white">ID</th>', false);

        // Halaman 2 dengan per_page = 10
        $responsePage2 = $this->actingAs($this->user)
            ->get(route('master.gears.index', ['per_page' => 10, 'page' => 2]));

        $responsePage2->assertOk();
        // Baris pertama halaman 2 harus bernomor 11
        $responsePage2->assertSee('11');
        $responsePage2->assertSee('12');
    }
}
