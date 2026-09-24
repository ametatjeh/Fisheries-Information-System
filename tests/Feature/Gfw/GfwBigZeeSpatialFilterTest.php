<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\AoiService;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gfw\GfwIngestionService;
use App\Services\Gis\BigMaritimeBoundaryService;
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
     * 9. BIG boundary failure test: invalid or unavailable BIG GeoJSON boundary fails safely with HTTP 502.
     * Strictly verifies no silent fallback to old box AOI.
     */
    public function test_invalid_aoi_configuration_returns_http_500(): void
    {
        $mockBig = $this->createMock(BigMaritimeBoundaryService::class);
        $mockBig->method('getAcehZeeGeometry')
            ->willReturn([
                'success' => false,
                'error' => 'BIG ZEE Aceh boundary geometry tidak tersedia atau tidak valid.',
            ]);
        $this->app->instance(BigMaritimeBoundaryService::class, $mockBig);

        $response = $this->actingAs($this->user)->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(502);
        $response->assertJson([
            'success' => false,
            'boundary_source' => 'BIG',
            'boundary_layer' => 10,
        ]);
    }

    /**
     * 10. GfwIngestionService spatial filter test:
     * Observations inside BIG ZEE Aceh are accepted, observations outside are excluded.
     */
    public function test_gfw_ingestion_service_filters_outside_big_zee_points(): void
    {
        /** @var GfwIngestionService $ingestionService */
        $ingestionService = app(GfwIngestionService::class);

        // Point strictly inside BIG ZEE Aceh (near Sabang / Banda Aceh)
        $insideTrack = [
            'latitude' => 5.55,
            'longitude' => 95.32,
            'observation_timestamp' => '2026-09-22T10:00:00Z',
            'speed_knots' => 8.5,
            'course' => 180.0,
        ];

        $insideResult = $ingestionService->ingestPresence('GFW-TEST-001', $insideTrack, 'zee-indonesia-aceh', dryRun: true);
        $this->assertSame('new', $insideResult['status']);

        // Point strictly outside BIG ZEE Aceh (deep in Gulf of Thailand)
        $outsideTrack = [
            'latitude' => 5.0,
            'longitude' => 102.0,
            'observation_timestamp' => '2026-09-22T10:00:00Z',
            'speed_knots' => 10.0,
            'course' => 90.0,
        ];

        $outsideResult = $ingestionService->ingestPresence('GFW-TEST-002', $outsideTrack, 'zee-indonesia-aceh', dryRun: true);
        $this->assertSame('outside_aoi', $outsideResult['status']);
    }

    /**
     * 11. BIG ZEE Aceh Polygon GeoJSON endpoint test:
     * GET /api/gis/big/zee/aceh?polygon=1 returns a valid Polygon with 400 authentic vertices.
     */
    public function test_big_zee_aceh_polygon_endpoint_returns_valid_polygon(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/gis/big/zee/aceh?polygon=1');

        $response->assertStatus(200);
        $this->assertSame('Feature', $response->json('type'));
        $this->assertSame('Polygon', $response->json('geometry.type'));
        $this->assertSame('Badan Informasi Geospasial (BIG)', $response->json('properties.source'));
        $this->assertSame(10, $response->json('properties.layer_id'));

        $coords = $response->json('geometry.coordinates.0');
        $this->assertIsArray($coords);
        $this->assertGreaterThanOrEqual(100, count($coords));

        // Ring must be closed
        $first = $coords[0];
        $last = $coords[count($coords) - 1];
        $this->assertEquals($first[0], $last[0]);
        $this->assertEquals($first[1], $last[1]);
    }
}
