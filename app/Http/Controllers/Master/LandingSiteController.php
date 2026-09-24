<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingSiteController extends Controller
{
    /**
     * Tampilkan halaman utama master data landing site (TPI / Pelabuhan Perikanan)
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $siteType = $request->query('site_type');
        $regencyId = $request->query('regency_id');
        $status = $request->query('status');

        // Ringkasan jumlah pangkalan per kategori
        $counts = [
            'total' => LandingSite::count(),
            'PPS' => LandingSite::where('site_type', 'PPS')->count(),
            'PPN' => LandingSite::where('site_type', 'PPN')->count(),
            'PPP' => LandingSite::where('site_type', 'PPP')->count(),
            'PPI' => LandingSite::where('site_type', 'PPI')->count(),
            'TPI' => LandingSite::where('site_type', 'TPI')->count(),
            'tradisional' => LandingSite::where('site_type', 'pangkalan_pendaratan_tradisional')->count(),
            'active' => LandingSite::where('is_active', true)->count(),
        ];

        // Query data landing site dengan eager loading
        $sites = LandingSite::with(['province', 'regency', 'district', 'village'])
            ->withCount(['vessels', 'landings'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($siteType, function ($q, $t) {
                $q->where('site_type', $t);
            })
            ->when($regencyId, function ($q, $r) {
                $q->where('regency_id', $r);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === '1');
            })
            ->orderByRaw("CASE site_type WHEN 'PPS' THEN 1 WHEN 'PPN' THEN 2 WHEN 'PPP' THEN 3 WHEN 'PPI' THEN 4 WHEN 'TPI' THEN 5 WHEN 'pangkalan_pendaratan_tradisional' THEN 6 ELSE 7 END")
            ->orderBy('code')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Opsi label tipe pelabuhan perikanan sesuai Permen KP
        $siteTypes = [
            'PPS' => [
                'label' => 'Pelabuhan Perikanan Samudera (PPS)',
                'short' => 'PPS',
                'class' => 'Kelas A (>60 GT)',
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
            ],
            'PPN' => [
                'label' => 'Pelabuhan Perikanan Nusantara (PPN)',
                'short' => 'PPN',
                'class' => 'Kelas B (15-60 GT)',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            ],
            'PPP' => [
                'label' => 'Pelabuhan Perikanan Pantai (PPP)',
                'short' => 'PPP',
                'class' => 'Kelas C (5-15 GT)',
                'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
            ],
            'PPI' => [
                'label' => 'Pangkalan Pendaratan Ikan (PPI)',
                'short' => 'PPI',
                'class' => 'Kelas D (<10 GT)',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            ],
            'TPI' => [
                'label' => 'Tempat Pelelangan Ikan (TPI)',
                'short' => 'TPI',
                'class' => 'Pelelangan & Pendaratan',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
            ],
            'pangkalan_pendaratan_tradisional' => [
                'label' => 'Pangkalan Pendaratan Tradisional',
                'short' => 'Tradisional',
                'class' => 'Pesisir / Gampong',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
            ],
        ];

        // Ambil data Provinsi dan Kabupaten untuk dropdown filter & modal
        $aceh = Province::where('code', '11')->first();
        $provinces = Province::orderBy('name')->get(['id', 'code', 'name']);
        $regencies = $aceh
            ? $aceh->regencies()->orderBy('name')->get(['id', 'code', 'name', 'type'])
            : Regency::orderBy('name')->get(['id', 'code', 'name', 'type']);

        return view('master.landing-sites.index', compact(
            'sites',
            'counts',
            'search',
            'siteType',
            'regencyId',
            'status',
            'siteTypes',
            'provinces',
            'regencies',
            'aceh'
        ));
    }

    /**
     * Simpan data landing site baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:landing_sites,code'],
            'name' => ['required', 'string', 'max:150'],
            'site_type' => ['required', 'in:PPS,PPN,PPP,PPI,TPI,pangkalan_pendaratan_tradisional'],
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        LandingSite::create($validated);

        return redirect()->route('master.landing-sites.index')
            ->with('success', __('Tempat pendaratan :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data landing site
     */
    public function update(Request $request, LandingSite $landingSite): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:30', 'unique:landing_sites,code,'.$landingSite->id],
            'name' => ['required', 'string', 'max:150'],
            'site_type' => ['required', 'in:PPS,PPN,PPP,PPI,TPI,pangkalan_pendaratan_tradisional'],
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $landingSite->update($validated);

        return redirect()->route('master.landing-sites.index')
            ->with('success', __('Data tempat pendaratan :name berhasil diperbarui.', ['name' => $landingSite->name]));
    }

    /**
     * Hapus data landing site
     */
    public function destroy(LandingSite $landingSite): RedirectResponse
    {
        $name = $landingSite->name;

        // Cek keterkaitan dengan trip, landing, kapal pangkalan, atau sampling
        if ($landingSite->vessels()->exists() || $landingSite->landings()->exists() || $landingSite->departureTrips()->exists() || $landingSite->returnTrips()->exists() || $landingSite->samples()->exists() || $landingSite->samplingPlans()->exists()) {
            return redirect()->route('master.landing-sites.index')
                ->with('error', __('Pangkalan :name tidak dapat dihapus karena masih terhubung dengan data armada kapal, riwayat trip/pendaratan ikan, atau sampling.', ['name' => $name]));
        }

        $landingSite->delete();

        return redirect()->route('master.landing-sites.index')
            ->with('success', __('Tempat pendaratan :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status aktif landing site
     */
    public function toggleStatus(LandingSite $landingSite): RedirectResponse
    {
        $landingSite->update(['is_active' => ! $landingSite->is_active]);

        $statusText = $landingSite->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __('Status tempat pendaratan :name berhasil :status.', [
                'name' => $landingSite->name,
                'status' => $statusText,
            ]));
    }
}
