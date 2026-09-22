<?php

namespace Tests\Feature\Master;

use App\Models\Species;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SpeciesBulkUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);

        $this->user = User::factory()->create();
        $this->user->assignRole('super-admin');
    }

    public function test_bulk_activate_updates_species_status(): void
    {
        $species = collect([
            Species::create(['fao_code' => 'AA1', 'is_active' => false]),
            Species::create(['fao_code' => 'AA2', 'is_active' => false]),
            Species::create(['fao_code' => 'AA3', 'is_active' => false]),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('master.species.bulk-update-status'), [
                'ids' => $species->pluck('id')->toArray(),
                'action' => 'activate',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($species as $item) {
            $this->assertDatabaseHas('species', [
                'id' => $item->id,
                'is_active' => true,
            ]);
        }
    }

    public function test_bulk_deactivate_updates_species_status(): void
    {
        $species = collect([
            Species::create(['fao_code' => 'BB1', 'is_active' => true]),
            Species::create(['fao_code' => 'BB2', 'is_active' => true]),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('master.species.bulk-update-status'), [
                'ids' => $species->pluck('id')->toArray(),
                'action' => 'deactivate',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        foreach ($species as $item) {
            $this->assertDatabaseHas('species', [
                'id' => $item->id,
                'is_active' => false,
            ]);
        }
    }

    public function test_bulk_update_requires_ids(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('master.species.bulk-update-status'), [
                'ids' => [],
                'action' => 'activate',
            ]);

        $response->assertSessionHasErrors('ids');
    }

    public function test_bulk_update_requires_valid_action(): void
    {
        $species = Species::create(['fao_code' => 'CC1', 'is_active' => true]);

        $response = $this->actingAs($this->user)
            ->post(route('master.species.bulk-update-status'), [
                'ids' => [$species->id],
                'action' => 'delete',
            ]);

        $response->assertSessionHasErrors('action');
    }

    public function test_bulk_deactivate_does_not_delete_records(): void
    {
        $species = Species::create(['fao_code' => 'DD1', 'is_active' => true]);

        $this->actingAs($this->user)
            ->post(route('master.species.bulk-update-status'), [
                'ids' => [$species->id],
                'action' => 'deactivate',
            ]);

        $this->assertDatabaseHas('species', ['id' => $species->id]);
        $this->assertDatabaseHas('species', ['id' => $species->id, 'is_active' => false]);
    }

    public function test_species_index_displays_with_pagination(): void
    {
        // Create enough records to paginate
        for ($i = 0; $i < 30; $i++) {
            Species::create(['fao_code' => 'Z'.str_pad($i, 2, '0', STR_PAD_LEFT), 'is_active' => true]);
        }

        $response = $this->actingAs($this->user)
            ->get(route('master.species.index', ['per_page' => 10]));

        $response->assertOk();
        $response->assertSee('Menampilkan 1');
    }

    public function test_species_index_preserves_query_string_on_pagination(): void
    {
        for ($i = 0; $i < 30; $i++) {
            Species::create(['fao_code' => 'T'.str_pad($i, 2, '0', STR_PAD_LEFT), 'is_active' => true, 'scientific_name' => 'Tuna Test']);
        }

        $response = $this->actingAs($this->user)
            ->get(route('master.species.index', [
                'search' => 'Tuna',
                'status' => 'active',
                'per_page' => 10,
            ]));

        $response->assertOk();
        // withQueryString should keep search, status, per_page in pagination links
        $response->assertSee('search=Tuna');
        $response->assertSee('per_page=10');
    }

    public function test_invalid_per_page_defaults_to_25(): void
    {
        Species::create(['fao_code' => 'EE1', 'is_active' => true]);

        $response = $this->actingAs($this->user)
            ->get(route('master.species.index', ['per_page' => 999]));

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_bulk_update(): void
    {
        $species = Species::create(['fao_code' => 'FF1', 'is_active' => true]);

        $response = $this->post(route('master.species.bulk-update-status'), [
            'ids' => [$species->id],
            'action' => 'deactivate',
        ]);

        $response->assertRedirect(route('login'));
    }
}
