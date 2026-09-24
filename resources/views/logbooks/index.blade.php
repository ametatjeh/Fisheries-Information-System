<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>📋</span>
            <span>{{ __('Pengumpulan Data: Buku Catatan Harian Kapal (Fishing Logbooks)') }}</span>
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">📋</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Buku Jurnal Harian Pelayaran Nelayan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Buku Catatan Harian Kapal (Fishing Logbooks)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Pencatatan kronologis harian operasi penangkapan ikan di laut: waktu setting & hauling, koordinat GPS lintang-bujur, parameter cuaca maritim, tinggi gelombang laut, dan catatan navigasi kapal nelayan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Catat Logbook Baru') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Total Logbook') }}</span>
                <span class="text-2xl font-black text-slate-800 mt-1">{{ number_format($counts['total']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Catatan harian') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-emerald-600 uppercase tracking-wider">{{ __('Cuaca Cerah') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-emerald-600">{{ number_format($counts['cerah']) }}</span>
                    <span class="text-sm">☀️</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Kondisi optimal') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-amber-600 uppercase tracking-wider">{{ __('Berawan') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-amber-600">{{ number_format($counts['berawan']) }}</span>
                    <span class="text-sm">⛅</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Teduh / berawan') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-rose-600 uppercase tracking-wider">{{ __('Hujan / Badai') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-rose-600">{{ number_format($counts['hujan_badai']) }}</span>
                    <span class="text-sm">🌧️</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Cuaca ekstrem') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-blue-600 uppercase tracking-wider">{{ __('Koordinat GPS') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-blue-600">{{ number_format($counts['with_coordinates']) }}</span>
                    <span class="text-sm">🌐</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Titik geospasial') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-cyan-600 uppercase tracking-wider">{{ __('Rata-rata Ombak') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-cyan-600">{{ $counts['avg_wave'] }}</span>
                    <span class="text-xs font-bold text-slate-500">m</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Tinggi gelombang') }}</span>
            </div>
        </div>

        {{-- Filters & Search Toolbar --}}
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form method="GET" action="{{ route('logbooks.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search Box --}}
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Cari Aktivitas, Kapal, atau No. Trip') }}</label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari aktivitas, no trip, kapal, ombak..."
                               class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">🔍</span>
                    </div>
                </div>

                {{-- Filter Cuaca --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Kondisi Cuaca') }}</label>
                    <select name="weather_condition" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kondisi Cuaca') }}</option>
                        @foreach($weatherOptions as $val => $label)
                            <option value="{{ $val }}" {{ request('weather_condition') == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Trip --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Trip Penangkapan') }}</label>
                    <select name="fishing_trip_id" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Semua Trip') }}</option>
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
                    @if(request()->hasAny(['search', 'weather_condition', 'fishing_trip_id', 'date_from', 'date_to']))
                        <a href="{{ route('logbooks.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors" title="{{ __('Reset Filter') }}">
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
                            <th class="px-4 py-3.5">{{ __('Tanggal & Waktu') }}</th>
                            <th class="px-4 py-3.5">{{ __('Trip & Kapal') }}</th>
                            <th class="px-4 py-3.5">{{ __('Koordinat GPS') }}</th>
                            <th class="px-4 py-3.5">{{ __('Cuaca & Gelombang') }}</th>
                            <th class="px-4 py-3.5">{{ __('Uraian Kegiatan') }}</th>
                            <th class="px-4 py-3.5 text-center w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($logbooks as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $logbooks->firstItem() + $index }}
                                </td>

                                {{-- Tanggal & Waktu --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-sm">
                                        {{ $item->log_date ? $item->log_date->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                        <span>⏰</span>
                                        <span>{{ $item->log_time ? \Illuminate\Support\Str::substr($item->log_time, 0, 5) . ' WIB' : 'Waktu tidak dicatat' }}</span>
                                    </div>
                                </td>

                                {{-- Trip & Kapal --}}
                                <td class="px-4 py-3">
                                    @if($item->fishingTrip)
                                        <div class="flex items-center gap-1.5">
                                            <a href="{{ route('trips.index', ['search' => $item->fishingTrip->trip_number]) }}"
                                               class="font-mono text-xs font-bold text-ocean-700 hover:text-ocean-900 bg-ocean-50 hover:bg-ocean-100 px-2 py-0.5 rounded border border-ocean-200">
                                                {{ $item->fishingTrip->trip_number }}
                                            </a>
                                        </div>
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

                                {{-- Koordinat GPS --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($item->latitude !== null && $item->longitude !== null)
                                        <div class="font-mono text-xs text-slate-800 bg-slate-100 px-2 py-1 rounded inline-block">
                                            {{ number_format($item->latitude, 5) }}°, {{ number_format($item->longitude, 5) }}°
                                        </div>
                                        <div class="mt-1">
                                            <a href="https://www.openstreetmap.org/?mlat={{ $item->latitude }}&mlon={{ $item->longitude }}#map=12/{{ $item->latitude }}/{{ $item->longitude }}"
                                               target="_blank"
                                               rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-[11px] text-ocean-600 hover:text-ocean-800 hover:underline font-medium">
                                                <span>🗺️</span>
                                                <span>{{ __('Lihat di Peta') }}</span>
                                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Tidak ada data GPS') }}</span>
                                    @endif
                                </td>

                                {{-- Cuaca & Gelombang --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        @php
                                            $wc = $item->weather_condition;
                                            $wcClass = match($wc) {
                                                'cerah'        => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                                'berawan'      => 'bg-amber-50 text-amber-700 border-amber-200',
                                                'hujan_ringan' => 'bg-sky-50 text-sky-700 border-sky-200',
                                                'hujan_lebat'  => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                                'badai'        => 'bg-rose-50 text-rose-700 border-rose-200',
                                                default        => 'bg-slate-50 text-slate-600 border-slate-200',
                                            };
                                            $wcIcon = match($wc) {
                                                'cerah'        => '☀️ Cerah',
                                                'berawan'      => '⛅ Berawan',
                                                'hujan_ringan' => '🌦️ Hujan Ringan',
                                                'hujan_lebat'  => '🌧️ Hujan Lebat',
                                                'badai'        => '⛈️ Badai',
                                                default        => 'Tidak tercatat',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $wcClass }}">
                                            {{ $wcIcon }}
                                        </span>
                                    </div>
                                    <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                        @if($item->wave_height_meters !== null)
                                            <span class="font-medium text-slate-700">🌊 {{ $item->wave_height_meters }} m</span>
                                        @endif
                                        @if($item->sea_condition)
                                            <span class="truncate max-w-[130px]" title="{{ $item->sea_condition }}">({{ $item->sea_condition }})</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Uraian Kegiatan --}}
                                <td class="px-4 py-3">
                                    <p class="text-xs text-slate-700 line-clamp-2 max-w-sm" title="{{ $item->activity_description }}">
                                        {{ $item->activity_description ?? '-' }}
                                    </p>
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
                                                    'log_date' => $item->log_date ? $item->log_date->format('d F Y') : '-',
                                                    'log_time' => $item->log_time ? \Illuminate\Support\Str::substr($item->log_time, 0, 5) . ' WIB' : '-',
                                                    'latitude' => $item->latitude,
                                                    'longitude' => $item->longitude,
                                                    'weather_condition' => $item->weather_condition,
                                                    'weather_label' => $wcIcon,
                                                    'wave_height_meters' => $item->wave_height_meters,
                                                    'sea_condition' => $item->sea_condition ?? '-',
                                                    'activity_description' => $item->activity_description ?? '-',
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
                                                    'log_date' => $item->log_date ? $item->log_date->format('Y-m-d') : '',
                                                    'log_time' => $item->log_time ? \Illuminate\Support\Str::substr($item->log_time, 0, 5) : '',
                                                    'latitude' => $item->latitude,
                                                    'longitude' => $item->longitude,
                                                    'weather_condition' => $item->weather_condition,
                                                    'wave_height_meters' => $item->wave_height_meters,
                                                    'sea_condition' => $item->sea_condition,
                                                    'activity_description' => $item->activity_description,
                                                ]) }}, '{{ route('logbooks.update', $item) }}')"
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Ubah Catatan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('logbooks.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus catatan logbook tanggal {{ $item->log_date ? $item->log_date->format('d/m/Y') : '' }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Hapus Catatan') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-4xl mb-3">📋</div>
                                    <p class="text-base font-semibold text-slate-700">{{ __('Belum ada data logbook kapal') }}</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        {{ __('Gunakan tombol Catat Logbook Baru di atas untuk mulai mencatat kronologi operasi penangkapan ikan di laut.') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$logbooks" />
        </div>

        {{-- ======================================= --}}
        {{-- MODAL TAMBAH LOGBOOK                    --}}
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
                        <span class="text-lg">📋</span>
                        <h3 class="font-bold text-base">{{ __('Catat Logbook Harian Kapal') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('logbooks.store') }}" class="p-6 space-y-4">
                    @csrf

                    {{-- Pilih Trip Penangkapan --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Trip Penangkapan Ikan') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="fishing_trip_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="">-- {{ __('Pilih Trip Penangkapan') }} --</option>
                            @foreach($trips as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }} ({{ $t->departure_date ? $t->departure_date->format('d/m/Y') : '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal & Jam --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Tanggal Pencatatan') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="log_date" value="{{ date('Y-m-d') }}" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu / Jam (WIB)') }}
                            </label>
                            <input type="time" name="log_time" value="{{ date('H:i') }}" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Posisi Lintang (Latitude)') }}
                            </label>
                            <input type="number" step="0.0000001" name="latitude" placeholder="Contoh: 5.8942000" class="w-full text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-mono">
                            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Perairan Aceh: 1.5° s/d 6.5° N') }}</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Posisi Bujur (Longitude)') }}
                            </label>
                            <input type="number" step="0.0000001" name="longitude" placeholder="Contoh: 94.6850000" class="w-full text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-mono">
                            <span class="text-[11px] text-slate-500 mt-0.5 block">{{ __('Perairan Aceh: 94.0° s/d 98.5° E') }}</span>
                        </div>
                    </div>

                    {{-- Cuaca & Ombak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Kondisi Cuaca') }}
                            </label>
                            <select name="weather_condition" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Cuaca') }} --</option>
                                @foreach($weatherOptions as $val => $lbl)
                                    <option value="{{ $val }}" {{ $val == 'cerah' ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Tinggi Gelombang (meter)') }}
                            </label>
                            <input type="number" step="0.01" name="wave_height_meters" placeholder="Contoh: 1.25" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Kondisi Arus / Ombak') }}
                            </label>
                            <input type="text" name="sea_condition" placeholder="Tenang / Berombak sedang..." class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Uraian Kegiatan Operasional --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Uraian Kegiatan / Kronologi di Laut') }}
                        </label>
                        <textarea name="activity_description" rows="3" placeholder="Contoh: Penurunan alat tangkap (setting 1) di sekitar rumpon laut dalam nomor 04..." class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Catatan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL UBAH (EDIT) LOGBOOK               --}}
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
                        <h3 class="font-bold text-base">{{ __('Ubah Catatan Logbook Kapal') }}</h3>
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
                        <select name="fishing_trip_id" x-model="editItem.fishing_trip_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            @foreach($trips as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Tanggal & Jam --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Tanggal Pencatatan') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" name="log_date" x-model="editItem.log_date" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu / Jam (WIB)') }}
                            </label>
                            <input type="time" name="log_time" x-model="editItem.log_time" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Koordinat GPS --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Posisi Lintang (Latitude)') }}
                            </label>
                            <input type="number" step="0.0000001" name="latitude" x-model="editItem.latitude" placeholder="5.8942000" class="w-full text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-mono">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Posisi Bujur (Longitude)') }}
                            </label>
                            <input type="number" step="0.0000001" name="longitude" x-model="editItem.longitude" placeholder="94.6850000" class="w-full text-sm border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 font-mono">
                        </div>
                    </div>

                    {{-- Cuaca & Ombak --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Kondisi Cuaca') }}
                            </label>
                            <select name="weather_condition" x-model="editItem.weather_condition" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Cuaca') }} --</option>
                                @foreach($weatherOptions as $val => $lbl)
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Tinggi Gelombang (meter)') }}
                            </label>
                            <input type="number" step="0.01" name="wave_height_meters" x-model="editItem.wave_height_meters" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Kondisi Arus / Ombak') }}
                            </label>
                            <input type="text" name="sea_condition" x-model="editItem.sea_condition" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Uraian Kegiatan Operasional --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Uraian Kegiatan / Kronologi di Laut') }}
                        </label>
                        <textarea name="activity_description" x-model="editItem.activity_description" rows="3" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
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
        {{-- MODAL DETAIL RINCIAN LOGBOOK            --}}
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
                        <span class="text-lg">📖</span>
                        <h3 class="font-bold text-base">{{ __('Rincian Catatan Jurnal Harian Kapal') }}</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Content --}}
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <span class="text-xs text-slate-500 block uppercase font-medium">{{ __('Nomor Trip Operasional') }}</span>
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
                            <span class="text-slate-500 block font-medium">{{ __('Tanggal & Waktu') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="detailItem.log_date + ' (' + detailItem.log_time + ')'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Kondisi Cuaca') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="detailItem.weather_label"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Tinggi Gelombang') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="(detailItem.wave_height_meters || '-') + ' meter'"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Kondisi Arus / Ombak') }}</span>
                            <span class="font-semibold text-slate-800 text-sm mt-0.5 block" x-text="detailItem.sea_condition"></span>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <span class="text-slate-500 block font-medium mb-1">{{ __('Koordinat GPS (Posisi Kapal)') }}</span>
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-sm font-semibold text-slate-800" x-text="(detailItem.latitude || '-') + '°, ' + (detailItem.longitude || '-') + '°'"></span>
                            <template x-if="detailItem.latitude && detailItem.longitude">
                                <a :href="'https://www.openstreetmap.org/?mlat=' + detailItem.latitude + '&mlon=' + detailItem.longitude + '#map=12/' + detailItem.latitude + '/' + detailItem.longitude"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="inline-flex items-center gap-1 text-xs text-ocean-600 hover:text-ocean-800 font-semibold underline">
                                    <span>🌐</span>
                                    <span>{{ __('Lihat di Peta') }}</span>
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">{{ __('Uraian Kegiatan Melaut:') }}</span>
                        <p class="text-sm text-slate-700 leading-relaxed bg-slate-50 p-3.5 rounded-xl border border-slate-200" x-text="detailItem.activity_description"></p>
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
