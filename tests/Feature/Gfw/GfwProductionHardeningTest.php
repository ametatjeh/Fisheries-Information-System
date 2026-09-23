<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\Gfw\GfwActivityService;
use App\Services\GFWService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwProductionHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('gfw.api_key', 'test-production-secret-token-gfw');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);
        $this->user = User::factory()->create();
        $this->user->givePermissionTo($gisPermission);
    }

    /**
     * 1. DISTINCT VESSEL DEDUPLICATION & PRESENCE VS EVENTS SEPARATION
     * Multiple events/positions for the same vessel MUST result in 1 unique vessel.
     */
    public function test_distinct_vessel_deduplication_and_event_separation(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 4,
                'entries' => [
                    [
                        'id' => 'evt-001',
                        'type' => 'fishing',
                        'start' => '2026-09-22T02:00:00Z',
                        'end' => '2026-09-22T04:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 95.80],
                        'vessel' => ['id' => 'vessel-same-01', 'name' => 'KM ACEH SATU', 'ssvid' => '525111111', 'flag' => 'IDN', 'type' => 'fishing'],
                    ],
                    [
                        'id' => 'evt-002',
                        'type' => 'fishing',
                        'start' => '2026-09-22T05:00:00Z',
                        'end' => '2026-09-22T07:00:00Z',
                        'position' => ['lat' => 5.30, 'lon' => 95.85],
                        'vessel' => ['id' => 'vessel-same-01', 'name' => 'KM ACEH SATU', 'ssvid' => '525111111', 'flag' => 'IDN', 'type' => 'fishing'],
                    ],
                    [
                        'id' => 'evt-003',
                        'type' => 'encounter',
                        'start' => '2026-09-22T08:00:00Z',
                        'end' => '2026-09-22T09:00:00Z',
                        'position' => ['lat' => 5.35, 'lon' => 95.90],
                        'vessel' => ['id' => 'vessel-same-01', 'name' => 'KM ACEH SATU', 'ssvid' => '525111111', 'flag' => 'IDN', 'type' => 'fishing'],
                    ],
                    [
                        'id' => 'evt-004',
                        'type' => 'port_visit',
                        'start' => '2026-09-22T10:00:00Z',
                        'end' => '2026-09-22T12:00:00Z',
                        'position' => ['lat' => 5.58, 'lon' => 95.32],
                        'vessel' => ['id' => 'vessel-same-01', 'name' => 'KM ACEH SATU', 'ssvid' => '525111111', 'flag' => 'IDN', 'type' => 'fishing'],
                    ],
                ],
            ], 200),
            'https://gateway.api.globalfishingwatch.org/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/dashboard?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(200);
        $data = $response->json();

        // 4 events belong to exactly 1 unique vessel
        $this->assertEquals(1, $data['kpi']['detected_vessels'], 'Detected vessels must count distinct vessels, not event records.');
        $this->assertEquals(1, count($data['vessels']), 'Vessel catalog must deduplicate into 1 unique vessel.');
        $this->assertEquals(2, $data['kpi']['fishing_activity'], 'Fishing events count must be preserved.');
        $this->assertEquals(1, $data['kpi']['encounters'], 'Encounter events count must be preserved.');
        $this->assertEquals(1, $data['kpi']['port_visits'], 'Port visit events count must be preserved.');
    }

    /**
     * 2. EXACT BOUNDARY CLASSIFICATION TESTS: LIVE / RECENT / STALE
     */
    public function test_live_recent_stale_boundary_classification(): void
    {
        // 23:59:59 (86399s) -> LIVE
        $this->assertEquals('LIVE', GFWService::classifyStatus(86399));

        // 24:00:00 (86400s) -> LIVE
        $this->assertEquals('LIVE', GFWService::classifyStatus(86400));

        // 24:00:01 (86401s) -> RECENT
        $this->assertEquals('RECENT', GFWService::classifyStatus(86401));

        // 71:59:59 (259199s) -> RECENT
        $this->assertEquals('RECENT', GFWService::classifyStatus(259199));

        // 72:00:00 (259200s) -> RECENT
        $this->assertEquals('RECENT', GFWService::classifyStatus(259200));

        // 72:00:01 (259201s) -> STALE
        $this->assertEquals('STALE', GFWService::classifyStatus(259201));

        // Null / negative -> STALE
        $this->assertEquals('STALE', GFWService::classifyStatus(null));
        $this->assertEquals('STALE', GFWService::classifyStatus(-10));
    }

    /**
     * 3. TRACK VALIDATION: CHRONOLOGICAL ORDER, INSUFFICIENT POINTS, COORDINATE CHECKS
     */
    public function test_track_points_are_strictly_sorted_oldest_to_newest(): void
    {
        $mockActivityService = $this->createMock(GfwActivityService::class);
        $mockActivityService->method('getVesselActivity')
            ->willReturn([
                'success' => true,
                'data' => [
                    // Deliberately unsorted points
                    ['lon' => 95.5, 'lat' => 5.7, 'timestamp' => '2026-09-20T12:00:00Z', 'speed' => 8.0],
                    ['lon' => 95.3, 'lat' => 5.5, 'timestamp' => '2026-09-20T10:00:00Z', 'speed' => 8.2],
                    ['lon' => 95.4, 'lat' => 5.6, 'timestamp' => '2026-09-20T11:00:00Z', 'speed' => 7.9],
                ],
            ]);
        $this->app->instance(GfwActivityService::class, $mockActivityService);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/vessel-chrono/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['sufficient']);
        $coords = $data['line_geojson']['coordinates'];
        $this->assertEquals([95.3, 5.5], $coords[0], 'Oldest coordinate must be first.');
        $this->assertEquals([95.4, 5.6], $coords[1]);
        $this->assertEquals([95.5, 5.7], $coords[2], 'Newest coordinate must be last.');
        $this->assertEquals('2026-09-20T10:00:00Z', $data['track_info']['first_detected']);
        $this->assertEquals('2026-09-20T12:00:00Z', $data['track_info']['last_detected']);
    }

    public function test_track_drops_out_of_bounds_coordinates_gracefully(): void
    {
        $mockActivityService = $this->createMock(GfwActivityService::class);
        $mockActivityService->method('getVesselActivity')
            ->willReturn([
                'success' => true,
                'data' => [
                    ['lon' => 95.3, 'lat' => 5.5, 'timestamp' => '2026-09-20T10:00:00Z'],
                    ['lon' => 250.0, 'lat' => 5.6, 'timestamp' => '2026-09-20T11:00:00Z'], // Invalid lon > 180
                    ['lon' => 95.5, 'lat' => -100.0, 'timestamp' => '2026-09-20T12:00:00Z'], // Invalid lat < -90
                    ['lon' => 95.6, 'lat' => 5.8, 'timestamp' => '2026-09-20T13:00:00Z'], // Valid
                ],
            ]);
        $this->app->instance(GfwActivityService::class, $mockActivityService);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/vessel-coords/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertTrue($data['sufficient']);
        $this->assertCount(2, $data['line_geojson']['coordinates'], 'Only the 2 valid coordinates should be included.');
    }

    public function test_track_fails_gracefully_with_zero_or_one_point(): void
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
            ->getJson('/api/gfw/vessels/vessel-single/track?start_date=2026-09-19&end_date=2026-09-21');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertFalse($data['sufficient']);
        $this->assertNull($data['line_geojson'], 'LineString must be null when points < 2.');
        $this->assertEquals('Track data insufficient', $data['track_info']['note']);
    }

    /**
     * 4. NO FALSE ZERO & ERROR STATUS FIDELITY
     */
    public function test_upstream_rate_limit_returns_429_not_zero_vessels(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['message' => 'Rate limit exceeded'], 429),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
        ]);
        $this->assertStringContainsStringIgnoringCase('rate limit', $response->json('message'));
    }

    public function test_upstream_server_error_returns_502_not_zero_vessels(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response(['message' => 'Internal GFW Gateway Error'], 500),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(502);
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_empty_dataset_returns_200_with_zero_vessels(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/zee-indonesia-aceh?start_date=2026-09-16&end_date=2026-09-22');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'summary' => [
                'total_vessels' => 0,
            ],
            'vessels' => [],
        ]);
    }
}
