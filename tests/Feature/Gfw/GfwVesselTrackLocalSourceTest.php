<?php

namespace Tests\Feature\Gfw;

use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Models\User;
use App\Services\Gfw\GfwIngestionService;
use App\Services\GFWService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GfwVesselTrackLocalSourceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();

        Config::set('gfw.api_key', 'test-api-key-track');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $gfwPermission = Permission::firstOrCreate(['name' => 'access.gfw', 'guard_name' => 'web']);
        $adminRole->givePermissionTo($gfwPermission);

        $this->user = User::factory()->create();
        $this->user->assignRole($adminRole);

        $this->ensureGfwSchemaAndFixtures();
    }

    /**
     * Ensure GFW database tables exist and fixtures are loaded if using SQLite or clean state.
     */
    protected function ensureGfwSchemaAndFixtures(): void
    {
        try {
            $connection = DB::connection('gfw');
            $driver = $connection->getDriverName();

            if ($driver === 'sqlite') {
                if (! Schema::connection('gfw')->hasTable('gfw_vessels')) {
                    Schema::connection('gfw')->create('gfw_vessels', function ($table) {
                        $table->id();
                        $table->string('gfw_vessel_id')->unique();
                        $table->string('name')->nullable();
                        $table->string('mmsi')->nullable()->index();
                        $table->string('imo')->nullable();
                        $table->string('flag')->nullable();
                        $table->string('vessel_type')->nullable();
                        $table->timestamps();
                    });
                }

                if (! Schema::connection('gfw')->hasTable('gfw_vessel_presence')) {
                    Schema::connection('gfw')->create('gfw_vessel_presence', function ($table) {
                        $table->id();
                        $table->string('gfw_vessel_id')->index();
                        $table->string('aoi')->nullable();
                        $table->dateTime('observed_at')->nullable();
                        $table->decimal('latitude', 10, 7)->nullable();
                        $table->decimal('longitude', 10, 7)->nullable();
                        $table->decimal('speed', 8, 2)->nullable();
                        $table->decimal('course', 8, 2)->nullable();
                        $table->string('vessel_type')->nullable();
                        $table->string('flag')->nullable();
                        $table->string('source_dataset')->nullable();
                        $table->string('source_version')->nullable();
                        $table->timestamps();
                    });
                }
            }

            // Ensure test fixture for MMSI 525137044 / UUID 3133bce7c-c6b8-a367-d03b-0e06b677bc23 exists
            $vessel = GfwVessel::where('gfw_vessel_id', '3133bce7c-c6b8-a367-d03b-0e06b677bc23')->first();
            if (! $vessel) {
                GfwVessel::create([
                    'gfw_vessel_id' => '3133bce7c-c6b8-a367-d03b-0e06b677bc23',
                    'name' => 'KM TEST VESSEL',
                    'mmsi' => '525137044',
                    'flag' => 'IDN',
                    'vessel_type' => 'fishing',
                ]);
            }

            $presenceCount = GfwVesselPresence::where('gfw_vessel_id', '3133bce7c-c6b8-a367-d03b-0e06b677bc23')->count();
            if ($presenceCount === 0) {
                for ($i = 1; $i <= 13; $i++) {
                    GfwVesselPresence::create([
                        'gfw_vessel_id' => '3133bce7c-c6b8-a367-d03b-0e06b677bc23',
                        'aoi' => 'zee-indonesia-aceh',
                        'observed_at' => Carbon::parse("2026-09-0{$i} 10:00:00"),
                        'latitude' => 5.10 + ($i * 0.01),
                        'longitude' => 98.10 + ($i * 0.01),
                        'speed' => 5.5,
                        'course' => 120.0,
                        'vessel_type' => 'fishing',
                        'flag' => 'IDN',
                        'source_dataset' => 'public-global-vessel-presence:latest',
                    ]);
                }
            }

            // Ensure test fixture for valid vessel with NO presence records exists
            $emptyVessel = GfwVessel::where('gfw_vessel_id', 'valid-vessel-no-presence-uuid')->first();
            if (! $emptyVessel) {
                GfwVessel::create([
                    'gfw_vessel_id' => 'valid-vessel-no-presence-uuid',
                    'name' => 'KM EMPTY VESSEL',
                    'mmsi' => '999888777',
                    'flag' => 'IDN',
                    'vessel_type' => 'fishing',
                ]);
            }
        } catch (\Throwable) {
            // Non-blocking in case connection issues arise
        }
    }

    /**
     * TEST 1: MMSI 525137044 must be resolved to GFW Vessel ID.
     */
    public function test_1_mmsi_resolves_to_gfw_vessel_id(): void
    {
        $vessel = GfwVessel::where('mmsi', '525137044')->first();
        $this->assertNotNull($vessel, 'Vessel with MMSI 525137044 must exist in sistem_gfw.gfw_vessels.');
        $this->assertSame('3133bce7c-c6b8-a367-d03b-0e06b677bc23', $vessel->gfw_vessel_id);

        /** @var GFWService $gfw */
        $gfw = app(GFWService::class);
        $result = $gfw->getVesselTrack('525137044', '2026-09-01', '2026-09-07');

        $this->assertTrue($result['success']);
        $this->assertSame('3133bce7c-c6b8-a367-d03b-0e06b677bc23', $result['vessel_id']);
        $this->assertSame('3133bce7c-c6b8-a367-d03b-0e06b677bc23', $result['vessel']['gfw_vessel_id']);
    }

    /**
     * TEST 2: GFW Vessel ID 3133bce7c-c6b8-a367-d03b-0e06b677bc23 returns local track.
     * Expectation: HTTP 200, FeatureCollection, features match local database count, sorted observed_at ASC.
     */
    public function test_2_gfw_vessel_id_returns_local_track_feature_collection(): void
    {
        $expectedCount = GfwVesselPresence::where('gfw_vessel_id', '3133bce7c-c6b8-a367-d03b-0e06b677bc23')
            ->whereBetween('observed_at', ['2026-09-01 00:00:00', '2026-09-07 23:59:59'])
            ->count();
        $this->assertGreaterThan(0, $expectedCount);

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/3133bce7c-c6b8-a367-d03b-0e06b677bc23/track?start_date=2026-09-01&end_date=2026-09-07');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'vessel_id' => '3133bce7c-c6b8-a367-d03b-0e06b677bc23',
                'track' => [
                    'type' => 'FeatureCollection',
                ],
                'data' => [
                    'type' => 'FeatureCollection',
                ],
            ]);

        $json = $response->json();
        $this->assertSame($expectedCount, $json['points_count']);

        // Verify features in data are Point features with coordinates [longitude, latitude]
        $dataFeatures = $json['data']['features'];
        $this->assertCount($expectedCount, $dataFeatures);
        foreach ($dataFeatures as $f) {
            $this->assertSame('Feature', $f['type']);
            $this->assertSame('Point', $f['geometry']['type']);
            $coords = $f['geometry']['coordinates'];
            $this->assertIsNumeric($coords[0]); // longitude
            $this->assertIsNumeric($coords[1]); // latitude
            $this->assertGreaterThanOrEqual(-180, $coords[0]);
            $this->assertLessThanOrEqual(180, $coords[0]);
            $this->assertGreaterThanOrEqual(-90, $coords[1]);
            $this->assertLessThanOrEqual(90, $coords[1]);
            $this->assertNotNull($f['properties']['observed_at']);
        }

        // Verify chronological sorting (observed_at ASC)
        $timestamps = array_map(fn ($f) => strtotime($f['properties']['observed_at']), $dataFeatures);
        $sortedTimestamps = $timestamps;
        sort($sortedTimestamps);
        $this->assertSame($sortedTimestamps, $timestamps, 'Track points must be strictly sorted by observed_at ASC.');
    }

    /**
     * TEST 3: Valid vessel with NO presence records returns HTTP 200 with empty features.
     */
    public function test_3_valid_vessel_without_presence_returns_http_200_with_empty_features(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/valid-vessel-no-presence-uuid/track?start_date=2026-09-01&end_date=2026-09-07');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'points_count' => 0,
                'message' => 'Belum tersedia data track untuk vessel ini.',
                'track' => [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ],
                'data' => [
                    'type' => 'FeatureCollection',
                    'features' => [],
                ],
            ]);
    }

    /**
     * TEST 4: Unknown vessel returns JSON response with success = false (HTTP 404).
     */
    public function test_4_unknown_vessel_returns_json_error_with_404(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/non-existent-vessel-uuid-99999/track?start_date=2026-09-01&end_date=2026-09-07');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Vessel GFW tidak ditemukan.',
            ]);
    }

    /**
     * TEST 5: Verify NO HTTP call is made to /v3/vessels/{id}/tracks during local track retrieval.
     */
    public function test_5_no_http_call_to_tracks_endpoint_during_track_retrieval(): void
    {
        // Calling track with MMSI
        $response1 = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/525137044/track?start_date=2026-09-01&end_date=2026-09-07');
        $response1->assertStatus(200);

        // Calling track with GFW Vessel UUID
        $response2 = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/3133bce7c-c6b8-a367-d03b-0e06b677bc23/track?start_date=2026-09-01&end_date=2026-09-07');
        $response2->assertStatus(200);

        // Calling track for vessel with 0 presences
        $response3 = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/valid-vessel-no-presence-uuid/track?start_date=2026-09-01&end_date=2026-09-07');
        $response3->assertStatus(200);

        // Crucial Assertion: Zero HTTP calls to GFW upstream tracks API
        Http::assertNothingSent();
    }

    /**
     * TEST 6: Presence synchronization from Events is idempotent and duplicate-safe.
     */
    public function test_6_presence_synchronization_from_events_is_idempotent(): void
    {
        $testVesselId = 'synced-test-vessel-uuid-'.uniqid();
        $testMmsi = '525'.rand(100000, 999999);

        $eventPayload = [
            [
                'id' => 'evt-test-sync-1',
                'vessel' => [
                    'id' => $testVesselId,
                    'name' => 'KM SYNC TEST',
                    'mmsi' => $testMmsi,
                    'flag' => 'IDN',
                    'type' => 'fishing',
                ],
                'start' => '2026-09-02T08:00:00Z',
                'end' => '2026-09-02T12:00:00Z',
                'position' => [
                    'lat' => 5.25,
                    'lon' => 97.50,
                ],
                'speed' => 6.2,
                'heading' => 180,
            ],
            [
                'id' => 'evt-test-sync-2',
                'vessel' => [
                    'id' => $testVesselId,
                    'name' => 'KM SYNC TEST',
                    'mmsi' => $testMmsi,
                    'flag' => 'IDN',
                    'type' => 'fishing',
                ],
                'start' => '2026-09-03T10:00:00Z',
                'end' => '2026-09-03T14:00:00Z',
                'position' => [
                    'lat' => 5.35,
                    'lon' => 97.60,
                ],
                'speed' => 5.8,
                'heading' => 190,
            ],
        ];

        /** @var GfwIngestionService $ingestionService */
        $ingestionService = app(GfwIngestionService::class);

        // Run sync 1 (First sync -> inserts)
        $stats1 = $ingestionService->ingestEvents($eventPayload, ['aoi' => 'zee-indonesia-aceh']);
        $this->assertSame(2, $stats1['presence_processed']);
        $this->assertSame(2, $stats1['presence_new']);
        $this->assertSame(0, $stats1['presence_duplicate']);

        $countAfterSync1 = GfwVesselPresence::where('gfw_vessel_id', $testVesselId)->count();
        $this->assertSame(2, $countAfterSync1);

        // Run sync 2 (Second sync with identical data -> idempotent ignore / 0 duplicates inserted)
        $stats2 = $ingestionService->ingestEvents($eventPayload, ['aoi' => 'zee-indonesia-aceh']);
        $this->assertSame(2, $stats2['presence_processed']);
        $this->assertSame(0, $stats2['presence_new'], 'Second sync must not insert duplicate presences');
        $this->assertSame(2, $stats2['presence_duplicate']);

        $countAfterSync2 = GfwVesselPresence::where('gfw_vessel_id', $testVesselId)->count();
        $this->assertSame(2, $countAfterSync2, 'Presence count in database must remain exactly 2');

        // Now query Track for this newly synced vessel via Track API
        $trackRes = $this->actingAs($this->user)
            ->getJson("/api/gfw/vessels/{$testVesselId}/track?start_date=2026-09-01&end_date=2026-09-07");
        $trackRes->assertStatus(200);
        $this->assertSame(2, $trackRes->json('points_count'));
    }

    /**
     * TEST 7: Date filtering on presence track returns only points within date range.
     */
    public function test_7_date_filtering_on_presence_track(): void
    {
        // Querying 2026-09-02 to 2026-09-04 returns points within that range (8 points)
        $expectedCount = GfwVesselPresence::where('gfw_vessel_id', '3133bce7c-c6b8-a367-d03b-0e06b677bc23')
            ->whereBetween('observed_at', ['2026-09-02 00:00:00', '2026-09-04 23:59:59'])
            ->count();

        $response = $this->actingAs($this->user)
            ->getJson('/api/gfw/vessels/3133bce7c-c6b8-a367-d03b-0e06b677bc23/track?start_date=2026-09-02&end_date=2026-09-04');

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertSame($expectedCount, $json['points_count']);
        foreach ($json['data']['features'] as $f) {
            $obsDate = substr($f['properties']['observed_at'], 0, 10);
            $this->assertGreaterThanOrEqual('2026-09-02', $obsDate);
            $this->assertLessThanOrEqual('2026-09-04', $obsDate);
        }
    }
}
