<?php

namespace App\Services\Gfw;

use App\Models\Gfw\GfwSyncRun;
use App\Models\Gfw\GfwVessel;
use App\Models\Gfw\GfwVesselPresence;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GfwObservatoryService
{
    /**
     * Get paginated list of ingested GFW vessels with optional filters.
     *
     * @param  array{
     *     search?: string,
     *     vessel_type?: string,
     *     flag?: string,
     *     sort_by?: string,
     *     sort_dir?: string
     * }  $filters
     */
    public function getVessels(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = GfwVessel::query();

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('ship_name', 'like', "%{$search}%")
                    ->orWhere('mmsi', 'like', "%{$search}%")
                    ->orWhere('imo', 'like', "%{$search}%")
                    ->orWhere('gfw_vessel_id', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['vessel_type'])) {
            $query->where('vessel_type', (string) $filters['vessel_type']);
        }

        if (! empty($filters['flag'])) {
            $query->where('flag', (string) $filters['flag']);
        }

        $sortBy = $filters['sort_by'] ?? 'last_synced_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';
        $allowedSorts = ['name', 'vessel_type', 'flag', 'last_synced_at', 'last_seen_at', 'created_at'];

        if (in_array($sortBy, $allowedSorts, true)) {
            $query->orderBy($sortBy, $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('last_synced_at');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get a single vessel by GFW vessel ID with latest presence summary.
     *
     * @return array{vessel: GfwVessel, latest_presence: GfwVesselPresence|null, presence_count: int}|null
     */
    public function getVessel(string $gfwVesselId): ?array
    {
        $vessel = GfwVessel::where('gfw_vessel_id', $gfwVesselId)->first();

        if (! $vessel) {
            return null;
        }

        $latestPresence = GfwVesselPresence::where('gfw_vessel_id', $gfwVesselId)
            ->orderByDesc('observed_at')
            ->first();

        $presenceCount = GfwVesselPresence::where('gfw_vessel_id', $gfwVesselId)->count();

        return [
            'vessel' => $vessel,
            'latest_presence' => $latestPresence,
            'presence_count' => $presenceCount,
        ];
    }

    /**
     * Get filtered presence records for a specific vessel.
     *
     * @param  array{
     *     start_date?: string,
     *     end_date?: string,
     *     sort_dir?: string
     * }  $filters
     */
    public function getVesselPresence(string $gfwVesselId, array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = GfwVesselPresence::where('gfw_vessel_id', $gfwVesselId);

        if (! empty($filters['start_date'])) {
            $query->where('observed_at', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->where('observed_at', '<=', $filters['end_date'].' 23:59:59');
        }

        $sortDir = ($filters['sort_dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy('observed_at', $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Get aggregated observatory statistics from sistem_gfw.
     *
     * @return array{
     *     total_vessels: int,
     *     total_presence: int,
     *     vessels_by_type: array<string, int>,
     *     vessels_by_flag: array<string, int>,
     *     last_sync: array{status: string, finished_at: string|null}|null,
     *     date_range: array{earliest: string|null, latest: string|null}
     * }
     */
    public function getObservatoryStats(): array
    {
        $totalVessels = GfwVessel::count();
        $totalPresence = GfwVesselPresence::count();

        $vesselsByType = GfwVessel::select('vessel_type', DB::raw('COUNT(*) as count'))
            ->whereNotNull('vessel_type')
            ->groupBy('vessel_type')
            ->orderByDesc('count')
            ->pluck('count', 'vessel_type')
            ->toArray();

        $vesselsByFlag = GfwVessel::select('flag', DB::raw('COUNT(*) as count'))
            ->whereNotNull('flag')
            ->where('flag', '!=', '')
            ->groupBy('flag')
            ->orderByDesc('count')
            ->pluck('count', 'flag')
            ->toArray();

        $lastSync = GfwSyncRun::orderByDesc('finished_at')->first();

        $earliestPresence = GfwVesselPresence::min('observed_at');
        $latestPresence = GfwVesselPresence::max('observed_at');

        return [
            'total_vessels' => $totalVessels,
            'total_presence' => $totalPresence,
            'vessels_by_type' => $vesselsByType,
            'vessels_by_flag' => $vesselsByFlag,
            'last_sync' => $lastSync ? [
                'status' => $lastSync->status,
                'finished_at' => $lastSync->finished_at?->toIso8601String(),
                'records_found' => $lastSync->records_found,
                'records_saved' => $lastSync->records_saved,
            ] : null,
            'date_range' => [
                'earliest' => $earliestPresence,
                'latest' => $latestPresence,
            ],
        ];
    }

    /**
     * Get recent sync run history.
     *
     * @return Collection<int, GfwSyncRun>
     */
    public function getSyncRuns(int $limit = 20): Collection
    {
        return GfwSyncRun::orderByDesc('started_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get current sync status and health.
     *
     * @return array{
     *     last_run: array<string, mixed>|null,
     *     total_runs: int,
     *     successful_runs: int,
     *     failed_runs: int,
     *     health: string
     * }
     */
    public function getSyncStatus(): array
    {
        $lastRun = GfwSyncRun::orderByDesc('finished_at')->first();
        $totalRuns = GfwSyncRun::count();
        $successfulRuns = GfwSyncRun::where('status', 'success')->count();
        $failedRuns = GfwSyncRun::where('status', 'failed')->count();

        $health = 'unknown';
        if ($lastRun) {
            $health = $lastRun->status === 'success' ? 'healthy' : 'degraded';
        }

        return [
            'last_run' => $lastRun ? [
                'id' => $lastRun->id,
                'aoi' => $lastRun->aoi,
                'date_from' => $lastRun->date_from?->toDateString(),
                'date_to' => $lastRun->date_to?->toDateString(),
                'status' => $lastRun->status,
                'records_found' => $lastRun->records_found,
                'records_saved' => $lastRun->records_saved,
                'started_at' => $lastRun->started_at?->toIso8601String(),
                'finished_at' => $lastRun->finished_at?->toIso8601String(),
            ] : null,
            'total_runs' => $totalRuns,
            'successful_runs' => $successfulRuns,
            'failed_runs' => $failedRuns,
            'health' => $health,
        ];
    }
}
