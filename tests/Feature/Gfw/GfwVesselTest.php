<?php

namespace Tests\Feature\Gfw;

use App\Models\Gfw\GfwVessel;
use App\Services\Gfw\GfwVesselService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwVesselTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<array<string, mixed>>|null */
    protected static ?array $vesselBackup = null;

    public static function tearDownAfterClass(): void
    {
        if (self::$vesselBackup !== null) {
            try {
                GfwVessel::query()->delete();
                foreach (self::$vesselBackup as $v) {
                    GfwVessel::create($v);
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
        }

        Http::preventStrayRequests();
        Cache::flush();
        Config::set('gfw.api_key', 'test-valid-gfw-key');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');
        GfwVessel::query()->delete();
    }

    public function test_vessel_search_returns_normalized_data_and_persists_to_gfw_vessels(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 1,
                'entries' => [
                    [
                        'id' => 'gfw-vessel-aceh-01',
                        'shipname' => 'KM MEULABOH RAYA',
                        'mmsi' => '525001234',
                        'imo' => '9876543',
                        'flag' => 'IDN',
                        'vesselType' => 'fishing',
                        'geartype' => 'tuna_purse_seine',
                        'lengthM' => 28.5,
                        'tonnageGt' => 120.5,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/search?query=MEULABOH');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cached' => false,
                'total' => 1,
            ])
            ->assertJsonPath('data.0.gfw_vessel_id', 'gfw-vessel-aceh-01')
            ->assertJsonPath('data.0.name', 'KM MEULABOH RAYA')
            ->assertJsonPath('data.0.mmsi', '525001234')
            ->assertJsonPath('data.0.imo', '9876543')
            ->assertJsonPath('data.0.flag', 'IDN')
            ->assertJsonPath('data.0.vessel_type', 'fishing')
            ->assertJsonPath('data.0.gear_type', 'tuna_purse_seine')
            ->assertJsonPath('data.0.length_m', 28.5)
            ->assertJsonPath('data.0.tonnage_gt', 120.5);

        // Verify persisted to dedicated gfw_vessels table (not local vessels table)
        $this->assertDatabaseHas('gfw_vessels', [
            'gfw_vessel_id' => 'gfw-vessel-aceh-01',
            'name' => 'KM MEULABOH RAYA',
            'mmsi' => '525001234',
        ], 'gfw');

        // Local vessels table must remain untouched
        $this->assertDatabaseCount('vessels', 0);
    }

    public function test_vessel_search_returns_empty_when_no_vessels_match(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/search?query=NONEXISTENT');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'total' => 0,
                'data' => [],
            ]);
    }

    public function test_vessel_show_returns_404_when_vessel_not_found(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/unknown-id*' => Http::response([
                'error' => 'Not found',
            ], 404),
        ]);

        $response = $this->getJson('/api/gfw/vessels/unknown-id');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'error' => 'Vessel tidak ditemukan pada data GFW.',
            ]);
    }

    public function test_vessel_search_handles_api_failure_gracefully(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Internal GFW gateway error',
            ], 500),
        ]);

        $response = $this->getJson('/api/gfw/vessels/search?query=ERROR_TRIGGER');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'data' => [],
            ]);
    }

    public function test_cache_hit_and_cache_miss_behavior(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 1,
                'entries' => [
                    [
                        'id' => 'vessel-cache-test',
                        'shipname' => 'KM BANDA ACEH',
                        'mmsi' => '525999888',
                    ],
                ],
            ], 200),
        ]);

        // 1. Initial request -> Cache Miss
        $firstResponse = $this->getJson('/api/gfw/vessels/search?query=BANDA');
        $firstResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cached' => false,
                'total' => 1,
            ]);

        Http::assertSentCount(1);

        // 2. Second request -> Cache Hit (no additional HTTP request sent)
        $secondResponse = $this->getJson('/api/gfw/vessels/search?query=BANDA');
        $secondResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'cached' => true,
                'total' => 1,
            ]);

        Http::assertSentCount(1);
    }

    public function test_vessel_service_normalizes_malformed_or_partial_payload(): void
    {
        $service = app(GfwVesselService::class);

        // Payload with missing fields and nested structures
        $malformedPayload = [
            'vesselId' => 'vessel-partial-01',
            'combinedInfo' => [
                'shipname' => 'KM PARTIAL DATA',
            ],
            // Missing mmsi, imo, flag, length, etc.
        ];

        $normalized = $service->normalize($malformedPayload);

        $this->assertEquals('vessel-partial-01', $normalized['gfw_vessel_id']);
        $this->assertEquals('KM PARTIAL DATA', $normalized['name']);
        $this->assertNull($normalized['mmsi']);
        $this->assertNull($normalized['imo']);
        $this->assertNull($normalized['flag']);
        $this->assertNull($normalized['length_m']);
        $this->assertNull($normalized['tonnage_gt']);
        $this->assertIsArray($normalized['raw_data']);
    }

    public function test_api_key_is_never_exposed_in_search_or_show_response(): void
    {
        $secretKey = 'ultra-secret-gfw-token-private-998877';
        Config::set('gfw.api_key', $secretKey);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Unauthorized or Forbidden key',
            ], 403),
            'https://gateway.api.globalfishingwatch.org/v3/vessels/secret-test*' => Http::response([
                'error' => 'Forbidden access',
            ], 403),
        ]);

        $searchResponse = $this->get('/api/gfw/vessels/search?query=TEST');
        $searchResponse->assertDontSee($secretKey);
        $searchResponse->assertDontSee('Bearer');

        $showResponse = $this->get('/api/gfw/vessels/secret-test');
        $showResponse->assertDontSee($secretKey);
        $showResponse->assertDontSee('Bearer');
    }

    public function test_search_validates_short_query(): void
    {
        $response = $this->getJson('/api/gfw/vessels/search?query=a');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Parameter query minimal 2 karakter.',
            ]);

        Http::assertNothingSent();
    }

    public function test_search_returns_503_when_api_key_is_unconfigured(): void
    {
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/vessels/search?query=ACEH');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'error' => 'GFW API key is not configured.',
            ]);

        Http::assertNothingSent();
    }

    public function test_vessels_index_without_query_returns_local_observatory_vessels_with_consistent_contract(): void
    {
        $vessel = GfwVessel::create([
            'gfw_vessel_id' => 'vessel-local-obs-01',
            'name' => 'KM MEULABOH BAHARI',
            'mmsi' => '525009988',
            'imo' => '9988776',
            'flag' => 'IDN',
            'vessel_type' => 'fishing',
            'gear_type' => 'purse_seine',
            'length_m' => 30.5,
            'tonnage_gt' => 140.0,
            'last_synced_at' => now(),
        ]);

        $response = $this->getJson('/api/gfw/vessels');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'count' => 1,
                    'total' => 1,
                ],
            ])
            ->assertJsonPath('data.0.id', 'vessel-local-obs-01')
            ->assertJsonPath('data.0.gfw_vessel_id', 'vessel-local-obs-01')
            ->assertJsonPath('data.0.name', 'KM MEULABOH BAHARI')
            ->assertJsonPath('data.0.shipname', 'KM MEULABOH BAHARI')
            ->assertJsonPath('data.0.mmsi', '525009988')
            ->assertJsonPath('data.0.flag', 'IDN');

        Http::assertNothingSent();
    }

    public function test_vessels_index_with_date_range_and_query_validation(): void
    {
        $vessel = GfwVessel::create([
            'gfw_vessel_id' => 'vessel-local-obs-02',
            'name' => 'KM ACEH JAYA',
            'mmsi' => '525001122',
            'flag' => 'IDN',
            'last_synced_at' => now(),
        ]);

        $response = $this->getJson('/api/gfw/vessels?start_date=2026-09-15&end_date=2026-09-22&limit=25');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'meta' => [
                    'count' => 1,
                ],
            ]);

        Http::assertNothingSent();
    }
}
