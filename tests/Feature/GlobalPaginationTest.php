<?php

namespace Tests\Feature;

use App\Models\Fisherman;
use App\Models\FishingGear;
use App\Models\FishingGround;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Species;
use App\Models\User;
use App\Models\Vessel;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Province $province;

    protected Regency $regency;

    protected LandingSite $landingSite;

    protected Vessel $vessel;

    protected Fisherman $captain;

    protected FishingGear $gear;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');

        $this->province = Province::create(['code' => '11', 'name' => 'Aceh']);
        $this->regency = Regency::create(['province_id' => $this->province->id, 'code' => '11.01', 'name' => 'Aceh Barat', 'type' => 'kabupaten']);
        $this->landingSite = LandingSite::create(['province_id' => $this->province->id, 'regency_id' => $this->regency->id, 'code' => 'LS01', 'name' => 'Pelabuhan Meulaboh', 'site_type' => 'PPI', 'is_active' => true]);
        $this->gear = FishingGear::create(['code' => 'G01', 'name' => 'Pancing Ulur', 'category' => 'pancing', 'source' => 'LOCAL', 'is_active' => true]);
        $this->captain = Fisherman::create(['province_id' => $this->province->id, 'name' => 'Panglima Laot', 'nik' => '1234567890123456', 'regency_id' => $this->regency->id, 'is_active' => true]);
        $this->vessel = Vessel::create(['name' => 'KM Samudra 01', 'vessel_type' => 'motor_tempel', 'gross_tonnage' => 5, 'is_active' => true]);
    }

    public function test_page_1_page_2_page_3_continuous_numbering(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            FishingGround::create([
                'code' => 'FG-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Fishing Ground '.$i,
                'is_active' => true,
            ]);
        }

        // Page 1 (per_page = 10): items 1-10 -> numbering # 1 to 10
        $response1 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 1]));
        $response1->assertOk();
        $response1->assertSee('>1<', false);
        $response1->assertSee('>10<', false);
        $response1->assertDontSee('>11<', false);

        // Page 2 (per_page = 10): items 11-20 -> numbering # 11 to 20
        $response2 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 2]));
        $response2->assertOk();
        $response2->assertSee('>11<', false);
        $response2->assertSee('>20<', false);
        $response2->assertDontSee('>1<', false);
        $response2->assertDontSee('>21<', false);

        // Page 3 (per_page = 10): items 21-25 (last page) -> numbering # 21 to 25
        $response3 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 3]));
        $response3->assertOk();
        $response3->assertSee('>21<', false);
        $response3->assertSee('>25<', false);
        $response3->assertDontSee('>26<', false);
    }

    public function test_search_and_filter_and_sorting_query_strings_are_preserved(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            FishingGround::create([
                'code' => 'FG-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Zona Perairan Laut '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', [
            'search' => 'Perairan',
            'status' => '1',
            'per_page' => 10,
            'page' => 2,
        ]));

        $response->assertOk();
        // Pagination link should retain query string parameters
        $response->assertSee('search=Perairan');
        $response->assertSee('status=1');
        $response->assertSee('per_page=10');
        // Page 2 of active (30 items total, 10 per page -> page 2 has # 11 to 20)
        $response->assertSee('>11<', false);
        $response->assertSee('>20<', false);
    }

    public function test_empty_result_displays_empty_state_without_fake_numbering(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', [
            'search' => 'NON_EXISTENT_QUERY_XYZ',
        ]));

        $response->assertOk();
        $response->assertSee('Belum ada data master Daerah Penangkapan Ikan');
        $response->assertDontSee('<td class="px-4 py-3 text-center font-mono text-slate-400 text-xs">1</td>', false);
    }

    public function test_edit_and_delete_actions_preserve_database_id(): void
    {
        $ground = FishingGround::create([
            'code' => 'FG-999',
            'name' => 'Spot Khusus Uji ID',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index'));
        $response->assertOk();

        // Tampilan # adalah 1
        $response->assertSee('>1<', false);

        // Backend edit route/modal uses database ID ($ground->id)
        $response->assertSee(route('master.fishing-grounds.update', $ground));
        // Backend delete uses database ID ($ground->id)
        $response->assertSee('action="'.route('master.fishing-grounds.destroy', $ground).'"', false);
    }

    public function test_trips_pagination_preserves_numbering_and_database_id(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            FishingTrip::create([
                'trip_number' => 'TRIP-GLOBAL-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'vessel_id' => $this->vessel->id,
                'captain_id' => $this->captain->id,
                'primary_gear_id' => $this->gear->id,
                'departure_site_id' => $this->landingSite->id,
                'departure_date' => now()->subDays($i)->toDateString(),
                'validation_status' => 'draft',
            ]);
        }

        // Page 2 (per_page = 10) -> items 11-15 -> numbering # 11 to 15
        $response = $this->actingAs($this->adminUser)->get(route('trips.index', ['per_page' => 10, 'page' => 2]));
        $response->assertOk();
        $response->assertSee('>11<', false);
        $response->assertSee('>15<', false);
        $response->assertDontSee('>1<', false);
    }

    public function test_last_page_numbering_does_not_reset_with_27_items(): void
    {
        for ($i = 1; $i <= 27; $i++) {
            FishingGround::create([
                'code' => 'FG-'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'name' => 'Daerah Penangkapan '.str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }

        // Page 1: 1-10
        $page1 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 1]));
        $page1->assertOk();
        $page1->assertSee('>1<', false);
        $page1->assertSee('>10<', false);

        // Page 2: 11-20
        $page2 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 2]));
        $page2->assertOk();
        $page2->assertSee('>11<', false);
        $page2->assertSee('>20<', false);

        // Page 3: 21-27
        $page3 = $this->actingAs($this->adminUser)->get(route('master.fishing-grounds.index', ['per_page' => 10, 'page' => 3]));
        $page3->assertOk();
        $page3->assertSee('>21<', false);
        $page3->assertSee('>27<', false);
        $page3->assertDontSee('>28<', false);
    }

    public function test_species_table_header_is_hash_and_numbers_continue(): void
    {
        for ($i = 1; $i <= 15; $i++) {
            Species::create([
                'fao_code' => 'SP'.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'scientific_name' => 'Species Test '.$i,
                'local_name_id' => 'Ikan Uji '.$i,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->adminUser)->get(route('master.species.index', ['per_page' => 10, 'page' => 2]));
        $response->assertOk();
        // Header contains #
        $response->assertSee('>#<', false);
        // Page 2 contains # 11
        $response->assertSee('>11<', false);
    }

    public function test_users_table_header_is_hash_and_numbers_continue(): void
    {
        User::factory()->count(15)->create();

        $response = $this->actingAs($this->adminUser)->get(route('admin.users.index', ['per_page' => 10, 'page' => 2]));
        $response->assertOk();
        // Header contains #
        $response->assertSee('>#<', false);
        // Page 2 contains # 11
        $response->assertSee('>11<', false);
    }
}
