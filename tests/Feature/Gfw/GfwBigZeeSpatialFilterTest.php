<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwActivityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwBigZeeSpatialFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-secret-spatial-token-123');
        Config::set('gfw.fishing_events_dataset', 'public-global-fishing-events:latest');

        $permission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($permission);
    }

    /**
     * 1. AOI Source validation: BIG source, EPSG:4326, valid Polygon.
     */
    public function test_aoi_metadata_and_big_polygon_source(): void
    {
        $aoiService = app(AoiService::class);
        $summary = $aoiService->getZeeIndonesiaAcehSummary();

        $this->assertTrue($summary['success']);
        $this->assertSame('BIG', $summary['source']);
        $this->assertSame('EPSG:4326', $summary['crs']);
        $this->assertSame('Polygon', $summary['geometry_type']);
        $this->assertSame('zee-indonesia-aceh', $summary['id']);

        // Check geometry coordinates
        $geoJson = $aoiService->getZeeIndonesiaAcehGeometry();
        $validation = $aoiService->validateGeoJsonStructure($geoJson);
        $this->assertTrue($validation['valid']);
    }

    /**
     * 2. Spatial filtering: vessel A (inside), vessel B (outside), vessel C (inside).
     * Expected: A and C included, B excluded.
     */
    public function test_spatial_filtering_includes_inside_and_excludes_outside(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 3,
                'entries' => [
                    [
                        'id' => 'evt-inside-a',
                        'type' => 'fishing',
                        'start' => '2026-09-01T02:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 95.80], // Inside BIG ZEE
                        'vessel' => [
                            'id' => 'vessel-a',
                            'name' => 'VESSEL A INSIDE',
                            'ssvid' => '525111001',
                            'flag' => 'IDN',
                            'type' => 'fishing',
                        ],
                    ],
                    [
                        'id' => 'evt-outside-b',
                        'type' => 'fishing',
                        'start' => '2026-09-01T03:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 102.00], // Far outside BIG ZEE
                        'vessel' => [
                            'id' => 'vessel-b',
                            'name' => 'VESSEL B OUTSIDE',
                            'ssvid' => '525222002',
                            'flag' => 'THA',
                            'type' => 'fishing',
                        ],
                    ],
                    [
                        'id' => 'evt-inside-c',
                        'type' => 'carrier',
                        'start' => '2026-09-01T04:00:00Z',
                        'position' => ['lat' => 4.80, 'lon' => 97.20], // Inside BIG ZEE
                        'vessel' => [
                            'id' => 'vessel-c',
                            'name' => 'VESSEL C INSIDE',
                            'ssvid' => '525333003',
                            'flag' => 'MYS',
                            'type' => 'carrier',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-01&end=2026-09-07');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 2);
        $response->assertJsonPath('aoi.source', 'BIG');

        $vessels = $response->json('vessels');
        $this->assertCount(2, $vessels);

        $vesselIds = array_column($vessels, 'id');
        $this->assertContains('vessel-a', $vesselIds);
        $this->assertContains('vessel-c', $vesselIds);
        $this->assertNotContains('vessel-b', $vesselIds);
    }

    /**
     * 3. Boundary test: strictly inside, strictly outside, boundary edge, and vertex.
     */
    public function test_boundary_and_point_in_polygon_consistency(): void
    {
        $aoiService = app(AoiService::class);

        // Point strictly inside
        $this->assertTrue($aoiService->isPointInAoi(95.5, 4.0));

        // Point strictly outside
        $this->assertFalse($aoiService->isPointInAoi(102.0, 5.0));
        $this->assertFalse($aoiService->isPointInAoi(92.0, 4.0));

        // Point directly on western boundary segment (lon: 94.5, lat between 1.8 and 6.2)
        $this->assertTrue($aoiService->isPointInAoi(94.5, 4.0));

        // Point directly on vertex (94.5, 1.8)
        $this->assertTrue($aoiService->isPointInAoi(94.5, 1.8));
    }

    /**
     * 4. Multiple positions test:
     * A inside (02:00), A outside (04:00), A inside (06:00).
     * Expected: A is detected, but only inside positions are recorded.
     * Latest position must be the inside observation at 06:00, not the outside one at 04:00.
     */
    public function test_multiple_positions_only_keeps_valid_zee_positions(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 3,
                'entries' => [
                    [
                        'id' => 'evt-pos-1',
                        'type' => 'fishing',
                        'start' => '2026-09-01T02:00:00Z',
                        'end' => '2026-09-01T02:30:00Z',
                        'position' => ['lat' => 5.20, 'lon' => 95.80], // Inside
                        'vessel' => [
                            'id' => 'vessel-multi',
                            'name' => 'KM MULTI POS',
                            'flag' => 'IDN',
                        ],
                    ],
                    [
                        'id' => 'evt-pos-2',
                        'type' => 'fishing',
                        'start' => '2026-09-01T04:00:00Z',
                        'end' => '2026-09-01T04:30:00Z',
                        'position' => ['lat' => 5.20, 'lon' => 105.00], // Far outside (e.g. Malaysia/South China Sea)
                        'vessel' => [
                            'id' => 'vessel-multi',
                            'name' => 'KM MULTI POS',
                            'flag' => 'IDN',
                        ],
                    ],
                    [
                        'id' => 'evt-pos-3',
                        'type' => 'fishing',
                        'start' => '2026-09-01T06:00:00Z',
                        'end' => '2026-09-01T06:30:00Z',
                        'position' => ['lat' => 5.35, 'lon' => 95.90], // Inside (latest inside)
                        'vessel' => [
                            'id' => 'vessel-multi',
                            'name' => 'KM MULTI POS',
                            'flag' => 'IDN',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-01&end=2026-09-07');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 1);

        $vessels = $response->json('vessels');
        $this->assertCount(1, $vessels);
        $vessel = $vessels[0];

        // Ensure latest position is the inside observation at 06:00, not the outside one
        $this->assertEquals(5.35, $vessel['lat']);
        $this->assertEquals(95.90, $vessel['lon']);
        $this->assertSame('2026-09-01T06:30:00Z', $vessel['last_seen']);
    }

    /**
     * 5. Events filtering: events inside BIG ZEE are included, outside are excluded.
     */
    public function test_events_filtering_by_big_zee_polygon(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    [
                        'id' => 'ev-inside',
                        'type' => 'fishing',
                        'start' => '2026-09-01T02:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 95.80], // Inside
                    ],
                    [
                        'id' => 'ev-outside',
                        'type' => 'fishing',
                        'start' => '2026-09-01T03:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 105.00], // Outside
                    ],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/events/zee-indonesia-aceh');

        $response->assertStatus(200);
        $response->assertJsonPath('returned_count', 1);

        $events = $response->json('events');
        $this->assertCount(1, $events);
        $this->assertSame('ev-inside', $events[0]['id']);
    }

    /**
     * 6. Empty result: valid BIG AOI + valid GFW response + 0 vessels = HTTP 200.
     */
    public function test_empty_result_returns_200_with_informative_message(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-01&end=2026-09-07');

        $response->assertStatus(200);
        $response->assertJsonPath('summary.total_vessels', 0);
        $response->assertJsonPath('message', 'No vessel detected in BIG ZEE Aceh for selected period.');
        $response->assertJsonPath('aoi.source', 'BIG');
    }

    /**
     * 7. Track endpoint: filters out positions outside BIG ZEE Aceh by default.
     */
    public function test_track_endpoint_filters_points_outside_big_zee(): void
    {
        $mockActivity = $this->createMock(GfwActivityService::class);
        $mockActivity->method('getVesselActivity')
            ->willReturn([
                'success' => true,
                'data' => [
                    ['lon' => 95.3, 'lat' => 5.5, 'timestamp' => '2026-09-20T10:00:00Z', 'speed' => 8.0], // Inside
                    ['lon' => 105.0, 'lat' => 5.5, 'timestamp' => '2026-09-20T11:00:00Z', 'speed' => 8.0], // Outside
                    ['lon' => 95.5, 'lat' => 5.7, 'timestamp' => '2026-09-20T12:00:00Z', 'speed' => 8.0], // Inside
                ],
            ]);
        $this->app->instance(GfwActivityService::class, $mockActivity);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/vessel-test/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $response->assertJsonPath('track_scope', 'ZEE Aceh Track');
        $response->assertJsonPath('points_count', 2); // Only the 2 inside points
        $response->assertJsonPath('aoi.source', 'BIG');
    }

    /**
     * 8. Security: GFW token is never exposed in response.
     */
    public function test_gfw_token_not_leaked_in_response(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringNotContainsString('test-secret-spatial-token-123', (string) $content);
    }

    /**
     * 9. AOI failure test: invalid or missing BIG GeoJSON configuration returns HTTP 500.
     */
    public function test_invalid_aoi_configuration_returns_http_500(): void
    {
        $mockAoi = $this->createMock(AoiService::class);
        $mockAoi->method('validateAoiOrThrow')
            ->willThrowException(new \RuntimeException('BIG ZEE Aceh AOI configuration is invalid: File not found.'));
        $this->app->instance(AoiService::class, $mockAoi);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(500);
        $response->assertJson([
            'success' => false,
            'message' => 'BIG ZEE Aceh AOI configuration is invalid.',
        ]);
    }
}
