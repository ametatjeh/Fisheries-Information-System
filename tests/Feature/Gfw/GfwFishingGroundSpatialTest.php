<?php

namespace Tests\Feature\Gfw;

use App\Models\FishingGround;
use App\Models\Wppnri;
use App\Services\Gfw\GfwFishingGroundSpatialAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwFishingGroundSpatialTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-gfw-token');
    }

    public function test_spatial_endpoint_is_accessible_and_returns_valid_contract(): void
    {
        $wpp = Wppnri::create(['code' => '571', 'name' => 'Selat Malaka']);
        FishingGround::create([
            'wppnri_id' => $wpp->id,
            'code' => 'FG-001',
            'name' => 'Perairan Ulee Lheue',
            'latitude' => null,
            'longitude' => null,
            'is_active' => true,
        ]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    [
                        'id' => 'ev-1',
                        'type' => 'fishing',
                        'start' => '2026-09-01T04:00:00Z',
                        'end' => '2026-09-01T06:00:00Z',
                        'position' => ['lat' => 5.5, 'lon' => 95.3],
                        'vessel' => ['name' => 'KM BERKAH', 'ssvid' => '525111222', 'flag' => 'IDN'],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/spatial/fishing-grounds?start_date=2026-09-01&end_date=2026-09-07');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'Global Fishing Watch',
                'analysis' => 'GFW Event × Master Fishing Ground Aceh',
                'aoi' => 'ZEE Indonesia - Kawasan Aceh',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
                'event_count' => 2,
                'returned_count' => 1,
                'spatial_match_count' => 0,
                'fishing_grounds' => [
                    [
                        'code' => 'FG-001',
                        'name' => 'Perairan Ulee Lheue',
                        'geometry_status' => 'NOT_READY',
                        'matched_event_count' => 0,
                    ],
                ],
                'matches' => [],
            ])
            ->assertJsonStructure([
                'success',
                'source',
                'analysis',
                'aoi',
                'start_date',
                'end_date',
                'event_count',
                'returned_count',
                'spatial_match_count',
                'pagination' => ['limit', 'offset', 'total', 'next_offset'],
                'fishing_grounds',
                'matches',
                'disclaimer',
            ]);
    }

    public function test_point_in_polygon_identifies_matching_event_with_within_relation(): void
    {
        // Define a Polygon covering lat 5.0..6.0, lon 95.0..96.0
        $polygonGeometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [
                    [95.0, 5.0],
                    [96.0, 5.0],
                    [96.0, 6.0],
                    [95.0, 6.0],
                    [95.0, 5.0],
                ],
            ],
        ];

        $service = app(GfwFishingGroundSpatialAnalysisService::class);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    [
                        'id' => 'ev-inside',
                        'type' => 'fishing',
                        'start' => '2026-09-01T05:00:00Z',
                        'end' => '2026-09-01T07:00:00Z',
                        'position' => ['lat' => 5.5, 'lon' => 95.5],
                        'vessel' => ['name' => 'KM ACEH SATU', 'ssvid' => '525000001', 'flag' => 'IDN'],
                        'dataset' => 'public-global-fishing-events:latest',
                    ],
                    [
                        'id' => 'ev-outside',
                        'type' => 'fishing',
                        'position' => ['lat' => 7.5, 'lon' => 98.0],
                    ],
                ],
            ], 200),
        ]);

        $result = $service->analyze('2026-09-01', '2026-09-07', [
            'fishing_grounds' => [
                [
                    'id' => 10,
                    'name' => 'Fishing Ground Test Alpha',
                    'code' => 'FG-ALPHA',
                    'geometry' => $polygonGeometry,
                ],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['spatial_match_count']);
        $this->assertCount(1, $result['matches']);
        $this->assertSame('ev-inside', $result['matches'][0]['event_id']);
        $this->assertSame(10, $result['matches'][0]['fishing_ground_id']);
        $this->assertSame('Fishing Ground Test Alpha', $result['matches'][0]['fishing_ground_name']);
        $this->assertSame('within', $result['matches'][0]['spatial_relation']);
        $this->assertSame('KM ACEH SATU', $result['matches'][0]['vessel_name']);
        $this->assertSame(5.5, $result['matches'][0]['latitude']);
        $this->assertSame(95.5, $result['matches'][0]['longitude']);
        $this->assertSame('READY', $result['fishing_grounds'][0]['geometry_status']);
        $this->assertSame(1, $result['fishing_grounds'][0]['matched_event_count']);
    }

    public function test_multipolygon_geometry_is_supported(): void
    {
        $multiPolygonGeometry = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                // Polygon 1: 95.0..95.5, 5.0..5.5
                [
                    [
                        [95.0, 5.0],
                        [95.5, 5.0],
                        [95.5, 5.5],
                        [95.0, 5.5],
                        [95.0, 5.0],
                    ],
                ],
                // Polygon 2: 96.0..96.5, 5.0..5.5
                [
                    [
                        [96.0, 5.0],
                        [96.5, 5.0],
                        [96.5, 5.5],
                        [96.0, 5.5],
                        [96.0, 5.0],
                    ],
                ],
            ],
        ];

        $service = app(GfwFishingGroundSpatialAnalysisService::class);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 3,
                'entries' => [
                    ['id' => 'ev-poly1', 'type' => 'fishing', 'position' => ['lat' => 5.2, 'lon' => 95.2]],
                    ['id' => 'ev-poly2', 'type' => 'fishing', 'position' => ['lat' => 5.3, 'lon' => 96.3]],
                    ['id' => 'ev-gap', 'type' => 'fishing', 'position' => ['lat' => 5.2, 'lon' => 95.7]],
                ],
            ], 200),
        ]);

        $result = $service->analyze('2026-09-01', '2026-09-07', [
            'fishing_grounds' => [
                [
                    'id' => 20,
                    'name' => 'MultiPolygon Ground',
                    'code' => 'FG-MULTI',
                    'geometry' => $multiPolygonGeometry,
                ],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(2, $result['spatial_match_count']);
        $matchIds = array_column($result['matches'], 'event_id');
        $this->assertContains('ev-poly1', $matchIds);
        $this->assertContains('ev-poly2', $matchIds);
        $this->assertNotContains('ev-gap', $matchIds);
    }

    public function test_polygon_with_interior_holes_correctly_excludes_points_inside_hole(): void
    {
        // Outer ring: 94.0..96.0, 4.0..6.0
        // Hole: 94.8..95.2, 4.8..5.2
        $polygonWithHole = [
            'type' => 'Polygon',
            'coordinates' => [
                // Outer exterior ring
                [
                    [94.0, 4.0],
                    [96.0, 4.0],
                    [96.0, 6.0],
                    [94.0, 6.0],
                    [94.0, 4.0],
                ],
                // Interior hole ring
                [
                    [94.8, 4.8],
                    [95.2, 4.8],
                    [95.2, 5.2],
                    [94.8, 5.2],
                    [94.8, 4.8],
                ],
            ],
        ];

        $service = app(GfwFishingGroundSpatialAnalysisService::class);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    ['id' => 'ev-outside-hole-inside-poly', 'type' => 'fishing', 'position' => ['lat' => 4.5, 'lon' => 94.5]],
                    ['id' => 'ev-inside-hole', 'type' => 'fishing', 'position' => ['lat' => 5.0, 'lon' => 95.0]],
                ],
            ], 200),
        ]);

        $result = $service->analyze('2026-09-01', '2026-09-07', [
            'fishing_grounds' => [
                [
                    'id' => 30,
                    'name' => 'Donut Ground',
                    'geometry' => $polygonWithHole,
                ],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['spatial_match_count']);
        $this->assertSame('ev-outside-hole-inside-poly', $result['matches'][0]['event_id']);
    }

    public function test_null_geometry_fishing_ground_marked_as_not_ready_and_produces_no_matches(): void
    {
        $service = app(GfwFishingGroundSpatialAnalysisService::class);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 1,
                'entries' => [
                    ['id' => 'ev-1', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 95.3]],
                ],
            ], 200),
        ]);

        $result = $service->analyze('2026-09-01', '2026-09-07', [
            'fishing_grounds' => [
                ['id' => 1, 'name' => 'No Geometry Ground', 'geometry' => null],
                ['id' => 2, 'name' => 'Empty Array Ground', 'geometry' => []],
                ['id' => 3, 'name' => 'Invalid Type Ground', 'geometry' => ['type' => 'Point']],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(0, $result['spatial_match_count']);
        $this->assertEmpty($result['matches']);
        foreach ($result['fishing_grounds'] as $fg) {
            $this->assertSame('NOT_READY', $fg['geometry_status']);
            $this->assertSame(0, $fg['matched_event_count']);
        }
    }

    public function test_invalid_event_coordinates_are_safely_skipped_without_failing_request(): void
    {
        $polygonGeometry = [
            'type' => 'Polygon',
            'coordinates' => [
                [[95.0, 5.0], [96.0, 5.0], [96.0, 6.0], [95.0, 6.0], [95.0, 5.0]],
            ],
        ];

        $service = app(GfwFishingGroundSpatialAnalysisService::class);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 4,
                'entries' => [
                    ['id' => 'ev-valid', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 95.5]],
                    ['id' => 'ev-bad-lat', 'type' => 'fishing', 'position' => ['lat' => 120.0, 'lon' => 95.5]],
                    ['id' => 'ev-bad-lon', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 200.0]],
                    ['id' => 'ev-null-pos', 'type' => 'fishing', 'position' => ['lat' => null, 'lon' => null]],
                ],
            ], 200),
        ]);

        $result = $service->analyze('2026-09-01', '2026-09-07', [
            'fishing_grounds' => [
                ['id' => 1, 'name' => 'Alpha', 'geometry' => $polygonGeometry],
            ],
        ]);

        $this->assertTrue($result['success']);
        $this->assertSame(1, $result['spatial_match_count']);
        $this->assertSame('ev-valid', $result['matches'][0]['event_id']);
    }

    public function test_date_range_validation(): void
    {
        // > 7 days range
        $responseTooLong = $this->getJson('/api/gfw/spatial/fishing-grounds?start_date=2026-09-01&end_date=2026-09-10');
        $responseTooLong->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Rentang tanggal tidak boleh melebihi 7 hari.',
            ]);

        // Invalid date format
        $responseInvalidFormat = $this->getJson('/api/gfw/spatial/fishing-grounds?start_date=2026/09/01&end_date=2026-09-05');
        $responseInvalidFormat->assertStatus(422);

        // Start date > End date
        $responseReversed = $this->getJson('/api/gfw/spatial/fishing-grounds?start_date=2026-09-07&end_date=2026-09-01');
        $responseReversed->assertStatus(422);
    }

    public function test_invalid_limit_and_offset_rejection(): void
    {
        // Invalid limit
        $this->getJson('/api/gfw/spatial/fishing-grounds?limit=0')->assertStatus(422);
        $this->getJson('/api/gfw/spatial/fishing-grounds?limit=101')->assertStatus(422);
        $this->getJson('/api/gfw/spatial/fishing-grounds?limit=abc')->assertStatus(422);

        // Invalid offset
        $this->getJson('/api/gfw/spatial/fishing-grounds?offset=-1')->assertStatus(422);
        $this->getJson('/api/gfw/spatial/fishing-grounds?offset=abc')->assertStatus(422);

        // Invalid fishing_ground_id
        $this->getJson('/api/gfw/spatial/fishing-grounds?fishing_ground_id=-5')->assertStatus(422);
        $this->getJson('/api/gfw/spatial/fishing-grounds?fishing_ground_id=abc')->assertStatus(422);
    }

    public function test_token_is_never_leaked_in_response_or_headers(): void
    {
        $token = 'secret-token-must-not-leak';
        Config::set('services.gfw.token', $token);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/spatial/fishing-grounds');

        $response->assertStatus(200);
        $response->assertDontSee($token, false);
        $this->assertFalse(str_contains(json_encode($response->headers->all()), $token));
    }
}
