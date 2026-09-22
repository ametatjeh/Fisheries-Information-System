<?php

namespace Tests\Feature\Gfw;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GfwTestEndpointTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_endpoint_returns_200_when_gfw_connection_is_successful(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-valid-jwt-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'entries' => [],
                'total' => 0,
            ], 200),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(200)
            ->assertExactJson([
                'success' => true,
                'message' => 'GFW API connection successful',
                'status' => 200,
            ]);

        Http::assertSent(function (Request $request) {
            return str_contains($request->url(), 'https://gateway.api.globalfishingwatch.org/v3/events')
                && $request->hasHeader('Authorization', 'Bearer test-valid-jwt-token')
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_endpoint_returns_401_when_token_is_missing(): void
    {
        Config::set('services.gfw.token', null);
        Config::set('gfw.token', null);
        Config::set('services.gfw.api_token', null);
        Config::set('gfw.api_token', null);
        Config::set('gfw.api_key', null);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(401)
            ->assertExactJson([
                'success' => false,
                'message' => 'GFW API token is not configured',
                'status' => 401,
            ]);

        Http::assertNothingSent();
    }

    public function test_endpoint_returns_401_when_gfw_authentication_fails(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'invalid-token-12345');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(401)
            ->assertExactJson([
                'success' => false,
                'message' => 'GFW API authentication failed',
                'status' => 401,
            ]);
    }

    public function test_endpoint_returns_403_when_gfw_access_is_forbidden(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Forbidden',
            ], 403),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(403)
            ->assertExactJson([
                'success' => false,
                'message' => 'GFW API access forbidden',
                'status' => 403,
            ]);
    }

    public function test_endpoint_returns_429_when_gfw_rate_limit_is_reached(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Too Many Requests',
            ], 429),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(429)
            ->assertExactJson([
                'success' => false,
                'message' => 'GFW API rate limit reached',
                'status' => 429,
            ]);
    }

    public function test_endpoint_returns_502_when_gfw_returns_server_error(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Internal Server Error',
            ], 500),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(502)
            ->assertExactJson([
                'success' => false,
                'message' => 'GFW API server error',
                'status' => 502,
            ]);
    }

    public function test_endpoint_returns_503_on_connection_timeout_exception(): void
    {
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', 'test-token');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => function () {
                throw new ConnectionException('Connection timed out');
            },
        ]);

        $response = $this->getJson('/api/gfw/test');

        $response->assertStatus(503)
            ->assertExactJson([
                'success' => false,
                'message' => 'Unable to connect to GFW API',
                'status' => 503,
            ]);
    }

    public function test_token_is_never_leaked_in_response_body(): void
    {
        $secretToken = 'super-secret-token-do-not-leak';
        Config::set('services.gfw.url', 'https://gateway.api.globalfishingwatch.org');
        Config::set('services.gfw.token', $secretToken);

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/events*' => Http::response([
                'error' => 'Authorization header error: '.$secretToken,
            ], 401),
        ]);

        $response = $this->getJson('/api/gfw/test');

        $content = $response->getContent();
        $this->assertStringNotContainsString($secretToken, $content);
        $this->assertStringNotContainsString('Authorization', $content);
        $this->assertStringNotContainsString('Bearer', $content);
    }
}
