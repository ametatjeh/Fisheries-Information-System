<?php

namespace Tests\Feature;

use App\Models\FishingGround;
use App\Models\Rzwp3kZone;
use App\Services\Rzwp3k\Rzwp3kSpatialAnalysisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class Rzwp3kSpatialAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-gfw-token-rzwp3k');
    }

    /**
     * TEST 01 — Endpoint returns 200 and standard API contract
     */
    public function test_01_endpoint_is_accessible_and_returns_valid_contract(): void
    {
        Rzwp3kZone::create([
            'code' => 'KPU-PT-01',
            'name' => 'Zona Perikanan Tangkap Pesisir',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => null,
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

        $response = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-07',
        ]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'Global Fishing Watch',
                'analysis' => 'GFW Event × RZWP3K Aceh',
                'aoi' => 'ZEE Indonesia - Kawasan Aceh',
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
                'event_count' => 2,
                'returned_count' => 1,
                'spatial_match_count' => 0,
                'zones' => [
                    [
                        'code' => 'KPU-PT-01',
                        'name' => 'Zona Perikanan Tangkap Pesisir',
                        'zone_type' => 'KPU',
                        'subzone_type' => 'KPU-PT',
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
                'zones',
                'matches',
                'disclaimer',
            ]);
    }

    /**
     * TEST 02 — Valid polygon containment matches event with within relation
     */
    public function test_02_point_in_polygon_identifies_matching_event_with_within_relation(): void
    {
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

        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            [
                'id' => 10,
                'code' => 'KPU-PT-SABANG',
                'name' => 'Zona Perikanan Sabang',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PT',
                'geometry' => $polygonGeometry,
            ],
        ];

        $customEvents = [
            [
                'id' => 'ev-inside',
                'type' => 'fishing',
                'position' => ['lat' => 5.5, 'lon' => 95.5],
                'vessel' => ['name' => 'KM REZEKI', 'ssvid' => '525000111', 'flag' => 'IDN'],
                'start' => '2026-09-01T02:00:00Z',
                'end' => '2026-09-01T04:00:00Z',
                'dataset' => 'public-global-fishing-events:latest',
            ],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $result['spatial_match_count']);
        $this->assertCount(1, $result['matches']);
        $match = $result['matches'][0];
        $this->assertEquals('ev-inside', $match['event_id']);
        $this->assertEquals(10, $match['zone_id']);
        $this->assertEquals('KPU-PT-SABANG', $match['zone_code']);
        $this->assertEquals('Within RZWP3K Zone', $match['spatial_relation']);
        $this->assertEquals(5.5, $match['latitude']);
        $this->assertEquals(95.5, $match['longitude']);
        $this->assertEquals('KM REZEKI', $match['vessel_name']);
    }

    /**
     * TEST 03 — Point outside polygon produces no false positive
     */
    public function test_03_point_outside_polygon_produces_no_false_positive(): void
    {
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

        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            [
                'id' => 11,
                'code' => 'KK-KKP-01',
                'name' => 'Kawasan Konservasi Perairan',
                'zone_type' => 'KK',
                'subzone_type' => 'KK-KKP',
                'geometry' => $polygonGeometry,
            ],
        ];

        $customEvents = [
            [
                'id' => 'ev-outside',
                'type' => 'fishing',
                'position' => ['lat' => 4.5, 'lon' => 97.0],
                'vessel' => ['name' => 'KM LAUT LUAR', 'ssvid' => '525999000', 'flag' => 'IDN'],
            ],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals(0, $result['spatial_match_count']);
        $this->assertEmpty($result['matches']);
        $this->assertEquals(0, $result['zones'][0]['matched_event_count']);
    }

    /**
     * TEST 04 — MultiPolygon correctly evaluates containment
     */
    public function test_04_point_in_multipolygon_works_correctly(): void
    {
        $multiPolygon = [
            'type' => 'MultiPolygon',
            'coordinates' => [
                [
                    [
                        [95.0, 5.0],
                        [96.0, 5.0],
                        [96.0, 6.0],
                        [95.0, 6.0],
                        [95.0, 5.0],
                    ],
                ],
                [
                    [
                        [97.0, 3.0],
                        [98.0, 3.0],
                        [98.0, 4.0],
                        [97.0, 4.0],
                        [97.0, 3.0],
                    ],
                ],
            ],
        ];

        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            [
                'id' => 12,
                'code' => 'AL-ALUR-01',
                'name' => 'Alur Pelayaran Terfragmentasi',
                'zone_type' => 'AL',
                'subzone_type' => 'AL-ALUR',
                'geometry' => $multiPolygon,
            ],
        ];

        $customEvents = [
            // Inside first polygon
            ['id' => 'ev-poly1', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 95.5], 'vessel' => ['name' => 'KM A']],
            // Inside second polygon
            ['id' => 'ev-poly2', 'type' => 'fishing', 'position' => ['lat' => 3.5, 'lon' => 97.5], 'vessel' => ['name' => 'KM B']],
            // Outside both
            ['id' => 'ev-outside', 'type' => 'fishing', 'position' => ['lat' => 4.5, 'lon' => 96.5], 'vessel' => ['name' => 'KM C']],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertEquals(2, $result['spatial_match_count']);
        $matchedIds = array_column($result['matches'], 'event_id');
        $this->assertContains('ev-poly1', $matchedIds);
        $this->assertContains('ev-poly2', $matchedIds);
        $this->assertNotContains('ev-outside', $matchedIds);
    }

    /**
     * TEST 05 — Polygon interior holes are properly excluded from containment
     */
    public function test_05_point_in_polygon_hole_is_excluded(): void
    {
        $polygonWithHole = [
            'type' => 'Polygon',
            'coordinates' => [
                // Outer ring [95.0..98.0, 4.0..7.0]
                [
                    [95.0, 4.0],
                    [98.0, 4.0],
                    [98.0, 7.0],
                    [95.0, 7.0],
                    [95.0, 4.0],
                ],
                // Inner hole ring [96.0..97.0, 5.0..6.0]
                [
                    [96.0, 5.0],
                    [97.0, 5.0],
                    [97.0, 6.0],
                    [96.0, 6.0],
                    [96.0, 5.0],
                ],
            ],
        ];

        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            [
                'id' => 13,
                'code' => 'KSNT-01',
                'name' => 'Kawasan Strategis Nasional Tertentu',
                'zone_type' => 'KSNT',
                'subzone_type' => 'KSNT-PBL',
                'geometry' => $polygonWithHole,
            ],
        ];

        $customEvents = [
            // Inside outer ring, outside hole
            ['id' => 'ev-solid', 'type' => 'fishing', 'position' => ['lat' => 4.5, 'lon' => 95.5], 'vessel' => ['name' => 'KM SOLID']],
            // Inside hole -> must NOT match
            ['id' => 'ev-hole', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 96.5], 'vessel' => ['name' => 'KM HOLE']],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertEquals(1, $result['spatial_match_count']);
        $this->assertEquals('ev-solid', $result['matches'][0]['event_id']);
    }

    /**
     * TEST 06 — NULL geometry returns NOT_READY status with 0 matches
     */
    public function test_06_null_geometry_returns_not_ready_status(): void
    {
        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            [
                'id' => 14,
                'code' => 'KPU-NULL',
                'name' => 'Zona Belum Berpeta',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PT',
                'geometry' => null,
            ],
        ];

        $customEvents = [
            ['id' => 'ev-1', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 95.5], 'vessel' => ['name' => 'KM TEST']],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertEquals('NOT_READY', $result['zones'][0]['geometry_status']);
        $this->assertEquals(0, $result['spatial_match_count']);
        $this->assertEmpty($result['matches']);
    }

    /**
     * TEST 07 — Invalid or missing coordinates are skipped safely
     */
    public function test_07_invalid_coordinates_skipped_gracefully(): void
    {
        $polygon = [
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

        $service = app(Rzwp3kSpatialAnalysisService::class);

        $customZones = [
            ['id' => 15, 'code' => 'KPU-01', 'name' => 'Zona Uji', 'zone_type' => 'KPU', 'subzone_type' => 'KPU-PT', 'geometry' => $polygon],
        ];

        $customEvents = [
            ['id' => 'ev-null-pos', 'type' => 'fishing', 'position' => null],
            ['id' => 'ev-invalid-lat', 'type' => 'fishing', 'position' => ['lat' => 999.0, 'lon' => 95.5]],
            ['id' => 'ev-nan', 'type' => 'fishing', 'position' => ['lat' => 'invalid', 'lon' => 95.5]],
            ['id' => 'ev-valid', 'type' => 'fishing', 'position' => ['lat' => 5.5, 'lon' => 95.5], 'vessel' => ['name' => 'KM VALID']],
        ];

        $result = $service->analyzeGfwEvents('2026-09-01', '2026-09-07', [
            'zones' => $customZones,
            'events' => $customEvents,
        ]);

        $this->assertEquals(1, $result['spatial_match_count']);
        $this->assertEquals('ev-valid', $result['matches'][0]['event_id']);
    }

    /**
     * TEST 08 — Date validation rejects range > 7 days or invalid format
     */
    public function test_08_date_validation_rejects_invalid_inputs(): void
    {
        // Greater than 7 days
        $resMoreThan7 = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-10',
        ]));
        $resMoreThan7->assertStatus(422)
            ->assertJson(['success' => false]);

        // start_date > end_date
        $resInverted = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '2026-09-07',
            'end_date' => '2026-09-01',
        ]));
        $resInverted->assertStatus(422);

        // Invalid date format
        $resFormat = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '01-09-2026',
            'end_date' => '2026-09-07',
        ]));
        $resFormat->assertStatus(422);
    }

    /**
     * TEST 09 — Limit validation accepts 1..100, rejects outside range
     */
    public function test_09_limit_validation(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['total' => 0, 'entries' => []], 200),
        ]);

        // Valid limits: 25, 50, 100
        foreach ([25, 50, 100] as $lim) {
            $res = $this->getJson(route('api.rzwp3k.spatial.gfw', [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
                'limit' => $lim,
            ]));
            $res->assertStatus(200);
        }

        // Invalid limits: 0, 101, text
        $this->getJson(route('api.rzwp3k.spatial.gfw', ['start_date' => '2026-09-01', 'end_date' => '2026-09-07', 'limit' => 0]))->assertStatus(422);
        $this->getJson(route('api.rzwp3k.spatial.gfw', ['start_date' => '2026-09-01', 'end_date' => '2026-09-07', 'limit' => 101]))->assertStatus(422);
        $this->getJson(route('api.rzwp3k.spatial.gfw', ['start_date' => '2026-09-01', 'end_date' => '2026-09-07', 'limit' => 'abc']))->assertStatus(422);
    }

    /**
     * TEST 10 & 11 — Offset validation accepts >= 0 and rejects invalid offset
     */
    public function test_10_and_11_offset_validation(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['total' => 0, 'entries' => []], 200),
        ]);

        // Valid offsets: 0, 25, 50
        foreach ([0, 25, 50] as $off) {
            $res = $this->getJson(route('api.rzwp3k.spatial.gfw', [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-07',
                'offset' => $off,
            ]));
            $res->assertStatus(200);
        }

        // Invalid offsets: -1, text
        $this->getJson(route('api.rzwp3k.spatial.gfw', ['start_date' => '2026-09-01', 'end_date' => '2026-09-07', 'offset' => -1]))->assertStatus(422);
        $this->getJson(route('api.rzwp3k.spatial.gfw', ['start_date' => '2026-09-01', 'end_date' => '2026-09-07', 'offset' => 'abc']))->assertStatus(422);
    }

    /**
     * TEST 12 — Pagination calculates next_offset properly
     */
    public function test_12_pagination_next_offset_handling(): void
    {
        $fakeEntries = [];
        for ($i = 1; $i <= 25; $i++) {
            $fakeEntries[] = [
                'id' => 'ev-test-'.$i,
                'type' => 'fishing',
                'position' => ['lat' => 5.5, 'lon' => 95.5],
            ];
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 60,
                'entries' => $fakeEntries,
            ], 200),
        ]);

        $res = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-07',
            'limit' => 25,
            'offset' => 0,
        ]));

        $res->assertStatus(200);
        $data = $res->json();
        $this->assertEquals(25, $data['pagination']['next_offset']);
        $this->assertEquals(60, $data['pagination']['total']);
    }

    /**
     * TEST 13 — Zone filters (zone_type, zone_id)
     */
    public function test_13_zone_filtering(): void
    {
        Rzwp3kZone::create([
            'code' => 'KPU-ZONA-1',
            'name' => 'Zona KPU 1',
            'zone_type' => 'KPU',
            'subzone_type' => 'KPU-PT',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => null,
        ]);

        Rzwp3kZone::create([
            'code' => 'KK-ZONA-2',
            'name' => 'Zona KK 2',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => null,
        ]);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['total' => 0, 'entries' => []], 200),
        ]);

        // Filter by zone_type = KPU
        $resKpu = $this->getJson(route('api.rzwp3k.spatial.gfw', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-07',
            'zone_type' => 'KPU',
        ]));

        $resKpu->assertStatus(200);
        $zonesKpu = $resKpu->json('zones');
        $this->assertCount(1, $zonesKpu);
        $this->assertEquals('KPU-ZONA-1', $zonesKpu[0]['code']);
    }

    /**
     * TEST 14 — Security: Secrets and tokens are never leaked
     */
    public function test_14_security_endpoints_do_not_expose_secrets(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['total' => 0, 'entries' => []], 200),
        ]);

        $responseGfw = $this->getJson(route('api.rzwp3k.spatial.gfw'));
        $responseGfw->assertStatus(200);
        $contentGfw = $responseGfw->getContent();
        $this->assertStringNotContainsString('DB_PASSWORD', $contentGfw);
        $this->assertStringNotContainsString('test-valid-gfw-token-rzwp3k', $contentGfw);
        $this->assertStringNotContainsString('APP_KEY', $contentGfw);

        $responseFg = $this->getJson(route('api.rzwp3k.spatial.fishing-grounds'));
        $responseFg->assertStatus(200);
        $contentFg = $responseFg->getContent();
        $this->assertStringNotContainsString('DB_PASSWORD', $contentFg);
        $this->assertStringNotContainsString('test-valid-gfw-token-rzwp3k', $contentFg);
        $this->assertStringNotContainsString('APP_KEY', $contentFg);
    }

    /**
     * Stage 18 Legacy Fishing Ground spatial analysis preservation test
     */
    public function test_15_legacy_fishing_ground_overlap_analysis_preserved(): void
    {
        $service = app(Rzwp3kSpatialAnalysisService::class);

        Rzwp3kZone::create([
            'code' => 'KK-KKP-SABANG-STAGE18',
            'name' => 'Kawasan Konservasi Perairan Sabang',
            'zone_type' => 'KK',
            'subzone_type' => 'KK-KKP',
            'legal_basis' => 'Qanun Aceh 1/2020',
            'status' => 'legal_active',
            'geometry' => [
                'type' => 'Polygon',
                'coordinates' => [
                    [
                        [95.2, 5.8],
                        [95.4, 5.8],
                        [95.4, 6.0],
                        [95.2, 6.0],
                        [95.2, 5.8],
                    ],
                ],
            ],
        ]);

        $fgInside = FishingGround::create([
            'name' => 'Perairan Pulau Weh',
            'code' => 'FG-WEH-01',
            'latitude' => 5.89,
            'longitude' => 95.31,
            'description' => 'Spot perikanan karang',
        ]);

        $analysis = $service->analyzeFishingGrounds();

        $this->assertEquals('SUCCESS', $analysis['status']);
        $insideResult = collect($analysis['results'])->firstWhere('fishing_ground_id', $fgInside->id);
        $this->assertTrue($insideResult['has_spatial_intersection']);
        $this->assertEquals('KK-KKP-SABANG-STAGE18', $insideResult['intersecting_zones'][0]['zone_code']);
    }
}
