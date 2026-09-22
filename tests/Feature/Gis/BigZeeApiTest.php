<?php

namespace Tests\Feature\Gis;

use App\Models\FishCatch;
use App\Models\Fisher;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Models\Landing;
use App\Models\Vessel;
use App\Services\Gfw\AoiService;
use App\Services\Gis\BigMaritimeBoundaryService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BigZeeApiTest extends TestCase
{
    /**
     * Test 1: Endpoint GET /api/gis/big/zee returns HTTP 200.
     */
    public function test_endpoint_big_zee_returns_valid_http_200_response(): void
    {
        $response = $this->getJson('/api/gis/big/zee');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');
    }

    /**
     * Test 2: Response contains required envelope: success, source, layer, layer_id, crs, data.
     */
    public function test_response_payload_structure_matches_specification(): void
    {
        $response = $this->getJson('/api/gis/big/zee');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'source',
            'layer',
            'layer_id',
            'crs',
            'data' => [
                'type',
                'features',
            ],
        ]);

        $this->assertTrue($response->json('success'));
        $this->assertSame('BIG', $response->json('source'));
        $this->assertSame('Peta Batas ZEE', $response->json('layer'));
        $this->assertSame(10, $response->json('layer_id'));
        $this->assertSame('EPSG:4326', $response->json('crs'));
    }

    /**
     * Test 3: Data is valid GeoJSON FeatureCollection.
     */
    public function test_geojson_is_valid_feature_collection(): void
    {
        $response = $this->getJson('/api/gis/big/zee');

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertSame('FeatureCollection', $data['type'] ?? null);
        $this->assertIsArray($data['features'] ?? null);
        $this->assertNotEmpty($data['features']);
    }

    /**
     * Test 4: Features have LineString or MultiLineString geometry matching BIG spec.
     */
    public function test_geometry_is_linestring_or_multilinestring(): void
    {
        $response = $this->getJson('/api/gis/big/zee');

        $features = $response->json('data.features');
        $this->assertIsArray($features);

        $allowedGeometryTypes = ['LineString', 'MultiLineString'];

        foreach ($features as $feature) {
            $this->assertSame('Feature', $feature['type'] ?? null);
            $this->assertArrayHasKey('geometry', $feature);
            $this->assertContains($feature['geometry']['type'] ?? '', $allowedGeometryTypes);
            $this->assertIsArray($feature['geometry']['coordinates'] ?? null);
            $this->assertNotEmpty($feature['geometry']['coordinates']);

            // Properties should have normalized BIG status info
            $props = $feature['properties'] ?? [];
            $this->assertArrayHasKey('stslat', $props);
            $this->assertArrayHasKey('status_label', $props);
            $this->assertSame('Badan Informasi Geospasial (BIG)', $props['source'] ?? null);
            $this->assertSame('Peta Batas ZEE', $props['layer'] ?? null);
            $this->assertSame(10, $props['layer_id'] ?? null);
        }
    }

    /**
     * Test 5: Response contains zero secret tokens or credentials.
     */
    public function test_response_contains_no_sensitive_credentials_or_tokens(): void
    {
        $response = $this->getJson('/api/gis/big/zee');
        $content = $response->getContent();

        $this->assertStringNotContainsStringIgnoringCase('bearer', $content);
        $this->assertStringNotContainsStringIgnoringCase('api_key', $content);
        $this->assertStringNotContainsStringIgnoringCase('apikey', $content);
        $this->assertStringNotContainsStringIgnoringCase('token', $content);
        $this->assertStringNotContainsStringIgnoringCase('password', $content);
        $this->assertStringNotContainsStringIgnoringCase('secret', $content);
    }

    /**
     * Test 6: Cache mechanism works on consecutive requests.
     */
    public function test_caching_mechanism_caches_big_response(): void
    {
        // Request 1: populate cache
        $res1 = $this->getJson('/api/gis/big/zee');
        $res1->assertStatus(200);

        // Verify cache entry exists
        $this->assertTrue(Cache::has(BigMaritimeBoundaryService::CACHE_KEY));

        // Request 2: must return cached data
        $res2 = $this->getJson('/api/gis/big/zee');
        $res2->assertStatus(200);
        $this->assertTrue($res2->json('cached'));
    }

    /**
     * Test 7 & 8: Upstream BIG failure gracefully falls back to cache/storage without crashing.
     */
    public function test_graceful_fallback_when_upstream_big_fails(): void
    {
        // Clear runtime cache first to test storage backup
        Cache::forget(BigMaritimeBoundaryService::CACHE_KEY);

        // Simulate upstream BIG returning 500 error
        Http::fake([
            'https://kspservices.big.go.id/*' => Http::response('Internal Server Error', 500),
        ]);

        $response = $this->getJson('/api/gis/big/zee');

        $response->assertStatus(200);
        $this->assertTrue($response->json('success'));
        $this->assertSame('BIG', $response->json('source'));
        $this->assertSame(10, $response->json('layer_id'));
        $this->assertNotNull($response->json('data'));
        $this->assertSame('FeatureCollection', $response->json('data.type'));
        $this->assertArrayHasKey('notice', $response->json());
        $this->assertStringContainsString('cache terakhir', $response->json('notice'));
    }

    /**
     * Test 8b: Raw GeoJSON mode via ?raw=1 or ?geojson=1.
     */
    public function test_raw_geojson_query_parameter_returns_direct_feature_collection(): void
    {
        $response = $this->getJson('/api/gis/big/zee?geojson=1');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/geo+json', $response->headers->get('Content-Type') ?? '');
        $this->assertSame('FeatureCollection', $response->json('type'));
        $this->assertIsArray($response->json('features'));
    }

    /**
     * Test 8c: GET /api/gis/big/zee/aceh returns GeoJSON FeatureCollection filtered for Aceh.
     */
    public function test_endpoint_big_zee_aceh_returns_valid_geojson_feature_collection(): void
    {
        $response = $this->getJson('/api/gis/big/zee/aceh');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/geo+json', $response->headers->get('Content-Type') ?? '');
        $this->assertSame('FeatureCollection', $response->json('type'));
        $this->assertSame('ZEE — Data Resmi BIG', $response->json('name'));
        $this->assertSame('Aceh', $response->json('wilayah'));
        $this->assertNotEmpty($response->json('features'));

        // All features must have Aceh wilayah label
        foreach ($response->json('features') as $feature) {
            $this->assertSame('LineString', $feature['geometry']['type']);
            $this->assertSame('Aceh', $feature['properties']['wilayah']);
            $this->assertSame('ZEE — Data Resmi BIG', $feature['properties']['label']);
        }
    }

    /**
     * Test 9: /gfw/vessels view template and /api/gfw/vessels endpoint operate properly.
     */
    public function test_gfw_vessels_endpoint_continues_to_serve_vessel_data(): void
    {
        // Unauthenticated access to /gfw/vessels redirects to login
        $guestRes = $this->get('/gfw/vessels');
        $guestRes->assertRedirect('/login');

        // View file contains BIG official boundary integration for Aceh
        $viewContent = file_get_contents(resource_path('views/gfw/vessels.blade.php'));
        $this->assertStringContainsString('ZEE — Data Resmi BIG', $viewContent);
        $this->assertStringContainsString('/api/gis/big/zee/aceh', $viewContent);
        $this->assertStringContainsString('big-zee-aceh-line', $viewContent);
        $this->assertStringContainsString('toggle-big-zee-aceh', $viewContent);
        $this->assertStringNotContainsString('toggle-buffer', $viewContent);
        $this->assertStringNotContainsString('gfw-aoi-buffer-100nm', $viewContent);

        // Internal GFW vessels API returns success when configured
        Config::set('gfw.api_key', 'test-key-for-test');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/*' => Http::response([
                'total' => 0,
                'entries' => [],
            ], 200),
        ]);

        $apiRes = $this->getJson('/api/gfw/vessels?query=TEST');
        $apiRes->assertStatus(200);
        $this->assertTrue($apiRes->json('success'));
        $this->assertIsArray($apiRes->json('data'));
    }

    /**
     * Test 10: GFW API continues to use existing AOI service independently from BIG.
     */
    public function test_gfw_api_continues_to_use_aoi_service_independently(): void
    {
        $aoiService = app(AoiService::class);
        $geoJson = $aoiService->getZeeIndonesiaAcehGeometry();

        $this->assertIsArray($geoJson);
        $this->assertSame('FeatureCollection', $geoJson['type']);
        $this->assertNotEmpty($geoJson['features']);
        $this->assertSame('Polygon', $geoJson['features'][0]['geometry']['type']);

        // GFW AOI API endpoint returns summary info
        $res = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh');
        $res->assertStatus(200);
        $this->assertTrue($res->json('success'));
        $this->assertSame('Polygon', $res->json('geometry_type'));
    }

    /**
     * Test 11: +100 NM Observation Zone remains intact and independent.
     */
    public function test_buffer_100nm_remains_intact_and_independent(): void
    {
        $aoiService = app(AoiService::class);
        $summary = $aoiService->getZeeIndonesiaAcehBuffer100NmSummary();

        $this->assertTrue($summary['success']);
        $this->assertSame('Zona Observasi GFW +100 NM', $summary['name']);
        $this->assertSame('Polygon', $summary['geometry_type']);
        $this->assertSame(185200, $summary['buffer_distance_meters']);
        $this->assertSame(100, $summary['buffer_distance_nm']);

        // Buffer endpoint returns summary
        $res = $this->getJson('/api/gfw/aoi/zee-indonesia-aceh?buffer=100nm');
        $res->assertStatus(200);
        $this->assertTrue($res->json('success'));
        $this->assertSame('Polygon', $res->json('geometry_type'));
    }

    /**
     * Test 12 & 13: Database models and connection configurations remain strictly isolated.
     */
    public function test_databases_schema_and_isolation_remain_unchanged(): void
    {
        // GFW models use the dedicated 'gfw' connection
        $gfwVessel = new GfwVessel;
        $gfwPresence = new GfwVesselPresence;
        $this->assertSame('gfw', $gfwVessel->getConnectionName());
        $this->assertSame('gfw', $gfwPresence->getConnectionName());

        // Main domain models do NOT use GFW connection
        $vessel = new Vessel;
        $fisher = new Fisher;
        $catch = new FishCatch;
        $landing = new Landing;
        $this->assertNotSame('gfw', $vessel->getConnectionName());
        $this->assertNotSame('gfw', $fisher->getConnectionName());
        $this->assertNotSame('gfw', $catch->getConnectionName());
        $this->assertNotSame('gfw', $landing->getConnectionName());

        // Connection configurations are separated
        $gfwConfig = Config::get('database.connections.gfw');
        $this->assertIsArray($gfwConfig);
        $this->assertSame('sistem_gfw', env('GFW_DB_DATABASE', 'sistem_gfw'));
        $this->assertSame('sistem_gfw', $gfwConfig['database']);
        $this->assertNotSame('gfw', Config::get('database.default'));
    }
}
