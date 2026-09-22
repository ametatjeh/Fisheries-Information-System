<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">🚢</span>
                <span class="font-bold text-slate-800">{{ __('GFW Vessel Monitoring (Pemantauan Kapal Satelit AIS/VMS & Track ZEE Aceh)') }}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>📊</span>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('dashboard.gis') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>🗺️</span>
                    <span>{{ __('Peta Terpadu GIS') }}</span>
                </a>
                <a href="{{ route('gfw.monitoring') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>🛰️</span>
                    <span>{{ __('GFW Monitoring') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- MapLibre GL JS CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" crossorigin=""/>
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" crossorigin=""></script>

    <div class="space-y-6">
        {{-- Banner Header & Freshness / Latency Notice --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-indigo-900/40">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🚢</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🛰️</span>
                        <span>{{ __('Global Fishing Watch (GFW) v3 Workspace') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                        <span>🚢</span>
                        <span>{{ __('GFW Vessel Monitoring — ZEE Indonesia Kawasan Aceh') }}</span>
                    </h2>
                    <p class="text-indigo-200 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ __('Pemantauan spasial kapal-kapal yang terdeteksi satelit AIS/VMS dalam Area of Interest (AOI) ZEE Aceh, pencarian identitas armada, inspeksi atribut kapal, serta visualisasi lintasan pergerakan (Observed Vessel Track).') }}
                    </p>
                </div>

                {{-- Latency & AOI Summary Badge --}}
                <div class="px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs max-w-md shrink-0">
                    <div class="font-bold flex items-center gap-1 text-amber-300">
                        <span>⚠️</span>
                        <span>{{ __('Pemberitahuan Latensi & AOI Spasial') }}</span>
                    </div>
                    <p class="mt-1 leading-normal opacity-90">
                        {{ $latencyNotice }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-amber-500/20 text-[11px] text-amber-300/90 flex flex-col gap-1">
                        <div class="flex items-center justify-between">
                            <span>AOI: <strong>{{ $aoiSummary['name'] ?? 'ZEE Indonesia - Kawasan Aceh' }}</strong></span>
                            <span class="font-mono text-[10px] bg-amber-400/20 px-1.5 py-0.5 rounded text-amber-200">EPSG:4326</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px] text-amber-200/80">
                            <span>Zona Observasi: <strong>Zona Observasi GFW +100 NM</strong></span>
                            <span class="font-mono text-[10px] bg-sky-400/20 px-1.5 py-0.5 rounded text-sky-200">185.2 km (185,200 m)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Search, Date Range & Filter Controls --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                {{-- Search Vessel --}}
                <div class="md:col-span-4">
                    <label for="vessel-search-input" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        {{ __('Search Vessel (Nama / MMSI / IMO / Call Sign)') }}
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="vessel-search-input"
                               placeholder="Ketik nama kapal, MMSI, IMO..."
                               class="w-full text-xs pl-8 pr-8 py-2.5 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 transition shadow-2xs">
                        <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none">🔍</span>
                        <button type="button" id="btn-clear-search" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-sm hidden font-bold">✕</button>
                    </div>
                </div>

                {{-- Start Date --}}
                <div class="md:col-span-2">
                    <label for="filter-start-date" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Tanggal Mulai') }}
                    </label>
                    <input type="date"
                           id="filter-start-date"
                           value="{{ $startDate }}"
                           class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- End Date --}}
                <div class="md:col-span-2">
                    <label for="filter-end-date" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Tanggal Selesai') }}
                    </label>
                    <input type="date"
                           id="filter-end-date"
                           value="{{ $endDate }}"
                           class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Flag Filter --}}
                <div class="md:col-span-2">
                    <label for="filter-flag" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Bendera (Flag)') }}
                    </label>
                    <select id="filter-flag" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Semua Bendera') }}</option>
                        <option value="IDN">🇮🇩 IDN (Indonesia)</option>
                        <option value="MYS">🇲🇾 MYS (Malaysia)</option>
                        <option value="THA">🇹🇭 THA (Thailand)</option>
                        <option value="VNM">🇻🇳 VNM (Vietnam)</option>
                        <option value="TWN">🇹🇼 TWN (Taiwan)</option>
                        <option value="CHN">🇨🇳 CHN (China)</option>
                    </select>
                </div>

                {{-- Vessel Type Filter --}}
                <div class="md:col-span-2">
                    <label for="filter-vessel-type" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Tipe Kapal') }}
                    </label>
                    <select id="filter-vessel-type" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Semua Tipe') }}</option>
                        <option value="fishing">{{ __('Fishing (Penangkap)') }}</option>
                        <option value="carrier">{{ __('Carrier / Cargo') }}</option>
                        <option value="support">{{ __('Support / Tug') }}</option>
                        <option value="passenger">{{ __('Passenger') }}</option>
                        <option value="tanker">{{ __('Tanker') }}</option>
                        <option value="other">{{ __('Lainnya / Unknown') }}</option>
                    </select>
                </div>
            </div>

            {{-- Action Buttons & Presets --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500 font-semibold">{{ __('Preset Rentang:') }}</span>
                    <button type="button" class="btn-preset-date px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition" data-days="7">7 Hari</button>
                    <button type="button" class="btn-preset-date px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition" data-days="14">14 Hari</button>
                    <button type="button" class="btn-preset-date px-2 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium transition" data-days="30">30 Hari</button>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-reset-filter" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        {{ __('Reset Filter') }}
                    </button>
                    <button type="button" id="btn-apply-filter" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <span>🔍</span>
                        <span>{{ __('Terapkan Filter') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Monitoring Split Container: Vessel List (Left) + Map Canvas (Right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Left Pane: Vessel List (4 cols on desktop) --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden flex flex-col h-[650px]">
                    {{-- List Header --}}
                    <div class="p-4 bg-slate-50 border-b border-slate-200/80 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                                <span>🚢</span>
                                <span>{{ __('Daftar Kapal Terdeteksi') }}</span>
                            </div>
                            <div class="text-[11px] text-slate-500 mt-0.5" id="vessel-list-subtitle">
                                {{ __('Dalam ZEE Indonesia - Kawasan Aceh') }}
                            </div>
                        </div>
                        <span id="vessel-count-badge" class="px-2.5 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 font-mono">
                            0 Kapal
                        </span>
                    </div>

                    {{-- Scrollable List Items --}}
                    <div id="vessel-list-container" class="flex-1 overflow-y-auto p-3 space-y-2 divide-y divide-slate-100">
                        {{-- Loading State --}}
                        <div id="vessel-list-loading" class="py-16 text-center text-slate-400">
                            <div class="inline-block animate-spin text-2xl mb-2">🔄</div>
                            <div class="text-xs font-semibold text-slate-600">{{ __('Memuat data kapal dari proxy GFW...') }}</div>
                            <div class="text-[11px] text-slate-400 mt-1">{{ __('Menghubungkan ke API internal gateway') }}</div>
                        </div>

                        {{-- Empty State --}}
                        <div id="vessel-list-empty" class="py-16 text-center text-slate-400 hidden">
                            <div class="text-3xl mb-2">🚢</div>
                            <div class="text-xs font-bold text-slate-700">{{ __('Tidak Ada Kapal Ditemukan') }}</div>
                            <p class="text-[11px] text-slate-500 mt-1 max-w-xs mx-auto">
                                {{ __('Tidak ditemukan kapal terdeteksi yang sesuai kriteria pencarian / rentang tanggal di ZEE Aceh.') }}
                            </p>
                        </div>
                    </div>

                    {{-- List Footer / Pagination & Count Info --}}
                    <div class="p-3 bg-slate-50 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-500">
                        <span id="vessel-pagination-info" class="text-[11px]">Menampilkan 0 kapal</span>
                        <div class="flex items-center gap-1">
                            <button type="button" id="btn-prev-page" class="px-2 py-1 rounded border border-slate-200 bg-white hover:bg-slate-100 text-[11px] disabled:opacity-40" disabled>&larr;</button>
                            <span id="vessel-page-number" class="text-[11px] font-mono px-1">1 / 1</span>
                            <button type="button" id="btn-next-page" class="px-2 py-1 rounded border border-slate-200 bg-white hover:bg-slate-100 text-[11px] disabled:opacity-40" disabled>&rarr;</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Pane: MapLibre GL JS Canvas (8 cols on desktop) --}}
            <div class="lg:col-span-8 space-y-3">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden relative">
                    {{-- Map Controls & Basemap Switcher Toolbar --}}
                    <div class="p-3 bg-slate-50 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex flex-wrap items-center gap-3">
                            {{-- Area Observasi Indicator --}}
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-slate-700">{{ __('Wilayah Observasi:') }}</span>
                                <span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-indigo-600 text-white shadow-xs">
                                    ZEE Aceh (Data Resmi BIG)
                                </span>
                            </div>

                            {{-- Basemap Switcher --}}
                            <div class="flex items-center gap-1.5">
                                <span class="font-semibold text-slate-700">{{ __('Basemap:') }}</span>
                                <div class="inline-flex rounded-lg border border-slate-300 p-0.5 bg-white shadow-2xs">
                                    <button type="button" id="btn-map-osm" class="px-2 py-1 rounded text-xs font-semibold bg-indigo-600 text-white shadow-xs">
                                        OSM
                                    </button>
                                    <button type="button" id="btn-map-ocean" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900">
                                        Ocean
                                    </button>
                                    <button type="button" id="btn-map-sat" class="px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900">
                                        Satelit
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Layer Toggles --}}
                        <div class="flex flex-wrap items-center gap-2">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-medium text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                <input type="checkbox" id="toggle-big-zee-aceh" checked class="rounded text-indigo-600 focus:ring-indigo-500 w-3.5 h-3.5">
                                <span>ZEE Aceh — BIG</span>
                            </label>
                            <label class="inline-flex items-center gap-1.5 cursor-pointer select-none text-[11px] font-medium text-slate-700 bg-white px-2.5 py-1 rounded-lg border border-slate-200 shadow-2xs">
                                <input type="checkbox" id="toggle-track" checked class="rounded text-cyan-600 focus:ring-cyan-500 w-3.5 h-3.5">
                                <span>Track</span>
                            </label>
                            <button type="button" id="btn-reset-map-view" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-medium transition text-xs shadow-2xs">
                                <span>🎯 Reset</span>
                            </button>
                        </div>
                    </div>

                    {{-- Notification / Map Status Overlay --}}
                    <div id="map-status-overlay" class="absolute top-14 right-4 z-20 px-3 py-1.5 rounded-xl bg-slate-900/90 text-white text-xs font-medium shadow-lg backdrop-blur-sm hidden items-center gap-2 pointer-events-none">
                        <span id="map-status-icon">🔄</span>
                        <span id="map-status-text">Memuat data peta...</span>
                    </div>

                    {{-- MapLibre Container --}}
                    <div id="gfw-vessels-map" style="height: 590px; width: 100%;" class="z-0"></div>

                    {{-- Map Legend Overlay --}}
                    <div class="absolute bottom-4 left-4 z-10 bg-slate-950/90 text-white backdrop-blur-md p-3 rounded-xl border border-slate-800 shadow-lg text-[11px] space-y-1.5 pointer-events-auto max-w-xs">
                        <div class="font-bold text-slate-200 uppercase tracking-wider text-[10px] pb-1 border-b border-slate-800 flex items-center justify-between">
                            <span>Legenda Peta Spasial</span>
                            <span class="text-[9px] text-slate-400 font-mono">BIG &bull; GFW</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-emerald-500 border border-white shrink-0"></span>
                            <span class="text-slate-300">Posisi Kapal (Detected Vessel)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-400 border border-white ring-2 ring-amber-300/50 shrink-0"></span>
                            <span class="text-slate-300">Kapal Terpilih (Selected Vessel)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-4 h-1 bg-cyan-400 rounded shrink-0"></span>
                            <span class="text-slate-300">Lintasan Pergerakan (Observed Track)</span>
                        </div>
                        <div class="pt-1 border-t border-slate-800/80">
                            <div class="text-[10px] text-slate-400 font-semibold mb-1">ZEE — Data Resmi BIG (Wilayah: Aceh):</div>
                            <div class="grid grid-cols-1 gap-1 text-[10px]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-1 bg-blue-600 rounded shrink-0"></span>
                                    <span class="text-slate-300">ZEE — Unilateral</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-1 bg-rose-500 rounded shrink-0"></span>
                                    <span class="text-slate-300">ZEE — Perlu Kesepakatan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-1 bg-emerald-500 rounded shrink-0"></span>
                                    <span class="text-slate-300">ZEE — Kesepakatan</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-3.5 h-1 bg-amber-500 rounded shrink-0"></span>
                                    <span class="text-slate-300">ZEE — Kesepakatan Belum Diratifikasi</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Pane: Selected Vessel Detail & Observed Track Summary --}}
        <div id="vessel-detail-card" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 transition-all">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📋</span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900" id="detail-title">{{ __('Identitas Kapal & Informasi Track (VESSEL DETAIL)') }}</h3>
                        <p class="text-[11px] text-slate-500" id="detail-subtitle">{{ __('Pilih kapal pada daftar atau klik marker pada peta untuk melihat detail spesifik.') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2" id="detail-actions">
                    <span id="detail-spatial-badge" class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        Belum ada kapal dipilih
                    </span>
                </div>
            </div>

            {{-- Detail Content Container --}}
            <div id="detail-content-empty" class="py-10 text-center text-slate-400">
                <div class="text-3xl mb-2">🎯</div>
                <div class="text-xs font-semibold text-slate-600">{{ __('Silakan pilih kapal dari daftar atau klik marker di peta') }}</div>
                <p class="text-[11px] text-slate-400 mt-1 max-w-sm mx-auto">
                    {{ __('Atribut identitas resmi (Nama, MMSI, IMO, Flag, Tipe), posisi observasi terakhir, serta rekaman track satelit akan ditampilkan di sini.') }}
                </p>
            </div>

            <div id="detail-content-active" class="hidden space-y-4">
                {{-- 2 Columns Grid: Vessel Identity (Left) + Track Observation (Right) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
                    {{-- Col 1: Identity Basic --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Identitas Kapal') }}</div>
                        <div>
                            <div class="text-[10px] text-slate-500">{{ __('Nama Kapal') }}</div>
                            <div class="font-bold text-sm text-slate-900" id="detail-vessel-name">—</div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1 border-t border-slate-200/60 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">{{ __('MMSI / SSVID') }}</span>
                                <span class="font-mono font-bold text-slate-800" id="detail-mmsi">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Nomor IMO') }}</span>
                                <span class="font-mono font-bold text-slate-800" id="detail-imo">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Col 2: Registry & Specifications --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Registrasi & Tipe') }}</div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">{{ __('Bendera (Flag)') }}</span>
                                <span class="font-bold text-slate-800" id="detail-flag">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Call Sign') }}</span>
                                <span class="font-mono font-bold text-slate-800" id="detail-callsign">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Tipe Kapal') }}</span>
                                <span class="font-semibold text-indigo-700" id="detail-type">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Alat Tangkap') }}</span>
                                <span class="font-semibold text-slate-800" id="detail-gear">—</span>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-slate-200/60 grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">{{ __('Panjang (LOA)') }}</span>
                                <span class="font-mono font-semibold text-slate-800" id="detail-length">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Tonase (GT)') }}</span>
                                <span class="font-mono font-semibold text-slate-800" id="detail-tonnage">—</span>
                            </div>
                        </div>
                    </div>

                    {{-- Col 3: Last Observed Position --}}
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 space-y-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">{{ __('Observasi Terakhir') }}</div>
                        <div>
                            <div class="text-[10px] text-slate-500">{{ __('Waktu Observasi (UTC)') }}</div>
                            <div class="font-mono font-bold text-slate-900 text-[11px]" id="detail-last-time">—</div>
                        </div>
                        <div class="pt-1 border-t border-slate-200/60 space-y-1 text-[11px]">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Latitude:') }}</span>
                                <span class="font-mono font-bold text-emerald-700" id="detail-lat">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Longitude:') }}</span>
                                <span class="font-mono font-bold text-emerald-700" id="detail-lon">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Kecepatan:') }}</span>
                                <span class="font-mono font-semibold text-slate-800" id="detail-speed">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Course:') }}</span>
                                <span class="font-mono font-semibold text-slate-800" id="detail-course">—</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-400">{{ __('Status Spasial:') }}</span>
                                <span class="font-semibold text-indigo-700" id="detail-spatial-status">Within AOI</span>
                            </div>
                        </div>
                    </div>

                    {{-- Col 4: Track Information --}}
                    <div class="p-3.5 rounded-xl bg-cyan-50/50 border border-cyan-100 space-y-2">
                        <div class="text-[10px] font-bold uppercase tracking-wider text-cyan-800 flex items-center justify-between">
                            <span>{{ __('Ringkasan Track') }}</span>
                            <span id="track-status-pill" class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-cyan-200 text-cyan-900">Aktif</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-[11px]">
                            <div>
                                <span class="text-slate-400 block">{{ __('Jumlah Titik') }}</span>
                                <span class="font-mono font-bold text-cyan-900 text-sm" id="detail-track-points">0 Titik</span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">{{ __('Rata-rata Speed') }}</span>
                                <span class="font-mono font-bold text-cyan-900 text-sm" id="detail-track-speed">—</span>
                            </div>
                        </div>
                        <div class="pt-1 border-t border-cyan-200/60 space-y-1 text-[10px]">
                            <div>
                                <span class="text-slate-400">{{ __('Awal Track:') }}</span>
                                <span class="font-mono text-slate-700" id="detail-track-start">—</span>
                            </div>
                            <div>
                                <span class="text-slate-400">{{ __('Akhir Track:') }}</span>
                                <span class="font-mono text-slate-700" id="detail-track-end">—</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Provenance & Disclaimer Note --}}
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-500 space-y-2">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/60 pb-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">🌐</span>
                            <span><strong>Sumber Batas ZEE:</strong> Badan Informasi Geospasial (BIG) — Peta Batas ZEE (Layer ID 10, Wilayah: Aceh).</span>
                        </div>
                        <div class="text-slate-400 font-mono text-[10px]">
                            Endpoint: <a href="https://kspservices.big.go.id/satupeta/rest/services/PUBLIK/BATAS_WILAYAH/MapServer/10" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline">BIG MapServer/10</a> &bull; Proxy: <span class="text-slate-600">/api/gis/big/zee/aceh</span>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="text-sm">🚢</span>
                            <span><strong>Sumber Observasi Kapal:</strong> Global Fishing Watch API v3 (Dataset Identitas &amp; Observasi Satelit AIS/VMS).</span>
                        </div>
                        <div class="text-slate-400 font-mono text-[10px]">
                            Proxy Internal: <span class="text-slate-600">/api/gfw/vessels</span> &bull; <span class="text-slate-600">/api/gfw/activity/vessels</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MapLibre GL JS Integration & Interactive Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Global state
            let map = null;
            let vesselsData = [];
            let filteredVessels = [];
            let selectedVesselId = null;
            let selectedVesselTrack = null;
            let aoiZeeGeoJson = null;
            let currentPage = 1;
            const pageSize = 15;
            let searchDebounceTimer = null;

            // Robust Value Formatters (GFW-V12 Hard Requirement: No null, undefined, NaN, 0000000)
            function formatText(val, fallback = 'Tidak tersedia') {
                if (val === null || val === undefined || val === '' || val === 'null' || val === 'undefined') {
                    return fallback;
                }
                const s = String(val).trim();
                return s === '' || s === 'null' || s === 'undefined' || s === '—' ? fallback : s;
            }

            function formatName(val) {
                if (!val || val === 'null' || val === 'undefined' || val === '—') {
                    return 'Tidak diketahui';
                }
                const s = String(val).trim();
                return s === '' ? 'Tidak diketahui' : s;
            }

            function formatType(val) {
                if (!val || val === 'null' || val === 'undefined' || val === '—' || val === 'other' || val === 'OTHER') {
                    return 'Tidak diketahui';
                }
                const s = String(val).trim();
                if (s === '') return 'Tidak diketahui';
                // Capitalize first letter (e.g. fishing -> Fishing)
                return s.charAt(0).toUpperCase() + s.slice(1).toLowerCase();
            }

            function formatLoa(val) {
                if (val === null || val === undefined || val === '' || val === 'null' || isNaN(parseFloat(val))) {
                    return 'Tidak tersedia';
                }
                const num = parseFloat(val);
                if (num < 0) return 'Tidak tersedia';
                return `${num.toFixed(1)} m`;
            }

            function formatTonnage(val) {
                if (val === null || val === undefined || val === '' || val === 'null' || isNaN(parseFloat(val))) {
                    return 'Tidak tersedia';
                }
                const num = parseFloat(val);
                if (num < 0) return 'Tidak tersedia';
                return `${num.toFixed(0)} GT`;
            }

            function formatSpeed(val) {
                if (val === null || val === undefined || val === '' || val === 'null' || isNaN(parseFloat(val))) {
                    return 'Tidak tersedia';
                }
                return `${parseFloat(val).toFixed(1)} kn`;
            }

            function formatCourse(val) {
                if (val === null || val === undefined || val === '' || val === 'null' || isNaN(parseFloat(val))) {
                    return 'Tidak tersedia';
                }
                return `${parseFloat(val).toFixed(0)}°`;
            }

            function formatCoord(val) {
                if (val === null || val === undefined || val === '' || val === 'null' || isNaN(parseFloat(val))) {
                    return 'Tidak tersedia';
                }
                return `${parseFloat(val).toFixed(5)}°`;
            }

            function formatObservedAt(val) {
                if (!val || val === 'null' || val === 'undefined' || val === '—') {
                    return 'Tidak tersedia';
                }
                try {
                    const d = new Date(val);
                    if (isNaN(d.getTime())) return 'Tidak tersedia';
                    return d.toUTCString();
                } catch {
                    return 'Tidak tersedia';
                }
            }

            // Ray-casting Point-in-Polygon for Geospatial Spatial Verification
            function isPointInRing(point, ring) {
                const x = point[0], y = point[1];
                let inside = false;
                for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                    const xi = ring[i][0], yi = ring[i][1];
                    const xj = ring[j][0], yj = ring[j][1];
                    const intersect = ((yi > y) !== (yj > y)) && (x < (xj - xi) * (y - yi) / (yj - yi) + xi);
                    if (intersect) inside = !inside;
                }
                return inside;
            }

            function isPointInGeoJson(point, geojson) {
                if (!geojson || !point) return false;
                const features = geojson.features || [geojson];
                for (const feat of features) {
                    const geom = feat.geometry || feat;
                    if (!geom || !geom.coordinates) continue;
                    if (geom.type === 'Polygon') {
                        if (isPointInRing(point, geom.coordinates[0])) return true;
                    } else if (geom.type === 'MultiPolygon') {
                        for (const poly of geom.coordinates) {
                            if (isPointInRing(point, poly[0])) return true;
                        }
                    }
                }
                return false;
            }

            // DOM Elements
            const searchInput = document.getElementById('vessel-search-input');
            const clearSearchBtn = document.getElementById('btn-clear-search');
            const startDateInput = document.getElementById('filter-start-date');
            const endDateInput = document.getElementById('filter-end-date');
            const flagFilter = document.getElementById('filter-flag');
            const typeFilter = document.getElementById('filter-vessel-type');
            const listContainer = document.getElementById('vessel-list-container');
            const listLoading = document.getElementById('vessel-list-loading');
            const listEmpty = document.getElementById('vessel-list-empty');
            const countBadge = document.getElementById('vessel-count-badge');
            const paginationInfo = document.getElementById('vessel-pagination-info');
            const pageNumber = document.getElementById('vessel-page-number');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');

            const statusOverlay = document.getElementById('map-status-overlay');
            const statusText = document.getElementById('map-status-text');
            const statusIcon = document.getElementById('map-status-icon');

            // Detail Card Elements
            const detailEmpty = document.getElementById('detail-content-empty');
            const detailActive = document.getElementById('detail-content-active');
            const detailVesselName = document.getElementById('detail-vessel-name');
            const detailMmsi = document.getElementById('detail-mmsi');
            const detailImo = document.getElementById('detail-imo');
            const detailFlag = document.getElementById('detail-flag');
            const detailCallsign = document.getElementById('detail-callsign');
            const detailType = document.getElementById('detail-type');
            const detailGear = document.getElementById('detail-gear');
            const detailLength = document.getElementById('detail-length');
            const detailTonnage = document.getElementById('detail-tonnage');
            const detailLastTime = document.getElementById('detail-last-time');
            const detailLat = document.getElementById('detail-lat');
            const detailLon = document.getElementById('detail-lon');
            const detailSpeed = document.getElementById('detail-speed');
            const detailCourse = document.getElementById('detail-course');
            const detailSpatialBadge = document.getElementById('detail-spatial-badge');
            const detailSpatialStatus = document.getElementById('detail-spatial-status');
            const detailTrackPoints = document.getElementById('detail-track-points');
            const detailTrackSpeed = document.getElementById('detail-track-speed');
            const detailTrackStart = document.getElementById('detail-track-start');
            const detailTrackEnd = document.getElementById('detail-track-end');

            function showStatus(text, icon = '🔄', isError = false) {
                statusText.innerText = text;
                statusIcon.innerText = icon;
                statusOverlay.classList.remove('hidden');
                statusOverlay.classList.toggle('bg-rose-900/90', isError);
                statusOverlay.classList.toggle('bg-slate-900/90', !isError);
            }

            function hideStatus() {
                statusOverlay.classList.add('hidden');
            }

            // 1. Initialize MapLibre GL JS Canvas
            const mapContainer = document.getElementById('gfw-vessels-map');
            if (mapContainer && typeof maplibregl !== 'undefined') {
                const osmStyle = {
                    version: 8,
                    sources: {
                        'osm-raster': {
                            type: 'raster',
                            tiles: [
                                'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                                'https://b.tile.openstreetmap.org/{z}/{x}/{y}.png',
                                'https://c.tile.openstreetmap.org/{z}/{x}/{y}.png'
                            ],
                            tileSize: 256,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &bull; Global Fishing Watch'
                        }
                    },
                    layers: [{
                        id: 'osm-raster-layer',
                        type: 'raster',
                        source: 'osm-raster',
                        minzoom: 0,
                        maxzoom: 19
                    }]
                };

                map = new maplibregl.Map({
                    container: 'gfw-vessels-map',
                    style: osmStyle,
                    center: [95.32, 5.55], // Aceh center [lng, lat]
                    zoom: 6.5,
                    minZoom: 3,
                    maxZoom: 18
                });

                map.addControl(new maplibregl.NavigationControl({ showCompass: true, showZoom: true }), 'top-right');
                map.addControl(new maplibregl.ScaleControl({ maxWidth: 120, unit: 'metric' }), 'bottom-right');

                map.on('load', async function () {
                    // Initialize empty GeoJSON sources and layers
                    initMapSourcesAndLayers();
                    // Load both AOI ZEE Aceh and 100 NM Buffer Zone
                    await loadAoiBoundary();
                    // Load Initial Vessels
                    await loadVesselsInAoi();
                });
            }

            // Basemap Switchers
            const basemaps = {
                osm: 'https://a.tile.openstreetmap.org/{z}/{x}/{y}.png',
                ocean: 'https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}',
                sat: 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
            };

            function switchBasemap(type, btn) {
                if (!map) return;
                const source = map.getSource('osm-raster');
                if (source) {
                    const tileUrl = basemaps[type] || basemaps.osm;
                    if (map.getLayer('osm-raster-layer')) {
                        map.removeLayer('osm-raster-layer');
                    }
                    if (map.getSource('osm-raster')) {
                        map.removeSource('osm-raster');
                    }
                    map.addSource('osm-raster', {
                        type: 'raster',
                        tiles: [tileUrl],
                        tileSize: 256,
                        attribution: 'Basemap &copy; OpenStreetMap/Esri &bull; Data Global Fishing Watch'
                    });
                    const firstLayerId = map.getStyle().layers[0]?.id;
                    map.addLayer({
                        id: 'osm-raster-layer',
                        type: 'raster',
                        source: 'osm-raster',
                        minzoom: 0,
                        maxzoom: 19
                    }, firstLayerId);
                }

                ['btn-map-osm', 'btn-map-ocean', 'btn-map-sat'].forEach(id => {
                    const b = document.getElementById(id);
                    if (b) b.className = 'px-2 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900';
                });
                btn.className = 'px-2 py-1 rounded text-xs font-semibold bg-indigo-600 text-white shadow-xs';
            }

            document.getElementById('btn-map-osm')?.addEventListener('click', function () { switchBasemap('osm', this); });
            document.getElementById('btn-map-ocean')?.addEventListener('click', function () { switchBasemap('ocean', this); });
            document.getElementById('btn-map-sat')?.addEventListener('click', function () { switchBasemap('sat', this); });

            // 2. Setup Sources and Layers for MapLibre
            function initMapSourcesAndLayers() {
                if (!map) return;

                // A. BIG Official ZEE Boundary Source & Layer for Aceh (Canonical: big-zee-aceh, big-zee-aceh-line)
                map.addSource('big-zee-aceh', {
                    type: 'geojson',
                    data: '/api/gis/big/zee/aceh'
                });

                map.addLayer({
                    id: 'big-zee-aceh-line',
                    type: 'line',
                    source: 'big-zee-aceh',
                    paint: {
                        'line-color': [
                            'match',
                            ['get', 'stslat'],
                            1, '#10b981', // Kesepakatan (Emerald)
                            2, '#2563eb', // Unilateral (Blue)
                            3, '#f59e0b', // Kesepakatan Belum Diratifikasi (Amber)
                            4, '#ef4444', // Perlu Kesepakatan (Rose)
                            '#3b82f6'      // Default
                        ],
                        'line-width': 2.5,
                        'line-opacity': 0.95
                    }
                });

                // C. Vessel Track Line & Points Source & Layers
                map.addSource('gfw-vessel-track-source', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] }
                });

                map.addLayer({
                    id: 'gfw-vessel-track-line',
                    type: 'line',
                    source: 'gfw-vessel-track-source',
                    filter: ['==', '$type', 'LineString'],
                    paint: {
                        'line-color': '#06b6d4',
                        'line-width': 3.5,
                        'line-opacity': 0.85
                    }
                });

                map.addLayer({
                    id: 'gfw-vessel-track-points',
                    type: 'circle',
                    source: 'gfw-vessel-track-source',
                    filter: ['==', '$type', 'Point'],
                    paint: {
                        'circle-radius': 4,
                        'circle-color': '#0891b2',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 1.5
                    }
                });

                // D. Vessel Positions Source & Layers (Canonical: source=gfw-vessels, layer=gfw-vessel-points)
                map.addSource('gfw-vessels', {
                    type: 'geojson',
                    data: { type: 'FeatureCollection', features: [] }
                });

                // Halo layer for selected vessel
                map.addLayer({
                    id: 'gfw-vessel-selected-halo',
                    type: 'circle',
                    source: 'gfw-vessels',
                    filter: ['==', 'id', ''],
                    paint: {
                        'circle-radius': 14,
                        'circle-color': '#f59e0b',
                        'circle-opacity': 0.35,
                        'circle-stroke-color': '#f59e0b',
                        'circle-stroke-width': 2
                    }
                });

                // Main circle layer for vessels
                map.addLayer({
                    id: 'gfw-vessel-points',
                    type: 'circle',
                    source: 'gfw-vessels',
                    paint: {
                        'circle-radius': [
                            'case',
                            ['boolean', ['feature-state', 'selected'], false],
                            9,
                            7
                        ],
                        'circle-color': [
                            'case',
                            ['boolean', ['feature-state', 'selected'], false],
                            '#d97706',
                            '#10b981'
                        ],
                        'circle-opacity': 0.95,
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 2
                    }
                });

                // Vessel Marker Click Event & Popup (GFW-V12 Exact Specification)
                map.on('click', 'gfw-vessel-points', function (e) {
                    if (e.features && e.features.length > 0) {
                        const feat = e.features[0];
                        const props = feat.properties || {};
                        const coords = feat.geometry.coordinates.slice();
                        const vesselId = props.id || props.gfw_vessel_id;

                        const shipName = formatName(props.shipname || props.name);
                        const mmsi = formatText(props.mmsi);
                        const imo = formatText(props.imo);
                        const callsign = formatText(props.callsign);
                        const flag = formatText(props.flag);
                        const vesselType = formatType(props.vesselType);
                        const loa = formatLoa(props.lengthM);
                        const observedAt = formatObservedAt(props.observed_at);
                        const speed = formatSpeed(props.speed);
                        const course = formatCourse(props.course);
                        const lat = formatCoord(coords[1]);
                        const lon = formatCoord(coords[0]);

                        new maplibregl.Popup({ offset: 14, closeButton: true })
                            .setLngLat(coords)
                            .setHTML(`
                                <div class="p-3 text-xs space-y-2 min-w-[270px]">
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                                        <span>🚢</span>
                                        <span>${shipName}</span>
                                    </div>
                                    <table class="w-full text-[11px] text-slate-600">
                                        <tbody>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 w-32 font-medium">Nama Kapal</td>
                                                <td class="font-semibold text-slate-800 py-0.5">: ${shipName}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">MMSI</td>
                                                <td class="font-mono font-semibold text-slate-800 py-0.5">: ${mmsi}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Nomor IMO</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${imo}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Call Sign</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${callsign}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Flag</td>
                                                <td class="font-semibold text-slate-800 py-0.5">: ${flag}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Tipe Kapal</td>
                                                <td class="font-semibold text-indigo-700 py-0.5">: ${vesselType}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Panjang (LOA)</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${loa}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Observasi Terakhir</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${observedAt}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Kecepatan</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${speed}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Course</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${course}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Latitude</td>
                                                <td class="font-mono font-semibold text-emerald-700 py-0.5">: ${lat}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Longitude</td>
                                                <td class="font-mono font-semibold text-emerald-700 py-0.5">: ${lon}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            `)
                            .addTo(map);

                        if (vesselId) {
                            selectVessel(vesselId, false);
                        }
                    }
                });

                // Hover cursor for vessel markers
                map.on('mouseenter', 'gfw-vessel-points', () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', 'gfw-vessel-points', () => { map.getCanvas().style.cursor = ''; });

                // BIG ZEE Line Click Event & Popup for Aceh
                map.on('click', 'big-zee-aceh-line', function (e) {
                    if (e.features && e.features.length > 0) {
                        const feat = e.features[0];
                        const props = feat.properties || {};
                        const coords = e.lngLat;

                        const statusLabel = props.status_label || formatText(props.stslat);
                        const stslat = props.stslat !== undefined && props.stslat !== null ? props.stslat : 'Tidak tersedia';
                        const objectId = props.objectid !== undefined && props.objectid !== null ? props.objectid : 'Tidak tersedia';
                        const pjgbts = props.pjgbts !== undefined && props.pjgbts !== null ? `${parseFloat(props.pjgbts).toFixed(2)} NM` : 'Tidak tersedia';
                        const srsId = props.srs_id || 'WGS84 (EPSG:4326)';

                        new maplibregl.Popup({ offset: 10, closeButton: true })
                            .setLngLat(coords)
                            .setHTML(`
                                <div class="p-3 text-xs space-y-2 min-w-[270px]">
                                    <div class="font-bold text-slate-800 text-sm flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                                        <span>🗺️</span>
                                        <span>Batas ZEE — Data Resmi BIG (Wilayah: Aceh)</span>
                                    </div>
                                    <table class="w-full text-[11px] text-slate-600">
                                        <tbody>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 w-28 font-medium">Sumber</td>
                                                <td class="font-semibold text-slate-800 py-0.5">: Badan Informasi Geospasial (BIG)</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Layer</td>
                                                <td class="font-semibold text-slate-800 py-0.5">: Peta Batas ZEE (Layer ID 10)</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Wilayah</td>
                                                <td class="font-semibold text-slate-800 py-0.5">: Aceh</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Status</td>
                                                <td class="font-semibold text-indigo-700 py-0.5">: ${statusLabel}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Kode Status</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${stslat}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">ID Objek</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${objectId}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Panjang Segmen</td>
                                                <td class="font-mono text-slate-800 py-0.5">: ${pjgbts}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-slate-400 py-0.5 font-medium">Sistem Koordinat</td>
                                                <td class="font-mono text-slate-700 py-0.5">: ${srsId}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            `)
                            .addTo(map);
                    }
                });

                // Hover cursor for BIG ZEE lines
                map.on('mouseenter', 'big-zee-aceh-line', () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', 'big-zee-aceh-line', () => { map.getCanvas().style.cursor = ''; });
            }

            // 3. Load BIG Official ZEE Boundary for Aceh and GFW AOI for Bounds
            async function loadAoiBoundary() {
                try {
                    // A. Load BIG Official ZEE Boundary for Aceh (Visual Boundary)
                    try {
                        const resBig = await fetch('/api/gis/big/zee/aceh');
                        const jsonBig = await resBig.json();
                        if (jsonBig && jsonBig.type === 'FeatureCollection') {
                            const srcBig = map?.getSource('big-zee-aceh');
                            if (srcBig) srcBig.setData(jsonBig);
                            if (jsonBig.notice) {
                                showStatus(jsonBig.notice, 'ℹ️');
                            }
                        }
                    } catch (eBig) {
                        console.warn('BIG ZEE Aceh loading error:', eBig);
                    }

                    // B. Load ZEE Aceh Base AOI for Map Bounds (Query GFW remains independent)
                    const resZee = await fetch('/api/gfw/aoi/zee-indonesia-aceh?geojson=1');
                    const jsonZee = await resZee.json();
                    if (jsonZee.success && jsonZee.geojson) {
                        aoiZeeGeoJson = jsonZee.geojson;
                        fitAoiBounds(aoiZeeGeoJson);
                    }
                } catch (e) {
                    console.warn('AOI loading error:', e);
                }
            }

            // Fit map bounds to GeoJSON
            function fitAoiBounds(geoJson) {
                if (!map || !geoJson) return;
                const bounds = new maplibregl.LngLatBounds();
                const extractCoords = (coords) => {
                    if (typeof coords[0] === 'number') {
                        bounds.extend(coords);
                    } else {
                        coords.forEach(extractCoords);
                    }
                };
                const features = geoJson.features || [geoJson];
                features.forEach(f => {
                    const geom = f.geometry || f;
                    if (geom && geom.coordinates) {
                        extractCoords(geom.coordinates);
                    }
                });
                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds, { padding: 50, maxZoom: 8.5 });
                }
            }

            // 5. Load Detected Vessels in ZEE Aceh
            async function loadVesselsInAoi() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const searchQuery = searchInput.value.trim();

                showStatus('Memuat data observasi kapal...', '🔄');
                listLoading.classList.remove('hidden');
                listEmpty.classList.add('hidden');

                try {
                    let endpoint = `/api/gfw/vessels?start_date=${startDate}&end_date=${endDate}&limit=50`;
                    
                    if (searchQuery.length >= 2) {
                        endpoint = `/api/gfw/vessels?query=${encodeURIComponent(searchQuery)}&limit=30&start_date=${startDate}&end_date=${endDate}`;
                    }

                    const res = await fetch(endpoint);
                    const json = await res.json();

                    listLoading.classList.add('hidden');

                    if (json.success && Array.isArray(json.data)) {
                        vesselsData = json.data.map((item, idx) => {
                            const vId = item.gfw_vessel_id || item.id || `vessel-${idx}`;
                            return {
                                id: vId,
                                gfw_vessel_id: vId,
                                name: item.name || item.shipname || item.raw_data?.shipname || null,
                                shipname: item.shipname || item.name || item.raw_data?.shipname || null,
                                mmsi: item.mmsi || item.ssvid || item.raw_data?.mmsi || null,
                                imo: item.imo || item.raw_data?.imo || null,
                                callsign: item.callsign || item.callSign || item.raw_data?.callsign || item.raw_data?.selfReportedInfo?.[0]?.callsign || null,
                                flag: item.flag || item.raw_data?.flag || null,
                                vessel_type: item.vessel_type || item.vesselType || item.raw_data?.vesselType || null,
                                gear_type: item.gear_type || item.geartype || item.raw_data?.geartype || null,
                                length_m: item.length_m !== undefined && item.length_m !== null ? item.length_m : (item.lengthM !== undefined ? item.lengthM : (item.raw_data?.lengthM ?? null)),
                                tonnage_gt: item.tonnage_gt !== undefined && item.tonnage_gt !== null ? item.tonnage_gt : (item.tonnageGt !== undefined ? item.tonnageGt : (item.raw_data?.tonnageGt ?? null)),
                                lat: (function() {
                                    const val = item.latitude !== undefined && item.latitude !== null ? item.latitude : (item.lat !== undefined && item.lat !== null ? item.lat : (item.position?.lat ?? null));
                                    const num = val !== null && !isNaN(parseFloat(val)) ? parseFloat(val) : null;
                                    return num;
                                })(),
                                lon: (function() {
                                    const val = item.longitude !== undefined && item.longitude !== null ? item.longitude : (item.lon !== undefined && item.lon !== null ? item.lon : (item.position?.lon ?? null));
                                    const num = val !== null && !isNaN(parseFloat(val)) ? parseFloat(val) : null;
                                    return num;
                                })(),
                                speed: item.speed !== undefined && item.speed !== null ? parseFloat(item.speed) : (item.speed_knots !== undefined ? parseFloat(item.speed_knots) : null),
                                course: item.course !== undefined && item.course !== null ? parseFloat(item.course) : null,
                                last_observed: item.observed_at || item.observation_timestamp || item.last_synced_at || null,
                                raw: item
                            };
                        }).map(v => {
                            if (v.lat === 0 && v.lon === 0) {
                                v.lat = null;
                                v.lon = null;
                            }
                            return v;
                        });

                        console.debug('[GFW-V12] Loaded vessels:', vesselsData.length);
                        console.debug('[GFW-V12] Vessels with coordinates:', vesselsData.filter(v => v.lat !== null && v.lon !== null).length);

                        applyFrontendFilters();
                        hideStatus();
                    } else {
                        vesselsData = [];
                        applyFrontendFilters();
                        if (json.error) {
                            showStatus(json.error, '⚠️', true);
                        } else {
                            showStatus('Tidak ada observasi kapal ditemukan.', 'ℹ️');
                        }
                        setTimeout(hideStatus, 4000);
                    }
                } catch (err) {
                    console.error('Failed fetching GFW vessels:', err);
                    listLoading.classList.add('hidden');
                    vesselsData = [];
                    applyFrontendFilters();
                    showStatus('Data GFW gagal dimuat.', '⚠️', true);
                    setTimeout(hideStatus, 5000);
                }
            }

            // 6. Apply Frontend Filters (Flag, Vessel Type, Area of Interest)
            function applyFrontendFilters() {
                const flagVal = flagFilter.value;
                const typeVal = typeFilter.value;

                filteredVessels = vesselsData.filter(v => {
                    let match = true;
                    if (flagVal && v.flag !== flagVal) {
                        match = false;
                    }
                    if (typeVal && typeVal !== 'other') {
                        const vt = (v.vessel_type || '').toLowerCase();
                        if (!vt.includes(typeVal.toLowerCase())) {
                            match = false;
                        }
                    }
                    return match;
                });

                currentPage = 1;
                renderVesselList();
                renderVesselMapMarkers();
            }

            // 7. Render Vessel List in Left Pane
            function renderVesselList() {
                const items = listContainer.querySelectorAll('.vessel-list-item');
                items.forEach(el => el.remove());

                countBadge.innerText = `${filteredVessels.length} Kapal`;

                if (filteredVessels.length === 0) {
                    listEmpty.classList.remove('hidden');
                    paginationInfo.innerText = 'Menampilkan 0 kapal';
                    pageNumber.innerText = '1 / 1';
                    btnPrev.disabled = true;
                    btnNext.disabled = true;
                    return;
                }

                listEmpty.classList.add('hidden');

                const totalPages = Math.ceil(filteredVessels.length / pageSize);
                const startIndex = (currentPage - 1) * pageSize;
                const pageVessels = filteredVessels.slice(startIndex, startIndex + pageSize);

                paginationInfo.innerText = `Menampilkan ${startIndex + 1} - ${Math.min(startIndex + pageSize, filteredVessels.length)} dari ${filteredVessels.length} kapal`;
                pageNumber.innerText = `${currentPage} / ${totalPages}`;
                btnPrev.disabled = currentPage <= 1;
                btnNext.disabled = currentPage >= totalPages;

                pageVessels.forEach(v => {
                    const isSelected = v.id === selectedVesselId;
                    const card = document.createElement('div');
                    card.className = `vessel-list-item p-3 rounded-xl border transition cursor-pointer select-none ${
                        isSelected 
                            ? 'bg-indigo-50/90 border-indigo-400 ring-2 ring-indigo-300/40 shadow-xs' 
                            : 'bg-white border-slate-200/80 hover:border-indigo-300 hover:bg-slate-50/80'
                    }`;

                    const flagBadge = v.flag ? `<span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">${v.flag}</span>` : '';
                    const typeBadge = `<span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 uppercase">${formatType(v.vessel_type)}</span>`;
                    
                    card.innerHTML = `
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <div class="font-bold text-xs text-slate-900 truncate flex items-center gap-1.5">
                                    <span>🚢</span>
                                    <span>${formatName(v.name)}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 font-mono mt-0.5">
                                    MMSI: <span class="font-semibold text-slate-700">${formatText(v.mmsi)}</span> &bull; IMO: <span>${formatText(v.imo)}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 shrink-0">
                                ${flagBadge}
                                ${typeBadge}
                            </div>
                        </div>
                        <div class="mt-2 pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                            <span>Posisi: <strong class="font-mono text-slate-600">${v.lat ? `${v.lat.toFixed(3)}°, ${v.lon.toFixed(3)}°` : 'Tidak tersedia'}</strong></span>
                            <span>${formatObservedAt(v.last_observed)}</span>
                        </div>
                    `;

                    card.addEventListener('click', () => {
                        selectVessel(v.id, true);
                    });

                    listContainer.appendChild(card);
                });
            }

            // 8. Render Map Markers for All Filtered Vessels
            function renderVesselMapMarkers() {
                if (!map) return;
                const source = map.getSource('gfw-vessels');
                if (!source) return;

                const validVessels = filteredVessels.filter(v => {
                    if (v.lat === null || v.lat === undefined || isNaN(v.lat)) return false;
                    if (v.lon === null || v.lon === undefined || isNaN(v.lon)) return false;
                    const lat = parseFloat(v.lat);
                    const lon = parseFloat(v.lon);
                    if (lat < -90 || lat > 90 || lon < -180 || lon > 180) return false;
                    if (lat === 0 && lon === 0) return false;
                    return true;
                });

                const features = validVessels.map(v => ({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [parseFloat(v.lon), parseFloat(v.lat)] // [lng, lat] (RFC 7946)
                    },
                    properties: {
                        id: v.id,
                        gfw_vessel_id: v.id,
                        shipname: v.name || v.shipname || null,
                        name: v.name || v.shipname || null,
                        mmsi: v.mmsi || null,
                        imo: v.imo || null,
                        callsign: v.callsign || null,
                        flag: v.flag || null,
                        vesselType: v.vessel_type || null,
                        lengthM: v.length_m || null,
                        tonnageGt: v.tonnage_gt || null,
                        speed: v.speed || null,
                        course: v.course || null,
                        latitude: v.lat,
                        longitude: v.lon,
                        observed_at: v.last_observed || null
                    }
                }));

                const geojson = {
                    type: 'FeatureCollection',
                    features: features
                };

                source.setData(geojson);

                // Update halo filter
                if (map.getLayer('gfw-vessel-selected-halo')) {
                    map.setFilter('gfw-vessel-selected-halo', ['==', 'id', selectedVesselId || '']);
                }

                // If vessels with coordinates exist, fit bounds to them
                if (features.length > 0) {
                    const bounds = new maplibregl.LngLatBounds();
                    features.forEach(f => bounds.extend(f.geometry.coordinates));
                    map.fitBounds(bounds, { padding: 60, maxZoom: 10 });
                }
            }

            // 9. Select Vessel, Inspect Detail & Load Track
            async function selectVessel(vesselId, flyTo = true) {
                selectedVesselId = vesselId;
                const vessel = vesselsData.find(v => v.id === vesselId);
                if (!vessel) return;

                renderVesselList();

                if (map && map.getLayer('gfw-vessel-selected-halo')) {
                    map.setFilter('gfw-vessel-selected-halo', ['==', 'id', vesselId]);
                }

                if (map && vessel.lat !== null && vessel.lon !== null && flyTo) {
                    map.flyTo({
                        center: [vessel.lon, vessel.lat],
                        zoom: Math.max(map.getZoom(), 8.5),
                        essential: true
                    });
                }

                // Fill Detail Pane with Normalized Vessel Object (Identical with Popup)
                detailEmpty.classList.add('hidden');
                detailActive.classList.remove('hidden');

                detailVesselName.innerText = formatName(vessel.name);
                detailMmsi.innerText = formatText(vessel.mmsi);
                detailImo.innerText = formatText(vessel.imo);
                detailFlag.innerText = formatText(vessel.flag);
                detailCallsign.innerText = formatText(vessel.callsign);
                detailType.innerText = formatType(vessel.vessel_type);
                detailGear.innerText = formatText(vessel.gear_type);
                detailLength.innerText = formatLoa(vessel.length_m);
                detailTonnage.innerText = formatTonnage(vessel.tonnage_gt);
                detailLastTime.innerText = formatObservedAt(vessel.last_observed);
                if (detailSpeed) detailSpeed.innerText = formatSpeed(vessel.speed);
                if (detailCourse) detailCourse.innerText = formatCourse(vessel.course);
                detailLat.innerText = formatCoord(vessel.lat);
                detailLon.innerText = formatCoord(vessel.lon);

                // Determine Spatial Relationship
                let spatialLabel = 'Dalam ZEE Aceh';
                if (vessel.lat !== null && vessel.lon !== null) {
                    const pt = [vessel.lon, vessel.lat];
                    if (aoiZeeGeoJson && isPointInGeoJson(pt, aoiZeeGeoJson)) {
                        spatialLabel = 'Dalam ZEE Aceh';
                    } else if (aoiBufferGeoJson && isPointInGeoJson(pt, aoiBufferGeoJson)) {
                        spatialLabel = 'Zona Observasi GFW +100 NM';
                    } else {
                        spatialLabel = 'Regional Presence';
                    }
                }
                if (detailSpatialStatus) detailSpatialStatus.innerText = spatialLabel;

                detailSpatialBadge.innerText = `Terpilih: ${formatName(vessel.name)}`;
                detailSpatialBadge.className = 'px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800';

                await loadVesselTrack(vesselId);
            }

            // 10. Load Track GeoJSON for Selected Vessel
            async function loadVesselTrack(vesselId) {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                showStatus('Memuat lintasan (track) kapal...', '🔄');

                try {
                    const res = await fetch(`/api/gfw/activity/vessels/${encodeURIComponent(vesselId)}?start_date=${startDate}&end_date=${endDate}`);
                    const json = await res.json();

                    const trackSource = map?.getSource('gfw-vessel-track-source');

                    if (json.success && Array.isArray(json.data) && json.data.length > 0) {
                        const points = json.data.filter(pt => pt.latitude !== null && pt.longitude !== null);
                        detailTrackPoints.innerText = `${points.length} Titik`;

                        if (points.length > 0) {
                            detailTrackStart.innerText = formatObservedAt(points[0].observation_timestamp);
                            detailTrackEnd.innerText = formatObservedAt(points[points.length - 1].observation_timestamp);

                            const validSpeeds = points.map(p => p.speed_knots).filter(s => s !== null && s !== undefined);
                            const avgSpeed = validSpeeds.length > 0 ? (validSpeeds.reduce((a, b) => a + b, 0) / validSpeeds.length).toFixed(1) : null;
                            detailTrackSpeed.innerText = formatSpeed(avgSpeed);

                            const coordinates = points.map(pt => [pt.longitude, pt.latitude]);
                            const pointFeatures = points.map((pt, i) => ({
                                type: 'Feature',
                                properties: {
                                    index: i,
                                    time: pt.observation_timestamp,
                                    speed: pt.speed_knots
                                },
                                geometry: {
                                    type: 'Point',
                                    coordinates: [pt.longitude, pt.latitude]
                                }
                            }));

                            const trackGeoJson = {
                                type: 'FeatureCollection',
                                features: [
                                    {
                                        type: 'Feature',
                                        properties: { id: vesselId, type: 'vessel-track' },
                                        geometry: {
                                            type: 'LineString',
                                            coordinates: coordinates
                                        }
                                    },
                                    ...pointFeatures
                                ]
                            };

                            if (trackSource) {
                                trackSource.setData(trackGeoJson);
                            }

                            if (map && coordinates.length > 1) {
                                const bounds = coordinates.reduce((b, coord) => b.extend(coord), new maplibregl.LngLatBounds(coordinates[0], coordinates[0]));
                                map.fitBounds(bounds, { padding: 40, maxZoom: 12 });
                            }
                        }
                        hideStatus();
                    } else {
                        detailTrackPoints.innerText = '0 Titik';
                        detailTrackSpeed.innerText = 'Tidak tersedia';
                        detailTrackStart.innerText = 'Tidak tersedia';
                        detailTrackEnd.innerText = 'Tidak tersedia';
                        if (trackSource) {
                            trackSource.setData({ type: 'FeatureCollection', features: [] });
                        }
                        hideStatus();
                    }
                } catch (err) {
                    console.warn('Track loading failed:', err);
                    detailTrackPoints.innerText = '0 Titik';
                    hideStatus();
                }
            }

            // 11. Search Input Event with Debounce
            searchInput.addEventListener('input', function () {
                const val = this.value.trim();
                clearSearchBtn.classList.toggle('hidden', val.length === 0);

                if (searchDebounceTimer) {
                    clearTimeout(searchDebounceTimer);
                }

                searchDebounceTimer = setTimeout(() => {
                    loadVesselsInAoi();
                }, 350);
            });

            clearSearchBtn.addEventListener('click', function () {
                searchInput.value = '';
                this.classList.add('hidden');
                loadVesselsInAoi();
            });

            // 12. Filter Changes
            flagFilter.addEventListener('change', applyFrontendFilters);
            typeFilter.addEventListener('change', applyFrontendFilters);

            // 13. Date Range Apply & Reset Buttons
            document.getElementById('btn-apply-filter').addEventListener('click', loadVesselsInAoi);
            document.getElementById('btn-reset-filter').addEventListener('click', function () {
                searchInput.value = '';
                clearSearchBtn.classList.add('hidden');
                flagFilter.value = '';
                typeFilter.value = '';
                loadVesselsInAoi();
            });

            // Date Presets
            document.querySelectorAll('.btn-preset-date').forEach(btn => {
                btn.addEventListener('click', function () {
                    const days = parseInt(this.getAttribute('data-days') || '7', 10);
                    const end = new Date();
                    end.setDate(end.getDate() - 3); // GFW latency offset
                    const start = new Date(end);
                    start.setDate(start.getDate() - days);

                    startDateInput.value = start.toISOString().split('T')[0];
                    endDateInput.value = end.toISOString().split('T')[0];
                    loadVesselsInAoi();
                });
            });

            // 14. Pagination Buttons
            btnPrev.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    renderVesselList();
                }
            });

            btnNext.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredVessels.length / pageSize);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderVesselList();
                }
            });

            // 15. Layer Visibility Toggles
            document.getElementById('toggle-big-zee-aceh')?.addEventListener('change', function () {
                if (!map || !map.getLayer('big-zee-aceh-line')) return;
                map.setLayoutProperty('big-zee-aceh-line', 'visibility', this.checked ? 'visible' : 'none');
            });

            document.getElementById('toggle-track')?.addEventListener('change', function () {
                if (!map) return;
                const vis = this.checked ? 'visible' : 'none';
                if (map.getLayer('gfw-vessel-track-line')) map.setLayoutProperty('gfw-vessel-track-line', 'visibility', vis);
                if (map.getLayer('gfw-vessel-track-points')) map.setLayoutProperty('gfw-vessel-track-points', 'visibility', vis);
            });

            document.getElementById('btn-reset-map-view')?.addEventListener('click', () => {
                if (!map) return;
                if (aoiZeeGeoJson) {
                    fitAoiBounds(aoiZeeGeoJson);
                } else {
                    map.flyTo({ center: [95.32, 5.55], zoom: 6.5, essential: true });
                }
            });
        });
    </script>
</x-app-layout>
