<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Fisherman;
use App\Models\FishingGear;
use App\Models\LandingSite;
use App\Models\Vessel;
use App\Models\VesselType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VesselController extends Controller
{
    /**
     * Tampilkan halaman utama master data kapal penangkap ikan
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $vesselType = $request->query('vessel_type');
        $vesselTypeId = $request->query('vessel_type_id');
        $homeportSiteId = $request->query('homeport_site_id');
        $primaryGearId = $request->query('primary_gear_id');
        $status = $request->query('status');

        // Ringkasan metrik statistik armada kapal
        $totalVessels = Vessel::count();
        $totalGt = (float) Vessel::sum('gross_tonnage');
        $counts = [
            'total' => $totalVessels,
            'samudera_gt30' => Vessel::where('gross_tonnage', '>=', 30)->count(),
            'kapal_gt10_30' => Vessel::whereBetween('gross_tonnage', [10, 29.99])->count(),
            'sedang_gt5_10' => Vessel::whereBetween('gross_tonnage', [5, 9.99])->count(),
            'motor_tempel' => Vessel::where('vessel_type', 'motor_tempel')->orWhere(function ($q) {
                $q->where('gross_tonnage', '<', 5)->where('gross_tonnage', '>', 0);
            })->count(),
            'total_gt' => round($totalGt, 1),
            'active' => Vessel::where('is_active', true)->count(),
        ];

        // Query kapal dengan relasi
        $vessels = Vessel::with(['owner', 'vesselType', 'primaryGear', 'homeportSite'])
            ->withCount('fishingTrips')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('registration_number', 'like', "%{$search}%")
                        ->orWhere('engine_brand', 'like', "%{$search}%")
                        ->orWhereHas('owner', function ($own) use ($search) {
                            $own->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($vesselType, function ($q, $vt) {
                $q->where('vessel_type', $vt);
            })
            ->when($vesselTypeId, function ($q, $vtid) {
                $q->where('vessel_type_id', $vtid);
            })
            ->when($homeportSiteId, function ($q, $hp) {
                $q->where('homeport_site_id', $hp);
            })
            ->when($primaryGearId, function ($q, $pg) {
                $q->where('primary_gear_id', $pg);
            })
            ->when($status !== null && $status !== '', function ($q) use ($status) {
                $q->where('is_active', $status === '1');
            })
            ->orderByDesc('gross_tonnage')
            ->paginate($this->getPerPage($request))
            ->withQueryString();

        // Opsi label tipe armada kapal
        $generalTypes = [
            'kapal_motor' => [
                'label' => 'Kapal Motor (Inboard)',
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
                'icon' => '🚢',
            ],
            'motor_tempel' => [
                'label' => 'Perahu Motor Tempel',
                'badge' => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                'icon' => '🚤',
            ],
            'tanpa_motor' => [
                'label' => 'Perahu Tanpa Motor',
                'badge' => 'bg-slate-100 text-slate-700 border-slate-200',
                'icon' => '🛶',
            ],
        ];

        // Master lists untuk filter dan modal form
        $owners = Fisherman::orderBy('name')->get(['id', 'nik', 'name', 'fisher_type']);
        $vesselTypes = VesselType::where('is_active', true)->orderBy('code')->get();
        $landingSites = LandingSite::where('is_active', true)->orderBy('name')->get();
        $fishingGears = FishingGear::where('is_active', true)->orderBy('name')->get();

        return view('master.vessels.index', compact(
            'vessels',
            'counts',
            'search',
            'vesselType',
            'vesselTypeId',
            'homeportSiteId',
            'primaryGearId',
            'status',
            'generalTypes',
            'owners',
            'vesselTypes',
            'landingSites',
            'fishingGears'
        ));
    }

    /**
     * Simpan data kapal baru
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'registration_number' => ['nullable', 'string', 'max:50', 'unique:vessels,registration_number'],
            'owner_id' => ['nullable', 'exists:fishers,id'],
            'vessel_type' => ['required', 'in:tanpa_motor,motor_tempel,kapal_motor'],
            'vessel_type_id' => ['nullable', 'exists:vessel_types,id'],
            'gross_tonnage' => ['required', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'engine_power_hp' => ['nullable', 'numeric', 'min:0'],
            'engine_brand' => ['nullable', 'string', 'max:100'],
            'build_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'homeport_site_id' => ['nullable', 'exists:landing_sites,id'],
            'primary_gear_id' => ['nullable', 'exists:fishing_gears,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        Vessel::create($validated);

        return redirect()->route('master.vessels.index')
            ->with('success', __('Kapal :name berhasil ditambahkan.', ['name' => $validated['name']]));
    }

    /**
     * Perbarui data kapal
     */
    public function update(Request $request, Vessel $vessel): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'registration_number' => ['nullable', 'string', 'max:50', 'unique:vessels,registration_number,'.$vessel->id],
            'owner_id' => ['nullable', 'exists:fishers,id'],
            'vessel_type' => ['required', 'in:tanpa_motor,motor_tempel,kapal_motor'],
            'vessel_type_id' => ['nullable', 'exists:vessel_types,id'],
            'gross_tonnage' => ['required', 'numeric', 'min:0'],
            'length' => ['nullable', 'numeric', 'min:0'],
            'width' => ['nullable', 'numeric', 'min:0'],
            'depth' => ['nullable', 'numeric', 'min:0'],
            'engine_power_hp' => ['nullable', 'numeric', 'min:0'],
            'engine_brand' => ['nullable', 'string', 'max:100'],
            'build_year' => ['nullable', 'integer', 'min:1950', 'max:'.(date('Y') + 1)],
            'homeport_site_id' => ['nullable', 'exists:landing_sites,id'],
            'primary_gear_id' => ['nullable', 'exists:fishing_gears,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $vessel->update($validated);

        return redirect()->route('master.vessels.index')
            ->with('success', __('Data kapal :name berhasil diperbarui.', ['name' => $vessel->name]));
    }

    /**
     * Hapus data kapal
     */
    public function destroy(Vessel $vessel): RedirectResponse
    {
        $name = $vessel->name;

        // Cek keterkaitan dengan riwayat trip penangkapan
        if ($vessel->fishingTrips()->exists()) {
            return redirect()->route('master.vessels.index')
                ->with('error', __('Kapal :name tidak dapat dihapus karena memiliki riwayat trip penangkapan ikan di logbook.', ['name' => $name]));
        }

        $vessel->delete();

        return redirect()->route('master.vessels.index')
            ->with('success', __('Kapal :name berhasil dihapus.', ['name' => $name]));
    }

    /**
     * Toggle status operasional kapal
     */
    public function toggleStatus(Vessel $vessel): RedirectResponse
    {
        $vessel->update(['is_active' => ! $vessel->is_active]);

        $statusText = $vessel->is_active ? __('diaktifkan') : __('dinonaktifkan');

        return redirect()->back()
            ->with('success', __('Status operasional kapal :name berhasil :status.', [
                'name' => $vessel->name,
                'status' => $statusText,
            ]));
    }
}
