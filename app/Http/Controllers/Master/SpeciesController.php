<?php

namespace App\Http\Controllers\Master;

use App\Exports\SpeciesExport;
use App\Exports\SpeciesTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Species;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SpeciesController extends Controller
{
    /**
     * Tampilkan halaman utama master data jenis ikan
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');

        $filterStatus = $request->query('status');
        $filterFishStat = $request->query('fishstat');
        $filterIsscaap = $request->query('isscaap');

        // Batasi nilai per_page
        $perPage = $request->integer('per_page', 25);
        if (! in_array($perPage, [10, 25, 50, 100, 250, 500])) {
            $perPage = 25;
        }

        // Ambil list unik ISSCAAP
        $isscaapList = Species::query()
            ->select('isscaap_code')
            ->whereNotNull('isscaap_code')
            ->where('isscaap_code', '!=', '')
            ->distinct()
            ->orderBy('isscaap_code')
            ->pluck('isscaap_code');

        // Query data jenis ikan
        $species = Species::query()
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fao_code', 'like', "%{$search}%")
                        ->orWhere('scientific_name', 'like', "%{$search}%")
                        ->orWhere('english_name', 'like', "%{$search}%")
                        ->orWhere('family', 'like', "%{$search}%");
                });
            })
            ->when($filterStatus !== null && $filterStatus !== '', function ($q) use ($filterStatus) {
                if (strtolower($filterStatus) === 'active') {
                    $q->where('is_active', 1);
                } elseif (strtolower($filterStatus) === 'inactive') {
                    $q->where('is_active', 0);
                }
            })
            ->when($filterFishStat !== null && $filterFishStat !== '', function ($q) use ($filterFishStat) {
                if (strtolower($filterFishStat) === 'yes') {
                    $q->where('is_statistical_item', 1);
                } elseif (strtolower($filterFishStat) === 'no') {
                    $q->where('is_statistical_item', 0);
                }
            })
            ->when($filterIsscaap, function ($q) use ($filterIsscaap) {
                $q->where('isscaap_code', $filterIsscaap);
            })
            ->orderByRaw("
                CASE
                    WHEN local_name_id IS NOT NULL AND TRIM(local_name_id) <> '' THEN 0
                    WHEN local_name_aceh IS NOT NULL AND TRIM(local_name_aceh) <> '' THEN 1
                    ELSE 2
                END
            ")
            ->orderBy('local_name_id')
            ->orderBy('local_name_aceh')
            ->orderBy('scientific_name')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('master.species.index', compact(
            'species',
            'search',
            'filterStatus',
            'filterFishStat',
            'filterIsscaap',
            'isscaapList',
            'perPage'
        ));
    }

    public function show(Species $species): View
    {
        return view('master.species.show', compact('species'));
    }

    /**
     * Endpoint API untuk pencarian dropdown species
     */
    public function search(Request $request)
    {
        $search = $request->query('q');
        $isActive = $request->query('is_active');
        $perPage = $request->integer('per_page', 25);
        if ($perPage > 100) {
            $perPage = 100;
        }

        $query = Species::query()
            ->select('id', 'fao_code', 'scientific_name', 'english_name', 'local_name_id', 'local_name_aceh')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fao_code', 'like', "%{$search}%")
                        ->orWhere('scientific_name', 'like', "%{$search}%")
                        ->orWhere('english_name', 'like', "%{$search}%")
                        ->orWhere('local_name_id', 'like', "%{$search}%")
                        ->orWhere('local_name_aceh', 'like', "%{$search}%");
                });
            })
            ->when($isActive !== null, function ($q) use ($isActive) {
                if ($isActive === 'true' || $isActive === '1' || $isActive === 1) {
                    $q->where('is_active', true);
                } elseif ($isActive === 'false' || $isActive === '0' || $isActive === 0) {
                    $q->where('is_active', false);
                }
            })
            ->orderBy('local_name_id');

        return response()->json($query->paginate($perPage));
    }

    /**
     * Simpan data jenis ikan baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fao_code' => ['nullable', 'string', 'max:3', 'unique:species,fao_code'],
            'local_name_id' => ['nullable', 'string', 'max:255'],
            'local_name_aceh' => ['nullable', 'string', 'max:255'],
            'scientific_name' => ['nullable', 'string', 'max:255'],
            'english_name' => ['nullable', 'string', 'max:255'],
            'family' => ['nullable', 'string', 'max:150'],
            'taxonomic_code' => ['nullable', 'string', 'max:15'],
            'isscaap_code' => ['nullable', 'string', 'max:2'],
            'functional_group' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'is_indonesia' => ['nullable', 'boolean'],
            'is_aceh' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['is_indonesia'] = $request->has('is_indonesia') ? true : false;
        $validated['is_aceh'] = $request->has('is_aceh') ? true : false;

        Species::create($validated);

        return redirect()->route('master.species.index')
            ->with('success', __('Jenis ikan :name berhasil ditambahkan.', ['name' => $validated['local_name_id'] ?? $validated['scientific_name']]));
    }

    /**
     * Perbarui data jenis ikan
     */
    public function update(Request $request, Species $species): RedirectResponse
    {
        $validated = $request->validate([
            'local_name_id' => ['nullable', 'string', 'max:255'],
            'local_name_variants' => ['nullable', 'string'],
            'indonesia_code' => ['nullable', 'string', 'max:50'],
            'is_indonesia' => ['nullable', 'boolean'],

            'local_name_aceh' => ['nullable', 'string', 'max:255'],

            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->has('is_active') ? true : false;
        $validated['is_indonesia'] = $request->has('is_indonesia') ? true : false;

        $species->update($validated);

        return redirect()->back()->with('success', 'Data lokal berhasil diperbarui.');
    }

    /**
     * Hapus data jenis ikan
     */
    public function destroy(Species $species): RedirectResponse
    {
        $name = $species->local_name_id ?? $species->scientific_name;
        $species->delete();

        return redirect()->route('master.species.index')
            ->with('success', __('Jenis ikan :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status aktif jenis ikan
     */
    public function toggleStatus(Species $species): RedirectResponse
    {
        $species->update(['is_active' => ! $species->is_active]);

        $statusText = $species->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __('Status jenis ikan :name berhasil :status.', [
                'name' => $species->local_name_id ?? $species->scientific_name,
                'status' => $statusText,
            ]));
    }

    /**
     * Bulk update status aktif/nonaktif jenis ikan
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:activate,deactivate'],
        ]);

        $isActive = $validated['action'] === 'activate';

        $count = Species::whereIn('id', $validated['ids'])->update(['is_active' => $isActive]);

        $statusText = $isActive ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __(':count jenis ikan berhasil :status.', [
                'count' => $count,
                'status' => $statusText,
            ]));
    }

    /**
     * Export data jenis ikan ke Excel
     */
    public function export(Request $request): BinaryFileResponse
    {
        $search = $request->query('search');
        $filterStatus = $request->query('status');
        $filterFishStat = $request->query('fishstat');
        $filterIsscaap = $request->query('isscaap');

        $date = now()->format('Y-m-d');
        $filename = "jenis-ikan-{$date}.xlsx";

        return Excel::download(new SpeciesExport($search, $filterStatus, $filterFishStat, $filterIsscaap), $filename);
    }

    /**
     * Tampilkan halaman Import Excel (Custom Import jika dibutuhkan di luar ASFIS command)
     */
    public function importView(Request $request): View
    {
        $importSessionId = $request->query('session_id');
        $previewData = null;

        if ($importSessionId && Cache::has('import_species_'.$importSessionId)) {
            $previewData = Cache::get('import_species_'.$importSessionId);
        }

        return view('master.species.import', compact('previewData', 'importSessionId'));
    }

    /**
     * Download template Import Excel
     */
    public function importTemplate(): BinaryFileResponse
    {
        return Excel::download(new SpeciesTemplateExport, 'template-import-species.xlsx');
    }

    public function importPreview(Request $request)
    {
        // Placeholder for legacy custom Excel import (since we rely on ASFIS command now).
        // If we want to keep it, we need to adapt it to the new `Species` model fields.
        return redirect()->back()->with('error', 'Silakan gunakan command `php artisan asfis:import` untuk mengimport data FAO ASFIS.');
    }

    public function importProcess(Request $request)
    {
        return redirect()->back()->with('error', 'Silakan gunakan command `php artisan asfis:import` untuk mengimport data FAO ASFIS.');
    }
}
