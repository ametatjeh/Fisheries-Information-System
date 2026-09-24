<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="flex items-center gap-2">
                <span class="text-xl">📊</span>
                <span class="font-bold text-white">{{ __('Analisis Lanjutan: Statistik & Dashboard Perikanan') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-ocean-100 text-ocean-800 font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-ocean-600 animate-pulse"></span>
                    <span>Periode: {{ $filters['period_label'] }}</span>
                </span>
                <a href="{{ route('reports.index') }}" class="px-3 py-1 bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 rounded-lg transition font-medium">
                    📋 {{ __('Buka Laporan') }}
                </a>
            </div>
        </div>
    </x-slot>

<div class="space-y-6">
    {{-- Banner Header --}}
    <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-6 rounded-2xl shadow-sm relative overflow-hidden">
        <div class="absolute right-4 -bottom-6 text-9xl opacity-10 pointer-events-none select-none">📊</div>
        <div class="relative z-10">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>📈</span>
                        <span>{{ __('Lapisan Analitik Terpadu (Data Lineage Terverifikasi)') }}</span>
                    </div>
                    <h2 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                        <span>📊</span>
                        <span>{{ __('Statistik Multi-Dimensi: Catch, Effort, CPUE, & Produksi') }}</span>
                    </h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Menghubungkan data operasional pelayaran: Trip & Upaya (Effort) → Tangkapan Laut (Catch) → Pendaratan (Landing) → Sampling Biologi → Estimasi Produksi → Statistik Bulanan.') }}
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="px-4 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm text-right">
                        <div class="text-[10px] text-ocean-200 uppercase tracking-wider font-semibold">{{ __('Periode Aktif') }}</div>
                        <div class="text-lg font-mono font-bold text-white">{{ $filters['period_label'] }}</div>
                    </div>
                </div>
            </div>

            {{-- 6 HERO KPI CARDS: Total Catch, Total Effort, CPUE, Production, Production Value, Estimated Production --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10">
                {{-- 1. Total Catch (Observed) --}}
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-ocean-200 font-semibold uppercase tracking-wider">1. {{ __('Total Catch') }}</span>
                        <span class="text-lg">🐟</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2 font-mono">
                        {{ number_format($totalCatchTon, 2, ',', '.') }} <span class="text-sm font-normal text-ocean-200">Ton</span>
                    </div>
                    <div class="text-xs text-cyan-300 mt-1 font-mono">
                        {{ number_format($totalCatchKg, 0, ',', '.') }} kg {{ __('fisik total') }}
                    </div>
                    <div class="text-[11px] text-slate-300/80 mt-1" title="Tangkapan teramati saat trip pelayaran">
                        {{ __('Observed Catch di laut') }}
                    </div>
                </div>

                {{-- 2. Total Effort --}}
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-ocean-200 font-semibold uppercase tracking-wider">2. {{ __('Total Effort') }}</span>
                        <span class="text-lg">⚓</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2 font-mono">
                        {{ number_format($totalTrips) }} <span class="text-sm font-normal text-ocean-200">Trip</span>
                    </div>
                    <div class="text-xs text-emerald-300 mt-1 font-mono">
                        {{ number_format($totalSettings) }} {{ __('setting operasi') }}
                    </div>
                    <div class="text-[11px] text-slate-300/80 mt-1">
                        {{ number_format($totalDurationHours, 1) }} {{ __('jam operasi laut') }}
                    </div>
                </div>

                {{-- 3. CPUE (Catch Per Unit Effort) --}}
                <div class="p-4 rounded-xl bg-gradient-to-br from-cyan-500/20 to-ocean-500/10 border border-cyan-400/30 backdrop-blur-sm shadow-inner">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-cyan-200 font-bold uppercase tracking-wider">3. {{ __('CPUE (Laju Tangkap)') }}</span>
                        <span class="text-lg">📈</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2 font-mono">
                        {{ number_format($cpuePerHour, 2, ',', '.') }} <span class="text-xs font-normal text-ocean-200">kg/jam</span>
                    </div>
                    <div class="text-xs text-cyan-300 mt-1 font-mono">
                        {{ number_format($cpuePerTrip, 1, ',', '.') }} <span class="text-[11px] text-ocean-200">kg/trip</span>
                    </div>
                    <div class="text-[11px] text-ocean-100/90 mt-1">
                        {{ __('Rasio Tangkapan / Upaya') }}
                    </div>
                </div>

                {{-- 4. Production (Landing Manifest) --}}
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-ocean-200 font-semibold uppercase tracking-wider">4. {{ __('Production') }}</span>
                        <span class="text-lg">📦</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2 font-mono">
                        {{ number_format($totalProductionTon, 2, ',', '.') }} <span class="text-sm font-normal text-ocean-200">Ton</span>
                    </div>
                    <div class="text-xs text-sky-300 mt-1 font-mono">
                        {{ number_format($totalProductionKg, 0, ',', '.') }} kg {{ __('pendaratan') }}
                    </div>
                    <div class="text-[11px] text-slate-300/80 mt-1">
                        {{ __('Manifest timbang TPI') }}
                    </div>
                </div>

                {{-- 5. Production Value (Omzet Lelang) --}}
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-ocean-200 font-semibold uppercase tracking-wider">5. {{ __('Nilai Omzet') }}</span>
                        <span class="text-lg">💰</span>
                    </div>
                    <div class="text-xl font-bold text-emerald-300 mt-2 font-mono truncate">
                        Rp {{ number_format($totalProductionValueRp / 1000000, 1, ',', '.') }} Jt
                    </div>
                    <div class="text-xs text-emerald-400 mt-1 font-mono">
                        Rp {{ number_format($avgPricePerKg, 0, ',', '.') }} <span class="text-[11px] text-ocean-200">/kg rata2</span>
                    </div>
                    <div class="text-[11px] text-slate-300/80 mt-1">
                        {{ __('Transaksi pendaratan TPI') }}
                    </div>
                </div>

                {{-- 6. Estimated Production --}}
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm hover:bg-white/10 transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-ocean-200 font-semibold uppercase tracking-wider">6. {{ __('Estimasi Produksi') }}</span>
                        <span class="text-lg">🎯</span>
                    </div>
                    <div class="text-2xl font-bold text-white mt-2 font-mono">
                        {{ number_format($totalEstimatedTon, 2, ',', '.') }} <span class="text-sm font-normal text-ocean-200">Ton</span>
                    </div>
                    <div class="text-xs text-amber-300 mt-1 font-mono">
                        {{ number_format($totalEstimatedKg, 0, ',', '.') }} kg {{ __('estimasi') }}
                    </div>
                    <div class="text-[11px] text-slate-300/80 mt-1">
                        {{ __('Raising factor Stage 10') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter Engine Bar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
        <form method="GET" action="{{ route('analysis.statistics.index') }}" class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🔍</span>
                    <span>{{ __('Filter Engine Analitik Statistik') }}</span>
                </div>
                <div class="text-xs text-slate-400">
                    {{ __('Filter terpadu multi-kriteria tanpa double-counting') }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 text-xs">
                {{-- Tahun --}}
                <div>
                    <label for="stat_filter_year" class="sr-only">{{ __('Pilih Tahun') }}</label>
                    <select id="stat_filter_year" name="year" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih Tahun') }}</option>
                        @foreach($filterOptions['years'] as $yr)
                            <option value="{{ $yr }}" {{ ($filters['year'] == $yr) ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Bulan --}}
                <div>
                    <label for="stat_filter_month" class="sr-only">{{ __('Pilih Bulan') }}</label>
                    <select id="stat_filter_month" name="month" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih Bulan') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $mName = DateTime::createFromFormat('!m', $m)->format('F'); @endphp
                            <option value="{{ $m }}" {{ ($filters['month'] == $m) ? 'selected' : '' }}>{{ __($mName) }}</option>
                        @endfor
                    </select>
                </div>

                {{-- WPP-NRI --}}
                <div>
                    <label for="stat_filter_wppnri" class="sr-only">{{ __('Pilih WPP-NRI') }}</label>
                    <select id="stat_filter_wppnri" name="wppnri_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih WPP-NRI') }}</option>
                        @foreach($filterOptions['wpp_list'] as $wpp)
                            <option value="{{ $wpp->id }}" {{ ($filters['wppnri_id'] == $wpp->id) ? 'selected' : '' }}>{{ $wpp->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pelabuhan / Landing Site --}}
                <div>
                    <label for="stat_filter_landing_site" class="sr-only">{{ __('Pilih Pangkalan / TPI') }}</label>
                    <select id="stat_filter_landing_site" name="landing_site_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih Pangkalan / TPI') }}</option>
                        @foreach($filterOptions['landing_sites'] as $site)
                            <option value="{{ $site->id }}" {{ ($filters['landing_site_id'] == $site->id) ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Alat Tangkap (Gear) --}}
                <div>
                    <label for="stat_filter_gear" class="sr-only">{{ __('Pilih Alat Tangkap') }}</label>
                    <select id="stat_filter_gear" name="gear_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih Alat Tangkap') }}</option>
                        @foreach($filterOptions['gears'] as $gear)
                            <option value="{{ $gear->id }}" {{ ($filters['gear_id'] == $gear->id) ? 'selected' : '' }}>{{ $gear->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Komoditas Spesies --}}
                <div>
                    <label for="stat_filter_species" class="sr-only">{{ __('Pilih Jenis Ikan (Spesies)') }}</label>
                    <select id="stat_filter_species" name="species_id" class="w-full text-xs rounded-lg border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Pilih Jenis Ikan (Spesies)') }}</option>
                        @foreach($filterOptions['top_species'] as $sp)
                            <option value="{{ $sp->id }}" {{ ($filters['species_id'] == $sp->id) ? 'selected' : '' }}>
                                {{ $sp->local_name_id ?: $sp->scientific_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <div class="text-[11px] text-slate-400">
                    {{ __('Aktif: ') }} <strong>{{ $filters['period_label'] }}</strong>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white rounded-lg font-semibold transition flex items-center gap-1.5 text-xs shadow-sm">
                        <span>🔍</span>
                        <span>{{ __('Terapkan Filter') }}</span>
                    </button>
                    <a href="{{ route('analysis.statistics.index') }}" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg transition text-xs">
                        {{ __('Reset Filter') }}
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Advanced 8 Charts Grid --}}
    <div class="space-y-6">
        <div class="flex items-center justify-between border-b border-slate-200 pb-2">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span>📊</span>
                <span>{{ __('Visualisasi Analitik Lanjutan (8 Grafik Terpadu)') }}</span>
            </h3>
            <span class="text-xs text-slate-500 font-mono">{{ __('Standard FAO / KKP') }}</span>
        </div>

        {{-- Row 1: Production Trend (Chart 1) & Catch Composition (Chart 3) --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Chart 1: Production Trend --}}
            <div class="lg:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>📈</span>
                            <span>{{ __('Chart 1: Tren Produksi & Tangkapan Bulanan') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Perbandingan kurva Observed Catch (Laut), Landing Production (TPI), dan Monthly Production (Dinas)') }}</p>
                    </div>
                </div>

                @if($trendChart['has_data'])
                    <div class="h-72 relative">
                        <canvas id="statTrendChart"></canvas>
                    </div>
                @else
                    <div class="h-72 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-4xl mb-2">📉</span>
                        <span>{{ __('Tidak ada data untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 3: Catch Composition (%) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🥧</span>
                            <span>{{ __('Chart 3: Komposisi Tangkapan (%)') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Proporsi tangkapan spesies ikan dominan (total 100%)') }}</p>
                    </div>
                </div>

                @if($speciesChart['has_data'])
                    <div class="h-72 relative">
                        <canvas id="statCompositionChart"></canvas>
                    </div>
                @else
                    <div class="h-72 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-4xl mb-2">🐟</span>
                        <span>{{ __('Tidak ada data spesies untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Catch by Species (Chart 2) & CPUE Trend (Chart 4) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart 2: Catch by Species (Volume kg) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🐠</span>
                            <span>{{ __('Chart 2: Hasil Tangkapan Berdasarkan Spesies (kg)') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Akumulasi bobot tangkapan berdasarkan identitas spesies resmi ASFIS') }}</p>
                    </div>
                </div>

                @if($speciesChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statSpeciesBarChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">🐟</span>
                        <span>{{ __('Tidak ada data tangkapan spesies untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 4: CPUE Trend --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>⏱️</span>
                            <span>{{ __('Chart 4: Tren CPUE (Catch Per Unit Effort)') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Rasio produktivitas bulanan: SUM(Catch kg) ÷ SUM(Duration Hours) dalam kg/jam') }}</p>
                    </div>
                </div>

                @if($cpueTrend['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statCpueChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">📈</span>
                        <span>{{ __('Tidak ada data CPUE untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 3: Length Frequency (Chart 5) & Catch by Gear (Chart 6) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart 5: Length Frequency Distribution --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>📏</span>
                            <span>{{ __('Chart 5: Distribusi Frekuensi Panjang (Length Frequency)') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Pengukuran biologis panjang cagak (Fork Length) interval 5 cm') }}</p>
                    </div>
                    @if($lengthFrequency['has_data'])
                        <div class="text-right text-[11px] font-mono text-slate-600 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200">
                            <span>n = {{ $lengthFrequency['valid_count'] }} ekor</span> |
                            <span>Min: {{ $lengthFrequency['min_length'] }} cm</span> |
                            <span>Max: {{ $lengthFrequency['max_length'] }} cm</span>
                        </div>
                    @endif
                </div>

                @if($lengthFrequency['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statLengthChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">📏</span>
                        <span>{{ __('Tidak ada data pengukuran biologi untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 6: Catch by Gear --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>⚓</span>
                            <span>{{ __('Chart 6: Hasil Tangkapan Berdasarkan Alat Tangkap') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Produktivitas dan akumulasi tangkapan per jenis alat penangkap ikan (ISSCFG)') }}</p>
                    </div>
                </div>

                @if($gearChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statGearChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">🎣</span>
                        <span>{{ __('Tidak ada data alat tangkap untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Row 4: Catch by WPP (Chart 7) & Fishing Effort Duration (Chart 8) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart 7: Catch by WPP (Transparent WPP NULL) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🌊</span>
                            <span>{{ __('Chart 7: Distribusi Produksi & Tangkapan per Wilayah WPP-NRI') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Transparansi data: WPP NULL dicatat tegas sebagai "Tidak Terpetakan"') }}</p>
                    </div>
                </div>

                @if($wppChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statWppChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">🗺️</span>
                        <span>{{ __('Tidak ada data WPP untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 8: Fishing Effort Duration & Settings --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>⏱️</span>
                            <span>{{ __('Chart 8: Upaya Penangkapan (Fishing Effort)') }}</span>
                        </h4>
                        <p class="text-xs text-slate-500">{{ __('Total jam operasi dan frekuensi setting alat tangkap') }}</p>
                    </div>
                </div>

                @if($effortChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="statEffortChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-slate-400 text-xs">
                        <span class="text-3xl mb-2">⚓</span>
                        <span>{{ __('Tidak ada data upaya untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Stage 13.4: Multi-Dimensional Cross Analysis Section --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-6">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>🧬</span>
                    <span>{{ __('Analisis Silang Multi-Dimensi (Multi-Dimensional Analysis)') }}</span>
                </h3>
                <p class="text-xs text-slate-500">{{ __('Korelasi silang bebas Cartesian duplicate: Species × Gear, Species × WPP, Gear × WPP, dan Pola Musim Bulanan') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Matriks 1: Species x Gear --}}
            <div class="p-4 rounded-xl bg-slate-50/75 border border-slate-200/80">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <span>🐟×🎣</span>
                    <span>{{ __('Spesies × Alat Tangkap') }}</span>
                </h4>
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($multiDimensional['species_x_gear'] as $sg)
                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900">{{ $sg->local_name_id ?: $sg->scientific_name }}</div>
                                <div class="text-[10px] text-ocean-700 font-medium">{{ $sg->gear_name }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-slate-900">{{ number_format($sg->total_catch_kg, 0, ',', '.') }} kg</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $sg->catch_records }} catatan</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">{{ __('Tidak ada data relasi spesies & alat tangkap.') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- Matriks 2: Species x WPP --}}
            <div class="p-4 rounded-xl bg-slate-50/75 border border-slate-200/80">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <span>🐟×🌊</span>
                    <span>{{ __('Spesies × Wilayah WPP') }}</span>
                </h4>
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($multiDimensional['species_x_wpp'] as $sw)
                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900">{{ $sw->local_name_id ?: $sw->scientific_name }}</div>
                                <div class="text-[10px] {{ $sw->wpp_name === 'Tidak Terpetakan' ? 'text-amber-700 font-semibold' : 'text-sky-700 font-medium' }}">
                                    {{ $sw->wpp_name }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-slate-900">{{ number_format($sw->total_catch_kg, 0, ',', '.') }} kg</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $sw->catch_records }} catatan</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">{{ __('Tidak ada data relasi spesies & WPP.') }}</div>
                    @endforelse
                </div>
            </div>

            {{-- Matriks 3: Gear x WPP --}}
            <div class="p-4 rounded-xl bg-slate-50/75 border border-slate-200/80">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                    <span>🎣×🌊</span>
                    <span>{{ __('Alat Tangkap × WPP') }}</span>
                </h4>
                <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                    @forelse($multiDimensional['gear_x_wpp'] as $gw)
                        <div class="p-2.5 bg-white rounded-lg border border-slate-200/60 flex items-center justify-between text-xs">
                            <div>
                                <div class="font-bold text-slate-900">{{ $gw->gear_name }}</div>
                                <div class="text-[10px] {{ $gw->wpp_name === 'Tidak Terpetakan' ? 'text-amber-700 font-semibold' : 'text-emerald-700 font-medium' }}">
                                    {{ $gw->wpp_name }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-mono font-bold text-slate-900">{{ number_format($gw->total_catch_kg, 0, ',', '.') }} kg</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $gw->trips_count }} trip</div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">{{ __('Tidak ada data relasi gear & WPP.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Rekapitulasi Statistik Produksi Bulanan (Monthly Production Statistics - Stage 11) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span>📋</span>
                    <span>{{ __('Rekapitulasi Statistik Produksi Bulanan Resmi (Stage 11 Integration)') }}</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ __('Data agregasi volume, nilai omzet, armada aktif, trip pelayaran, dan CPUE per pelabuhan pangkalan.') }}
                </p>
            </div>

            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 font-semibold text-slate-700">
                    {{ __('Total Agregat') }}: {{ number_format($aggVolumeKg / 1000, 1) }} Ton / Rp {{ number_format($aggValueRp / 1000000, 1) }} Jt
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-semibold text-[11px]">
                        <th class="py-3 px-4">{{ __('Periode') }}</th>
                        <th class="py-3 px-4">{{ __('Pelabuhan / Kabupaten') }}</th>
                        <th class="py-3 px-4">{{ __('Komoditas Ikan') }}</th>
                        <th class="py-3 px-4">{{ __('Alat Tangkap') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Volume (kg)') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Nilai Produksi (Rp)') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('Harga/kg') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Armada') }}</th>
                        <th class="py-3 px-4 text-center">{{ __('Trip') }}</th>
                        <th class="py-3 px-4 text-right">{{ __('CPUE (kg/trip)') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @forelse ($monthlyStats as $row)
                        @php
                            $rowCpue = $row->total_trips > 0 ? round($row->total_volume_kg / $row->total_trips, 1) : 0;
                            $monthName = DateTime::createFromFormat('!m', $row->month)->format('M');
                        @endphp
                        <tr class="hover:bg-slate-50/75 transition-colors">
                            <td class="py-3 px-4 font-mono font-bold text-slate-900 whitespace-nowrap">
                                {{ $monthName }} {{ $row->year }}
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-semibold text-slate-900">{{ $row->landingSite ? $row->landingSite->name : '-' }}</div>
                                <div class="text-[10px] text-slate-500">{{ $row->regency ? $row->regency->name : '-' }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <div class="font-semibold text-ocean-900">{{ $row->fishSpecies ? $row->fishSpecies->indonesian_name : '-' }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $row->fishSpecies ? $row->fishSpecies->code : '' }}</div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-medium">
                                    {{ $row->fishingGear ? $row->fishingGear->name : '-' }}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                {{ number_format($row->total_volume_kg, 0, ',', '.') }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">
                                Rp {{ number_format($row->total_value_rp, 0, ',', '.') }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono text-slate-600">
                                Rp {{ number_format($row->average_price_per_kg, 0, ',', '.') }}
                            </td>

                            <td class="py-3 px-4 text-center font-mono font-semibold">
                                {{ $row->total_active_vessels }}
                            </td>

                            <td class="py-3 px-4 text-center font-mono font-semibold text-ocean-700">
                                {{ $row->total_trips }}
                            </td>

                            <td class="py-3 px-4 text-right font-mono font-bold text-cyan-700">
                                {{ number_format($rowCpue, 1, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-slate-400 text-xs">
                                {{ __('Tidak ada data statistik produksi untuk filter yang dipilih.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Dropdown Baris Data --}}
        <x-pagination :paginator="$monthlyStats" />
    </div>

    {{-- Data Lineage & Methodology Guide --}}
    <div class="bg-gradient-to-br from-slate-50 to-ocean-50/30 rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
        <div class="flex items-center gap-2 border-b border-slate-200/60 pb-3">
            <span class="text-xl">ℹ️</span>
            <div>
                <h4 class="text-sm font-bold text-slate-900">{{ __('Panduan Metodologi & Definisi Semantik Metrik Perikanan') }}</h4>
                <p class="text-xs text-slate-500">{{ __('Standar verifikasi dan rekonsiliasi data penangkapan, pendaratan, estimasi, dan statistik dinas') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
            <div class="p-3.5 bg-white rounded-xl border border-slate-200/80 space-y-1.5 shadow-sm">
                <div class="font-bold text-ocean-700 flex items-center gap-1.5">
                    <span>🐟</span>
                    <span>1. {{ __('Total Catch (Tangkapan)') }}</span>
                </div>
                <div class="text-slate-600 leading-relaxed text-[11px]">
                    <strong>Sumber:</strong> Logbook & catatan laut (Tabel <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">catches</code>).<br>
                    <strong>Semantik Tanggal:</strong> <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">fishing_trips.departure_date</code>.<br>
                    <strong>Definisi:</strong> Hasil tangkapan riil yang diangkat saat beroperasi di fishing ground (Termasuk legacy catch ID 10 = 290 kg).
                </div>
            </div>

            <div class="p-3.5 bg-white rounded-xl border border-slate-200/80 space-y-1.5 shadow-sm">
                <div class="font-bold text-emerald-700 flex items-center gap-1.5">
                    <span>📦</span>
                    <span>2. {{ __('Total Landing (Pendaratan)') }}</span>
                </div>
                <div class="text-slate-600 leading-relaxed text-[11px]">
                    <strong>Sumber:</strong> Manifest timbang TPI (Tabel <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">landing_items</code>).<br>
                    <strong>Semantik Tanggal:</strong> <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">landings.landing_date</code>.<br>
                    <strong>Definisi:</strong> Bobot ikan yang dibongkar dan ditransaksikan di pelabuhan pangkalan TPI.
                </div>
            </div>

            <div class="p-3.5 bg-white rounded-xl border border-slate-200/80 space-y-1.5 shadow-sm">
                <div class="font-bold text-indigo-700 flex items-center gap-1.5">
                    <span>🎯</span>
                    <span>3. {{ __('Estimated Production') }}</span>
                </div>
                <div class="text-slate-600 leading-relaxed text-[11px]">
                    <strong>Sumber:</strong> Model raising factor (Tabel <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">catch_estimations</code>).<br>
                    <strong>Semantik Tanggal:</strong> Periode stratum (Bulan & Tahun).<br>
                    <strong>Definisi:</strong> Ekstrapolasi statistik dari sampel tangkapan terhadap total trip armada aktif (Stage 10).
                </div>
            </div>

            <div class="p-3.5 bg-white rounded-xl border border-slate-200/80 space-y-1.5 shadow-sm">
                <div class="font-bold text-cyan-700 flex items-center gap-1.5">
                    <span>📋</span>
                    <span>4. {{ __('Monthly Production') }}</span>
                </div>
                <div class="text-slate-600 leading-relaxed text-[11px]">
                    <strong>Sumber:</strong> Rekap dinas (Tabel <code class="bg-slate-100 px-1 py-0.5 rounded font-mono">monthly_production_statistics</code>).<br>
                    <strong>Semantik Tanggal:</strong> Rekapitulasi bulanan resmi.<br>
                    <strong>Definisi:</strong> Agregat final yang divalidasi dinas perikanan untuk pelaporan resmi statistik (Stage 11).
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const palette = ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#84cc16', '#ec4899', '#f97316', '#64748b'];

        // Chart 1: Production Trend
        const trendCanvas = document.getElementById('statTrendChart');
        if (trendCanvas) {
            const trendData = @json($trendChart);
            new Chart(trendCanvas, {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: trendData.datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        borderColor: ds.borderColor,
                        backgroundColor: ds.backgroundColor,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 4
                    }))
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { intersect: false, mode: 'index' },
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.dataset.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return (v / 1000).toLocaleString('id-ID') + ' Ton'; },
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart 3: Composition Donut
        const compCanvas = document.getElementById('statCompositionChart');
        if (compCanvas) {
            const compData = @json($speciesChart);
            new Chart(compCanvas, {
                type: 'doughnut',
                data: {
                    labels: compData.labels,
                    datasets: [{
                        data: compData.data,
                        backgroundColor: palette.slice(0, compData.labels.length),
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 10 } },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const val = ctx.raw || 0;
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                                    return ctx.label + ': ' + val.toLocaleString('id-ID') + ' kg (' + pct + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Chart 2: Species Bar
        const spBarCanvas = document.getElementById('statSpeciesBarChart');
        if (spBarCanvas) {
            const spData = @json($speciesChart);
            new Chart(spBarCanvas, {
                type: 'bar',
                data: {
                    labels: spData.labels,
                    datasets: [{
                        label: 'Hasil Tangkapan (kg)',
                        data: spData.data,
                        backgroundColor: 'rgba(2, 132, 199, 0.75)',
                        borderColor: '#0284c7',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return (v / 1000).toLocaleString('id-ID') + ' Ton'; },
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart 4: CPUE Bar
        const cpueCanvas = document.getElementById('statCpueChart');
        if (cpueCanvas) {
            const cpueData = @json($cpueTrend);
            new Chart(cpueCanvas, {
                type: 'line',
                data: {
                    labels: cpueData.labels,
                    datasets: [{
                        label: 'CPUE (kg/jam)',
                        data: cpueData.cpue_data,
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8, 145, 178, 0.15)',
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return 'CPUE: ' + ctx.raw.toLocaleString('id-ID') + ' kg/jam'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return v + ' kg/jam'; },
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart 5: Length Frequency
        const lenCanvas = document.getElementById('statLengthChart');
        if (lenCanvas) {
            const lenData = @json($lengthFrequency);
            new Chart(lenCanvas, {
                type: 'bar',
                data: {
                    labels: lenData.labels,
                    datasets: [{
                        label: 'Frekuensi (Ekor)',
                        data: lenData.data,
                        backgroundColor: 'rgba(139, 92, 246, 0.75)',
                        borderColor: '#7c3aed',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return 'Frekuensi: ' + ctx.raw + ' ekor'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1, font: { size: 10 } }
                        },
                        x: {
                            title: { display: true, text: 'Kelas Panjang Fork Length (cm)', font: { size: 10 } },
                            ticks: { font: { size: 9 } }
                        }
                    }
                }
            });
        }

        // Chart 6: Catch by Gear
        const gearCanvas = document.getElementById('statGearChart');
        if (gearCanvas) {
            const gearData = @json($gearChart);
            new Chart(gearCanvas, {
                type: 'bar',
                data: {
                    labels: gearData.labels,
                    datasets: [{
                        label: 'Hasil Tangkapan (kg)',
                        data: gearData.data,
                        backgroundColor: 'rgba(16, 185, 129, 0.75)',
                        borderColor: '#059669',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return (v / 1000).toLocaleString('id-ID') + ' Ton'; },
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart 7: Catch by WPP
        const wppCanvas = document.getElementById('statWppChart');
        if (wppCanvas) {
            const wppData = @json($wppChart);
            new Chart(wppCanvas, {
                type: 'bar',
                data: {
                    labels: wppData.labels,
                    datasets: [{
                        label: 'Hasil Tangkapan (kg)',
                        data: wppData.data,
                        backgroundColor: [
                            'rgba(2, 132, 199, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(139, 92, 246, 0.8)'
                        ],
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) { return ctx.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg'; }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(v) { return (v / 1000).toLocaleString('id-ID') + ' Ton'; },
                                font: { size: 10 }
                            }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }

        // Chart 8: Effort Chart
        const effortCanvas = document.getElementById('statEffortChart');
        if (effortCanvas) {
            const effortData = @json($effortChart);
            new Chart(effortCanvas, {
                type: 'bar',
                data: {
                    labels: effortData.labels,
                    datasets: [
                        {
                            label: 'Total Jam Operasi',
                            data: effortData.hours_data,
                            backgroundColor: 'rgba(99, 102, 241, 0.8)',
                            yAxisID: 'y'
                        },
                        {
                            label: 'Jumlah Setting',
                            data: effortData.settings_data,
                            backgroundColor: 'rgba(245, 158, 11, 0.8)',
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { font: { size: 11 } } }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Jam Operasi', font: { size: 10 } },
                            ticks: { font: { size: 10 } }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Setting', font: { size: 10 } },
                            ticks: { font: { size: 10 } }
                        },
                        x: { ticks: { font: { size: 10 } } }
                    }
                }
            });
        }
    });
</script>
@endpush
</x-app-layout>
