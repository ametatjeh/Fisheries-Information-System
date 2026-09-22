<?php

namespace App\Services;

use App\Models\CatchEstimation;
use App\Models\FishingTrip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CatchEstimationService
{
    /**
     * Hitung estimasi produksi tangkapan berdasarkan data lapangan (Observed Catch & Effort)
     * menggunakan metodologi Stratified Sampling & Raising Factor standar FAO/KKP.
     */
    public function calculateEstimation(
        int $year,
        ?int $month = null,
        ?int $landingSiteId = null,
        ?int $gearId = null,
        ?int $speciesId = null
    ): Collection {
        // 1. Ekstraksi data tangkapan tersampel (Observed/Sampled Catch) per stratum
        $strataQuery = DB::table('catches')
            ->join('fishing_efforts', 'catches.fishing_effort_id', '=', 'fishing_efforts.id')
            ->join('fishing_trips', 'fishing_efforts.fishing_trip_id', '=', 'fishing_trips.id')
            ->leftJoin('landing_sites', function ($join) {
                $join->on('fishing_trips.landing_site_id', '=', 'landing_sites.id')
                    ->orOn('fishing_trips.departure_site_id', '=', 'landing_sites.id');
            })
            ->whereYear('fishing_trips.departure_date', $year);

        if ($month) {
            $strataQuery->whereMonth('fishing_trips.departure_date', $month);
        }
        if ($landingSiteId) {
            $strataQuery->where('landing_sites.id', $landingSiteId);
        }
        if ($gearId) {
            $strataQuery->where('fishing_efforts.fishing_gear_id', $gearId);
        }
        if ($speciesId) {
            $strataQuery->where('catches.fish_species_id', $speciesId);
        }

        $driver = DB::connection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "cast(strftime('%Y', fishing_trips.departure_date) as integer)" : 'YEAR(fishing_trips.departure_date)';
        $monthExpr = $driver === 'sqlite' ? "cast(strftime('%m', fishing_trips.departure_date) as integer)" : 'MONTH(fishing_trips.departure_date)';
        $varianceExpr = $driver === 'sqlite' ? '0' : 'VARIANCE(catches.weight_kg)';

        $strataResults = $strataQuery->select([
            DB::raw("{$yearExpr} as year"),
            DB::raw("{$monthExpr} as month"),
            'landing_sites.id as landing_site_id',
            'landing_sites.regency_id',
            'fishing_efforts.fishing_gear_id',
            'catches.fish_species_id',
            DB::raw('SUM(catches.weight_kg) as sampled_catch_kg'),
            DB::raw('COUNT(DISTINCT fishing_trips.id) as sampled_trips'),
            DB::raw('SUM(fishing_efforts.duration_hours) as sampled_effort_hours'),
            DB::raw("{$varianceExpr} as catch_variance"),
        ])
            ->groupBy([
                DB::raw($yearExpr),
                DB::raw($monthExpr),
                'landing_sites.id',
                'landing_sites.regency_id',
                'fishing_efforts.fishing_gear_id',
                'catches.fish_species_id',
            ])
            ->get();

        $estimations = collect();

        foreach ($strataResults as $row) {
            // 2. Hitung Total Populasi Trip (Frame Effort N) pada stratum pangkalan & alat tangkap yang sama
            $populationTrips = FishingTrip::query()
                ->whereYear('departure_date', $row->year)
                ->whereMonth('departure_date', $row->month)
                ->where(function ($q) use ($row) {
                    if ($row->landing_site_id) {
                        $q->where('landing_site_id', $row->landing_site_id)
                            ->orWhere('departure_site_id', $row->landing_site_id);
                    }
                })
                ->whereHas('fishingEfforts', function ($e) use ($row) {
                    if ($row->fishing_gear_id) {
                        $e->where('fishing_gear_id', $row->fishing_gear_id);
                    }
                })
                ->distinct('id')
                ->count('id');

            // Jika populasi trip belum tercatat lebih tinggi, gunakan minimal trip tersampel
            $n = (int) $row->sampled_trips;
            $N = max($populationTrips, $n);

            // 3. Hitung Raising Factor (R = N / n)
            $raisingFactor = $n > 0 ? round($N / $n, 4) : 1.0000;
            if ($raisingFactor < 1.0000) {
                $raisingFactor = 1.0000;
            }

            // 4. Hitung Estimated Catch (Y_hat = Observed Catch * R)
            $sampledCatch = (float) $row->sampled_catch_kg;
            $estimatedCatch = round($sampledCatch * $raisingFactor, 2);

            // 5. Hitung CPUE Estimasi (kg per trip)
            $cpue = $N > 0 ? round($estimatedCatch / $N, 4) : 0.0;

            // 6. Variansi
            $variance = $row->catch_variance ? round((float) $row->catch_variance, 4) : null;

            $estimations->push([
                'year' => (int) $row->year,
                'month' => (int) $row->month,
                'regency_id' => $row->regency_id,
                'landing_site_id' => $row->landing_site_id,
                'fishing_gear_id' => $row->fishing_gear_id,
                'fish_species_id' => $row->fish_species_id,
                'sampled_catch_kg' => $sampledCatch,
                'sampled_trips' => $n,
                'population_trips' => $N,
                'raising_factor' => $raisingFactor,
                'estimated_catch_kg' => $estimatedCatch,
                'estimated_effort_trips' => $N,
                'cpue' => $cpue,
                'variance' => $variance,
                'coverage_ratio' => $N > 0 ? round(($n / $N) * 100, 1) : 100.0,
            ]);
        }

        return $estimations;
    }

    /**
     * Simpan hasil kalkulasi ke dalam tabel penyimpanan hasil estimasi (catch_estimations).
     */
    public function storeEstimation(array $attributes, string $status = 'draft'): CatchEstimation
    {
        $this->validateEstimationData($attributes);

        $notes = $attributes['notes'] ?? '';
        $cleanNotes = preg_replace('/\[STATUS:(draft|validated|rejected)\]/', '', $notes);
        $cleanNotes = trim($cleanNotes);
        $statusTag = "[STATUS:{$status}]";
        $finalNotes = $cleanNotes ? "{$cleanNotes} {$statusTag}" : $statusTag;

        return CatchEstimation::updateOrCreate(
            [
                'regency_id' => $attributes['regency_id'],
                'landing_site_id' => $attributes['landing_site_id'] ?? null,
                'fish_species_id' => $attributes['fish_species_id'] ?? null,
                'fishing_gear_id' => $attributes['fishing_gear_id'] ?? null,
                'year' => $attributes['year'],
                'month' => $attributes['month'],
            ],
            [
                'sampled_catch_kg' => $attributes['sampled_catch_kg'],
                'raising_factor' => $attributes['raising_factor'],
                'estimated_catch_kg' => $attributes['estimated_catch_kg'],
                'estimated_effort_trips' => $attributes['estimated_effort_trips'] ?? 0,
                'cpue' => $attributes['cpue'] ?? null,
                'variance' => $attributes['variance'] ?? null,
                'notes' => $finalNotes,
            ]
        );
    }

    /**
     * Alur kerja validasi estimasi (Draft -> Validated -> Rejected).
     */
    public function updateValidationStatus(
        CatchEstimation $estimation,
        string $newStatus,
        ?string $reason = null
    ): CatchEstimation {
        if (! in_array($newStatus, ['draft', 'validated', 'rejected'], true)) {
            throw new InvalidArgumentException("Status validasi tidak sah: {$newStatus}");
        }

        // Jalankan uji kepatuhan data sebelum validasi disetujui
        if ($newStatus === 'validated') {
            $this->validateEstimationCompliance($estimation);
        }

        $notes = $estimation->notes ?? '';
        $cleanNotes = preg_replace('/\[STATUS:(draft|validated|rejected)\]/', '', $notes);
        $cleanNotes = trim($cleanNotes);

        if ($reason) {
            $cleanNotes .= " (Catatan Verifikasi: {$reason})";
        }

        $finalNotes = $cleanNotes ? "{$cleanNotes} [STATUS:{$newStatus}]" : "[STATUS:{$newStatus}]";

        $estimation->update(['notes' => $finalNotes]);

        return $estimation;
    }

    /**
     * Uji kepatuhan data estimasi untuk mencegah data anomali atau tidak wajar.
     *
     * @throws InvalidArgumentException
     */
    protected function validateEstimationCompliance(CatchEstimation $estimation): void
    {
        if ($estimation->sampled_catch_kg < 0 || $estimation->estimated_catch_kg < 0) {
            throw new InvalidArgumentException('Nilai tangkapan tidak boleh negatif.');
        }

        if ($estimation->raising_factor < 1.0000) {
            throw new InvalidArgumentException('Faktor pengali (raising factor) tidak boleh lebih kecil dari 1.0.');
        }

        if ($estimation->raising_factor > 15.0000) {
            throw new InvalidArgumentException('Faktor pengali (raising factor) terlalu tinggi (> 15.0). Diperlukan verifikasi sampel.');
        }

        if (! $estimation->regency_id || ! $estimation->year || ! $estimation->month) {
            throw new InvalidArgumentException('Stratum wilayah, tahun, dan bulan wajib terisi lengkap.');
        }
    }

    /**
     * Validasi input dasar array estimasi.
     *
     * @throws InvalidArgumentException
     */
    protected function validateEstimationData(array $data): void
    {
        if (($data['sampled_catch_kg'] ?? 0) < 0 || ($data['estimated_catch_kg'] ?? 0) < 0) {
            throw new InvalidArgumentException('Nilai tangkapan tidak boleh bernilai negatif.');
        }

        if (($data['raising_factor'] ?? 1.0) < 1.0) {
            throw new InvalidArgumentException('Raising factor tidak boleh lebih kecil dari 1.0.');
        }
    }

    /**
     * Ambil daftar riwayat estimasi dengan paginasi dan filter.
     */
    public function getPaginatedEstimations(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return CatchEstimation::with(['regency', 'landingSite', 'fishSpecies', 'fishingGear'])
            ->when(! empty($filters['year']), fn ($q) => $q->where('year', $filters['year']))
            ->when(! empty($filters['month']), fn ($q) => $q->where('month', $filters['month']))
            ->when(! empty($filters['landing_site_id']), fn ($q) => $q->where('landing_site_id', $filters['landing_site_id']))
            ->when(! empty($filters['fishing_gear_id']), fn ($q) => $q->where('fishing_gear_id', $filters['fishing_gear_id']))
            ->when(! empty($filters['fish_species_id']), fn ($q) => $q->where('fish_species_id', $filters['fish_species_id']))
            ->when(! empty($filters['status']) && $filters['status'] !== 'all', function ($q) use ($filters) {
                $status = $filters['status'];
                $q->where('notes', 'like', "%[STATUS:{$status}]%");
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }
}
