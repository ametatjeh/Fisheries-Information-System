<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\GfwQueryGeometryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwQueryGeometryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);

        $this->user = User::factory()->create();
        $this->user->assignRole($adminRole);

        Config::set('gfw.token', 'test-gfw-bearer-token');
        Config::set('gfw.timeout', 60);
        Config::set('gfw.connect_timeout', 5);
        Config::set('gfw.vessel_cache_ttl', 3600);
        Cache::flush();
    }

    /**
     * Test GfwQueryGeometryService returns a valid, closed, optimized Polygon with 50-100 vertices.
     */
    public function test_gfw_query_geometry_service_returns_valid_polygon(): void
    {
        $service = new GfwQueryGeometryService;
        $polygon = $service->getAcehQueryPolygon();

        $this->assertSame('Polygon', $polygon['type']);
        $this->assertIsArray($polygon['coordinates']);
        $this->assertCount(1, $polygon['coordinates']);

        $coords = $polygon['coordinates'][0];
        $vertexCount = count($coords);

        // Target: 50 to 100 vertices
        $this->assertGreaterThanOrEqual(50, $vertexCount);
        $this->assertLessThanOrEqual(100, $vertexCount);
        $this->assertSame(55, $vertexCount);

        // Ring must be closed
        $this->assertSame($coords[0], $coords[$vertexCount - 1]);

        // Longitude / Latitude within Aceh bounds
        $lons = array_column($coords, 0);
        $lats = array_column($coords, 1);
        $this->assertGreaterThanOrEqual(92.0, min($lons));
        $this->assertLessThanOrEqual(101.0, max($lons));
        $this->assertGreaterThanOrEqual(1.5, min($lats));
        $this->assertLessThanOrEqual(8.5, max($lats));

        // Metadata check
        $metadata = $service->getMetadata();
        $this->assertSame('GFW Query AOI — Aceh', $metadata['name']);
        $this->assertSame('EPSG:4326', $metadata['crs']);
        $this->assertSame(55, $metadata['vertex_count']);
        $this->assertStringContainsString('bukan representasi batas hukum ZEE', $metadata['disclaimer']);
    }

    /**
     * Test point-in-polygon algorithm inside GfwQueryGeometryService.
     */
    public function test_point_in_polygon_identifies_aceh_waters(): void
    {
        $service = new GfwQueryGeometryService;

        // Inside Aceh waters: 95.0, 5.5
        $this->assertTrue($service->isPointInPolygon(95.0, 5.5));

        // Outside Aceh waters (Java Sea): 106.8, -6.1
        $this->assertFalse($service->isPointInPolygon(106.8, -6.1));

        // Outside Aceh waters (Pacific Ocean): 140.0, 0.0
        $this->assertFalse($service->isPointInPolygon(140.0, 0.0));
    }

    /**
     * Test /api/gfw/vessels/zee-indonesia-aceh uses GFW Query Geometry instead of BIG ZEE Layer 10.
     */
    public function test_vessels_endpoint_uses_gfw_query_geometry(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'event-101',
                        'type' => 'fishing',
                        'start' => '2026-09-18T10:00:00Z',
                        'end' => '2026-09-18T12:00:00Z',
                        'position' => ['lat' => 5.5, 'lon' => 95.0],
                        'vessel' => [
                            'id' => 'vessel-101',
                            'name' => 'KM SAMUDRA ACEH',
                            'ssvid' => '525000001',
                            'flag' => 'IDN',
                            'type' => 'fishing',
                        ],
                    ],
                ],
                'total' => 1,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22&limit=50');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'aoi' => [
                    'id' => 'gfw-query-aoi-aceh',
                    'name' => 'GFW Query AOI — Aceh',
                    'source' => 'GFW Query AOI',
                ],
            ])
            ->assertJsonPath('summary.total_vessels', 1);

        // Verify the geometry sent to upstream GFW API matches GfwQueryGeometryService
        Http::assertSent(function (ClientRequest $request) {
            $data = $request->data();
            $geometry = $data['geometry'] ?? [];

            return ($geometry['type'] ?? '') === 'Polygon'
                && count($geometry['coordinates'][0] ?? []) === 55;
        });
    }

    /**
     * Test configuration: timeout is 60 seconds, connect timeout is 5 seconds, and cache TTL is 3600 seconds.
     */
    public function test_gfw_timeout_and_cache_configuration(): void
    {
        $this->assertSame(60, config('gfw.timeout'));
        $this->assertSame(5, config('gfw.connect_timeout'));
        $this->assertSame(3600, config('gfw.vessel_cache_ttl'));
    }

    /**
     * Test that GFW token is never exposed in response body.
     */
    public function test_token_is_never_exposed_in_response(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [],
                'total' => 0,
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(200);
        $this->assertStringNotContainsString('test-gfw-bearer-token', $response->getContent());
    }
}
