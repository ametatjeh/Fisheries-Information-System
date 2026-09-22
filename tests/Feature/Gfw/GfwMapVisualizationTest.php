<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GfwMapVisualizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
        $this->user = User::factory()->create();
        $this->user->assignRole('admin');
    }

    public function test_gis_workspace_page_renders_with_gis_and_gfw_integration(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.gis'));

        $response->assertStatus(200);
        $response->assertSee('Peta Geospasial Perikanan Tangkap', false);
        $response->assertSee('fisheries-map', false);
        $response->assertSee('GFW Monitoring', false);
        $response->assertDontSee('gateway.api.globalfishingwatch.org', false);
    }

    public function test_gis_workspace_renders_spatial_filter_controls(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.gis'));

        $response->assertStatus(200);
        $response->assertSee('Filter Spasial', false);
        $response->assertSee('WPP-NRI', false);
        $response->assertSee('Pelabuhan/TPI', false);
    }

    public function test_gis_workspace_renders_wpp_analysis(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard.gis'));

        $response->assertStatus(200);
        $response->assertSee('Analisis Spasial Multi-Dimensi WPP-NRI', false);
        $response->assertSee('WPP Terpantau', false);
    }

    public function test_gis_workspace_does_not_leak_gfw_token(): void
    {
        $token = config('services.gfw.api_token') ?: config('services.gfw.token');
        $response = $this->actingAs($this->user)->get(route('dashboard.gis'));

        $response->assertStatus(200);
        if (! empty($token)) {
            $response->assertDontSee($token, false);
        }
    }
}
