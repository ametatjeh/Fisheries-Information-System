<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>📄</span>
            <span>{{ __('Laporan & Rekapitulasi Data Perikanan Tangkap') }}</span>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Banner Header --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">📄</div>
            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex-1 max-w-4xl">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                            <span>📊</span>
                            <span>{{ __('Modul Output & Ekspor Data Terpadu') }}</span>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                            <span>📄</span>
                            <span>{{ __('Pusat Laporan & Ekspor Data Perikanan') }}</span>
                        </h2>
                        <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                            {{ __('Akses, filter, cetak dokumen resmi, dan ekspor data komprehensif penangkapan ikan, upaya tangkap, pendaratan TPI, sampel biologi, serta rekapitulasi statistik bulanan.') }}
                        </p>
                    </div>

                    {{-- Action Buttons: Print & Export CSV --}}
                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        <a href="{{ route('reports.print', request()->query()) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-sm text-sm font-semibold text-white transition shadow-sm hover:shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            <span>{{ __('Cetak / PDF') }}</span>
                        </a>

                        <a href="{{ route('reports.export', request()->query()) }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-sm font-semibold transition shadow-md hover:shadow-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>{{ __('Ekspor CSV') }}</span>
                        </a>
                    </div>
                </div>

                {{-- Quick Tab Switcher --}}
                <div class="mt-6 pt-5 border-t border-white/10 flex flex-wrap items-center gap-2 text-sm font-medium">
                    <span class="text-xs text-ocean-300 mr-2 uppercase tracking-wider font-semibold">{{ __('Pilih Laporan:') }}</span>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'catches'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'catches' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        🐟 {{ __('Hasil Tangkapan') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'efforts'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'efforts' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        ⚓ {{ __('Upaya & Trip Tangkap') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'landings'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'landings' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        📦 {{ __('Pendaratan & Omzet TPI') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'sampling'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'sampling' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        🔬 {{ __('Biologi & Sampling') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'monthly'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'monthly' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        📊 {{ __('Rekapitulasi Bulanan') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'production'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'production' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        📈 {{ __('Produksi Terpadu') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'statistics'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'statistics' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        📉 {{ __('Statistik & CPUE') }}
                    </a>
                    <a href="{{ route('reports.index', array_merge(request()->except(['page']), ['type' => 'summary'])) }}"
                       class="px-3.5 py-1.5 rounded-lg transition {{ $type === 'summary' ? 'bg-ocean-500 text-white font-semibold shadow-inner' : 'bg-white/10 text-ocean-100 hover:bg-white/20' }}">
                        📑 {{ __('Ringkasan Eksekutif') }}
                    </a>
                </div>
            </div>
        </div>

        {{-- Dynamic Filter Bar --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <form method="GET" action="{{ route('reports.index') }}" class="space-y-4">
                <input type="hidden" name="type" value="{{ $type }}">

                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                        <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>{{ __('Filter Parameter Laporan') }}</span>
                    </div>

                    @if(array_filter($filters))
                        <a href="{{ route('reports.index', ['type' => $type]) }}"
                           class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline">
                            {{ __('Reset Filter') }}
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @if($type === 'monthly')
                        {{-- Tahun --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Tahun') }}</label>
                            <select name="year" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                @for($y = 2026; $y >= 2020; $y--)
                                    <option value="{{ $y }}" {{ ($filters['year'] ?? 2026) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        {{-- Bulan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Bulan') }}</label>
                            <select name="month" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Semua Bulan (1 Tahun Penuh)') }}</option>
                                @php
                                    $bulanList = [
                                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                    ];
                                @endphp
                                @foreach($bulanList as $mNum => $mName)
                                    <option value="{{ $mNum }}" {{ ($filters['month'] ?? '') == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        {{-- Rentang Tanggal Mulai --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Dari Tanggal') }}</label>
                            <input type="date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>

                        {{-- Rentang Tanggal Akhir --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Sampai Tanggal') }}</label>
                            <input type="date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                                   class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                    @endif

                    {{-- Pelabuhan / Landing Site --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Pelabuhan / TPI') }}</label>
                        <select name="landing_site_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Semua Pelabuhan / TPI') }}</option>
                            @foreach($landingSites as $site)
                                <option value="{{ $site->id }}" {{ ($filters['landing_site_id'] ?? '') == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }} ({{ $site->code ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(in_array($type, ['catches', 'efforts', 'landings', 'monthly']))
                        {{-- Wilayah Pengelolaan Perikanan (WPP) --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Wilayah WPP-NRI') }}</label>
                            <select name="wppnri_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Semua Wilayah WPP') }}</option>
                                @foreach($wppList as $wpp)
                                    <option value="{{ $wpp->id }}" {{ ($filters['wppnri_id'] ?? '') == $wpp->id ? 'selected' : '' }}>
                                        {{ $wpp->name }} ({{ $wpp->code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(in_array($type, ['catches', 'efforts', 'monthly']))
                        {{-- Alat Tangkap --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Alat Tangkap') }}</label>
                            <select name="gear_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Semua Alat Tangkap') }}</option>
                                @foreach($gearsList as $gear)
                                    <option value="{{ $gear->id }}" {{ ($filters['gear_id'] ?? '') == $gear->id ? 'selected' : '' }}>
                                        {{ $gear->name }} ({{ $gear->gear_type ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(in_array($type, ['catches', 'sampling', 'monthly']))
                        {{-- Jenis Ikan / Komoditas --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Jenis Ikan / Spesies') }}</label>
                            <select name="species_id" class="w-full text-sm rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Semua Jenis Ikan') }}</option>
                                @foreach($speciesList as $sp)
                                    <option value="{{ $sp->id }}" {{ ($filters['species_id'] ?? '') == $sp->id ? 'selected' : '' }}>
                                        {{ $sp->local_name_id ?? $sp->english_name ?? $sp->scientific_name }} ({{ $sp->scientific_name }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold transition shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>{{ __('Terapkan Filter') }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- KPI Summary Cards based on Report Type --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @if($type === 'efforts')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Setting Alat') }}</span>
                        <span class="text-xl">⚙️</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format($totalSettings) }} <span class="text-xs font-normal text-slate-500">kali</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Siklus penurunan jaring/pancing') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Jam Operasi') }}</span>
                        <span class="text-xl">⏱️</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalDurationHours, 1) }} <span class="text-xs font-normal text-slate-500">Jam</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Durasi di fishing ground') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Rata-rata Durasi') }}</span>
                        <span class="text-xl">📊</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ $avgDurationHours }} <span class="text-xs font-normal text-slate-500">Jam/setting</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Efisiensi penangkapan') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Data Terarsip') }}</span>
                        <span class="text-xl">📋</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format($totalRecords) }} <span class="text-xs font-normal text-slate-500">baris</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Log operasi penangkapan') }}</div>
                </div>

            @elseif($type === 'landings')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Volume Didaratkan') }}</span>
                        <span class="text-xl">📦</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalWeightTon, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalWeightKg, 0, ',', '.') }} kg komoditas</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Nilai Omzet (Rp)') }}</span>
                        <span class="text-xl">💰</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        Rp {{ number_format($totalValueRp, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Omzet transaksi lelang TPI') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Harga Rata-Rata / Kg') }}</span>
                        <span class="text-xl">🏷️</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        Rp {{ number_format($avgPricePerKg, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Indeks nilai ekonomi ikan') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Frekuensi Pendaratan') }}</span>
                        <span class="text-xl">⚓</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format($totalRecords) }} <span class="text-xs font-normal text-slate-500">pendaratan</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Manifest TPI terdata') }}</div>
                </div>

            @elseif($type === 'sampling')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Spesimen Diukur') }}</span>
                        <span class="text-xl">🔬</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalSpecimens) }} <span class="text-xs font-normal text-slate-500">ekor</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Spesimen biologi morfometrik') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Rata-rata Panjang Total') }}</span>
                        <span class="text-xl">📏</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ $avgTotalLengthCm }} <span class="text-xs font-normal text-slate-500">cm</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">Rentang: {{ $minTotalLengthCm }} - {{ $maxTotalLengthCm }} cm</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Rata-rata Bobot') }}</span>
                        <span class="text-xl">⚖️</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        {{ $avgWeightGram }} <span class="text-xs font-normal text-slate-500">gram</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Berat per individu ikan') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Status Data Biologi') }}</span>
                        <span class="text-xl">🧬</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-indigo-700 mt-2">
                        {{ number_format($totalRecords) }} <span class="text-xs font-normal text-slate-500">sample</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('TKG & morfometrik lengkap') }}</div>
                </div>

            @elseif($type === 'monthly')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Volume Produksi') }}</span>
                        <span class="text-xl">📊</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalVolumeTon, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalVolumeKg, 0, ',', '.') }} kg</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Nilai Produksi') }}</span>
                        <span class="text-xl">💵</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        Rp {{ number_format($totalValueRp, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Agregat statistik dinas') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Trip & CPUE') }}</span>
                        <span class="text-xl">📈</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        {{ number_format($totalTrips) }} <span class="text-xs font-normal text-slate-500">Trip</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">CPUE Rata-rata: {{ $avgCpue }} kg/trip</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Agregat Rekap') }}</span>
                        <span class="text-xl">📑</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format($totalRecords) }} <span class="text-xs font-normal text-slate-500">entri</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Statistik bulanan terverifikasi') }}</div>
                </div>

            @elseif($type === 'production')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Volume Pendaratan') }}</span>
                        <span class="text-xl">📦</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format(($totalLandedKg ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalLandedKg ?? 0, 0, ',', '.') }} kg pendaratan riil</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Tangkapan Fisik') }}</span>
                        <span class="text-xl">🐟</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ number_format(($totalObservedKg ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalObservedKg ?? 0, 0, ',', '.') }} kg observed catch</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Estimasi Produksi') }}</span>
                        <span class="text-xl">📈</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        {{ number_format(($totalEstimatedKg ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalEstimatedKg ?? 0, 0, ',', '.') }} kg raising factor</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Produksi') }}</span>
                        <span class="text-xl">📊</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format(($totalProductionKg ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Agregat terpadu seluruh pelabuhan') }}</div>
                </div>

            @elseif($type === 'statistics')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Upaya (Effort)') }}</span>
                        <span class="text-xl">⚓</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalTrips ?? 0) }} <span class="text-xs font-normal text-slate-500">Trip</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalEffortHours ?? 0, 1) }} jam operasi laut</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Tangkapan') }}</span>
                        <span class="text-xl">🐟</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ number_format(($totalCatchKg ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalCatchKg ?? 0, 0, ',', '.') }} kg teramati</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('CPUE Rata-rata') }}</span>
                        <span class="text-xl">📈</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        {{ number_format($cpuePerHour ?? 0, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">kg/jam</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Produktivitas upaya tangkap') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Kepatuhan Validasi') }}</span>
                        <span class="text-xl">✅</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-indigo-700 mt-2">
                        {{ number_format($validationCounts['validated'] ?? 0) }} / {{ number_format($validationCounts['total'] ?? 0) }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">Trip tervalidasi resmi</div>
                </div>

            @elseif($type === 'summary')
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Pelayaran') }}</span>
                        <span class="text-xl">⚓</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($summaryKpis['total_trips'] ?? 0) }} <span class="text-xs font-normal text-slate-500">Trip</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($summaryKpis['active_vessels'] ?? 0) }} kapal aktif</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Hasil Tangkapan') }}</span>
                        <span class="text-xl">🐟</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ number_format(($summaryKpis['total_catch_kg'] ?? 0) / 1000, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($summaryKpis['active_species'] ?? 0) }} spesies terdata</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Pendaratan & Omzet') }}</span>
                        <span class="text-xl">💰</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        Rp {{ number_format($summaryKpis['total_landing_value'] ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format(($summaryKpis['total_landing_kg'] ?? 0) / 1000, 2, ',', '.') }} Ton pendaratan</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('CPUE & Pangkalan') }}</span>
                        <span class="text-xl">📊</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ number_format($summaryKpis['cpue_kg_hour'] ?? 0, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">kg/jam</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($summaryKpis['active_landing_sites'] ?? 0) }} pangkalan / TPI</div>
                </div>

            @else {{-- catches --}}
                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Volume Tangkapan') }}</span>
                        <span class="text-xl">🐟</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-ocean-700 mt-2">
                        {{ number_format($totalWeightTon, 2, ',', '.') }} <span class="text-xs font-normal text-slate-500">Ton</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ number_format($totalWeightKg, 0, ',', '.') }} kg ikan tangkap</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Jumlah Ekor') }}</span>
                        <span class="text-xl">🔢</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-emerald-700 mt-2">
                        {{ number_format($totalFishCount) }} <span class="text-xs font-normal text-slate-500">ekor</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Spesimen komoditas tangkapan') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Total Catatan Tangkapan') }}</span>
                        <span class="text-xl">📝</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-cyan-700 mt-2">
                        {{ number_format($totalRecords) }} <span class="text-xs font-normal text-slate-500">entri</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Rincian hasil tangkapan logbook') }}</div>
                </div>

                <div class="p-5 rounded-2xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="flex items-center justify-between text-xs font-semibold text-slate-500 uppercase">
                        <span>{{ __('Rata-rata per Catatan') }}</span>
                        <span class="text-xl">⚖️</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-800 mt-2">
                        {{ $totalRecords > 0 ? round($totalWeightKg / $totalRecords, 1) : 0 }} <span class="text-xs font-normal text-slate-500">kg/catatan</span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('Produktivitas per komoditas') }}</div>
                </div>
            @endif
        </div>

        {{-- Table Card --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>📋</span>
                        <span>
                            @if($type === 'efforts')
                                {{ __('Rincian Upaya Penangkapan & Trip Operasi Laut') }}
                            @elseif($type === 'landings')
                                {{ __('Rincian Manifest Pendaratan & Transaksi Lelang TPI') }}
                            @elseif($type === 'sampling')
                                {{ __('Rincian Pengukuran Biologi & Morfometrik Ikan') }}
                            @elseif($type === 'monthly')
                                {{ __('Rincian Agregat Statistik Produksi Bulanan') }}
                            @elseif($type === 'production')
                                {{ __('Rincian Laporan Produksi Terpadu Multi-Dimensi') }}
                            @elseif($type === 'statistics')
                                {{ __('Rincian Laporan Statistik Perikanan Tangkap & CPUE') }}
                            @elseif($type === 'summary')
                                {{ __('Rincian Indikator Kinerja Utama (Summary Report)') }}
                            @else
                                {{ __('Rincian Data Hasil Tangkapan (Catches)') }}
                            @endif
                        </span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">{{ __('Menampilkan data terarsip sesuai filter aktif') }}</p>
                </div>

                <div class="text-xs font-mono font-medium text-slate-600 px-3 py-1.5 rounded-lg bg-slate-100">
                    Total: {{ number_format($totalRecords) }} baris data
                </div>
            </div>

            <div class="overflow-x-auto">
                @if($type === 'efforts')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Kode Trip') }}</th>
                                <th class="py-3.5 px-4">{{ __('Kapal & Nahkoda') }}</th>
                                <th class="py-3.5 px-4">{{ __('Pelabuhan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Alat Tangkap') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Setting Ke-') }}</th>
                                <th class="py-3.5 px-4">{{ __('Waktu Setting') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Durasi (Jam)') }}</th>
                                <th class="py-3.5 px-4">{{ __('Titik Koordinat') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700">
                                        {{ $row->fishingTrip?->trip_number ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->fishingTrip?->vessel?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $row->fishingTrip?->captain?->name ?? 'Nahkoda -' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs">{{ $row->fishingTrip?->landingSite?->name ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-50 text-cyan-800 border border-cyan-200">
                                            {{ $row->fishingGear?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-semibold">{{ $row->setting_number ?? 1 }}</td>
                                    <td class="py-3 px-4 text-xs font-mono">
                                        {{ $row->setting_date ? $row->setting_date->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                                        {{ number_format($row->duration_hours, 1) }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono text-slate-500">
                                        @if($row->latitude_setting)
                                            {{ $row->latitude_setting }}, {{ $row->longitude_setting }}
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">⚓</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data upaya penangkapan yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'landings')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('No. Pendaratan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Tgl Pendaratan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Pelabuhan / TPI') }}</th>
                                <th class="py-3.5 px-4">{{ __('Kapal Asal') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Volume (kg)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Nilai Omzet (Rp)') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Pembeli') }}</th>
                                <th class="py-3.5 px-4">{{ __('Pencatat') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700">
                                        {{ $row->landing_number }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono">
                                        {{ $row->landing_date ? $row->landing_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ $row->landingSite?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-xs">
                                        <div class="font-medium text-slate-900">{{ $row->fishingTrip?->vessel?->name ?? '-' }}</div>
                                        <div class="text-slate-400 font-mono">{{ $row->fishingTrip?->trip_number ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($row->total_weight_kg, 1, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                        Rp {{ number_format($row->total_value_rp, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-xs">{{ $row->buyer_count ?? 0 }}</td>
                                    <td class="py-3 px-4 text-xs text-slate-500">{{ $row->recordedBy?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">📦</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data pendaratan yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'sampling')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Kode Sampel') }}</th>
                                <th class="py-3.5 px-4">{{ __('Tgl Sampling') }}</th>
                                <th class="py-3.5 px-4">{{ __('Lokasi TPI') }}</th>
                                <th class="py-3.5 px-4">{{ __('Jenis Ikan (Spesies)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Panjang (cm)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Bobot (gram)') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Kelamin') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('TKG') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700">
                                        {{ $row->sample?->sample_code ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono">
                                        {{ $row->sample?->sample_date ? $row->sample->sample_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs">{{ $row->sample?->landingSite?->name ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->fishSpecies?->local_name_id ?? $row->fishSpecies?->english_name ?? $row->fishSpecies?->scientific_name ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400">{{ $row->fishSpecies?->scientific_name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($row->total_length_cm, 1) }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">
                                        {{ number_format($row->weight_gram, 1) }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs">
                                        @if($row->sex === 'male' || $row->sex === 'Jantan')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">♂ Jantan</span>
                                        @elseif($row->sex === 'female' || $row->sex === 'Betina')
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700">♀ Betina</span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-xs font-semibold text-slate-700">
                                        {{ $row->gonad_maturity_stage ? 'TKG ' . $row->gonad_maturity_stage : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">🔬</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data sampling biologi yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'monthly')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Periode') }}</th>
                                <th class="py-3.5 px-4">{{ __('Pelabuhan / Pangkalan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Wilayah WPP') }}</th>
                                <th class="py-3.5 px-4">{{ __('Alat Tangkap') }}</th>
                                <th class="py-3.5 px-4">{{ __('Jenis Ikan') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Volume (kg)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Nilai Omzet (Rp)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Harga/Kg') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                @php
                                    $vol = (float) ($row->volume_kg ?? $row->total_volume_kg ?? 0);
                                    $val = (float) ($row->value_rp ?? $row->total_value_rp ?? 0);
                                    $avg = $vol > 0 ? round($val / $vol, 2) : 0;
                                    $wppName = $row->wpp_name ?? '-';
                                    $statusName = $row->status ?? 'validated';
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700 text-xs">
                                        {{ $row->year }} - Bln {{ str_pad($row->month, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->landing_site_name ?? $row->landingSite?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $row->regency_name ?? $row->regency?->name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono">
                                        @if($wppName === 'Tidak Terpetakan')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700 border border-amber-200">{{ __('Tidak Terpetakan') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-sky-50 text-sky-700 border border-sky-200">{{ $wppName }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-xs font-medium text-slate-700">
                                        {{ $row->gear_name ?? $row->fishingGear?->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->species_name ?? $row->fishSpecies?->local_name_id ?? $row->fishSpecies?->indonesian_name ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400">{{ $row->scientific_name ?? $row->fishSpecies?->scientific_name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($vol, 1, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                        Rp {{ number_format($val, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-xs text-slate-600">
                                        Rp {{ number_format($avg, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs">
                                        @if($statusName === 'validated' || $statusName === 'completed')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">✓ {{ __('Tervalidasi') }}</span>
                                        @elseif($statusName === 'draft')
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">{{ __('Draft') }}</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-rose-50 text-rose-700 border border-rose-200">{{ ucfirst($statusName) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">📊</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data rekapitulasi produksi bulanan yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'production')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Periode') }}</th>
                                <th class="py-3.5 px-4">{{ __('Kabupaten / Pelabuhan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Alat Tangkap') }}</th>
                                <th class="py-3.5 px-4">{{ __('Jenis Ikan') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Volume (kg)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Nilai (Rp)') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700">
                                        {{ $row->year }}-{{ str_pad($row->month, 2, '0', STR_PAD_LEFT) }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->landing_site_name ?? $row->landingSite?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $row->regency_name ?? $row->regency?->name ?? '-' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs">{{ $row->gear_name ?? $row->fishingGear?->name ?? '-' }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->species_name ?? $row->fishSpecies?->local_name_id ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400">{{ $row->scientific_name ?? $row->fishSpecies?->scientific_name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($row->volume_kg ?? $row->total_volume_kg ?? 0, 1, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono text-xs text-emerald-700 font-semibold">
                                        Rp {{ number_format($row->value_rp ?? $row->total_value_rp ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center text-xs">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">✓ {{ __('Tervalidasi') }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">📈</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data laporan produksi terpadu yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'statistics')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Alat Tangkap') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Total Tangkapan (kg)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Upaya Tangkap (Jam)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('CPUE (kg/jam)') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Jumlah Trip') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $row->gear_name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-ocean-700">{{ number_format($row->total_catch_kg ?? 0, 1, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-800">{{ number_format($row->total_effort_hours ?? 0, 1) }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">{{ number_format($row->cpue ?? 0, 2, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center font-mono text-slate-800">{{ number_format($row->trips_count ?? 0) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">📉</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data statistik perikanan yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @elseif($type === 'summary')
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Indikator Kinerja Utama (KPI)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Nilai Agregat') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Satuan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">{{ $index + 1 }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-900">{{ $row->indicator }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-ocean-700 text-base">{{ $row->value }}</td>
                                    <td class="py-3 px-4 text-center text-xs text-slate-500 font-medium">{{ $row->unit }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">📑</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada ringkasan KPI yang sesuai filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                @else {{-- catches --}}
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs font-bold text-slate-600 uppercase bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 text-center w-12">#</th>
                                <th class="py-3.5 px-4">{{ __('Kode Trip') }}</th>
                                <th class="py-3.5 px-4">{{ __('Tgl Berangkat') }}</th>
                                <th class="py-3.5 px-4">{{ __('Kapal & Nahkoda') }}</th>
                                <th class="py-3.5 px-4">{{ __('Pelabuhan') }}</th>
                                <th class="py-3.5 px-4">{{ __('Alat Tangkap') }}</th>
                                <th class="py-3.5 px-4">{{ __('Jenis Ikan (Spesies)') }}</th>
                                <th class="py-3.5 px-4 text-right">{{ __('Berat (kg)') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Ekor') }}</th>
                                <th class="py-3.5 px-4 text-center">{{ __('Kondisi') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse($records as $index => $row)
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 text-center text-xs text-slate-400 font-mono">
                                        {{ ($records instanceof \Illuminate\Pagination\LengthAwarePaginator) ? $records->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-mono font-semibold text-ocean-700">
                                        {{ $row->fishingTrip?->trip_number ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-xs font-mono">
                                        {{ $row->fishingTrip?->departure_date ? $row->fishingTrip->departure_date->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->fishingTrip?->vessel?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-400">{{ $row->fishingTrip?->captain?->name ?? 'Nahkoda -' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-xs">{{ $row->fishingTrip?->landingSite?->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cyan-50 text-cyan-800 border border-cyan-200">
                                            {{ $row->fishingTrip?->primaryGear?->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-slate-900">{{ $row->species?->local_name_id ?? $row->species?->english_name ?? $row->species?->scientific_name ?? '-' }}</div>
                                        <div class="text-xs italic text-slate-400">{{ $row->species?->scientific_name ?? '' }}</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                        {{ number_format($row->weight_kg, 1, ',', '.') }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono text-xs">{{ $row->fish_count ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $row->catch_status === 'segar' || $row->catch_status === 'Fresh' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700' }}">
                                            {{ $row->catch_status ?? 'Segar' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-12 text-center text-slate-400">
                                        <div class="text-3xl mb-2">🐟</div>
                                        <div class="text-sm font-medium">{{ __('Tidak ada data tangkapan yang sesuai dengan kriteria filter') }}</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            {{-- Pagination Links --}}
            @if($records instanceof \Illuminate\Pagination\LengthAwarePaginator && $records->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
