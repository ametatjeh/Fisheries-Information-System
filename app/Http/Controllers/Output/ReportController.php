<?php

namespace App\Http\Controllers\Output;

use App\Http\Controllers\Controller;
use App\Models\BiologicalMeasurement;
use App\Models\CatchEstimation;
use App\Models\FishCatch;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingSite;
use App\Models\MonthlyProductionStatistic;
use App\Models\Species;
use App\Models\Wppnri;
use App\Services\AdvancedStatisticService;
use App\Services\CatchEstimationService;
use App\Services\MonthlyProductionService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected MonthlyProductionService $monthlyProductionService,
        protected ?AdvancedStatisticService $statisticService = null,
        protected ?CatchEstimationService $estimationService = null
    ) {
        $this->statisticService = $statisticService ?? app(AdvancedStatisticService::class);
        $this->estimationService = $estimationService ?? app(CatchEstimationService::class);
    }

    /**
     * Tampilkan pusat laporan perikanan dengan filter dan tab jenis laporan.
     */
    public function index(Request $request): View
    {
        $type = $request->query('type', 'catches');
        $filters = $this->extractFilters($request);

        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $gearsList = FishingGear::where('is_active', true)->orderBy('name')->get();
        $wppList = Wppnri::orderBy('name')->get();

        // Get only species that are used in transactions
        $speciesIds = array_filter(array_unique(array_merge(
            FishCatch::distinct()->pluck('fish_species_id')->toArray(),
            MonthlyProductionStatistic::distinct()->pluck('fish_species_id')->toArray(),
            BiologicalMeasurement::distinct()->pluck('fish_species_id')->toArray()
        )));

        $speciesList = Species::whereIn('id', $speciesIds)->orderBy('local_name_id')->get();

        $reportData = $this->getReportData($type, $filters, paginate: true);

        return view('reports.index', array_merge([
            'type' => $type,
            'filters' => $filters,
            'landingSites' => $landingSites,
            'gearsList' => $gearsList,
            'speciesList' => $speciesList,
            'wppList' => $wppList,
        ], $reportData));
    }

    /**
     * Ekspor data laporan terpilih ke format CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $type = $request->query('type', 'catches');
        $filters = $this->extractFilters($request);
        $reportData = $this->getReportData($type, $filters, paginate: false);

        $filename = sprintf('laporan_%s_%s.csv', $type, now()->format('Ymd_His'));

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($type, $reportData) {
            $handle = fopen('php://output', 'w');
            // Tambahkan UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            $this->writeCsvContent($handle, $type, $reportData['records']);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Tampilan pratinjau cetak resmi (Print / PDF View) untuk dokumen dinas.
     */
    public function print(Request $request): View
    {
        $type = $request->query('type', 'catches');
        $filters = $this->extractFilters($request);
        $reportData = $this->getReportData($type, $filters, paginate: false, limit: 150);

        $selectedLandingSite = $filters['landing_site_id'] ? LandingSite::find($filters['landing_site_id']) : null;
        $selectedSpecies = $filters['species_id'] ? Species::find($filters['species_id']) : null;
        $selectedGear = $filters['gear_id'] ? FishingGear::find($filters['gear_id']) : null;
        $selectedWpp = $filters['wppnri_id'] ? Wppnri::find($filters['wppnri_id']) : null;

        return view('reports.print', array_merge([
            'type' => $type,
            'filters' => $filters,
            'selectedLandingSite' => $selectedLandingSite,
            'selectedSpecies' => $selectedSpecies,
            'selectedGear' => $selectedGear,
            'selectedWpp' => $selectedWpp,
            'printedAt' => Carbon::now()->isoFormat('D MMMM Y, HH:mm'),
        ], $reportData));
    }

    /**
     * Ekstrak parameter filter dari request.
     *
     * @return array<string, mixed>
     */
    protected function extractFilters(Request $request): array
    {
        return [
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'year' => (int) $request->query('year', 2026),
            'month' => $request->query('month') ? (int) $request->query('month') : null,
            'landing_site_id' => $request->query('landing_site_id'),
            'gear_id' => $request->query('gear_id'),
            'species_id' => $request->query('species_id'),
            'wppnri_id' => $request->query('wppnri_id') ? (int) $request->query('wppnri_id') : null,
        ];
    }

    /**
     * Ambil data dan agregat ringkasan berdasarkan tipe laporan.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getReportData(string $type, array $filters, bool $paginate = true, int $limit = 25): array
    {
        return match ($type) {
            'efforts' => $this->getEffortsReport($filters, $paginate, $limit),
            'landings' => $this->getLandingsReport($filters, $paginate, $limit),
            'sampling' => $this->getSamplingReport($filters, $paginate, $limit),
            'monthly' => $this->getMonthlyReport($filters, $paginate, $limit),
            'production' => $this->getProductionReport($filters, $paginate, $limit),
            'statistics' => $this->getStatisticsReport($filters),
            'summary' => $this->getSummaryReport($filters),
            default => $this->getCatchesReport($filters, $paginate, $limit),
        };
    }

    /**
     * 1. Laporan Hasil Tangkapan (Catches & Species)
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getCatchesReport(array $filters, bool $paginate, int $limit): array
    {
        $query = FishCatch::with([
            'species',
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'fishingTrip.landingSite',
            'fishingTrip.primaryGear',
            'fishingTrip.wppnri',
        ]);

        if (! empty($filters['species_id'])) {
            $query->where('fish_species_id', $filters['species_id']);
        }

        if (! empty($filters['landing_site_id']) || ! empty($filters['gear_id']) || ! empty($filters['wppnri_id']) || ! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $query->whereHas('fishingTrip', function ($t) use ($filters) {
                if (! empty($filters['landing_site_id'])) {
                    $t->where('landing_site_id', $filters['landing_site_id']);
                }
                if (! empty($filters['gear_id'])) {
                    $t->where('primary_gear_id', $filters['gear_id']);
                }
                if (! empty($filters['wppnri_id'])) {
                    $t->where('wppnri_id', $filters['wppnri_id']);
                }
                if (! empty($filters['start_date'])) {
                    $t->whereDate('departure_date', '>=', $filters['start_date']);
                }
                if (! empty($filters['end_date'])) {
                    $t->whereDate('departure_date', '<=', $filters['end_date']);
                }
            });
        }

        // Hitung agregat
        $totalWeightKg = (float) (clone $query)->sum('weight_kg');
        $totalFishCount = (int) (clone $query)->sum('fish_count');
        $totalRecords = (int) (clone $query)->count();

        $query->orderByDesc('id');

        $records = $paginate ? $query->paginate($limit)->withQueryString() : $query->limit(5000)->get();

        return [
            'records' => $records,
            'totalWeightKg' => $totalWeightKg,
            'totalWeightTon' => round($totalWeightKg / 1000, 2),
            'totalFishCount' => $totalFishCount,
            'totalRecords' => $totalRecords,
        ];
    }

    /**
     * 2. Laporan Upaya Tangkap & Trip (Efforts & Trips)
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getEffortsReport(array $filters, bool $paginate, int $limit): array
    {
        $query = FishingEffort::with([
            'fishingGear',
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'fishingTrip.landingSite',
            'fishingTrip.wppnri',
        ]);

        if (! empty($filters['gear_id'])) {
            $query->where('fishing_gear_id', $filters['gear_id']);
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('setting_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('setting_date', '<=', $filters['end_date']);
        }

        if (! empty($filters['landing_site_id']) || ! empty($filters['wppnri_id'])) {
            $query->whereHas('fishingTrip', function ($t) use ($filters) {
                if (! empty($filters['landing_site_id'])) {
                    $t->where('landing_site_id', $filters['landing_site_id']);
                }
                if (! empty($filters['wppnri_id'])) {
                    $t->where('wppnri_id', $filters['wppnri_id']);
                }
            });
        }

        $totalSettings = (int) (clone $query)->sum('setting_count') ?: (clone $query)->count();
        $totalDurationHours = (float) (clone $query)->sum('duration_hours');
        $totalRecords = (int) (clone $query)->count();
        $avgDurationHours = $totalRecords > 0 ? round($totalDurationHours / $totalRecords, 1) : 0;

        $query->orderByDesc('setting_date')->orderByDesc('id');

        $records = $paginate ? $query->paginate($limit)->withQueryString() : $query->limit(5000)->get();

        return [
            'records' => $records,
            'totalSettings' => $totalSettings,
            'totalDurationHours' => $totalDurationHours,
            'avgDurationHours' => $avgDurationHours,
            'totalRecords' => $totalRecords,
        ];
    }

    /**
     * 3. Laporan Pendaratan Ikan & Nilai Omzet Lelang (Landings & Revenue)
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getLandingsReport(array $filters, bool $paginate, int $limit): array
    {
        $query = Landing::with([
            'landingSite',
            'fishingTrip.vessel',
            'fishingTrip.wppnri',
            'recordedBy',
            'items.species',
        ]);

        if (! empty($filters['landing_site_id'])) {
            $query->where('landing_site_id', $filters['landing_site_id']);
        }
        if (! empty($filters['wppnri_id'])) {
            $query->whereHas('fishingTrip', function ($t) use ($filters) {
                $t->where('wppnri_id', $filters['wppnri_id']);
            });
        }
        if (! empty($filters['start_date'])) {
            $query->whereDate('landing_date', '>=', $filters['start_date']);
        }
        if (! empty($filters['end_date'])) {
            $query->whereDate('landing_date', '<=', $filters['end_date']);
        }

        $totalWeightKg = (float) (clone $query)->sum('total_weight_kg');
        $totalValueRp = (float) (clone $query)->sum('total_value_rp');
        $totalRecords = (int) (clone $query)->count();
        $avgPricePerKg = $totalWeightKg > 0 ? round($totalValueRp / $totalWeightKg, 0) : 0;

        $query->orderByDesc('landing_date')->orderByDesc('id');

        $records = $paginate ? $query->paginate($limit)->withQueryString() : $query->limit(5000)->get();

        return [
            'records' => $records,
            'totalWeightKg' => $totalWeightKg,
            'totalWeightTon' => round($totalWeightKg / 1000, 2),
            'totalValueRp' => $totalValueRp,
            'avgPricePerKg' => $avgPricePerKg,
            'totalRecords' => $totalRecords,
        ];
    }

    /**
     * 4. Laporan Biologi & Pengukuran Morfometrik Ikan (Sampling & Biology)
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getSamplingReport(array $filters, bool $paginate, int $limit): array
    {
        $query = BiologicalMeasurement::with([
            'sample.landingSite',
            'sample.fishingTrip',
            'fishSpecies',
        ]);

        if (! empty($filters['species_id'])) {
            $query->where('fish_species_id', $filters['species_id']);
        }

        if (! empty($filters['landing_site_id']) || ! empty($filters['start_date']) || ! empty($filters['end_date'])) {
            $query->whereHas('sample', function ($s) use ($filters) {
                if (! empty($filters['landing_site_id'])) {
                    $s->where('landing_site_id', $filters['landing_site_id']);
                }
                if (! empty($filters['start_date'])) {
                    $s->whereDate('sample_date', '>=', $filters['start_date']);
                }
                if (! empty($filters['end_date'])) {
                    $s->whereDate('sample_date', '<=', $filters['end_date']);
                }
            });
        }

        $totalSpecimens = (int) (clone $query)->count();
        $avgTotalLengthCm = $totalSpecimens > 0 ? round((float) (clone $query)->avg('total_length_cm'), 1) : 0;
        $avgWeightGram = $totalSpecimens > 0 ? round((float) (clone $query)->avg('weight_gram'), 1) : 0;
        $maxTotalLengthCm = (float) (clone $query)->max('total_length_cm');
        $minTotalLengthCm = (float) (clone $query)->min('total_length_cm');

        $query->orderByDesc('id');

        $records = $paginate ? $query->paginate($limit)->withQueryString() : $query->limit(5000)->get();

        return [
            'records' => $records,
            'totalSpecimens' => $totalSpecimens,
            'avgTotalLengthCm' => $avgTotalLengthCm,
            'avgWeightGram' => $avgWeightGram,
            'maxTotalLengthCm' => $maxTotalLengthCm,
            'minTotalLengthCm' => $minTotalLengthCm,
            'totalRecords' => $totalSpecimens,
        ];
    }

    /**
     * 5. Laporan Rekapitulasi Produksi Bulanan (Monthly Production Report)
     * Menggunakan MonthlyProductionService untuk kalkulasi yang aman dari duplikasi join
     * dan mendukung rekonsiliasi statistik lintas metrik.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getMonthlyReport(array $filters, bool $paginate, int $limit): array
    {
        return $this->monthlyProductionService->getMonthlyProductionReport($filters, $paginate, $limit);
    }

    /**
     * 6. Laporan Produksi Terpadu (Production Report)
     * Menggabungkan Observed Catch, Landed Production, Estimated Production, dan Total Production.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getProductionReport(array $filters, bool $paginate, int $limit): array
    {
        $monthlyReport = $this->monthlyProductionService->getMonthlyProductionReport($filters, $paginate, $limit);
        $summary = $this->monthlyProductionService->getProductionSummary($filters);

        return array_merge($monthlyReport, [
            'summary' => $summary,
            'totalObservedKg' => $summary['metrics']['total_observed_catch_kg'] ?? 0,
            'totalLandedKg' => $summary['metrics']['total_landing_kg'] ?? 0,
            'totalEstimatedKg' => $summary['metrics']['total_estimated_production_kg'] ?? 0,
            'totalProductionKg' => ($summary['metrics']['total_landing_kg'] ?? 0) > 0
                ? $summary['metrics']['total_landing_kg']
                : ($summary['metrics']['total_observed_catch_kg'] ?? 0),
        ]);
    }

    /**
     * 7. Laporan Statistik Perikanan (Fisheries Statistics Report)
     * Menampilkan Effort, Catch, CPUE, Sampling, Estimasi, dan Status Validasi.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getStatisticsReport(array $filters): array
    {
        $normalized = $this->statisticService->normalizeFilters($filters);
        $act = $this->statisticService->getActivityKpi($normalized);
        $cat = $this->statisticService->getCatchKpi($normalized);
        $gearComparison = $this->statisticService->getCatchByGear($normalized);

        $tripQuery = FishingTrip::query();
        if (! empty($normalized['year'])) {
            $tripQuery->whereYear('departure_date', $normalized['year']);
        }
        if (! empty($normalized['month'])) {
            $tripQuery->whereMonth('departure_date', $normalized['month']);
        }
        if (! empty($normalized['landing_site_id'])) {
            $tripQuery->where('landing_site_id', $normalized['landing_site_id']);
        }

        $validationCounts = [
            'total' => (clone $tripQuery)->count(),
            'validated' => (clone $tripQuery)->where('validation_status', 'validated')->count(),
            'submitted' => (clone $tripQuery)->where('validation_status', 'submitted')->count(),
            'draft' => (clone $tripQuery)->where('validation_status', 'draft')->count(),
            'rejected' => (clone $tripQuery)->where('validation_status', 'rejected')->count(),
        ];

        $samplingCount = BiologicalMeasurement::count();
        $estimationCount = CatchEstimation::count();

        $gearRows = collect($gearComparison['items'] ?? [])->map(function ($gear, $index) {
            return (object) [
                'index' => $index + 1,
                'gear_name' => $gear['gear_name'],
                'total_catch_kg' => $gear['catch_kg'],
                'total_effort_hours' => $gear['effort_hours'],
                'cpue' => $gear['cpue'],
                'trips_count' => $gear['trips_count'],
            ];
        });

        return [
            'records' => $gearRows,
            'gearStats' => $gearRows,
            'validationCounts' => $validationCounts,
            'samplingCount' => $samplingCount,
            'estimationCount' => $estimationCount,
            'act' => $act,
            'cat' => $cat,
            'totalTrips' => $act['total_trips']['value'],
            'totalEffortHours' => $act['total_effort_hours']['value'],
            'totalCatchKg' => $cat['total_catch_physical']['value'],
            'totalCatchTon' => $cat['total_catch_physical']['ton'],
            'cpuePerHour' => $cat['cpue']['value'],
            'totalRecords' => $gearRows->count(),
        ];
    }

    /**
     * 8. Laporan Ringkasan Eksekutif (Summary Report)
     * KPI Utama: total trips, total effort, total catch, total landing, CPUE, jumlah spesies, jumlah kapal, jumlah pelabuhan.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    protected function getSummaryReport(array $filters): array
    {
        $normalized = $this->statisticService->normalizeFilters($filters);
        $act = $this->statisticService->getActivityKpi($normalized);
        $cat = $this->statisticService->getCatchKpi($normalized);

        $speciesCount = Species::whereHas('catches', function ($c) use ($normalized) {
            if (! empty($normalized['year'])) {
                $c->whereYear('created_at', $normalized['year']);
            }
        })->count();

        $landingSitesCount = LandingSite::whereHas('landings', function ($l) use ($normalized) {
            if (! empty($normalized['year'])) {
                $l->whereYear('landing_date', $normalized['year']);
            }
            if (! empty($normalized['month'])) {
                $l->whereMonth('landing_date', $normalized['month']);
            }
        })->count() ?: LandingSite::where('is_active', true)->count();

        $summaryKpis = [
            'total_trips' => $act['total_trips']['value'],
            'total_effort_hours' => $act['total_effort_hours']['value'],
            'total_settings' => $act['fishing_efforts']['value'],
            'total_catch_kg' => $cat['total_catch_physical']['value'],
            'total_catch_ton' => $cat['total_catch_physical']['ton'],
            'total_landing_kg' => $cat['total_landing']['value'],
            'total_landing_ton' => $cat['total_landing']['ton'],
            'total_landing_value' => $cat['total_landing_value']['value'],
            'cpue_kg_hour' => $cat['cpue']['value'],
            'active_vessels' => $act['active_vessels']['value'],
            'active_species' => $speciesCount ?: Species::where('is_active', true)->count(),
            'active_landing_sites' => $landingSitesCount,
        ];

        $kpiRows = collect([
            (object) ['indicator' => 'Total Fishing Trips', 'value' => number_format($summaryKpis['total_trips']), 'unit' => 'Trip'],
            (object) ['indicator' => 'Total Upaya Penangkapan (Effort Hours)', 'value' => number_format($summaryKpis['total_effort_hours'], 1), 'unit' => 'Jam'],
            (object) ['indicator' => 'Total Siklus Setting Alat Tangkap', 'value' => number_format($summaryKpis['total_settings']), 'unit' => 'Kali'],
            (object) ['indicator' => 'Total Hasil Tangkapan Teramati (Catch)', 'value' => number_format($summaryKpis['total_catch_kg'], 1), 'unit' => 'kg'],
            (object) ['indicator' => 'Total Volume Pendaratan TPI (Landing)', 'value' => number_format($summaryKpis['total_landing_kg'], 1), 'unit' => 'kg'],
            (object) ['indicator' => 'Total Nilai Transaksi Pelelangan', 'value' => 'Rp '.number_format($summaryKpis['total_landing_value']), 'unit' => 'Rupiah'],
            (object) ['indicator' => 'Catch Per Unit Effort (CPUE)', 'value' => number_format($summaryKpis['cpue_kg_hour'], 2), 'unit' => 'kg/jam'],
            (object) ['indicator' => 'Jumlah Kapal Aktif', 'value' => number_format($summaryKpis['active_vessels']), 'unit' => 'Kapal'],
            (object) ['indicator' => 'Jumlah Spesies Teridentifikasi', 'value' => number_format($summaryKpis['active_species']), 'unit' => 'Spesies'],
            (object) ['indicator' => 'Jumlah Pelabuhan / TPI Aktif', 'value' => number_format($summaryKpis['active_landing_sites']), 'unit' => 'Pangkalan'],
        ]);

        return [
            'records' => $kpiRows,
            'summaryKpis' => $summaryKpis,
            'totalTrips' => $summaryKpis['total_trips'],
            'totalEffortHours' => $summaryKpis['total_effort_hours'],
            'totalCatchKg' => $summaryKpis['total_catch_kg'],
            'totalLandingKg' => $summaryKpis['total_landing_kg'],
            'cpuePerHour' => $summaryKpis['cpue_kg_hour'],
            'totalRecords' => $kpiRows->count(),
        ];
    }

    /**
     * Tulis baris CSV sesuai dengan tipe laporan.
     *
     * @param  resource  $handle
     * @param  Collection<int, mixed>  $records
     */
    protected function writeCsvContent($handle, string $type, $records): void
    {
        switch ($type) {
            case 'efforts':
                fputcsv($handle, [
                    'No',
                    'Kode Trip',
                    'Nama Kapal',
                    'Nahkoda',
                    'Pelabuhan Pangkalan',
                    'Alat Tangkap',
                    'Setting Ke',
                    'Tgl Setting',
                    'Tgl Hauling',
                    'Durasi (Jam)',
                    'Jumlah Setting',
                    'Jumlah Mata Pancing',
                    'Panjang Jaring (m)',
                    'Koordinat Setting',
                    'Koordinat Hauling',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->fishingTrip?->trip_number ?? '-',
                        $row->fishingTrip?->vessel?->name ?? '-',
                        $row->fishingTrip?->captain?->name ?? '-',
                        $row->fishingTrip?->landingSite?->name ?? '-',
                        $row->fishingGear?->name ?? '-',
                        $row->setting_number ?? 1,
                        $row->setting_date ? $row->setting_date->format('Y-m-d H:i') : '-',
                        $row->hauling_date ? $row->hauling_date->format('Y-m-d H:i') : '-',
                        $row->duration_hours ?? 0,
                        $row->setting_count ?? 1,
                        $row->hook_count ?? '-',
                        $row->net_length_meters ?? '-',
                        $row->latitude_setting ? "{$row->latitude_setting}, {$row->longitude_setting}" : '-',
                        $row->latitude_hauling ? "{$row->latitude_hauling}, {$row->longitude_hauling}" : '-',
                    ]);
                }
                break;

            case 'landings':
                fputcsv($handle, [
                    'No',
                    'Nomor Pendaratan',
                    'Tanggal Pendaratan',
                    'Pelabuhan / TPI',
                    'Nama Kapal Asal',
                    'Kode Trip',
                    'Total Berat (kg)',
                    'Total Nilai Omzet (Rp)',
                    'Jumlah Pembeli/Bakul',
                    'Petugas Pencatat',
                    'Catatan',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->landing_number ?? '-',
                        $row->landing_date ? $row->landing_date->format('Y-m-d') : '-',
                        $row->landingSite?->name ?? '-',
                        $row->fishingTrip?->vessel?->name ?? '-',
                        $row->fishingTrip?->trip_number ?? '-',
                        $row->total_weight_kg ?? 0,
                        $row->total_value_rp ?? 0,
                        $row->buyer_count ?? 0,
                        $row->recordedBy?->name ?? '-',
                        $row->notes ?? '-',
                    ]);
                }
                break;

            case 'sampling':
                fputcsv($handle, [
                    'No',
                    'Kode Sampel',
                    'Tanggal Sampling',
                    'Lokasi / TPI',
                    'Jenis Ikan (Lokal)',
                    'Nama Ilmiah',
                    'Nomor Spesimen',
                    'Panjang Total (cm)',
                    'Panjang Cagak (cm)',
                    'Panjang Baku (cm)',
                    'Bobot (gram)',
                    'Jenis Kelamin',
                    'Kematangan Gonad (TKG)',
                    'Kepenuhan Lambung',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->sample?->sample_code ?? '-',
                        $row->sample?->sample_date ? $row->sample->sample_date->format('Y-m-d') : '-',
                        $row->sample?->landingSite?->name ?? '-',
                        $row->fishSpecies?->local_name_id ?? '-',
                        $row->fishSpecies?->scientific_name ?? '-',
                        $row->specimen_number ?? '-',
                        $row->total_length_cm ?? '-',
                        $row->fork_length_cm ?? '-',
                        $row->standard_length_cm ?? '-',
                        $row->weight_gram ?? '-',
                        $row->sex ?? '-',
                        $row->gonad_maturity_stage ?? '-',
                        $row->stomach_fullness ?? '-',
                    ]);
                }
                break;

            case 'monthly':
                fputcsv($handle, [
                    'No',
                    'Tahun',
                    'Bulan',
                    'Kabupaten / Kota',
                    'Pelabuhan / TPI',
                    'WPP',
                    'Alat Tangkap',
                    'Jenis Ikan',
                    'Nama Ilmiah',
                    'Volume Produksi (kg)',
                    'Nilai Produksi (Rp)',
                    'Harga Rata-Rata (Rp/kg)',
                    'Status Validasi',
                ]);

                foreach ($records as $index => $row) {
                    $vol = (float) ($row->volume_kg ?? $row->total_volume_kg ?? 0);
                    $val = (float) ($row->value_rp ?? $row->total_value_rp ?? 0);
                    $avgPrice = $vol > 0 ? round($val / $vol, 2) : 0;

                    fputcsv($handle, [
                        $index + 1,
                        $row->year,
                        $row->month,
                        $row->regency_name ?? $row->regency?->name ?? '-',
                        $row->landing_site_name ?? $row->landingSite?->name ?? '-',
                        $row->wpp_name ?? '-',
                        $row->gear_name ?? $row->fishingGear?->name ?? '-',
                        $row->species_name ?? $row->fishSpecies?->local_name_id ?? '-',
                        $row->scientific_name ?? $row->fishSpecies?->scientific_name ?? '-',
                        $vol,
                        $val,
                        $avgPrice,
                        $row->status ?? 'validated',
                    ]);
                }
                break;

            case 'production':
                fputcsv($handle, [
                    'No',
                    'Tahun',
                    'Bulan',
                    'Kabupaten / Kota',
                    'Pelabuhan / TPI',
                    'WPP',
                    'Alat Tangkap',
                    'Jenis Ikan',
                    'Nama Ilmiah',
                    'Volume Produksi (kg)',
                    'Nilai Produksi (Rp)',
                    'Status Validasi',
                ]);

                foreach ($records as $index => $row) {
                    $vol = (float) ($row->volume_kg ?? $row->total_volume_kg ?? 0);
                    $val = (float) ($row->value_rp ?? $row->total_value_rp ?? 0);

                    fputcsv($handle, [
                        $index + 1,
                        $row->year,
                        $row->month,
                        $row->regency_name ?? $row->regency?->name ?? '-',
                        $row->landing_site_name ?? $row->landingSite?->name ?? '-',
                        $row->wpp_name ?? '-',
                        $row->gear_name ?? $row->fishingGear?->name ?? '-',
                        $row->species_name ?? $row->fishSpecies?->local_name_id ?? '-',
                        $row->scientific_name ?? $row->fishSpecies?->scientific_name ?? '-',
                        $vol,
                        $val,
                        $row->status ?? 'validated',
                    ]);
                }
                break;

            case 'statistics':
                fputcsv($handle, [
                    'No',
                    'Alat Tangkap',
                    'Total Tangkapan (kg)',
                    'Total Upaya (Jam)',
                    'CPUE (kg/jam)',
                    'Jumlah Trip Teramati',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->gear_name ?? '-',
                        $row->total_catch_kg ?? 0,
                        $row->total_effort_hours ?? 0,
                        $row->cpue ?? 0,
                        $row->trips_count ?? 0,
                    ]);
                }
                break;

            case 'summary':
                fputcsv($handle, [
                    'No',
                    'Indikator Utama',
                    'Nilai',
                    'Satuan',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->indicator ?? '-',
                        $row->value ?? '-',
                        $row->unit ?? '-',
                    ]);
                }
                break;

            default: // catches
                fputcsv($handle, [
                    'No',
                    'Kode Trip',
                    'Tanggal Keberangkatan',
                    'Nama Kapal',
                    'Nahkoda',
                    'Pelabuhan Pendaratan',
                    'Alat Tangkap',
                    'Nama Jenis Ikan',
                    'Nama Ilmiah',
                    'Berat Tangkapan (kg)',
                    'Jumlah Ekor',
                    'Kondisi Mutu',
                    'Catatan',
                ]);

                foreach ($records as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->fishingTrip?->trip_number ?? '-',
                        $row->fishingTrip?->departure_date ? $row->fishingTrip->departure_date->format('Y-m-d') : '-',
                        $row->fishingTrip?->vessel?->name ?? '-',
                        $row->fishingTrip?->captain?->name ?? '-',
                        $row->fishingTrip?->landingSite?->name ?? '-',
                        $row->fishingTrip?->primaryGear?->name ?? '-',
                        $row->species?->local_name_id ?? '-',
                        $row->species?->scientific_name ?? '-',
                        $row->weight_kg ?? 0,
                        $row->fish_count ?? '-',
                        $row->catch_status ?? '-',
                        $row->notes ?? '-',
                    ]);
                }
                break;
        }
    }
}
