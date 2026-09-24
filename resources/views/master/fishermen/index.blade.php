<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>👨‍🌾</span>
            <span>{{ __('Master Data Nelayan (Pelaku Usaha Penangkapan Ikan)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showStatusModal: false,
        statusTarget: { id: null, name: '', is_active: false, actionUrl: '' },
        editItem: {},
        editAction: '',

        // Cascading dropdowns for Create modal
        createProvinceId: '{{ $aceh?->id ?? '' }}',
        createRegencyId: '',
        createDistrictId: '',
        createVillageId: '',
        createDistricts: [],
        createVillages: [],
        isLoadingDistricts: false,
        isLoadingVillages: false,

        // Cascading dropdowns for Edit modal
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">👨‍🌾</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🆔</span>
                        <span>{{ __('Database Pelaku Usaha Kelautan & Perikanan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Katalog Data Nelayan & Awak Kapal Perikanan') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi identitas NIK kependudukan, nomor Kartu KUSUKA resmi KKP, klasifikasi peran melaut (Pemilik Kapal, Nahkoda/Jurumudi, ABK, Nelayan Tanpa Perahu), dan keanggotaan Kelompok Usaha Bersama (KUB).') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Nelayan') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics & Role Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            {{-- All --}}
            <a href="{{ route('master.fishermen.index') }}"
               class="p-3.5 rounded-xl border transition-all {{ empty($fisherType) ? 'bg-ocean-50/90 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-gray-800">{{ $counts['total'] }}</span>
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">{{ $counts['active'] }} Aktif</span>
                </div>
                <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Semua Nelayan') }}</div>
            </a>

            {{-- Pemilik --}}
            <a href="{{ route('master.fishermen.index', ['fisher_type' => 'pemilik']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $fisherType === 'pemilik' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-amber-700">{{ $counts['pemilik'] }}</span>
                    <span class="text-base">👑</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Pemilik Kapal') }}</div>
            </a>

            {{-- Nahkoda --}}
            <a href="{{ route('master.fishermen.index', ['fisher_type' => 'nahkoda_jurumudi']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $fisherType === 'nahkoda_jurumudi' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-blue-700">{{ $counts['nahkoda'] }}</span>
                    <span class="text-base">🧭</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Nahkoda / Jurumudi') }}</div>
            </a>

            {{-- ABK --}}
            <a href="{{ route('master.fishermen.index', ['fisher_type' => 'abk']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $fisherType === 'abk' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['abk'] }}</span>
                    <span class="text-base">⚓</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Anak Buah Kapal (ABK)') }}</div>
            </a>

            {{-- Tanpa Perahu --}}
            <a href="{{ route('master.fishermen.index', ['fisher_type' => 'nelayan_tanpa_perahu']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $fisherType === 'nelayan_tanpa_perahu' ? 'bg-slate-50 border-slate-300 ring-2 ring-slate-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-slate-700">{{ $counts['tanpa_perahu'] }}</span>
                    <span class="text-base">🎣</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Tanpa Perahu') }}</div>
            </a>

            {{-- With KUSUKA --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-cyan-700">{{ $counts['with_kusuka'] }}</span>
                    <span class="text-base">💳</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Kartu KUSUKA KKP') }}</div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.fishermen.index') }}" class="flex flex-col md:flex-row gap-3">
                {{-- Search --}}
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari nama nelayan, NIK, nomor KUSUKA, telepon, atau alamat...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                {{-- Fisher Type --}}
                <div class="w-full md:w-48">
                    <select name="fisher_type" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Peran') }}</option>
                        @foreach($fisherTypes as $key => $ft)
                            <option value="{{ $key }}" {{ $fisherType === $key ? 'selected' : '' }}>
                                {{ $ft['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- KUB Group --}}
                <div class="w-full md:w-48">
                    <select name="fisher_group_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kelompok KUB') }}</option>
                        @foreach($fisherGroups as $fg)
                            <option value="{{ $fg->id }}" {{ (string)$fisherGroupId === (string)$fg->id ? 'selected' : '' }}>
                                {{ $fg->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Regency --}}
                <div class="w-full md:w-44">
                    <select name="regency_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kab/Kota') }}</option>
                        @foreach($regencies as $r)
                            <option value="{{ $r->id }}" {{ (string)$regencyId === (string)$r->id ? 'selected' : '' }}>
                                {{ ucfirst($r->type) }} {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Status --}}
                <div class="w-full md:w-32">
                    <select name="status" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Status') }}</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Filter') }}
                    </button>
                    @if($search || $fisherType || $fisherGroupId || $regencyId || ($status !== null && $status !== ''))
                        <a href="{{ route('master.fishermen.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Fishermen Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-ocean-900 border-b border-ocean-950 text-xs font-semibold text-white uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center text-white">#</th>
                            <th class="py-3.5 px-4 w-44 text-white">{{ __('NIK & KUSUKA') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Nama Nelayan & Kontak') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Peran Nelayan') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Kelompok KUB') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Domisili Wilayah') }}</th>
                            <th class="py-3.5 px-4 text-center w-24 text-white">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4 text-right w-24 text-white">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($fishermen as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                {{-- Index # --}}
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $fishermen->firstItem() + $index }}
                                </td>

                                {{-- NIK & KUSUKA --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-mono text-xs font-bold text-gray-800">
                                        {{ $item->nik }}
                                    </div>
                                    @if($item->kusuka_number)
                                        <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded bg-cyan-50 text-cyan-700 border border-cyan-200 text-[11px] font-mono" title="{{ __('Nomor Registrasi KUSUKA KKP') }}">
                                            <span>💳</span>
                                            <span>{{ $item->kusuka_number }}</span>
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">{{ __('Tanpa KUSUKA') }}</span>
                                    @endif
                                </td>

                                {{-- Name, Gender, Phone --}}
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-gray-900">{{ $item->name }}</span>
                                        <span class="text-xs px-1.5 py-0.2 rounded font-semibold {{ $item->gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}" title="{{ $item->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}">
                                            {{ $item->gender }}
                                        </span>
                                    </div>
                                    @if($item->phone)
                                        <div class="mt-0.5">
                                            <a href="tel:{{ $item->phone }}" class="inline-flex items-center gap-1 text-xs text-ocean-600 hover:text-ocean-700">
                                                <span>📞</span>
                                                <span class="font-mono">{{ $item->phone }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                {{-- Fisher Role --}}
                                <td class="py-3.5 px-4">
                                    @php
                                        $typeData = $fisherTypes[$item->fisher_type] ?? [
                                            'label' => ucfirst(str_replace('_', ' ', $item->fisher_type)),
                                            'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
                                            'icon'  => '⚓'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full border {{ $typeData['badge'] }}">
                                        <span>{{ $typeData['icon'] }}</span>
                                        <span>{{ $typeData['label'] }}</span>
                                    </span>
                                </td>

                                {{-- Fisher Group --}}
                                <td class="py-3.5 px-4">
                                    @if($item->fisherGroup)
                                        <div class="font-medium text-xs text-ocean-800">
                                            {{ $item->fisherGroup->name }}
                                        </div>
                                        <span class="text-[10px] text-gray-400 font-mono">{{ $item->fisherGroup->code }}</span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ __('Individu / Mandiri') }}</span>
                                    @endif
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

                                {{-- Active Status Toggle with Confirmation Popup --}}
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            @click="openStatusModal({{ json_encode(['id' => $item->id, 'name' => $item->name, 'is_active' => (bool)$item->is_active]) }}, '{{ route('master.fishermen.toggle-status', $item->id) }}')"
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
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('master.fishermen.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Nelayan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('master.fishermen.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data nelayan :name?', ['name' => $item->name]) }}');" class="inline">
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
                                    <div class="text-4xl mb-2">👨‍🌾</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data nelayan ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci lain atau bersihkan filter.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$fishermen" />
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
                        <span class="text-xl">👨‍🌾</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Data Nelayan Baru') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.fishermen.store') }}" class="space-y-4 mt-4">
                    @csrf

                    {{-- Baris 1: NIK & KUSUKA --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Induk Kependudukan (NIK)') }} *</label>
                            <input type="text"
                                   name="nik"
                                   required
                                   maxlength="16"
                                   placeholder="Contoh: 1171021204780001"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Kartu KUSUKA KKP') }}</label>
                            <input type="text"
                                   name="kusuka_number"
                                   placeholder="Contoh: KUSUKA-1171-2023-0001"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Nama Lengkap & Jenis Kelamin --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lengkap Nelayan') }} *</label>
                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="Contoh: Pawang Bukhari Ahmad"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jenis Kelamin') }} *</label>
                            <select name="gender" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="L">{{ __('Laki-laki') }}</option>
                                <option value="P">{{ __('Perempuan') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 3: Peran Nelayan & Kelompok KUB --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi Peran Nelayan') }} *</label>
                            <select name="fisher_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($fisherTypes as $key => $ft)
                                    <option value="{{ $key }}" {{ $key === 'abk' ? 'selected' : '' }}>
                                        {{ $ft['icon'] }} {{ $ft['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Afiliasi Kelompok KUB') }}</label>
                            <select name="fisher_group_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Individu / Tanpa Kelompok') }} --</option>
                                @foreach($fisherGroups as $fg)
                                    <option value="{{ $fg->id }}">{{ $fg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: TTL & Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tempat Lahir') }}</label>
                            <input type="text"
                                   name="birth_place"
                                   placeholder="Contoh: Banda Aceh"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanggal Lahir') }}</label>
                            <input type="date"
                                   name="birth_date"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor HP / WhatsApp') }}</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="Contoh: 081269112233"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Lokasi Administratif (Provinsi & Kab/Kota) --}}
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
                                <option value="">-- {{ __('Pilih Kab/Kota') }} --</option>
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 6: Kecamatan & Desa (Dinamis dari API) --}}
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
                                <option value="">-- {{ __('Pilih Kecamatan') }} --</option>
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
                                <option value="">-- {{ __('Pilih Desa') }} --</option>
                                <template x-for="v in createVillages" :key="v.id">
                                    <option :value="v.id" x-text="v.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 7: Alamat Domisili --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Lengkap Domisili') }}</label>
                        <textarea name="address"
                                  rows="2"
                                  placeholder="{{ __('Masukkan alamat rumah atau lorong gampong nelayan...') }}"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Nelayan aktif melaut dan beroperasi') }}
                        </label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Data Nelayan') }}
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
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Data Nelayan') }}</h3>
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

                    {{-- Baris 1: NIK & KUSUKA --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Induk Kependudukan (NIK)') }} *</label>
                            <input type="text"
                                   name="nik"
                                   x-model="editItem.nik"
                                   required
                                   maxlength="16"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Kartu KUSUKA KKP') }}</label>
                            <input type="text"
                                   name="kusuka_number"
                                   x-model="editItem.kusuka_number"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Nama Lengkap & Jenis Kelamin --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lengkap Nelayan') }} *</label>
                            <input type="text"
                                   name="name"
                                   x-model="editItem.name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jenis Kelamin') }} *</label>
                            <select name="gender" x-model="editItem.gender" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="L">{{ __('Laki-laki') }}</option>
                                <option value="P">{{ __('Perempuan') }}</option>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 3: Peran Nelayan & Kelompok KUB --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi Peran Nelayan') }} *</label>
                            <select name="fisher_type" x-model="editItem.fisher_type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($fisherTypes as $key => $ft)
                                    <option value="{{ $key }}">
                                        {{ $ft['icon'] }} {{ $ft['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Afiliasi Kelompok KUB') }}</label>
                            <select name="fisher_group_id" x-model="editItem.fisher_group_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Individu / Tanpa Kelompok') }} --</option>
                                @foreach($fisherGroups as $fg)
                                    <option value="{{ $fg->id }}">{{ $fg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: TTL & Telepon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tempat Lahir') }}</label>
                            <input type="text"
                                   name="birth_place"
                                   x-model="editItem.birth_place"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanggal Lahir') }}</label>
                            <input type="date"
                                   name="birth_date"
                                   x-model="editItem.birth_date"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor HP / WhatsApp') }}</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editItem.phone"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Lokasi Administratif (Provinsi & Kab/Kota) --}}
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
                                <option value="">-- {{ __('Pilih Kab/Kota') }} --</option>
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 6: Kecamatan & Desa --}}
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
                                <option value="">-- {{ __('Pilih Kecamatan') }} --</option>
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
                                <option value="">-- {{ __('Pilih Desa') }} --</option>
                                <template x-for="v in editVillages" :key="v.id">
                                    <option :value="v.id" :selected="v.id == editItem.village_id" x-text="v.name"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Baris 7: Alamat Domisili --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Lengkap Domisili') }}</label>
                        <textarea name="address"
                                  rows="2"
                                  x-model="editItem.address"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editItem.is_active"
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="edit_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Nelayan aktif melaut dan beroperasi') }}
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
                    <span x-show="statusTarget.is_active">{{ __('Nonaktifkan Status Nelayan?') }}</span>
                    <span x-show="!statusTarget.is_active">{{ __('Aktifkan Status Nelayan?') }}</span>
                </h3>

                {{-- Target Fisherman Name --}}
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    {{ __('Apakah Anda yakin ingin mengubah status operasional untuk nelayan:') }}
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
                                {{ __('Nelayan yang berstatus Nonaktif tidak akan muncul dalam pilihan operasional trip penangkapan ikan baru.') }}
                            </span>
                            <span x-show="!statusTarget.is_active">
                                {{ __('Nelayan akan berstatus Aktif dan dapat dipilih dalam pencatatan trip penangkapan, logbook, dan pendataan hasil tangkapan.') }}
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
