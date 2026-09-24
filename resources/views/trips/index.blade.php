<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>📝</span>
            <span>{{ __('Pengumpulan Data: Trip Penangkapan (Fishing Trips)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        editItem: {},
        detailItem: {},
        editAction: '',

        openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editAction = actionUrl;
            this.showEditModal = true;
        },

        openDetail(item) {
            this.detailItem = Object.assign({}, item);
            this.showDetailModal = true;
        },

        // Auto-fill captain, homeport, and gear when vessel is selected in create form
        onVesselSelect(vesselId, vesselsList) {
            const v = vesselsList.find(x => x.id == vesselId);
            if (v) {
                if (v.homeport_site_id) {
                    const depSelect = document.getElementById('create_departure_site_id');
                    const lanSelect = document.getElementById('create_landing_site_id');
                    if (depSelect) depSelect.value = v.homeport_site_id;
                    if (lanSelect && !lanSelect.value) lanSelect.value = v.homeport_site_id;
                }
                if (v.primary_gear_id) {
                    const gearSelect = document.getElementById('create_primary_gear_id');
                    if (gearSelect) gearSelect.value = v.primary_gear_id;
                }
            }
        }
    }">


        @if (isset($errors) && $errors->any())
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">📝</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Pencatatan Logbook & Operasional Melaut') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Manajemen Trip Penangkapan Ikan (Fishing Trips)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi operasional melaut kapal, pelabuhan keberangkatan dan pendaratan, alokasi awak kapal (ABK), estimasi logistik BBM dan es, Wilayah Pengelolaan Perikanan (WPPNRI 571 & 572), serta verifikasi data hasil tangkapan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Catat Trip Baru') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            {{-- Total Trips --}}
            <a href="{{ route('trips.index') }}"
               class="p-3.5 rounded-xl border transition-all {{ empty($validationStatus) ? 'bg-ocean-50/90 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-gray-800">{{ $counts['total'] }}</span>
                    <span class="text-base">📝</span>
                </div>
                <div class="text-xs font-medium text-gray-500 mt-1">{{ __('Total Semua Trip') }}</div>
            </a>

            {{-- Sedang Melaut (Ongoing) --}}
            <a href="{{ route('trips.index', ['validation_status' => 'ongoing']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $validationStatus === 'ongoing' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-amber-700">{{ $counts['ongoing'] }}</span>
                    <span class="text-xs px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full font-semibold animate-pulse">{{ __('Di Laut') }}</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Sedang Melaut (Aktif)') }}</div>
            </a>

            {{-- Validated --}}
            <a href="{{ route('trips.index', ['validation_status' => 'validated']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $validationStatus === 'validated' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['validated'] }}</span>
                    <span class="text-base">✅</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Trip Tervalidasi') }}</div>
            </a>

            {{-- Submitted (Pending) --}}
            <a href="{{ route('trips.index', ['validation_status' => 'submitted']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $validationStatus === 'submitted' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-blue-700">{{ $counts['submitted'] }}</span>
                    <span class="text-base">⏳</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-1">{{ __('Menunggu Validasi') }}</div>
            </a>

            {{-- Logistik Summary --}}
            <div class="p-3.5 rounded-xl border bg-white border-gray-100 col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-bold text-gray-800 font-mono">{{ number_format($counts['total_fuel']) }} L BBM</div>
                        <div class="text-xs text-gray-500 font-mono mt-0.5">{{ number_format($counts['total_ice']) }} kg Es</div>
                    </div>
                    <span class="text-base">⛽</span>
                </div>
                <div class="text-[11px] font-medium text-gray-400 mt-1">{{ __('Akumulasi Logistik') }}</div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('trips.index') }}" class="flex flex-col md:flex-row gap-3">
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
                           placeholder="{{ __('Cari no. trip (TRIP-...), nama kapal, nahkoda, atau daerah penangkapan...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                {{-- Status Filter --}}
                <div class="w-full md:w-44">
                    <select name="validation_status" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="ongoing" {{ $validationStatus === 'ongoing' ? 'selected' : '' }}>{{ __('Sedang Melaut (Di Laut)') }}</option>
                        <option value="validated" {{ $validationStatus === 'validated' ? 'selected' : '' }}>{{ __('Tervalidasi (Validated)') }}</option>
                        <option value="submitted" {{ $validationStatus === 'submitted' ? 'selected' : '' }}>{{ __('Diajukan (Submitted)') }}</option>
                        <option value="draft" {{ $validationStatus === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                        <option value="rejected" {{ $validationStatus === 'rejected' ? 'selected' : '' }}>{{ __('Ditolak (Rejected)') }}</option>
                    </select>
                </div>

                {{-- WPPNRI Filter --}}
                <div class="w-full md:w-48">
                    <select name="fma_code" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua WPPNRI') }}</option>
                        @foreach($wppList as $code => $label)
                            <option value="{{ $code }}" {{ $fmaCode === $code ? 'selected' : '' }}>
                                {{ $code }} - {{ $code === '571' ? 'Selat Malaka' : 'Samudera Hindia' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Landing Site Filter --}}
                <div class="w-full md:w-44">
                    <select name="landing_site_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Pelabuhan') }}</option>
                        @foreach($landingSites as $ls)
                            <option value="{{ $ls->id }}" {{ (string)$landingSiteId === (string)$ls->id ? 'selected' : '' }}>
                                {{ $ls->code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Filter') }}
                    </button>
                    @if($search || $validationStatus || $fmaCode || $vesselId || $landingSiteId)
                        <a href="{{ route('trips.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Trips Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4 w-40">{{ __('No. Trip') }}</th>
                            <th class="py-3 px-4">{{ __('Kapal & Nahkoda') }}</th>
                            <th class="py-3 px-4">{{ __('Pelabuhan (Berangkat / Bongkar)') }}</th>
                            <th class="py-3 px-4">{{ __('Waktu Melaut & Durasi') }}</th>
                            <th class="py-3 px-4">{{ __('WPPNRI & Fishing Ground') }}</th>
                            <th class="py-3 px-4 text-center">{{ __('Logistik') }}</th>
                            <th class="py-3 px-4 text-center w-28">{{ __('Status') }}</th>
                            <th class="py-3 px-4 text-right w-24">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($trips as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                {{-- Index # --}}
                                <td class="py-3.5 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $trips->firstItem() + $index }}
                                </td>

                                {{-- Trip Number --}}
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold px-2.5 py-1 rounded-md bg-ocean-50 text-ocean-700 border border-ocean-200 text-xs tracking-wider block text-center">
                                        {{ $item->trip_number }}
                                    </span>
                                </td>

                                {{-- Vessel & Captain --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900">{{ $item->vessel ? $item->vessel->name : '-' }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5 flex items-center gap-1">
                                        <span>🧭</span>
                                        <span>{{ $item->captain ? $item->captain->name : 'Nahkoda tidak tercatat' }}</span>
                                    </div>
                                </td>

                                {{-- Departure & Landing Sites --}}
                                <td class="py-3.5 px-4">
                                    <div class="text-xs text-gray-800 font-medium">
                                        🛫 {{ $item->departureSite ? $item->departureSite->name : '-' }}
                                    </div>
                                    <div class="text-xs text-ocean-700 mt-0.5">
                                        🛬 {{ $item->landingSite ? $item->landingSite->name : '-' }}
                                    </div>
                                </td>

                                {{-- Departure Date & Duration --}}
                                <td class="py-3.5 px-4">
                                    <div class="text-xs font-mono font-medium text-gray-800">
                                        {{ $item->departure_date->format('d/m/Y H:i') }}
                                    </div>
                                    @if($item->return_date)
                                        <div class="text-xs font-mono text-gray-500 mt-0.5">
                                            {{ $item->return_date->format('d/m/Y H:i') }}
                                        </div>
                                        <div class="text-[10px] text-emerald-600 font-semibold mt-0.5">
                                            ⏱️ {{ round($item->departure_date->diffInHours($item->return_date) / 24, 1) }} {{ __('hari') }}
                                        </div>
                                    @else
                                        <div class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[11px] font-semibold animate-pulse">
                                            <span>⚓</span>
                                            <span>{{ __('Sedang Melaut') }}</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- FMA & Fishing Ground --}}
                                <td class="py-3.5 px-4">
                                    @if($item->fma_code)
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold font-mono {{ $item->fma_code === '571' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-purple-100 text-purple-800 border border-purple-200' }}">
                                            WPP-{{ $item->fma_code }}
                                        </span>
                                    @endif
                                    <div class="text-xs text-gray-600 mt-1 line-clamp-1" title="{{ $item->fishing_ground_name }}">
                                        {{ $item->fishing_ground_name ?? '-' }}
                                    </div>
                                </td>

                                {{-- Logistics & Crew --}}
                                <td class="py-3.5 px-4 text-center">
                                    <div class="text-xs font-mono text-gray-700">
                                        ⛽ {{ $item->fuel_consumption_liters ? number_format($item->fuel_consumption_liters, 0) . ' L' : '-' }}
                                    </div>
                                    <div class="text-[11px] font-mono text-cyan-700 mt-0.5">
                                        🧊 {{ $item->ice_consumption_kg ? number_format($item->ice_consumption_kg, 0) . ' kg' : '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-400 mt-0.5">
                                        👥 {{ $item->crew_count }} {{ __('ABK') }}
                                    </div>
                                </td>

                                {{-- Validation Status --}}
                                <td class="py-3.5 px-4 text-center">
                                    @php
                                        $statusStyles = match($item->validation_status) {
                                            'validated' => 'bg-green-100 text-green-800 border-green-200',
                                            'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'rejected'  => 'bg-rose-100 text-rose-800 border-rose-200',
                                            default     => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                        $statusLabel = match($item->validation_status) {
                                            'validated' => 'Tervalidasi',
                                            'submitted' => 'Diajukan',
                                            'rejected'  => 'Ditolak',
                                            default     => 'Draft',
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full border {{ $statusStyles }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Quick Validate Button (if not yet validated) --}}
                                        @if($item->validation_status !== 'validated')
                                            <form method="POST" action="{{ route('trips.validate', $item->id) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="action" value="validate">
                                                <button type="submit"
                                                        class="p-1.5 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                                                        title="{{ __('Validasi Trip Ini') }}">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif

                                        {{-- Edit Button --}}
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('trips.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Trip') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('trips.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data trip :number?', ['number' => $item->trip_number]) }}');" class="inline">
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
                                    <div class="text-4xl mb-2">📝</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data trip penangkapan ikan ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci lain atau bersihkan filter pencarian.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$trips" />
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH TRIP (CREATE MODAL)                                  --}}
        {{-- ================================================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showCreateModal = false">
            <div @click.away="showCreateModal = false"
                 class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📝</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Catat Trip Penangkapan Baru') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('trips.store') }}" class="space-y-4 mt-4">
                    @csrf

                    {{-- Baris 1: Nomor Trip & Kapal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Trip (Kosongkan utk Otomatis)') }}</label>
                            <input type="text"
                                   name="trip_number"
                                   placeholder="Otomatis: TRIP-{{ date('Ym') }}-XXXX"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kapal Operasi') }} *</label>
                            <select name="vessel_id"
                                    required
                                    @change="onVesselSelect($event.target.value, {{ json_encode($vessels) }})"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kapal') }} --</option>
                                @foreach($vessels as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }} ({{ number_format($v->gross_tonnage, 1) }} GT)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 2: Nahkoda & Jumlah ABK --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nahkoda / Jurumudi') }}</label>
                            <select name="captain_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Nahkoda (Opsional)') }} --</option>
                                @foreach($captains as $cap)
                                    <option value="{{ $cap->id }}">{{ $cap->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jumlah Awak Kapal (ABK)') }} *</label>
                            <input type="number"
                                   name="crew_count"
                                   min="1"
                                   value="5"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 3: Pelabuhan Berangkat & Bongkar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pelabuhan Keberangkatan') }} *</label>
                            <select name="departure_site_id" id="create_departure_site_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pelabuhan Berangkat') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">{{ $ls->code }} - {{ $ls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pelabuhan Pendaratan / Bongkar') }}</label>
                            <select name="landing_site_id" id="create_landing_site_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pelabuhan Pendaratan') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">{{ $ls->code }} - {{ $ls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Waktu Berangkat & Kembali --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Waktu Keberangkatan') }} *</label>
                            <input type="datetime-local"
                                   name="departure_date"
                                   required
                                   value="{{ date('Y-m-d\TH:i') }}"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Waktu Kembali (Kosongkan jika masih melaut)') }}</label>
                            <input type="datetime-local"
                                   name="return_date"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: WPPNRI & Area Tangkapan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Wilayah WPPNRI') }}</label>
                            <select name="fma_code" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih WPP') }} --</option>
                                <option value="571">{{ __('WPP 571 - Selat Malaka') }}</option>
                                <option value="572" selected>{{ __('WPP 572 - Samudera Hindia') }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Daerah Penangkapan (Fishing Ground)') }}</label>
                            <input type="text"
                                   name="fishing_ground_name"
                                   placeholder="Contoh: Perairan Barat Pulo Aceh & ZEEI"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Logistik (BBM, Es) & Alat Tangkap --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Konsumsi BBM (Liter)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="fuel_consumption_liters"
                                   placeholder="Contoh: 500"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Konsumsi Es (Kg)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="ice_consumption_kg"
                                   placeholder="Contoh: 1200"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alat Tangkap Utama') }}</label>
                            <select name="primary_gear_id" id="create_primary_gear_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Alat Tangkap') }} --</option>
                                @foreach($fishingGears as $fg)
                                    <option value="{{ $fg->id }}">{{ $fg->code }} - {{ $fg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 7: Status Validasi & Catatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Status Validasi') }} *</label>
                            <select name="validation_status" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="draft">{{ __('Draft (Pencatatan)') }}</option>
                                <option value="submitted" selected>{{ __('Submitted (Diajukan)') }}</option>
                                <option value="validated">{{ __('Validated (Disetujui)') }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Catatan Operasional') }}</label>
                            <input type="text"
                                   name="notes"
                                   placeholder="{{ __('Kondisi cuaca, hasil tangkapan utama, atau keterangan lainnya...') }}"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Footer Buttons --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Trip Penangkapan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL UBAH TRIP (EDIT MODAL)                                      --}}
        {{-- ================================================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-2xl max-w-3xl w-full p-6 shadow-xl border border-gray-100 my-8">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Data Trip Penangkapan') }}</h3>
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

                    {{-- Baris 1: Nomor Trip & Kapal --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nomor Trip') }} *</label>
                            <input type="text"
                                   name="trip_number"
                                   x-model="editItem.trip_number"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kapal Operasi') }} *</label>
                            <select name="vessel_id" x-model="editItem.vessel_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($vessels as $v)
                                    <option value="{{ $v->id }}">{{ $v->name }} ({{ number_format($v->gross_tonnage, 1) }} GT)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 2: Nahkoda & Jumlah ABK --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nahkoda / Jurumudi') }}</label>
                            <select name="captain_id" x-model="editItem.captain_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Nahkoda (Opsional)') }} --</option>
                                @foreach($captains as $cap)
                                    <option value="{{ $cap->id }}">{{ $cap->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Jumlah Awak Kapal (ABK)') }} *</label>
                            <input type="number"
                                   name="crew_count"
                                   x-model="editItem.crew_count"
                                   min="1"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 3: Pelabuhan Berangkat & Bongkar --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pelabuhan Keberangkatan') }} *</label>
                            <select name="departure_site_id" x-model="editItem.departure_site_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">{{ $ls->code }} - {{ $ls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Pelabuhan Pendaratan / Bongkar') }}</label>
                            <select name="landing_site_id" x-model="editItem.landing_site_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pelabuhan Pendaratan') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">{{ $ls->code }} - {{ $ls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 4: Waktu Berangkat & Kembali --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Waktu Keberangkatan') }} *</label>
                            <input type="datetime-local"
                                   name="departure_date"
                                   :value="editItem.departure_date ? editItem.departure_date.substring(0, 16) : ''"
                                   required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Waktu Kembali') }}</label>
                            <input type="datetime-local"
                                   name="return_date"
                                   :value="editItem.return_date ? editItem.return_date.substring(0, 16) : ''"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 5: WPPNRI & Area Tangkapan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Wilayah WPPNRI') }}</label>
                            <select name="fma_code" x-model="editItem.fma_code" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih WPP') }} --</option>
                                <option value="571">{{ __('WPP 571 - Selat Malaka') }}</option>
                                <option value="572">{{ __('WPP 572 - Samudera Hindia') }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Daerah Penangkapan (Fishing Ground)') }}</label>
                            <input type="text"
                                   name="fishing_ground_name"
                                   x-model="editItem.fishing_ground_name"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Baris 6: Logistik (BBM, Es) & Alat Tangkap --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Konsumsi BBM (Liter)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="fuel_consumption_liters"
                                   x-model="editItem.fuel_consumption_liters"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Konsumsi Es (Kg)') }}</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   name="ice_consumption_kg"
                                   x-model="editItem.ice_consumption_kg"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alat Tangkap Utama') }}</label>
                            <select name="primary_gear_id" x-model="editItem.primary_gear_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Alat Tangkap') }} --</option>
                                @foreach($fishingGears as $fg)
                                    <option value="{{ $fg->id }}">{{ $fg->code }} - {{ $fg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Baris 7: Status Validasi & Catatan --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Status Validasi') }} *</label>
                            <select name="validation_status" x-model="editItem.validation_status" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="draft">{{ __('Draft') }}</option>
                                <option value="submitted">{{ __('Submitted') }}</option>
                                <option value="validated">{{ __('Validated') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Catatan Operasional') }}</label>
                            <input type="text"
                                   name="notes"
                                   x-model="editItem.notes"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
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
