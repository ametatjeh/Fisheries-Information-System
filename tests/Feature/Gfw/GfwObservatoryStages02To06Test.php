<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\GfwActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwObservatoryStages02To06Test extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('gfw.api_key', 'test-key-stage-02-06');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);
        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($gisPermission);
        $this->user->assignRole($adminRole);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    [
                        'id' => 'evt-001',
                        'type' => 'fishing',
                        'start' => '2026-09-22T02:00:00Z',
                        'end' => '2026-09-22T08:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 95.80],
                        'vessel' => [
                            'id' => 'vessel-idn-01',
                            'name' => 'KM SAMUDRA ACEH',
                            'ssvid' => '525001111',
                            'imo' => '9111111',
                            'flag' => 'IDN',
                            'type' => 'fishing',
                        ],
                    ],
                    [
                        'id' => 'evt-002',
                        'type' => 'port_visit',
                        'start' => '2026-09-21T10:00:00Z',
                        'end' => '2026-09-21T14:00:00Z',
                        'position' => ['lat' => 5.58, 'lon' => 95.32],
                        'vessel' => [
                            'id' => 'vessel-mys-02',
                            'name' => 'CARGO MALACCA',
                            'ssvid' => '533002222',
                            'flag' => 'MYS',
                            'type' => 'cargo',
                        ],
                    ],
                ],
            ], 200),
            'https://gateway.api.globalfishingwatch.org/*' => Http::response(['success' => true], 200),
        ]);
    }

    /**
     * STAGE 02: Live monitoring metadata and status normalization.
     */
    public function test_vessels_endpoint_returns_live_metadata_and_status_normalization(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'live',
            'last_updated',
            'data_age_seconds',
            'aoi' => [
                'id',
                'name',
                'source',
                'crs',
            ],
            'period' => [
                'start',
                'end',
            ],
            'summary' => [
                'total_vessels',
                'live_vessels',
                'recent_vessels',
                'stale_vessels',
            ],
            'vessels',
        ]);

        $data = $response->json();
        $this->assertTrue($data['live']);
        $this->assertIsInt($data['data_age_seconds']);
        $this->assertNotEmpty($data['last_updated']);
        $this->assertEquals('BIG', $data['aoi']['source']);
        $this->assertEquals('EPSG:4326', $data['aoi']['crs']);

        // Check each vessel has normalized status
        if (! empty($data['vessels'])) {
            $vessel = $data['vessels'][0];
            $this->assertArrayHasKey('status', $vessel);
            $this->assertContains($vessel['status'], ['LIVE', 'RECENT', 'STALE']);
            $this->assertArrayHasKey('data_age_seconds', $vessel);
        }
    }

    /**
     * STAGE 03: Vessel track endpoint validation and response structure.
     */
    public function test_vessel_track_endpoint_validates_date_range(): void
    {
        // More than 7 days should fail with 422
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/test-vessel-123/track?start_date=2026-09-01&end_date=2026-09-15');

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'success' => false,
        ]);

        // End date before start date should fail with 422
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/test-vessel-123/track?start_date=2026-09-15&end_date=2026-09-10');

        $response->assertStatus(422);
    }

    public function test_vessel_track_endpoint_returns_valid_track_structure(): void
    {
        $mockActivityService = $this->createMock(GfwActivityService::class);
        $mockActivityService->method('getVesselActivity')
            ->willReturn([
                'success' => true,
                'data' => [
                    ['lon' => 95.3, 'lat' => 5.5, 'timestamp' => '2026-09-20T10:00:00Z', 'speed' => 8.2],
                    ['lon' => 95.4, 'lat' => 5.6, 'timestamp' => '2026-09-20T11:00:00Z', 'speed' => 7.9],
                    ['lon' => 95.5, 'lat' => 5.7, 'timestamp' => '2026-09-20T12:00:00Z', 'speed' => 8.0],
                ],
            ]);
        $this->app->instance(GfwActivityService::class, $mockActivityService);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/vessel-abc/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'vessel_id' => 'vessel-abc',
            'sufficient' => true,
        ]);

        $data = $response->json();
        $this->assertEquals('LineString', $data['line_geojson']['type']);
        $this->assertCount(3, $data['line_geojson']['coordinates']);
        $this->assertEquals('FeatureCollection', $data['points_geojson']['type']);
        $this->assertCount(3, $data['points_geojson']['features']);
        $this->assertEquals(3, $data['track_info']['position_count']);
        $this->assertEquals('2026-09-20T10:00:00Z', $data['track_info']['first_detected']);
        $this->assertEquals('2026-09-20T12:00:00Z', $data['track_info']['last_detected']);
    }

    public function test_vessel_track_handles_insufficient_data(): void
    {
        $mockActivityService = $this->createMock(GfwActivityService::class);
        $mockActivityService->method('getVesselActivity')
            ->willReturn([
                'success' => true,
                'data' => [
                    ['lon' => 95.3, 'lat' => 5.5, 'timestamp' => '2026-09-20T10:00:00Z'],
                ],
            ]);
        $this->app->instance(GfwActivityService::class, $mockActivityService);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/vessel-single-point/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertFalse($data['sufficient']);
        $this->assertEquals('Track data insufficient', $data['track_info']['note']);
    }

    /**
     * STAGE 04 & 06: Dashboard Summary API and Activity & Alerts
     */
    public function test_dashboard_summary_endpoint(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/dashboard?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'aoi' => ['id', 'name', 'source', 'crs'],
            'period' => ['start', 'end'],
            'kpi' => [
                'detected_vessels',
                'live_recent',
                'fishing_activity',
                'encounters',
                'loitering',
                'port_visits',
            ],
            'activity_feed',
            'alerts',
            'summary',
            'vessels',
            'last_updated',
        ]);

        $data = $response->json();
        $this->assertEquals('zee-indonesia-aceh', $data['aoi']['id']);
        $this->assertEquals('BIG', $data['aoi']['source']);
        $this->assertEquals('EPSG:4326', $data['aoi']['crs']);

        // Check alerts taxonomy are factual not criminal
        foreach ($data['alerts'] as $alert) {
            $this->assertStringNotContainsStringIgnoringCase('illegal', $alert['title'] ?? '');
            $this->assertStringNotContainsStringIgnoringCase('suspicious', $alert['title'] ?? '');
        }
    }

    /**
     * STAGE 06: Web view for /gfw/dashboard
     */
    public function test_dashboard_web_route_renders_correctly(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/gfw/dashboard');

        $response->assertStatus(200);
        $response->assertSee('GFW Operational Dashboard');
        $response->assertSee('ZEE Indonesia — Kawasan Aceh');
        $response->assertSee('Batas ZEE: BIG');
        $response->assertSee('Global Fishing Watch');
        $response->assertSee('gfw-dashboard-map');
        $response->assertSee('btn-toggle-live');
        $response->assertSee('dashboard-vessel-detail');
    }

    /**
     * SECURITY: GFW token must NEVER be exposed in responses or HTML.
     */
    public function test_gfw_api_token_never_leaks_in_responses(): void
    {
        $fakeToken = 'gfw-secret-production-token-never-expose-xyz987';
        config([
            'services.gfw.token' => $fakeToken,
            'gfw.api_key' => $fakeToken,
        ]);

        $endpoints = [
            '/api/gfw/vessels/zee-indonesia-aceh',
            '/api/gfw/dashboard',
            '/api/gfw/vessels/test-vessel-abc/track',
            '/gfw/vessels',
            '/gfw/dashboard',
        ];

        foreach ($endpoints as $url) {
            $response = $this->actingAs($this->user)->get($url);
            $this->assertStringNotContainsString($fakeToken, $response->getContent(), "Token leaked in: {$url}");
        }
    }
}
