<?php

namespace Tests\Feature\Gfw;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class GfwInternalApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Cache::flush();
        RateLimiter::clear('gfw-api');
        Config::set('gfw.api_key', 'valid-internal-test-key-999');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');
    }

    public function test_gateway_vessels_endpoint_returns_standard_envelope(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'total' => 1,
                'entries' => [
                    [
                        'id' => 'vessel-gfw-lampulo-01',
                        'shipname' => 'KM LAMPULO RAYA',
                        'mmsi' => '525009999',
                        'flag' => 'IDN',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/vessels?query=LAMPULO');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'source',
                'data' => [
                    '*' => [
                        'gfw_vessel_id',
                        'name',
                        'mmsi',
                        'flag',
                    ],
                ],
                'meta' => [
                    'total',
                    'cached',
                    'query',
                ],
            ])
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
            ])
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.gfw_vessel_id', 'vessel-gfw-lampulo-01');
    }

    public function test_gateway_activity_endpoint_returns_standard_envelope(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/activity*' => Http::response([
                'entries' => [
                    [
                        'vesselId' => 'vessel-act-01',
                        'lat' => 5.50,
                        'lon' => 95.30,
                        'timestamp' => '2026-09-18T10:00:00Z',
                        'hours' => 3.0,
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/activity?region=aceh_waters');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'source',
                'data',
                'meta' => [
                    'region',
                    'query_period',
                    'total',
                    'cached',
                    'latency_notice',
                ],
            ])
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
            ]);
    }

    public function test_gateway_events_endpoint_returns_standard_envelope(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [
                    [
                        'id' => 'evt-fishing-001',
                        'type' => 'fishing',
                        'vesselId' => 'vessel-f-01',
                        'lat' => 5.20,
                        'lon' => 95.10,
                        'start' => '2026-09-15T00:00:00Z',
                        'end' => '2026-09-15T04:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/events?type=apparent_fishing&region=indonesia_eez');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'source',
                'data',
                'meta' => [
                    'event_type',
                    'semantic_label',
                    'semantic_disclaimer',
                    'region',
                    'query_period',
                    'total',
                    'cached',
                ],
            ])
            ->assertJson([
                'success' => true,
                'source' => 'global_fishing_watch',
            ]);
    }

    public function test_gateway_validation_failure_returns_422_with_standard_envelope(): void
    {
        // Query too short (1 character)
        $response = $this->getJson('/api/gfw/vessels?query=a');

        $response->assertStatus(422)
            ->assertJsonStructure([
                'success',
                'source',
                'error',
                'status',
            ])
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 422,
            ]);

        Http::assertNothingSent();
    }

    public function test_gateway_rate_limiting_enforces_429_too_many_requests(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [],
            ], 200),
        ]);

        // Send 60 allowed requests
        for ($i = 0; $i < 60; $i++) {
            $res = $this->getJson('/api/gfw/vessels?query=TEST'.($i % 5));
            $this->assertEquals(200, $res->status());
        }

        // 61st request must trigger 429 Too Many Requests
        $limitResponse = $this->getJson('/api/gfw/vessels?query=TEST_BLOCKED');
        $limitResponse->assertStatus(429)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 429,
            ]);
    }

    public function test_gateway_unconfigured_api_key_returns_503(): void
    {
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/vessels?query=ACEH');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 503,
            ]);

        Http::assertNothingSent();
    }

    public function test_gateway_external_api_failure_returns_502(): void
    {
        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Bad Gateway upstream',
            ], 502),
        ]);

        $response = $this->getJson('/api/gfw/vessels?query=ERROR_TRIGGER');

        $response->assertStatus(502)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 502,
            ]);
    }

    public function test_gateway_never_leaks_api_keys_or_bearer_tokens(): void
    {
        $secretKey = 'super-secret-production-gfw-token-99887766';
        Config::set('gfw.api_key', $secretKey);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Unauthorized error',
            ], 401),
        ]);

        $response = $this->get('/api/gfw/vessels?query=SECRET_CHECK');

        $response->assertDontSee($secretKey);
        $response->assertDontSee('Bearer');
    }
}
