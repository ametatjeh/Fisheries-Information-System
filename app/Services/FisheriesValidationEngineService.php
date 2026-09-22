<?php

namespace App\Services;

use App\Models\FishingTrip;
use App\Models\Landing;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * STAGE 23 — Advanced Fisheries Data Validation Engine
 *
 * Provides comprehensive data quality checks without silently rewriting existing data.
 * Anomalies are categorized into:
 * 1. Referential integrity
 * 2. Temporal consistency
 * 3. Numeric & sanity constraints
 * 4. Statistical consistency
 * 5. Workflow compliance
 */
class FisheriesValidationEngineService
{
    /**
     * Audit a single FishingTrip and all its nested relations (Efforts, Catches, Landings).
     *
     * @return array{
     *     trip_id: int,
     *     trip_number: string,
     *     is_valid: bool,
     *     has_errors: bool,
     *     has_warnings: bool,
     *     error_count: int,
     *     warning_count: int,
     *     issues: array<int, array{category: string, rule: string, field: string, severity: string, message: string}>
     * }
     */
    public function auditTrip(FishingTrip $trip): array
    {
        $trip->loadMissing([
            'vessel',
            'captain',
            'departureSite',
            'landingSite',
            'primaryGear',
            'wppnri',
            'fishingEfforts',
            'catches.species',
            'landings.items.species',
        ]);

        $issues = [];

        // 1. Referential Integrity Checks
        $this->checkReferentialIntegrity($trip, $issues);

        // 2. Temporal Consistency Checks
        $this->checkTemporalConsistency($trip, $issues);

        // 3. Numeric Sanity Checks
        $this->checkNumericConstraints($trip, $issues);

        // 4. Statistical Consistency Checks
        $this->checkStatisticalConsistency($trip, $issues);

        // 5. Workflow State Checks
        $this->checkWorkflowIntegrity($trip, $issues);

        $errorCount = count(array_filter($issues, fn ($i) => $i['severity'] === 'error'));
        $warningCount = count(array_filter($issues, fn ($i) => $i['severity'] === 'warning'));

        return [
            'trip_id' => $trip->id,
            'trip_number' => $trip->trip_number ?? "TRIP-{$trip->id}",
            'is_valid' => $errorCount === 0,
            'has_errors' => $errorCount > 0,
            'has_warnings' => $warningCount > 0,
            'error_count' => $errorCount,
            'warning_count' => $warningCount,
            'issues' => $issues,
        ];
    }

    /**
     * Audit a collection of FishingTrips.
     *
     * @param  Collection<int, FishingTrip>  $trips
     * @return array{
     *     total_trips: int,
     *     valid_trips: int,
     *     flagged_trips: int,
     *     total_errors: int,
     *     total_warnings: int,
     *     reports: array<int, mixed>
     * }
     */
    public function auditTrips(Collection $trips): array
    {
        $reports = [];
        $totalErrors = 0;
        $totalWarnings = 0;
        $validTrips = 0;
        $flaggedTrips = 0;

        foreach ($trips as $trip) {
            $report = $this->auditTrip($trip);
            $totalErrors += $report['error_count'];
            $totalWarnings += $report['warning_count'];

            if ($report['is_valid'] && $report['warning_count'] === 0) {
                $validTrips++;
            } else {
                $flaggedTrips++;
            }

            $reports[] = $report;
        }

        return [
            'total_trips' => $trips->count(),
            'valid_trips' => $validTrips,
            'flagged_trips' => $flaggedTrips,
            'total_errors' => $totalErrors,
            'total_warnings' => $totalWarnings,
            'reports' => $reports,
        ];
    }

