<?php

namespace App\Http\Controllers\Analysis;

use App\Http\Controllers\Controller;
use App\Models\BiologicalMeasurement;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Sample;
use App\Models\SamplingPlan;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SamplingController extends Controller
{
    /**
     * Dashboard utama modul sampling & pemantauan biologi perikanan.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'samples');
        $search = $request->query('search');
        $landingSiteId = $request->query('landing_site_id');
        $samplingPlanId = $request->query('sampling_plan_id');
        $speciesId = $request->query('species_id');
        $status = $request->query('status');

        // Metrik Ringkasan Utama
        $totalPlans = SamplingPlan::count();
        $activePlans = SamplingPlan::where('status', 'active')->count();
        $totalSamples = Sample::count();
        $totalSpecimens = BiologicalMeasurement::count();

        $avgForkLength = BiologicalMeasurement::whereNotNull('fork_length_cm')->avg('fork_length_cm') ?: 0;

        $gonadCountTotal = BiologicalMeasurement::whereNotNull('gonad_maturity_stage')->count();
        $gonadMatureCount = BiologicalMeasurement::whereIn('gonad_maturity_stage', [4, 5])->count();
        $matureRatio = $gonadCountTotal > 0 ? round(($gonadMatureCount / $gonadCountTotal) * 100, 1) : 0;

        // Query Batch Sampel
        $samplesQuery = Sample::with([
            'samplingPlan',
            'fishingTrip.vessel',
            'landingSite',
            'enumerator',
            'biologicalMeasurements.fishSpecies',
        ])
            ->withAvg('biologicalMeasurements as avg_fl', 'fork_length_cm')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('sample_code', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('fishingTrip', function ($t) use ($search) {
                            $t->where('trip_number', 'like', "%{$search}%")
                                ->orWhereHas('vessel', function ($v) use ($search) {
                                    $v->where('name', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($landingSiteId, fn ($q) => $q->where('landing_site_id', $landingSiteId))
            ->when($samplingPlanId, fn ($q) => $q->where('sampling_plan_id', $samplingPlanId))
            ->when($speciesId, function ($q) use ($speciesId) {
                $q->whereHas('biologicalMeasurements', function ($m) use ($speciesId) {
                    $m->where('fish_species_id', $speciesId);
                });
            })
            ->orderByDesc('sample_date')
            ->orderByDesc('id');

        $samples = $samplesQuery->paginate(10, ['*'], 'samples_page')->withQueryString();

        // Query Rencana Program Sampling
        $plansQuery = SamplingPlan::with([
            'landingSite',
            'targetSpecies',
        ])
            ->withCount('samples')
            ->withSum('samples as measured_specimens_count', 'total_specimens')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fao_code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%");
                });
            })
            ->when($landingSiteId, fn ($q) => $q->where('landing_site_id', $landingSiteId))
            ->when($speciesId, fn ($q) => $q->where('target_species_id', $speciesId))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('start_date');

        $plans = $plansQuery->paginate(10, ['*'], 'plans_page')->withQueryString();

        // Data Master untuk Filter & Form Modal
        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $plansList = SamplingPlan::orderBy('code')->get();
        $tripsList = FishingTrip::with('vessel')->orderByDesc('departure_date')->limit(30)->get();
        $enumerators = User::orderBy('name')->get();

        return view('analysis.sampling.index', compact(
            'tab',
            'totalPlans',
            'activePlans',
            'totalSamples',
            'totalSpecimens',
            'avgForkLength',
            'matureRatio',
            'samples',
            'plans',
            'landingSites',
            'plansList',
            'tripsList',
            'enumerators'
        ));
    }

    /**
     * Simpan batch sampel baru.
     */
    public function storeSample(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sample_code' => ['required', 'string', 'max:50', 'unique:samples,sample_code'],
            'sampling_plan_id' => ['nullable', 'exists:sampling_plans,id'],
            'fishing_trip_id' => ['nullable', 'exists:fishing_trips,id'],
            'landing_site_id' => ['required', 'exists:landing_sites,id'],
            'enumerator_id' => ['nullable', 'exists:users,id'],
            'sample_date' => ['required', 'date'],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $validated['enumerator_id'] = $validated['enumerator_id'] ?: auth()->id();
        $validated['total_specimens'] = 0;
        $validated['total_weight_kg'] = $validated['total_weight_kg'] ?? 0;

        Sample::create($validated);

        return redirect()->route('analysis.sampling.index', ['tab' => 'samples'])
            ->with('success', "Batch sampel {$validated['sample_code']} berhasil didaftarkan.");
    }

    /**
     * Perbarui data batch sampel.
     */
    public function updateSample(Request $request, Sample $sample): RedirectResponse
    {
        $validated = $request->validate([
            'sampling_plan_id' => ['nullable', 'exists:sampling_plans,id'],
            'fishing_trip_id' => ['nullable', 'exists:fishing_trips,id'],
            'landing_site_id' => ['required', 'exists:landing_sites,id'],
            'enumerator_id' => ['nullable', 'exists:users,id'],
            'sample_date' => ['required', 'date'],
            'total_weight_kg' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $sample->update($validated);

        return redirect()->route('analysis.sampling.index', ['tab' => 'samples'])
            ->with('success', "Data batch sampel {$sample->sample_code} berhasil diperbarui.");
    }

    /**
     * Hapus batch sampel (dilindungi dari penghapusan bila ada data pengukuran spesimen).
     */
    public function destroySample(Sample $sample): RedirectResponse
    {
        if ($sample->biologicalMeasurements()->exists()) {
            return redirect()->route('analysis.sampling.index', ['tab' => 'samples'])
                ->with('error', "Batch sampel {$sample->sample_code} tidak dapat dihapus karena memiliki data spesimen pengukuran biologis.");
        }

        $code = $sample->sample_code;
        $sample->delete();

        return redirect()->route('analysis.sampling.index', ['tab' => 'samples'])
            ->with('success', "Batch sampel {$code} berhasil dihapus.");
    }

    /**
     * Simpan rencana program sampling baru.
     */
    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:sampling_plans,code'],
            'title' => ['required', 'string', 'max:150'],
            'landing_site_id' => ['required', 'exists:landing_sites,id'],
            'target_species_id' => [
                'nullable',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'target_sample_size' => ['required', 'integer', 'min:1'],
            'sampling_method' => ['required', 'in:random,stratified,systematic'],
            'status' => ['required', 'in:planned,active,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        SamplingPlan::create($validated);

        return redirect()->route('analysis.sampling.index', ['tab' => 'plans'])
            ->with('success', "Rencana program sampling {$validated['code']} berhasil ditambahkan.");
    }

    /**
     * Perbarui rencana program sampling.
     */
    public function updatePlan(Request $request, SamplingPlan $plan): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'landing_site_id' => ['required', 'exists:landing_sites,id'],
            'target_species_id' => [
                'nullable',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'target_sample_size' => ['required', 'integer', 'min:1'],
            'sampling_method' => ['required', 'in:random,stratified,systematic'],
            'status' => ['required', 'in:planned,active,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $plan->update($validated);

        return redirect()->route('analysis.sampling.index', ['tab' => 'plans'])
            ->with('success', "Rencana program sampling {$plan->code} berhasil diperbarui.");
    }

    /**
     * Hapus rencana program sampling (dilindungi bila sudah memiliki data sampel).
     */
    public function destroyPlan(SamplingPlan $plan): RedirectResponse
    {
        if ($plan->samples()->exists()) {
            return redirect()->route('analysis.sampling.index', ['tab' => 'plans'])
                ->with('error', "Rencana program sampling {$plan->code} tidak dapat dihapus karena memiliki data batch sampel terkait.");
        }

        $code = $plan->code;
        $plan->delete();

        return redirect()->route('analysis.sampling.index', ['tab' => 'plans'])
            ->with('success', "Rencana program sampling {$code} berhasil dihapus.");
    }

    /**
     * Tambah data pengukuran morfometrik spesimen baru ke dalam suatu batch sampel.
     */
    public function storeMeasurement(Request $request, Sample $sample): RedirectResponse
    {
        $validated = $request->validate([
            'fish_species_id' => [
                'required',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'fork_length_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'total_length_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'standard_length_cm' => ['nullable', 'numeric', 'gt:0', 'max:500'],
            'weight_gram' => ['nullable', 'numeric', 'gt:0'],
            'sex' => ['required', 'in:male,female,undetermined'],
            'gonad_maturity_stage' => ['nullable', 'integer', 'min:1', 'max:5'],
            'stomach_fullness' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($sample, $validated) {
            $nextNumber = ($sample->biologicalMeasurements()->max('specimen_number') ?? 0) + 1;
            $validated['sample_id'] = $sample->id;
            $validated['specimen_number'] = $nextNumber;

            BiologicalMeasurement::create($validated);

            // Perbarui rekapitulasi pada sampel
            $totalSpecimens = $sample->biologicalMeasurements()->count();
            $totalWeightGram = $sample->biologicalMeasurements()->sum('weight_gram') ?: 0;

            $sample->update([
                'total_specimens' => $totalSpecimens,
                'total_weight_kg' => round($totalWeightGram / 1000, 2),
            ]);
        });

        return redirect()->route('analysis.sampling.index', ['tab' => 'samples', 'inspect_sample_id' => $sample->id])
            ->with('success', "Spesimen berhasil ditambahkan ke sampel {$sample->sample_code}.");
    }

    /**
     * Hapus data pengukuran morfometrik spesimen.
     */
    public function destroyMeasurement(BiologicalMeasurement $measurement): RedirectResponse
    {
        $sample = $measurement->sample;

        DB::transaction(function () use ($measurement, $sample) {
            $measurement->delete();

            $totalSpecimens = $sample->biologicalMeasurements()->count();
            $totalWeightGram = $sample->biologicalMeasurements()->sum('weight_gram') ?: 0;

            $sample->update([
                'total_specimens' => $totalSpecimens,
                'total_weight_kg' => round($totalWeightGram / 1000, 2),
            ]);
        });

        return redirect()->route('analysis.sampling.index', ['tab' => 'samples', 'inspect_sample_id' => $sample->id])
            ->with('success', 'Spesimen berhasil dihapus.');
    }

    /**
     * Endpoint JSON untuk memuat daftar spesimen morfometrik dari suatu sampel.
     */
    public function getSpecimens(Sample $sample): JsonResponse
    {
        $sample->load([
            'samplingPlan',
            'fishingTrip.vessel',
            'landingSite',
            'enumerator',
            'biologicalMeasurements.fishSpecies',
        ]);

        return response()->json([
            'sample' => $sample,
            'measurements' => $sample->biologicalMeasurements,
        ]);
    }
}
