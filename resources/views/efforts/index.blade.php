<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>⚙️</span>
            <span>{{ __('Pengumpulan Data: Upaya Penangkapan Ikan (Fishing Effort)') }}</span>
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

        // Helper to calculate hours between setting & hauling dates
        calculateDuration(settingVal, haulingVal) {
            if (!settingVal || !haulingVal) return null;
            const start = new Date(settingVal);
            const end = new Date(haulingVal);
            const diffMs = end - start;
            if (diffMs > 0) {
                return (diffMs / (1000 * 60 * 60)).toFixed(2);
            }
            return null;
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">⚙️</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Operasi Setting & Hauling Alat Tangkap') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Upaya Penangkapan Ikan (Fishing Effort)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Pencatatan siklus teknis penurunan (setting) dan penarikan (hauling) alat penangkapan ikan: durasi perendaman alat (soaking time), panjang bentangan jaring, jumlah mata pancing, serta pelacakan koordinat GPS operasi di laut.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Catat Effort Baru') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Total Siklus') }}</span>
                <span class="text-2xl font-black text-slate-800 mt-1">{{ number_format($counts['total_efforts']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Operasi setting/hauling') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-ocean-700 uppercase tracking-wider">{{ __('Total Jam Operasi') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-ocean-700">{{ number_format($counts['total_hours'], 1) }}</span>
                    <span class="text-xs font-bold text-slate-500">jam</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Akumulasi perendaman') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-teal-600 uppercase tracking-wider">{{ __('Rata-rata Durasi') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-teal-600">{{ $counts['avg_duration'] }}</span>
                    <span class="text-xs font-bold text-slate-500">jam</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Per siklus setting') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-indigo-600 uppercase tracking-wider">{{ __('Bentang Jaring') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-indigo-600">{{ number_format($counts['total_net_length']) }}</span>
                    <span class="text-xs font-bold text-slate-500">m</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Total panjang jaring') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-amber-600 uppercase tracking-wider">{{ __('Mata Pancing') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-amber-600">{{ number_format($counts['total_hooks']) }}</span>
                    <span class="text-xs font-bold text-slate-500">hooks</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Rawai & pancing ulur') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-purple-600 uppercase tracking-wider">{{ __('Total Tebar') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-purple-600">{{ number_format($counts['total_settings']) }}</span>
                    <span class="text-xs font-bold text-slate-500">kali</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Frekuensi penurunan') }}</span>
            </div>
        </div>

        {{-- Filters & Search Toolbar --}}
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form method="GET" action="{{ route('efforts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search Box --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Cari Trip, Kapal, atau Alat Tangkap') }}</label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Nomor trip, nama kapal, alat tangkap..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">🔍</span>
                    </div>
                </div>

                {{-- Filter Alat Tangkap --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Alat Penangkapan Ikan') }}</label>
                    <select name="fishing_gear_id" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Alat Tangkap') }}</option>
                        @foreach($gears as $g)
                            <option value="{{ $g->id }}" {{ request('fishing_gear_id') == $g->id ? 'selected' : '' }}>
                                [{{ $g->code }}] {{ $g->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Trip --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Trip Penangkapan') }}</label>
                    <select name="fishing_trip_id" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Trip Operasional') }}</option>
                        @foreach($trips as $t)
                            <option value="{{ $t->id }}" {{ request('fishing_trip_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->trip_number }} - {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-ocean-800 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors text-center">
                        {{ __('Filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'fishing_gear_id', 'fishing_trip_id', 'date_from', 'date_to']))
                        <a href="{{ route('efforts.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors" title="{{ __('Reset Filter') }}">
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
                            <th class="px-4 py-3.5">{{ __('Trip & Kapal') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Urutan Setting') }}</th>
                            <th class="px-4 py-3.5">{{ __('Alat Tangkap') }}</th>
                            <th class="px-4 py-3.5">{{ __('Waktu & Durasi Perendaman') }}</th>
                            <th class="px-4 py-3.5">{{ __('Spesifikasi Intensitas') }}</th>
                            <th class="px-4 py-3.5">{{ __('Koordinat GPS (Setting ➔ Hauling)') }}</th>
                            <th class="px-4 py-3.5 text-center w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($efforts as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $efforts->firstItem() + $index }}
                                </td>

                                {{-- Trip & Kapal --}}
                                <td class="px-4 py-3">
                                    @if($item->fishingTrip)
                                        <a href="{{ route('trips.index', ['search' => $item->fishingTrip->trip_number]) }}"
                                           class="font-mono text-xs font-bold text-ocean-700 hover:text-ocean-900 bg-ocean-50 hover:bg-ocean-100 px-2 py-0.5 rounded border border-ocean-200 inline-block">
                                            {{ $item->fishingTrip->trip_number }}
                                        </a>
                                        <div class="font-medium text-xs text-slate-900 mt-1 flex items-center gap-1">
                                            <span>🚢</span>
                                            <span>{{ $item->fishingTrip->vessel->name ?? '-' }}</span>
                                        </div>
                                        @if($item->fishingTrip->captain)
                                            <div class="text-[11px] text-slate-500">
                                                {{ __('Nahkoda') }}: {{ $item->fishingTrip->captain->name }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Trip tidak terhubung') }}</span>
                                    @endif
                                </td>

                                {{-- Urutan Setting & Frekuensi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-ocean-100 text-ocean-800 border border-ocean-200">
                                        {{ __('Setting ke-') }}{{ $item->setting_number }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 mt-1">
                                        {{ $item->setting_count }}x {{ __('tebar/ulur') }}
                                    </div>
                                </td>

                                {{-- Alat Tangkap --}}
                                <td class="px-4 py-3">
                                    @if($item->fishingGear)
                                        <div class="font-semibold text-xs text-slate-900">
                                            {{ $item->fishingGear->name }}
                                        </div>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="font-mono text-[10px] text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                                {{ $item->fishingGear->code }}
                                            </span>
                                            <span class="text-[10px] text-slate-500 capitalize">
                                                ({{ str_replace('_', ' ', $item->fishingGear->category) }})
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Alat tidak dicatat') }}</span>
                                    @endif
                                </td>

                                {{-- Waktu & Durasi --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs text-slate-700">
                                        <span class="text-emerald-700 font-medium">⬇️ {{ __('Set') }}:</span>
                                        {{ $item->setting_date ? $item->setting_date->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    <div class="text-xs text-slate-700 mt-0.5">
                                        <span class="text-rose-700 font-medium">⬆️ {{ __('Haul') }}:</span>
                                        {{ $item->hauling_date ? $item->hauling_date->format('d/m/Y H:i') : '-' }}
                                    </div>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-800 bg-teal-50 px-2 py-0.5 rounded border border-teal-200">
                                            <span>⏱️</span>
                                            <span>{{ $item->duration_hours !== null ? $item->duration_hours . ' jam' : '-' }}</span>
                                        </span>
                                    </div>
                                </td>

                                {{-- Spesifikasi Intensitas --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($item->net_length_meters !== null)
                                        <div class="text-xs font-semibold text-indigo-700">
                                            📏 {{ number_format($item->net_length_meters) }} m
                                            <span class="text-[11px] font-normal text-slate-500 block">{{ __('Panjang Jaring') }}</span>
                                        </div>
                                    @endif
                                    @if($item->hook_count !== null)
                                        <div class="text-xs font-semibold text-amber-700 mt-0.5">
                                            🪝 {{ number_format($item->hook_count) }}
                                            <span class="text-[11px] font-normal text-slate-500 block">{{ __('Mata Pancing') }}</span>
                                        </div>
                                    @endif
                                    @if($item->net_length_meters === null && $item->hook_count === null)
                                        <span class="text-xs text-slate-400 italic">{{ __('Tidak dispesifikasi') }}</span>
                                    @endif
                                </td>

                                {{-- Titik Koordinat GPS --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-xs font-mono text-slate-700">
                                        @if($item->latitude_setting !== null && $item->longitude_setting !== null)
                                            <div>
                                                <span class="text-emerald-700 font-semibold">S:</span>
                                                {{ number_format($item->latitude_setting, 4) }}°, {{ number_format($item->longitude_setting, 4) }}°
                                            </div>
                                        @endif
                                        @if($item->latitude_hauling !== null && $item->longitude_hauling !== null)
                                            <div class="mt-0.5">
                                                <span class="text-rose-700 font-semibold">H:</span>
                                                {{ number_format($item->latitude_hauling, 4) }}°, {{ number_format($item->longitude_hauling, 4) }}°
                                            </div>
                                        @endif
                                    </div>
                                    @if($item->latitude_setting !== null && $item->longitude_setting !== null)
                                        <div class="mt-1">
                                            <a href="https://www.openstreetmap.org/?mlat={{ $item->latitude_setting }}&mlon={{ $item->longitude_setting }}#map=12/{{ $item->latitude_setting }}/{{ $item->longitude_setting }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] text-ocean-600 hover:text-ocean-800 hover:underline font-medium">
                                                <span>🗺️</span>
                                                <span>{{ __('Peta Setting') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Detail Button --}}
                                        <button type="button"
                                                @click="openDetail({{ json_encode([
                                                    'id' => $item->id,
                                                    'trip_number' => $item->fishingTrip->trip_number ?? '-',
                                                    'vessel_name' => $item->fishingTrip->vessel->name ?? '-',
                                                    'captain_name' => $item->fishingTrip->captain->name ?? '-',
                                                    'gear_name' => $item->fishingGear->name ?? '-',
                                                    'gear_code' => $item->fishingGear->code ?? '-',
                                                    'setting_number' => $item->setting_number,
                                                    'setting_count' => $item->setting_count,
                                                    'setting_date' => $item->setting_date ? $item->setting_date->format('d F Y, H:i') . ' WIB' : '-',
                                                    'hauling_date' => $item->hauling_date ? $item->hauling_date->format('d F Y, H:i') . ' WIB' : '-',
                                                    'duration_hours' => $item->duration_hours,
                                                    'net_length_meters' => $item->net_length_meters,
                                                    'hook_count' => $item->hook_count,
                                                    'latitude_setting' => $item->latitude_setting,
                                                    'longitude_setting' => $item->longitude_setting,
                                                    'latitude_hauling' => $item->latitude_hauling,
                                                    'longitude_hauling' => $item->longitude_hauling,
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
                                                    'fishing_gear_id' => $item->fishing_gear_id,
                                                    'setting_number' => $item->setting_number,
                                                    'setting_date' => $item->setting_date ? $item->setting_date->format('Y-m-d\TH:i') : '',
                                                    'hauling_date' => $item->hauling_date ? $item->hauling_date->format('Y-m-d\TH:i') : '',
                                                    'duration_hours' => $item->duration_hours,
                                                    'setting_count' => $item->setting_count,
                                                    'hook_count' => $item->hook_count,
                                                    'net_length_meters' => $item->net_length_meters,
                                                    'latitude_setting' => $item->latitude_setting,
                                                    'longitude_setting' => $item->longitude_setting,
                                                    'latitude_hauling' => $item->latitude_hauling,
                                                    'longitude_hauling' => $item->longitude_hauling,
                                                ]) }}, '{{ route('efforts.update', $item) }}')"
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Ubah Upaya Penangkapan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('efforts.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan setting ke-{{ $item->setting_number }} ini?')">
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
                                <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-4xl mb-3">⚙️</div>
                                    <p class="text-base font-semibold text-slate-700">{{ __('Belum ada data upaya penangkapan (effort)') }}</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        {{ __('Gunakan tombol Catat Effort Baru di atas untuk meregistrasi siklus penurunan dan penarikan alat tangkap nelayan.') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Links --}}
            @if($efforts->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50">
                    {{ $efforts->links() }}
                </div>
            @endif
        </div>

        {{-- ======================================= --}}
        {{-- MODAL TAMBAH EFFORT                     --}}
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
                        <span class="text-lg">⚙️</span>
                        <h3 class="font-bold text-base">{{ __('Catat Upaya Penangkapan Baru (Setting/Hauling)') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('efforts.store') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Pilih Trip & Alat Tangkap --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Trip Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="fishing_trip_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Trip Operasional') }} --</option>
                                @foreach($trips as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Alat Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="fishing_gear_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Alat Tangkap') }} --</option>
                                @foreach($gears as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Urutan Setting & Frekuensi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Urutan Setting (Ke-)') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="setting_number" value="1" min="1" max="100" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Contoh: 1 untuk setting pertama, 2 untuk kedua') }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Frekuensi Tebar / Ulur') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="setting_count" value="1" min="1" max="1000" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Waktu Operasi & Durasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Penurunan (Setting)') }}
                            </label>
                            <input type="datetime-local" name="setting_date" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Penarikan (Hauling)') }}
                            </label>
                            <input type="datetime-local" name="hauling_date" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Durasi Perendaman (Jam)') }}
                            </label>
                            <input type="number" step="0.01" name="duration_hours" placeholder="Auto / isi manual" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Spesifikasi Intensitas Alat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Panjang Jaring (Meter)') }}
                            </label>
                            <input type="number" step="0.01" name="net_length_meters" placeholder="Khusus pukat cincin / jaring insang" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Mata Pancing (Hooks)') }}
                            </label>
                            <input type="number" name="hook_count" placeholder="Khusus pancing ulur / rawai tuna" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Titik Koordinat GPS Setting & Hauling --}}
                    <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-800 block">{{ __('Posisi Titik Geospasial GPS di Laut:') }}</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-emerald-700 mb-0.5">
                                    {{ __('Lintang Setting (Lat)') }}
                                </label>
                                <input type="number" step="0.0000001" name="latitude_setting" placeholder="Contoh: 5.8821000" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-emerald-700 mb-0.5">
                                    {{ __('Bujur Setting (Lon)') }}
                                </label>
                                <input type="number" step="0.0000001" name="longitude_setting" placeholder="Contoh: 94.6710000" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-200/60">
                            <div>
                                <label class="block text-[11px] font-semibold text-rose-700 mb-0.5">
                                    {{ __('Lintang Hauling (Lat)') }}
                                </label>
                                <input type="number" step="0.0000001" name="latitude_hauling" placeholder="Contoh: 5.8750000" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-rose-700 mb-0.5">
                                    {{ __('Bujur Hauling (Lon)') }}
                                </label>
                                <input type="number" step="0.0000001" name="longitude_hauling" placeholder="Contoh: 94.6650000" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                        </div>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Effort') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL UBAH (EDIT) EFFORT                --}}
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
                        <h3 class="font-bold text-base">{{ __('Ubah Upaya Penangkapan Ikan') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" :action="editAction" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Pilih Trip & Alat Tangkap --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Trip Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="fishing_trip_id" x-model="editItem.fishing_trip_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                @foreach($trips as $t)
                                    <option value="{{ $t->id }}">{{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Alat Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="fishing_gear_id" x-model="editItem.fishing_gear_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                @foreach($gears as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Urutan Setting & Frekuensi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Urutan Setting (Ke-)') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="setting_number" x-model="editItem.setting_number" min="1" max="100" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Frekuensi Tebar / Ulur') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" name="setting_count" x-model="editItem.setting_count" min="1" max="1000" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Waktu Operasi & Durasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Penurunan (Setting)') }}
                            </label>
                            <input type="datetime-local" name="setting_date" x-model="editItem.setting_date" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Penarikan (Hauling)') }}
                            </label>
                            <input type="datetime-local" name="hauling_date" x-model="editItem.hauling_date" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Durasi Perendaman (Jam)') }}
                            </label>
                            <input type="number" step="0.01" name="duration_hours" x-model="editItem.duration_hours" class="w-full text-xs border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Spesifikasi Intensitas Alat --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Panjang Jaring (Meter)') }}
                            </label>
                            <input type="number" step="0.01" name="net_length_meters" x-model="editItem.net_length_meters" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Mata Pancing (Hooks)') }}
                            </label>
                            <input type="number" name="hook_count" x-model="editItem.hook_count" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Titik Koordinat GPS Setting & Hauling --}}
                    <div class="space-y-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <span class="text-xs font-bold text-slate-800 block">{{ __('Posisi Titik Geospasial GPS di Laut:') }}</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-semibold text-emerald-700 mb-0.5">
                                    {{ __('Lintang Setting (Lat)') }}
                                </label>
                                <input type="number" step="0.0000001" name="latitude_setting" x-model="editItem.latitude_setting" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-emerald-700 mb-0.5">
                                    {{ __('Bujur Setting (Lon)') }}
                                </label>
                                <input type="number" step="0.0000001" name="longitude_setting" x-model="editItem.longitude_setting" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1 border-t border-slate-200/60">
                            <div>
                                <label class="block text-[11px] font-semibold text-rose-700 mb-0.5">
                                    {{ __('Lintang Hauling (Lat)') }}
                                </label>
                                <input type="number" step="0.0000001" name="latitude_hauling" x-model="editItem.latitude_hauling" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                            <div>
                                <label class="block text-[11px] font-semibold text-rose-700 mb-0.5">
                                    {{ __('Bujur Hauling (Lon)') }}
                                </label>
                                <input type="number" step="0.0000001" name="longitude_hauling" x-model="editItem.longitude_hauling" class="w-full text-xs border border-slate-300 rounded-lg p-2 font-mono">
                            </div>
                        </div>
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
        {{-- MODAL DETAIL RINCIAN EFFORT             --}}
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
                        <span class="text-lg">⚙️</span>
                        <h3 class="font-bold text-base">{{ __('Rincian Siklus Upaya Penangkapan') }}</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Content --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-xs text-slate-500 block uppercase font-medium">{{ __('Trip Operasional') }}</span>
                            <span class="text-base font-bold text-ocean-800 font-mono" x-text="detailItem.trip_number"></span>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-500 block uppercase font-medium">{{ __('Kapal / Nahkoda') }}</span>
                            <span class="text-sm font-semibold text-slate-800" x-text="detailItem.vessel_name"></span>
                            <span class="text-xs text-slate-500 block" x-text="'Nahkoda: ' + detailItem.captain_name"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Alat Penangkapan Ikan') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="detailItem.gear_name + ' (' + detailItem.gear_code + ')'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Urutan & Frekuensi') }}</span>
                            <span class="font-semibold text-ocean-700 text-sm mt-0.5 block" x-text="'Setting ke-' + detailItem.setting_number + ' (' + detailItem.setting_count + 'x tebar)'"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Waktu Setting (Penurunan)') }}</span>
                            <span class="font-semibold text-emerald-800 text-sm mt-0.5 block" x-text="detailItem.setting_date"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Waktu Hauling (Penarikan)') }}</span>
                            <span class="font-semibold text-rose-800 text-sm mt-0.5 block" x-text="detailItem.hauling_date"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs text-center">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Durasi Perendaman') }}</span>
                            <span class="font-bold text-teal-700 text-sm mt-0.5 block" x-text="(detailItem.duration_hours || '-') + ' jam'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Panjang Jaring') }}</span>
                            <span class="font-bold text-indigo-700 text-sm mt-0.5 block" x-text="detailItem.net_length_meters ? detailItem.net_length_meters + ' m' : '-'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Mata Pancing') }}</span>
                            <span class="font-bold text-amber-700 text-sm mt-0.5 block" x-text="detailItem.hook_count ? detailItem.hook_count + ' hooks' : '-'"></span>
                        </div>
                    </div>

                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <span class="text-slate-700 font-semibold block">{{ __('Titik Koordinat GPS Operasi di Laut:') }}</span>
                        <div class="flex items-center justify-between border-b border-slate-200/60 pb-2">
                            <div>
                                <span class="text-emerald-700 font-bold block">{{ __('Lokasi Setting:') }}</span>
                                <span class="font-mono text-slate-800" x-text="(detailItem.latitude_setting || '-') + '°, ' + (detailItem.longitude_setting || '-') + '°'"></span>
                            </div>
                            <template x-if="detailItem.latitude_setting && detailItem.longitude_setting">
                                <a :href="'https://www.openstreetmap.org/?mlat=' + detailItem.latitude_setting + '&mlon=' + detailItem.longitude_setting + '#map=12/' + detailItem.latitude_setting + '/' + detailItem.longitude_setting"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="text-ocean-600 hover:text-ocean-800 font-semibold underline inline-flex items-center gap-1">
                                    <span>🌐</span>
                                    <span>{{ __('Peta Setting') }}</span>
                                </a>
                            </template>
                        </div>
                        <div class="flex items-center justify-between pt-1">
                            <div>
                                <span class="text-rose-700 font-bold block">{{ __('Lokasi Hauling:') }}</span>
                                <span class="font-mono text-slate-800" x-text="(detailItem.latitude_hauling || '-') + '°, ' + (detailItem.longitude_hauling || '-') + '°'"></span>
                            </div>
                            <template x-if="detailItem.latitude_hauling && detailItem.longitude_hauling">
                                <a :href="'https://www.openstreetmap.org/?mlat=' + detailItem.latitude_hauling + '&mlon=' + detailItem.longitude_hauling + '#map=12/' + detailItem.latitude_hauling + '/' + detailItem.longitude_hauling"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="text-ocean-600 hover:text-ocean-800 font-semibold underline inline-flex items-center gap-1">
                                    <span>🌐</span>
                                    <span>{{ __('Peta Hauling') }}</span>
                                </a>
                            </template>
                        </div>
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
