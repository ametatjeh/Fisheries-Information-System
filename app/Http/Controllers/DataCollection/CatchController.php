<?php

namespace App\Http\Controllers\DataCollection;

use App\Http\Controllers\Controller;
use App\Models\FishCatch;
use App\Models\FishingTrip;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CatchController extends Controller
{
    /**
     * Tampilkan halaman utama pengumpulan data hasil tangkapan ikan (Fish Catches).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $catchStatus = $request->query('catch_status');
        $fishGroup = $request->query('fish_group');
        $fishingTripId = $request->query('fishing_trip_id');
        $fishSpeciesId = $request->query('fish_species_id');

        // Ringkasan metrik statistik hasil tangkapan
        $totalWeightKg = (float) FishCatch::sum('weight_kg');
        $counts = [
            'total_weight_kg' => $totalWeightKg,
            'total_weight_ton' => round($totalWeightKg / 1000, 2),
            'total_fish_count' => (int) FishCatch::sum('fish_count'),
            'total_species' => (int) FishCatch::distinct('fish_species_id')->count('fish_species_id'),
            'total_records' => FishCatch::count(),
            'target_count' => FishCatch::where('catch_status', 'target')->count(),
            'bycatch_count' => FishCatch::where('catch_status', 'bycatch')->count(),
            'discarded_count' => FishCatch::where('catch_status', 'discarded')->count(),
            'target_weight_kg' => (float) FishCatch::where('catch_status', 'target')->sum('weight_kg'),
        ];

        // Query data hasil tangkapan
        $catches = FishCatch::with([
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'species',
            'fishingEffort.fishingGear',
        ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('species', function ($s) use ($search) {
                            $s->where('local_name_id', 'like', "%{$search}%")
                                ->orWhere('local_name', 'like', "%{$search}%")
                                ->orWhere('scientific_name', 'like', "%{$search}%")
                                ->orWhere('fao_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('fishingTrip', function ($ft) use ($search) {
                            $ft->where('trip_number', 'like', "%{$search}%")
                                ->orWhereHas('vessel', function ($v) use ($search) {
                                    $v->where('name', 'like', "%{$search}%")
                                        ->orWhere('registration_number', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($catchStatus, function ($q, $cs) {
                $q->where('catch_status', $cs);
            })
            ->when($fishGroup, function ($q, $fg) {
                $q->whereHas('species', function ($s) use ($fg) {
                    $s->where('fish_group', $fg);
                });
            })
            ->when($fishingTripId, function ($q, $ftId) {
                $q->where('fishing_trip_id', $ftId);
            })
            ->when($fishSpeciesId, function ($q, $fsId) {
                $q->where('fish_species_id', $fsId);
            })
            ->orderByDesc('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Data master untuk filter & modal formulir
        $trips = FishingTrip::with(['vessel', 'fishingEfforts.fishingGear'])
            ->orderByDesc('trip_number')
            ->get();

        $fishGroups = [
            'pelagis_besar' => 'Pelagis Besar',
            'pelagis_kecil' => 'Pelagis Kecil',
            'demersal' => 'Demersal',
            'karang' => 'Ikan Karang',
            'krustasea_moluska' => 'Krustasea & Moluska',
        ];

        return view('catches.index', compact(
            'catches',
            'counts',
            'trips',
            'fishGroups'
        ));
    }

    /**
     * Simpan data hasil tangkapan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'fish_species_id' => [
                'required',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'fishing_effort_id' => [
                'nullable',
                Rule::exists('fishing_efforts', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('fishing_trip_id')) {
                        $query->where('fishing_trip_id', $request->input('fishing_trip_id'));
                    }
                }),
            ],
            'weight_kg' => 'required|numeric|min:0.01|max:100000',
            'fish_count' => 'nullable|integer|min:0|max:1000000',
            'catch_status' => 'required|in:target,bycatch,discarded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $catch = FishCatch::create($validated);
        $speciesName = $catch->species->local_name_id ?? 'Ikan';

        return redirect()
            ->route('catches.index')
            ->with('success', "Data hasil tangkapan {$speciesName} ({$catch->weight_kg} kg) berhasil dicatat.");
    }

    /**
     * Perbarui data hasil tangkapan.
     */
    public function update(Request $request, FishCatch $catch): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'fish_species_id' => [
                'required',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'fishing_effort_id' => [
                'nullable',
                Rule::exists('fishing_efforts', 'id')->where(function ($query) use ($request) {
                    if ($request->filled('fishing_trip_id')) {
                        $query->where('fishing_trip_id', $request->input('fishing_trip_id'));
                    }
                }),
            ],
            'weight_kg' => 'required|numeric|min:0.01|max:100000',
            'fish_count' => 'nullable|integer|min:0|max:1000000',
            'catch_status' => 'required|in:target,bycatch,discarded',
            'notes' => 'nullable|string|max:1000',
        ]);

        $catch->update($validated);
        $speciesName = $catch->species->local_name_id ?? 'Ikan';

        return redirect()
            ->route('catches.index')
            ->with('success', "Data hasil tangkapan {$speciesName} berhasil diperbarui.");
    }

    /**
     * Hapus data hasil tangkapan.
     */
    public function destroy(FishCatch $catch): RedirectResponse
    {
        $speciesName = $catch->species->local_name_id ?? 'Ikan';
        $weight = $catch->weight_kg;
        $catch->delete();

        return redirect()
            ->route('catches.index')
            ->with('success', "Catatan hasil tangkapan {$speciesName} ({$weight} kg) berhasil dihapus.");
    }
}
