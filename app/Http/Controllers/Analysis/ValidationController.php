<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use App\Models\FishingTrip;
use App\Models\ValidationLog;
use App\Services\FisheriesValidationEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ValidationController extends Controller
{
    public function __construct(
        protected ?FisheriesValidationEngineService $validationEngine = null
    ) {
        $this->validationEngine = $validationEngine ?? app(FisheriesValidationEngineService::class);
    }

    /**
     * Tampilkan halaman utama workflow validasi dan kontrol mutu data operasional penangkapan.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $status = $request->query('status');
        $fmaCode = $request->query('fma_code');

        // Ringkasan metrik alur kerja verifikasi data
        $counts = [
            'total_trips' => FishingTrip::count(),
            'submitted_count' => FishingTrip::where('validation_status', 'submitted')->count(),
            'validated_count' => FishingTrip::where('validation_status', 'validated')->count(),
            'draft_count' => FishingTrip::where('validation_status', 'draft')->count(),
            'rejected_count' => FishingTrip::where('validation_status', 'rejected')->count(),
            'total_audit_logs' => ValidationLog::count(),
        ];

        // Query trip dengan relasi lengkap, indikator kelengkapan dokumen, dan rekonsiliasi bobot
        $trips = FishingTrip::with([
            'vessel',
            'captain',
            'departureSite',
            'landingSite',
            'primaryGear',
            'submittedBy',
            'validatedBy',
            'validationLogs.validator',
        ])
            ->withCount(['logbooks', 'fishingEfforts', 'catches', 'landings'])
            ->withSum('catches as total_catch_kg', 'weight_kg')
            ->withSum('landings as total_landing_kg', 'total_weight_kg')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('trip_number', 'like', "%{$search}%")
                        ->orWhere('fishing_ground_name', 'like', "%{$search}%")
                        ->orWhereHas('vessel', function ($v) use ($search) {
                            $v->where('name', 'like', "%{$search}%")
                                ->orWhere('registration_number', 'like', "%{$search}%");
                        })
                        ->orWhereHas('captain', function ($c) use ($search) {
                            $c->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status && $status !== 'all', function ($q) use ($status) {
                $q->where('validation_status', $status);
            })
            ->when($fmaCode, function ($q, $fma) {
                $q->where('fma_code', $fma);
            })
        // Prioritas urutan: submitted (menunggu verifikasi) paling atas, lalu draft, rejected, validated
            ->orderByRaw("CASE 
            WHEN validation_status = 'submitted' THEN 1 
            WHEN validation_status = 'draft' THEN 2 
            WHEN validation_status = 'rejected' THEN 3 
            WHEN validation_status = 'validated' THEN 4 
            ELSE 5 END")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // 10 log audit terbaru untuk visualisasi cepat
        $recentLogs = ValidationLog::with(['fishingTrip.vessel', 'validator'])
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        return view('analysis.validation.index', compact(
            'trips',
            'counts',
            'recentLogs'
        ));
    }

    /**
     * Proses pembaruan status validasi data trip (Setujui, Tolak/Minta Revisi, Draf).
     */
    public function updateStatus(Request $request, FishingTrip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'validation_status' => 'required|in:draft,submitted,validated,rejected',
            'notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $trip->validation_status;
        $newStatus = $validated['validation_status'];
        $notes = $validated['notes'];

        DB::transaction(function () use ($trip, $oldStatus, $newStatus, $notes, $validated) {
            $trip->validation_status = $newStatus;

            if ($newStatus === 'validated') {
                $trip->validated_by = auth()->id();
                $trip->validated_at = now();
                $trip->rejection_reason = null;
            } elseif ($newStatus === 'rejected') {
                $trip->rejection_reason = $validated['rejection_reason'] ?? $notes;
                $trip->validated_by = auth()->id();
                $trip->validated_at = now();
            } elseif ($newStatus === 'submitted') {
                $trip->submitted_by = auth()->id();
                $trip->submitted_at = now();
            }

            $trip->save();

            // Catat ke log audit validasi
            ValidationLog::create([
                'fishing_trip_id' => $trip->id,
                'validator_id' => auth()->id() ?? 1,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'notes' => $notes ?? ($newStatus === 'rejected' ? $trip->rejection_reason : "Status trip diubah dari {$oldStatus} ke {$newStatus}"),
            ]);
        });

        $statusLabels = [
            'validated' => 'disetujui (Tervalidasi ✅)',
            'rejected' => 'ditolak / diminta revisi (❌)',
            'submitted' => 'diajukan untuk verifikasi (⏳)',
            'draft' => 'dikembalikan ke draf (📝)',
        ];

        $label = $statusLabels[$newStatus] ?? $newStatus;

        return redirect()
            ->route('analysis.validation.index')
            ->with('success', "Trip {$trip->trip_number} berhasil {$label}.");
    }

    /**
     * Tampilkan riwayat lengkap audit trail validasi data.
     */
    public function logs(Request $request): View
    {
        $search = $request->query('search');

        $logs = ValidationLog::with(['fishingTrip.vessel', 'fishingTrip.captain', 'validator'])
            ->when($search, function ($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                    ->orWhereHas('fishingTrip', function ($ft) use ($search) {
                        $ft->where('trip_number', 'like', "%{$search}%")
                            ->orWhereHas('vessel', function ($v) use ($search) {
                                $v->where('name', 'like', "%{$search}%");
                            });
                    })
                    ->orWhereHas('validator', function ($u) use ($search) {
                        $u->where('name', 'like', "%{$search}%");
                    });
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('analysis.validation.logs', compact('logs'));
    }

    /**
     * Jalankan audit diagnostik kontrol mutu data tanpa mengubah data secara sepihak.
     */
    public function audit(FishingTrip $trip): JsonResponse
    {
        $report = $this->validationEngine->auditTrip($trip);

        return response()->json([
            'status' => 'ok',
            'data' => $report,
        ]);
    }
}