    /**
     * Category 1: Referential Integrity.
     */
    protected function checkReferentialIntegrity(FishingTrip $trip, array &$issues): void
    {
        if (empty($trip->vessel_id) || ! $trip->vessel) {
            $issues[] = [
                'category' => 'referential',
                'rule' => 'referential.vessel_exists',
                'field' => 'vessel_id',
                'severity' => 'error',
                'message' => 'Kapal penangkap ikan tidak terdaftar atau tidak ditemukan dalam basis data.',
            ];
        }

        if (empty($trip->landing_site_id) || ! $trip->landingSite) {
            $issues[] = [
                'category' => 'referential',
                'rule' => 'referential.landing_site_exists',
                'field' => 'landing_site_id',
                'severity' => 'error',
                'message' => 'Pelabuhan pendaratan / TPI tidak terdaftar atau tidak ditemukan.',
            ];
        }

        if (! empty($trip->captain_id) && ! $trip->captain) {
            $issues[] = [
                'category' => 'referential',
                'rule' => 'referential.captain_exists',
                'field' => 'captain_id',
                'severity' => 'warning',
                'message' => 'Nahkoda kapal yang direferensikan tidak ditemukan dalam data nelayan.',
            ];
        }

        if (! empty($trip->primary_gear_id) && ! $trip->primaryGear) {
            $issues[] = [
                'category' => 'referential',
                'rule' => 'referential.gear_exists',
                'field' => 'primary_gear_id',
                'severity' => 'warning',
                'message' => 'Alat tangkap utama yang direferensikan tidak terdaftar.',
            ];
        }

        if (! empty($trip->wppnri_id) && ! $trip->wppnri) {
            $issues[] = [
                'category' => 'referential',
                'rule' => 'referential.wpp_exists',
                'field' => 'wppnri_id',
                'severity' => 'warning',
                'message' => 'Wilayah Pengelolaan Perikanan (WPP-NRI) yang direferensikan tidak ditemukan.',
            ];
        }

        // Catches referential checks
        foreach ($trip->catches as $catch) {
            if (empty($catch->fish_species_id) || ! $catch->species) {
                $issues[] = [
                    'category' => 'referential',
                    'rule' => 'referential.catch_species_exists',
                    'field' => "catches[{$catch->id}].fish_species_id",
                    'severity' => 'error',
                    'message' => "Spesies ikan pada tangkapan #{$catch->id} tidak terdaftar.",
                ];
            }
        }

        // Landings referential checks
        foreach ($trip->landings as $landing) {
            foreach ($landing->items as $item) {
                if (empty($item->fish_species_id) || ! $item->species) {
                    $issues[] = [
                        'category' => 'referential',
                        'rule' => 'referential.landing_species_exists',
                        'field' => "landing_items[{$item->id}].fish_species_id",
                        'severity' => 'error',
                        'message' => "Spesies ikan pada pendaratan item #{$item->id} tidak terdaftar.",
                    ];
                }
            }
        }
    }

    /**
     * Category 2: Temporal Consistency.
     */
    protected function checkTemporalConsistency(FishingTrip $trip, array &$issues): void
    {
        $depDate = $trip->departure_date ? Carbon::parse($trip->departure_date) : null;
        $retDate = $trip->return_date ? Carbon::parse($trip->return_date) : null;

        if ($depDate && $retDate) {
            if ($depDate->isAfter($retDate)) {
                $issues[] = [
                    'category' => 'temporal',
                    'rule' => 'temporal.departure_before_return',
                    'field' => 'return_date',
                    'severity' => 'error',
                    'message' => sprintf(
                        'Tanggal kepulangan (%s) mendahului tanggal keberangkatan (%s).',
                        $retDate->format('Y-m-d H:i'),
                        $depDate->format('Y-m-d H:i')
                    ),
                ];
            }

            // Unrealistic trip duration (> 90 days for provincial coastal fishery)
            $durationDays = $depDate->diffInDays($retDate);
            if ($durationDays > 90) {
                $issues[] = [
                    'category' => 'temporal',
                    'rule' => 'temporal.trip_duration_reasonable',
                    'field' => 'return_date',
                    'severity' => 'warning',
                    'message' => "Durasi trip penangkapan ({$durationDays} hari) melebihi batas wajar 90 hari.",
                ];
            }
        }

        // Check effort setting dates against trip window
        foreach ($trip->fishingEfforts as $effort) {
            if ($effort->setting_date && $depDate) {
                $setDate = Carbon::parse($effort->setting_date);
                if ($setDate->lt($depDate->startOfDay())) {
                    $issues[] = [
                        'category' => 'temporal',
                        'rule' => 'temporal.effort_within_trip_window',
                        'field' => "fishing_efforts[{$effort->id}].setting_date",
                        'severity' => 'error',
                        'message' => "Tanggal setting #{$effort->id} ({$setDate->format('Y-m-d')}) sebelum trip berangkat ({$depDate->format('Y-m-d')}).",
                    ];
                }
                if ($retDate && $setDate->gt($retDate->endOfDay())) {
                    $issues[] = [
                        'category' => 'temporal',
                        'rule' => 'temporal.effort_within_trip_window',
                        'field' => "fishing_efforts[{$effort->id}].setting_date",
                        'severity' => 'error',
                        'message' => "Tanggal setting #{$effort->id} ({$setDate->format('Y-m-d')}) setelah trip pulang ({$retDate->format('Y-m-d')}).",
                    ];
                }
            }

            if ($effort->setting_date && $effort->hauling_date) {
                $setDate = Carbon::parse($effort->setting_date);
                $haulDate = Carbon::parse($effort->hauling_date);
                if ($haulDate->lt($setDate)) {
                    $issues[] = [
                        'category' => 'temporal',
                        'rule' => 'temporal.hauling_after_setting',
                        'field' => "fishing_efforts[{$effort->id}].hauling_date",
                        'severity' => 'error',
                        'message' => "Tanggal hauling alat tangkap #{$effort->id} mendahului waktu setting.",
                    ];
                }
            }
        }

        // Check landing date against trip departure
        foreach ($trip->landings as $landing) {
            if ($landing->landing_date && $depDate) {
                $landDate = Carbon::parse($landing->landing_date);
                if ($landDate->lt($depDate->startOfDay())) {
                    $issues[] = [
                        'category' => 'temporal',
                        'rule' => 'temporal.landing_after_departure',
                        'field' => "landings[{$landing->id}].landing_date",
                        'severity' => 'error',
                        'message' => "Tanggal pendaratan ikan ({$landDate->format('Y-m-d')}) mendahului tanggal keberangkatan kapal ({$depDate->format('Y-m-d')}).",
                    ];
                }
            }
        }
    }

