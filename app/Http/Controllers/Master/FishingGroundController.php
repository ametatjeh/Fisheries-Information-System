<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\FishingGround;
use App\Models\Wppnri;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FishingGroundController extends Controller
{
    /**
     * Tampilkan daftar master Daerah Penangkapan Ikan (Fishing Grounds).
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $wppnriId = $request->query('wppnri_id');
        $status = $request->query('status');

        $counts = [
            'total' => FishingGround::count(),
            'active' => FishingGround::where('is_active', true)->count(),
            'with_coords' => FishingGround::whereNotNull('latitude')->whereNotNull('longitude')->count(),
            'wpp_count' => FishingGround::distinct('wppnri_id')->count('wppnri_id'),
        ];

        $grounds = FishingGround::with('wppnri')
            ->withCount('fishingTrips')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($wppnriId, function ($q, $w) {
                $q->where('wppnri_id', $w);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === '1');
            })
            ->orderBy('name')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        $wppList = Wppnri::where('is_active', true)->orderBy('code')->get();

        return view('master.fishing-grounds.index', compact(
            'grounds',
            'counts',
            'search',
            'wppnriId',
            'status',
            'wppList'
        ));
    }

    /**
     * Simpan data master Daerah Penangkapan Ikan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'wppnri_id' => ! empty($request->input('wppnri_id')) ? (int) $request->input('wppnri_id') : null,
            'latitude' => ($request->filled('latitude') && is_numeric($request->input('latitude'))) ? (float) $request->input('latitude') : null,
            'longitude' => ($request->filled('longitude') && is_numeric($request->input('longitude'))) ? (float) $request->input('longitude') : null,
            'code' => ! empty($request->input('code')) ? trim($request->input('code')) : null,
            'description' => ! empty($request->input('description')) ? trim($request->input('description')) : null,
        ]);

        $validated = $request->validate([
            'wppnri_id' => ['nullable', 'integer', 'exists:wppnri,id'],
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        FishingGround::create($validated);

        return redirect()->route('master.fishing-grounds.index')
            ->with('success', __('Daerah penangkapan :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data master Daerah Penangkapan Ikan.
     */
    public function update(Request $request, FishingGround $fishingGround): RedirectResponse
    {
        $request->merge([
            'wppnri_id' => ! empty($request->input('wppnri_id')) ? (int) $request->input('wppnri_id') : null,
            'latitude' => ($request->filled('latitude') && is_numeric($request->input('latitude'))) ? (float) $request->input('latitude') : null,
            'longitude' => ($request->filled('longitude') && is_numeric($request->input('longitude'))) ? (float) $request->input('longitude') : null,
            'code' => ! empty($request->input('code')) ? trim($request->input('code')) : null,
            'description' => ! empty($request->input('description')) ? trim($request->input('description')) : null,
        ]);

        $validated = $request->validate([
            'wppnri_id' => ['nullable', 'integer', 'exists:wppnri,id'],
            'code' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $fishingGround->update($validated);

        return redirect()->route('master.fishing-grounds.index')
            ->with('success', __('Data daerah penangkapan :name berhasil diperbarui.', ['name' => $fishingGround->name]));
    }

    /**
     * Hapus data master Daerah Penangkapan Ikan dengan proteksi relasi integritas.
     */
    public function destroy(FishingGround $fishingGround): RedirectResponse
    {
        if ($fishingGround->fishingTrips()->exists()) {
            return back()->with('error', __('Daerah penangkapan :name tidak dapat dihapus karena masih digunakan pada data trip penangkapan.', [
                'name' => $fishingGround->name,
            ]));
        }

        $name = $fishingGround->name;
        $fishingGround->delete();

        return redirect()->route('master.fishing-grounds.index')
            ->with('success', __('Daerah penangkapan :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status keaktifan daerah penangkapan ikan.
     */
    public function toggleStatus(FishingGround $fishingGround): RedirectResponse
    {
        $fishingGround->update(['is_active' => ! $fishingGround->is_active]);

        return back()->with('success', __('Status daerah penangkapan :name berhasil diperbarui.', ['name' => $fishingGround->name]));
    }
}
