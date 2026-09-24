<?php

namespace App\Http\Controllers\DataCollection;

use App\Http\Controllers\Controller;
use App\Models\Fisherman;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\Vessel;
use App\Models\Wppnri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FishingTripController extends Controller
{
    /**
     * Tampilkan halaman utama pengumpulan data trip penangkapan ikan
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $validationStatus = $request->query('validation_status');
        $fmaCode = $request->query('fma_code');
        $vesselId = $request->query('vessel_id');
        $landingSiteId = $request->query('landing_site_id');

        // Ringkasan metrik operasional trip penangkapan
        $counts = [
            'total' => FishingTrip::count(),
            'validated' => FishingTrip::where('validation_status', 'validated')->count(),
            'submitted' => FishingTrip::where('validation_status', 'submitted')->count(),
            'draft' => FishingTrip::where('validation_status', 'draft')->count(),
            'ongoing' => FishingTrip::whereNull('return_date')->count(),
            'total_fuel' => (float) FishingTrip::sum('fuel_consumption_liters'),
            'total_ice' => (float) FishingTrip::sum('ice_consumption_kg'),
        ];

        // Query data trip dengan relasi
        $trips = FishingTrip::with([
            'vessel',
            'captain',
            'departureSite',
            'landingSite',
            'primaryGear',
            'submittedBy',
            'validatedBy',
        ])
            ->withCount(['catches', 'landings'])
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
            ->when($validationStatus, function ($q, $vs) {
                if ($vs === 'ongoing') {
                    $q->whereNull('return_date');
                } else {
                    $q->where('validation_status', $vs);
                }
            })
            ->when($fmaCode, function ($q, $fma) {
                $q->where('fma_code', $fma);
            })
            ->when($vesselId, function ($q, $vid) {
                $q->where('vessel_id', $vid);
            })
            ->when($landingSiteId, function ($q, $lsid) {
                $q->where(function ($sub) use ($lsid) {
                    $sub->where('departure_site_id', $lsid)
                        ->orWhere('landing_site_id', $lsid);
                });
            })
            ->orderByDesc('departure_date')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Opsi Wilayah Pengelolaan Perikanan (WPPNRI)
        $wppList = [
            '571' => 'WPPNRI 571 (Selat Malaka & Laut Andaman)',
            '572' => 'WPPNRI 572 (Samudera Hindia Sebelah Barat Sumatera)',
        ];

        // Master data untuk dropdown modal & filter
        $vessels = Vessel::where('is_active', true)->with(['owner', 'homeportSite', 'primaryGear'])->orderBy('name')->get();
        $captains = Fisherman::where('is_active', true)->orderBy('name')->get(['id', 'nik', 'name', 'fisher_type', 'phone']);
        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name', 'site_type']);
        $fishingGears = FishingGear::where('is_active', true)->orderBy('name')->get(['id', 'code', 'name']);

        return view('trips.index', compact(
            'trips',
            'counts',
            'search',
            'validationStatus',
            'fmaCode',
            'vesselId',
            'landingSiteId',
            'wppList',
            'vessels',
            'captains',
            'landingSites',
            'fishingGears'
        ));
    }

    /**
     * Simpan data trip penangkapan baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'trip_number' => ['nullable', 'string', 'max:50', 'unique:fishing_trips,trip_number'],
            'vessel_id' => ['required', Rule::exists('vessels', 'id')->where('is_active', true)],
            'captain_id' => ['nullable', 'exists:fishers,id'],
            'departure_site_id' => ['required', 'exists:landing_sites,id'],
            'landing_site_id' => ['nullable', 'exists:landing_sites,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'crew_count' => ['required', 'integer', 'min:1'],
            'fuel_consumption_liters' => ['nullable', 'numeric', 'min:0'],
            'ice_consumption_kg' => ['nullable', 'numeric', 'min:0'],
            'primary_gear_id' => ['nullable', 'exists:fishing_gears,id'],
            'wppnri_id' => ['nullable', 'exists:wppnri,id'],
            'fishing_ground_id' => ['nullable', 'exists:fishing_grounds,id'],
            'fishing_ground_name' => ['nullable', 'string', 'max:150'],
            'fma_code' => ['nullable', 'string', 'max:20'],
            'validation_status' => ['required', 'in:draft,submitted,validated,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        // Sinkronisasi wppnri_id dan fma_code
        if (! empty($validated['wppnri_id']) && empty($validated['fma_code'])) {
            $validated['fma_code'] = Wppnri::find($validated['wppnri_id'])?->code;
        } elseif (! empty($validated['fma_code']) && empty($validated['wppnri_id'])) {
            $validated['wppnri_id'] = Wppnri::where('code', $validated['fma_code'])->value('id');
        }

        // Auto-generate trip_number jika kosong
        if (empty($validated['trip_number'])) {
            $yearMonth = date('Ym');
            $countThisMonth = FishingTrip::where('trip_number', 'like', "TRIP-{$yearMonth}-%")->count() + 1;
            $validated['trip_number'] = sprintf('TRIP-%s-%04d', $yearMonth, $countThisMonth);
        }

        // Catat submitted / validated timestamp dan user
        $user = auth()->user();
        $validated['submitted_by'] = $user?->id;
        if ($validated['validation_status'] !== 'draft') {
            $validated['submitted_at'] = now();
        }
        if ($validated['validation_status'] === 'validated') {
            $validated['validated_by'] = $user?->id;
            $validated['validated_at'] = now();
        }

        FishingTrip::create($validated);

        return redirect()->route('trips.index')
            ->with('success', __('Trip penangkapan :number berhasil ditambahkan.', ['number' => $validated['trip_number']]));
    }

    /**
     * Perbarui data trip penangkapan
     */
    public function update(Request $request, FishingTrip $trip): RedirectResponse
    {
        $validated = $request->validate([
            'trip_number' => ['required', 'string', 'max:50', 'unique:fishing_trips,trip_number,'.$trip->id],
            'vessel_id' => ['required', 'exists:vessels,id'],
            'captain_id' => ['nullable', 'exists:fishers,id'],
            'departure_site_id' => ['required', 'exists:landing_sites,id'],
            'landing_site_id' => ['nullable', 'exists:landing_sites,id'],
            'departure_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'crew_count' => ['required', 'integer', 'min:1'],
            'fuel_consumption_liters' => ['nullable', 'numeric', 'min:0'],
            'ice_consumption_kg' => ['nullable', 'numeric', 'min:0'],
            'primary_gear_id' => ['nullable', 'exists:fishing_gears,id'],
            'wppnri_id' => ['nullable', 'exists:wppnri,id'],
            'fishing_ground_id' => ['nullable', 'exists:fishing_grounds,id'],
            'fishing_ground_name' => ['nullable', 'string', 'max:150'],
            'fma_code' => ['nullable', 'string', 'max:20'],
            'validation_status' => ['required', 'in:draft,submitted,validated,rejected'],
            'notes' => ['nullable', 'string'],
        ]);

        // Sinkronisasi wppnri_id dan fma_code
        if (! empty($validated['wppnri_id']) && empty($validated['fma_code'])) {
            $validated['fma_code'] = Wppnri::find($validated['wppnri_id'])?->code;
        } elseif (! empty($validated['fma_code']) && empty($validated['wppnri_id'])) {
            $validated['wppnri_id'] = Wppnri::where('code', $validated['fma_code'])->value('id');
        }

        $user = auth()->user();
        if ($validated['validation_status'] === 'validated' && $trip->validation_status !== 'validated') {
            $validated['validated_by'] = $user?->id;
            $validated['validated_at'] = now();
        } elseif ($validated['validation_status'] === 'submitted' && empty($trip->submitted_at)) {
            $validated['submitted_by'] = $user?->id;
            $validated['submitted_at'] = now();
        }

        $trip->update($validated);

        return redirect()->route('trips.index')
            ->with('success', __('Data trip :number berhasil diperbarui.', ['number' => $trip->trip_number]));
    }

    /**
     * Hapus data trip penangkapan
     */
    public function destroy(FishingTrip $trip): RedirectResponse
    {
        $number = $trip->trip_number;

        // Cek keterkaitan dengan data operasional (effort, logbook, hasil tangkapan, pendaratan, sampling)
        if ($trip->landings()->exists() || $trip->catches()->exists() || $trip->fishingEfforts()->exists() || $trip->logbooks()->exists() || $trip->samples()->exists()) {
            return redirect()->route('trips.index')
                ->with('error', __('Trip :number tidak dapat dihapus karena sudah memiliki rekaman data operasional (effort, logbook, hasil tangkapan, pendaratan, atau sampling).', ['number' => $number]));
        }

        $trip->delete();

        return redirect()->route('trips.index')
            ->with('success', __('Trip penangkapan :number berhasil dihapus.', ['number' => $number]));
    }

    /**
     * Aksi cepat verifikasi status trip
     */
    public function validateTrip(Request $request, FishingTrip $trip): RedirectResponse
    {
        $action = $request->input('action', 'validate');
        $user = auth()->user();

        if ($action === 'validate') {
            $trip->update([
                'validation_status' => 'validated',
                'validated_by' => $user?->id,
                'validated_at' => now(),
                'rejection_reason' => null,
            ]);
            $msg = __('Trip :number berhasil diverifikasi dan divalidasi.', ['number' => $trip->trip_number]);
        } elseif ($action === 'reject') {
            $trip->update([
                'validation_status' => 'rejected',
                'validated_by' => $user?->id,
                'validated_at' => now(),
                'rejection_reason' => $request->input('rejection_reason', __('Data tidak sesuai / belum lengkap')),
            ]);
            $msg = __('Trip :number ditolak.', ['number' => $trip->trip_number]);
        } else {
            $trip->update([
                'validation_status' => 'submitted',
                'submitted_by' => $user?->id,
                'submitted_at' => now(),
            ]);
            $msg = __('Trip :number diajukan untuk verifikasi.', ['number' => $trip->trip_number]);
        }

        return redirect()->back()->with('success', $msg);
    }
}
