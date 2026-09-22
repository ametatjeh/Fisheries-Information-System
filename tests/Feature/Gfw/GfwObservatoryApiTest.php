<?php

namespace Tests\Feature\Gfw;

use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GfwObservatoryApiTest extends TestCase
{
    /** @var list<array<string, mixed>>|null */
    protected static ?array $vesselBackup = null;

    /** @var list<array<string, mixed>>|null */
    protected static ?array $presenceBackup = null;

    /** @var list<array<string, mixed>>|null */
    protected static ?array $syncRunBackup = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$vesselBackup !== null) {
            try {
                GfwVessel::query()->delete();
                GfwVesselPresence::query()->delete();
                GfwSyncRun::query()->delete();

                foreach (self::$vesselBackup as $v) {
                    GfwVessel::create($v);
                }
                foreach (self::$presenceBackup as $p) {
                    GfwVesselPresence::create($p);
                }
                foreach (self::$syncRunBackup as $s) {
                    GfwSyncRun::create($s);
                }
            } catch (\Throwable) {
            }
        }

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$vesselBackup === null) {
            self::$vesselBackup = GfwVessel::all()->toArray();
            self::$presenceBackup = GfwVesselPresence::all()->toArray();
            self::$syncRunBackup = GfwSyncRun::all()->toArray();
        }

        // Ensure tests start clean in sistem_gfw isolated tables
        GfwVesselPresence::query()->delete();
        GfwVessel::query()->delete();
        GfwSyncRun::query()->delete();
    }

    public function test_observatory_stats_endpoint_returns_valid_envelope(): void
    {
        GfwVessel::create([
            'gfw_vessel_id' => 'TEST-001',
            'name' => 'KM TEST 1',
            'vessel_type' => 'FISHING',
            'flag' => 'IDN',
            'first_seen_at' => now()->subDays(5),
            'last_seen_at' => now(),
            'last_synced_at' => now(),
        ]);

        GfwVesselPresence::create([
            'gfw_vessel_id' => 'TEST-001',
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => now()->subDays(2),
            'latitude' => 5.551234,
            'longitude' => 95.312345,
            'speed' => 7.2,
            'course' => 120.0,
            'vessel_type' => 'FISHING',
            'flag' => 'IDN',
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        $response = $this->getJson(route('api.gfw.observatory.stats'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'source',
                'data' => [
                    'total_vessels',
                    'total_presence',
                    'vessels_by_type',
                    'vessels_by_flag',
                    'last_sync',
                    'date_range',
                ],
                'meta',
            ]);

        $this->assertTrue($response->json('success'));
        $this->assertSame('gfw_observatory', $response->json('source'));
        $this->assertSame(1, $response->json('data.total_vessels'));
        $this->assertSame(1, $response->json('data.total_presence'));
        $this->assertSame(1, $response->json('data.vessels_by_type.FISHING'));
        $this->assertSame(1, $response->json('data.vessels_by_flag.IDN'));
    }

    public function test_observatory_vessels_endpoint_with_pagination_and_filtering(): void
    {
        GfwVessel::create([
            'gfw_vessel_id' => 'VESSEL-IDN-01',
            'name' => 'KM MEULABOH RAYA',
            'vessel_type' => 'FISHING',
            'flag' => 'IDN',
            'last_synced_at' => now(),
        ]);

        GfwVessel::create([
            'gfw_vessel_id' => 'VESSEL-MYS-02',
            'name' => 'CARGO SELAT',
            'vessel_type' => 'CARGO',
            'flag' => 'MYS',
            'last_synced_at' => now(),
        ]);

        // Filter by vessel_type
        $response = $this->getJson(route('api.gfw.observatory.vessels.index', ['vessel_type' => 'FISHING']));
        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('VESSEL-IDN-01', $response->json('data.0.gfw_vessel_id'));

        // Filter by flag
        $response = $this->getJson(route('api.gfw.observatory.vessels.index', ['flag' => 'MYS']));
        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('VESSEL-MYS-02', $response->json('data.0.gfw_vessel_id'));

        // Search by name
        $response = $this->getJson(route('api.gfw.observatory.vessels.index', ['search' => 'MEULABOH']));
        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('VESSEL-IDN-01', $response->json('data.0.gfw_vessel_id'));
    }

    public function test_observatory_vessel_show_endpoint(): void
    {
        GfwVessel::create([
            'gfw_vessel_id' => 'VESSEL-SHOW-01',
            'name' => 'KM BANDA ACEH',
            'vessel_type' => 'FISHING',
            'flag' => 'IDN',
            'last_synced_at' => now(),
        ]);

        GfwVesselPresence::create([
            'gfw_vessel_id' => 'VESSEL-SHOW-01',
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => now()->subHour(),
            'latitude' => 5.60,
            'longitude' => 95.30,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        $response = $this->getJson(route('api.gfw.observatory.vessels.show', 'VESSEL-SHOW-01'));
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'gfw_observatory',
                'data' => [
                    'vessel' => [
                        'gfw_vessel_id' => 'VESSEL-SHOW-01',
                        'name' => 'KM BANDA ACEH',
                    ],
                    'presence_count' => 1,
                ],
            ]);

        // 404 for non-existent vessel
        $response404 = $this->getJson(route('api.gfw.observatory.vessels.show', 'NON-EXISTENT'));
        $response404->assertStatus(404)
            ->assertJson([
                'success' => false,
                'source' => 'gfw_observatory',
            ]);
    }

    public function test_observatory_vessel_presence_endpoint(): void
    {
        GfwVesselPresence::create([
            'gfw_vessel_id' => 'PRESENCE-TEST-01',
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => '2026-09-20 10:00:00',
            'latitude' => 5.55,
            'longitude' => 95.35,
            'speed' => 6.5,
            'course' => 45.0,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        GfwVesselPresence::create([
            'gfw_vessel_id' => 'PRESENCE-TEST-01',
            'aoi' => 'zee-indonesia-aceh',
            'observed_at' => '2026-09-21 14:00:00',
            'latitude' => 5.60,
            'longitude' => 95.40,
            'speed' => 8.1,
            'course' => 90.0,
            'source_dataset' => 'public-global-vessel-tracks:latest',
            'source_version' => 'v3',
        ]);

        $response = $this->getJson(route('api.gfw.observatory.vessels.presence', [
            'gfwVesselId' => 'PRESENCE-TEST-01',
            'start_date' => '2026-09-21',
        ]));

        $response->assertStatus(200);
        $this->assertSame(1, $response->json('meta.total'));
        $this->assertSame('PRESENCE-TEST-01', $response->json('meta.gfw_vessel_id'));
    }

    public function test_observatory_sync_runs_and_status(): void
    {
        GfwSyncRun::create([
            'aoi' => 'zee-indonesia-aceh',
            'date_from' => '2026-09-15',
            'date_to' => '2026-09-22',
            'dataset' => 'public-global-vessel-tracks:latest',
            'endpoint' => '/vessels/{id}/tracks',
            'records_found' => 10,
            'records_saved' => 8,
            'status' => 'success',
            'started_at' => now()->subMinutes(5),
            'finished_at' => now()->subMinutes(4),
        ]);

        $responseRuns = $this->getJson(route('api.gfw.observatory.sync-runs'));
        $responseRuns->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'gfw_observatory',
                'meta' => ['total' => 1],
            ]);

        $responseStatus = $this->getJson(route('api.gfw.observatory.sync-status'));
        $responseStatus->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'gfw_observatory',
                'data' => [
                    'health' => 'healthy',
                    'total_runs' => 1,
                    'successful_runs' => 1,
                    'failed_runs' => 0,
                ],
            ]);
    }

    public function test_observatory_queries_do_not_touch_main_database(): void
    {
        DB::connection('mysql')->enableQueryLog();

        $this->getJson(route('api.gfw.observatory.stats'));
        $this->getJson(route('api.gfw.observatory.vessels.index'));
        $this->getJson(route('api.gfw.observatory.sync-status'));

        $mainDbQueries = DB::connection('mysql')->getQueryLog();

        // Main database must receive 0 queries from observatory endpoints
        $this->assertCount(0, $mainDbQueries, 'Observatory API must not query the main database (sistem_perikanan).');

        DB::connection('mysql')->disableQueryLog();
    }

    public function test_observatory_vessels_empty_result_returns_proper_envelope(): void
    {
        $response = $this->getJson(route('api.gfw.observatory.vessels.index', ['search' => 'NON_EXISTENT_VESSEL_XYZ']));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'source' => 'gfw_observatory',
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'current_page' => 1,
                ],
            ]);
    }

    public function test_observatory_vessels_invalid_parameters_rejected(): void
    {
        $response = $this->getJson(route('api.gfw.observatory.vessels.index', [
            'per_page' => 999, // exceeds max: 100
        ]));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['per_page']);

        $responseDate = $this->getJson(route('api.gfw.observatory.vessels.index', [
            'start_date' => '2026-09-30',
            'end_date' => '2026-09-01', // before start_date
        ]));

        $responseDate->assertStatus(422)
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_observatory_endpoints_never_leak_token_or_credentials(): void
    {
        config(['gfw.api_key' => 'super-secret-gfw-api-token-12345']);

        $endpoints = [
            route('api.gfw.observatory.stats'),
            route('api.gfw.observatory.vessels.index'),
            route('api.gfw.observatory.sync-runs'),
            route('api.gfw.observatory.sync-status'),
        ];

        foreach ($endpoints as $url) {
            $response = $this->getJson($url);
            $response->assertStatus(200);
            $content = $response->getContent();
            $this->assertStringNotContainsString('super-secret-gfw-api-token-12345', $content);
            $this->assertStringNotContainsString('Bearer ', $content);
        }
    }
}
