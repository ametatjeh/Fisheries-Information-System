<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\GfwApiService;
use App\Services\GFWService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GfwStage11ConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_map_page_contains_gfw_monitoring_cta_and_gis_workspace_identity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('dashboard.gis'));

        $response->assertStatus(200);
        // Header & Navigation
        $response->assertSee(route('gfw.monitoring'), false);
        $response->assertSee('GFW Monitoring', false);
        $response->assertSee('Peta Geospasial Perikanan Tangkap', false);

        // Security
        $response->assertDontSee('gateway.api.globalfishingwatch.org', false);
        $token = config('services.gfw.token') ?: config('gfw.token');
        if (! empty($token)) {
            $response->assertDontSee($token, false);
        }
    }

    public function test_gfw_monitoring_page_contains_map_cta_and_dedicated_workspace_identity(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get(route('gfw.monitoring'));

        $response->assertStatus(200);
        // Header & Navigation
        $response->assertSee(route('dashboard.gis'), false);
        $response->assertSee('Peta Terpadu GIS', false);
        $response->assertSee('GFW Vessel Monitoring', false);
        $response->assertSee('Global Fishing Watch (GFW) v3 Integration', false);

        // Security
        $response->assertDontSee('gateway.api.globalfishingwatch.org', false);
        $token = config('services.gfw.token') ?: config('gfw.token');
        if (! empty($token)) {
            $response->assertDontSee($token, false);
        }
    }

    public function test_gfw_service_is_resolvable_as_canonical_service(): void
    {
        $gfwService = app(GFWService::class);
        $this->assertInstanceOf(GFWService::class, $gfwService);

        $gfwApiService = app(GfwApiService::class);
        $this->assertInstanceOf(GfwApiService::class, $gfwApiService);
    }

    public function test_neutral_terminology_enforced_in_both_workspaces(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        // 1. GIS Workspace page
        $mapResponse = $this->actingAs($user)->get(route('dashboard.gis'));
        $mapResponse->assertStatus(200);
        $mapResponse->assertDontSee('Illegal Fishing Terbukti', false);
        $mapResponse->assertDontSee('Kapal Pelanggar Hukum', false);

        // 2. GFW Monitoring page
        $gfwResponse = $this->actingAs($user)->get(route('gfw.monitoring'));
        $gfwResponse->assertStatus(200);
        $gfwResponse->assertDontSee('Illegal Fishing Terbukti', false);
        $gfwResponse->assertDontSee('Kapal Pelanggar Hukum', false);
        $gfwResponse->assertSee('Indikasi analitik algoritma pergerakan AIS/VMS', false);
    }
}
