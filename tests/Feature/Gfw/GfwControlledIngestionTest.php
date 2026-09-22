<?php

namespace Tests\Feature\Gfw;

use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Models\Vessel;
use App\Services\Gfw\GfwIngestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwControlledIngestionTest extends TestCase
{
    use RefreshDatabase;

    protected GfwIngestionService $ingestionService;

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
                GfwVessel::truncate();
                GfwVesselPresence::truncate();
                GfwSyncRun::truncate();

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

        Config::set('gfw.api_token', 'test-gfw-token-secret-123');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $this->ingestionService = app(GfwIngestionService::class);

        // Ensure test table isolation
        GfwVesselPresence::truncate();
        GfwVessel::truncate();
        GfwSyncRun::truncate();
    }

    /**
     * Fake standard GFW API responses for testing.
     */
    protected function fakeGfwApi(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 2,
                'entries' => [
                    [
                        'id' => 'GFW-ACEH-001',
                        'shipname' => 'KM MEULABOH BAHARI',
                        'mmsi' => '525001111',
                        'imo' => '9111111',
                        'flag' => 'IDN',
                        'vesselType' => 'fishing',
                        'geartype' => 'purse_seine',
                        'lengthM' => 24.5,
                        'tonnageGt' => 85.0,
                    ],
                    [
                        'id' => 'GFW-ACEH-002',
                        'shipname' => 'MV MALACCA CARRIER',
                        'mmsi' => '525002222',
                        'imo' => '9222222',
                        'flag' => 'PAN',
                        'vesselType' => 'cargo',
                        'lengthM' => 120.0,
                        'tonnageGt' => 4500.0,
                    ],
                ],
            ], 200),

            'https://gateway.api.globalfishingwatch.org/v3/vessels/GFW-ACEH-001/tracks*' => Http::response([
                'entries' => [
                    [
                        'lat' => 5.551234,
                        'lon' => 95.319876,
                        'timestamp' => '2026-09-22T08:00:00Z',
                        'speedKnots' => 7.8,
                        'course' => 120.5,
                        'region' => 'zee-indonesia-aceh',
                    ],
                    [
                        'lat' => 5.560123,
                        'lon' => 95.328901,
                        'timestamp' => '2026-09-22T09:00:00Z',
                        'speedKnots' => 8.2,
                        'course' => 125.0,
                        'region' => 'zee-indonesia-aceh',
                    ],
                ],
            ], 200),

            'https://gateway.api.globalfishingwatch.org/v3/vessels/GFW-ACEH-002/tracks*' => Http::response([
                'entries' => [
                    [
                        'lat' => 4.512345,
                        'lon' => 98.213456,
                        'timestamp' => '2026-09-22T08:30:00Z',
                        'speedKnots' => 14.5,
                        'course' => 310.0,
                        'region' => 'zee-indonesia-aceh',
                    ],
                ],
            ], 200),
        ]);
    }

    /**
     * Test A: Dry Run calculates ingestion plan with ZERO database writes.
     */
    public function test_dry_run_calculates_ingestion_plan_without_database_writes(): void
    {
        $this->fakeGfwApi();

        $initialVessels = GfwVessel::count();
        $initialPresence = GfwVesselPresence::count();
        $initialSyncRuns = GfwSyncRun::count();

        $result = $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
            'aoi' => 'zee-indonesia-aceh',
        ], dryRun: true);

        $this->assertTrue($result['dry_run']);
        $this->assertSame(2, $result['vessels_found']);
        $this->assertSame(2, $result['new_vessels']);
        $this->assertSame(3, $result['presence_found']);
        $this->assertSame(3, $result['new_presence']);
        $this->assertSame(0, $result['duplicate_presence']);
        $this->assertNull($result['sync_run_id']);

        // Assert ZERO database writes occurred
        $this->assertSame($initialVessels, GfwVessel::count());
        $this->assertSame($initialPresence, GfwVesselPresence::count());
        $this->assertSame($initialSyncRuns, GfwSyncRun::count());
    }

    /**
     * Test B: First Controlled Sync persists vessels, presence, and records a sync run.
     */
    public function test_first_controlled_sync_persists_vessels_and_presence_to_sistem_gfw(): void
    {
        $this->fakeGfwApi();

        $result = $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
            'aoi' => 'zee-indonesia-aceh',
        ], dryRun: false);

        $this->assertFalse($result['dry_run']);
        $this->assertSame(2, $result['vessels_found']);
        $this->assertSame(2, $result['new_vessels']);
        $this->assertSame(3, $result['presence_found']);
        $this->assertSame(3, $result['new_presence']);
        $this->assertNotNull($result['sync_run_id']);

        // Verify rows exist in sistem_gfw tables
        $this->assertSame(2, GfwVessel::count());
        $this->assertSame(3, GfwVesselPresence::count());
        $this->assertSame(1, GfwSyncRun::count());

        $vessel = GfwVessel::where('gfw_vessel_id', 'GFW-ACEH-001')->first();
        $this->assertNotNull($vessel);
        $this->assertSame('KM MEULABOH BAHARI', $vessel->name);
        $this->assertSame('525001111', $vessel->mmsi);
        $this->assertSame('fishing', $vessel->vessel_type);
        $this->assertSame('purse_seine', $vessel->gear_type);

        $syncRun = GfwSyncRun::find($result['sync_run_id']);
        $this->assertNotNull($syncRun);
        $this->assertSame('zee-indonesia-aceh', $syncRun->aoi);
        $this->assertSame(3, $syncRun->records_found);
        $this->assertSame(3, $syncRun->records_saved);
        $this->assertSame('success', $syncRun->status);
    }

    /**
     * Test C: Repeat Same Sync is 100% idempotent and produces ZERO duplicate rows.
     */
    public function test_repeat_sync_is_idempotent_producing_zero_duplicate_rows(): void
    {
        $this->fakeGfwApi();

        // Run 1: First sync
        $run1 = $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
        ], dryRun: false);

        $this->assertSame(2, $run1['new_vessels']);
        $this->assertSame(3, $run1['new_presence']);
        $this->assertSame(2, GfwVessel::count());
        $this->assertSame(3, GfwVesselPresence::count());

        // Run 2: Repeat identical sync
        $run2 = $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
        ], dryRun: false);

        $this->assertSame(0, $run2['new_vessels']);
        $this->assertSame(2, $run2['vessels_skipped']);
        $this->assertSame(0, $run2['new_presence']);
        $this->assertSame(3, $run2['duplicate_presence']);

        // Assert database count has NOT doubled!
        $this->assertSame(2, GfwVessel::count());
        $this->assertSame(3, GfwVesselPresence::count());
        $this->assertSame(2, GfwSyncRun::count()); // Two sync records recorded
    }

    /**
     * Test D: Invalid spatial/temporal records are skipped and logged without crashing.
     */
    public function test_invalid_records_are_skipped_without_crashing(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 1,
                'entries' => [
                    [
                        'id' => 'GFW-INVALID-TEST',
                        'shipname' => 'KM TEST INVALID',
                    ],
                ],
            ], 200),

            'https://gateway.api.globalfishingwatch.org/v3/vessels/GFW-INVALID-TEST/tracks*' => Http::response([
                'entries' => [
                    // Invalid lat (out of world range)
                    ['lat' => 195.0, 'lon' => 95.0, 'timestamp' => '2026-09-22T08:00:00Z'],
                    // Invalid lon
                    ['lat' => 5.0, 'lon' => 300.0, 'timestamp' => '2026-09-22T08:00:00Z'],
                    // Missing timestamp
                    ['lat' => 5.0, 'lon' => 95.0, 'timestamp' => null],
                    // Valid record
                    ['lat' => 5.123456, 'lon' => 95.123456, 'timestamp' => '2026-09-22T08:00:00Z'],
                ],
            ], 200),
        ]);

        $result = $this->ingestionService->ingest([
            'query' => 'INVALID',
            'limit' => 1,
            'days' => 7,
        ], dryRun: false);

        $this->assertSame(3, $result['invalid_records']);
        $this->assertSame(1, $result['new_presence']);
        $this->assertSame(1, GfwVesselPresence::count());
    }

    /**
     * Test E: Main operational database (sistem_perikanan.vessels) remains strictly untouched.
     */
    public function test_main_database_remains_strictly_untouched_during_gfw_ingestion(): void
    {
        $this->fakeGfwApi();

        $initialLocalVessels = Vessel::count();

        $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
        ], dryRun: false);

        // Core fisheries vessels count must remain identical
        $this->assertSame($initialLocalVessels, Vessel::count());
        $this->assertDatabaseMissing('vessels', ['name' => 'KM MEULABOH BAHARI']);
    }

    /**
     * Test F: GFW API token is never exposed in stored raw data or sync runs.
     */
    public function test_gfw_api_token_is_never_leaked_into_raw_data_or_sync_runs(): void
    {
        $this->fakeGfwApi();

        $this->ingestionService->ingest([
            'query' => 'MEULABOH',
            'limit' => 5,
            'days' => 7,
        ], dryRun: false);

        $vessel = GfwVessel::first();
        $this->assertNotNull($vessel);

        $rawJson = json_encode($vessel->raw_data);
        $this->assertStringNotContainsString('test-gfw-token-secret-123', (string) $rawJson);

        $syncRun = GfwSyncRun::first();
        $this->assertNotNull($syncRun);
        $this->assertStringNotContainsString('test-gfw-token-secret-123', (string) $syncRun->error_message);
    }

    /**
     * Test G: Artisan command gfw:sync-observatory executes smoothly.
     */
    public function test_artisan_command_gfw_sync_observatory_supports_dry_run_and_live_execution(): void
    {
        $this->fakeGfwApi();

        $this->artisan('gfw:sync-observatory', ['--dry-run' => true, '--limit' => 2])
            ->expectsOutputToContain('DRY-RUN (NO WRITE)')
            ->expectsOutputToContain('Dry-run completed successfully. Zero database writes.')
            ->assertExitCode(0);

        $this->assertSame(0, GfwVessel::count());

        $this->artisan('gfw:sync-observatory', ['--limit' => 2])
            ->expectsOutputToContain('LIVE CONTROLLED SYNC')
            ->expectsOutputToContain('Controlled ingestion completed successfully.')
            ->assertExitCode(0);

        $this->assertSame(2, GfwVessel::count());
    }
}
