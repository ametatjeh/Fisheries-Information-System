<?php

namespace App\Http\Controllers\DataCollection;

use App\Http\Controllers\Controller;
use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FishingEffortController extends Controller
{
    /**
     * Tampilkan halaman utama pengumpulan data upaya penangkapan ikan (Fishing Efforts).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $fishingTripId = $request->query('fishing_trip_id');
        $fishingGearId = $request->query('fishing_gear_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Ringkasan metrik statistik upaya penangkapan (Fishing Effort)
        $counts = [
            'total_efforts' => FishingEffort::count(),
            'total_hours' => round((float) FishingEffort::sum('duration_hours'), 2),
            'avg_duration' => round((float) (FishingEffort::avg('duration_hours') ?? 0), 2),
            'total_net_length' => round((float) FishingEffort::sum('net_length_meters'), 2),
            'total_hooks' => (int) FishingEffort::sum('hook_count'),
            'total_settings' => (int) FishingEffort::sum('setting_count'),
        ];

        // Query data upaya penangkapan
        $efforts = FishingEffort::with([
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'fishingGear',
        ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('fishingTrip', function ($ft) use ($search) {
                        $ft->where('trip_number', 'like', "%{$search}%")
                            ->orWhereHas('vessel', function ($v) use ($search) {
                                $v->where('name', 'like', "%{$search}%")
                                    ->orWhere('registration_number', 'like', "%{$search}%");
                            });
                    })
                        ->orWhereHas('fishingGear', function ($g) use ($search) {
                            $g->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($fishingTripId, function ($q, $ftId) {
                $q->where('fishing_trip_id', $ftId);
            })
            ->when($fishingGearId, function ($q, $fgId) {
                $q->where('fishing_gear_id', $fgId);
            })
            ->when($dateFrom, function ($q, $df) {
                $q->whereDate('setting_date', '>=', $df);
            })
            ->when($dateTo, function ($q, $dt) {
                $q->whereDate('setting_date', '<=', $dt);
            })
            ->orderByDesc('setting_date')
            ->orderByDesc('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Data referensi untuk dropdown filter & modal
        $trips = FishingTrip::with('vessel')
            ->orderByDesc('trip_number')
            ->get();

        $gears = FishingGear::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('efforts.index', compact(
            'efforts',
            'counts',
            'trips',
            'gears'
        ));
    }

    /**
     * Simpan data upaya penangkapan ikan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'fishing_gear_id' => 'required|exists:fishing_gears,id',
            'setting_number' => 'required|integer|min:1|max:100',
            'setting_date' => 'nullable|date',
            'hauling_date' => 'nullable|date|after_or_equal:setting_date',
            'duration_hours' => 'nullable|numeric|min:0|max:1000',
            'setting_count' => 'required|integer|min:1|max:1000',
            'hook_count' => 'nullable|integer|min:0|max:100000',
            'net_length_meters' => 'nullable|numeric|min:0|max:100000',
            'latitude_setting' => 'nullable|numeric|between:-90,90',
            'longitude_setting' => 'nullable|numeric|between:-180,180',
            'latitude_hauling' => 'nullable|numeric|between:-90,90',
            'longitude_hauling' => 'nullable|numeric|between:-180,180',
        ]);

        // Kalkulasi durasi otomatis jika belum diinput manual
        if (empty($validated['duration_hours']) && ! empty($validated['setting_date']) && ! empty($validated['hauling_date'])) {
            $minutes = Carbon::parse($validated['setting_date'])->diffInMinutes(Carbon::parse($validated['hauling_date']));
            $validated['duration_hours'] = round($minutes / 60, 2);
        }

        $effort = FishingEffort::create($validated);

        return redirect()
            ->route('efforts.index')
            ->with('success', "Upaya penangkapan setting ke-{$effort->setting_number} berhasil dicatat.");
    }

    /**
     * Perbarui data upaya penangkapan ikan.
     */
    public function update(Request $request, FishingEffort $effort): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'fishing_gear_id' => 'required|exists:fishing_gears,id',
            'setting_number' => 'required|integer|min:1|max:100',
            'setting_date' => 'nullable|date',
            'hauling_date' => 'nullable|date|after_or_equal:setting_date',
            'duration_hours' => 'nullable|numeric|min:0|max:1000',
            'setting_count' => 'required|integer|min:1|max:1000',
            'hook_count' => 'nullable|integer|min:0|max:100000',
            'net_length_meters' => 'nullable|numeric|min:0|max:100000',
            'latitude_setting' => 'nullable|numeric|between:-90,90',
            'longitude_setting' => 'nullable|numeric|between:-180,180',
            'latitude_hauling' => 'nullable|numeric|between:-90,90',
            'longitude_hauling' => 'nullable|numeric|between:-180,180',
        ]);

        // Kalkulasi durasi otomatis jika belum diisi
        if (empty($validated['duration_hours']) && ! empty($validated['setting_date']) && ! empty($validated['hauling_date'])) {
            $minutes = Carbon::parse($validated['setting_date'])->diffInMinutes(Carbon::parse($validated['hauling_date']));
            $validated['duration_hours'] = round($minutes / 60, 2);
        }

        $effort->update($validated);

        return redirect()
            ->route('efforts.index')
            ->with('success', "Upaya penangkapan setting ke-{$effort->setting_number} berhasil diperbarui.");
    }

    /**
     * Hapus data upaya penangkapan ikan.
     */
    public function destroy(FishingEffort $effort): RedirectResponse
    {
        $settingNum = $effort->setting_number;

        // Cek keterkaitan dengan data hasil tangkapan (catches)
        if ($effort->catches()->exists()) {
            return redirect()
                ->route('efforts.index')
                ->with('error', "Upaya penangkapan setting ke-{$settingNum} tidak dapat dihapus karena sudah memiliki rekaman data hasil tangkapan ikan.");
        }

        $effort->delete();

        return redirect()
            ->route('efforts.index')
            ->with('success', "Data upaya penangkapan setting ke-{$settingNum} berhasil dihapus.");
    }
}
