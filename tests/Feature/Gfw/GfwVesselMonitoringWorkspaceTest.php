<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwVesselMonitoringWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected User $gisUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();
        Config::set('gfw.api_key', 'test-secret-gfw-token-vessel-workspace');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        // Create permissions
        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);

        $this->gisUser = User::factory()->create();
        $this->gisUser->givePermissionTo($gisPermission);

        $this->regularUser = User::factory()->create();
    }

    public function test_guest_is_redirected_to_login_when_accessing_gfw_vessels(): void
    {
        $response = $this->get('/gfw/vessels');
        $response->assertRedirect('/login');
    }

    public function test_user_without_gis_permission_is_forbidden(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/gfw/vessels');
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_gfw_vessels_workspace(): void
    {
        $response = $this->actingAs($this->gisUser)->get('/gfw/vessels');

        $response->assertStatus(200)
            ->assertSee('GFW Vessel Monitoring')
            ->assertSee('ZEE Indonesia Kawasan Aceh')
            ->assertSee('maplibre-gl@4.7.1')
            ->assertSee('gfw-vessels-map')
            ->assertSee('vessel-search-input')
            ->assertSee('filter-start-date')
            ->assertSee('filter-end-date')
            ->assertSee('vessel-list-container')
            ->assertSee('vessel-detail-card')
            ->assertSee('Pemberitahuan Latensi & AOI Spasial')
            ->assertSee('Observed Track');
    }

    public function test_sidebar_renders_gfw_vessel_monitoring_and_active_state(): void
    {
        $response = $this->actingAs($this->gisUser)->get(route('gfw.vessels'));

        $response->assertStatus(200)
            ->assertSee(route('gfw.monitoring'))
            ->assertSee(route('gfw.vessels'))
            ->assertSee('GFW Monitoring')
            ->assertSee('GFW Vessel Monitoring');
    }

    public function test_gfw_vessel_workspace_does_not_leak_api_token(): void
    {
        $secretToken = 'test-secret-gfw-token-vessel-workspace';
        $response = $this->actingAs($this->gisUser)->get('/gfw/vessels');

        $response->assertStatus(200);
        $response->assertDontSee($secretToken);
        $response->assertDontSee('Bearer');
    }

    public function test_internal_vessel_search_and_track_apis_for_workspace(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 1,
                'entries' => [
                    [
                        'id' => 'vessel-aceh-gfw-01',
                        'shipname' => 'KM MEULABOH MAJU',
                        'mmsi' => '525112233',
                        'imo' => '9123456',
                        'flag' => 'IDN',
                        'vesselType' => 'fishing',
                        'geartype' => 'purse_seine',
                        'lengthM' => 32.0,
                        'tonnageGt' => 150.0,
                    ],
                ],
            ], 200),
            'https://gateway.api.globalfishingwatch.org/v3/vessels/vessel-aceh-gfw-01/tracks*' => Http::response([
                'entries' => [
                    [
                        'lat' => 5.58,
                        'lon' => 95.31,
                        'timestamp' => '2026-09-18T10:00:00Z',
                        'speedKnots' => 7.5,
                        'distanceKm' => 10.0,
                        'hours' => 2.0,
                    ],
                    [
                        'lat' => 5.65,
                        'lon' => 95.38,
                        'timestamp' => '2026-09-18T12:00:00Z',
                        'speedKnots' => 8.0,
                        'distanceKm' => 14.0,
                        'hours' => 2.0,
                    ],
                ],
            ], 200),
        ]);

        // 1. Test search endpoint
        $searchRes = $this->getJson('/api/gfw/vessels/search?query=MEULABOH');
        $searchRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total' => 1,
            ])
            ->assertJsonPath('data.0.gfw_vessel_id', 'vessel-aceh-gfw-01')
            ->assertJsonPath('data.0.name', 'KM MEULABOH MAJU')
            ->assertJsonPath('data.0.mmsi', '525112233')
            ->assertJsonPath('data.0.flag', 'IDN');

        // 2. Test track endpoint
        $trackRes = $this->getJson('/api/gfw/activity/vessels/vessel-aceh-gfw-01?start_date=2026-09-15&end_date=2026-09-20');
        $trackRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total' => 2,
            ])
            ->assertJsonPath('data.0.gfw_vessel_id', 'vessel-aceh-gfw-01')
            ->assertJsonPath('data.0.latitude', 5.58)
            ->assertJsonPath('data.0.longitude', 95.31)
            ->assertJsonPath('data.0.speed_knots', 7.5);
    }
}
