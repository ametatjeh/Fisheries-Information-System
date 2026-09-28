<?php

namespace App\Services\Gfw;

use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use App\Services\GFWService;
use App\Services\Gis\BigMaritimeBoundaryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Throwable;

class GfwIngestionService
{
    public function __construct(
        protected GfwApiService $apiService,
        protected GfwVesselService $vesselService,
        protected GfwActivityService $activityService,
        protected AoiService $aoiService,
        protected ?GfwQueryGeometryService $queryGeometryService = null
    ) {
        $this->queryGeometryService = $queryGeometryService ?? app(GfwQueryGeometryService::class);
    }

    /**
     * Execute a controlled vessel and presence ingestion.
     *
     * @param  array{
     *     query?: string,
     *     aoi?: string,
     *     days?: int,
     *     limit?: int,
     *     start_date?: string,
     *     end_date?: string
     * }  $params
     * @return array{
     *     dry_run: bool,
     *     aoi: string,
     *     query_period: array{start_date: string, end_date: string},
     *     vessels_found: int,
     *     new_vessels: int,
     *     vessels_updated: int,
     *     vessels_skipped: int,
     *     presence_found: int,
     *     new_presence: int,
     *     duplicate_presence: int,
     *     invalid_records: int,
     *     errors: list<string>,
     *     sync_run_id: int|null
     * }
     */
    public function ingest(array $params = [], bool $dryRun = false): array
    {
        $startedAt = now();
        $aoi = (string) ($params['aoi'] ?? 'zee-indonesia-aceh');
        $query = trim((string) ($params['query'] ?? 'MEULABOH'));
        $limit = max(1, min((int) ($params['limit'] ?? 5), 10)); // Strict controlled batch (max 10)
        $days = max(1, min((int) ($params['days'] ?? 7), 7)); // Strict window <= 7 days

        $endDate = Carbon::now()->toDateString();
        $startDate = Carbon::now()->subDays($days)->toDateString();

        $stats = [
            'dry_run' => $dryRun,
            'aoi' => $aoi,
            'query_period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'vessels_found' => 0,
            'new_vessels' => 0,
            'vessels_updated' => 0,
            'vessels_skipped' => 0,
            'presence_found' => 0,
            'new_presence' => 0,
            'duplicate_presence' => 0,
            'invalid_records' => 0,
            'errors' => [],
            'sync_run_id' => null,
        ];

        // 1. Search vessels via canonical GfwVesselService
        $searchResult = $this->vesselService->search($query, [
            'limit' => $limit,
            'refresh' => false,
            'persist' => false,
        ]);

        if (! $searchResult['success']) {
            $stats['errors'][] = $searchResult['error'] ?? 'Gagal mencari kapal dari GFW API.';

            if (! $dryRun) {
                $this->recordSyncRun([
                    'aoi' => $aoi,
                    'date_from' => $startDate,
                    'date_to' => $endDate,
                    'dataset' => (string) config('gfw.vessel_dataset', 'public-global-vessel-identity:latest'),
                    'endpoint' => '/vessels/search',
                    'records_found' => 0,
                    'records_saved' => 0,
                    'status' => 'failed',
                    'error_message' => implode('; ', $stats['errors']),
                    'started_at' => $startedAt,
                    'finished_at' => now(),
                ]);
            }

            return $stats;
        }

        $vessels = $searchResult['data'] ?? [];
        $stats['vessels_found'] = count($vessels);

        // 2. Process each vessel identity and its presence/tracks
        foreach ($vessels as $vesselData) {
            $vesselId = $vesselData['gfw_vessel_id'] ?? null;
            if (empty($vesselId)) {
                $stats['invalid_records']++;

                continue;
            }

            // Ingest vessel identity
            $vesselIngestResult = $this->ingestVessel($vesselData, $dryRun);
            if ($vesselIngestResult['status'] === 'new') {
                $stats['new_vessels']++;
            } elseif ($vesselIngestResult['status'] === 'updated') {
                $stats['vessels_updated']++;
            } else {
                $stats['vessels_skipped']++;
            }

            // Fetch and ingest tracks / presence for this vessel
            $trackResult = $this->activityService->getVesselActivity((string) $vesselId, [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'refresh' => false,
            ]);

            if ($trackResult['success'] && ! empty($trackResult['data'])) {
                $tracks = $trackResult['data'];
                $stats['presence_found'] += count($tracks);

                foreach ($tracks as $trackPoint) {
                    $presenceResult = $this->ingestPresence((string) $vesselId, $trackPoint, $aoi, $dryRun);
                    if ($presenceResult['status'] === 'new') {
                        $stats['new_presence']++;
                    } elseif ($presenceResult['status'] === 'duplicate') {
                        $stats['duplicate_presence']++;
                    } elseif ($presenceResult['status'] === 'outside_aoi') {
                        // Excluded by GFW Query AOI spatial filter
                        continue;
                    } else {
                        $stats['invalid_records']++;
                    }
                }
            }
        }

        // 3. Record Sync Run in sistem_gfw.gfw_sync_runs (Live mode only)
        if (! $dryRun) {
            $syncRun = $this->recordSyncRun([
                'aoi' => $aoi,
                'date_from' => $startDate,
                'date_to' => $endDate,
                'dataset' => (string) config('gfw.activity_dataset', 'public-global-vessel-presence:latest'),
                'endpoint' => '/vessels',
                'records_found' => $stats['presence_found'],
                'records_saved' => $stats['new_presence'],
                'status' => count($stats['errors']) > 0 ? 'partial' : 'success',
                'error_message' => count($stats['errors']) > 0 ? implode('; ', $stats['errors']) : null,
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $stats['sync_run_id'] = $syncRun?->id;
        }

        return $stats;
    }

    /**
     * Ingest a single vessel identity record into sistem_gfw.gfw_vessels.
     *
     * @param  array<string, mixed>  $data
     * @return array{status: 'new'|'updated'|'skipped'|'invalid'}
     */
    public function ingestVessel(array $data, bool $dryRun = false): array
    {
        $cleanId = trim((string) ($data['gfw_vessel_id'] ?? ''));
        if ($cleanId === '') {
            return ['status' => 'invalid'];
        }

        // Sanitize raw payload before computing hash to ensure NO tokens or Authorization headers are saved
        $rawPayload = is_array($data['raw_data'] ?? null) ? $data['raw_data'] : [];
        unset($rawPayload['authorization'], $rawPayload['token'], $rawPayload['api_key']);

        $rawHash = md5(json_encode($rawPayload));

        $existing = GfwVessel::where('gfw_vessel_id', $cleanId)->first();

        if ($existing) {
            if ($existing->raw_hash === $rawHash && ! empty($existing->raw_hash)) {
                return ['status' => 'skipped'];
            }

            if (! $dryRun) {
                $existing->update([
                    'name' => $data['name'] ?? $existing->name,
                    'ship_name' => $data['ship_name'] ?? $data['name'] ?? $existing->ship_name,
                    'mmsi' => $data['mmsi'] ?? $existing->mmsi,
                    'imo' => $data['imo'] ?? $existing->imo,
                    'flag' => $data['flag'] ?? $existing->flag,
                    'vessel_type' => $data['vessel_type'] ?? $existing->vessel_type,
                    'vessel_class' => $data['vessel_class'] ?? $existing->vessel_class,
                    'gear_type' => $data['gear_type'] ?? $existing->gear_type,
                    'length_m' => $data['length_m'] ?? $existing->length_m,
                    'tonnage_gt' => $data['tonnage_gt'] ?? $existing->tonnage_gt,
                    'gross_tonnage' => $data['gross_tonnage'] ?? $data['tonnage_gt'] ?? $existing->gross_tonnage,
                    'engine_power_kw' => $data['engine_power_kw'] ?? $existing->engine_power_kw,
                    'last_seen_at' => now(),
                    'last_updated_at' => now(),
                    'last_synced_at' => now(),
                    'raw_hash' => $rawHash,
                    'raw_data' => $rawPayload,
                ]);
            }

            return ['status' => 'updated'];
        }

        // New vessel
        if (! $dryRun) {
            GfwVessel::create([
                'gfw_vessel_id' => $cleanId,
                'name' => $data['name'] ?? null,
                'ship_name' => $data['ship_name'] ?? $data['name'] ?? null,
                'mmsi' => $data['mmsi'] ?? null,
                'imo' => $data['imo'] ?? null,
                'flag' => $data['flag'] ?? null,
                'vessel_type' => $data['vessel_type'] ?? null,
                'vessel_class' => $data['vessel_class'] ?? null,
                'gear_type' => $data['gear_type'] ?? null,
                'length_m' => $data['length_m'] ?? null,
                'tonnage_gt' => $data['tonnage_gt'] ?? null,
                'gross_tonnage' => $data['gross_tonnage'] ?? $data['tonnage_gt'] ?? null,
                'engine_power_kw' => $data['engine_power_kw'] ?? null,
                'source' => 'GFW',
                'source_version' => (string) config('gfw.vessel_dataset', 'public-global-vessel-identity:latest'),
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'last_updated_at' => now(),
                'last_synced_at' => now(),
                'raw_hash' => $rawHash,
                'raw_data' => $rawPayload,
            ]);
        }

        return ['status' => 'new'];
    }

    /**
     * Ingest a single vessel presence/track observation with deterministic deduplication
     * and authoritative BIG ZEE spatial validation.
     *
     * @param  array<string, mixed>  $track
     * @return array{status: 'new'|'duplicate'|'invalid'|'outside_aoi'}
     */
    public function ingestPresence(string $vesselId, array $track, string $aoi, bool $dryRun = false): array
    {
        $lat = $track['latitude'] ?? $track['lat'] ?? null;
        $lon = $track['longitude'] ?? $track['lon'] ?? null;
        $observedAtStr = $track['observation_timestamp'] ?? $track['timestamp'] ?? null;

        // Data quality validations
        if (! is_numeric($lat) || ! is_numeric($lon) || empty($observedAtStr)) {
            return ['status' => 'invalid'];
        }

        $latFloat = (float) $lat;
        $lonFloat = (float) $lon;

        if ($latFloat < -90.0 || $latFloat > 90.0 || $lonFloat < -180.0 || $lonFloat > 180.0) {
            return ['status' => 'invalid'];
        }

        try {
            $observedAt = Carbon::parse($observedAtStr);
        } catch (Throwable) {
            return ['status' => 'invalid'];
        }

        // Spatial validation: verify coordinates reside within GFW Query AOI boundary
        if (! $this->queryGeometryService->isPointInPolygon($lonFloat, $latFloat)) {
            Log::info('GFW presence point excluded by spatial filter (outside GFW Query AOI boundary)', [
                'vessel_id' => $vesselId,
                'latitude' => $latFloat,
                'longitude' => $lonFloat,
                'boundary_source' => 'GFW_QUERY_AOI',
            ]);

            return ['status' => 'outside_aoi'];
        }

        $speed = isset($track['speed_knots']) && is_numeric($track['speed_knots'])
            ? round((float) $track['speed_knots'], 2)
            : (isset($track['speed']) && is_numeric($track['speed']) ? round((float) $track['speed'], 2) : null);

        $course = isset($track['course']) && is_numeric($track['course'])
            ? round((float) $track['course'], 2)
            : null;

        // Deterministic deduplication check on (gfw_vessel_id, observed_at, latitude, longitude)
        $existingPresence = GfwVesselPresence::where('gfw_vessel_id', $vesselId)
            ->where('observed_at', $observedAt)
            ->where('latitude', round($latFloat, 6))
            ->where('longitude', round($lonFloat, 6))
            ->first();

        if ($existingPresence) {
            return ['status' => 'duplicate'];
        }

        if (! $dryRun) {
            GfwVesselPresence::create([
                'gfw_vessel_id' => $vesselId,
                'aoi' => $aoi,
                'observed_at' => $observedAt,
                'latitude' => round($latFloat, 6),
                'longitude' => round($lonFloat, 6),
                'speed' => $speed,
                'course' => $course,
                'vessel_type' => $track['vessel_type'] ?? null,
                'flag' => $track['flag'] ?? null,
                'source_dataset' => (string) config('gfw.activity_dataset', 'public-global-vessel-presence:latest'),
                'source_version' => 'v3',
            ]);
        }

        return ['status' => 'new'];
    }

    /**
     * Synchronize vessel presence and identities directly from GFW Events with strict idempotency
     * and spatial validation against BIG Layer 10 ZEE Aceh boundary.
     *
     * @param  list<array<string, mixed>>  $rawEntries
     * @param  array<string, mixed>  $context
     * @return array{
     *     vessels_processed: int,
     *     vessels_new: int,
     *     vessels_updated: int,
     *     presence_processed: int,
     *     presence_new: int,
     *     presence_duplicate: int,
     *     presence_invalid: int,
     *     presence_outside_aoi: int,
     *     sync_run_id: int|null
     * }
     */
    public function ingestEvents(array $rawEntries, array $context = [], bool $dryRun = false): array
    {
        $startedAt = now();
        $aoi = (string) ($context['aoi'] ?? 'zee-indonesia-aceh');
        $dateFrom = (string) ($context['date_from'] ?? now()->subDays(7)->toDateString());
        $dateTo = (string) ($context['date_to'] ?? now()->toDateString());
        $dataset = (string) ($context['dataset'] ?? config('gfw.fishing_events_dataset', 'public-global-fishing-events:latest'));

        $stats = [
            'vessels_processed' => 0,
            'vessels_new' => 0,
            'vessels_updated' => 0,
            'presence_processed' => 0,
            'presence_new' => 0,
            'presence_duplicate' => 0,
            'presence_invalid' => 0,
            'presence_outside_aoi' => 0,
            'sync_run_id' => null,
        ];

        if (empty($rawEntries)) {
            return $stats;
        }

        /** @var BigMaritimeBoundaryService $bigService */
        $bigService = app(BigMaritimeBoundaryService::class);
        $bigGeomResult = $bigService->getAcehZeeGeometry();
        $bigGeometry = ($bigGeomResult['success'] ?? false) ? ($bigGeomResult['geometry'] ?? null) : null;

        $processedVesselIds = [];

        foreach ($rawEntries as $entry) {
            if (! is_array($entry)) {
                $stats['presence_invalid']++;

                continue;
            }

            $lat = isset($entry['position']['lat']) && is_numeric($entry['position']['lat']) ? (float) $entry['position']['lat'] : null;
            $lon = isset($entry['position']['lon']) && is_numeric($entry['position']['lon']) ? (float) $entry['position']['lon'] : null;

            if ($lat === null || $lon === null || $lat < -90.0 || $lat > 90.0 || $lon < -180.0 || $lon > 180.0) {
                $stats['presence_invalid']++;

                continue;
            }

            // Spatial check: strictly ensure inside BIG Layer 10 ZEE Aceh (or Query AOI if BIG geometry not loaded)
            $isInside = $bigGeometry
                ? $bigService->isPointInGeometry($lon, $lat, $bigGeometry)
                : $this->queryGeometryService->isPointInPolygon($lon, $lat);

            if (! $isInside) {
                $stats['presence_outside_aoi']++;

                continue;
            }

            $vesselRaw = $entry['vessel'] ?? [];
            $vId = ! empty($vesselRaw['id']) ? trim((string) $vesselRaw['id']) : (! empty($entry['vesselId']) ? trim((string) $entry['vesselId']) : null);
            $ssvid = ! empty($vesselRaw['ssvid']) ? trim((string) $vesselRaw['ssvid']) : null;
            $mmsi = ! empty($vesselRaw['ssvid']) ? trim((string) $vesselRaw['ssvid']) : (! empty($vesselRaw['mmsi']) ? trim((string) $vesselRaw['mmsi']) : null);
            $imo = ! empty($vesselRaw['imo']) ? trim((string) $vesselRaw['imo']) : null;

            // Resolve canonical GFW Vessel UUID
            $canonicalGfwId = $vId ?? ($ssvid && ! ctype_digit($ssvid) ? $ssvid : null);
            if (empty($canonicalGfwId) && $mmsi !== null) {
                try {
                    $existingV = GfwVessel::where('mmsi', $mmsi)->first();
                    if ($existingV && ! empty($existingV->gfw_vessel_id)) {
                        $canonicalGfwId = $existingV->gfw_vessel_id;
                    }
                } catch (Throwable) {
                }
            }
            if (empty($canonicalGfwId)) {
                $canonicalGfwId = $mmsi ?? ($entry['id'] ?? null);
            }

            if (empty($canonicalGfwId)) {
                $stats['presence_invalid']++;

                continue;
            }

            // 1. Ingest/Update Vessel record in sistem_gfw.gfw_vessels (if not yet processed in this batch)
            if (! isset($processedVesselIds[$canonicalGfwId])) {
                $vesselData = [
                    'gfw_vessel_id' => $canonicalGfwId,
                    'name' => ! empty($vesselRaw['name']) ? trim((string) $vesselRaw['name']) : null,
                    'ship_name' => ! empty($vesselRaw['name']) ? trim((string) $vesselRaw['name']) : null,
                    'mmsi' => $mmsi,
                    'imo' => $imo,
                    'callsign' => ! empty($vesselRaw['callsign']) ? trim((string) $vesselRaw['callsign']) : null,
                    'flag' => ! empty($vesselRaw['flag']) ? strtoupper(trim((string) $vesselRaw['flag'])) : null,
                    'vessel_type' => GFWService::normalizeVesselType($vesselRaw['type'] ?? ($entry['vessel_type'] ?? null)),
                    'gear_type' => $vesselRaw['gear'] ?? ($vesselRaw['gear_type'] ?? null),
                    'length_m' => isset($vesselRaw['length']) && is_numeric($vesselRaw['length']) ? (float) $vesselRaw['length'] : (isset($vesselRaw['lengthM']) && is_numeric($vesselRaw['lengthM']) ? (float) $vesselRaw['lengthM'] : null),
                    'tonnage_gt' => isset($vesselRaw['tonnage']) && is_numeric($vesselRaw['tonnage']) ? (float) $vesselRaw['tonnage'] : (isset($vesselRaw['tonnageGt']) && is_numeric($vesselRaw['tonnageGt']) ? (float) $vesselRaw['tonnageGt'] : null),
                    'raw_data' => $entry,
                ];

                $vRes = $this->ingestVessel($vesselData, $dryRun);
                if ($vRes['status'] === 'new') {
                    $stats['vessels_new']++;
                } elseif ($vRes['status'] === 'updated') {
                    $stats['vessels_updated']++;
                }
                $stats['vessels_processed']++;
                $processedVesselIds[$canonicalGfwId] = true;
            }

            // 2. Ingest Presence record in sistem_gfw.gfw_vessel_presence (Idempotent)
            $obsTimeStr = $entry['end'] ?? ($entry['start'] ?? null);
            if (! $obsTimeStr) {
                $stats['presence_invalid']++;

                continue;
            }

            try {
                $observedAt = Carbon::parse($obsTimeStr);
            } catch (Throwable) {
                $stats['presence_invalid']++;

                continue;
            }

            $speed = isset($entry['speed']) && is_numeric($entry['speed'])
                ? round((float) $entry['speed'], 2)
                : (isset($entry['speedKnots']) && is_numeric($entry['speedKnots']) ? round((float) $entry['speedKnots'], 2) : null);

            $course = isset($entry['course']) && is_numeric($entry['course'])
                ? round((float) $entry['course'], 2)
                : (isset($entry['heading']) && is_numeric($entry['heading']) ? round((float) $entry['heading'], 2) : null);

            // Deduplication on (gfw_vessel_id, observed_at, latitude, longitude)
            $existingPresence = GfwVesselPresence::where('gfw_vessel_id', $canonicalGfwId)
                ->where('observed_at', $observedAt)
                ->where('latitude', round($lat, 6))
                ->where('longitude', round($lon, 6))
                ->first();

            $stats['presence_processed']++;

            if ($existingPresence) {
                $stats['presence_duplicate']++;

                continue;
            }

            if (! $dryRun) {
                GfwVesselPresence::create([
                    'gfw_vessel_id' => $canonicalGfwId,
                    'aoi' => $aoi,
                    'observed_at' => $observedAt,
                    'latitude' => round($lat, 6),
                    'longitude' => round($lon, 6),
                    'speed' => $speed,
                    'course' => $course,
                    'vessel_type' => GFWService::normalizeVesselType($vesselRaw['type'] ?? ($entry['vessel_type'] ?? null)),
                    'flag' => ! empty($vesselRaw['flag']) ? strtoupper(trim((string) $vesselRaw['flag'])) : null,
                    'source_dataset' => $dataset,
                    'source_version' => 'v3',
                ]);
            }

            $stats['presence_new']++;
        }

        // Record Sync Run in sistem_gfw.gfw_sync_runs (if live mode and records were processed)
        if (! $dryRun && ($stats['presence_processed'] > 0 || $stats['vessels_processed'] > 0)) {
            $syncRun = $this->recordSyncRun([
                'aoi' => $aoi,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'dataset' => $dataset,
                'endpoint' => '/v3/events',
                'records_found' => count($rawEntries),
                'records_saved' => $stats['presence_new'],
                'status' => 'success',
                'started_at' => $startedAt,
                'finished_at' => now(),
            ]);

            $stats['sync_run_id'] = $syncRun?->id;
        }

        return $stats;
    }

    /**
     * Record a sync run audit log in sistem_gfw.gfw_sync_runs.
     *
     * @param  array<string, mixed>  $auditData
     */
    public function recordSyncRun(array $auditData): ?GfwSyncRun
    {
        try {
            return GfwSyncRun::create([
                'aoi' => $auditData['aoi'] ?? 'zee-indonesia-aceh',
                'date_from' => $auditData['date_from'],
                'date_to' => $auditData['date_to'],
                'dataset' => $auditData['dataset'] ?? 'public-global-vessel-presence:latest',
                'endpoint' => $auditData['endpoint'] ?? '/vessels',
                'records_found' => (int) ($auditData['records_found'] ?? 0),
                'records_saved' => (int) ($auditData['records_saved'] ?? 0),
                'status' => in_array($auditData['status'] ?? '', ['running', 'success', 'partial', 'failed']) ? $auditData['status'] : 'running',
                'error_message' => $auditData['error_message'] ?? null,
                'started_at' => $auditData['started_at'] ?? now(),
                'finished_at' => $auditData['finished_at'] ?? now(),
            ]);
        } catch (Throwable $e) {
            Log::warning('Failed recording GFW sync run', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
