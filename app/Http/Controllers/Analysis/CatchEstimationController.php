<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use App\Models\CatchEstimation;
use App\Models\FishingGear;
use App\Models\LandingSite;
use App\Models\Species;
use App\Services\CatchEstimationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;

class CatchEstimationController extends Controller
{
    public function __construct(
        protected CatchEstimationService $estimationService
    ) {}

    /**
     * Tampilkan halaman utama manajemen & hasil estimasi produksi tangkapan.
     */
    public function index(Request $request): View
    {
        $filters = [
            'year' => $request->query('year', 2026),
            'month' => $request->query('month'),
            'landing_site_id' => $request->query('landing_site_id'),
            'fishing_gear_id' => $request->query('fishing_gear_id'),
            'fish_species_id' => $request->query('fish_species_id'),
            'status' => $request->query('status'),
        ];

        $estimations = $this->estimationService->getPaginatedEstimations($filters, $this->getPerPage($request));

        // Ringkasan metrik statistik hasil estimasi
        $allEstimations = CatchEstimation::all();
        $totalSampled = (float) $allEstimations->sum('sampled_catch_kg');
        $totalEstimated = (float) $allEstimations->sum('estimated_catch_kg');
        $avgRaisingFactor = $allEstimations->count() > 0
            ? round((float) $allEstimations->avg('raising_factor'), 4)
            : 1.0;

        $counts = [
            'total_estimations' => $allEstimations->count(),
            'total_sampled_kg' => $totalSampled,
            'total_estimated_kg' => $totalEstimated,
            'avg_raising_factor' => $avgRaisingFactor,
            'validated_count' => $allEstimations->where('status', 'validated')->count(),
            'draft_count' => $allEstimations->where('status', 'draft')->count(),
            'rejected_count' => $allEstimations->where('status', 'rejected')->count(),
        ];

        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $gears = FishingGear::where('is_active', true)->orderBy('name')->get();
        $species = Species::where('is_active', true)
            ->whereHas('catches')
            ->orderBy('local_name_id')
            ->get();

        return view('analysis.estimations.index', compact(
            'estimations',
            'counts',
            'filters',
            'landingSites',
            'gears',
            'species'
        ));
    }

    /**
     * Hitung otomatis (generate) estimasi baru dari data observasi lapangan.
     */
    public function generate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2030'],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'landing_site_id' => ['nullable', 'exists:landing_sites,id'],
            'fishing_gear_id' => ['nullable', 'exists:fishing_gears,id'],
            'fish_species_id' => ['nullable', 'exists:species,id'],
        ]);

        $calculated = $this->estimationService->calculateEstimation(
            (int) $validated['year'],
            isset($validated['month']) ? (int) $validated['month'] : null,
            isset($validated['landing_site_id']) ? (int) $validated['landing_site_id'] : null,
            isset($validated['fishing_gear_id']) ? (int) $validated['fishing_gear_id'] : null,
            isset($validated['fish_species_id']) ? (int) $validated['fish_species_id'] : null
        );

        if ($calculated->isEmpty()) {
            return redirect()
                ->route('analysis.estimations.index', $validated)
                ->with('info', 'Tidak ditemukan data tangkapan observasi pada stratum yang dipilih.');
        }

        $savedCount = 0;
        foreach ($calculated as $item) {
            $this->estimationService->storeEstimation($item, 'draft');
            $savedCount++;
        }

        return redirect()
            ->route('analysis.estimations.index', $validated)
            ->with('success', "Berhasil menghitung dan menyimpan {$savedCount} baris estimasi tangkapan (status: Draf).");
    }

    /**
     * Perbarui status validasi estimasi (Draft -> Validated -> Rejected).
     */
    public function updateStatus(Request $request, CatchEstimation $estimation): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,validated,rejected'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->estimationService->updateValidationStatus(
                $estimation,
                $validated['status'],
                $validated['notes'] ?? null
            );

            return redirect()
                ->back()
                ->with('success', "Status validasi estimasi #{$estimation->id} berhasil diubah menjadi {$validated['status']}.");
        } catch (InvalidArgumentException $e) {
            return redirect()
                ->back()
                ->with('error', "Gagal memvalidasi: {$e->getMessage()}");
        }
    }

    /**
     * Hapus rekor estimasi (hanya diizinkan untuk data berstatus draf atau ditolak).
     */
    public function destroy(CatchEstimation $estimation): RedirectResponse
    {
        if ($estimation->status === 'validated') {
            return redirect()
                ->back()
                ->with('error', 'Rekor estimasi yang sudah divalidasi resmi tidak dapat dihapus.');
        }

        $id = $estimation->id;
        $estimation->delete();

        return redirect()
            ->back()
            ->with('success', "Rekor estimasi #{$id} berhasil dihapus.");
    }
}
