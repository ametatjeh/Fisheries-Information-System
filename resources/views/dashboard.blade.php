<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <div class="hidden md:flex items-center gap-2">
                <span class="text-xl">📊</span>
                <span class="font-bold text-white">{{ __('Dashboard Eksekutif & Analisis Terpadu') }}</span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-ocean-100 text-ocean-800 font-semibold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-ocean-600 animate-pulse"></span>
                    <span>Periode: {{ $filters['period_label'] }}</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        {{-- Welcome & Overview Banner --}}
        <div class="bg-gradient-to-r from-ocean-700 via-ocean-800 to-ocean-950 rounded-2xl p-6 text-white relative overflow-hidden shadow-sm">
            <div class="absolute right-2 -bottom-6 text-9xl opacity-10 pointer-events-none select-none">🐟</div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="max-w-2xl">
                        <div class="inline-flex items-center gap-2 bg-white/15 backdrop-blur-sm rounded-lg px-3 py-1 text-xs font-semibold text-ocean-100 mb-2">
                            <span>🏛️</span>
                            <span>{{ $currentOrganization?->organization_name ?? __('Dinas Kelautan dan Perikanan Aceh') }}</span>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight">{{ __('Selamat Datang') }}, {{ Auth::user()->name }}! 👋</h2>
                        <p class="mt-1 text-ocean-100 text-sm leading-relaxed">
                            {{ __('Sistem Data dan Statistik Perikanan Terpadu — Lapisan analitik real-time menghubungkan seluruh data operasional dari trip laut hingga manifest pendaratan TPI.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        @if(Auth::user()->roles->count() > 0)
                            <div class="bg-white/10 border border-white/20 rounded-xl px-3.5 py-2 text-right backdrop-blur-sm">
                                <div class="text-[10px] text-ocean-200 uppercase font-semibold">{{ __('Otoritas Role') }}</div>
                                <div class="text-sm font-bold text-white">{{ Auth::user()->roles->pluck('name')->join(', ') }}</div>
                            </div>
                        @endif
                        @can('access.gis')
                        <a href="{{ route('dashboard.gis') }}" class="px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs transition shadow-sm flex items-center gap-1.5">
                            <span>🗺️</span>
                            <span>{{ __('Buka Peta Terpadu') }}</span>
                        </a>
                        @endcan
                        @can('access.statistics')
                        <a href="{{ route('analysis.statistics.index') }}" class="px-4 py-2 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-semibold text-xs transition shadow-sm flex items-center gap-1.5">
                            <span>📈</span>
                            <span>{{ __('Buka Statistik Lengkap') }}</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        {{-- Filter Engine Bar --}}
        <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('dashboard') }}" class="flex flex-wrap items-end gap-3 text-xs">
                <div class="w-32">
                    <label class="block text-gray-500 font-semibold mb-1">{{ __('Tahun') }}</label>
                    <select name="year" class="w-full text-xs rounded-lg border-gray-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua Tahun') }}</option>
                        @foreach($filterOptions['years'] as $yr)
                            <option value="{{ $yr }}" {{ ($filters['year'] == $yr) ? 'selected' : '' }}>{{ $yr }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-36">
                    <label class="block text-gray-500 font-semibold mb-1">{{ __('Bulan') }}</label>
                    <select name="month" class="w-full text-xs rounded-lg border-gray-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua Bulan') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            @php $mName = DateTime::createFromFormat('!m', $m)->format('F'); @endphp
                            <option value="{{ $m }}" {{ ($filters['month'] == $m) ? 'selected' : '' }}>{{ __($mName) }}</option>
                        @endfor
                    </select>
                </div>

                <div class="w-48">
                    <label class="block text-gray-500 font-semibold mb-1">{{ __('Wilayah WPP-NRI') }}</label>
                    <select name="wppnri_id" class="w-full text-xs rounded-lg border-gray-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua WPP-NRI') }}</option>
                        @foreach($filterOptions['wpp_list'] as $wpp)
                            <option value="{{ $wpp->id }}" {{ ($filters['wppnri_id'] == $wpp->id) ? 'selected' : '' }}>{{ $wpp->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-52">
                    <label class="block text-gray-500 font-semibold mb-1">{{ __('Pelabuhan / TPI') }}</label>
                    <select name="landing_site_id" class="w-full text-xs rounded-lg border-gray-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua Pangkalan / TPI') }}</option>
                        @foreach($filterOptions['landing_sites'] as $site)
                            <option value="{{ $site->id }}" {{ ($filters['landing_site_id'] == $site->id) ? 'selected' : '' }}>{{ $site->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white rounded-lg font-semibold transition flex items-center gap-1">
                        <span>🔍</span>
                        <span>{{ __('Terapkan Filter') }}</span>
                    </button>
                    @if(!empty($filters['year']) || !empty($filters['month']) || !empty($filters['wppnri_id']) || !empty($filters['landing_site_id']))
                        <a href="{{ route('dashboard') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg transition">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Section 1: Activity & Operational KPIs --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>⚓</span>
                    <span>{{ __('Aktivitas Penangkapan & Armada Kapal') }}</span>
                </h3>
                <span class="text-xs text-gray-400 font-mono">{{ $filters['period_label'] }}</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Total Fishing Trips --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ $activityKpis['total_trips']['label'] }}</span>
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-lg text-sm">🚢</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        @if($activityKpis['total_trips']['has_data'])
                            <span class="text-3xl font-extrabold text-gray-900 font-mono">{{ $activityKpis['total_trips']['formatted'] }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $activityKpis['total_trips']['unit'] }}</span>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1" title="{{ $activityKpis['total_trips']['description'] }}">
                        {{ $activityKpis['total_trips']['description'] }}
                    </p>
                </div>

                {{-- Active Vessels in Trips --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ $activityKpis['active_vessels']['label'] }}</span>
                        <span class="p-2 bg-emerald-50 text-emerald-600 rounded-lg text-sm">⛵</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        @if($activityKpis['active_vessels']['has_data'])
                            <span class="text-3xl font-extrabold text-gray-900 font-mono">{{ $activityKpis['active_vessels']['formatted'] }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $activityKpis['active_vessels']['unit'] }}</span>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1" title="{{ $activityKpis['active_vessels']['description'] }}">
                        {{ $activityKpis['active_vessels']['description'] }}
                    </p>
                </div>

                {{-- Fishing Efforts (Settings) --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ $activityKpis['fishing_efforts']['label'] }}</span>
                        <span class="p-2 bg-amber-50 text-amber-600 rounded-lg text-sm">🎣</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        @if($activityKpis['fishing_efforts']['has_data'])
                            <span class="text-3xl font-extrabold text-gray-900 font-mono">{{ $activityKpis['fishing_efforts']['formatted'] }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $activityKpis['fishing_efforts']['unit'] }}</span>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1" title="{{ $activityKpis['fishing_efforts']['description'] }}">
                        {{ $activityKpis['fishing_efforts']['description'] }}
                    </p>
                </div>

                {{-- Total Effort Duration Hours --}}
                <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-gray-500 uppercase">{{ $activityKpis['total_effort_hours']['label'] }}</span>
                        <span class="p-2 bg-purple-50 text-purple-600 rounded-lg text-sm">⏱️</span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        @if($activityKpis['total_effort_hours']['has_data'])
                            <span class="text-3xl font-extrabold text-gray-900 font-mono">{{ $activityKpis['total_effort_hours']['formatted'] }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $activityKpis['total_effort_hours']['unit'] }}</span>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1" title="{{ $activityKpis['total_effort_hours']['description'] }}">
                        {{ $activityKpis['total_effort_hours']['description'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 2: Catch, Landing & CPUE KPIs --}}
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>🐟</span>
                    <span>{{ __('Hasil Tangkapan, Produksi Pendaratan & CPUE') }}</span>
                </h3>
                <span class="text-[11px] text-ocean-600 font-semibold bg-ocean-50 px-2 py-0.5 rounded border border-ocean-100">
                    Observed ≠ Landing
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Observed Catch (Physical Total) --}}
                <div class="bg-gradient-to-br from-white to-blue-50/40 rounded-xl p-5 shadow-sm border border-blue-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-blue-900 uppercase tracking-wider">{{ $catchKpis['total_catch_physical']['label'] }}</span>
                        <span class="p-2 bg-blue-100 text-blue-700 rounded-lg text-sm">🌊</span>
                    </div>
                    <div class="mt-2">
                        @if($catchKpis['total_catch_physical']['has_data'])
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-blue-950 font-mono">{{ $catchKpis['total_catch_physical']['formatted'] }}</span>
                                <span class="text-xs text-blue-700 font-bold">{{ $catchKpis['total_catch_physical']['unit'] }}</span>
                            </div>
                            <div class="text-xs text-blue-600 font-mono mt-0.5">
                                ≈ {{ $catchKpis['total_catch_physical']['ton'] }} Ton (Laut)
                            </div>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">
                        {{ $catchKpis['total_catch_physical']['description'] }}
                    </p>
                </div>

                {{-- Landing Production (TPI) --}}
                <div class="bg-gradient-to-br from-white to-emerald-50/40 rounded-xl p-5 shadow-sm border border-emerald-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-emerald-900 uppercase tracking-wider">{{ $catchKpis['total_landing']['label'] }}</span>
                        <span class="p-2 bg-emerald-100 text-emerald-700 rounded-lg text-sm">📦</span>
                    </div>
                    <div class="mt-2">
                        @if($catchKpis['total_landing']['has_data'])
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-emerald-950 font-mono">{{ $catchKpis['total_landing']['formatted'] }}</span>
                                <span class="text-xs text-emerald-700 font-bold">{{ $catchKpis['total_landing']['unit'] }}</span>
                            </div>
                            <div class="text-xs text-emerald-600 font-mono mt-0.5">
                                ≈ {{ $catchKpis['total_landing']['ton'] }} Ton (Darat)
                            </div>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">
                        {{ $catchKpis['total_landing']['description'] }}
                    </p>
                </div>

                {{-- Landing Value Omzet --}}
                <div class="bg-gradient-to-br from-white to-amber-50/40 rounded-xl p-5 shadow-sm border border-amber-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-amber-900 uppercase tracking-wider">{{ $catchKpis['total_landing_value']['label'] }}</span>
                        <span class="p-2 bg-amber-100 text-amber-700 rounded-lg text-sm">💰</span>
                    </div>
                    <div class="mt-2">
                        @if($catchKpis['total_landing_value']['has_data'])
                            <div class="text-2xl font-black text-amber-950 font-mono truncate">
                                {{ $catchKpis['total_landing_value']['formatted'] }}
                            </div>
                            <div class="text-xs text-amber-700 font-mono mt-0.5">
                                {{ __('Omzet lelang pangkalan TPI') }}
                            </div>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">
                        {{ $catchKpis['total_landing_value']['description'] }}
                    </p>
                </div>

                {{-- CPUE Productivity --}}
                <div class="bg-gradient-to-br from-white to-cyan-50/40 rounded-xl p-5 shadow-sm border border-cyan-100 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-cyan-900 uppercase tracking-wider">{{ $catchKpis['cpue']['label'] }}</span>
                        <span class="p-2 bg-cyan-100 text-cyan-700 rounded-lg text-sm">📈</span>
                    </div>
                    <div class="mt-2">
                        @if($catchKpis['cpue']['has_data'])
                            <div class="flex items-baseline gap-2">
                                <span class="text-3xl font-black text-cyan-950 font-mono">{{ $catchKpis['cpue']['formatted'] }}</span>
                                <span class="text-xs text-cyan-700 font-bold">{{ $catchKpis['cpue']['unit'] }}</span>
                            </div>
                            <div class="text-xs text-cyan-600 font-mono mt-0.5">
                                {{ __('Rasio Tangkapan / Jam Upaya') }}
                            </div>
                        @else
                            <span class="text-sm font-semibold text-amber-600 bg-amber-50 px-2.5 py-1 rounded-md">{{ __('Tidak ada data') }}</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400 mt-2">
                        {{ $catchKpis['cpue']['description'] }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Section 3: Interactive Analytical Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Chart 1: Production Trend (Observed vs Landing) --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <span>📊</span>
                            <span>{{ __('Tren Hasil Tangkapan (Laut) vs Pendaratan (TPI)') }}</span>
                        </h4>
                        <p class="text-xs text-gray-400">{{ __('Perbandingan bulanan tangkapan teramati dengan manifest pendaratan resmi.') }}</p>
                    </div>
                </div>

                @if($trendChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="dashboardTrendChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <span class="text-3xl mb-2">📉</span>
                        <span>{{ __('Tidak ada data untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 2: Catch by Species Composition --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <span>🐠</span>
                            <span>{{ __('Komposisi Spesies Tangkapan Utama') }}</span>
                        </h4>
                        <p class="text-xs text-gray-400">{{ __('Distribusi volume spesies ikan dominan berbasis master taksonomi ASFIS.') }}</p>
                    </div>
                </div>

                @if($speciesChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="dashboardSpeciesChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <span class="text-3xl mb-2">🐟</span>
                        <span>{{ __('Tidak ada data spesies untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 3: CPUE Productivity Trend --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <span>📈</span>
                            <span>{{ __('Tren Produktivitas CPUE (kg/jam)') }}</span>
                        </h4>
                        <p class="text-xs text-gray-400">{{ __('Laju tangkap per jam penarikan alat tangkap (SUM(Catch)/SUM(Effort Duration)).') }}</p>
                    </div>
                </div>

                @if($cpueTrend['has_data'])
                    <div class="h-64 relative">
                        <canvas id="dashboardCpueChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <span class="text-3xl mb-2">⏱️</span>
                        <span>{{ __('Tidak ada data upaya untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>

            {{-- Chart 4: Catch by WPP --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-1.5">
                            <span>🌊</span>
                            <span>{{ __('Distribusi Tangkapan per WPP-NRI') }}</span>
                        </h4>
                        <p class="text-xs text-gray-400">{{ __('Sebaran tangkapan menurut zona WPP (WPP NULL dicatat transparan).') }}</p>
                    </div>
                </div>

                @if($wppChart['has_data'])
                    <div class="h-64 relative">
                        <canvas id="dashboardWppChart"></canvas>
                    </div>
                @else
                    <div class="h-64 flex flex-col items-center justify-center text-gray-400 text-xs">
                        <span class="text-3xl mb-2">🗺️</span>
                        <span>{{ __('Tidak ada data WPP untuk filter yang dipilih.') }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Section 4: Data Lineage & Pipeline Tracker --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
            <div class="border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span>⛓️</span>
                    <span>{{ __('Alur Data Lineage Terpadu Sistem Perikanan') }}</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ __('Setiap angka pada dashboard dapat ditelusuri kembali ke tabel sumber tanpa asumsi atau modifikasi historis.') }}
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 text-center">
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="text-[10px] text-gray-400 uppercase font-semibold">1. Master</div>
                    <div class="font-bold text-sm text-gray-800 font-mono mt-1">19 Kapal</div>
                    <div class="text-[10px] text-gray-500">13 Pangkalan</div>
                </div>

                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="text-[10px] text-blue-600 uppercase font-semibold">2. Activity</div>
                    <div class="font-bold text-sm text-blue-900 font-mono mt-1">45 Trip</div>
                    <div class="text-[10px] text-blue-700">Laut Aceh</div>
                </div>

                <div class="p-3 bg-indigo-50 rounded-xl border border-indigo-100">
                    <div class="text-[10px] text-indigo-600 uppercase font-semibold">3. Effort</div>
                    <div class="font-bold text-sm text-indigo-900 font-mono mt-1">62 Setting</div>
                    <div class="text-[10px] text-indigo-700">471.5 Jam</div>
                </div>

                <div class="p-3 bg-cyan-50 rounded-xl border border-cyan-100">
                    <div class="text-[10px] text-cyan-600 uppercase font-semibold">4. Catch</div>
                    <div class="font-bold text-sm text-cyan-900 font-mono mt-1">29.090 kg</div>
                    <div class="text-[10px] text-cyan-700">169 Catatan</div>
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <div class="text-[10px] text-emerald-600 uppercase font-semibold">5. Landing</div>
                    <div class="font-bold text-sm text-emerald-900 font-mono mt-1">21.350 kg</div>
                    <div class="text-[10px] text-emerald-700">Rp 719,1 Jt</div>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="text-[10px] text-amber-600 uppercase font-semibold">6. Sampling</div>
                    <div class="font-bold text-sm text-amber-900 font-mono mt-1">33 Ikan</div>
                    <div class="text-[10px] text-amber-700">Biometrik</div>
                </div>

                <div class="p-3 bg-violet-50 rounded-xl border border-violet-100">
                    <div class="text-[10px] text-violet-600 uppercase font-semibold">7. Estimasi</div>
                    <div class="font-bold text-sm text-violet-900 font-mono mt-1">217,9 Ton</div>
                    <div class="text-[10px] text-violet-700">Stage 10</div>
                </div>

                <div class="p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <div class="text-[10px] text-rose-600 uppercase font-semibold">8. Produksi</div>
                    <div class="font-bold text-sm text-rose-900 font-mono mt-1">341,0 Ton</div>
                    <div class="text-[10px] text-rose-700">Stage 11</div>
                </div>
            </div>
        </div>

        {{-- Section 5: Dedicated GIS & Satellite Workspace Access --}}
        @can('access.gis')
        <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-950 rounded-2xl p-6 text-white border border-indigo-500/20 shadow-lg relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-9xl opacity-5 pointer-events-none select-none">🗺️</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div class="max-w-2xl">
                    <div class="inline-flex items-center gap-2 bg-indigo-500/20 border border-indigo-400/30 rounded-lg px-3 py-1 text-xs font-semibold text-indigo-300 mb-2">
                        <span>🌐</span>
                        <span>{{ __('Akses Spasial Internal Perikanan Aceh') }}</span>
                    </div>
                    <h3 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                        <span>🗺️</span>
                        <span>{{ __('Peta Terpadu GIS & Pemantauan Satelit') }}</span>
                    </h3>
                    <p class="mt-1 text-slate-300 text-xs sm:text-sm leading-relaxed">
                        {{ __('Eksplorasi data geospasial aktivitas penangkapan ikan, sebaran fishing ground master, pangkalan TPI, koordinat setting alat tangkap, serta integrasi pemantauan satelit AIS Global Fishing Watch (GFW).') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('dashboard.gis') }}" class="px-5 py-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold text-xs sm:text-sm transition shadow-md shadow-indigo-900/40 flex items-center gap-2">
                        <span>🗺️</span>
                        <span>{{ __('Buka Peta Terpadu GIS') }}</span>
                        <span class="text-xs">→</span>
                    </a>
                    <a href="{{ route('gfw.monitoring') }}" class="px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-indigo-200 hover:text-white font-bold text-xs sm:text-sm transition backdrop-blur-sm flex items-center gap-2">
                        <span>🛰️</span>
                        <span>{{ __('GFW Monitoring Workspace') }}</span>
                        <span class="text-xs">→</span>
                    </a>
                    <a href="{{ route('gfw.vessels') }}" class="px-5 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-emerald-200 hover:text-white font-bold text-xs sm:text-sm transition backdrop-blur-sm flex items-center gap-2">
                        <span>🔭</span>
                        <span>{{ __('GFW Vessel Observatory') }}</span>
                        <span class="text-xs">→</span>
                    </a>
                </div>
            </div>
        </div>
        @endcan

        {{-- Section 6: Quick Operational Actions --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @can('access.trips')
            <a href="{{ route('trips.index') }}" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-ocean-300 hover:bg-ocean-50/20 transition flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center font-bold">🚢</div>
                <div>
                    <div class="text-xs font-bold text-gray-800">{{ __('Trip Penangkapan') }}</div>
                    <div class="text-[11px] text-gray-500">{{ __('Kelola pelayaran kapal laut') }}</div>
                </div>
            </a>
            @endcan

            @can('access.catches')
            <a href="{{ route('catches.index') }}" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-ocean-300 hover:bg-ocean-50/20 transition flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-cyan-100 text-cyan-700 flex items-center justify-center font-bold">🐟</div>
                <div>
                    <div class="text-xs font-bold text-gray-800">{{ __('Catatan Tangkapan') }}</div>
                    <div class="text-[11px] text-gray-500">{{ __('Entri data hasil tangkap di laut') }}</div>
                </div>
            </a>

            <a href="{{ route('landings.index') }}" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-ocean-300 hover:bg-ocean-50/20 transition flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">📦</div>
                <div>
                    <div class="text-xs font-bold text-gray-800">{{ __('Pendaratan Ikan TPI') }}</div>
                    <div class="text-[11px] text-gray-500">{{ __('Manifest timbang lelang TPI') }}</div>
                </div>
            </a>
            @endcan

            @can('access.gis')
            <a href="{{ route('dashboard.gis') }}" class="p-4 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-ocean-300 hover:bg-ocean-50/20 transition flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold">🗺️</div>
                <div>
                    <div class="text-xs font-bold text-gray-800">{{ __('Peta Terpadu GIS') }}</div>
                    <div class="text-[11px] text-gray-500">{{ __('Sebaran lokasi effort & WPP') }}</div>
                </div>
            </a>
            @endcan
        </div>
    </div>

    {{-- Chart.js Script Initialization --}}
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Palette Colors
            const colors = ['#0284c7', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#84cc16'];

            // 1. Production Trend Chart
            const trendCtx = document.getElementById('dashboardTrendChart');
            if (trendCtx) {
                const trendData = @json($trendChart);
                new Chart(trendCtx, {
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
                            pointRadius: 4,
                            pointHoverRadius: 6
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
                                    label: function(ctx) {
                                        return ctx.dataset.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg';
                                    }
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

            // 2. Species Composition Chart
            const speciesCtx = document.getElementById('dashboardSpeciesChart');
            if (speciesCtx) {
                const spData = @json($speciesChart);
                new Chart(speciesCtx, {
                    type: 'doughnut',
                    data: {
                        labels: spData.labels,
                        datasets: [{
                            data: spData.data,
                            backgroundColor: colors.slice(0, spData.labels.length),
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 12 } },
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

            // 3. CPUE Trend Chart
            const cpueCtx = document.getElementById('dashboardCpueChart');
            if (cpueCtx) {
                const cpueData = @json($cpueTrend);
                new Chart(cpueCtx, {
                    type: 'bar',
                    data: {
                        labels: cpueData.labels,
                        datasets: [{
                            label: 'CPUE (kg/jam)',
                            data: cpueData.cpue_data,
                            backgroundColor: 'rgba(6, 182, 212, 0.75)',
                            borderColor: '#0891b2',
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
                                    label: function(ctx) {
                                        return 'CPUE: ' + ctx.raw.toLocaleString('id-ID') + ' kg/jam';
                                    }
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

            // 4. Catch by WPP Chart
            const wppCtx = document.getElementById('dashboardWppChart');
            if (wppCtx) {
                const wppData = @json($wppChart);
                new Chart(wppCtx, {
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
                                    label: function(ctx) {
                                        return ctx.label + ': ' + ctx.raw.toLocaleString('id-ID') + ' kg';
                                    }
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
        });
    </script>
    @endpush
</x-app-layout>
