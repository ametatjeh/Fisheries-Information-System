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
        $this->timeout = (int) config('gfw.timeout', 30);
        $this->connectTimeout = (int) config('gfw.connect_timeout', 5);
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
            $this->logError($endpoint, null, 'GFW API key is not configured.');

            return [
                'success' => false,
                'status' => 500,
                'error' => 'GFW API key is not configured.',
                'data' => null,
            ];
        }

        try {
            $client = $this->client();
            $methodLower = strtolower($method);

            /** @var Response $response */
            $response = match ($methodLower) {
                'get' => $client->get($endpoint, $options['query'] ?? []),
                'post' => $client->post($endpoint, $options['json'] ?? []),
                default => $client->send($method, $endpoint, $options),
            };

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->status(),
                    'data' => $response->json() ?? [],
                ];
            }

            $errorMessage = "GFW API returned status {$response->status()}";
            $this->logError($endpoint, $response->status(), $errorMessage);

            return [
                'success' => false,
                'status' => $response->status(),
                'error' => $errorMessage,
                'data' => null,
            ];
        } catch (ConnectionException $e) {
            $this->logError($endpoint, null, 'Connection timeout or network error: '.$e->getMessage());

            return [
                'success' => false,
                'status' => 504,
                'error' => 'Connection timeout or network failure reaching GFW API.',
                'data' => null,
            ];
        } catch (Throwable $e) {
            $this->logError($endpoint, null, 'Unexpected GFW client error: '.$e->getMessage());

            return [
                'success' => false,
                'status' => 500,
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
            'query' => '0',
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
