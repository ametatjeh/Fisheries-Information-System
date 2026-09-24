<?php

namespace App\Services;

use App\Models\Gfw\GfwVessel;
use App\Services\Gfw\GfwActivityService;
use App\Services\Gis\BigMaritimeBoundaryService;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class GFWService
{
    public const MAX_UPSTREAM_EVENTS = 500;

    public const MAX_UPSTREAM_PAGES = 5;

    public const UPSTREAM_PAGE_SIZE = 100;

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

        $cacheKey = 'gfw:events:'.md5(json_encode([$dataset, $startDate, $endDate, $limit, $offset]));
        if (! ($options['refresh'] ?? false) && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

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

                // Filter events strictly inside BIG ZEE Aceh and deduplicate by unique event.id
                $normalizedEvents = [];
                $seenIds = [];
                /** @var BigMaritimeBoundaryService $bigService */
                $bigService = app(BigMaritimeBoundaryService::class);

                foreach ($rawEntries as $entry) {
                    $lat = isset($entry['position']['lat']) && is_numeric($entry['position']['lat']) ? (float) $entry['position']['lat'] : null;
                    $lon = isset($entry['position']['lon']) && is_numeric($entry['position']['lon']) ? (float) $entry['position']['lon'] : null;

                    if ($lat !== null && ($lat < -90.0 || $lat > 90.0)) {
                        $lat = null;
                    }
                    if ($lon !== null && ($lon < -180.0 || $lon > 180.0)) {
                        $lon = null;
                    }

                    if ($lat !== null && $lon !== null) {
                        if (! $bigService->isPointInGeometry($lon, $lat, $cleanGeometry)) {
                            continue; // Exclude events located outside BIG ZEE Aceh
                        }
                    }

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

                $result = [
                    'success' => true,
                    'source' => 'Global Fishing Watch',
                    'aoi' => $aoiName,
                    'aoi_metadata' => [
                        'id' => 'zee-indonesia-aceh',
                        'name' => 'ZEE Indonesia - Kawasan Aceh',
                        'source' => 'BIG',
                        'boundary_source' => 'BIG',
                        'boundary_layer' => 10,
                        'crs' => 'EPSG:4326',
                    ],
                    'description' => 'Observasi kapal Global Fishing Watch yang berada di dalam batas ZEE Aceh berdasarkan BIG',
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

                Cache::put($cacheKey, $result, 300);

                return $result;
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
     * Query vessels in AOI geometry from GFW API.
     *
     * @param  array<string, mixed>  $geometry  GeoJSON geometry
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getVesselsInAoi(array $geometry, string $startDate, string $endDate, array $options = []): array
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
                'message' => 'Invalid geometry provided for GFW Vessels query',
                'status' => 422,
            ];
        }

        $limit = isset($options['limit']) ? max(1, min(100, (int) $options['limit'])) : 50;
        $offset = isset($options['offset']) ? max(0, (int) $options['offset']) : 0;
        $vesselTypeFilter = ! empty($options['vessel_type']) && strtolower($options['vessel_type']) !== 'all' ? trim((string) $options['vessel_type']) : null;
        $flagFilter = ! empty($options['flag']) && strtolower($options['flag']) !== 'all' ? strtoupper(trim((string) $options['flag'])) : null;
        $activityFilter = ! empty($options['activity']) && strtolower($options['activity']) !== 'all' ? trim((string) $options['activity']) : null;
        $searchQuery = ! empty($options['search']) ? trim((string) $options['search']) : null;

        $datasets = $options['datasets'] ?? [
            $options['dataset'] ?? config('gfw.fishing_events_dataset', 'public-global-fishing-events:latest'),
        ];

        $cacheKey = 'gfw:vessels_in_aoi:'.md5(json_encode([$startDate, $endDate, $limit, $offset, $vesselTypeFilter, $flagFilter, $activityFilter, $searchQuery]));
        if (! ($options['refresh'] ?? false) && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $maxPages = self::MAX_UPSTREAM_PAGES;
        $maxUpstreamEvents = self::MAX_UPSTREAM_EVENTS;
        $upstreamLimit = self::UPSTREAM_PAGE_SIZE;
        $currentOffset = 0;
        $page = 1;
        $allEntries = [];
        $paginationComplete = true;
        $paginationTruncated = false;

        try {
            while (true) {
                try {
                    $response = Http::baseUrl($url)
                        ->withToken($token)
                        ->timeout(30)
                        ->connectTimeout(5)
                        ->acceptJson()
                        ->asJson()
                        ->withQueryParameters([
                            'limit' => $upstreamLimit,
                            'offset' => $currentOffset,
                        ])
                        ->post('/v3/events', [
                            'datasets' => $datasets,
                            'startDate' => $startDate,
                            'endDate' => $endDate,
                            'geometry' => $cleanGeometry,
                        ]);
                } catch (ConnectionException $e) {
                    if ($page === 1) {
                        throw $e;
                    }

                    $this->logWarning('/v3/events', null, "Connection or timeout on pagination page {$page}: ".$e->getMessage());
                    $paginationComplete = false;
                    break;
                } catch (Throwable $e) {
                    if ($page === 1) {
                        throw $e;
                    }

                    $this->logWarning('/v3/events', null, "Unexpected error on pagination page {$page}: ".$e->getMessage());
                    $paginationComplete = false;
                    break;
                }

                $status = $response->status();

                if (! $response->successful()) {
                    if ($page === 1) {
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
                    }

                    // If page > 1 fails, gracefully preserve entries from previous pages
                    $this->logWarning('/v3/events', $status, "Upstream GFW returned HTTP {$status} on pagination page {$page}. Processing previously collected pages.");
                    $paginationComplete = false;
                    break;
                }

                $payload = $response->json() ?? [];
                $rawEntries = $payload['entries'] ?? [];
                $entriesCount = count($rawEntries);

                Log::info('GFW upstream pagination progress', [
                    'boundary_source' => 'BIG',
                    'boundary_layer' => 10,
                    'dataset' => $datasets[0] ?? 'public-global-fishing-events:latest',
                    'pagination_page' => $page,
                    'offset' => $currentOffset,
                    'events_received' => $entriesCount,
                ]);

                foreach ($rawEntries as $entry) {
                    if (is_array($entry)) {
                        $allEntries[] = $entry;
                    }
                }

                // Check if safety limit reached
                if (count($allEntries) >= $maxUpstreamEvents || $page >= $maxPages) {
                    $hasMoreUpstream = ! empty($payload['nextOffset']) || (isset($payload['total']) && ($currentOffset + $entriesCount) < (int) $payload['total']);
                    if ($hasMoreUpstream) {
                        $paginationComplete = false;
                        $paginationTruncated = true;
                        Log::info('GFW upstream pagination reached safety limit', [
                            'boundary_source' => 'BIG',
                            'boundary_layer' => 10,
                            'max_events' => $maxUpstreamEvents,
                            'max_pages' => $maxPages,
                            'events_received' => count($allEntries),
                            'pagination_complete' => false,
                            'pagination_truncated' => true,
                        ]);
                    }
                    break;
                }

                // Determine nextOffset
                $nextOffset = $payload['nextOffset'] ?? null;
                if ($nextOffset === null || $nextOffset === '') {
                    break;
                }

                if (! is_numeric($nextOffset) || (int) $nextOffset <= $currentOffset) {
                    Log::warning('GFW upstream pagination stopped: invalid or non-advancing nextOffset', [
                        'current_offset' => $currentOffset,
                        'next_offset' => $nextOffset,
                        'pagination_stopped' => true,
                        'reason' => 'invalid_next_offset',
                    ]);
                    break;
                }

                $currentOffset = (int) $nextOffset;
                $page++;
            }

            Log::info('GFW upstream pagination completed', [
                'boundary_source' => 'BIG',
                'boundary_layer' => 10,
                'total_pages' => $page,
                'total_events' => count($allEntries),
                'pagination_complete' => $paginationComplete,
                'pagination_truncated' => $paginationTruncated,
            ]);

            $rawEntries = $allEntries;

            // Group entries into distinct vessels
            /** @var array<string, array<string, mixed>> $vesselsById */
            $vesselsById = [];
            $vesselKeyByMmsi = [];
            $vesselKeyByImo = [];

            /** @var BigMaritimeBoundaryService $bigService */
            $bigService = app(BigMaritimeBoundaryService::class);

            foreach ($rawEntries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $lat = isset($entry['position']['lat']) && is_numeric($entry['position']['lat']) ? (float) $entry['position']['lat'] : null;
                $lon = isset($entry['position']['lon']) && is_numeric($entry['position']['lon']) ? (float) $entry['position']['lon'] : null;

                if ($lat !== null && ($lat < -90.0 || $lat > 90.0)) {
                    $lat = null;
                }
                if ($lon !== null && ($lon < -180.0 || $lon > 180.0)) {
                    $lon = null;
                }

                // Strict point-in-polygon verification:
                // Any observation with spatial coordinates MUST fall strictly inside the official BIG ZEE Aceh polygon.
                if ($lat !== null && $lon !== null) {
                    if (! $bigService->isPointInGeometry($lon, $lat, $cleanGeometry)) {
                        continue; // Strictly excluded outside BIG ZEE
                    }
                }

                $vesselRaw = $entry['vessel'] ?? [];
                $vId = ! empty($vesselRaw['id']) ? trim((string) $vesselRaw['id']) : null;
                $mmsi = ! empty($vesselRaw['ssvid']) ? trim((string) $vesselRaw['ssvid']) : (! empty($vesselRaw['mmsi']) ? trim((string) $vesselRaw['mmsi']) : null);
                $imo = ! empty($vesselRaw['imo']) ? trim((string) $vesselRaw['imo']) : null;

                // Resolve canonical vessel key: prioritize GFW vessel id, then seen MMSI, then seen IMO
                $vKey = null;
                if ($vId !== null && isset($vesselsById[$vId])) {
                    $vKey = $vId;
                } elseif ($mmsi !== null && isset($vesselKeyByMmsi[$mmsi])) {
                    $vKey = $vesselKeyByMmsi[$mmsi];
                } elseif ($imo !== null && isset($vesselKeyByImo[$imo])) {
                    $vKey = $vesselKeyByImo[$imo];
                } else {
                    $vKey = $vId ?? ($mmsi ?? ($imo ?? ($entry['id'] ?? null)));
                }

                if (empty($vKey)) {
                    continue;
                }

                $vKey = (string) $vKey;
                if ($mmsi !== null) {
                    $vesselKeyByMmsi[$mmsi] = $vKey;
                }
                if ($imo !== null) {
                    $vesselKeyByImo[$imo] = $vKey;
                }

                $eventType = strtolower(trim((string) ($entry['type'] ?? 'fishing')));
                $activityName = match ($eventType) {
                    'fishing' => 'Fishing Activity',
                    'encounter' => 'Encounter',
                    'loitering' => 'Loitering',
                    'port_visit' => 'Port Visit',
                    default => 'Vessel Presence',
                };

                $startTime = $entry['start'] ?? null;
                $endTime = $entry['end'] ?? null;
                $obsTime = $endTime ?? $startTime;

                if (! isset($vesselsById[$vKey])) {
                    $rawType = $vesselRaw['type'] ?? ($entry['vessel_type'] ?? null);
                    $vesselsById[$vKey] = [
                        'id' => $vKey,
                        'name' => ! empty($vesselRaw['name']) ? trim((string) $vesselRaw['name']) : null,
                        'mmsi' => ! empty($vesselRaw['ssvid']) ? trim((string) $vesselRaw['ssvid']) : (! empty($vesselRaw['mmsi']) ? trim((string) $vesselRaw['mmsi']) : null),
                        'ssvid' => ! empty($vesselRaw['ssvid']) ? trim((string) $vesselRaw['ssvid']) : null,
                        'imo' => ! empty($vesselRaw['imo']) ? trim((string) $vesselRaw['imo']) : null,
                        'flag' => ! empty($vesselRaw['flag']) ? strtoupper(trim((string) $vesselRaw['flag'])) : null,
                        'vessel_type' => self::normalizeVesselType($rawType),
                        'length' => isset($vesselRaw['length']) && is_numeric($vesselRaw['length']) ? round((float) $vesselRaw['length'], 2) : (isset($vesselRaw['lengthM']) && is_numeric($vesselRaw['lengthM']) ? round((float) $vesselRaw['lengthM'], 2) : null),
                        'tonnage' => isset($vesselRaw['tonnage']) && is_numeric($vesselRaw['tonnage']) ? round((float) $vesselRaw['tonnage'], 2) : (isset($vesselRaw['tonnageGt']) && is_numeric($vesselRaw['tonnageGt']) ? round((float) $vesselRaw['tonnageGt'], 2) : null),
                        'engine_power' => isset($vesselRaw['engine_power']) && is_numeric($vesselRaw['engine_power']) ? round((float) $vesselRaw['engine_power'], 2) : (isset($vesselRaw['enginePower']) && is_numeric($vesselRaw['enginePower']) ? round((float) $vesselRaw['enginePower'], 2) : null),
                        'gear' => ! empty($vesselRaw['gear']) ? trim((string) $vesselRaw['gear']) : (! empty($vesselRaw['gear_type']) ? trim((string) $vesselRaw['gear_type']) : null),
                        'first_seen' => $startTime,
                        'last_seen' => $obsTime,
                        'activity' => $activityName,
                        'position' => [
                            'lat' => $lat,
                            'lon' => $lon,
                        ],
                        'lat' => $lat,
                        'lon' => $lon,
                    ];
                } else {
                    // Update latest observation and positions if newer
                    if ($obsTime && (! $vesselsById[$vKey]['last_seen'] || $obsTime >= $vesselsById[$vKey]['last_seen'])) {
                        $vesselsById[$vKey]['position'] = ['lat' => $lat, 'lon' => $lon];
                        $vesselsById[$vKey]['lat'] = $lat;
                        $vesselsById[$vKey]['lon'] = $lon;
                        $vesselsById[$vKey]['last_seen'] = $obsTime;
                    }
                    if ($startTime && (! $vesselsById[$vKey]['first_seen'] || $startTime < $vesselsById[$vKey]['first_seen'])) {
                        $vesselsById[$vKey]['first_seen'] = $startTime;
                    }
                    if ($activityName === 'Fishing Activity') {
                        $vesselsById[$vKey]['activity'] = 'Fishing Activity';
                    }
                }
            }

            // Enrich missing vessel details from local database if available (single batch query, zero N+1)
            try {
                if (! empty($vesselsById) && class_exists(GfwVessel::class)) {
                    $keys = array_keys($vesselsById);
                    $mmsis = array_filter(array_column($vesselsById, 'mmsi'));
                    $localRecords = GfwVessel::whereIn('gfw_vessel_id', $keys)
                        ->orWhereIn('mmsi', $mmsis)
                        ->get();

                    foreach ($localRecords as $lr) {
                        foreach ($vesselsById as $k => &$vRef) {
                            if ($k === $lr->gfw_vessel_id || (! empty($vRef['mmsi']) && $vRef['mmsi'] === $lr->mmsi)) {
                                if ($vRef['length'] === null && $lr->length_m !== null) {
                                    $vRef['length'] = (float) $lr->length_m;
                                }
                                if ($vRef['tonnage'] === null && $lr->tonnage_gt !== null) {
                                    $vRef['tonnage'] = (float) $lr->tonnage_gt;
                                }
                                if ($vRef['gear'] === null && $lr->gear_type !== null) {
                                    $vRef['gear'] = $lr->gear_type;
                                }
                                if ($vRef['imo'] === null && $lr->imo !== null) {
                                    $vRef['imo'] = $lr->imo;
                                }
                            }
                        }
                        unset($vRef);
                    }
                }
            } catch (Throwable) {
                // Graceful fallback if database unavailable
            }

            $latestSeenTimestamp = null;
            $minDataAgeSeconds = null;
            $nowUtc = Carbon::now('UTC');

            foreach ($vesselsById as &$vItem) {
                $lastSeen = $vItem['last_seen'] ?? null;
                $ageSeconds = null;
                if ($lastSeen !== null) {
                    try {
                        $parsedLast = Carbon::parse($lastSeen, 'UTC');
                        $ageSeconds = max(0, (int) $nowUtc->diffInSeconds($parsedLast));
                        if ($latestSeenTimestamp === null || $parsedLast->gt(Carbon::parse($latestSeenTimestamp, 'UTC'))) {
                            $latestSeenTimestamp = $lastSeen;
                        }
                        if ($minDataAgeSeconds === null || $ageSeconds < $minDataAgeSeconds) {
                            $minDataAgeSeconds = $ageSeconds;
                        }
                    } catch (Throwable) {
                        $ageSeconds = null;
                    }
                }

                $vItem['data_age_seconds'] = $ageSeconds;
                $vItem['status'] = self::classifyStatus($ageSeconds);
            }
            unset($vItem);

            // Collect distinct flags and vessel types across ALL deduplicated vessels (before user filtering)
            $availableFlags = [];
            $availableVesselTypes = [];
            foreach ($vesselsById as $vb) {
                if (! empty($vb['flag'])) {
                    $fl = strtoupper(trim((string) $vb['flag']));
                    $availableFlags[$fl] = ($availableFlags[$fl] ?? 0) + 1;
                }
                if (! empty($vb['vessel_type'])) {
                    $vt = trim((string) $vb['vessel_type']);
                    $availableVesselTypes[$vt] = ($availableVesselTypes[$vt] ?? 0) + 1;
                }
            }
            ksort($availableFlags);
            ksort($availableVesselTypes);

            // Apply filters
            $filteredVessels = array_values(array_filter($vesselsById, function (array $v) use ($vesselTypeFilter, $flagFilter, $activityFilter, $searchQuery) {
                if ($vesselTypeFilter !== null && strcasecmp($v['vessel_type'], $vesselTypeFilter) !== 0) {
                    return false;
                }
                if ($flagFilter !== null && strcasecmp((string) $v['flag'], $flagFilter) !== 0) {
                    return false;
                }
                if ($activityFilter !== null) {
                    $actLower = strtolower($v['activity']);
                    $filterLower = strtolower($activityFilter);
                    if (! str_contains($actLower, $filterLower)) {
                        return false;
                    }
                }
                if ($searchQuery !== null) {
                    $qLower = strtolower($searchQuery);
                    $nameMatch = ! empty($v['name']) && str_contains(strtolower($v['name']), $qLower);
                    $mmsiMatch = ! empty($v['mmsi']) && str_contains(strtolower((string) $v['mmsi']), $qLower);
                    $ssvidMatch = ! empty($v['ssvid']) && str_contains(strtolower((string) $v['ssvid']), $qLower);
                    $imoMatch = ! empty($v['imo']) && str_contains(strtolower((string) $v['imo']), $qLower);
                    $idMatch = ! empty($v['id']) && str_contains(strtolower((string) $v['id']), $qLower);
                    if (! $nameMatch && ! $mmsiMatch && ! $ssvidMatch && ! $imoMatch && ! $idMatch) {
                        return false;
                    }
                }

                return true;
            }));

            $totalVessels = count($filteredVessels);
            $fishingVessels = count(array_filter($filteredVessels, fn ($v) => strcasecmp($v['vessel_type'] ?? '', 'Fishing') === 0));
            $otherVessels = $totalVessels - $fishingVessels;
            $liveVessels = count(array_filter($filteredVessels, fn ($v) => ($v['status'] ?? '') === 'LIVE'));
            $recentVessels = count(array_filter($filteredVessels, fn ($v) => ($v['status'] ?? '') === 'RECENT'));
            $staleVessels = count(array_filter($filteredVessels, fn ($v) => ($v['status'] ?? '') === 'STALE'));

            // Group flags and vessel types
            $flagsSummary = [];
            $vesselTypesSummary = [];
            foreach ($filteredVessels as $fv) {
                $fl = $fv['flag'] ?? 'UNKNOWN';
                $flagsSummary[$fl] = ($flagsSummary[$fl] ?? 0) + 1;
                $vt = $fv['vessel_type'] ?? 'Unknown';
                $vesselTypesSummary[$vt] = ($vesselTypesSummary[$vt] ?? 0) + 1;
            }
            arsort($flagsSummary);
            arsort($vesselTypesSummary);

            // Apply pagination
            $pagedVessels = array_values(array_slice($filteredVessels, $offset, $limit));
            $hasMore = ($offset + $limit) < $totalVessels;

            return [
                'success' => true,
                'live' => true,
                'message' => $totalVessels > 0 ? null : 'No vessel detected in BIG ZEE Aceh for selected period.',
                'last_updated' => $latestSeenTimestamp ?? $nowUtc->toIso8601String(),
                'data_age_seconds' => $minDataAgeSeconds ?? 0,
                'aoi' => [
                    'id' => 'zee-indonesia-aceh',
                    'name' => 'ZEE Indonesia - Kawasan Aceh',
                    'source' => 'BIG',
                    'crs' => 'EPSG:4326',
                ],
                'description' => 'Observasi kapal Global Fishing Watch yang berada di dalam batas ZEE Aceh berdasarkan BIG',
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'timezone' => 'UTC',
                'timezone_display' => 'WIB (UTC+7)',
                'summary' => [
                    'total_vessels' => $totalVessels,
                    'fishing_vessels' => $fishingVessels,
                    'other_vessels' => $otherVessels,
                    'live_vessels' => $liveVessels,
                    'recent_vessels' => $recentVessels,
                    'stale_vessels' => $staleVessels,
                    'flags' => $flagsSummary,
                    'vessel_types' => $vesselTypesSummary,
                    'available_flags' => array_keys($availableFlags),
                    'available_vessel_types' => array_keys($availableVesselTypes),
                    'upstream_events_count' => count($allEntries),
                ],
                'vessels' => $pagedVessels,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $hasMore,
                    'pagination_complete' => $paginationComplete,
                    'pagination_truncated' => $paginationTruncated,
                    'upstream_events_count' => count($allEntries),
                ],
                'pagination_complete' => $paginationComplete,
                'pagination_truncated' => $paginationTruncated,
                'status' => 200,
            ];

            Cache::put($cacheKey, $result, 300);

            return $result;
        } catch (ConnectionException $e) {
            $this->logWarning('/v3/events', null, 'Connection or timeout exception reaching GFW Events API: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Unable to connect to GFW API',
                'status' => 503,
            ];
        } catch (Throwable $e) {
            $this->logWarning('/v3/events', null, 'Unexpected exception in GFWService getVesselsInAoi: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'GFW API connection error',
                'status' => 500,
            ];
        }
    }

    /**
     * Normalize raw vessel type to official GFW taxonomy.
     */
    public static function normalizeVesselType(?string $rawType): string
    {
        if (empty($rawType)) {
            return 'Unknown';
        }

        $lower = strtolower(trim($rawType));

        if (str_contains($lower, 'fish') || str_contains($lower, 'trawl') || str_contains($lower, 'longlin') || str_contains($lower, 'seiner') || str_contains($lower, 'squid') || str_contains($lower, 'pole') || str_contains($lower, 'dredge')) {
            return 'Fishing';
        }

        if (str_contains($lower, 'carrier') || str_contains($lower, 'reefer')) {
            return 'Carrier';
        }

        if (str_contains($lower, 'bunker')) {
            return 'Bunker';
        }

        if (str_contains($lower, 'tanker') || str_contains($lower, 'lng') || str_contains($lower, 'lpg') || str_contains($lower, 'oil') || str_contains($lower, 'chemical')) {
            return 'Tanker';
        }

        if (str_contains($lower, 'cargo') || str_contains($lower, 'container') || str_contains($lower, 'bulk') || str_contains($lower, 'freighter')) {
            return 'Cargo';
        }

        if (str_contains($lower, 'passenger') || str_contains($lower, 'ferry') || str_contains($lower, 'cruise')) {
            return 'Passenger';
        }

        if (str_contains($lower, 'recreation') || str_contains($lower, 'yacht') || str_contains($lower, 'pleasure') || str_contains($lower, 'sailing')) {
            return 'Recreational';
        }

        if (str_contains($lower, 'support') || str_contains($lower, 'supply') || str_contains($lower, 'tug') || str_contains($lower, 'pilot') || str_contains($lower, 'dredger') || str_contains($lower, 'towing')) {
            return 'Support';
        }

        if ($lower === 'other' || str_contains($lower, 'special') || str_contains($lower, 'military') || str_contains($lower, 'research') || str_contains($lower, 'law_enforcement')) {
            return 'Other';
        }

        if ($lower === 'unknown') {
            return 'Unknown';
        }

        return ucfirst($lower);
    }

    /**
     * Classify vessel observation recency status based on elapsed seconds.
     * Boundary rules:
     * - LIVE: <= 24 hours (86,400s)
     * - RECENT: <= 72 hours (259,200s)
     * - STALE: > 72 hours (> 259,200s) or missing
     */
    public static function classifyStatus(?int $ageSeconds): string
    {
        if ($ageSeconds === null || $ageSeconds < 0) {
            return 'STALE';
        }

        if ($ageSeconds <= 86400) {
            return 'LIVE';
        }

        if ($ageSeconds <= 259200) {
            return 'RECENT';
        }

        return 'STALE';
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

    /**
     * Retrieve chronological movement track for a specific GFW vessel.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getVesselTrack(string $vesselId, string $startDate, string $endDate, array $options = []): array
    {
        $cleanId = trim($vesselId);
        if ($cleanId === '') {
            return [
                'success' => false,
                'message' => 'Vessel ID tidak boleh kosong.',
                'status' => 422,
            ];
        }

        try {
            /** @var GfwActivityService $activityService */
            $activityService = app(GfwActivityService::class);
            $result = $activityService->getVesselActivity($cleanId, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'refresh' => $options['refresh'] ?? false,
            ]);

            if (! ($result['success'] ?? false)) {
                return [
                    'success' => false,
                    'message' => $result['error'] ?? 'Gagal mengambil data track kapal dari GFW.',
                    'status' => 502,
                ];
            }

            $rawPoints = $result['data'] ?? [];

            // Sort chronologically (oldest to newest)
            usort($rawPoints, function ($a, $b) {
                $tA = strtotime($a['timestamp'] ?? $a['observed_at'] ?? $a['date'] ?? '1970-01-01');
                $tB = strtotime($b['timestamp'] ?? $b['observed_at'] ?? $b['date'] ?? '1970-01-01');

                return $tA <=> $tB;
            });

            $scope = $options['scope'] ?? 'zee_aceh';
            $isZeeAcehScope = $scope !== 'global';
            /** @var BigMaritimeBoundaryService $bigService */
            $bigService = app(BigMaritimeBoundaryService::class);
            $bigGeomResult = $isZeeAcehScope ? $bigService->getAcehZeeGeometry() : null;
            $bigGeometry = ($bigGeomResult['success'] ?? false) ? ($bigGeomResult['geometry'] ?? null) : null;

            $validPoints = [];
            $seenPointKeys = [];

            foreach ($rawPoints as $pt) {
                $lat = isset($pt['latitude']) ? (float) $pt['latitude'] : (isset($pt['lat']) ? (float) $pt['lat'] : null);
                $lon = isset($pt['longitude']) ? (float) $pt['longitude'] : (isset($pt['lon']) ? (float) $pt['lon'] : null);
                $time = $pt['timestamp'] ?? $pt['observed_at'] ?? $pt['date'] ?? null;

                // 1. Validate coordinates (Section 11)
                if ($lat === null || $lon === null || ! is_numeric($lat) || ! is_numeric($lon) || is_nan($lat) || is_nan($lon) || is_infinite($lat) || is_infinite($lon)) {
                    continue;
                }
                if ($lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
                    continue;
                }

                // 2. Spatial check against BIG ZEE Layer 10 (Section 9)
                if ($isZeeAcehScope && (! $bigGeometry || ! $bigService->isPointInGeometry($lon, $lat, $bigGeometry))) {
                    continue;
                }

                // 3. Deduplicate track points (Section 12)
                $ptKey = sprintf('%s:%.5f:%.5f', $time ?? '', $lat, $lon);
                if (isset($seenPointKeys[$ptKey])) {
                    continue;
                }
                $seenPointKeys[$ptKey] = true;

                $timeUnix = $time ? strtotime($time) : null;

                $validPoints[] = [
                    'lat' => $lat,
                    'lon' => $lon,
                    'timestamp' => $time,
                    'timestamp_unix' => $timeUnix,
                    'speed_knots' => $pt['speed_knots'] ?? $pt['speed'] ?? null,
                    'course' => $pt['course'] ?? $pt['heading'] ?? null,
                ];
            }

            $totalValidPoints = count($validPoints);
            $pointFeatures = [];
            $firstDetected = $totalValidPoints > 0 ? $validPoints[0]['timestamp'] : null;
            $lastDetected = $totalValidPoints > 0 ? $validPoints[$totalValidPoints - 1]['timestamp'] : null;

            // Build point features with START and LAST identification (Section 18)
            for ($i = 0; $i < $totalValidPoints; $i++) {
                $p = $validPoints[$i];
                $isStart = ($i === 0);
                $isLast = ($i === $totalValidPoints - 1);

                $markerType = 'waypoint';
                if ($totalValidPoints === 1) {
                    $markerType = 'single';
                } elseif ($isStart) {
                    $markerType = 'start';
                } elseif ($isLast) {
                    $markerType = 'last';
                }

                $pointFeatures[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$p['lon'], $p['lat']],
                    ],
                    'properties' => [
                        'vessel_id' => $cleanId,
                        'point_index' => $i + 1,
                        'total_points' => $totalValidPoints,
                        'timestamp' => $p['timestamp'],
                        'speed_knots' => $p['speed_knots'],
                        'course' => $p['course'],
                        'marker_type' => $markerType,
                        'is_start' => $isStart,
                        'is_last' => $isLast,
                    ],
                ];
            }

            // Build LineString / MultiLineString with gap detection to avoid false lines (Section 10)
            $segments = [];
            $currentSegment = [];
            $lastTime = null;

            foreach ($validPoints as $p) {
                $curTime = $p['timestamp_unix'];
                if ($lastTime !== null && $curTime !== null) {
                    $timeDelta = abs($curTime - $lastTime);
                    // Gap greater than 12 hours (43200 seconds) breaks continuous segment
                    if ($timeDelta > 43200) {
                        if (count($currentSegment) >= 2) {
                            $segments[] = $currentSegment;
                        }
                        $currentSegment = [];
                    }
                }
                $currentSegment[] = [$p['lon'], $p['lat']];
                $lastTime = $curTime;
            }
            if (count($currentSegment) >= 2) {
                $segments[] = $currentSegment;
            }

            $lineFeature = null;
            if (count($segments) === 1) {
                $lineFeature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $segments[0],
                    ],
                    'properties' => [
                        'vessel_id' => $cleanId,
                        'points_count' => count($segments[0]),
                        'segments_count' => 1,
                    ],
                ];
            } elseif (count($segments) > 1) {
                $totalCoords = 0;
                foreach ($segments as $seg) {
                    $totalCoords += count($seg);
                }
                $lineFeature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'MultiLineString',
                        'coordinates' => $segments,
                    ],
                    'properties' => [
                        'vessel_id' => $cleanId,
                        'points_count' => $totalCoords,
                        'segments_count' => count($segments),
                    ],
                ];
            }

            $features = [];
            if ($lineFeature) {
                $features[] = $lineFeature;
            }
            $features = array_merge($features, $pointFeatures);

            // Fetch vessel details from database or options (Section 14 & 27)
            $dbVessel = null;
            try {
                $dbVessel = GfwVessel::where('gfw_vessel_id', $cleanId)
                    ->orWhere('mmsi', $cleanId)
                    ->first();
            } catch (Throwable) {
                // Non-blocking fallback
            }

            $vesselInfo = [
                'id' => $cleanId,
                'gfw_vessel_id' => $cleanId,
                'name' => $options['name'] ?? $dbVessel?->name ?? '—',
                'mmsi' => $options['mmsi'] ?? $dbVessel?->mmsi ?? '—',
                'ssvid' => $options['ssvid'] ?? $options['mmsi'] ?? $dbVessel?->mmsi ?? '—',
                'imo' => $options['imo'] ?? $dbVessel?->imo ?? '—',
                'flag' => $options['flag'] ?? $dbVessel?->flag ?? '—',
                'vessel_type' => $options['vessel_type'] ?? $dbVessel?->vessel_type ?? '—',
                'first_detected' => $firstDetected,
                'last_detected' => $lastDetected,
            ];

            $sufficient = $totalValidPoints >= 2;

            return [
                'success' => true,
                'vessel' => $vesselInfo,
                'vessel_id' => $cleanId,
                'track' => [
                    'type' => 'FeatureCollection',
                    'features' => $features,
                ],
                'metadata' => [
                    'count' => $totalValidPoints,
                    'date_from' => $startDate,
                    'date_to' => $endDate,
                    'boundary_source' => 'BIG',
                    'boundary_layer' => 10,
                    'boundary_name' => 'Peta Batas ZEE',
                ],
                'boundary_source' => 'BIG',
                'boundary_layer' => 10,
                'boundary_name' => 'Peta Batas ZEE',
                'track_scope' => $isZeeAcehScope ? 'ZEE Aceh Track' : 'Global Vessel Track',
                'track_scope_description' => $isZeeAcehScope ? 'Track di ZEE Aceh berdasarkan batas BIG Layer 10' : 'Global Vessel Track',
                'aoi' => [
                    'id' => 'zee-indonesia-aceh',
                    'name' => 'ZEE Indonesia - Kawasan Aceh',
                    'source' => 'BIG',
                    'layer' => 10,
                    'crs' => 'EPSG:4326',
                ],
                'sufficient' => $sufficient,
                'message' => $sufficient ? 'Data lintasan tersedia.' : ($totalValidPoints === 1 ? 'Hanya 1 posisi tercatat dalam ZEE Aceh.' : 'Tidak ada data track untuk vessel dan periode yang dipilih.'),
                'points_count' => $totalValidPoints,
                'first_detected' => $firstDetected,
                'last_detected' => $lastDetected,
                'approximate_coverage' => $totalValidPoints > 0 ? ($totalValidPoints.' posisi tercatat') : 'Tidak ada data track',
                'track_info' => [
                    'first_detected' => $firstDetected,
                    'last_detected' => $lastDetected,
                    'position_count' => $totalValidPoints,
                    'approximate_coverage' => $totalValidPoints > 0 ? ($totalValidPoints.' posisi tercatat') : 'Tidak ada data track',
                    'note' => $sufficient ? null : 'Track data insufficient',
                ],
                'line_geojson' => $lineFeature ? $lineFeature['geometry'] : null,
                'points_geojson' => [
                    'type' => 'FeatureCollection',
                    'features' => $pointFeatures,
                ],
                'status' => 200,
            ];
        } catch (Throwable $e) {
            $this->logWarning('/v3/vessels/tracks', 500, 'Error processing vessel track: '.$e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal memproses data track kapal: '.$e->getMessage(),
                'status' => 500,
            ];
        }
    }

    /**
     * Consolidate operational dashboard metrics, events, and vessel dataset.
     *
     * @param  array<string, mixed>  $geometry
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function getDashboardData(array $geometry, string $startDate, string $endDate, array $options = []): array
    {
        $cacheKey = 'gfw:dashboard_data:'.md5(json_encode([$startDate, $endDate, $options]));
        if (! ($options['refresh'] ?? false) && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $vesselsResult = $this->getVesselsInAoi($geometry, $startDate, $endDate, $options);
        if (! ($vesselsResult['success'] ?? false)) {
            return $vesselsResult;
        }

        // Query Fishing Events (Stage 4)
        $fishingResult = $this->getEvents($geometry, $startDate, $endDate, [
            'limit' => 100,
            'offset' => 0,
            'dataset' => config('gfw.fishing_events_dataset', 'public-global-fishing-events:latest'),
        ]);
        $rawFishingEvents = ($fishingResult['success'] ?? false) ? ($fishingResult['events'] ?? []) : [];

        // Query Loitering Events (Stage 6)
        $loiteringResult = $this->getEvents($geometry, $startDate, $endDate, [
            'limit' => 100,
            'offset' => 0,
            'dataset' => config('gfw.loitering_dataset', 'public-global-loitering-events:latest'),
        ]);
        $rawLoiteringEvents = ($loiteringResult['success'] ?? false) ? ($loiteringResult['events'] ?? []) : [];

        // Note: Encounters (Stage 7) and Port Visits (Stage 8) dataset aliases returned 404 from upstream GFW API v3
        // To uphold the FINAL RULE (REAL DATA > MOCK DATA), no fake events are invented.
        $rawEvents = array_merge($rawFishingEvents, $rawLoiteringEvents);

        $fishingCount = count(array_filter($rawFishingEvents, fn ($e) => ($e['type'] ?? 'fishing') === 'fishing'));
        $loiteringCount = count($rawLoiteringEvents);
        $encounterCount = count(array_filter($rawFishingEvents, fn ($e) => in_array($e['type'] ?? '', ['encounter', 'encounters'])));
        $portVisitCount = count(array_filter($rawFishingEvents, fn ($e) => in_array($e['type'] ?? '', ['port_visit', 'port_visits'])));

        $activityFeed = [];
        $alerts = [];

        // Process Loitering Events (Stage 6 & Stage 9)
        foreach ($rawLoiteringEvents as $ev) {
            $vesselName = $ev['vessel']['name'] ?? 'Kapal Tidak Dikenal';
            $vesselId = $ev['vessel']['id'] ?? null;
            $mmsi = $ev['vessel']['ssvid'] ?? null;
            $time = $ev['start'] ?? $ev['end'] ?? null;
            $lat = $ev['position']['lat'] ?? null;
            $lon = $ev['position']['lon'] ?? null;

            $activityFeed[] = [
                'id' => $ev['id'] ?? uniqid('ev-loiter-'),
                'time' => $time,
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'type' => 'loitering',
                'activity' => 'Loitering Event',
                'location' => ($lat !== null && $lon !== null) ? round($lat, 4).', '.round($lon, 4) : 'N/A',
                'lat' => $lat,
                'lon' => $lon,
            ];

            $alerts[] = [
                'id' => 'alert-loiter-'.($ev['id'] ?? uniqid()),
                'category' => 'Loitering',
                'severity' => 'WARNING',
                'type' => 'loitering',
                'status' => 'NEW',
                'title' => 'Pola Loitering Terdeteksi',
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'time' => $time,
                'lat' => $lat,
                'lon' => $lon,
                'location' => ($lat !== null && $lon !== null) ? round($lat, 4).', '.round($lon, 4) : 'N/A',
                'source' => 'Global Fishing Watch',
                'boundary_source' => 'BIG',
                'boundary_layer' => 10,
                'reason' => 'Peristiwa perlambatan atau pola menunggu kapal di perairan ZEE Aceh teridentifikasi secara analitik. Indikator pemantauan untuk ditinjau manusia (bukan bukti pelanggaran).',
                'description' => "Terdeteksi pola Loitering oleh kapal {$vesselName} di perairan ZEE Aceh berdasarkan inferensi analitik satelit GFW.",
            ];
        }

        // Process Fishing Events (Stage 4 & Stage 9)
        foreach ($rawFishingEvents as $ev) {
            $vesselName = $ev['vessel']['name'] ?? 'Kapal Tidak Dikenal';
            $vesselId = $ev['vessel']['id'] ?? null;
            $mmsi = $ev['vessel']['ssvid'] ?? null;
            $time = $ev['start'] ?? $ev['end'] ?? null;
            $lat = $ev['position']['lat'] ?? null;
            $lon = $ev['position']['lon'] ?? null;

            $activityFeed[] = [
                'id' => $ev['id'] ?? uniqid('ev-fish-'),
                'time' => $time,
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'type' => 'fishing',
                'activity' => 'Fishing Activity',
                'location' => ($lat !== null && $lon !== null) ? round($lat, 4).', '.round($lon, 4) : 'N/A',
                'lat' => $lat,
                'lon' => $lon,
            ];

            $alerts[] = [
                'id' => 'alert-fish-'.($ev['id'] ?? uniqid()),
                'category' => 'Fishing',
                'severity' => 'INFO',
                'type' => 'apparent_fishing',
                'status' => 'NEW',
                'title' => 'Indikasi Aktivitas Penangkapan Ikan',
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'time' => $time,
                'lat' => $lat,
                'lon' => $lon,
                'location' => ($lat !== null && $lon !== null) ? round($lat, 4).', '.round($lon, 4) : 'N/A',
                'source' => 'Global Fishing Watch',
                'boundary_source' => 'BIG',
                'boundary_layer' => 10,
                'reason' => 'Peristiwa ini merupakan indikasi penangkapan ikan berdasarkan model analitik algoritma pergerakan AIS/VMS (Apparent Fishing Event) dan bukan verifikasi penangkapan faktual atau kesimpulan penangkapan ikan ilegal.',
                'description' => "Terdeteksi indikasi penangkapan ikan oleh kapal {$vesselName} di perairan ZEE Aceh.",
            ];
        }

        // Add alerts for new or live vessels
        foreach ($vesselsResult['vessels'] as $v) {
            if (($v['status'] ?? '') === 'LIVE') {
                $alerts[] = [
                    'id' => 'alert-live-'.$v['id'],
                    'category' => 'Live Vessel',
                    'severity' => 'INFO',
                    'type' => 'live_observation',
                    'status' => 'NEW',
                    'title' => 'Observasi Kapal Terkini (LIVE)',
                    'vessel' => $v['name'] ?? 'Kapal Tanpa Nama',
                    'vessel_id' => $v['id'],
                    'mmsi' => $v['mmsi'] ?? null,
                    'time' => $v['last_seen'] ?? null,
                    'lat' => $v['lat'] ?? null,
                    'lon' => $v['lon'] ?? null,
                    'location' => (isset($v['lat'], $v['lon']) && $v['lat'] !== null && $v['lon'] !== null) ? round($v['lat'], 4).', '.round($v['lon'], 4) : 'N/A',
                    'source' => 'Global Fishing Watch',
                    'boundary_source' => 'BIG',
                    'boundary_layer' => 10,
                    'reason' => 'Kapal diobservasi di dalam batas ZEE Aceh dengan usia data terbaru < 24 jam.',
                    'description' => "Kapal {$v['name']} ({$v['vessel_type']}) diobservasi dengan data terbaru < 24 jam di ZEE Aceh.",
                ];
            }
        }

        // Sort activity feed and alerts by time descending
        usort($activityFeed, function ($a, $b) {
            return strtotime($b['time'] ?? '1970-01-01') <=> strtotime($a['time'] ?? '1970-01-01');
        });
        usort($alerts, function ($a, $b) {
            return strtotime($b['time'] ?? '1970-01-01') <=> strtotime($a['time'] ?? '1970-01-01');
        });

        $detectedVessels = $vesselsResult['summary']['total_vessels'] ?? 0;
        $liveRecentCount = ($vesselsResult['summary']['live_vessels'] ?? 0) + ($vesselsResult['summary']['recent_vessels'] ?? 0);
        $trackPointsCount = count($vesselsResult['vessels'] ?? []);

        $dashboardResponse = [
            'success' => true,
            'live' => true,
            'last_updated' => $vesselsResult['last_updated'] ?? now()->toIso8601String(),
            'data_age_seconds' => $vesselsResult['data_age_seconds'] ?? 0,
            'aoi' => $vesselsResult['aoi'],
            'description' => 'Observasi kapal Global Fishing Watch yang berada di dalam batas ZEE Aceh berdasarkan BIG',
            'period' => $vesselsResult['period'],
            'timezone' => 'UTC',
            'timezone_display' => 'WIB (UTC+7)',
            'kpi' => [
                'total_vessels' => $detectedVessels,
                'detected_vessels' => $detectedVessels,
                'active_vessels' => $liveRecentCount,
                'live_recent' => $liveRecentCount,
                'fishing_events' => $fishingCount,
                'fishing_activity' => $fishingCount,
                'track_points' => $trackPointsCount,
                'loitering' => $loiteringCount,
                'loitering_events' => $loiteringCount,
                'encounters' => $encounterCount,
                'encounters_status' => 'BLOCKED — DATA/API NOT AVAILABLE',
                'port_visits' => $portVisitCount,
                'port_visits_status' => 'BLOCKED — DATA/API NOT AVAILABLE',
                'alerts' => count($alerts),
                'alerts_count' => count($alerts),
            ],
            'summary' => $vesselsResult['summary'],
            'vessels' => $vesselsResult['vessels'],
            'pagination' => $vesselsResult['pagination'],
            'activity_feed' => $activityFeed,
            'alerts' => $alerts,
            'events' => $rawEvents,
            'status' => 200,
        ];

        Cache::put($cacheKey, $dashboardResponse, 300);

        return $dashboardResponse;
    }

    /**
     * Determine whether a longitude/latitude coordinate is inside a GeoJSON geometry,
     * defaulting to the authoritative BIG ZEE boundary when no geometry is passed.
     *
     * @param  array<string, mixed>|null  $geometry
     */
    public function isPointInGeometry(float $lon, float $lat, ?array $geometry = null): bool
    {
        /** @var BigMaritimeBoundaryService $bigService */
        $bigService = app(BigMaritimeBoundaryService::class);

        return $bigService->isPointInGeometry($lon, $lat, $geometry);
    }
}
