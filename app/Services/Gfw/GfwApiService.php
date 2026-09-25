<?php

namespace App\Services\Gfw;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GfwApiService
{
    protected string $baseUrl;

    protected ?string $apiKey;

    protected int $timeout;

    protected int $connectTimeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) (config('gfw.base_url') ?? config('services.gfw.base_url', 'https://gateway.api.globalfishingwatch.org/v3')), '/');
        $this->timeout = (int) (config('gfw.timeout') ?? config('services.gfw.timeout') ?? 60);
        $this->connectTimeout = (int) (config('gfw.connect_timeout') ?? config('services.gfw.connect_timeout') ?? 5);
    }

    /**
     * Retrieve the active GFW API token from server configuration.
     */
    public function getApiKey(): ?string
    {
        $key = config('gfw.api_token');
        if (empty($key)) {
            $key = config('gfw.api_key');
        }
        if (empty($key)) {
            $key = config('services.gfw.api_token');
        }
        if (empty($key)) {
            $key = config('services.gfw.token');
        }

        return ! empty($key) ? (string) $key : null;
    }

    /**
     * Check if the GFW API key is configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->getApiKey());
    }

    /**
     * Build the pre-configured HTTP client for GFW API v3.
     */
    protected function client(): PendingRequest
    {
        $token = $this->getApiKey();

        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->acceptJson()
            ->withToken($token ?? '');
    }

    /**
     * Perform a GET request to the GFW API.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->send('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Execute an HTTP request to GFW with safety, timeout, and logging.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function send(string $method, string $endpoint, array $options = []): array
    {
        $endpoint = '/'.ltrim($endpoint, '/');

        if (! $this->isConfigured()) {
            $this->logError($endpoint, null, 'GFW token missing');

            return [
                'success' => false,
                'status' => 500,
                'error' => 'GFW token missing',
                'data' => null,
            ];
        }

        $startTime = microtime(true);

        try {
            $client = $this->client();
            $methodLower = strtolower($method);

            /** @var Response $response */
            $response = match ($methodLower) {
                'get' => $client->get($endpoint, $options['query'] ?? []),
                'post' => $client->post($endpoint, $options['json'] ?? []),
                default => $client->send($method, $endpoint, $options),
            };

            $durationMs = (int) round((microtime(true) - $startTime) * 1000);

            if ($response->successful()) {
                Log::info('GFW API request completed', [
                    'endpoint' => $endpoint,
                    'host' => parse_url($this->baseUrl, PHP_URL_HOST),
                    'status' => $response->status(),
                    'duration_ms' => $durationMs,
                ]);

                return [
                    'success' => true,
                    'status' => $response->status(),
                    'duration_ms' => $durationMs,
                    'data' => $response->json() ?? [],
                ];
            }

            $status = $response->status();
            $sanitizedBody = mb_substr(strip_tags((string) $response->body()), 0, 300);
            $errorMessage = "GFW API returned status {$status}";
            $this->logError($endpoint, $status, $errorMessage, [
                'duration_ms' => $durationMs,
                'response_sample' => $sanitizedBody,
            ]);

            return [
                'success' => false,
                'status' => $status,
                'duration_ms' => $durationMs,
                'error' => $errorMessage,
                'data' => null,
            ];
        } catch (ConnectionException $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $msg = $e->getMessage();
            $isCurl28 = str_contains($msg, 'cURL error 28') || str_contains($msg, 'timed out');
            $this->logError($endpoint, 504, ($isCurl28 ? 'Upstream request timed out (cURL error 28)' : 'Connection failure reaching GFW API: '.$msg), [
                'duration_ms' => $durationMs,
                'timeout_config' => $this->timeout,
                'connect_timeout_config' => $this->connectTimeout,
                'exception_class' => get_class($e),
            ]);

            return [
                'success' => false,
                'status' => 504,
                'duration_ms' => $durationMs,
                'error' => 'Connection timeout or network failure reaching GFW API.',
                'data' => null,
            ];
        } catch (Throwable $e) {
            $durationMs = (int) round((microtime(true) - $startTime) * 1000);
            $this->logError($endpoint, 500, 'Unexpected GFW client error: '.$e->getMessage(), [
                'duration_ms' => $durationMs,
                'exception_class' => get_class($e),
            ]);

            return [
                'success' => false,
                'status' => 500,
                'duration_ms' => $durationMs,
                'error' => 'Unexpected error communicating with GFW API.',
                'data' => null,
            ];
        }
    }

    /**
     * Check health/connectivity to GFW API.
     *
     * @return array{success: bool, source: string, status: string, message?: string}
     */
    public function checkHealth(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'source' => 'global_fishing_watch',
                'status' => 'unavailable',
                'message' => 'GFW API key is not configured.',
            ];
        }

        // Send a lightweight test request to verify API connectivity and authentication
        $res = $this->get('/vessels/search', [
            'query' => 'INDONESIA',
            'datasets[0]' => (string) config('gfw.vessel_dataset', 'public-global-vessel-identity:latest'),
            'limit' => 1,
        ]);

        if ($res['success']) {
            return [
                'success' => true,
                'source' => 'global_fishing_watch',
                'status' => 'connected',
            ];
        }

        return [
            'success' => false,
            'source' => 'global_fishing_watch',
            'status' => 'unavailable',
            'message' => $res['error'] ?? 'GFW API service is unavailable.',
        ];
    }

    /**
     * Log failure safely without leaking API key, tokens, or credentials.
     */
    protected function logError(string $endpoint, ?int $status, string $message): void
    {
        Log::warning('GFW API communication failed', [
            'endpoint' => $endpoint,
            'status' => $status,
            'error' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
