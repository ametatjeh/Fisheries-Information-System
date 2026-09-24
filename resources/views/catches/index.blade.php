<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🐟</span>
            <span>{{ __('Pengumpulan Data: Hasil Tangkapan Ikan (Fish Catches)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        editItem: {},
        detailItem: {},
        editAction: '',
        tripsData: {{ json_encode($trips->map(fn($t) => [
            'id' => $t->id,
            'trip_number' => $t->trip_number,
            'vessel_name' => $t->vessel->name ?? 'Tanpa Kapal',
            'efforts' => $t->fishingEfforts->map(fn($e) => [
                'id' => $e->id,
                'setting_number' => $e->setting_number,
                'gear_name' => $e->fishingGear->name ?? 'Alat Tangkap',
            ])
        ])) }},
        createTripId: '',
        editTripId: '',

        openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editTripId = item.fishing_trip_id;
            this.editAction = actionUrl;
            this.showEditModal = true;
        },

        openDetail(item) {
            this.detailItem = Object.assign({}, item);
            this.showDetailModal = true;
        },

        getEffortsForTrip(tripId) {
            if (!tripId) return [];
            const t = this.tripsData.find(x => x.id == tripId);
            return t ? t.efforts : [];
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🐟</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Komposisi & Hasil Tangkapan Kapal Nelayan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Hasil Tangkapan Ikan (Fish Catches)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Pencatatan kuantitatif hasil tangkapan ikan di laut per trip operasional: kode FAO, nama lokal Aceh, berat total (kg), estimasi jumlah ekor, status tangkapan (Target, Bycatch, Discard), serta keterkaitan dengan siklus setting alat tangkap.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Catat Tangkapan Baru') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-ocean-700 uppercase tracking-wider">{{ __('Total Berat') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-ocean-800">{{ number_format($counts['total_weight_kg']) }}</span>
                    <span class="text-xs font-bold text-slate-500">kg</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">≈ {{ $counts['total_weight_ton'] }} ton tangkapan</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-teal-600 uppercase tracking-wider">{{ __('Total Ekor') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-teal-600">{{ number_format($counts['total_fish_count']) }}</span>
                    <span class="text-xs font-bold text-slate-500">ekor</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Estimasi individu') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-blue-600 uppercase tracking-wider">{{ __('Spesies Terdata') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-blue-600">{{ $counts['total_species'] }}</span>
                    <span class="text-xs font-bold text-slate-500">spesies</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ $counts['total_records'] }} {{ __('catatan tangkapan') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-emerald-600 uppercase tracking-wider">{{ __('Tangkapan Utama') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-emerald-600">{{ $counts['target_count'] }}</span>
                    <span class="text-sm">🎯</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ number_format($counts['target_weight_kg']) }} kg target</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-amber-600 uppercase tracking-wider">{{ __('Sampingan (Bycatch)') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-amber-600">{{ $counts['bycatch_count'] }}</span>
                    <span class="text-sm">⚠️</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Tetap didaratkan') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-rose-600 uppercase tracking-wider">{{ __('Dibuang (Discard)') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-rose-600">{{ $counts['discarded_count'] }}</span>
                    <span class="text-sm">❌</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Tidak dikonsumsi/lindung') }}</span>
            </div>
        </div>

        {{-- Filters & Search Toolbar --}}
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form method="GET" action="{{ route('catches.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search Box --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Cari Ikan, Nama Lokal, atau Trip') }}</label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Nama ikan, kode FAO, nama Aceh, trip..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">🔍</span>
                    </div>
                </div>

                {{-- Filter Status Tangkapan --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Status Tangkapan') }}</label>
                    <select name="catch_status" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="target" {{ request('catch_status') == 'target' ? 'selected' : '' }}>🎯 Target (Utama)</option>
                        <option value="bycatch" {{ request('catch_status') == 'bycatch' ? 'selected' : '' }}>⚠️ Bycatch (Sampingan)</option>
                        <option value="discarded" {{ request('catch_status') == 'discarded' ? 'selected' : '' }}>❌ Discarded (Dibuang)</option>
                    </select>
                </div>

                {{-- Filter Kelompok Ikan --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Kelompok Ikan') }}</label>
                    <select name="fish_group" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kelompok Ikan') }}</option>
                        @foreach($fishGroups as $val => $lbl)
                            <option value="{{ $val }}" {{ request('fish_group') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-ocean-800 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors text-center">
                        {{ __('Filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'catch_status', 'fish_group', 'fishing_trip_id', 'fish_species_id']))
                        <a href="{{ route('catches.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors" title="{{ __('Reset Filter') }}">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">#</th>
                            <th class="px-4 py-3.5">{{ __('Spesies Ikan') }}</th>
                            <th class="px-4 py-3.5">{{ __('Trip & Kapal') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Status Tangkapan') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Berat (kg)') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Jumlah Ekor') }}</th>
                            <th class="px-4 py-3.5">{{ __('Setting Alat') }}</th>
                            <th class="px-4 py-3.5">{{ __('Catatan / Kondisi') }}</th>
                            <th class="px-4 py-3.5 text-center w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($catches as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $catches->firstItem() + $index }}
                                </td>

                                {{-- Spesies Ikan --}}
                                <td class="px-4 py-3">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="font-mono text-xs font-bold px-1.5 py-0.5 rounded bg-ocean-100 text-ocean-800 border border-ocean-200">
                                            {{ $item->species->fao_code ?? '-' }}
                                        </span>
                                        <span class="font-bold text-slate-900 text-sm">
                                            {{ $item->species->local_name_id ?? '-' }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-500 italic mt-0.5">
                                        {{ $item->species->scientific_name ?? '' }}
                                    </div>
                                    @if(!empty($item->species->local_name_aceh))
                                        <div class="text-[11px] text-emerald-700 font-medium mt-0.5">
                                            Aceh: {{ $item->species->local_name_aceh }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Trip & Kapal --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($item->fishingTrip)
                                        <a href="{{ route('trips.index', ['search' => $item->fishingTrip->trip_number]) }}"
                                           class="font-mono text-xs font-bold text-ocean-700 hover:text-ocean-900 bg-ocean-50 hover:bg-ocean-100 px-2 py-0.5 rounded border border-ocean-200 inline-block">
                                            {{ $item->fishingTrip->trip_number }}
                                        </a>
                                        <div class="font-medium text-xs text-slate-900 mt-1 flex items-center gap-1">
                                            <span>🚢</span>
                                            <span>{{ $item->fishingTrip->vessel->name ?? '-' }}</span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Trip tidak terhubung') }}</span>
                                    @endif
                                </td>

                                {{-- Status Tangkapan --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $cs = $item->catch_status;
                                        $csClass = match($cs) {
                                            'target'    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'bycatch'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'discarded' => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default     => 'bg-slate-50 text-slate-600 border-slate-200',
                                        };
                                        $csLabel = match($cs) {
                                            'target'    => '🎯 Target Utama',
                                            'bycatch'   => '⚠️ Bycatch',
                                            'discarded' => '❌ Discarded',
                                            default     => $cs,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $csClass }}">
                                        {{ $csLabel }}
                                    </span>
                                </td>

                                {{-- Berat (kg) --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-black text-slate-900">
                                        {{ number_format($item->weight_kg, 1) }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500">kg</span>
                                    @if($item->weight_kg >= 1000)
                                        <div class="text-[11px] text-ocean-700 font-semibold mt-0.5">
                                            ({{ round($item->weight_kg / 1000, 2) }} ton)
                                        </div>
                                    @endif
                                </td>

                                {{-- Jumlah Ekor --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap text-xs">
                                    @if($item->fish_count !== null)
                                        <span class="font-bold text-slate-800">{{ number_format($item->fish_count) }}</span>
                                        <span class="text-slate-500 text-[11px]">ekor</span>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Curah / bulk') }}</span>
                                    @endif
                                </td>

                                {{-- Setting Alat --}}
                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                    @if($item->fishingEffort)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold border border-slate-200">
                                            Setting ke-{{ $item->fishingEffort->setting_number }}
                                        </span>
                                        <div class="text-[11px] text-slate-500 mt-0.5 truncate max-w-[140px]">
                                            {{ $item->fishingEffort->fishingGear->name ?? '-' }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">{{ __('Total Trip') }}</span>
                                    @endif
                                </td>

                                {{-- Catatan --}}
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-700 line-clamp-2 max-w-xs" title="{{ $item->notes }}">
                                        {{ $item->notes ?? '-' }}
                                    </p>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Detail Button --}}
                                        <button type="button"
                                                @click="openDetail({{ json_encode([
                                                    'id' => $item->id,
                                                    'species_code' => $item->species->fao_code ?? '-',
                                                    'indonesian_name' => $item->species->local_name_id ?? '-',
                                                    'scientific_name' => $item->species->scientific_name ?? '-',
                                                    'local_name' => $item->species->local_name_aceh ?? '-',
                                                    'english_name' => $item->species->english_name ?? '-',
                                                    'family' => $item->species->family ?? '-',
                                                    'trip_number' => $item->fishingTrip->trip_number ?? '-',
                                                    'vessel_name' => $item->fishingTrip->vessel->name ?? '-',
                                                    'captain_name' => $item->fishingTrip->captain->name ?? '-',
                                                    'setting_info' => $item->fishingEffort ? ('Setting ke-' . $item->fishingEffort->setting_number . ' (' . ($item->fishingEffort->fishingGear->name ?? '-') . ')') : 'Akumulasi Trip',
                                                    'weight_kg' => $item->weight_kg,
                                                    'fish_count' => $item->fish_count,
                                                    'catch_status' => $item->catch_status,
                                                    'catch_status_label' => $csLabel,
                                                    'notes' => $item->notes ?? '-',
                                                ]) }})"
                                                class="p-1.5 text-slate-500 hover:text-ocean-700 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Lihat Rincian') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button type="button"
                                                @click="openEdit({{ json_encode([
                                                    'id' => $item->id,
                                                    'fishing_trip_id' => $item->fishing_trip_id,
                                                    'fishing_effort_id' => $item->fishing_effort_id,
                                                    'fish_species_id' => $item->fish_species_id,
                                                    'fish_species_text' => '['.($item->species->fao_code ?? '-').'] '.($item->species->local_name_id ?? '-'),
                                                    'weight_kg' => $item->weight_kg,
                                                    'fish_count' => $item->fish_count,
                                                    'catch_status' => $item->catch_status,
                                                    'notes' => $item->notes,
                                                ]) }}, '{{ route('catches.update', $item) }}')"
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Ubah Data Tangkapan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('catches.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data tangkapan {{ $item->species->indonesian_name ?? '' }} ({{ $item->weight_kg }} kg)?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Hapus') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-4xl mb-3">🐟</div>
                                    <p class="text-base font-semibold text-slate-700">{{ __('Belum ada data hasil tangkapan') }}</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        {{ __('Gunakan tombol Catat Tangkapan Baru di atas untuk meregistrasi rincian spesies ikan hasil tangkapan nelayan.') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$catches" />
        </div>

        {{-- ======================================= --}}
        {{-- MODAL TAMBAH TANGKAPAN                  --}}
        {{-- ======================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showCreateModal = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showCreateModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🐟</span>
                        <h3 class="font-bold text-base">{{ __('Catat Hasil Tangkapan Baru') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('catches.store') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Pilih Trip Penangkapan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Trip Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="fishing_trip_id" x-model="createTripId" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="">-- {{ __('Pilih Trip Operasional') }} --</option>
                            @foreach($trips as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }} ({{ $t->departure_date ? $t->departure_date->format('d/m/Y') : '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Setting Alat Tangkap (Opsional) --}}
                    <div x-show="createTripId && getEffortsForTrip(createTripId).length > 0">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Terkait Siklus Setting Tertentu (Opsional)') }}
                        </label>
                        <select name="fishing_effort_id" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="">-- {{ __('Seluruh Trip (Tidak terikat spesifik setting)') }} --</option>
                            <template x-for="eff in getEffortsForTrip(createTripId)" :key="eff.id">
                                <option :value="eff.id" x-text="'Setting ke-' + eff.setting_number + ' (' + eff.gear_name + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Spesies Ikan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Jenis Ikan / Komoditas Spesies') }} <span class="text-rose-500">*</span>
                        </label>
                        <div x-data="{
                            open: false,
                            search: '',
                            options: [],
                            loading: false,
                            selectedText: '',
                            
                            async fetchOptions() {
                                this.loading = true;
                                try {
                                    const res = await fetch(`{{ route('master.species.search') }}?q=${encodeURIComponent(this.search)}&is_active=true`);
                                    const json = await res.json();
                                    this.options = json.data || [];
                                } catch(e) { this.options = []; }
                                this.loading = false;
                            },
                            selectOpt(opt) {
                                $refs.hiddenInput.value = opt.id;
                                this.selectedText = `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}`;
                                this.open = false;
                            }
                        }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                            <input type="hidden" name="fish_species_id" x-ref="hiddenInput" required>
                            
                            <div @click="open = !open" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus-within:ring-2 focus-within:ring-ocean-500 bg-white cursor-pointer flex justify-between items-center">
                                <span x-text="selectedText || '-- Pilih Spesies Ikan --'" :class="selectedText ? 'text-slate-900' : 'text-slate-500'"></span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg">
                                <div class="p-2 border-b border-slate-100">
                                    <input type="text" x-model="search" @input.debounce.300ms="fetchOptions" placeholder="Cari FAO, Nama Lokal, Ilmiah..." class="w-full text-sm border border-slate-300 rounded-md p-2 focus:ring-2 focus:ring-ocean-500">
                                </div>
                                <ul class="max-h-60 overflow-y-auto p-1">
                                    <li x-show="loading" class="p-2 text-xs text-slate-500 text-center">Mencari...</li>
                                    <li x-show="!loading && options.length === 0" class="p-2 text-xs text-slate-500 text-center">Tidak ditemukan</li>
                                    <template x-for="opt in options" :key="opt.id">
                                        <li @click="selectOpt(opt)" class="p-2 hover:bg-ocean-50 cursor-pointer rounded-md">
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="font-mono text-xs font-bold px-1.5 py-0.5 rounded bg-ocean-100 text-ocean-800" x-text="opt.fao_code || '-'"></span>
                                                <span class="font-bold text-slate-900 text-sm" x-text="opt.local_name_id || '-'"></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 italic mt-0.5" x-text="opt.scientific_name"></div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Berat & Jumlah Ekor --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Berat Tangkapan (Kg)') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="weight_kg" placeholder="Contoh: 1250.50" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-bold text-slate-900">
                            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Gunakan satuan kilogram (kg)') }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Ekor (Estimasi)') }}
                            </label>
                            <input type="number" name="fish_count" placeholder="Contoh: 450 (kosongkan jika curah)" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Opsional untuk ikan ukuran besar') }}</span>
                        </div>
                    </div>

                    {{-- Status Tangkapan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Status Hasil Tangkapan') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="catch_status" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="target" selected>🎯 {{ __('Hasil Tangkapan Utama (Target)') }}</option>
                            <option value="bycatch">⚠️ {{ __('Hasil Tangkapan Sampingan (Bycatch)') }}</option>
                            <option value="discarded">❌ {{ __('Hasil Tangkapan Dibuang (Discarded)') }}</option>
                        </select>
                    </div>

                    {{-- Catatan Penanganan / Kualitas --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Catatan Kualitas & Penanganan di Kapal') }}
                        </label>
                        <textarea name="notes" rows="2" placeholder="Contoh: Ikan segar kualitas super, tersimpan di palka es nomor 1..." class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Tangkapan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL UBAH (EDIT) TANGKAPAN             --}}
        {{-- ======================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showEditModal = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showEditModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">✏️</span>
                        <h3 class="font-bold text-base">{{ __('Ubah Data Hasil Tangkapan') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" :action="editAction" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Pilih Trip Penangkapan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Trip Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="fishing_trip_id" x-model="editItem.fishing_trip_id" @change="editTripId = $event.target.value" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            @foreach($trips as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Setting Alat Tangkap (Opsional) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Terkait Siklus Setting Tertentu (Opsional)') }}
                        </label>
                        <select name="fishing_effort_id" x-model="editItem.fishing_effort_id" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="">-- {{ __('Seluruh Trip (Tidak terikat spesifik setting)') }} --</option>
                            <template x-for="eff in getEffortsForTrip(editTripId)" :key="eff.id">
                                <option :value="eff.id" :selected="eff.id == editItem.fishing_effort_id" x-text="'Setting ke-' + eff.setting_number + ' (' + eff.gear_name + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Spesies Ikan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Jenis Ikan / Komoditas Spesies') }} <span class="text-rose-500">*</span>
                        </label>
                        <div x-data="{
                            open: false,
                            search: '',
                            options: [],
                            loading: false,
                            
                            async fetchOptions() {
                                this.loading = true;
                                try {
                                    const res = await fetch(`{{ route('master.species.search') }}?q=${encodeURIComponent(this.search)}`);
                                    const json = await res.json();
                                    this.options = json.data || [];
                                } catch(e) { this.options = []; }
                                this.loading = false;
                            },
                            selectOpt(opt) {
                                editItem.fish_species_id = opt.id;
                                editItem.fish_species_text = `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}`;
                                this.open = false;
                            }
                        }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                            <input type="hidden" name="fish_species_id" x-model="editItem.fish_species_id" required>
                            
                            <div @click="open = !open" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus-within:ring-2 focus-within:ring-ocean-500 bg-white cursor-pointer flex justify-between items-center">
                                <span x-text="editItem.fish_species_text || '-- Pilih Spesies Ikan --'" :class="editItem.fish_species_text ? 'text-slate-900' : 'text-slate-500'"></span>
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </div>

                            <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg">
                                <div class="p-2 border-b border-slate-100">
                                    <input type="text" x-model="search" @input.debounce.300ms="fetchOptions" placeholder="Cari FAO, Nama Lokal, Ilmiah..." class="w-full text-sm border border-slate-300 rounded-md p-2 focus:ring-2 focus:ring-ocean-500">
                                </div>
                                <ul class="max-h-60 overflow-y-auto p-1">
                                    <li x-show="loading" class="p-2 text-xs text-slate-500 text-center">Mencari...</li>
                                    <li x-show="!loading && options.length === 0" class="p-2 text-xs text-slate-500 text-center">Tidak ditemukan</li>
                                    <template x-for="opt in options" :key="opt.id">
                                        <li @click="selectOpt(opt)" class="p-2 hover:bg-ocean-50 cursor-pointer rounded-md">
                                            <div class="flex items-baseline gap-1.5">
                                                <span class="font-mono text-xs font-bold px-1.5 py-0.5 rounded bg-ocean-100 text-ocean-800" x-text="opt.fao_code || '-'"></span>
                                                <span class="font-bold text-slate-900 text-sm" x-text="opt.local_name_id || '-'"></span>
                                            </div>
                                            <div class="text-[11px] text-slate-500 italic mt-0.5" x-text="opt.scientific_name"></div>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Berat & Jumlah Ekor --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Berat Tangkapan (Kg)') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.01" name="weight_kg" x-model="editItem.weight_kg" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-bold text-slate-900">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Ekor (Estimasi)') }}
                            </label>
                            <input type="number" name="fish_count" x-model="editItem.fish_count" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Status Tangkapan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Status Hasil Tangkapan') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="catch_status" x-model="editItem.catch_status" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="target">🎯 {{ __('Hasil Tangkapan Utama (Target)') }}</option>
                            <option value="bycatch">⚠️ {{ __('Hasil Tangkapan Sampingan (Bycatch)') }}</option>
                            <option value="discarded">❌ {{ __('Hasil Tangkapan Dibuang (Discarded)') }}</option>
                        </select>
                    </div>

                    {{-- Catatan Penanganan / Kualitas --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Catatan Kualitas & Penanganan di Kapal') }}
                        </label>
                        <textarea name="notes" x-model="editItem.notes" rows="2" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL DETAIL RINCIAN TANGKAPAN          --}}
        {{-- ======================================= --}}
        <div x-show="showDetailModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showDetailModal = false">
            <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showDetailModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">🐟</span>
                        <h3 class="font-bold text-base">{{ __('Rincian Hasil Tangkapan Ikan') }}</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Content --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-start justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="font-mono text-xs font-bold px-2 py-0.5 rounded bg-ocean-100 text-ocean-800 border border-ocean-200" x-text="detailItem.species_code"></span>
                            <h4 class="text-lg font-bold text-slate-900 mt-1" x-text="detailItem.indonesian_name"></h4>
                            <span class="text-xs text-slate-500 italic block" x-text="detailItem.scientific_name"></span>
                            <template x-if="detailItem.local_name">
                                <span class="text-xs text-emerald-700 font-semibold block mt-0.5" x-text="'Nama Aceh: ' + detailItem.local_name"></span>
                            </template>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border"
                                  :class="{
                                      'bg-emerald-50 text-emerald-700 border-emerald-200': detailItem.catch_status == 'target',
                                      'bg-amber-50 text-amber-700 border-amber-200': detailItem.catch_status == 'bycatch',
                                      'bg-rose-50 text-rose-700 border-rose-200': detailItem.catch_status == 'discarded'
                                  }"
                                  x-text="detailItem.catch_status_label">
                            </span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Trip Operasional') }}</span>
                            <span class="font-mono font-bold text-ocean-800 text-sm mt-0.5 block" x-text="detailItem.trip_number"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Kapal / Nahkoda') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="detailItem.vessel_name"></span>
                            <span class="text-slate-500 block text-[11px]" x-text="'Nahkoda: ' + detailItem.captain_name"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Berat Tangkapan') }}</span>
                            <div class="mt-0.5">
                                <span class="text-lg font-black text-slate-900" x-text="detailItem.weight_kg"></span>
                                <span class="font-bold text-slate-600">kg</span>
                            </div>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Jumlah Ekor') }}</span>
                            <div class="mt-0.5">
                                <span class="text-lg font-black text-slate-900" x-text="detailItem.fish_count ? detailItem.fish_count + ' ekor' : 'Curah / bulk'"></span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Famili & Kelompok') }}</span>
                            <span class="font-semibold text-slate-800 text-xs mt-0.5 block" x-text="detailItem.family + ' (' + detailItem.fish_group + ')'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Keterkaitan Setting Alat') }}</span>
                            <span class="font-semibold text-slate-800 text-xs mt-0.5 block" x-text="detailItem.setting_info"></span>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">{{ __('Catatan Kualitas & Palka:') }}</span>
                        <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-200" x-text="detailItem.notes"></p>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button" @click="showDetailModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium text-sm rounded-lg transition-colors">
                            {{ __('Tutup') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
