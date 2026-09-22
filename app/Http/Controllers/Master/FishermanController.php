<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FisherGroup;
use App\Models\Fisherman;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FishermanController extends Controller
{
    /**
     * Tampilkan halaman utama master data nelayan
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $fisherType = $request->query('fisher_type');
        $fisherGroupId = $request->query('fisher_group_id');
        $regencyId = $request->query('regency_id');
        $status = $request->query('status');

        // Ringkasan metrik statistik peran nelayan
        $counts = [
            'total' => Fisherman::count(),
            'pemilik' => Fisherman::where('fisher_type', 'pemilik')->count(),
            'nahkoda' => Fisherman::where('fisher_type', 'nahkoda_jurumudi')->count(),
            'abk' => Fisherman::where('fisher_type', 'abk')->count(),
            'tanpa_perahu' => Fisherman::where('fisher_type', 'nelayan_tanpa_perahu')->count(),
            'with_kusuka' => Fisherman::whereNotNull('kusuka_number')->where('kusuka_number', '!=', '')->count(),
            'active' => Fisherman::where('is_active', true)->count(),
        ];

        // Query nelayan dengan eager loading relasi
        $fishermen = Fisherman::with(['province', 'regency', 'district', 'village', 'fisherGroup'])
            ->withCount('vessels')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%")
                        ->orWhere('kusuka_number', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($fisherType, function ($q, $t) {
                $q->where('fisher_type', $t);
            })
            ->when($fisherGroupId, function ($q, $g) {
                $q->where('fisher_group_id', $g);
            })
            ->when($regencyId, function ($q, $r) {
                $q->where('regency_id', $r);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === '1');
            })
            ->orderByRaw("CASE fisher_type WHEN 'pemilik' THEN 1 WHEN 'nahkoda_jurumudi' THEN 2 WHEN 'abk' THEN 3 WHEN 'nelayan_tanpa_perahu' THEN 4 ELSE 5 END")
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Opsi label dan badge peran nelayan
        $fisherTypes = [
            'pemilik' => [
                'label' => 'Pemilik Kapal',
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
                'icon' => '👑',
            ],
            'nahkoda_jurumudi' => [
                'label' => 'Nahkoda / Jurumudi',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'icon' => '🧭',
            ],
            'abk' => [
                'label' => 'Anak Buah Kapal (ABK)',
                'badge' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                'icon' => '⚓',
            ],
            'nelayan_tanpa_perahu' => [
                'label' => 'Nelayan Tanpa Perahu',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                'icon' => '🎣',
            ],
        ];

        // Data wilayah dan KUB untuk form modal & filter
        $aceh = Province::where('code', '11')->first();
        $provinces = Province::orderBy('name')->get(['id', 'code', 'name']);
        $regencies = $aceh
            ? $aceh->regencies()->orderBy('name')->get(['id', 'code', 'name', 'type'])
            : Regency::orderBy('name')->get(['id', 'code', 'name', 'type']);
        $fisherGroups = FisherGroup::orderBy('name')->get(['id', 'code', 'name']);

        return view('master.fishermen.index', compact(
            'fishermen',
            'counts',
            'search',
            'fisherType',
            'fisherGroupId',
            'regencyId',
            'status',
            'fisherTypes',
            'provinces',
            'regencies',
            'fisherGroups',
            'aceh'
        ));
    }

    /**
     * Simpan data nelayan baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:20', 'unique:fishers,nik'],
            'kusuka_number' => ['nullable', 'string', 'max:50', 'unique:fishers,kusuka_number'],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'fisher_group_id' => ['nullable', 'exists:fisher_groups,id'],
            'fisher_type' => ['required', 'in:pemilik,nahkoda_jurumudi,abk,nelayan_tanpa_perahu'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Fisherman::create($validated);

        return redirect()->route('master.fishermen.index')
            ->with('success', __('Data nelayan :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data nelayan
     */
    public function update(Request $request, Fisherman $fisherman): RedirectResponse
    {
        $validated = $request->validate([
            'nik' => ['required', 'string', 'max:20', 'unique:fishers,nik,'.$fisherman->id],
            'kusuka_number' => ['nullable', 'string', 'max:50', 'unique:fishers,kusuka_number,'.$fisherman->id],
            'name' => ['required', 'string', 'max:150'],
            'gender' => ['required', 'in:L,P'],
            'birth_place' => ['nullable', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:30'],
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'address' => ['nullable', 'string'],
            'fisher_group_id' => ['nullable', 'exists:fisher_groups,id'],
            'fisher_type' => ['required', 'in:pemilik,nahkoda_jurumudi,abk,nelayan_tanpa_perahu'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $fisherman->update($validated);

        return redirect()->route('master.fishermen.index')
            ->with('success', __('Data nelayan :name berhasil diperbarui.', ['name' => $fisherman->name]));
    }

    /**
     * Hapus data nelayan
     */
    public function destroy(Fisherman $fisherman): RedirectResponse
    {
        $name = $fisherman->name;

        // Cek keterkaitan dengan data kapal atau trip
        if ($fisherman->vessels()->exists() || $fisherman->tripsAsCaptain()->exists()) {
            return redirect()->route('master.fishermen.index')
                ->with('error', __('Nelayan :name tidak dapat dihapus karena tercatat sebagai pemilik kapal atau nahkoda trip penangkapan aktif.', ['name' => $name]));
        }

        $fisherman->delete();

        return redirect()->route('master.fishermen.index')
            ->with('success', __('Data nelayan :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status aktif nelayan
     */
    public function toggleStatus(Fisherman $fisherman): RedirectResponse
    {
        $fisherman->update(['is_active' => ! $fisherman->is_active]);

        $statusText = $fisherman->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __('Status nelayan :name berhasil :status.', [
                'name' => $fisherman->name,
                'status' => $statusText,
            ]));
    }
}
