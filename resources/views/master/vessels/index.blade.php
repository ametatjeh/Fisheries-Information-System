<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🚢</span>
            <span>{{ __('Master Data Kapal Penangkap Ikan (Vessels)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showStatusModal: false,
        statusTarget: { id: null, name: '', is_active: false, actionUrl: '' },
        editItem: {},
        editAction: '',

        openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editAction = actionUrl;
            this.showEditModal = true;
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🚢</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>⚓</span>
                        <span>{{ __('Armada Perikanan Tangkap & Pangkalan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Katalog Armada Kapal Penangkap Ikan (Vessels)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi identitas kapal, tanda selar resmi, kapasitas tonase kotor (Gross Tonnage / GT), dimensi fisik, kekuatan mesin (PK), pangkalan pendaratan (Homeport), kepemilikan nelayan, serta alat penangkapan utama.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Kapal') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics & Armada Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-5 mb-6">
            {{-- All --}}
            <a href="{{ route('master.vessels.index') }}"
               class="p-3.5 rounded-xl border transition-all {{ empty($vesselType) ? 'bg-ocean-50/90 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-gray-800">{{ $counts['total'] }}</span>
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full font-medium">{{ $counts['active'] }} Aktif</span>
                </div>
                <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Semua Kapal') }}</div>
            </a>

            {{-- Samudera > 30 GT --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-purple-700">{{ $counts['samudera_gt30'] }}</span>
                    <span class="text-base">🛳️</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Samudera (≥30 GT)') }}</div>
            </div>

            {{-- Pukat 10 - 30 GT --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-blue-700">{{ $counts['kapal_gt10_30'] }}</span>
                    <span class="text-base">🚢</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Kapal (10-30 GT)') }}</div>
            </div>

            {{-- Boat Sedang 5 - 10 GT (Tep-Tep) --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['sedang_gt5_10'] }}</span>
                    <span class="text-base">🚤</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Boat Sedang (5-10 GT)') }}</div>
            </div>

            {{-- Motor Tempel < 5 GT --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-amber-700">{{ $counts['motor_tempel'] }}</span>
                    <span class="text-base">🛶</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Motor Tempel (<5 GT)') }}</div>
            </div>

            {{-- Total GT Akumulasi --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-cyan-700">{{ $counts['total_gt'] }}</span>
                    <span class="text-xs font-bold text-cyan-600">GT</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Total Kapasitas Tonase') }}</div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.vessels.index') }}" class="flex flex-col md:flex-row gap-5">
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
                           placeholder="{{ __('Cari nama kapal (KM...), tanda selar, merek mesin, atau pemilik...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                {{-- General Vessel Type Filter --}}
                <div class="w-full md:w-44">
                    <select name="vessel_type" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Tipe Armada') }}</option>
                        @foreach($generalTypes as $key => $gt)
                            <option value="{{ $key }}" {{ $vesselType === $key ? 'selected' : '' }}>
                                {{ $gt['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Homeport Landing Site --}}
                <div class="w-full md:w-48">
                    <select name="homeport_site_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Pangkalan') }}</option>
                        @foreach($landingSites as $ls)
                            <option value="{{ $ls->id }}" {{ (string)$homeportSiteId === (string)$ls->id ? 'selected' : '' }}>
                                {{ $ls->code }} - {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Primary Gear --}}
                <div class="w-full md:w-44">
                    <select name="primary_gear_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Alat Tangkap') }}</option>
                        @foreach($fishingGears as $fg)
                            <option value="{{ $fg->id }}" {{ (string)$primaryGearId === (string)$fg->id ? 'selected' : '' }}>
                                {{ $fg->name }}
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
                    @if($search || $vesselType || $vesselTypeId || $homeportSiteId || $primaryGearId || ($status !== null && $status !== ''))
                        <a href="{{ route('master.vessels.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Vessels Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 w-40">{{ __('Tanda Selar / Registrasi') }}</th>
                            <th class="py-3 px-4">{{ __('Nama Kapal & Klasifikasi') }}</th>
                            <th class="py-3 px-4">{{ __('Pemilik Kapal') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Tonase & Dimensi') }}</th>
                            <th class="py-3 px-4">{{ __('Mesin Utama') }}</th>
                            <th class="py-3 px-4">{{ __('Pangkalan & Alat Tangkap') }}</th>
                            <th class="py-3 px-4 text-center w-24">{{ __('Status') }}</th>
                            <th class="py-3 px-4 text-right w-24">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($vessels as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                {{-- Index # --}}
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $vessels->firstItem() + $index }}
                                </td>

                                {{-- Tanda Selar / Registration Number --}}
                                <td class="py-3.5 px-4">
                                    @if($item->registration_number)
                                        <span class="font-mono font-bold px-2.5 py-1 rounded-md bg-ocean-50 text-ocean-700 border border-ocean-200 text-xs tracking-wider block text-center">
                                            {{ $item->registration_number }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ __('Tanpa Tanda Selar') }}</span>
                                    @endif
                                </td>

                                {{-- Name & Type --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $item->name }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        @if($item->vesselType)
                                            <span class="font-semibold text-ocean-700">{{ $item->vesselType->name }}</span>
                                        @else
                                            <span>{{ ucfirst(str_replace('_', ' ', $item->vessel_type)) }}</span>
                                        @endif
                                        @if($item->build_year)
                                            <span class="text-gray-400">• Th. {{ $item->build_year }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Owner --}}
                                <td class="py-3.5 px-4">
                                    @if($item->owner)
                                        <div class="font-semibold text-gray-800">{{ $item->owner->name }}</div>
                                        @if($item->owner->phone)
                                            <a href="tel:{{ $item->owner->phone }}" class="text-[11px] text-ocean-600 hover:text-ocean-700 font-mono flex items-center gap-1 mt-0.5">
                                                <span>📞</span> {{ $item->owner->phone }}
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-xs text-gray-400 italic">{{ __('Belum ditautkan') }}</span>
                                    @endif
                                </td>

                                {{-- Gross Tonnage & Dimensions --}}
                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 text-xs font-bold font-mono">
                                        <span>{{ number_format($item->gross_tonnage, 1) }} GT</span>
                                    </div>
                                    @if($item->length || $item->width)
                                        <div class="text-[11px] text-gray-500 mt-1 font-mono">
                                            {{ $item->length ?? '-' }}m × {{ $item->width ?? '-' }}m × {{ $item->depth ?? '-' }}m
                                        </div>
                                    @endif
                                </td>

                                {{-- Engine --}}
                                <td class="py-3.5 px-4">
                                    @if($item->engine_power_hp)
                                        <div class="font-bold text-xs text-gray-800 font-mono">
                                            {{ number_format($item->engine_power_hp, 0) }} PK/HP
                                        </div>
                                    @endif
                                    <div class="text-xs text-gray-600">
                                        {{ $item->engine_brand ?? ($item->vessel_type === 'tanpa_motor' ? 'Tenaga Dayung/Layar' : '-') }}
                                    </div>
                                </td>

                                {{-- Homeport & Primary Gear --}}
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-semibold text-gray-800 flex items-center gap-1">
                                        <span>⚓</span>
                                        <span>{{ $item->homeportSite ? $item->homeportSite->name : '-' }}</span>
                                    </div>
                                    <div class="text-[11px] text-ocean-600 mt-0.5 flex items-center gap-1">
                                        <span>🎣</span>
                                        <span>{{ $item->primaryGear ? $item->primaryGear->name : '-' }}</span>
                                    </div>
                                </td>

                                {{-- Active Status Toggle with Confirmation Popup --}}
                                <td class="py-3.5 px-4 text-center">
                                    <button type="button"
                                            @click="openStatusModal({{ json_encode(['id' => $item->id, 'name' => $item->name . ($item->registration_number ? ' (' . $item->registration_number . ')' : ''), 'is_active' => (bool)$item->is_active]) }}, '{{ route('master.vessels.toggle-status', $item->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer shadow-xs {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border border-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-200' }}"
                                            title="{{ __('Klik untuk ubah status operasional kapal') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $item->is_active ? __('Aktif') : __('Nonaktif') }}</span>
                                    </button>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('master.vessels.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Kapal') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('master.vessels.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data kapal :name?', ['name' => $item->name]) }}');" class="inline">
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
                                <td colspan="9" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">🚢</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data kapal penangkap ikan ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci lain atau bersihkan filter pencarian.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($vessels->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $vessels->links() }}
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
                 class="bg-white rounded-2xl max-w-4xl w-full p-8 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🚢</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Kapal Penangkap Ikan Baru') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.vessels.store') }}" class="space-y-6 mt-6">
                    @csrf

                    {{-- Baris 1: Nama Kapal & Tanda Selar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Kapal (KM / KMN)') }} *</label>
                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="Contoh: KM. Lampulo Jaya"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanda Selar / No. Registrasi') }}</label>
                            <input type="text"
                                   name="registration_number"
                                   placeholder="Contoh: GT. 18 No. 422/Bda"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Pemilik & Pangkalan Homeport --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nelayan Pemilik Kapal') }}</label>
                            <select name="owner_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Nelayan Pemilik (Opsional)') }} --</option>
                                @foreach($owners as $own)
                                    <option value="{{ $own->id }}">
                                        {{ $own->name }} ({{ $own->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pangkalan Pendaratan (Homeport)') }}</label>
                            <select name="homeport_site_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pangkalan / TPI') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">
                                        {{ $ls->code }} - {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 3: Tipe Armada & Klasifikasi Tipe Kapal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kategori Tipe Armada') }} *</label>
                            <select name="vessel_type" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="kapal_motor">{{ __('Kapal Motor (Inboard Engine)') }}</option>
                                <option value="motor_tempel">{{ __('Perahu Motor Tempel') }}</option>
                                <option value="tanpa_motor">{{ __('Perahu Tanpa Motor') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi Tipe Kapal (Master)') }}</label>
                            <select name="vessel_type_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Klasifikasi Tipe') }} --</option>
                                @foreach($vesselTypes as $vt)
                                    <option value="{{ $vt->id }}">
                                        {{ $vt->code }} - {{ $vt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Tonase & Dimensi Kapal --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Gross Tonnage (GT)') }} *</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="gross_tonnage"
                                   required
                                   placeholder="Contoh: 18.5"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Panjang (LoA m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="length"
                                   placeholder="15.5"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Lebar (m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="width"
                                   placeholder="3.8"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Dalam (Draft m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="depth"
                                   placeholder="1.7"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Mesin & Tahun Buat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Daya Mesin (PK/HP)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="engine_power_hp"
                                   placeholder="Contoh: 160"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Merek Mesin') }}</label>
                            <input type="text"
                                   name="engine_brand"
                                   placeholder="Contoh: Yanmar 6CX"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tahun Pembuatan') }}</label>
                            <input type="number"
                                   min="1960"
                                   max="{{ date('Y') + 1 }}"
                                   name="build_year"
                                   placeholder="Contoh: 2020"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Alat Tangkap Utama --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alat Tangkap Utama') }}</label>
                        <select name="primary_gear_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">-- {{ __('Pilih Alat Tangkap Utama') }} --</option>
                            @foreach($fishingGears as $fg)
                                <option value="{{ $fg->id }}">{{ $fg->code }} - {{ $fg->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="create_is_active" value="1" checked
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="create_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Kapal aktif beroperasi melaut') }}
                        </label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-5 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Data Kapal') }}
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
                 class="bg-white rounded-2xl max-w-4xl w-full p-8 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Data Kapal') }}</h3>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="space-y-6 mt-6">
                    @csrf
                    @method('PUT')

                    {{-- Baris 1: Nama Kapal & Tanda Selar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Kapal (KM / KMN)') }} *</label>
                            <input type="text"
                                   name="name"
                                   x-model="editItem.name"
                                   required
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tanda Selar / No. Registrasi') }}</label>
                            <input type="text"
                                   name="registration_number"
                                   x-model="editItem.registration_number"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 2: Pemilik & Pangkalan Homeport --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nelayan Pemilik Kapal') }}</label>
                            <select name="owner_id" x-model="editItem.owner_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Nelayan Pemilik (Opsional)') }} --</option>
                                @foreach($owners as $own)
                                    <option value="{{ $own->id }}">
                                        {{ $own->name }} ({{ $own->nik }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pangkalan Pendaratan (Homeport)') }}</label>
                            <select name="homeport_site_id" x-model="editItem.homeport_site_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pangkalan / TPI') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">
                                        {{ $ls->code }} - {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 3: Tipe Armada & Klasifikasi Tipe Kapal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kategori Tipe Armada') }} *</label>
                            <select name="vessel_type" x-model="editItem.vessel_type" required class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="kapal_motor">{{ __('Kapal Motor (Inboard Engine)') }}</option>
                                <option value="motor_tempel">{{ __('Perahu Motor Tempel') }}</option>
                                <option value="tanpa_motor">{{ __('Perahu Tanpa Motor') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Klasifikasi Tipe Kapal (Master)') }}</label>
                            <select name="vessel_type_id" x-model="editItem.vessel_type_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Klasifikasi Tipe') }} --</option>
                                @foreach($vesselTypes as $vt)
                                    <option value="{{ $vt->id }}">
                                        {{ $vt->code }} - {{ $vt->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Tonase & Dimensi Kapal --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Gross Tonnage (GT)') }} *</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="gross_tonnage"
                                   x-model="editItem.gross_tonnage"
                                   required
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Panjang (LoA m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="length"
                                   x-model="editItem.length"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Lebar (m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="width"
                                   x-model="editItem.width"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Dalam (Draft m)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="depth"
                                   x-model="editItem.depth"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: Mesin & Tahun Buat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Daya Mesin (PK/HP)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="engine_power_hp"
                                   x-model="editItem.engine_power_hp"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Merek Mesin') }}</label>
                            <input type="text"
                                   name="engine_brand"
                                   x-model="editItem.engine_brand"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tahun Pembuatan') }}</label>
                            <input type="number"
                                   min="1960"
                                   max="{{ date('Y') + 1 }}"
                                   name="build_year"
                                   x-model="editItem.build_year"
                                   class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Alat Tangkap Utama --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alat Tangkap Utama') }}</label>
                        <select name="primary_gear_id" x-model="editItem.primary_gear_id" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">-- {{ __('Pilih Alat Tangkap Utama') }} --</option>
                            @foreach($fishingGears as $fg)
                                <option value="{{ $fg->id }}">{{ $fg->code }} - {{ $fg->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Checkbox Status Aktif --}}
                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1" :checked="editItem.is_active"
                               class="rounded border-gray-300 text-ocean-600 shadow-sm focus:ring-ocean-500">
                        <label for="edit_is_active" class="text-sm font-medium text-gray-700 cursor-pointer">
                            {{ __('Kapal aktif beroperasi melaut') }}
                        </label>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-5 pt-4 border-t border-gray-100">
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
                    <span x-show="statusTarget.is_active">{{ __('Nonaktifkan Operasional Kapal?') }}</span>
                    <span x-show="!statusTarget.is_active">{{ __('Aktifkan Operasional Kapal?') }}</span>
                </h3>

                {{-- Target Vessel Name --}}
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    {{ __('Apakah Anda yakin ingin mengubah status operasional armada kapal:') }}
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
                                {{ __('Kapal yang berstatus Nonaktif tidak akan muncul dalam opsi kapal operasional pada pencatatan trip baru.') }}
                            </span>
                            <span x-show="!statusTarget.is_active">
                                {{ __('Kapal akan kembali aktif dan dapat dipilih dalam pencatatan keberangkatan trip melaut serta logbook penangkapan.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-5">
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
