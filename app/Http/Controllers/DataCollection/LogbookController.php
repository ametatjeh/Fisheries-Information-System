<?php

namespace App\Http\Controllers\DataCollection;

use App\Http\Controllers\Controller;
use App\Models\FishingTrip;
use App\Models\Logbook;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogbookController extends Controller
{
    /**
     * Tampilkan halaman utama pengumpulan data logbook harian kapal.
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $weatherCondition = $request->query('weather_condition');
        $fishingTripId = $request->query('fishing_trip_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Ringkasan metrik logbook harian
        $counts = [
            'total' => Logbook::count(),
            'cerah' => Logbook::where('weather_condition', 'cerah')->count(),
            'berawan' => Logbook::where('weather_condition', 'berawan')->count(),
            'hujan_badai' => Logbook::whereIn('weather_condition', ['hujan_ringan', 'hujan_lebat', 'badai'])->count(),
            'avg_wave' => round((float) (Logbook::avg('wave_height_meters') ?? 0), 2),
            'with_coordinates' => Logbook::whereNotNull('latitude')->whereNotNull('longitude')->count(),
        ];

        // Query daftar logbook
        $logbooks = Logbook::with([
            'fishingTrip.vessel',
            'fishingTrip.captain',
        ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('activity_description', 'like', "%{$search}%")
                        ->orWhere('sea_condition', 'like', "%{$search}%")
                        ->orWhereHas('fishingTrip', function ($ft) use ($search) {
                            $ft->where('trip_number', 'like', "%{$search}%")
                                ->orWhereHas('vessel', function ($v) use ($search) {
                                    $v->where('name', 'like', "%{$search}%")
                                        ->orWhere('registration_number', 'like', "%{$search}%");
                                });
                        });
                });
            })
            ->when($weatherCondition, function ($q, $wc) {
                $q->where('weather_condition', $wc);
            })
            ->when($fishingTripId, function ($q, $ftId) {
                $q->where('fishing_trip_id', $ftId);
            })
            ->when($dateFrom, function ($q, $df) {
                $q->whereDate('log_date', '>=', $df);
            })
            ->when($dateTo, function ($q, $dt) {
                $q->whereDate('log_date', '<=', $dt);
            })
            ->orderByDesc('log_date')
            ->orderByDesc('log_time')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        // Data pendukung untuk filter & modal form
        $trips = FishingTrip::with('vessel')
            ->orderByDesc('trip_number')
            ->get();

        $weatherOptions = [
            'cerah' => 'Cerah (☀️)',
            'berawan' => 'Berawan (⛅)',
            'hujan_ringan' => 'Hujan Ringan (🌦️)',
            'hujan_lebat' => 'Hujan Lebat (🌧️)',
            'badai' => 'Badai (⛈️)',
        ];

        return view('logbooks.index', compact(
            'logbooks',
            'counts',
            'trips',
            'weatherOptions'
        ));
    }

    /**
     * Simpan catatan logbook harian kapal baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'log_date' => 'required|date',
            'log_time' => 'nullable',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'weather_condition' => 'nullable|in:cerah,berawan,hujan_ringan,hujan_lebat,badai',
            'wave_height_meters' => 'nullable|numeric|min:0|max:20',
            'sea_condition' => 'nullable|string|max:50',
            'activity_description' => 'nullable|string|max:1000',
        ]);

        $logbook = Logbook::create($validated);

        return redirect()
            ->route('logbooks.index')
            ->with('success', "Catatan logbook tanggal {$logbook->log_date->format('d/m/Y')} berhasil ditambahkan.");
    }

    /**
     * Perbarui catatan logbook harian kapal.
     */
    public function update(Request $request, Logbook $logbook): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'required|exists:fishing_trips,id',
            'log_date' => 'required|date',
            'log_time' => 'nullable',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'weather_condition' => 'nullable|in:cerah,berawan,hujan_ringan,hujan_lebat,badai',
            'wave_height_meters' => 'nullable|numeric|min:0|max:20',
            'sea_condition' => 'nullable|string|max:50',
            'activity_description' => 'nullable|string|max:1000',
        ]);

        $logbook->update($validated);

        return redirect()
            ->route('logbooks.index')
            ->with('success', "Catatan logbook tanggal {$logbook->log_date->format('d/m/Y')} berhasil diperbarui.");
    }

    /**
     * Hapus catatan logbook kapal dari sistem.
     */
    public function destroy(Logbook $logbook): RedirectResponse
    {
        $date = $logbook->log_date ? $logbook->log_date->format('d/m/Y') : 'terpilih';
        $logbook->delete();

        return redirect()
            ->route('logbooks.index')
            ->with('success', "Catatan logbook {$date} berhasil dihapus dari sistem.");
    }
}
