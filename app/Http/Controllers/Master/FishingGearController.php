<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FaoIsscfgGear;
use App\Models\FishingGear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FishingGearController extends Controller
{
    /**
     * Tampilkan halaman utama master data alat tangkap
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $category = $request->query('category');
        $status = $request->query('status');
        $source = $request->query('source');
        $level = $request->query('level');

        // Validasi dan batasi nilai per_page
        $perPage = $request->integer('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100, 250, 500])) {
            $perPage = 25;
        }

        // Ringkasan jumlah alat tangkap
        $counts = [
            'total' => FishingGear::count(),
            'local' => FishingGear::where('source', 'LOCAL')->count(),
            'fao' => FishingGear::where('source', 'FAO_ISSCFG')->count(),
            'active' => FishingGear::where('is_active', true)->count(),
            'inactive' => FishingGear::where('is_active', false)->count(),
            'jaring_lingkar' => FishingGear::where('category', 'jaring_lingkar')->count(),
            'pancing' => FishingGear::where('category', 'pancing')->count(),
            'jaring_insang' => FishingGear::where('category', 'jaring_insang')->count(),
            'jaring_tarik' => FishingGear::where('category', 'jaring_tarik')->count(),
            'perangkap' => FishingGear::where('category', 'perangkap')->count(),
            'jaring_angkat' => FishingGear::where('category', 'jaring_angkat')->count(),
        ];

        // Query data alat tangkap dengan eager loading
        $gears = FishingGear::with(['parent'])
            ->withCount('vessels')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('code', 'like', "%{$search}%")
                        ->orWhere('isscfg_code', 'like', "%{$search}%")
                        ->orWhere('standard_abbreviation', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('name_en', 'like', "%{$search}%")
                        ->orWhere('name_id', 'like', "%{$search}%")
                        ->orWhere('local_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, function ($q, $c) {
                $q->where('category', $c);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($source, function ($q, $s) {
                $q->where('source', $s);
            })
            ->when($level, function ($q, $l) {
                $q->where('level', $l);
            })
            ->orderByRaw("CASE WHEN source = 'LOCAL' THEN 0 ELSE 1 END")
            ->orderBy('sort_order')
            ->orderBy('code')
            ->paginate($perPage)
            ->withQueryString();

        // Opsi label kategori alat penangkapan ikan (Permen KP / FAO)
        $categories = [
            'jaring_lingkar' => 'Jaring Lingkar (Purse Seine)',
            'pancing' => 'Pancing (Hook & Line)',
            'jaring_insang' => 'Jaring Insang (Gillnet)',
            'jaring_tarik' => 'Jaring Tarik (Beach Seine)',
            'jaring_angkat' => 'Jaring Angkat (Lift Net)',
            'perangkap' => 'Perangkap (Trap / Bubu)',
            'jaring_hela' => 'Jaring Hela (Trawl)',
            'alat_jatuh' => 'Alat yang Dijatuhkan',
            'alat_penjepit_melukai' => 'Alat Penjepit & Melukai',
            'lainnya' => 'Lainnya',
        ];

        // Daftar referensi FAO untuk dropdown mapping/induk (Legacy)
        $faoParents = FishingGear::where('source', 'FAO_ISSCFG')
            ->whereIn('level', [1, 2, 3])
            ->orderBy('sort_order')
            ->orderBy('isscfg_code')
            ->get(['id', 'isscfg_code', 'standard_abbreviation', 'name_en', 'level']);

        // Daftar referensi FAO murni untuk mapping ISSCFG (fao_isscfg_gear_id)
        $faoIsscfgReferences = FaoIsscfgGear::where('is_active', true)
            ->orderBy('isscfg_code')
            ->get(['id', 'isscfg_code', 'standard_abbreviation', 'name_en', 'name_id', 'level']);

        return view('master.gear.index', compact(
            'gears',
            'counts',
            'search',
            'category',
            'status',
            'source',
            'level',
            'perPage',
            'categories',
            'faoParents',
            'faoIsscfgReferences'
        ));
    }

    /**
     * Simpan data alat tangkap baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:fishing_gears,code'],
            'isscfg_code' => ['nullable', 'string', 'max:20'],
            'standard_abbreviation' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'name_id' => ['nullable', 'string', 'max:150'],
            'local_name' => ['nullable', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:fishing_gears,id'],
            'fao_isscfg_gear_id' => ['nullable', 'exists:fao_isscfg_gears,id'],
            'level' => ['nullable', 'integer', 'between:1,4'],
            'source' => ['required', 'in:FAO_ISSCFG,LOCAL'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['level'] = $validated['level'] ?? ($validated['source'] === 'LOCAL' ? 4 : 3);

        FishingGear::create($validated);

        return redirect()->route('master.gears.index')
            ->with('success', __('Alat tangkap :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data alat tangkap
     */
    public function update(Request $request, FishingGear $gear): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:fishing_gears,code,'.$gear->id],
            'isscfg_code' => ['nullable', 'string', 'max:20'],
            'standard_abbreviation' => ['nullable', 'string', 'max:10'],
            'name' => ['required', 'string', 'max:100'],
            'name_en' => ['nullable', 'string', 'max:150'],
            'name_id' => ['nullable', 'string', 'max:150'],
            'local_name' => ['nullable', 'string', 'max:150'],
            'category' => ['required', 'string', 'max:50'],
            'parent_id' => ['nullable', 'exists:fishing_gears,id'],
            'fao_isscfg_gear_id' => ['nullable', 'exists:fao_isscfg_gears,id'],
            'level' => ['nullable', 'integer', 'between:1,4'],
            'source' => ['required', 'in:FAO_ISSCFG,LOCAL'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;

        $gear->update($validated);

        return redirect()->route('master.gears.index')
            ->with('success', __('Data alat tangkap :name berhasil diperbarui.', ['name' => $gear->name]));
    }

    /**
     * Hapus data alat tangkap
     */
    public function destroy(FishingGear $gear): RedirectResponse
    {
        $name = $gear->name;

        // Cek keterkaitan dengan data kapal, trip, effort, atau hierarki anak
        if ($gear->vessels()->exists() || $gear->fishingTrips()->exists() || $gear->fishingEfforts()->exists() || $gear->children()->exists()) {
            return redirect()->route('master.gears.index')
                ->with('error', __('Alat tangkap :name tidak dapat dihapus karena masih digunakan oleh kapal, trip, logbook effort penangkapan, atau memiliki sub-alat tangkap.', ['name' => $name]));
        }

        $gear->delete();

        return redirect()->route('master.gears.index')
            ->with('success', __('Alat tangkap :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status aktif alat tangkap
     */
    public function toggleStatus(FishingGear $gear): RedirectResponse
    {
        $gear->update(['is_active' => ! $gear->is_active]);

        $statusText = $gear->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __('Status alat tangkap :name berhasil :status.', [
                'name' => $gear->name,
                'status' => $statusText,
            ]));
    }

    /**
     * Bulk update status aktif/nonaktif alat tangkap
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:fishing_gears,id'],
            'action' => ['required', 'in:activate,deactivate'],
        ]);

        $isActive = $validated['action'] === 'activate';

        $count = FishingGear::whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        $statusText = $isActive ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __(':count alat tangkap berhasil :status.', [
                'count' => $count,
                'status' => $statusText,
            ]));
    }
}
