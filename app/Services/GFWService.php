<?php

namespace App\Services;

use App\Models\Gfw\GfwVessel;
use App\Services\Gfw\GfwActivityService;
use Carbon\Carbon;
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

        try {
            $response = Http::baseUrl($url)
                ->withToken($token)
                ->timeout(30)
                ->connectTimeout(5)
                ->acceptJson()
                ->asJson()
                ->withQueryParameters([
                    'limit' => 100,
                    'offset' => 0,
                ])
                ->post('/v3/events', [
                    'datasets' => $datasets,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'geometry' => $cleanGeometry,
                ]);

            $status = $response->status();

            if (! $response->successful()) {
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

            $payload = $response->json() ?? [];
            $rawEntries = $payload['entries'] ?? [];

            // Group entries into distinct vessels
            /** @var array<string, array<string, mixed>> $vesselsById */
            $vesselsById = [];

            foreach ($rawEntries as $entry) {
                if (! is_array($entry)) {
                    continue;
                }

                $vesselRaw = $entry['vessel'] ?? [];
                $vId = $vesselRaw['id'] ?? $vesselRaw['ssvid'] ?? $vesselRaw['mmsi'] ?? ($entry['id'] ?? null);
                if (empty($vId)) {
                    continue;
                }

                $vKey = (string) $vId;
                $lat = isset($entry['position']['lat']) && is_numeric($entry['position']['lat']) ? (float) $entry['position']['lat'] : null;
                $lon = isset($entry['position']['lon']) && is_numeric($entry['position']['lon']) ? (float) $entry['position']['lon'] : null;

                if ($lat !== null && ($lat < -90.0 || $lat > 90.0)) {
                    $lat = null;
                }
                if ($lon !== null && ($lon < -180.0 || $lon > 180.0)) {
                    $lon = null;
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
                    // Update latest observation and positions
                    if ($lat !== null && $lon !== null) {
                        $vesselsById[$vKey]['position'] = ['lat' => $lat, 'lon' => $lon];
                        $vesselsById[$vKey]['lat'] = $lat;
                        $vesselsById[$vKey]['lon'] = $lon;
                    }
                    if ($startTime && (! $vesselsById[$vKey]['first_seen'] || $startTime < $vesselsById[$vKey]['first_seen'])) {
                        $vesselsById[$vKey]['first_seen'] = $startTime;
                    }
                    if ($obsTime && (! $vesselsById[$vKey]['last_seen'] || $obsTime > $vesselsById[$vKey]['last_seen'])) {
                        $vesselsById[$vKey]['last_seen'] = $obsTime;
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
                if ($ageSeconds !== null && $ageSeconds <= 86400) {
                    $vItem['status'] = 'LIVE';
                } elseif ($ageSeconds !== null && $ageSeconds <= 259200) {
                    $vItem['status'] = 'RECENT';
                } else {
                    $vItem['status'] = 'STALE';
                }
            }
            unset($vItem);

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
                    $mmsiMatch = ! empty($v['mmsi']) && str_contains(strtolower($v['mmsi']), $qLower);
                    $imoMatch = ! empty($v['imo']) && str_contains(strtolower($v['imo']), $qLower);
                    if (! $nameMatch && ! $mmsiMatch && ! $imoMatch) {
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
                'last_updated' => $latestSeenTimestamp ?? $nowUtc->toIso8601String(),
                'data_age_seconds' => $minDataAgeSeconds ?? 0,
                'aoi' => [
                    'id' => 'zee-indonesia-aceh',
                    'name' => 'ZEE Indonesia - Kawasan Aceh',
                    'source' => 'BIG',
                    'crs' => 'EPSG:4326',
                ],
                'period' => [
                    'start' => $startDate,
                    'end' => $endDate,
                ],
                'summary' => [
                    'total_vessels' => $totalVessels,
                    'fishing_vessels' => $fishingVessels,
                    'other_vessels' => $otherVessels,
                    'live_vessels' => $liveVessels,
                    'recent_vessels' => $recentVessels,
                    'stale_vessels' => $staleVessels,
                    'flags' => $flagsSummary,
                    'vessel_types' => $vesselTypesSummary,
                ],
                'vessels' => $pagedVessels,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => $hasMore,
                ],
                'status' => 200,
            ];
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

            // Sort chronologically
            usort($rawPoints, function ($a, $b) {
                $tA = strtotime($a['timestamp'] ?? $a['observed_at'] ?? '1970-01-01');
                $tB = strtotime($b['timestamp'] ?? $b['observed_at'] ?? '1970-01-01');

                return $tA <=> $tB;
            });

            $coordinates = [];
            $pointFeatures = [];
            $firstDetected = null;
            $lastDetected = null;

            foreach ($rawPoints as $pt) {
                $lat = isset($pt['latitude']) ? (float) $pt['latitude'] : (isset($pt['lat']) ? (float) $pt['lat'] : null);
                $lon = isset($pt['longitude']) ? (float) $pt['longitude'] : (isset($pt['lon']) ? (float) $pt['lon'] : null);
                $time = $pt['timestamp'] ?? $pt['observed_at'] ?? null;

                if ($lat !== null && $lon !== null && $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180) {
                    $coordinates[] = [$lon, $lat];

                    if ($firstDetected === null && $time !== null) {
                        $firstDetected = $time;
                    }
                    if ($time !== null) {
                        $lastDetected = $time;
                    }

                    $pointFeatures[] = [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [$lon, $lat],
                        ],
                        'properties' => [
                            'timestamp' => $time,
                            'speed_knots' => $pt['speed_knots'] ?? $pt['speed'] ?? null,
                            'course' => $pt['course'] ?? null,
                        ],
                    ];
                }
            }

            $sufficient = count($coordinates) >= 2;
            $lineFeature = null;
            if ($sufficient) {
                $lineFeature = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'LineString',
                        'coordinates' => $coordinates,
                    ],
                    'properties' => [
                        'vessel_id' => $cleanId,
                        'points_count' => count($coordinates),
                    ],
                ];
            }

            $features = [];
            if ($lineFeature) {
                $features[] = $lineFeature;
            }
            $features = array_merge($features, $pointFeatures);

            return [
                'success' => true,
                'vessel_id' => $cleanId,
                'sufficient' => $sufficient,
                'message' => $sufficient ? 'Data lintasan tersedia.' : 'Track data insufficient',
                'points_count' => count($coordinates),
                'first_detected' => $firstDetected,
                'last_detected' => $lastDetected,
                'approximate_coverage' => count($coordinates) > 0 ? (count($coordinates).' posisi tercatat') : 'Track data insufficient',
                'track_info' => [
                    'first_detected' => $firstDetected,
                    'last_detected' => $lastDetected,
                    'position_count' => count($coordinates),
                    'approximate_coverage' => count($coordinates) > 0 ? (count($coordinates).' posisi tercatat') : 'Track data insufficient',
                    'note' => $sufficient ? null : 'Track data insufficient',
                ],
                'line_geojson' => $lineFeature ? $lineFeature['geometry'] : null,
                'points_geojson' => [
                    'type' => 'FeatureCollection',
                    'features' => $pointFeatures,
                ],
                'track' => [
                    'type' => 'FeatureCollection',
                    'features' => $features,
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
        $vesselsResult = $this->getVesselsInAoi($geometry, $startDate, $endDate, $options);
        if (! ($vesselsResult['success'] ?? false)) {
            return $vesselsResult;
        }

        $eventsResult = $this->getEvents($geometry, $startDate, $endDate, [
            'limit' => 100,
            'offset' => 0,
        ]);

        $rawEvents = $eventsResult['events'] ?? [];

        // Count event types
        $fishingCount = 0;
        $encounterCount = 0;
        $loiteringCount = 0;
        $portVisitCount = 0;

        $activityFeed = [];
        $alerts = [];

        foreach ($rawEvents as $ev) {
            $type = strtolower((string) ($ev['type'] ?? ''));
            $vesselName = $ev['vessel']['name'] ?? 'Kapal Tidak Dikenal';
            $vesselId = $ev['vessel']['id'] ?? null;
            $mmsi = $ev['vessel']['ssvid'] ?? null;
            $time = $ev['start'] ?? $ev['end'] ?? null;
            $lat = $ev['position']['lat'] ?? null;
            $lon = $ev['position']['lon'] ?? null;

            $activityLabel = 'Aktivitas Terdeteksi';
            $category = 'Activity';

            if (str_contains($type, 'fish')) {
                $fishingCount++;
                $activityLabel = 'Fishing Activity';
                $category = 'Fishing';
            } elseif (str_contains($type, 'encount')) {
                $encounterCount++;
                $activityLabel = 'Encounter Event';
                $category = 'Encounter';
            } elseif (str_contains($type, 'loiter')) {
                $loiteringCount++;
                $activityLabel = 'Loitering Event';
                $category = 'Loitering';
            } elseif (str_contains($type, 'port')) {
                $portVisitCount++;
                $activityLabel = 'Port Visit';
                $category = 'Port Visit';
            }

            $activityFeed[] = [
                'id' => $ev['id'] ?? uniqid('ev-'),
                'time' => $time,
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'type' => $ev['type'] ?? 'activity',
                'activity' => $activityLabel,
                'location' => ($lat !== null && $lon !== null) ? round($lat, 4).', '.round($lon, 4) : 'N/A',
                'lat' => $lat,
                'lon' => $lon,
            ];

            $alerts[] = [
                'id' => 'alert-'.($ev['id'] ?? uniqid()),
                'category' => $category,
                'title' => $activityLabel.' terdeteksi',
                'vessel' => $vesselName,
                'vessel_id' => $vesselId,
                'mmsi' => $mmsi,
                'time' => $time,
                'lat' => $lat,
                'lon' => $lon,
                'description' => "Terdeteksi {$activityLabel} oleh kapal {$vesselName} di perairan ZEE Aceh.",
            ];
        }

        // Add alerts for new or live vessels
        foreach ($vesselsResult['vessels'] as $v) {
            if (($v['status'] ?? '') === 'LIVE') {
                $alerts[] = [
                    'id' => 'alert-live-'.$v['id'],
                    'category' => 'Live Vessel',
                    'title' => 'Observasi kapal terbaru (LIVE)',
                    'vessel' => $v['name'] ?? 'Kapal Tanpa Nama',
                    'vessel_id' => $v['id'],
                    'mmsi' => $v['mmsi'] ?? null,
                    'time' => $v['last_seen'] ?? null,
                    'lat' => $v['lat'] ?? null,
                    'lon' => $v['lon'] ?? null,
                    'description' => "Kapal {$v['name']} ({$v['vessel_type']}) diobservasi dengan data terbaru < 24 jam.",
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

        return [
            'success' => true,
            'live' => true,
            'last_updated' => $vesselsResult['last_updated'] ?? now()->toIso8601String(),
            'data_age_seconds' => $vesselsResult['data_age_seconds'] ?? 0,
            'aoi' => $vesselsResult['aoi'],
            'period' => $vesselsResult['period'],
            'kpi' => [
                'detected_vessels' => $detectedVessels,
                'live_recent' => $liveRecentCount,
                'fishing_activity' => $fishingCount,
                'encounters' => $encounterCount,
                'loitering' => $loiteringCount,
                'port_visits' => $portVisitCount,
            ],
            'summary' => $vesselsResult['summary'],
            'vessels' => $vesselsResult['vessels'],
            'pagination' => $vesselsResult['pagination'],
            'activity_feed' => $activityFeed,
            'alerts' => $alerts,
            'events' => $rawEvents,
            'status' => 200,
        ];
    }
}
