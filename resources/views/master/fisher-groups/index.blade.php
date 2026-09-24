<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>👥</span>
            <span>{{ __('Master Data Kelompok Nelayan (KUB)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        editItem: {},
        editAction: '',

        // Cascading dropdowns for Create modal
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

            // Load districts and villages for existing regency
            if (this.editItem.regency_id) {
                await this.onRegencyChange(this.editItem.regency_id, 'edit');
                if (this.editItem.district_id) {
                    await this.onDistrictChange(this.editItem.district_id, 'edit');
                }
            }
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">👥</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🤝</span>
                        <span>{{ __('Kelembagaan Nelayan & Kemitraan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Kelompok Usaha Bersama (KUB) & Kelompok Nelayan') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi kelompok usaha bersama nelayan tangkap pesisir Aceh, kelembagaan Panglima Laot gampong, pendataan ketua kelompok, nomor kontak, serta persebaran anggota binaan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Kelompok Nelayan') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            {{-- Total Groups --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-gray-800">{{ $counts['total_groups'] }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Total Kelompok Nelayan / KUB') }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-ocean-50 text-ocean-600 flex items-center justify-center text-xl">
                        👥
                    </div>
                </div>
            </div>

            {{-- Total Members --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-emerald-700">{{ number_format($counts['total_members']) }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Total Anggota Terdaftar') }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl">
                        👨‍🌾
                    </div>
                </div>
            </div>

            {{-- Avg Members --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-blue-700">{{ $counts['avg_members'] }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Rata-rata Anggota / Kelompok') }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl">
                        📊
                    </div>
                </div>
            </div>

            {{-- Total Regencies Covered --}}
            <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-2xl font-bold text-purple-700">{{ $counts['total_regencies'] }}</div>
                        <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Kab/Kota Terjangkau') }}</div>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl">
                        🌍
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.fisher-groups.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari nama KUB, kode registrasi, nama ketua, telepon, atau alamat...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                <div class="w-full sm:w-64">
                    <select name="regency_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kabupaten / Kota') }}</option>
                        @foreach($regencies as $r)
                            <option value="{{ $r->id }}" {{ (string)$regencyId === (string)$r->id ? 'selected' : '' }}>
                                {{ ucfirst($r->type) }} {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Filter') }}
                    </button>
                    @if($search || $regencyId)
                        <a href="{{ route('master.fisher-groups.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Groups Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-ocean-900 border-b border-ocean-950 text-xs font-semibold text-white uppercase tracking-wider">
                            <th class="py-3.5 px-4 w-12 text-center text-white">#</th>
                            <th class="py-3.5 px-4 w-32 text-white">{{ __('Kode KUB') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Nama Kelompok Nelayan') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Ketua & Kontak') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Domisili Wilayah') }}</th>
                            <th class="py-3.5 px-4 text-center text-white">{{ __('Tgl Pengukuhan') }}</th>
                            <th class="py-3.5 px-4 text-center w-28 text-white">{{ __('Anggota') }}</th>
                            <th class="py-3.5 px-4 text-right w-24 text-white">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($groups as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                {{-- Index # --}}
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $groups->firstItem() + $index }}
                                </td>

                                {{-- Code --}}
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold px-2.5 py-1 rounded-md bg-ocean-50 text-ocean-700 border border-ocean-200 text-xs tracking-wider">
                                        {{ $item->code ?? '-' }}
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

                                {{-- Leader & Phone --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-800">
                                        {{ $item->leader_name ?? '-' }}
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

                                {{-- Established Date --}}
                                <td class="py-3.5 px-4 text-center text-xs text-gray-600">
                                    @if($item->established_date)
                                        <span class="font-mono">{{ $item->established_date->format('d/m/Y') }}</span>
                                        <div class="text-[10px] text-gray-400">
                                            ({{ $item->established_date->diffForHumans(['parts' => 1]) }})
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>

                                {{-- Members Count --}}
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span>👥</span>
                                        <span>{{ $item->total_members }}</span>
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('master.fisher-groups.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Kelompok') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('master.fisher-groups.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data kelompok nelayan :name?', ['name' => $item->name]) }}');" class="inline">
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
                                    <div class="text-4xl mb-2">👥</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data kelompok nelayan ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci pencarian lain atau bersihkan filter.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$groups" />
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
                        <span class="text-xl">👥</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Kelompok Usaha Bersama (KUB)') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.fisher-groups.store') }}" class="space-y-4 mt-4">
                    @csrf

                    {{-- Baris 1: Kode KUB & Nama Kelompok --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode KUB / Kelompok') }}</label>
                            <input type="text"
                                   name="code"
                                   placeholder="Contoh: KUB-BA-001"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Kelompok Nelayan') }} *</label>
                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="Contoh: KUB Mina Bahari Lampulo"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Wilayah Administratif (Kab/Kota, Kecamatan, Desa) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
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

                    {{-- Baris 3: Ketua & Kontak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Ketua Kelompok') }}</label>
                            <input type="text"
                                   name="leader_name"
                                   placeholder="Contoh: Teuku Zulkifli"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Kontak / WhatsApp') }}</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="Contoh: 081269012345"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 4: Tanggal Berdiri & Estimasi Jumlah Anggota --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanggal Pengukuhan / Berdiri') }}</label>
                            <input type="date"
                                   name="established_date"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jumlah Anggota Terdaftar') }}</label>
                            <input type="number"
                                   name="total_members"
                                   min="0"
                                   value="0"
                                   placeholder="Contoh: 25"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Alamat / Sekretariat --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Sekretariat / Keterangan Domisili') }}</label>
                        <textarea name="address"
                                  rows="2"
                                  placeholder="{{ __('Masukkan alamat lengkap kantor atau rumah sekretariat kelompok...') }}"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Kelompok Nelayan') }}
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
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Kelompok Nelayan') }}</h3>
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

                    {{-- Baris 1: Kode KUB & Nama Kelompok --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode KUB / Kelompok') }}</label>
                            <input type="text"
                                   name="code"
                                   x-model="editItem.code"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Kelompok Nelayan') }} *</label>
                            <input type="text"
                                   name="name"
                                   x-model="editItem.name"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Wilayah Administratif (Kab/Kota, Kecamatan, Desa) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
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

                    {{-- Baris 3: Ketua & Kontak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Ketua Kelompok') }}</label>
                            <input type="text"
                                   name="leader_name"
                                   x-model="editItem.leader_name"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Kontak / WhatsApp') }}</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editItem.phone"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 4: Tanggal Berdiri & Estimasi Jumlah Anggota --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanggal Pengukuhan / Berdiri') }}</label>
                            <input type="date"
                                   name="established_date"
                                   x-model="editItem.established_date"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jumlah Anggota Terdaftar') }}</label>
                            <input type="number"
                                   name="total_members"
                                   min="0"
                                   x-model="editItem.total_members"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Alamat / Sekretariat --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Sekretariat / Keterangan Domisili') }}</label>
                        <textarea name="address"
                                  rows="2"
                                  x-model="editItem.address"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
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
    </div>
</x-app-layout>
