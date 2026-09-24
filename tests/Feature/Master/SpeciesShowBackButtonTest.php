<?php

namespace Tests\Feature\Master;

use App\Models\Species;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeciesShowBackButtonTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('super-admin');
    }

    public function test_species_show_has_kembali_button_with_border_and_background_next_to_submit_button(): void
    {
        $species = Species::create([
            'fao_code' => 'YFT',
            'scientific_name' => 'Thunnus albacares',
            'local_name_id' => 'Madidihang / Tuna Sirip Kuning',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('master.species.show', $species));

        $response->assertOk();
        $response->assertSee('Kembali');
        $response->assertSee('Simpan Perubahan');
        $response->assertSee('border-gray-300');
        $response->assertSee('bg-white');
    }

    public function test_species_show_preserves_previous_table_page_url_from_session(): void
    {
        $species = Species::create([
            'fao_code' => 'SKJ',
            'scientific_name' => 'Katsuwonus pelamis',
            'local_name_id' => 'Cakalang',
            'is_active' => true,
        ]);

        // User visits species table page 2 with filters
        $indexUrl = route('master.species.index', ['page' => 2, 'per_page' => 50, 'search' => 'Katsuwonus']);
        $this->actingAs($this->adminUser)->get($indexUrl);

        // User views species detail
        $response = $this->actingAs($this->adminUser)->get(route('master.species.show', $species));

        $response->assertOk();
        $response->assertSee(e($indexUrl), false);
    }

    public function test_species_update_shows_only_single_notification_with_icon_and_close_button(): void
    {
        $species = Species::create([
            'fao_code' => 'TUN',
            'scientific_name' => 'Thunnus tonggol',
            'local_name_id' => 'Tongkol Abu-abu',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->adminUser)
            ->from(route('master.species.show', $species))
            ->put(route('master.species.update', $species), [
                'local_name_id' => 'Tongkol Komo',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('master.species.show', $species));

        $followed = $this->actingAs($this->adminUser)
            ->get(route('master.species.show', $species));

        $followed->assertOk();
        // Ensure flash message appears only once
        $content = $followed->getContent();
        $this->assertEquals(1, substr_count($content, 'Data lokal berhasil diperbarui.'));
        $followed->assertSee('aria-label="Tutup notifikasi"', false);
    }
}
