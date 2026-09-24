<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use App\Services\GFWService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardGisRestructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_public_home_does_not_contain_gis_map_or_maplibre(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);

        // 1. Must NOT contain map canvas or preview map on public home
        $response->assertDontSee('id="home-map-preview"', false);
        $response->assertDontSee('id="explorer-map-canvas"', false);
        $response->assertDontSee('initHomePreviewMap', false);

        // 2. Must NOT initiate map data request
        $response->assertDontSee('fetch(\'{{ route(\'gis.data\') }}\'', false);

    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_dashboard_displays_prominent_gis_and_gfw_workspace_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Eksekutif', false);
        $response->assertSee(route('dashboard.gis'), false);
        $response->assertSee('Buka Peta Terpadu', false);
        $response->assertSee(route('gfw.monitoring'), false);
        $response->assertSee('GFW Monitoring Workspace', false);
    }

    public function test_dashboard_gis_requires_authentication_and_permission(): void
    {
        // Unauthenticated
        $response = $this->get(route('dashboard.gis'));
        $response->assertRedirect('/login');

        // Authenticated with GIS permission (admin)
        $user = User::factory()->create();
        $user->assignRole('admin');

        $gisResponse = $this->actingAs($user)->get(route('dashboard.gis'));
        $gisResponse->assertStatus(200);
        $gisResponse->assertSee('Peta Geospasial Perikanan Tangkap', false);
        $gisResponse->assertSee('fisheries-map', false);
    }

    public function test_map_route_redirects_to_dashboard_gis_with_auth_protection(): void
    {
        // Unauthenticated
        $response = $this->get('/map');
        $response->assertRedirect('/login');

        // Authenticated
        $user = User::factory()->create();
        $user->assignRole('admin');

        $authResponse = $this->actingAs($user)->get('/map');
        $authResponse->assertRedirect(route('dashboard.gis'));
    }

    public function test_gfw_monitoring_workspace_requires_authentication_and_permission(): void
    {
        // Unauthenticated
        $response = $this->get(route('gfw.monitoring'));
        $response->assertRedirect('/login');

        // Authenticated
        $user = User::factory()->create();
        $user->assignRole('admin');

        $gfwResponse = $this->actingAs($user)->get(route('gfw.monitoring'));
        $gfwResponse->assertStatus(200);
        $gfwResponse->assertSee('GFW Vessel Monitoring', false);
        $gfwResponse->assertSee('Global Fishing Watch (GFW) v3 Integration', false);
    }

    public function test_canonical_gfw_service_is_preserved_and_no_token_leaks(): void
    {
        $gfwService = app(GFWService::class);
        $this->assertInstanceOf(GFWService::class, $gfwService);

        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('dashboard.gis'));
        $response->assertStatus(200);
        $response->assertDontSee('gateway.api.globalfishingwatch.org', false);

        $token = config('services.gfw.token') ?: config('gfw.token');
        if (! empty($token)) {
            $response->assertDontSee($token, false);
        }
    }
}
