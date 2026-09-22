<?php

namespace Tests\Unit\Gfw;

use App\Services\Gfw\GfwApiService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class GfwApiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    public function test_is_configured_returns_false_when_api_key_is_null_or_empty(): void
    {
        Config::set('gfw.api_token', null);
        Config::set('gfw.api_key', null);
        Config::set('services.gfw.api_token', null);
        $service = new GfwApiService;
        $this->assertFalse($service->isConfigured());

        Config::set('gfw.api_token', '');
        Config::set('gfw.api_key', '');
        $service = new GfwApiService;
        $this->assertFalse($service->isConfigured());
    }

    public function test_is_configured_returns_true_when_api_token_is_set(): void
    {
        Config::set('gfw.api_token', 'sample-gfw-api-token-value');
        $service = new GfwApiService;
        $this->assertTrue($service->isConfigured());
    }

    public function test_is_configured_returns_true_when_api_key_is_set(): void
    {
        Config::set('gfw.api_token', null);
        Config::set('gfw.api_key', 'valid-sample-key');
        $service = new GfwApiService;
        $this->assertTrue($service->isConfigured());
    }

    public function test_send_returns_graceful_error_when_api_key_is_not_configured(): void
    {
        Config::set('gfw.api_key', null);
        $service = new GfwApiService;

        $result = $service->get('/vessels/search');

        $this->assertFalse($result['success']);
        $this->assertSame(500, $result['status']);
        $this->assertSame('GFW API key is not configured.', $result['error']);
        $this->assertNull($result['data']);

        Http::assertNothingSent();
    }

    public function test_send_executes_http_request_with_bearer_token_and_headers(): void
    {
        Config::set('gfw.api_key', 'super-secret-key-12345');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'entries' => [
                    ['id' => 'VESSEL-1', 'shipname' => 'KM TEST'],
                ],
                'total' => 1,
            ], 200),
        ]);

        $service = new GfwApiService;
        $result = $service->get('/vessels/search', ['query' => 'KM TEST']);

        $this->assertTrue($result['success']);
        $this->assertSame(200, $result['status']);
        $this->assertIsArray($result['data']);
        $this->assertSame('VESSEL-1', $result['data']['entries'][0]['id']);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'vessels/search')
                && $request->hasHeader('Authorization', 'Bearer super-secret-key-12345')
                && $request->hasHeader('Accept', 'application/json');
        });
    }

    public function test_send_handles_upstream_http_errors_gracefully(): void
    {
        Config::set('gfw.api_key', 'test-key');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Rate limit exceeded',
            ], 429),
        ]);

        $service = new GfwApiService;
        $result = $service->get('/vessels/search');

        $this->assertFalse($result['success']);
        $this->assertSame(429, $result['status']);
        $this->assertStringContainsString('429', $result['error']);
        $this->assertNull($result['data']);
    }

    public function test_send_handles_timeout_and_connection_exceptions_gracefully(): void
    {
        Config::set('gfw.api_key', 'test-key');
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => function () {
                throw new ConnectionException('Connection timed out after 30 seconds');
            },
        ]);

        $service = new GfwApiService;
        $result = $service->get('/vessels/search');

        $this->assertFalse($result['success']);
        $this->assertSame(504, $result['status']);
        $this->assertStringContainsString('Connection timeout', $result['error']);
        $this->assertNull($result['data']);
    }

    public function test_logging_never_records_api_key_or_bearer_token(): void
    {
        $secretKey = 'super-confidential-gfw-token-abc';
        Config::set('gfw.api_key', $secretKey);
        Config::set('gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3');

        Http::fake([
            'https://gateway.api.globalfishingwatch.org/v3/vessels/search*' => Http::response([
                'error' => 'Internal server error from upstream GFW',
            ], 500),
        ]);

        $loggedMessages = [];
        Log::listen(function ($message) use (&$loggedMessages) {
            $loggedMessages[] = is_string($message->message) ? $message->message : json_encode($message->message);
            if (! empty($message->context)) {
                $loggedMessages[] = json_encode($message->context);
            }
        });

        $service = new GfwApiService;
        $result = $service->get('/vessels/search');

        $this->assertFalse($result['success']);

        // Check all log entries to verify zero secret leakage
        foreach ($loggedMessages as $entry) {
            $this->assertStringNotContainsString($secretKey, $entry);
            $this->assertStringNotContainsString('Bearer '.$secretKey, $entry);
        }
    }
}
