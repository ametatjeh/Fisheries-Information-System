<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FisherGroup;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FisherGroupController extends Controller
{
    /**
     * Tampilkan halaman utama master data Kelompok Nelayan / KUB
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $regencyId = $request->query('regency_id');

        // Ringkasan metrik statistik kelompok nelayan
        $totalGroups = FisherGroup::count();
        $totalMembers = (int) FisherGroup::sum('total_members');
        $avgMembers = $totalGroups > 0 ? round(FisherGroup::avg('total_members'), 1) : 0;
        $totalRegencies = FisherGroup::distinct('regency_id')->count('regency_id');

        $counts = [
            'total_groups' => $totalGroups,
            'total_members' => $totalMembers,
            'avg_members' => $avgMembers,
            'total_regencies' => $totalRegencies,
        ];

        // Query data kelompok nelayan dengan relasi
        $groups = FisherGroup::with(['regency', 'district', 'village'])
            ->withCount('members')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('leader_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($regencyId, function ($q, $r) {
                $q->where('regency_id', $r);
            })
            ->orderBy('code')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Data wilayah untuk dropdown filter & modal form
        $aceh = Province::where('code', '11')->first();
        $regencies = $aceh
            ? $aceh->regencies()->orderBy('name')->get(['id', 'code', 'name', 'type'])
            : Regency::orderBy('name')->get(['id', 'code', 'name', 'type']);

        return view('master.fisher-groups.index', compact(
            'groups',
            'counts',
            'search',
            'regencyId',
            'regencies',
            'aceh'
        ));
    }

    /**
     * Simpan data kelompok nelayan baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:30', 'unique:fisher_groups,code'],
            'name' => ['required', 'string', 'max:150'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'leader_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'established_date' => ['nullable', 'date'],
            'total_members' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['total_members'] = $validated['total_members'] ?? 0;

        FisherGroup::create($validated);

        return redirect()->route('master.fisher-groups.index')
            ->with('success', __('Kelompok nelayan :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data kelompok nelayan
     */
    public function update(Request $request, FisherGroup $fisherGroup): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:30', 'unique:fisher_groups,code,'.$fisherGroup->id],
            'name' => ['required', 'string', 'max:150'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],
            'leader_name' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'established_date' => ['nullable', 'date'],
            'total_members' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['total_members'] = $validated['total_members'] ?? 0;

        $fisherGroup->update($validated);

        return redirect()->route('master.fisher-groups.index')
            ->with('success', __('Data kelompok nelayan :name berhasil diperbarui.', ['name' => $fisherGroup->name]));
    }

    /**
     * Hapus data kelompok nelayan
     */
    public function destroy(FisherGroup $fisherGroup): RedirectResponse
    {
        $name = $fisherGroup->name;

        // Cek keterkaitan dengan data nelayan
        if ($fisherGroup->members()->exists()) {
            return redirect()->route('master.fisher-groups.index')
                ->with('error', __('Kelompok :name tidak dapat dihapus karena masih memiliki data anggota nelayan terdaftar.', ['name' => $name]));
        }

        $fisherGroup->delete();

        return redirect()->route('master.fisher-groups.index')
            ->with('success', __('Kelompok nelayan :name berhasil dihapus.', ['name' => $name]));
    }
}
