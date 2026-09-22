<?php

namespace Tests\Feature\Gfw;

use App\Services\Gfw\GfwApiService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwHealthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_health_returns_unavailable_when_api_key_is_not_configured(): void
    {
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 'unavailable',
                'message' => 'GFW API key is not configured.',
            ]);

        Http::assertNothingSent();
    }

    public function test_health_returns_connected_when_gfw_api_succeeds(): void
    {
        Config::set('gfw.api_key', 'test-valid-token-12345');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [],
                'total' => 0,
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(200)
            ->assertExactJson([
                'success' => true,
                'source' => 'global_fishing_watch',
                'status' => 'connected',
            ]);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'gateway.api.globalfishingwatch.org/v3/vessels/search')
                && $request->hasHeader('Authorization', 'Bearer test-valid-token-12345')
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_health_returns_unavailable_when_gfw_api_returns_error(): void
    {
        Config::set('gfw.api_key', 'invalid-token');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Unauthorized access token',
            ], 401),
        ]);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 'unavailable',
            ]);
    }

    public function test_health_endpoint_never_exposes_api_key_or_secrets(): void
    {
        $secretKey = 'sensitive-super-secret-gfw-key-xyz999';
        Config::set('gfw.api_key', $secretKey);
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Forbidden token',
            ], 403),
        ]);

        $response = $this->get('/api/gfw/health');

        $response->assertDontSee($secretKey);
        $response->assertDontSee('Bearer');
    }

    public function test_health_returns_connected_when_gfw_api_token_is_configured(): void
    {
        Config::set('gfw.api_token', 'test-valid-jwt-token');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [],
                'total' => 0,
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(200)
            ->assertExactJson([
                'success' => true,
                'source' => 'global_fishing_watch',
                'status' => 'connected',
            ]);

        Http::assertSent(function (Request $request) {
            return $request->hasHeader('Authorization', 'Bearer test-valid-jwt-token');
        });
    }

    public function test_health_handles_401_unauthorized_from_gfw_safely(): void
    {
        Config::set('gfw.api_token', 'invalid-expired-token');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Invalid or expired token',
            ], 401),
        ]);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 'unavailable',
            ]);

        $response->assertDontSee('invalid-expired-token');
        $response->assertDontSee('Bearer');
    }

    public function test_health_handles_403_forbidden_from_gfw_safely(): void
    {
        Config::set('gfw.api_token', 'forbidden-token');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Forbidden resource access',
            ], 403),
        ]);

        $response = $this->getJson('/api/gfw/health');

        $response->assertStatus(503)
            ->assertJson([
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 'unavailable',
            ]);

        $response->assertDontSee('forbidden-token');
        $response->assertDontSee('Bearer');
    }

    public function test_service_returns_safe_structured_array_on_direct_call(): void
    {
        Config::set('gfw.api_token', 'test-direct-key');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [['id' => 'vessel-1']],
            ], 200),
        ]);

        $service = new GfwApiService;
        $this->assertTrue($service->isConfigured());

        $result = $service->get('/vessels/search', ['query' => 'KM']);
        $this->assertTrue($result['success']);
        $this->assertEquals(200, $result['status']);
        $this->assertArrayHasKey('entries', $result['data']);
    }
}
