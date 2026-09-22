<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GFWService
{
    protected string $url;

    protected ?string $token;

    public function __construct()
    {
        $this->url = rtrim((string) (config('services.gfw.url') ?? config('gfw.url', 'https://gateway.api.globalfishingwatch.org')), '/');
        $this->token = config('services.gfw.token') ?: config('gfw.token') ?: config('services.gfw.api_token') ?: config('gfw.api_token') ?: config('gfw.api_key');
    }

    /**
     * Retrieve the configured token dynamically.
     */
    public function getToken(): ?string
    {
        $token = config('services.gfw.token') ?: config('gfw.token') ?: config('services.gfw.api_token') ?: config('gfw.api_token') ?: config('gfw.api_key');

        return ! empty($token) ? (string) $token : null;
    }

    /**
     * Retrieve the configured base URL dynamically.
     */
    public function getUrl(): string
    {
        $url = config('services.gfw.url') ?? config('gfw.url', 'https://gateway.api.globalfishingwatch.org');

        return rtrim((string) $url, '/');
    }

    /**
     * Test connection and authentication to the Global Fishing Watch API v3 Events endpoint.
     *
     * @return array{success: bool, message: string, status: int}
     */
    public function testConnection(): array
    {
        $token = $this->getToken();
        $url = $this->getUrl();

        if (empty($token)) {
            $this->logWarning('/v3/events', null, 'GFW API token is not configured.');

            return [
                'success' => false,
                'message' => 'GFW API token is not configured',
                'status' => 401,
            ];
        }

        try {
            $response = Http::baseUrl($url)
                ->withToken($token)
                ->timeout(30)
                ->connectTimeout(5)
                ->acceptJson()
                ->get('/v3/events', [
                    'datasets' => ['public-global-fishing-events:latest'],
                    'limit' => 1,
                    'offset' => 0,
                ]);

            $status = $response->status();

            if ($response->successful()) {
                return [
                    'success' => true,
                    'message' => 'GFW API connection successful',
                    'status' => 200,
                ];
            }

            if ($status === 401) {
                $this->logWarning('/v3/events', 401, 'Authentication failed on upstream GFW.');

                return [
                    'success' => false,
                    'message' => 'GFW API authentication failed',
                    'status' => 401,
                ];
            }

            if ($status === 403) {
                $this->logWarning('/v3/events', 403, 'Access forbidden on upstream GFW.');

                return [
                    'success' => false,
                    'message' => 'GFW API access forbidden',
                    'status' => 403,
                ];
            }

            if ($status === 429) {
                $this->logWarning('/v3/events', 429, 'Rate limit reached on upstream GFW.');

                return [
                    'success' => false,
                    'message' => 'GFW API rate limit reached',
                    'status' => 429,
                ];
            }

            $this->logWarning('/v3/events', $status, "Upstream GFW returned status {$status}");

            return [
                'success' => false,
                'message' => 'GFW API server error',
                'status' => 502,
            ];
        } catch (ConnectionException $e) {
            $this->logWarning('/v3/events', null, 'Connection or timeout exception reaching GFW API: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Unable to connect to GFW API',
                'status' => 503,
            ];
        } catch (Throwable $e) {
            $this->logWarning('/v3/events', null, 'Unexpected exception in GFWService: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'GFW API connection error',
                'status' => 500,
            ];
        }
    }

    /**
     * Query fishing events from GFW API v3 filtered by a spatial geometry AOI.
     *
     * @param  array<string, mixed>  $geometry  GeoJSON geometry (Polygon/MultiPolygon/Feature/FeatureCollection)
     * @param  array<string, mixed>  $options
     * @return array{
     *     success: bool,
     *     source?: string,
     *     aoi?: string,
     *     start_date?: string,
     *     end_date?: string,
     *     event_count?: int,
     *     returned_count?: int,
     *     pagination?: array{limit: int, offset: int, total: int, next_offset?: int|null},
     *     events?: list<array<string, mixed>>,
     *     message?: string,
     *     status?: int
     * }
     */
    public function getEvents(array $geometry, string $startDate, string $endDate, array $options = []): array
    {
        $token = $this->getToken();
        $url = $this->getUrl();

        if (empty($token)) {
            $this->logWarning('/v3/events', null, 'GFW API token is not configured.');

            return [
                'success' => false,
                'message' => 'GFW API token is not configured',
                'status' => 401,
            ];
        }

        $cleanGeometry = $this->extractGeometryObject($geometry);
        if (empty($cleanGeometry)) {
            return [
                'success' => false,
                'message' => 'Invalid geometry provided for GFW Events query',
                'status' => 422,
            ];
        }

        $dataset = $options['dataset'] ?? config('gfw.fishing_events_dataset', 'public-global-fishing-events:latest');
        $aoiName = $options['aoi_name'] ?? 'ZEE Indonesia - Kawasan Aceh';
        $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 50;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;

        try {
            $response = Http::baseUrl($url)
                ->withToken($token)
                ->timeout(30)
                ->connectTimeout(5)
                ->acceptJson()
                ->asJson()
                ->withQueryParameters([
                    'limit' => $limit,
                    'offset' => $offset,
                ])
                ->post('/v3/events', [
                    'datasets' => [$dataset],
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'geometry' => $cleanGeometry,
                ]);

            $status = $response->status();

            if ($response->successful()) {
                $payload = $response->json() ?? [];
                $rawEntries = $payload['entries'] ?? [];
                $total = (int) ($payload['total'] ?? count($rawEntries));

                // Deduplicate events by unique event.id while preserving upstream ordering
                $normalizedEvents = [];
                $seenIds = [];

                foreach ($rawEntries as $entry) {
                    $id = $entry['id'] ?? null;
                    if ($id !== null && isset($seenIds[$id])) {
                        continue;
                    }
                    if ($id !== null) {
                        $seenIds[$id] = true;
                    }
                    $normalizedEvents[] = $this->normalizeEvent($entry, $dataset);
                }

                $returnedCount = count($normalizedEvents);
                $nextOffset = $payload['nextOffset'] ?? ($offset + $returnedCount < $total ? $offset + $returnedCount : null);

                return [
                    'success' => true,
                    'source' => 'Global Fishing Watch',
                    'aoi' => $aoiName,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'event_count' => $total,
                    'returned_count' => $returnedCount,
                    'pagination' => [
                        'limit' => $limit,
                        'offset' => $offset,
                        'total' => $total,
                        'next_offset' => $nextOffset,
                    ],
                    'events' => $normalizedEvents,
                    'status' => 200,
                ];
            }

            if ($status === 400) {
                $this->logWarning('/v3/events', 400, 'Bad request to GFW Events API.');

                return [
                    'success' => false,
                    'message' => 'Invalid request sent to GFW API',
                    'status' => 400,
                ];
            }

            if ($status === 401) {
                $this->logWarning('/v3/events', 401, 'Authentication failed on GFW Events API.');

                return [
                    'success' => false,
                    'message' => 'GFW API authentication failed',
                    'status' => 401,
                ];
            }

            if ($status === 403) {
                $this->logWarning('/v3/events', 403, 'Access forbidden on GFW Events API.');

                return [
                    'success' => false,
                    'message' => 'GFW API access forbidden',
                    'status' => 403,
                ];
            }

            if ($status === 422) {
                $this->logWarning('/v3/events', 422, 'Unprocessable entity in GFW Events API request.');

                return [
                    'success' => false,
                    'message' => 'Unprocessable entity in GFW request',
                    'status' => 422,
                ];
            }

            if ($status === 429) {
                $this->logWarning('/v3/events', 429, 'Rate limit reached on GFW Events API.');

                return [
                    'success' => false,
                    'message' => 'GFW API rate limit reached',
                    'status' => 429,
                ];
            }

            $this->logWarning('/v3/events', $status, "Upstream GFW returned HTTP {$status}");

            return [
                'success' => false,
                'message' => 'GFW API server error',
                'status' => 502,
            ];
        } catch (ConnectionException $e) {
            $this->logWarning('/v3/events', null, 'Connection or timeout exception reaching GFW Events API: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Unable to connect to GFW API',
                'status' => 503,
            ];
        } catch (Throwable $e) {
            $this->logWarning('/v3/events', null, 'Unexpected exception in GFWService getEvents: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'GFW API connection error',
                'status' => 500,
            ];
        }
    }

    /**
     * Extract clean Geometry object from FeatureCollection, Feature, or direct Geometry.
     *
     * @param  array<string, mixed>  $geometry
     * @return array<string, mixed>|null
     */
    protected function extractGeometryObject(array $geometry): ?array
    {
        $type = $geometry['type'] ?? null;

        if ($type === 'FeatureCollection' && ! empty($geometry['features'])) {
            return $geometry['features'][0]['geometry'] ?? null;
        }

        if ($type === 'Feature') {
            return $geometry['geometry'] ?? null;
        }

        if (in_array($type, ['Polygon', 'MultiPolygon'])) {
            return $geometry;
        }

        return null;
    }

    /**
     * Normalize raw GFW event into clean standard format without inventing fields.
     *
     * @param  array<string, mixed>  $entry
     * @return array<string, mixed>
     */
    protected function normalizeEvent(array $entry, string $dataset): array
    {
        $lat = isset($entry['position']['lat']) && is_numeric($entry['position']['lat']) ? (float) $entry['position']['lat'] : null;
        $lon = isset($entry['position']['lon']) && is_numeric($entry['position']['lon']) ? (float) $entry['position']['lon'] : null;

        // Coordinate integrity check [-90, 90] for lat, [-180, 180] for lon
        if ($lat !== null && ($lat < -90.0 || $lat > 90.0)) {
            $lat = null;
        }
        if ($lon !== null && ($lon < -180.0 || $lon > 180.0)) {
            $lon = null;
        }

        return [
            'id' => $entry['id'] ?? null,
            'type' => $entry['type'] ?? 'fishing',
            'start' => $entry['start'] ?? null,
            'end' => $entry['end'] ?? null,
            'position' => [
                'lat' => $lat,
                'lon' => $lon,
            ],
            'boundingBox' => $entry['boundingBox'] ?? null,
            'distances' => $entry['distances'] ?? null,
            'vessel' => isset($entry['vessel']) ? [
                'id' => $entry['vessel']['id'] ?? null,
                'name' => $entry['vessel']['name'] ?? null,
                'ssvid' => $entry['vessel']['ssvid'] ?? null,
                'flag' => $entry['vessel']['flag'] ?? null,
                'type' => $entry['vessel']['type'] ?? null,
            ] : null,
            'regions' => $entry['regions'] ?? null,
            'dataset' => $dataset,
        ];
    }

    /**
     * Log failure safely without exposing secret credentials or authorization headers.
     */
    protected function logWarning(string $endpoint, ?int $status, string $message): void
    {
        Log::warning('GFW API communication warning', [
            'endpoint' => $endpoint,
            'status' => $status,
            'error' => $message,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
