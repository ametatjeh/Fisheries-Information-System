<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Fisherman;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WilayahController extends Controller
{
    /**
     * Tampilkan halaman utama master data wilayah
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'kabupaten');
        if ($tab === 'provinsi') {
            $tab = 'kabupaten';
        }
        $search = $request->query('search');

        // Ambil data Provinsi Aceh sebagai fokus utama aplikasi
        $aceh = Province::where('code', '11')->first();

        // Ringkasan jumlah data
        $counts = [
            'provinsi' => Province::count(),
            'kabupaten' => Regency::count(),
            'kecamatan' => District::count(),
            'desa' => Village::count(),
            'aceh_kab' => $aceh ? $aceh->regencies()->count() : 0,
        ];

        // Filter default untuk tab kabupaten/kecamatan/desa
        $selectedProvinceId = $request->query('province_id', $aceh?->id);

        // Ambil data sesuai tab yang aktif (default: kabupaten)
        $items = match ($tab) {
            'kecamatan' => District::with(['regency.province'])
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->when($request->query('regency_id'), function ($q, $rId) {
                    $q->where('regency_id', $rId);
                })
                ->orderBy('code')
                ->paginate(25)
                ->withQueryString(),

            'desa' => Village::with(['district.regency.province'])
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->when($request->query('district_id'), function ($q, $dId) {
                    $q->where('district_id', $dId);
                })
                ->orderBy('code')
                ->paginate(25)
                ->withQueryString(),

            default => Regency::with('province')
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                ->when($request->has('province_id') ? $request->query('province_id') : $aceh?->id, function ($q, $pId) {
                    if ($pId !== 'all') {
                        $q->where('province_id', $pId);
                    }
                })
                ->orderBy('code')
                ->paginate(25)
                ->withQueryString(),
        };

        // Data opsi untuk dropdown modal tambah/edit dan filter
        // Posisikan Aceh di baris paling atas
        $provinces = Province::orderByRaw("code = '11' DESC, name ASC")->get(['id', 'code', 'name']);
        $regencies = Regency::where('province_id', $aceh?->id)
            ->orWhereIn('id', District::pluck('regency_id'))
            ->orderBy('code')
            ->get(['id', 'province_id', 'code', 'name', 'type']);
        $districts = District::orderBy('code')->get(['id', 'regency_id', 'code', 'name']);

        return view('master.wilayah.index', compact(
            'tab',
            'search',
            'counts',
            'items',
            'provinces',
            'regencies',
            'districts',
            'aceh',
            'selectedProvinceId'
        ));
    }

    // =========================================================================
    // PROVINSI CRUD
    // =========================================================================

    public function storeProvince(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:provinces,code'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        Province::create($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'provinsi'])
            ->with('success', __('Provinsi berhasil ditambahkan.'));
    }

    public function updateProvince(Request $request, Province $province): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:provinces,code,'.$province->id],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $province->update($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'provinsi'])
            ->with('success', __('Data provinsi berhasil diperbarui.'));
    }

    public function destroyProvince(Province $province): RedirectResponse
    {
        $name = $province->name;

        // Cek keterkaitan dengan kabupaten/kota, pangkalan pendaratan, atau nelayan
        if ($province->regencies()->exists() || LandingSite::where('province_id', $province->id)->exists() || Fisherman::where('province_id', $province->id)->exists()) {
            return redirect()->route('master.wilayah.index', ['tab' => 'provinsi'])
                ->with('error', __('Provinsi :name tidak dapat dihapus karena masih memiliki kabupaten/kota atau terhubung dengan data operasional.', ['name' => $name]));
        }

        $province->delete();

        return redirect()->route('master.wilayah.index', ['tab' => 'provinsi'])
            ->with('success', __('Provinsi :name berhasil dihapus.', ['name' => $name]));
    }

    // =========================================================================
    // KABUPATEN/KOTA CRUD
    // =========================================================================

    public function storeRegency(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'exists:provinces,id'],
            'code' => ['required', 'string', 'max:10', 'unique:regencies,code'],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:kabupaten,kota'],
        ]);

        Regency::create($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'kabupaten'])
            ->with('success', __('Kabupaten/Kota berhasil ditambahkan.'));
    }

    public function updateRegency(Request $request, Regency $regency): RedirectResponse
    {
        $validated = $request->validate([
            'province_id' => ['required', 'exists:provinces,id'],
            'code' => ['required', 'string', 'max:10', 'unique:regencies,code,'.$regency->id],
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:kabupaten,kota'],
        ]);

        $regency->update($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'kabupaten'])
            ->with('success', __('Data Kabupaten/Kota berhasil diperbarui.'));
    }

    public function destroyRegency(Regency $regency): RedirectResponse
    {
        $name = $regency->name;

        // Cek keterkaitan dengan kecamatan, pangkalan pendaratan, atau nelayan
        if ($regency->districts()->exists() || LandingSite::where('regency_id', $regency->id)->exists() || Fisherman::where('regency_id', $regency->id)->exists()) {
            return redirect()->route('master.wilayah.index', ['tab' => 'kabupaten'])
                ->with('error', __('Kabupaten/Kota :name tidak dapat dihapus karena masih memiliki kecamatan atau terhubung dengan data operasional.', ['name' => $name]));
        }

        $regency->delete();

        return redirect()->route('master.wilayah.index', ['tab' => 'kabupaten'])
            ->with('success', __('Kabupaten/Kota :name berhasil dihapus.', ['name' => $name]));
    }

    // =========================================================================
    // KECAMATAN CRUD
    // =========================================================================

    public function storeDistrict(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'regency_id' => ['required', 'exists:regencies,id'],
            'code' => ['required', 'string', 'max:15', 'unique:districts,code'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        District::create($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'kecamatan'])
            ->with('success', __('Kecamatan berhasil ditambahkan.'));
    }

    public function updateDistrict(Request $request, District $district): RedirectResponse
    {
        $validated = $request->validate([
            'regency_id' => ['required', 'exists:regencies,id'],
            'code' => ['required', 'string', 'max:15', 'unique:districts,code,'.$district->id],
            'name' => ['required', 'string', 'max:100'],
        ]);

        $district->update($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'kecamatan'])
            ->with('success', __('Data kecamatan berhasil diperbarui.'));
    }

    public function destroyDistrict(District $district): RedirectResponse
    {
        $name = $district->name;

        // Cek keterkaitan dengan desa/kelurahan, pangkalan pendaratan, atau nelayan
        if ($district->villages()->exists() || LandingSite::where('district_id', $district->id)->exists() || Fisherman::where('district_id', $district->id)->exists()) {
            return redirect()->route('master.wilayah.index', ['tab' => 'kecamatan'])
                ->with('error', __('Kecamatan :name tidak dapat dihapus karena masih memiliki desa atau terhubung dengan data operasional.', ['name' => $name]));
        }

        $district->delete();

        return redirect()->route('master.wilayah.index', ['tab' => 'kecamatan'])
            ->with('success', __('Kecamatan :name berhasil dihapus.', ['name' => $name]));
    }

    // =========================================================================
    // DESA/KELURAHAN CRUD
    // =========================================================================

    public function storeVillage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'code' => ['required', 'string', 'max:20', 'unique:villages,code'],
            'name' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        Village::create($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'desa'])
            ->with('success', __('Desa/Kelurahan berhasil ditambahkan.'));
    }

    public function updateVillage(Request $request, Village $village): RedirectResponse
    {
        $validated = $request->validate([
            'district_id' => ['required', 'exists:districts,id'],
            'code' => ['required', 'string', 'max:20', 'unique:villages,code,'.$village->id],
            'name' => ['required', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        $village->update($validated);

        return redirect()->route('master.wilayah.index', ['tab' => 'desa'])
            ->with('success', __('Data Desa/Kelurahan berhasil diperbarui.'));
    }

    public function destroyVillage(Village $village): RedirectResponse
    {
        $name = $village->name;

        // Cek keterkaitan dengan pangkalan pendaratan atau nelayan
        if (LandingSite::where('village_id', $village->id)->exists() || Fisherman::where('village_id', $village->id)->exists()) {
            return redirect()->route('master.wilayah.index', ['tab' => 'desa'])
                ->with('error', __('Desa/Kelurahan :name tidak dapat dihapus karena masih terhubung dengan data operasional.', ['name' => $name]));
        }

        $village->delete();

        return redirect()->route('master.wilayah.index', ['tab' => 'desa'])
            ->with('success', __('Desa/Kelurahan :name berhasil dihapus.', ['name' => $name]));
    }

    // =========================================================================
    // API CASCADING DROPDOWN (JSON)
    // =========================================================================

    public function apiRegencies(Province $province): JsonResponse
    {
        $regencies = $province->regencies()->orderBy('name')->get(['id', 'code', 'name', 'type']);

        return response()->json($regencies);
    }

    public function apiDistricts(Regency $regency): JsonResponse
    {
        $districts = $regency->districts()->orderBy('name')->get(['id', 'code', 'name']);

        return response()->json($districts);
    }

    public function apiVillages(District $district): JsonResponse
    {
        $villages = $district->villages()->orderBy('name')->get(['id', 'code', 'name', 'postal_code']);

        return response()->json($villages);
    }
}