    /**
     * Category 3: Numeric Constraints & Physical Sanity.
     */
    protected function checkNumericConstraints(FishingTrip $trip, array &$issues): void
    {
        // Trip-level numeric checks
        if ($trip->crew_count !== null && $trip->crew_count < 0) {
            $issues[] = [
                'category' => 'numeric',
                'rule' => 'numeric.non_negative_crew',
                'field' => 'crew_count',
                'severity' => 'error',
                'message' => 'Jumlah ABK / awak kapal tidak boleh bernilai negatif.',
            ];
        }

        if ($trip->fuel_consumption_liters !== null && (float) $trip->fuel_consumption_liters < 0) {
            $issues[] = [
                'category' => 'numeric',
                'rule' => 'numeric.non_negative_fuel',
                'field' => 'fuel_consumption_liters',
                'severity' => 'error',
                'message' => 'Konsumsi BBM tidak boleh bernilai negatif.',
            ];
        }

        if ($trip->ice_consumption_kg !== null && (float) $trip->ice_consumption_kg < 0) {
            $issues[] = [
                'category' => 'numeric',
                'rule' => 'numeric.non_negative_ice',
                'field' => 'ice_consumption_kg',
                'severity' => 'error',
                'message' => 'Penggunaan es tidak boleh bernilai negatif.',
            ];
        }

        // Effort-level numeric checks
        foreach ($trip->fishingEfforts as $effort) {
            if ($effort->duration_hours !== null && (float) $effort->duration_hours < 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.non_negative_duration',
                    'field' => "fishing_efforts[{$effort->id}].duration_hours",
                    'severity' => 'error',
                    'message' => "Durasi operasi alat tangkap #{$effort->id} tidak boleh negatif.",
                ];
            }

            if ($effort->duration_hours !== null && (float) $effort->duration_hours > 72) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.reasonable_effort_duration',
                    'field' => "fishing_efforts[{$effort->id}].duration_hours",
                    'severity' => 'warning',
                    'message' => "Durasi satu kali setting alat tangkap #{$effort->id} ({$effort->duration_hours} jam) melebihi 72 jam.",
                ];
            }

            if ($effort->setting_count !== null && (int) $effort->setting_count < 1) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.positive_setting_count',
                    'field' => "fishing_efforts[{$effort->id}].setting_count",
                    'severity' => 'error',
                    'message' => "Jumlah setting alat tangkap #{$effort->id} minimal harus 1.",
                ];
            }
        }

        // Catches-level numeric checks
        foreach ($trip->catches as $catch) {
            $w = (float) $catch->weight_kg;
            if ($w < 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.non_negative_catch_weight',
                    'field' => "catches[{$catch->id}].weight_kg",
                    'severity' => 'error',
                    'message' => "Bobot tangkapan ikan #{$catch->id} bernilai negatif ({$w} kg).",
                ];
            } elseif ($w == 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.positive_catch_weight',
                    'field' => "catches[{$catch->id}].weight_kg",
                    'severity' => 'warning',
                    'message' => "Bobot tangkapan tercatat 0 kg pada tangkapan #{$catch->id}.",
                ];
            }

            if ($w > 50000) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.reasonable_catch_weight',
                    'field' => "catches[{$catch->id}].weight_kg",
                    'severity' => 'warning',
                    'message' => "Bobot tangkapan satu spesies ({$w} kg) melebihi batas wajar 50 Ton per trip.",
                ];
            }

            if ($catch->fish_count !== null && (int) $catch->fish_count < 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.non_negative_fish_count',
                    'field' => "catches[{$catch->id}].fish_count",
                    'severity' => 'error',
                    'message' => "Jumlah ekor ikan pada tangkapan #{$catch->id} bernilai negatif.",
                ];
            }
        }

        // Landings-level numeric checks
        foreach ($trip->landings as $landing) {
            if ($landing->total_weight_kg !== null && (float) $landing->total_weight_kg < 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.non_negative_landing_weight',
                    'field' => "landings[{$landing->id}].total_weight_kg",
                    'severity' => 'error',
                    'message' => "Total berat pendaratan #{$landing->id} bernilai negatif.",
                ];
            }

            if ($landing->total_value_rp !== null && (float) $landing->total_value_rp < 0) {
                $issues[] = [
                    'category' => 'numeric',
                    'rule' => 'numeric.non_negative_landing_value',
                    'field' => "landings[{$landing->id}].total_value_rp",
                    'severity' => 'error',
                    'message' => "Total nilai omzet pendaratan #{$landing->id} bernilai negatif.",
                ];
            }

            foreach ($landing->items as $item) {
                if ((float) $item->weight_kg < 0) {
                    $issues[] = [
                        'category' => 'numeric',
                        'rule' => 'numeric.non_negative_item_weight',
                        'field' => "landing_items[{$item->id}].weight_kg",
                        'severity' => 'error',
                        'message' => "Berat ikan pendaratan item #{$item->id} bernilai negatif.",
                    ];
                }
                if ($item->unit_price !== null && (float) $item->unit_price < 0) {
                    $issues[] = [
                        'category' => 'numeric',
                        'rule' => 'numeric.non_negative_unit_price',
                        'field' => "landing_items[{$item->id}].unit_price",
                        'severity' => 'error',
                        'message' => "Harga per kg pada pendaratan item #{$item->id} bernilai negatif.",
                    ];
                }
            }
        }
    }

    /**
     * Category 4: Statistical Consistency.
     */
    protected function checkStatisticalConsistency(FishingTrip $trip, array &$issues): void
    {
        $totalCatchKg = (float) $trip->catches->sum('weight_kg');
        $totalLandingKg = (float) $trip->landings->sum('total_weight_kg');
        $totalEffortHours = (float) $trip->fishingEfforts->sum('duration_hours');
        $totalSettings = (int) $trip->fishingEfforts->sum('setting_count') ?: $trip->fishingEfforts->count();

        // Check CPUE Denominator
        if ($totalCatchKg > 0 && $totalEffortHours == 0 && $totalSettings == 0) {
            $issues[] = [
                'category' => 'statistical',
                'rule' => 'statistical.cpue_denominator_available',
                'field' => 'fishing_efforts',
                'severity' => 'warning',
                'message' => 'Tangkapan tercatat namun tidak ditemukan durasi/setting upaya tangkap untuk kalkulasi CPUE.',
            ];
        }

        // Reconcile Catch vs Landing discrepancy
        if ($totalCatchKg > 0 && $totalLandingKg > 0) {
            $diffRatio = abs($totalCatchKg - $totalLandingKg) / max($totalCatchKg, $totalLandingKg);
            if ($diffRatio > 0.40) { // > 40% discrepancy
                $issues[] = [
                    'category' => 'statistical',
                    'rule' => 'statistical.catch_landing_reconciliation',
                    'field' => 'catches',
                    'severity' => 'warning',
                    'message' => sprintf(
                        'Terdapat perbedaan signifikan antara tangkapan fisik (%.1f kg) dan pendaratan TPI (%.1f kg) sebesar %.1f%%.',
                        $totalCatchKg,
                        $totalLandingKg,
                        $diffRatio * 100
                    ),
                ];
            }
        }
    }

    /**
     * Category 5: Workflow State Compliance.
     */
    protected function checkWorkflowIntegrity(FishingTrip $trip, array &$issues): void
    {
        $validStatuses = ['draft', 'submitted', 'validated', 'rejected'];
        $currentStatus = $trip->validation_status ?? 'draft';

        if (! in_array($currentStatus, $validStatuses, true)) {
            $issues[] = [
                'category' => 'workflow',
                'rule' => 'workflow.valid_status_value',
                'field' => 'validation_status',
                'severity' => 'error',
                'message' => "Status validasi '{$currentStatus}' tidak sesuai standar alur kerja.",
            ];
        }

        if ($currentStatus === 'validated' && empty($trip->validated_by)) {
            $issues[] = [
                'category' => 'workflow',
                'rule' => 'workflow.validator_audit_trail',
                'field' => 'validated_by',
                'severity' => 'warning',
                'message' => 'Data trip berstatus tervalidasi namun tidak mencatat identitas validator resmi.',
            ];
        }

        if ($currentStatus === 'rejected' && empty($trip->rejection_reason)) {
            $issues[] = [
                'category' => 'workflow',
                'rule' => 'workflow.rejection_reason_required',
                'field' => 'rejection_reason',
                'severity' => 'warning',
                'message' => 'Trip ditolak / diminta revisi namun alasan penolakan tidak dicantumkan.',
            ];
        }
    }
}
