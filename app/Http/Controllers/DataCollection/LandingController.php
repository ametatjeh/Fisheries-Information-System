<?php

namespace App\Http\Controllers\DataCollection;

use App\Http\Controllers\Controller;
use App\Models\FishingTrip;
use App\Models\Landing;
use App\Models\LandingItem;
use App\Models\LandingSite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Tampilkan halaman utama transaksi pendaratan dan pembongkaran ikan (Fish Landings).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $landingSiteId = $request->query('landing_site_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Ringkasan metrik statistik pendaratan ikan
        $totalWeightKg = (float) Landing::sum('total_weight_kg');
        $totalValueRp = (float) Landing::sum('total_value_rp');

        $counts = [
            'total_landings' => Landing::count(),
            'total_weight_kg' => $totalWeightKg,
            'total_weight_ton' => round($totalWeightKg / 1000, 2),
            'total_value_rp' => $totalValueRp,
            'total_value_juta' => round($totalValueRp / 1000000, 1),
            'avg_transaction_value' => round((float) (Landing::avg('total_value_rp') ?? 0), 2),
            'total_buyers' => (int) Landing::sum('buyer_count'),
        ];

        // Query data pendaratan
        $landings = Landing::with([
            'fishingTrip.vessel',
            'fishingTrip.captain',
            'landingSite',
            'recordedBy',
            'items.species',
        ])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('landing_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('landingSite', function ($s) use ($search) {
                            $s->where('name', 'like', "%{$search}%")
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
            ->when($landingSiteId, function ($q, $lsId) {
                $q->where('landing_site_id', $lsId);
            })
            ->when($dateFrom, function ($q, $df) {
                $q->whereDate('landing_date', '>=', $df);
            })
            ->when($dateTo, function ($q, $dt) {
                $q->whereDate('landing_date', '<=', $dt);
            })
            ->orderByDesc('landing_date')
            ->orderByDesc('id')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Data pendukung form & filter
        $trips = FishingTrip::with(['vessel', 'catches.species'])
            ->orderByDesc('trip_number')
            ->get();

        $landingSites = LandingSite::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('landings.index', compact(
            'landings',
            'counts',
            'trips',
            'landingSites'
        ));
    }

    /**
     * Simpan transaksi pendaratan ikan baru ke database.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'landing_number' => 'nullable|string|max:50|unique:landings,landing_number',
            'fishing_trip_id' => 'nullable|exists:fishing_trips,id',
            'landing_site_id' => 'required|exists:landing_sites,id',
            'landing_date' => 'required|date',
            'buyer_count' => 'nullable|integer|min:0|max:1000',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.fish_species_id' => [
                'required_with:items',
                'integer',
                Rule::exists('species', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'items.*.weight_kg' => 'required_with:items|numeric|min:0.01|max:100000',
            'items.*.price_per_kg' => 'required_with:items|numeric|min:0|max:10000000',
            'items.*.fish_count' => 'nullable|integer|min:0',
            'items.*.quality_grade' => 'nullable|in:A,B,C,reject',
        ]);

        // Auto-generate landing_number jika tidak diisi manual
        if (empty($validated['landing_number'])) {
            $nextId = (Landing::max('id') ?? 0) + 1;
            $validated['landing_number'] = 'LND-'.date('Ym').'-'.str_pad($nextId, 4, '0', STR_PAD_LEFT);
        }

        $validated['recorded_by'] = auth()->id();

        DB::transaction(function () use ($validated) {
            $totalWeight = 0;
            $totalValue = 0;

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $weight = (float) $item['weight_kg'];
                    $price = (float) $item['price_per_kg'];
                    $totalWeight += $weight;
                    $totalValue += ($weight * $price);
                }
            }

            $landing = Landing::create([
                'landing_number' => $validated['landing_number'],
                'fishing_trip_id' => $validated['fishing_trip_id'] ?? null,
                'landing_site_id' => $validated['landing_site_id'],
                'landing_date' => $validated['landing_date'],
                'recorded_by' => $validated['recorded_by'],
                'total_weight_kg' => $totalWeight,
                'total_value_rp' => $totalValue,
                'buyer_count' => $validated['buyer_count'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    $weight = (float) $item['weight_kg'];
                    $price = (float) $item['price_per_kg'];

                    LandingItem::create([
                        'landing_id' => $landing->id,
                        'fish_species_id' => $item['fish_species_id'],
                        'weight_kg' => $weight,
                        'fish_count' => $item['fish_count'] ?? null,
                        'price_per_kg' => $price,
                        'total_price' => $weight * $price,
                        'quality_grade' => $item['quality_grade'] ?? 'A',
                    ]);
                }
            }
        });

        return redirect()
            ->route('landings.index')
            ->with('success', "Transaksi pendaratan {$validated['landing_number']} berhasil dibukukan.");
    }

    /**
     * Perbarui transaksi pendaratan ikan.
     */
    public function update(Request $request, Landing $landing): RedirectResponse
    {
        $validated = $request->validate([
            'fishing_trip_id' => 'nullable|exists:fishing_trips,id',
            'landing_site_id' => 'required|exists:landing_sites,id',
            'landing_date' => 'required|date',
            'buyer_count' => 'nullable|integer|min:0|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $landing->update($validated);

        return redirect()
            ->route('landings.index')
            ->with('success', "Data pendaratan {$landing->landing_number} berhasil diperbarui.");
    }

    /**
     * Hapus transaksi pendaratan ikan beserta seluruh rinciannya.
     */
    public function destroy(Landing $landing): RedirectResponse
    {
        $landingNumber = $landing->landing_number;
        $landing->delete();

        return redirect()
            ->route('landings.index')
            ->with('success', "Transaksi pendaratan {$landingNumber} berhasil dihapus.");
    }
}
