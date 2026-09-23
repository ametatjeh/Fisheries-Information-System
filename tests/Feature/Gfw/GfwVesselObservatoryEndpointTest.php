<?php

namespace Tests\Feature\Gfw;

use App\Models\User;
use App\Services\GFWService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class GfwVesselObservatoryEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $gisUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();

        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-secret-token-gfw-obs-01');
        Config::set('gfw.fishing_events_dataset', 'public-global-fishing-events:latest');

        // Create permission and users
        $gisPermission = Permission::firstOrCreate(['name' => 'access.gis', 'guard_name' => 'web']);

        $this->gisUser = User::factory()->create();
        $this->gisUser->givePermissionTo($gisPermission);

        $this->regularUser = User::factory()->create();
    }

    /**
     * 1. Authentication and Authorization on /gfw/vessels
     */
    public function test_guest_is_redirected_to_login_when_accessing_observatory(): void
    {
        $response = $this->get('/gfw/vessels');
        $response->assertRedirect('/login');
    }

    public function test_user_without_gis_permission_is_forbidden_from_observatory(): void
    {
        $response = $this->actingAs($this->regularUser)->get('/gfw/vessels');
        $response->assertStatus(403);
    }

    public function test_authorized_user_can_access_gfw_vessel_observatory(): void
    {
        $response = $this->actingAs($this->gisUser)->get('/gfw/vessels');

        $response->assertStatus(200)
            ->assertSee('GFW VESSEL OBSERVATORY')
            ->assertSee('ZEE Indonesia – Kawasan Aceh')
            ->assertSee('AOI Source: BIG')
            ->assertSee('Vessel Data: Global Fishing Watch')
            ->assertSee('Total Vessels')
            ->assertSee('Fishing Vessels')
            ->assertSee('Other Vessels')
            ->assertSee('Flags')
            ->assertSee('maplibre-gl@4.7.1')
            ->assertSee('gfw-vessels-map')
            ->assertSee('vessel-search-input')
            ->assertSee('vessel-detail-card')
            ->assertSee('vessel-list-container');
    }

    /**
     * 2. API Contract with BIG AOI: GET /api/gfw/vessels/zee-indonesia-aceh
     */
    public function test_vessels_endpoint_returns_structured_contract_with_big_aoi(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 3,
                'entries' => [
                    [
                        'id' => 'evt-001',
                        'type' => 'fishing',
                        'start' => '2026-09-01T02:00:00Z',
                        'end' => '2026-09-01T08:00:00Z',
                        'position' => ['lat' => 5.25, 'lon' => 95.80],
                        'vessel' => [
                            'id' => 'vessel-idn-01',
                            'name' => 'KM SAMUDRA ACEH',
                            'ssvid' => '525001111',
                            'imo' => '9111111',
                            'flag' => 'IDN',
                            'type' => 'fishing',
                            'length' => 28.5,
                            'tonnage' => 110.0,
                            'gear' => 'Trawlers',
                        ],
                    ],
                    [
                        'id' => 'evt-002',
                        'type' => 'port_visit',
                        'start' => '2026-09-02T10:00:00Z',
                        'end' => '2026-09-02T14:00:00Z',
                        'position' => ['lat' => 5.58, 'lon' => 95.32],
                        'vessel' => [
                            'id' => 'vessel-mys-02',
                            'name' => 'CARGO MALACCA',
                            'ssvid' => '533002222',
                            'imo' => '9222222',
                            'flag' => 'MYS',
                            'type' => 'cargo',
                            'length' => 120.0,
                            'tonnage' => 5000.0,
                        ],
                    ],
                    [
                        'id' => 'evt-003',
                        'type' => 'encounter',
                        'start' => '2026-09-03T01:00:00Z',
                        'end' => '2026-09-03T05:00:00Z',
                        'position' => ['lat' => 4.80, 'lon' => 97.20],
                        'vessel' => [
                            'id' => 'vessel-tha-03',
                            'name' => 'OCEAN CARRIER',
                            'ssvid' => '567003333',
                            'imo' => '9333333',
                            'flag' => 'THA',
                            'type' => 'carrier',
                            'length' => 85.0,
                            'tonnage' => 2200.0,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'aoi' => [
                    'id' => 'zee-indonesia-aceh',
                    'name' => 'ZEE Indonesia - Kawasan Aceh',
                    'source' => 'BIG',
                    'crs' => 'EPSG:4326',
                ],
                'period' => [
                    'start' => '2026-09-01',
                    'end' => '2026-09-07',
                ],
                'summary' => [
                    'total_vessels' => 3,
                    'fishing_vessels' => 1,
                    'other_vessels' => 2,
                ],
                'pagination' => [
                    'limit' => 50,
                    'offset' => 0,
                    'has_more' => false,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'aoi' => ['id', 'name', 'source', 'crs'],
                'period' => ['start', 'end'],
                'summary' => ['total_vessels', 'fishing_vessels', 'other_vessels'],
                'vessels' => [
                    '*' => [
                        'id',
                        'name',
                        'mmsi',
                        'imo',
                        'flag',
                        'vessel_type',
                        'length',
                        'tonnage',
                        'engine_power',
                        'gear',
                        'first_seen',
                        'last_seen',
                        'activity',
                        'position' => ['lat', 'lon'],
                    ],
                ],
                'pagination' => ['limit', 'offset', 'has_more'],
            ]);

        // Verify vessel data accuracy
        $vessels = $response->json('vessels');
        $this->assertCount(3, $vessels);

        $fishingVessel = collect($vessels)->firstWhere('id', 'vessel-idn-01');
        $this->assertNotNull($fishingVessel);
        $this->assertSame('KM SAMUDRA ACEH', $fishingVessel['name']);
        $this->assertSame('525001111', $fishingVessel['mmsi']);
        $this->assertSame('Fishing', $fishingVessel['vessel_type']);
        $this->assertSame('Fishing Activity', $fishingVessel['activity']);
        $this->assertEquals(28.5, $fishingVessel['length']);
        $this->assertEquals(110.0, $fishingVessel['tonnage']);
        $this->assertSame('Trawlers', $fishingVessel['gear']);

        $cargoVessel = collect($vessels)->firstWhere('id', 'vessel-mys-02');
        $this->assertNotNull($cargoVessel);
        $this->assertSame('Cargo', $cargoVessel['vessel_type']);
        $this->assertSame('Port Visit', $cargoVessel['activity']);

        $carrierVessel = collect($vessels)->firstWhere('id', 'vessel-tha-03');
        $this->assertNotNull($carrierVessel);
        $this->assertSame('Carrier', $carrierVessel['vessel_type']);
        $this->assertSame('Encounter', $carrierVessel['activity']);
    }

    /**
     * 3. Empty result handling (0 vessels detected)
     */
    public function test_empty_result_returns_zero_vessels_summary(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'summary' => [
                    'total_vessels' => 0,
                    'fishing_vessels' => 0,
                    'other_vessels' => 0,
                ],
                'vessels' => [],
                'pagination' => [
                    'limit' => 50,
                    'offset' => 0,
                    'has_more' => false,
                ],
            ]);
    }

    /**
     * 4. GFW API error handling (distinguishing API error from empty result)
     */
    public function test_gfw_api_error_returns_appropriate_failure_status_and_not_zero_vessels(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Internal GFW Server Error',
            ], 502),
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API server error',
            ]);

        // Must not return success or 0 vessels on upstream failure
        $this->assertFalse($response->json('success'));
        $this->assertNull($response->json('vessels'));
    }

    /**
     * 5. Timeout / Connection Exception handling
     */
    public function test_timeout_or_connection_exception_handling(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => function () {
                throw new ConnectionException('Connection timed out after 30 seconds');
            },
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'message' => 'Unable to connect to GFW API',
            ]);
    }

    /**
     * 6. Token validation & protection
     */
    public function test_missing_gfw_token_returns_401(): void
    {
        Config::set('services.gfw.token', null);
        Config::set('gfw.token', null);
        Config::set('services.gfw.api_token', null);
        Config::set('gfw.api_token', null);
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'GFW API token is not configured',
            ]);
    }

    public function test_token_leakage_protection(): void
    {
        $secretToken = 'test-secret-token-gfw-obs-01';

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh');

        $response->assertStatus(200);
        $content = (string) $response->getContent();
        $this->assertStringNotContainsString($secretToken, $content);
        $this->assertStringNotContainsString('bearer', strtolower($content));
        $this->assertStringNotContainsString('authorization', strtolower($content));
    }

    /**
     * 7. Date validation: Format, calendar validity, chronological, max 7 days
     */
    public function test_invalid_date_format_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=invalid-date');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Format tanggal harus berformat YYYY-MM-DD.',
            ]);
    }

    public function test_invalid_calendar_date_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-02-31&end=2026-03-05');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Tanggal yang dimasukkan tidak valid.',
            ]);
    }

    public function test_start_date_greater_than_end_date_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-08&end=2026-09-01');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'start_date harus lebih kecil atau sama dengan end_date.',
            ]);
    }

    public function test_date_range_exceeding_7_days_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-01&end=2026-09-08');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_date_range_of_exactly_7_days_passes_validation(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?start=2026-09-01&end=2026-09-07');
        $response->assertStatus(200);
    }

    /**
     * 8. Pagination tests
     */
    public function test_pagination_limits_and_offset(): void
    {
        $mockEntries = [];
        for ($i = 1; $i <= 5; $i++) {
            $mockEntries[] = [
                'id' => "evt-page-{$i}",
                'type' => 'fishing',
                'start' => '2026-09-01T04:00:00Z',
                'end' => '2026-09-01T08:00:00Z',
                'position' => ['lat' => 5.0 + ($i * 0.1), 'lon' => 95.0 + ($i * 0.1)],
                'vessel' => [
                    'id' => "vessel-page-{$i}",
                    'name' => "KM TEST {$i}",
                    'ssvid' => "52500000{$i}",
                    'flag' => 'IDN',
                    'type' => 'fishing',
                ],
            ];
        }

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 5,
                'entries' => $mockEntries,
            ], 200),
        ]);

        // Request limit 2, offset 0 -> 2 items, has_more = true
        $resPage1 = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=2&offset=0');
        $resPage1->assertStatus(200)
            ->assertJsonPath('summary.total_vessels', 5)
            ->assertJsonPath('pagination.limit', 2)
            ->assertJsonPath('pagination.offset', 0)
            ->assertJsonPath('pagination.has_more', true);
        $this->assertCount(2, $resPage1->json('vessels'));

        // Request limit 2, offset 4 -> 1 item, has_more = false
        $resPage3 = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=2&offset=4');
        $resPage3->assertStatus(200)
            ->assertJsonPath('pagination.offset', 4)
            ->assertJsonPath('pagination.has_more', false);
        $this->assertCount(1, $resPage3->json('vessels'));
    }

    public function test_invalid_limit_parameter_returns_422(): void
    {
        $response = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=0');
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Parameter limit harus berupa bilangan bulat antara 1 dan 100.',
            ]);

        $responseOver = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?limit=101');
        $responseOver->assertStatus(422);
    }

    /**
     * 9. Vessel Type and Flag Filter tests
     */
    public function test_vessel_type_filter(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 3,
                'entries' => [
                    [
                        'id' => 'evt-1',
                        'type' => 'fishing',
                        'vessel' => ['id' => 'v-1', 'name' => 'FISH 1', 'type' => 'fishing', 'flag' => 'IDN'],
                    ],
                    [
                        'id' => 'evt-2',
                        'type' => 'port_visit',
                        'vessel' => ['id' => 'v-2', 'name' => 'CARRIER 2', 'type' => 'carrier', 'flag' => 'THA'],
                    ],
                    [
                        'id' => 'evt-3',
                        'type' => 'loitering',
                        'vessel' => ['id' => 'v-3', 'name' => 'TANKER 3', 'type' => 'tanker', 'flag' => 'MYS'],
                    ],
                ],
            ], 200),
        ]);

        // Filter for Carrier only
        $res = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?vessel_type=Carrier');
        $res->assertStatus(200)
            ->assertJsonPath('summary.total_vessels', 1)
            ->assertJsonPath('summary.fishing_vessels', 0)
            ->assertJsonPath('summary.other_vessels', 1);

        $vessels = $res->json('vessels');
        $this->assertCount(1, $vessels);
        $this->assertSame('Carrier', $vessels[0]['vessel_type']);
    }

    public function test_flag_filter(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'total' => 2,
                'entries' => [
                    ['id' => 'evt-1', 'vessel' => ['id' => 'v-1', 'name' => 'V1', 'flag' => 'IDN', 'type' => 'fishing']],
                    ['id' => 'evt-2', 'vessel' => ['id' => 'v-2', 'name' => 'V2', 'flag' => 'THA', 'type' => 'fishing']],
                ],
            ], 200),
        ]);

        $res = $this->getJson('/api/gfw/vessels/zee-indonesia-aceh?flag=IDN');
        $res->assertStatus(200)
            ->assertJsonPath('summary.total_vessels', 1);

        $vessels = $res->json('vessels');
        $this->assertCount(1, $vessels);
        $this->assertSame('IDN', $vessels[0]['flag']);
    }

    /**
     * 10. Taxonomy mapping tests
     */
    public function test_taxonomy_normalization_across_official_types(): void
    {
        $this->assertSame('Fishing', GFWService::normalizeVesselType('fishing'));
        $this->assertSame('Fishing', GFWService::normalizeVesselType('trawlers'));
        $this->assertSame('Fishing', GFWService::normalizeVesselType('purse_seiners'));
        $this->assertSame('Carrier', GFWService::normalizeVesselType('carrier'));
        $this->assertSame('Carrier', GFWService::normalizeVesselType('reefer'));
        $this->assertSame('Bunker', GFWService::normalizeVesselType('bunker'));
        $this->assertSame('Tanker', GFWService::normalizeVesselType('tanker'));
        $this->assertSame('Cargo', GFWService::normalizeVesselType('cargo'));
        $this->assertSame('Passenger', GFWService::normalizeVesselType('passenger'));
        $this->assertSame('Recreational', GFWService::normalizeVesselType('recreational'));
        $this->assertSame('Support', GFWService::normalizeVesselType('support'));
        $this->assertSame('Support', GFWService::normalizeVesselType('tug'));
        $this->assertSame('Other', GFWService::normalizeVesselType('other'));
        $this->assertSame('Unknown', GFWService::normalizeVesselType(null));
        $this->assertSame('Unknown', GFWService::normalizeVesselType(''));
    }
}
