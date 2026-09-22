<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>⚓</span>
            <span>{{ __('Master Data Landing Site (Pangkalan Pendaratan Ikan)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showStatusModal: false,
        statusTarget: { id: null, name: '', is_active: false, actionUrl: '' },
        editItem: {},
        editAction: '',
        
        // Cascading dropdown data for Create modal
        createProvinceId: '{{ $aceh?->id ?? '' }}',
        createRegencyId: '',
        createDistrictId: '',
        createVillageId: '',
        createDistricts: [],
        createVillages: [],
        isLoadingDistricts: false,
        isLoadingVillages: false,

        // Cascading dropdown data for Edit modal
        editDistricts: [],
        editVillages: [],
        isLoadingEditDistricts: false,
        isLoadingEditVillages: false,

        async onRegencyChange(regencyId, mode = 'create') {
            if (!regencyId) {
                if (mode === 'create') {
                    this.createDistricts = [];
                    this.createVillages = [];
                    this.createDistrictId = '';
                    this.createVillageId = '';
                } else {
                    this.editDistricts = [];
                    this.editVillages = [];
                    this.editItem.district_id = '';
                    this.editItem.village_id = '';
                }
                return;
            }

            try {
                if (mode === 'create') this.isLoadingDistricts = true;
                else this.isLoadingEditDistricts = true;

                const res = await fetch(`/master/wilayah/api/districts/${regencyId}`);
                const data = await res.json();

                if (mode === 'create') {
                    this.createDistricts = data;
                    this.createVillages = [];
                    this.createDistrictId = '';
                    this.createVillageId = '';
                } else {
                    this.editDistricts = data;
                }
            } catch (err) {
                console.error('Failed to fetch districts', err);
            } finally {
                if (mode === 'create') this.isLoadingDistricts = false;
                else this.isLoadingEditDistricts = false;
            }
        },

        async onDistrictChange(districtId, mode = 'create') {
            if (!districtId) {
                if (mode === 'create') {
                    this.createVillages = [];
                    this.createVillageId = '';
                } else {
                    this.editVillages = [];
                    this.editItem.village_id = '';
                }
                return;
            }

            try {
                if (mode === 'create') this.isLoadingVillages = true;
                else this.isLoadingEditVillages = true;

                const res = await fetch(`/master/wilayah/api/villages/${districtId}`);
                const data = await res.json();

                if (mode === 'create') {
                    this.createVillages = data;
                    this.createVillageId = '';
                } else {
                    this.editVillages = data;
                }
            } catch (err) {
                console.error('Failed to fetch villages', err);
            } finally {
                if (mode === 'create') this.isLoadingVillages = false;
                else this.isLoadingEditVillages = false;
            }
        },

        async openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editAction = actionUrl;
            this.editDistricts = [];
            this.editVillages = [];
            this.showEditModal = true;

            // Load districts & villages for existing regency
            if (this.editItem.regency_id) {
                await this.onRegencyChange(this.editItem.regency_id, 'edit');
                if (this.editItem.district_id) {
                    await this.onDistrictChange(this.editItem.district_id, 'edit');
                }
            }
        },

        openStatusModal(item, actionUrl) {
            this.statusTarget = {
                id: item.id,
                name: item.name,
                is_active: item.is_active,
                actionUrl: actionUrl
            };
            this.showStatusModal = true;
        }
    }">


        @if ($errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-semibold text-sm">
                    <span>⚠️</span>
                    <span>{{ __('Terdapat kesalahan input formulir:') }}</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Intro Header Banner --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">⚓</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🚢</span>
                        <span>{{ __('Katalog Pangkalan & Pelabuhan Perikanan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Pangkalan Pendaratan Ikan & TPI (Landing Sites)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi titik pelabuhan dan pangkalan pendaratan ikan (PPS, PPN, PPP, PPI, TPI, dan Pangkalan Tradisional Pesisir) di seluruh pesisir Aceh untuk pelacakan kapal, logbook, dan statistik pendaratan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Tempat Pendaratan') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            {{-- All --}}
            <a href="{{ route('master.landing-sites.index') }}"
               class="p-3.5 rounded-xl border transition-all {{ empty($siteType) ? 'bg-ocean-50/90 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-gray-800">{{ $counts['total'] }}</span>
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">{{ $counts['active'] }} Aktif</span>
                </div>
                <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Semua Pangkalan') }}</div>
            </a>

            {{-- PPS --}}
            <a href="{{ route('master.landing-sites.index', ['site_type' => 'PPS']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $siteType === 'PPS' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-purple-700">{{ $counts['PPS'] }}</span>
                    <span class="text-base">🏢</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('PPS (Samudera)') }}</div>
            </a>

            {{-- PPN --}}
            <a href="{{ route('master.landing-sites.index', ['site_type' => 'PPN']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $siteType === 'PPN' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-blue-700">{{ $counts['PPN'] }}</span>
                    <span class="text-base">🏬</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('PPN (Nusantara)') }}</div>
            </a>

            {{-- PPP --}}
            <a href="{{ route('master.landing-sites.index', ['site_type' => 'PPP']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $siteType === 'PPP' ? 'bg-cyan-50 border-cyan-300 ring-2 ring-cyan-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-cyan-700">{{ $counts['PPP'] }}</span>
                    <span class="text-base">🏖️</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('PPP (Pantai)') }}</div>
            </a>

            {{-- PPI --}}
            <a href="{{ route('master.landing-sites.index', ['site_type' => 'PPI']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $siteType === 'PPI' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['PPI'] }}</span>
                    <span class="text-base">⚓</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('PPI (Pangkalan)') }}</div>
            </a>

            {{-- TPI & Tradisional --}}
            <a href="{{ route('master.landing-sites.index', ['site_type' => 'TPI']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $siteType === 'TPI' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-amber-700">{{ $counts['TPI'] + $counts['tradisional'] }}</span>
                    <span class="text-base">🏪</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('TPI & Tradisional') }}</div>
            </a>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.landing-sites.index') }}" class="flex flex-col md:flex-row gap-3">
                {{-- Search Input --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari nama tempat pendaratan, kode (PPS-LMP, TPI-01), atau alamat...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                {{-- Site Type Filter --}}
                <div class="w-full md:w-56">
                    <select name="site_type" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Tipe Pelabuhan') }}</option>
                        @foreach($siteTypes as $key => $type)
                            <option value="{{ $key }}" {{ $siteType === $key ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Regency Filter --}}
                <div class="w-full md:w-52">
                    <select name="regency_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kab / Kota') }}</option>
                        @foreach($regencies as $r)
                            <option value="{{ $r->id }}" {{ (string)$regencyId === (string)$r->id ? 'selected' : '' }}>
                                {{ ucfirst($r->type) }} {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-36">
                    <select name="status" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Filter') }}
                    </button>
                    @if($search || $siteType || $regencyId || ($status !== null && $status !== ''))
                        <a href="{{ route('master.landing-sites.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Landing Sites Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 w-28">{{ __('Kode') }}</th>
                            <th class="py-3 px-4">{{ __('Nama Tempat Pendaratan / TPI') }}</th>
                            <th class="py-3 px-4">{{ __('Tipe / Kelas') }}</th>
                            <th class="py-3 px-4">{{ __('Wilayah Administrasi') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Koordinat GIS') }}</th>
                            <th class="py-3 px-4 text-center w-28">{{ __('Status') }}</th>
                            <th class="py-3 px-4 text-right w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($sites as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                {{-- Index # --}}
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $sites->firstItem() + $index }}
                                </td>

                                {{-- Code --}}
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold px-2.5 py-1 rounded-md bg-ocean-50 text-ocean-700 border border-ocean-200 text-xs tracking-wider">
                                        {{ $item->code }}
                                    </span>
                                </td>

                                {{-- Name & Address --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $item->name }}</div>
                                    @if($item->address)
                                        <div class="text-xs text-gray-500 line-clamp-1 mt-0.5" title="{{ $item->address }}">
                                            📍 {{ $item->address }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Site Type --}}
                                <td class="py-3.5 px-4">
                                    @php
                                        $typeInfo = $siteTypes[$item->site_type] ?? [
                                            'short' => $item->site_type,
                                            'badge' => 'bg-gray-100 text-gray-800 border-gray-200',
                                            'class' => ''
                                        ];
                                    @endphp
                                    <div class="flex flex-col items-start gap-1">
                                        <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full border {{ $typeInfo['badge'] }}">
                                            {{ $typeInfo['short'] }}
                                        </span>
                                        <span class="text-[11px] text-gray-400">
                                            {{ $typeInfo['class'] }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Region --}}
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-medium text-gray-800">
                                        {{ $item->regency?->name ? ucfirst($item->regency->type) . ' ' . $item->regency->name : '-' }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        @if($item->district) Kec. {{ $item->district->name }} @endif
                                        @if($item->village) • {{ $item->village->name }} @endif
                                    </div>
                                </td>

                                {{-- Coordinates & Map Preview --}}
                                <td class="py-3.5 px-4 text-center">
                                    @if($item->latitude && $item->longitude)
                                        <div class="inline-flex flex-col items-center">
                                            <a href="https://www.openstreetmap.org/?mlat={{ $item->latitude }}&mlon={{ $item->longitude }}#map=16/{{ $item->latitude }}/{{ $item->longitude }}"
                                               target="_blank"
                                               class="inline-flex items-center gap-1 px-2 py-1 rounded bg-ocean-50 text-ocean-700 hover:bg-ocean-100 border border-ocean-200 text-xs font-mono transition-colors"
                                               title="{{ __('Buka Peta Lokasi (OpenStreetMap)') }}">
                                                <span>🗺️</span>
                                                <span>{{ number_format($item->latitude, 4) }}, {{ number_format($item->longitude, 4) }}</span>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ __('Belum ada GPS') }}</span>
                                    @endif
                                </td>

                                {{-- Active Status Toggle with Confirmation Popup --}}
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            @click="openStatusModal({{ json_encode(['id' => $item->id, 'name' => $item->name . ($item->code ? ' (' . $item->code . ')' : ''), 'is_active' => (bool)$item->is_active]) }}, '{{ route('master.landing-sites.toggle-status', $item->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer shadow-xs {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border border-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-200' }}"
                                            title="{{ __('Klik untuk ubah status aktif/nonaktif') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $item->is_active ? __('Aktif') : __('Nonaktif') }}</span>
                                    </button>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('master.landing-sites.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Tempat Pendaratan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('master.landing-sites.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data pangkalan :name?', ['name' => $item->name]) }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                                    title="{{ __('Hapus') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">⚓</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data tempat pendaratan ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci lain atau bersihkan filter pencarian.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($sites->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $sites->links() }}
                </div>
            @endif
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH DATA (CREATE MODAL)                                  --}}
        {{-- ================================================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showCreateModal = false">
            <div @click.away="showCreateModal = false"
                 class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">⚓</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Tempat Pendaratan Ikan (TPI)') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.landing-sites.store') }}" class="space-y-4 mt-4">
                    @csrf

                    {{-- Baris 1: Kode & Nama --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Pangkalan') }} *</label>
                            <input type="text"
                                   name="code"
                                   required
                                   placeholder="Contoh: PPS-LMP"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Tempat Pendaratan / TPI') }} *</label>
                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="Contoh: PPS Lampulo Banda Aceh"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Tipe Pelabuhan / Kategori --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi / Tipe Pelabuhan') }} *</label>
                        <select name="site_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            @foreach($siteTypes as $val => $t)
                                <option value="{{ $val }}" {{ $val === 'TPI' ? 'selected' : '' }}>
                                    {{ $t['label'] }} — {{ $t['class'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Baris 3: Lokasi Administratif (Provinsi & Kab/Kota) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Provinsi') }} *</label>
                            <select name="province_id" x-model="createProvinceId" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($provinces as $p)
                                    <option value="{{ $p->id }}" {{ $aceh && $p->id === $aceh->id ? 'selected' : '' }}>
                                        {{ $p->code }} - {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kabupaten / Kota') }} *</label>
                            <select name="regency_id"
                                    x-model="createRegencyId"
                                    @change="onRegencyChange($event.target.value, 'create')"
                                    required
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kabupaten/Kota') }} --</option>
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Kecamatan & Desa (Dinamis dari API) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                {{ __('Kecamatan') }}
                                <span x-show="isLoadingDistricts" class="text-xs text-ocean-600 font-normal">({{ __('Memuat...') }})</span>
                            </label>
                            <select name="district_id"
                                    x-model="createDistrictId"
                                    @change="onDistrictChange($event.target.value, 'create')"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kecamatan (Opsional)') }} --</option>
                                <template x-for="d in createDistricts" :key="d.id">
                                    <option :value="d.id" x-text="d.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                {{ __('Gampong / Desa') }}
                                <span x-show="isLoadingVillages" class="text-xs text-ocean-600 font-normal">({{ __('Memuat...') }})</span>
                            </label>
                            <select name="village_id"
                                    x-model="createVillageId"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Desa (Opsional)') }} --</option>
                                <template x-for="v in createVillages" :key="v.id">
                                    <option :value="v.id" x-text="v.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 5: Koordinat GIS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Latitude (Lintang)') }}</label>
                            <input type="number"
                                   step="0.0000001"
                                   name="latitude"
                                   placeholder="Contoh: 5.5866120"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Longitude (Bujur)') }}</label>
                            <input type="number"
                                   step="0.0000001"
                                   name="longitude"
                                   placeholder="Contoh: 95.3262450"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Alamat Fisik --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Lengkap / Keterangan Lokasi') }}</label>
                        <textarea name="address"
                                  rows="2"
                                  placeholder="{{ __('Masukkan alamat dermaga atau petunjuk arah ke pangkalan...') }}"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Tempat pendaratan aktif dan beroperasi') }}
                        </label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Tempat Pendaratan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL UBAH DATA (EDIT MODAL)                                      --}}
        {{-- ================================================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Tempat Pendaratan Ikan') }}</h3>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="space-y-4 mt-4">
                    @csrf
                    @method('PUT')

                    {{-- Baris 1: Kode & Nama --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Pangkalan') }} *</label>
                            <input type="text"
                                   name="code"
                                   x-model="editItem.code"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Tempat Pendaratan / TPI') }} *</label>
                            <input type="text"
                                   name="name"
                                   x-model="editItem.name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Tipe Pelabuhan / Kategori --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi / Tipe Pelabuhan') }} *</label>
                        <select name="site_type" x-model="editItem.site_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            @foreach($siteTypes as $val => $t)
                                <option value="{{ $val }}">
                                    {{ $t['label'] }} — {{ $t['class'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Baris 3: Lokasi Administratif (Provinsi & Kab/Kota) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Provinsi') }} *</label>
                            <select name="province_id" x-model="editItem.province_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($provinces as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->code }} - {{ $p->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kabupaten / Kota') }} *</label>
                            <select name="regency_id"
                                    x-model="editItem.regency_id"
                                    @change="onRegencyChange($event.target.value, 'edit')"
                                    required
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kabupaten/Kota') }} --</option>
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Kecamatan & Desa --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                {{ __('Kecamatan') }}
                                <span x-show="isLoadingEditDistricts" class="text-xs text-ocean-600 font-normal">({{ __('Memuat...') }})</span>
                            </label>
                            <select name="district_id"
                                    x-model="editItem.district_id"
                                    @change="onDistrictChange($event.target.value, 'edit')"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kecamatan (Opsional)') }} --</option>
                                <template x-for="d in editDistricts" :key="d.id">
                                    <option :value="d.id" :selected="d.id == editItem.district_id" x-text="d.name"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                                {{ __('Gampong / Desa') }}
                                <span x-show="isLoadingEditVillages" class="text-xs text-ocean-600 font-normal">({{ __('Memuat...') }})</span>
                            </label>
                            <select name="village_id"
                                    x-model="editItem.village_id"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Desa (Opsional)') }} --</option>
                                <template x-for="v in editVillages" :key="v.id">
                                    <option :value="v.id" :selected="v.id == editItem.village_id" x-text="v.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 5: Koordinat GIS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Latitude (Lintang)') }}</label>
                            <input type="number"
                                   step="0.0000001"
                                   name="latitude"
                                   x-model="editItem.latitude"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Longitude (Bujur)') }}</label>
                            <input type="number"
                                   step="0.0000001"
                                   name="longitude"
                                   x-model="editItem.longitude"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Alamat Fisik --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Lengkap / Keterangan Lokasi') }}</label>
                        <textarea name="address"
                                  x-model="editItem.address"
                                  rows="2"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editItem.is_active"
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="edit_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Tempat pendaratan aktif dan beroperasi') }}
                        </label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL KONFIRMASI STATUS (ACTIVE / INACTIVE POPUP)                 --}}
        {{-- ================================================================= --}}
        <div x-show="showStatusModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
             @keydown.escape.window="showStatusModal = false">
            <div @click.away="showStatusModal = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 text-center animate-in fade-in zoom-in-95 duration-150">

                {{-- Icon Badge --}}
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-3xl shadow-inner transition-colors"
                     :class="statusTarget.is_active ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'">
                    <span x-show="statusTarget.is_active">⏸️</span>
                    <span x-show="!statusTarget.is_active">✅</span>
                </div>

                {{-- Title --}}
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    <span x-show="statusTarget.is_active">{{ __('Nonaktifkan Pangkalan Pendaratan?') }}</span>
                    <span x-show="!statusTarget.is_active">{{ __('Aktifkan Pangkalan Pendaratan?') }}</span>
                </h3>

                {{-- Target Site Name --}}
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    {{ __('Apakah Anda yakin ingin mengubah status operasional pangkalan pendaratan:') }}
                    <br>
                    <span class="font-bold text-gray-900 text-base mt-2 inline-block bg-gray-50 px-3.5 py-1.5 rounded-xl border border-gray-200" x-text="statusTarget.name"></span>
                </p>

                {{-- Information Box --}}
                <div class="p-3.5 rounded-xl text-xs text-left mb-6"
                     :class="statusTarget.is_active ? 'bg-amber-50/80 border border-amber-200 text-amber-800' : 'bg-emerald-50/80 border border-emerald-200 text-emerald-800'">
                    <div class="flex items-start gap-2.5">
                        <span class="text-base leading-none">💡</span>
                        <div class="leading-normal">
                            <span x-show="statusTarget.is_active">
                                {{ __('Pangkalan yang berstatus Nonaktif tidak akan muncul dalam opsi pelabuhan pangkalan kapal (homeport) dan pelabuhan pendaratan baru.') }}
                            </span>
                            <span x-show="!statusTarget.is_active">
                                {{ __('Pangkalan akan kembali aktif dan dapat dipilih dalam pendataan armada kapal, pendaratan ikan, serta peta spasial GIS.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            @click="showStatusModal = false"
                            class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                        {{ __('Batal') }}
                    </button>

                    <form method="POST" :action="statusTarget.actionUrl" class="inline w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="statusTarget.is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                            <span x-show="statusTarget.is_active">⏸️ {{ __('Ya, Nonaktifkan') }}</span>
                            <span x-show="!statusTarget.is_active">✅ {{ __('Ya, Aktifkan') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
