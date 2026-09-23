<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center shadow-xs">
                    <span class="text-lg">🚢</span>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-800 tracking-tight leading-tight">
                        {{ __('GFW VESSEL OBSERVATORY') }}
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        {{ __('ZEE Indonesia – Kawasan Aceh') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
                    <span>📊</span>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('dashboard.gis') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
                    <span>🗺️</span>
                    <span>{{ __('Peta GIS') }}</span>
                </a>
                <a href="{{ route('gfw.monitoring') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
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
        {{-- Banner Header, AOI Attribution & Source Info --}}
        <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-indigo-900/50">
            <div class="absolute right-4 -bottom-6 text-9xl opacity-5 pointer-events-none select-none">🚢</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-3xl space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[11px] font-semibold tracking-wide uppercase">
                            <span>🛰️</span>
                            <span>Global Fishing Watch v3</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-semibold">
                            <span>🏛️</span>
                            <span>AOI Source: BIG</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-[11px] font-semibold">
                            <span>📡</span>
                            <span>Vessel Data: Global Fishing Watch</span>
                        </span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight text-white flex flex-wrap items-center gap-2">
                        <span>GFW VESSEL OBSERVATORY</span>
                        <span class="text-indigo-400 font-light text-lg">|</span>
                        <span class="text-indigo-200 text-base font-medium">GFW Vessel Monitoring (ZEE Indonesia Kawasan Aceh)</span>
                    </h2>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                        Observatori pemantauan spasial terpadu seluruh armada kapal (fishing vessel & other commercial vessels) yang terdeteksi satelit AIS/VMS dalam Area of Interest (AOI) ZEE Aceh, dengan visualisasi posisi dan lintasan pergerakan (Observed Track) dalam batas poligon resmi <strong>ZEE Indonesia Kawasan Aceh (Badan Informasi Geospasial)</strong>.
                    </p>
                </div>

                {{-- AOI & Provenance Summary Badge --}}
                <div class="px-4 py-3.5 rounded-xl bg-slate-800/80 backdrop-blur-xs border border-indigo-500/30 text-xs max-w-md shrink-0 space-y-2 text-slate-200">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-700/60 font-semibold text-indigo-300">
                        <span class="flex items-center gap-1.5">
                            <span>🗺️</span>
                            <span>{{ __('Pemberitahuan Latensi & AOI Spasial') }}</span>
                        </span>
                        <span class="font-mono text-[10px] bg-indigo-900/60 text-indigo-300 px-1.5 py-0.5 rounded border border-indigo-700/50">EPSG:4326</span>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-[11px]">
                        <div>
                            <span class="text-slate-400">ID Poligon:</span>
                            <span class="font-mono text-slate-200 ml-1 font-semibold">zee-indonesia-aceh</span>
                            <span class="text-slate-400 text-[10px] ml-1">(ZEE Aceh)</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Sumber Batas:</span>
                            <span class="text-emerald-400 ml-1 font-semibold">BIG</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Tipe Geometri:</span>
                            <span class="text-slate-200 ml-1">Polygon (Batas Resmi)</span>
                        </div>
                        <div>
                            <span class="text-slate-400">Maksimal Rentang:</span>
                            <span class="text-amber-400 ml-1 font-semibold">7 Hari (API GFW)</span>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center justify-between gap-1.5 pt-1 border-t border-slate-700/40 text-[10px]">
                        <span class="text-indigo-300 font-medium">Batas Spasial Resmi ZEE Aceh (BIG)</span>
                        <label class="inline-flex items-center gap-1 cursor-pointer text-slate-300">
                            <input type="checkbox" id="toggle-big-zee-aceh" checked class="rounded border-slate-600 bg-slate-700 text-indigo-500 focus:ring-0 text-[10px]">
                            <span>ZEE — Data Resmi BIG</span>
                        </label>
                    </div>
                    <div class="text-[10px] text-slate-400 pt-1 border-t border-slate-700/40">
                        <span class="text-amber-400">ℹ️ Catatan:</span> Membedakan kehadiran kapal (<em>Vessel Presence</em>) dengan indikasi penangkapan (<em>Fishing Activity</em>). Jika atribut belum terdata maka ditampilkan <em>Tidak tersedia</em>.
                    </div>
                    <div class="pt-2 border-t border-slate-700/40 flex items-center justify-between text-[11px]">
                        <span class="text-slate-300 font-semibold flex items-center gap-1.5">
                            <span id="vessels-live-pulse" class="w-2 h-2 rounded-full bg-slate-500 inline-block"></span>
                            <span>Live Monitoring:</span>
                        </span>
                        <button type="button" id="btn-toggle-live-vessels" class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-700 text-slate-300 hover:bg-slate-600 transition">
                            OFF
                        </button>
                    </div>
                    <div class="text-[10px] text-slate-400 flex items-center justify-between pt-0.5">
                        <span>Pembaruan: <span id="meta-last-updated" class="text-indigo-300 font-mono">-</span></span>
                        <span>Usia Data: <span id="meta-data-age" class="text-slate-300 font-mono">-</span></span>
                    </div>
                    <div id="meta-delta-container" class="hidden pt-1 border-t border-slate-700/40">
                        <span id="meta-delta-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-700 block text-center">
                            Memeriksa perubahan data...
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fallback / Error Resilience Notice --}}
        <div id="vessels-refresh-error-notice" class="hidden p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-base">⚠️</span>
                <div>
                    <span class="font-bold text-amber-100">Gagal memperbarui data dari GFW API.</span>
                    <span class="text-[11px] text-amber-300 ml-1">Menampilkan dataset berhasil terakhir. Data kapal tidak direset ke 0.</span>
                </div>
            </div>
            <button type="button" id="btn-retry-vessels" class="px-3 py-1 rounded-lg bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shrink-0">
                🔄 Coba Lagi
            </button>
        </div>

        {{-- KPI / Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Vessels --}}
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ __('Total Vessels') }}</span>
                    <h3 id="stat-total-vessels" class="text-2xl font-black text-slate-800 mt-1">
                        <span class="animate-pulse">...</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Armada teramati di ZEE</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    🚢
                </div>
            </div>

            {{-- Fishing Vessels --}}
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-600">{{ __('Fishing Vessels') }}</span>
                    <h3 id="stat-fishing-vessels" class="text-2xl font-black text-emerald-600 mt-1">
                        <span class="animate-pulse">...</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kapal perikanan tangkap</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shrink-0">
                    🎣
                </div>
            </div>

            {{-- Other Vessels --}}
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-sky-600">{{ __('Other Vessels') }}</span>
                    <h3 id="stat-other-vessels" class="text-2xl font-black text-sky-600 mt-1">
                        <span class="animate-pulse">...</span>
                    </h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Kargo, tanker, tug, carrier</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl shrink-0">
                    ⛴️
                </div>
            </div>

            {{-- Flags --}}
            <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/80 flex items-center justify-between">
                <div>
                    <span class="text-[11px] font-semibold uppercase tracking-wider text-purple-600">{{ __('Flags') }}</span>
                    <h3 id="stat-flags" class="text-2xl font-black text-purple-600 mt-1">
                        <span class="animate-pulse">...</span>
                    </h3>
                    <p id="stat-flags-preview" class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[140px]">Negara bendera</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl shrink-0">
                    🚩
                </div>
            </div>
        </div>

        {{-- Filter Controls --}}
        <div class="bg-white rounded-2xl p-5 shadow-2xs border border-slate-200/80 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                {{-- Search Vessel --}}
                <div class="md:col-span-3">
                    <label for="vessel-search-input" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        {{ __('Search Vessel') }}
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="vessel-search-input"
                               placeholder="Nama kapal, MMSI, IMO..."
                               class="w-full text-xs pl-8 pr-8 py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                        <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none">🔍</span>
                        <button type="button" id="btn-clear-search" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-xs hidden font-bold">✕</button>
                    </div>
                </div>

                {{-- Start Date --}}
                <div class="md:col-span-2">
                    <label for="filter-start-date" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Start Date') }}
                    </label>
                    <input type="date"
                           id="filter-start-date"
                           value="{{ $startDate }}"
                           class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- End Date --}}
                <div class="md:col-span-2">
                    <label for="filter-end-date" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('End Date (Maks 7 Hari)') }}
                    </label>
                    <input type="date"
                           id="filter-end-date"
                           value="{{ $endDate }}"
                           class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Vessel Type Filter --}}
                <div class="md:col-span-2">
                    <label for="filter-vessel-type" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Vessel Type') }}
                    </label>
                    <select id="filter-vessel-type" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Semua Tipe') }}</option>
                        <option value="Fishing">Fishing</option>
                        <option value="Carrier">Carrier</option>
                        <option value="Support">Support</option>
                        <option value="Bunker">Bunker</option>
                        <option value="Tanker">Tanker</option>
                        <option value="Cargo">Cargo</option>
                        <option value="Passenger">Passenger</option>
                        <option value="Recreational">Recreational</option>
                        <option value="Other">Other</option>
                        <option value="Unknown">Unknown</option>
                    </select>
                </div>

                {{-- Flag Filter --}}
                <div class="md:col-span-1">
                    <label for="filter-flag" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Flag') }}
                    </label>
                    <select id="filter-flag" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        <option value="IDN">IDN</option>
                        <option value="MYS">MYS</option>
                        <option value="THA">THA</option>
                        <option value="VNM">VNM</option>
                        <option value="CHN">CHN</option>
                        <option value="TWN">TWN</option>
                        <option value="PAN">PAN</option>
                        <option value="LBR">LBR</option>
                        <option value="SGP">SGP</option>
                    </select>
                </div>

                {{-- Activity Filter --}}
                <div class="md:col-span-2">
                    <label for="filter-activity" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Activity') }}
                    </label>
                    <select id="filter-activity" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('Semua Aktivitas') }}</option>
                        <option value="Vessel Presence">Vessel Presence</option>
                        <option value="Fishing">Fishing Activity</option>
                        <option value="Encounter">Encounter</option>
                        <option value="Loitering">Loitering</option>
                        <option value="Port Visit">Port Visit</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="md:col-span-1">
                    <label for="filter-status" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Status') }}
                    </label>
                    <select id="filter-status" class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Semua</option>
                        <option value="LIVE">LIVE (&lt;24h)</option>
                        <option value="RECENT">RECENT</option>
                        <option value="STALE">STALE</option>
                    </select>
                </div>
            </div>

            {{-- Actions Bar --}}
            <div class="flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100">
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-400 font-medium">Batas Periode:</span>
                    <button type="button" class="btn-preset-7d px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold transition">
                        📅 7 Hari Terakhir
                    </button>
                    <span id="active-filter-indicator" class="text-[11px] text-slate-500 hidden"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-reset-filter" class="px-3.5 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        {{ __('Reset Filter') }}
                    </button>
                    <button type="button" id="btn-apply-filter" class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center gap-1.5">
                        <span id="btn-apply-spinner" class="hidden">🔄</span>
                        <span>{{ __('Terapkan Filter') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Map and Vessel Detail Layout --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Map Container (8 cols) --}}
            <div class="lg:col-span-8 space-y-3">
                <div class="bg-white rounded-2xl p-3 shadow-2xs border border-slate-200/80 relative">
                    {{-- Map View Canvas --}}
                    <div id="gfw-vessels-map" class="w-full h-[520px] rounded-xl overflow-hidden bg-slate-950 z-0"></div>

                    {{-- Map Status Banner / Notification --}}
                    <div id="map-status-overlay" class="absolute top-6 left-6 z-10 hidden bg-slate-900/90 text-white text-xs px-3.5 py-2 rounded-xl backdrop-blur-xs border border-slate-700 shadow-lg flex items-center gap-2">
                        <span id="map-status-icon">🔄</span>
                        <span id="map-status-text">Memuat peta...</span>
                    </div>

                    {{-- Map Legend --}}
                    <div class="mt-3 px-2 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-600 border-t border-slate-100 pt-2.5">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="font-semibold text-slate-700">Legenda:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                                <span>Fishing</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-purple-500 inline-block"></span>
                                <span>Carrier</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-amber-500 inline-block"></span>
                                <span>Tanker / Bunker</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-sky-500 inline-block"></span>
                                <span>Cargo</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-3 h-3 rounded-full bg-slate-500 inline-block"></span>
                                <span>Other / Unknown</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="w-4 h-1.5 bg-blue-600 rounded-sm inline-block"></span>
                                <span>Batas ZEE (BIG)</span>
                            </div>
                        </div>

                        <div class="text-slate-400">
                            <span>Sistem Proyeksi: <strong>EPSG:4326 (WGS 84)</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vessel Detail Panel (4 cols) --}}
            <div class="lg:col-span-4 space-y-4">
                <div id="vessel-detail-card" class="bg-white rounded-2xl p-5 shadow-2xs border border-slate-200/80 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('VESSEL DETAIL') }}</h3>
                        </div>
                        <span id="detail-badge-type" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
                            Pilih Kapal
                        </span>
                    </div>

                    {{-- Empty State: No Vessel Selected --}}
                    <div id="vessel-detail-empty" class="py-10 text-center space-y-2">
                        <div class="text-4xl text-slate-300">🚢</div>
                        <p class="text-xs font-semibold text-slate-600">Belum ada kapal dipilih</p>
                        <p class="text-[11px] text-slate-400 max-w-[220px] mx-auto">
                            Klik salah satu titik kapal pada peta atau pilih dari tabel untuk melihat spesifikasi detail.
                        </p>
                    </div>

                    {{-- Selected Vessel Information --}}
                    <div id="vessel-detail-content" class="hidden space-y-3.5">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h4 id="detail-name" class="font-extrabold text-sm text-slate-800 break-words">-</h4>
                                    <p id="detail-activity" class="text-xs font-semibold text-indigo-600 mt-0.5">-</p>
                                </div>
                                <span id="detail-flag-badge" class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-white border border-slate-200 text-slate-700 shrink-0">
                                    -
                                </span>
                            </div>
                        </div>

                        {{-- Identity Attributes Table --}}
                        <table class="w-full text-xs">
                            <tbody class="divide-y divide-slate-100">
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium w-1/3">MMSI</td>
                                    <td id="detail-mmsi" class="py-1.5 font-mono font-semibold text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Nomor IMO</td>
                                    <td id="detail-imo" class="py-1.5 font-mono text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Call Sign</td>
                                    <td id="detail-callsign" class="py-1.5 font-mono text-slate-800">: Tidak tersedia</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Flag</td>
                                    <td id="detail-flag" class="py-1.5 font-semibold text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Vessel Type</td>
                                    <td id="detail-vessel-type" class="py-1.5 font-semibold text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Panjang (LOA)</td>
                                    <td id="detail-length" class="py-1.5 text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Tonnage</td>
                                    <td id="detail-tonnage" class="py-1.5 text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Engine Power</td>
                                    <td id="detail-engine-power" class="py-1.5 text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Kecepatan</td>
                                    <td id="detail-speed" class="py-1.5 text-slate-700">: Tidak tersedia</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Course</td>
                                    <td id="detail-course" class="py-1.5 text-slate-700">: Tidak tersedia</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Gear</td>
                                    <td id="detail-gear" class="py-1.5 text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">First Seen</td>
                                    <td id="detail-first-seen" class="py-1.5 text-slate-700">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Observasi Terakhir</td>
                                    <td id="detail-last-seen" class="py-1.5 font-semibold text-slate-800">: -</td>
                                </tr>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Koordinat</td>
                                    <td id="detail-coords" class="py-1.5 font-mono text-slate-700">: -</td>
                                </tr>
                            </tbody>
                                <tr>
                                    <td class="py-1.5 text-slate-500 font-medium">Status Data</td>
                                    <td class="py-1.5">
                                        : <span id="detail-status" class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">STALE</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        {{-- Track Summary Box (Shown when track loaded) --}}
                        <div id="vessel-track-info-box" class="hidden p-3 rounded-xl bg-cyan-50 border border-cyan-200 text-xs text-cyan-950 space-y-1.5">
                            <div class="flex items-center justify-between font-bold text-cyan-900 pb-1 border-b border-cyan-200/60">
                                <span>Histori Pergerakan (Track)</span>
                                <span id="vessel-track-points-count" class="font-mono text-[10px] bg-cyan-200/70 px-1.5 py-0.5 rounded">0 titik</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1 text-[11px]">
                                <div>
                                    <span class="text-cyan-700 text-[10px] block">Pertama Terdeteksi:</span>
                                    <span id="vessel-track-first-seen" class="font-medium text-cyan-900">-</span>
                                </div>
                                <div>
                                    <span class="text-cyan-700 text-[10px] block">Terakhir Terdeteksi:</span>
                                    <span id="vessel-track-last-seen" class="font-medium text-cyan-900">-</span>
                                </div>
                            </div>
                            <div class="text-[10px] text-cyan-800 pt-0.5">
                                <span>Cakupan: <strong id="vessel-track-coverage">-</strong></span>
                            </div>
                        </div>

                        {{-- Activity Intelligence Summary Box --}}
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200/70 space-y-1.5">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500 block">Ringkasan Aktivitas GFW</span>
                            <div class="grid grid-cols-2 gap-1 text-[11px]">
                                <div><span class="text-slate-500">Fishing:</span> <span id="summary-ev-fishing" class="font-bold text-emerald-600 ml-1">N/A</span></div>
                                <div><span class="text-slate-500">Encounter:</span> <span id="summary-ev-encounter" class="font-bold text-amber-600 ml-1">N/A</span></div>
                                <div><span class="text-slate-500">Loitering:</span> <span id="summary-ev-loitering" class="font-bold text-purple-600 ml-1">N/A</span></div>
                                <div><span class="text-slate-500">Port Visit:</span> <span id="summary-ev-port" class="font-bold text-sky-600 ml-1">N/A</span></div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="pt-2 flex items-center gap-2">
                            <button type="button" id="btn-focus-vessel" class="w-1/2 py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5">
                                <span>🎯</span>
                                <span>{{ __('Fokus Peta') }}</span>
                            </button>
                            <button type="button" id="btn-view-track" class="w-1/2 py-2 px-3 rounded-xl bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                <span>🗺️</span>
                                <span>{{ __('Lihat Track') }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Status Box / Notification Messages --}}
                <div id="vessel-error-box" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-rose-700">
                        <span>⚠️</span>
                        <span id="vessel-error-title">Kesalahan API</span>
                    </div>
                    <p id="vessel-error-message" class="text-rose-600 leading-relaxed"></p>
                </div>
            </div>
        </div>

        {{-- Vessel Table Section --}}
        <div id="vessel-list-container" class="bg-white rounded-2xl p-5 shadow-2xs border border-slate-200/80 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2">
                    <span class="text-lg">📊</span>
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">{{ __('Tabel Armada Terdeteksi (ZEE Aceh)') }}</h3>
                        <p class="text-xs text-slate-500 font-medium">Data posisi kapal satelit AIS/VMS dalam Area of Interest ZEE Indonesia Kawasan Aceh.</p>
                    </div>
                </div>

                {{-- Limit Selector --}}
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500">Tampilkan:</span>
                    <select id="table-limit-select" class="text-xs py-1 px-2.5 rounded-lg border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                    <span class="text-slate-500">per halaman</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-semibold uppercase tracking-wider text-[11px] border-y border-slate-200/80">
                            <th class="py-2.5 px-3">#</th>
                            <th class="py-2.5 px-3">Vessel</th>
                            <th class="py-2.5 px-3">MMSI</th>
                            <th class="py-2.5 px-3">IMO</th>
                            <th class="py-2.5 px-3">Flag</th>
                            <th class="py-2.5 px-3">Type</th>
                            <th class="py-2.5 px-3">Last Seen</th>
                            <th class="py-2.5 px-3">Activity</th>
                            <th class="py-2.5 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="vessel-table-body" class="divide-y divide-slate-100 text-slate-700">
                        {{-- Populated dynamically --}}
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <span class="animate-pulse">Memuat data kapal...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Table Pagination Controls --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                <div id="pagination-info" class="text-slate-500 font-medium">
                    Menampilkan 0 dari 0 kapal
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" id="btn-prev-page" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
                        ← Sebelumnya
                    </button>
                    <span id="pagination-page-display" class="px-3 py-1 font-bold text-slate-800">
                        Hal 1
                    </span>
                    <button type="button" id="btn-next-page" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold disabled:opacity-40 disabled:cursor-not-allowed transition">
                        Berikutnya →
                    </button>
                </div>
            </div>
        </div>

        {{-- Activity & Alerts Section (Stage 04 & Stage 05) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            {{-- Recent Activity Feed --}}
            <div class="bg-white rounded-2xl p-5 shadow-2xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Aktivitas Terkini (Recent Activity)') }}</h3>
                    </div>
                    <span id="vessels-activity-feed-count" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">0 events</span>
                </div>
                <div id="vessels-activity-feed-list" class="space-y-2 max-h-[380px] overflow-y-auto pr-1 text-xs">
                    <div class="py-8 text-center text-slate-400">
                        <span class="animate-pulse">Memuat data aktivitas armada...</span>
                    </div>
                </div>
            </div>

            {{-- Alerts & Intelligence Panel --}}
            <div class="bg-white rounded-2xl p-5 shadow-2xs border border-slate-200/80 space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="text-base">🔔</span>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Peringatan Observasi (Data Alerts)') }}</h3>
                    </div>
                    <span id="vessels-alert-count" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-700">0 alerts</span>
                </div>
                <div id="vessels-alerts-list" class="space-y-2 max-h-[380px] overflow-y-auto pr-1 text-xs">
                    <div class="py-8 text-center text-slate-400">
                        <span class="animate-pulse">Memuat data alerts...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MapLibre & Frontend Logic --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // State
            let map = null;
            let vesselsList = [];
            let selectedVessel = null;
            let currentOffset = 0;
            let currentLimit = 50;
            let hasMore = false;
            let totalVesselsCount = 0;
            let isLiveActive = false;
            let liveTimer = null;
            let previousVesselsMap = new Map();

            // DOM elements
            const mapContainer = document.getElementById('gfw-vessels-map');
            const mapStatusOverlay = document.getElementById('map-status-overlay');
            const mapStatusIcon = document.getElementById('map-status-icon');
            const mapStatusText = document.getElementById('map-status-text');

            const statTotal = document.getElementById('stat-total-vessels');
            const statFishing = document.getElementById('stat-fishing-vessels');
            const statOther = document.getElementById('stat-other-vessels');
            const statFlags = document.getElementById('stat-flags');
            const statFlagsPreview = document.getElementById('stat-flags-preview');

            const filterSearch = document.getElementById('vessel-search-input');
            const btnClearSearch = document.getElementById('btn-clear-search');
            const filterStart = document.getElementById('filter-start-date');
            const filterEnd = document.getElementById('filter-end-date');
            const filterType = document.getElementById('filter-vessel-type');
            const filterFlag = document.getElementById('filter-flag');
            const filterActivity = document.getElementById('filter-activity');
            const filterStatus = document.getElementById('filter-status');
            const btnApply = document.getElementById('btn-apply-filter');
            const btnReset = document.getElementById('btn-reset-filter');
            const btnApplySpinner = document.getElementById('btn-apply-spinner');

            const btnToggleLive = document.getElementById('btn-toggle-live-vessels');
            const livePulse = document.getElementById('vessels-live-pulse');
            const metaLastUpdated = document.getElementById('meta-last-updated');
            const metaDataAge = document.getElementById('meta-data-age');
            const metaDeltaContainer = document.getElementById('meta-delta-container');
            const metaDeltaBadge = document.getElementById('meta-delta-badge');
            const refreshErrorNotice = document.getElementById('vessels-refresh-error-notice');
            const btnRetry = document.getElementById('btn-retry-vessels');

            const tableBody = document.getElementById('vessel-table-body');
            const tableLimitSelect = document.getElementById('table-limit-select');
            const btnPrev = document.getElementById('btn-prev-page');
            const btnNext = document.getElementById('btn-next-page');
            const paginationInfo = document.getElementById('pagination-info');
            const paginationPageDisplay = document.getElementById('pagination-page-display');

            const detailEmpty = document.getElementById('vessel-detail-empty');
            const detailContent = document.getElementById('vessel-detail-content');
            const detailBadgeType = document.getElementById('detail-badge-type');
            const detailName = document.getElementById('detail-name');
            const detailActivity = document.getElementById('detail-activity');
            const detailFlagBadge = document.getElementById('detail-flag-badge');
            const detailMmsi = document.getElementById('detail-mmsi');
            const detailImo = document.getElementById('detail-imo');
            const detailFlag = document.getElementById('detail-flag');
            const detailVesselType = document.getElementById('detail-vessel-type');
            const detailLength = document.getElementById('detail-length');
            const detailTonnage = document.getElementById('detail-tonnage');
            const detailEnginePower = document.getElementById('detail-engine-power');
            const detailGear = document.getElementById('detail-gear');
            const detailFirstSeen = document.getElementById('detail-first-seen');
            const detailLastSeen = document.getElementById('detail-last-seen');
            const detailCoords = document.getElementById('detail-coords');
            const detailStatus = document.getElementById('detail-status');
            const btnFocusVessel = document.getElementById('btn-focus-vessel');
            const btnViewTrack = document.getElementById('btn-view-track');

            const trackInfoBox = document.getElementById('vessel-track-info-box');
            const trackPointsCount = document.getElementById('vessel-track-points-count');
            const trackFirstSeen = document.getElementById('vessel-track-first-seen');
            const trackLastSeen = document.getElementById('vessel-track-last-seen');
            const trackCoverage = document.getElementById('vessel-track-coverage');

            const summaryEvFishing = document.getElementById('summary-ev-fishing');
            const summaryEvEncounter = document.getElementById('summary-ev-encounter');
            const summaryEvLoitering = document.getElementById('summary-ev-loitering');
            const summaryEvPort = document.getElementById('summary-ev-port');

            const actFeedList = document.getElementById('vessels-activity-feed-list');
            const actFeedCount = document.getElementById('vessels-activity-feed-count');
            const alertsList = document.getElementById('vessels-alerts-list');
            const alertCount = document.getElementById('vessels-alert-count');

            const errorBox = document.getElementById('vessel-error-box');
            const errorTitle = document.getElementById('vessel-error-title');
            const errorMessage = document.getElementById('vessel-error-message');

            function showStatus(text, icon = '🔄') {
                if (!mapStatusOverlay) return;
                mapStatusText.textContent = text;
                mapStatusIcon.textContent = icon;
                mapStatusOverlay.classList.remove('hidden');
            }

            function hideStatus() {
                if (!mapStatusOverlay) return;
                mapStatusOverlay.classList.add('hidden');
            }

            function showError(msg, title = 'Kesalahan API') {
                if (!errorBox) return;
                errorTitle.textContent = title;
                errorMessage.textContent = msg;
                errorBox.classList.remove('hidden');
            }

            function hideError() {
                if (!errorBox) return;
                errorBox.classList.add('hidden');
            }

            // Initialize MapLibre GL JS
            function initMap() {
                showStatus('Memulai peta GIS...', '🗺️');

                map = new maplibregl.Map({
                    container: 'gfw-vessels-map',
                    style: {
                        version: 8,
                        glyphs: 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf',
                        sources: {
                            'osm-tiles': {
                                type: 'raster',
                                tiles: [
                                    'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
                                ],
                                tileSize: 256,
                                attribution: '&copy; OpenStreetMap contributors'
                            }
                        },
                        layers: [
                            {
                                id: 'osm-tiles-layer',
                                type: 'raster',
                                source: 'osm-tiles',
                                minzoom: 0,
                                maxzoom: 19
                            }
                        ]
                    },
                    center: [95.5, 4.5], // Center of Aceh EEZ
                    zoom: 6,
                    attributionControl: false
                });

                map.addControl(new maplibregl.NavigationControl(), 'top-right');
                map.addControl(new maplibregl.ScaleControl({ maxWidth: 100, unit: 'nautical' }), 'bottom-left');

                map.on('load', () => {
                    hideStatus();
                    loadBigZeePolygon();
                    fetchVesselsData();
                    fetchActivityAndAlerts();
                });
            }

            // Load BIG ZEE Aceh polygon to Map
            async function loadBigZeePolygon() {
                try {
                    // Fetch official ZEE Aceh AOI GeoJSON
                    const res = await fetch('/api/gfw/aoi/zee-indonesia-aceh?geojson=1');
                    const json = await res.json();

                    if (json.success && json.geojson) {
                        if (!map.getSource('big-zee-aoi')) {
                            map.addSource('big-zee-aoi', {
                                type: 'geojson',
                                data: json.geojson
                            });

                            // Fill layer
                            map.addLayer({
                                id: 'big-zee-aoi-fill',
                                type: 'fill',
                                source: 'big-zee-aoi',
                                paint: {
                                    'fill-color': '#0284c7',
                                    'fill-opacity': 0.08
                                }
                            });

                            // Boundary stroke layer
                            map.addLayer({
                                id: 'big-zee-aoi-line',
                                type: 'line',
                                source: 'big-zee-aoi',
                                paint: {
                                    'line-color': '#0284c7',
                                    'line-width': 2.5,
                                    'line-dasharray': [3, 1]
                                }
                            });
                        }

                        // Fit bounds to polygon
                        fitBoundsToGeoJson(json.geojson);
                    }
                } catch (e) {
                    console.warn('Gagal memuat poligon AOI ZEE Aceh:', e);
                }

                // Also load BIG Maritime Boundary line if available
                try {
                    const resBig = await fetch('/api/gis/big/zee/aceh');
                    const jsonBig = await resBig.json();
                    if (jsonBig && jsonBig.type === 'FeatureCollection' && !map.getSource('big-zee-line-src')) {
                        map.addSource('big-zee-line-src', {
                            type: 'geojson',
                            data: jsonBig
                        });
                        map.addLayer({
                            id: 'big-zee-aceh-line',
                            type: 'line',
                            source: 'big-zee-line-src',
                            paint: {
                                'line-color': '#2563eb',
                                'line-width': 2.5
                            }
                        });

                        document.getElementById('toggle-big-zee-aceh')?.addEventListener('change', function () {
                            if (!map) return;
                            const vis = this.checked ? 'visible' : 'none';
                            if (map.getLayer('big-zee-aceh-line')) map.setLayoutProperty('big-zee-aceh-line', 'visibility', vis);
                            if (map.getLayer('big-zee-aoi-fill')) map.setLayoutProperty('big-zee-aoi-fill', 'visibility', vis);
                            if (map.getLayer('big-zee-aoi-line')) map.setLayoutProperty('big-zee-aoi-line', 'visibility', vis);
                        });
                    }
                } catch (e) {
                    // Non-blocking
                }
            }

            function fitBoundsToGeoJson(geojson) {
                if (!map || !geojson) return;
                const bounds = new maplibregl.LngLatBounds();
                const extract = (coords) => {
                    if (typeof coords[0] === 'number') {
                        bounds.extend(coords);
                    } else {
                        coords.forEach(extract);
                    }
                };

                const features = geojson.features || [geojson];
                features.forEach(f => {
                    const geom = f.geometry || f;
                    if (geom && geom.coordinates) {
                        extract(geom.coordinates);
                    }
                });

                if (!bounds.isEmpty()) {
                    map.fitBounds(bounds, { padding: 40, maxZoom: 8.5 });
                }
            }

            // Main API Fetcher for /api/gfw/vessels/zee-indonesia-aceh
            async function fetchVesselsData() {
                hideError();
                showStatus('Memuat data armada GFW di ZEE Aceh...', '🚢');
                btnApplySpinner.classList.remove('hidden');

                const startDate = filterStart.value;
                const endDate = filterEnd.value;
                const vType = filterType.value;
                const flag = filterFlag.value;
                const activity = filterActivity.value;
                const search = filterSearch.value.trim();

                const params = new URLSearchParams({
                    start: startDate,
                    end: endDate,
                    limit: currentLimit,
                    offset: currentOffset,
                });

                if (vType) params.append('vessel_type', vType);
                if (flag) params.append('flag', flag);
                if (activity) params.append('activity', activity);
                if (search) params.append('search', search);

                const endpoint = `/api/gfw/vessels/zee-indonesia-aceh?${params.toString()}`;

                try {
                    const response = await fetch(endpoint);
                    const json = await response.json();

                    btnApplySpinner.classList.add('hidden');
                    hideStatus();

                    if (!response.ok || !json.success) {
                        let errTitle = 'GFW API unavailable';
                        if (response.status === 404 || (json.message && json.message.includes('AOI'))) {
                            errTitle = 'AOI unavailable';
                        }
                        // Resilience: if we already have vessels, don't wipe them!
                        if (vesselsList.length > 0) {
                            if (refreshErrorNotice) refreshErrorNotice.classList.remove('hidden');
                        } else {
                            showError(json.message || 'Gagal memuat data observasi kapal dari GFW.', errTitle);
                            renderEmptyTable('Terjadi kesalahan saat memuat data: ' + (json.message || 'API error'));
                        }
                        return;
                    }

                    // Success: hide error notice
                    if (refreshErrorNotice) refreshErrorNotice.classList.add('hidden');

                    // Delta detection
                    detectVesselDeltas(json.vessels || []);

                    // Update summary KPI
                    totalVesselsCount = json.summary?.total_vessels ?? 0;
                    statTotal.textContent = totalVesselsCount.toLocaleString();
                    statFishing.textContent = (json.summary?.fishing_vessels ?? 0).toLocaleString();
                    statOther.textContent = (json.summary?.other_vessels ?? 0).toLocaleString();

                    // Update live timing metadata
                    if (metaLastUpdated) metaLastUpdated.textContent = formatDate(json.last_updated);
                    if (metaDataAge) metaDataAge.textContent = formatAge(json.data_age_seconds);

                    // Calculate distinct flags from vessels
                    vesselsList = json.vessels || [];
                    hasMore = json.pagination?.has_more ?? false;

                    const uniqueFlags = [...new Set(vesselsList.map(v => v.flag).filter(Boolean))];
                    statFlags.textContent = uniqueFlags.length;
                    statFlagsPreview.textContent = uniqueFlags.length ? uniqueFlags.slice(0, 4).join(', ') : 'Negara bendera';

                    // Apply status filter locally if selected
                    let displayVessels = vesselsList;
                    if (filterStatus && filterStatus.value) {
                        displayVessels = vesselsList.filter(v => (v.status || '').toUpperCase() === filterStatus.value);
                    }

                    // Update map layers
                    updateMapVessels(displayVessels);

                    // Update table
                    renderVesselsTable(displayVessels);

                    // Update pagination controls
                    updatePaginationUI();

                } catch (err) {
                    btnApplySpinner.classList.add('hidden');
                    hideStatus();
                    if (vesselsList.length > 0) {
                        if (refreshErrorNotice) refreshErrorNotice.classList.remove('hidden');
                    } else {
                        showError('Koneksi ke server gagal: ' + err.message, 'GFW API unavailable');
                        renderEmptyTable('Koneksi terputus saat memuat data armada.');
                    }
                }
            }

            // Delta detection between successive queries
            function detectVesselDesselDeltas(incoming) {
                if (previousVesselsMap.size > 0) {
                    let newVessels = 0;
                    let movedVessels = 0;
                    const incomingIds = new Set();

                    incoming.forEach(v => {
                        incomingIds.add(String(v.id));
                        if (!previousVesselsMap.has(String(v.id))) {
                            newVessels++;
                        } else {
                            const prev = previousVesselsMap.get(String(v.id));
                            if (prev.lat !== v.lat || prev.lon !== v.lon) {
                                movedVessels++;
                            }
                        }
                    });

                    let notDetectedCount = 0;
                    previousVesselsMap.forEach((_, id) => {
                        if (!incomingIds.has(id)) notDetectedCount++;
                    });

                    if (newVessels > 0 || movedVessels > 0 || notDetectedCount > 0) {
                        const parts = [];
                        if (newVessels > 0) parts.push(`+${newVessels} kapal baru`);
                        if (movedVessels > 0) parts.push(`${movedVessels} posisi diperbarui`);
                        if (notDetectedCount > 0) parts.push(`${notDetectedCount} tidak terdeteksi pada snapshot terkini`);

                        if (metaDeltaBadge) {
                            metaDeltaBadge.textContent = parts.join(' • ');
                            metaDeltaContainer.classList.remove('hidden');
                        }
                    }
                }

                previousVesselsMap.clear();
                incoming.forEach(v => previousVesselsMap.set(String(v.id), v));
            }
            const detectVesselDeltas = detectVesselDesselDeltas;

            // Fetch Recent Activities and Alerts
            async function fetchActivityAndAlerts() {
                try {
                    const res = await fetch('/api/gfw/dashboard?limit=50');
                    const json = await res.json();
                    if (json.success) {
                        renderActivityTimeline(json.activity_feed || []);
                        renderAlertsList(json.alerts || []);
                    }
                } catch (e) {
                    console.warn('Gagal memuat feed aktivitas:', e);
                }
            }

            function renderActivityTimeline(activities) {
                if (!actFeedList) return;
                actFeedCount.textContent = `${activities.length} events`;

                if (activities.length === 0) {
                    actFeedList.innerHTML = '<div class="py-6 text-center text-slate-400 text-xs">Tidak ada aktivitas tercatat.</div>';
                    return;
                }

                actFeedList.innerHTML = '';
                activities.slice(0, 15).forEach(act => {
                    const item = document.createElement('div');
                    item.className = 'p-2 rounded-xl bg-slate-50 border border-slate-100 hover:border-indigo-200 transition cursor-pointer space-y-0.5';

                    let badgeColor = 'bg-slate-100 text-slate-700';
                    if (act.activity.includes('Fishing')) badgeColor = 'bg-emerald-100 text-emerald-800';
                    else if (act.activity.includes('Encounter')) badgeColor = 'bg-amber-100 text-amber-800';
                    else if (act.activity.includes('Loitering')) badgeColor = 'bg-purple-100 text-purple-800';
                    else if (act.activity.includes('Port')) badgeColor = 'bg-sky-100 text-sky-800';

                    item.innerHTML = `
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-800">${escapeHtml(act.vessel)}</span>
                            <span class="font-mono text-[10px] text-slate-400">${formatDate(act.time)}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="px-1.5 py-0.5 rounded font-bold ${badgeColor}">${escapeHtml(act.activity)}</span>
                            <span class="text-slate-500 font-mono">${escapeHtml(act.location)}</span>
                        </div>
                    `;

                    item.addEventListener('click', () => {
                        if (act.lat !== null && act.lon !== null && map) {
                            map.flyTo({ center: [act.lon, act.lat], zoom: 10 });
                        }
                        if (act.vessel_id) selectVesselById(act.vessel_id);
                    });

                    actFeedList.appendChild(item);
                });
            }

            function renderAlertsList(alerts) {
                if (!alertsList) return;
                alertCount.textContent = `${alerts.length} alerts`;

                if (alerts.length === 0) {
                    alertsList.innerHTML = '<div class="py-6 text-center text-slate-400 text-xs">Tidak ada alert terdeteksi.</div>';
                    return;
                }

                alertsList.innerHTML = '';
                alerts.slice(0, 12).forEach(al => {
                    const item = document.createElement('div');
                    item.className = 'p-2 rounded-xl bg-amber-50/50 border border-amber-200/60 hover:border-amber-300 transition cursor-pointer space-y-0.5';

                    item.innerHTML = `
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-800 flex items-center gap-1">
                                <span>🔔</span>
                                <span>${escapeHtml(al.title)}</span>
                            </span>
                            <span class="font-mono text-[10px] text-slate-400">${formatDate(al.time)}</span>
                        </div>
                        <p class="text-[10px] text-slate-600 leading-tight">${escapeHtml(al.description)}</p>
                    `;

                    item.addEventListener('click', () => {
                        if (al.lat !== null && al.lon !== null && map) {
                            map.flyTo({ center: [al.lon, al.lat], zoom: 10 });
                        }
                        if (al.vessel_id) selectVesselById(al.vessel_id);
                    });

                    alertsList.appendChild(item);
                });
            }

            // Update MapLibre GeoJSON Points and Clustering
            function updateMapVessels(vessels) {
                if (!map) return;

                const geoJsonPoints = {
                    type: 'FeatureCollection',
                    features: vessels
                        .filter(v => v.lat !== null && v.lon !== null)
                        .map(v => ({
                            type: 'Feature',
                            geometry: {
                                type: 'Point',
                                coordinates: [v.lon, v.lat]
                            },
                            properties: {
                                id: v.id,
                                name: v.name || 'Unknown Vessel',
                                mmsi: v.mmsi || 'Not available',
                                imo: v.imo || 'Not available',
                                flag: v.flag || 'Not available',
                                vessel_type: v.vessel_type || 'Unknown',
                                status: v.status || 'STALE',
                                length: v.length !== null ? `${v.length} m` : 'Not available',
                                tonnage: v.tonnage !== null ? `${v.tonnage} GT` : 'Not available',
                                engine_power: v.engine_power !== null ? `${v.engine_power} kW` : 'Not available',
                                gear: v.gear || 'Not available',
                                first_seen: v.first_seen ? formatDate(v.first_seen) : 'Not available',
                                last_seen: v.last_seen ? formatDate(v.last_seen) : 'Not available',
                                activity: v.activity || 'Vessel Presence',
                                lat: v.lat,
                                lon: v.lon
                            }
                        }))
                };

                const sourceId = 'gfw-vessels-data';

                if (map.getSource(sourceId)) {
                    map.getSource(sourceId).setData(geoJsonPoints);
                } else {
                    map.addSource(sourceId, {
                        type: 'geojson',
                        data: geoJsonPoints,
                        cluster: true,
                        clusterMaxZoom: 14,
                        clusterRadius: 50
                    });

                    // Cluster circles
                    map.addLayer({
                        id: 'clusters',
                        type: 'circle',
                        source: sourceId,
                        filter: ['has', 'point_count'],
                        paint: {
                            'circle-color': [
                                'step',
                                ['get', 'point_count'],
                                '#6366f1',
                                10,
                                '#4f46e5',
                                30,
                                '#4338ca'
                            ],
                            'circle-radius': [
                                'step',
                                ['get', 'point_count'],
                                18,
                                10,
                                24,
                                30,
                                30
                            ],
                            'circle-stroke-width': 2,
                            'circle-stroke-color': '#ffffff'
                        }
                    });

                    // Cluster count label
                    map.addLayer({
                        id: 'cluster-count',
                        type: 'symbol',
                        source: sourceId,
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-font': ['Open Sans Semibold'],
                            'text-size': 12
                        },
                        paint: {
                            'text-color': '#ffffff'
                        }
                    });

                    // Unclustered vessel circle point
                    map.addLayer({
                        id: 'unclustered-point',
                        type: 'circle',
                        source: sourceId,
                        filter: ['!', ['has', 'point_count']],
                        paint: {
                            'circle-color': [
                                'match',
                                ['get', 'vessel_type'],
                                'Fishing', '#10b981',
                                'Carrier', '#8b5cf6',
                                'Tanker', '#f59e0b',
                                'Bunker', '#f59e0b',
                                'Cargo', '#3b82f6',
                                'Passenger', '#ec4899',
                                'Support', '#06b6d4',
                                /* other/unknown */ '#64748b'
                            ],
                            'circle-radius': 7,
                            'circle-stroke-width': 2,
                            'circle-stroke-color': '#ffffff'
                        }
                    });

                    // Click on cluster
                    map.on('click', 'clusters', (e) => {
                        const features = map.queryRenderedFeatures(e.point, { layers: ['clusters'] });
                        const clusterId = features[0].properties.cluster_id;
                        map.getSource(sourceId).getClusterExpansionZoom(clusterId, (err, zoom) => {
                            if (err) return;
                            map.easeTo({
                                center: features[0].geometry.coordinates,
                                zoom: zoom
                            });
                        });
                    });

                    // Click on individual vessel point
                    map.on('click', 'unclustered-point', (e) => {
                        const feature = e.features[0];
                        const coords = feature.geometry.coordinates.slice();
                        const p = feature.properties;

                        new maplibregl.Popup({ offset: 15 })
                            .setLngLat(coords)
                            .setHTML(`
                                <div class="text-xs p-1 space-y-1.5 font-sans">
                                    <div class="font-bold text-slate-800 text-sm border-b border-slate-200 pb-1 flex items-center justify-between">
                                        <span>${p.name}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-100 text-slate-600 font-mono">${p.flag}</span>
                                    </div>
                                    <div class="text-[11px] space-y-0.5">
                                        <div><span class="text-slate-400">MMSI:</span> <strong>${p.mmsi}</strong></div>
                                        <div><span class="text-slate-400">IMO:</span> <strong>${p.imo}</strong></div>
                                        <div><span class="text-slate-400">Type:</span> <strong>${p.vessel_type}</strong></div>
                                        <div><span class="text-slate-400">Activity:</span> <strong class="text-indigo-600">${p.activity}</strong></div>
                                        <div><span class="text-slate-400">Status:</span> <strong>${p.status}</strong></div>
                                        <div><span class="text-slate-400">Last Seen:</span> ${p.last_seen}</div>
                                        ${p.length !== 'Not available' ? `<div><span class="text-slate-400">Length:</span> ${p.length}</div>` : ''}
                                        ${p.tonnage !== 'Not available' ? `<div><span class="text-slate-400">Tonnage:</span> ${p.tonnage}</div>` : ''}
                                    </div>
                                </div>
                            `)
                            .addTo(map);

                        selectVesselById(p.id);
                    });

                    // Hover cursor effects
                    map.on('mouseenter', 'clusters', () => { map.getCanvas().style.cursor = 'pointer'; });
                    map.on('mouseleave', 'clusters', () => { map.getCanvas().style.cursor = ''; });
                    map.on('mouseenter', 'unclustered-point', () => { map.getCanvas().style.cursor = 'pointer'; });
                    map.on('mouseleave', 'unclustered-point', () => { map.getCanvas().style.cursor = ''; });
                }
            }

            // Render Table Rows
            function renderVesselsTable(vessels) {
                if (!vessels || vessels.length === 0) {
                    renderEmptyTable('Tidak ada kapal terdeteksi pada filter ini (0 vessels).');
                    return;
                }

                tableBody.innerHTML = '';
                vessels.forEach((v, idx) => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-slate-50/80 transition cursor-pointer border-b border-slate-100';
                    row.dataset.vesselId = v.id;

                    const rowNum = currentOffset + idx + 1;
                    const vName = v.name || 'Unnamed Vessel';
                    const vMmsi = v.mmsi || '-';
                    const vImo = v.imo || '-';
                    const vFlag = v.flag || '-';
                    const vType = v.vessel_type || 'Unknown';
                    const vLastSeen = v.last_seen ? formatDate(v.last_seen) : 'Not available';
                    const vActivity = v.activity || 'Vessel Presence';
                    const vStatus = v.status || 'STALE';

                    // Activity badge color
                    let actBadgeClass = 'bg-slate-100 text-slate-700';
                    if (vActivity.includes('Fishing')) actBadgeClass = 'bg-emerald-100 text-emerald-800';
                    else if (vActivity.includes('Encounter')) actBadgeClass = 'bg-amber-100 text-amber-800';
                    else if (vActivity.includes('Loitering')) actBadgeClass = 'bg-purple-100 text-purple-800';
                    else if (vActivity.includes('Port')) actBadgeClass = 'bg-sky-100 text-sky-800';

                    let statusClass = 'bg-slate-100 text-slate-600';
                    if (vStatus === 'LIVE') statusClass = 'bg-emerald-100 text-emerald-800';
                    else if (vStatus === 'RECENT') statusClass = 'bg-sky-100 text-sky-800';

                    row.innerHTML = `
                        <td class="py-2.5 px-3 text-slate-400 font-mono text-[11px]">${rowNum}</td>
                        <td class="py-2.5 px-3 font-bold text-slate-800">${escapeHtml(vName)}</td>
                        <td class="py-2.5 px-3 font-mono text-slate-600">${escapeHtml(vMmsi)}</td>
                        <td class="py-2.5 px-3 font-mono text-slate-500">${escapeHtml(vImo)}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-700">${escapeHtml(vFlag)}</td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700">
                                ${escapeHtml(vType)}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-slate-600">${escapeHtml(vLastSeen)}</td>
                        <td class="py-2.5 px-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold ${actBadgeClass}">
                                ${escapeHtml(vActivity)}
                            </span>
                        </td>
                        <td class="py-2.5 px-3 text-right">
                            <button type="button" class="btn-inspect px-2 py-1 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold text-[11px] transition">
                                Detail ↗
                            </button>
                        </td>
                    `;

                    row.addEventListener('click', () => {
                        selectVessel(v);
                        highlightTableRow(row);
                    });

                    tableBody.appendChild(row);
                });
            }

            function renderEmptyTable(message) {
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400 space-y-2">
                            <div class="text-3xl text-slate-300">🔍</div>
                            <p class="font-medium text-xs text-slate-500">${escapeHtml(message)}</p>
                        </td>
                    </tr>
                `;
            }

            function highlightTableRow(selectedRow) {
                tableBody.querySelectorAll('tr').forEach(r => r.classList.remove('bg-indigo-50/60'));
                if (selectedRow) selectedRow.classList.add('bg-indigo-50/60');
            }

            // Select Vessel into Detail Panel
            function selectVesselById(id) {
                const vessel = vesselsList.find(v => String(v.id) === String(id));
                if (vessel) {
                    selectVessel(vessel);
                }
            }

            function selectVessel(v) {
                selectedVessel = v;

                detailEmpty.classList.add('hidden');
                detailContent.classList.remove('hidden');

                detailBadgeType.textContent = v.vessel_type || 'Unknown';
                detailName.textContent = v.name || 'Unnamed Vessel';
                detailActivity.textContent = v.activity || 'Vessel Presence';
                detailFlagBadge.textContent = v.flag || 'N/A';

                detailMmsi.textContent = ': ' + (v.mmsi || 'Not available');
                detailImo.textContent = ': ' + (v.imo || 'Not available');
                detailFlag.textContent = ': ' + (v.flag || 'Not available');
                detailVesselType.textContent = ': ' + (v.vessel_type || 'Not available');
                detailLength.textContent = ': ' + (v.length !== null ? `${v.length} m` : 'Not available');
                detailTonnage.textContent = ': ' + (v.tonnage !== null ? `${v.tonnage} GT` : 'Not available');
                detailEnginePower.textContent = ': ' + (v.engine_power !== null ? `${v.engine_power} kW` : 'Not available');
                detailGear.textContent = ': ' + (v.gear || 'Not available');
                detailFirstSeen.textContent = ': ' + (v.first_seen ? formatDate(v.first_seen) : 'Not available');
                detailLastSeen.textContent = ': ' + (v.last_seen ? formatDate(v.last_seen) : 'Not available');

                if (detailStatus) {
                    const st = v.status || 'STALE';
                    detailStatus.textContent = st;
                    detailStatus.className = 'inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold ' + 
                        (st === 'LIVE' ? 'bg-emerald-100 text-emerald-800' : (st === 'RECENT' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700'));
                }

                if (v.lat !== null && v.lon !== null) {
                    detailCoords.textContent = `: ${v.lat.toFixed(4)}, ${v.lon.toFixed(4)}`;
                    btnFocusVessel.classList.remove('hidden');
                } else {
                    detailCoords.textContent = ': Not available';
                    btnFocusVessel.classList.add('hidden');
                }

                // Reset track info box
                if (trackInfoBox) trackInfoBox.classList.add('hidden');
            }

            // Focus on Vessel on map
            btnFocusVessel.addEventListener('click', () => {
                if (selectedVessel && selectedVessel.lat !== null && selectedVessel.lon !== null && map) {
                    map.flyTo({
                        center: [selectedVessel.lon, selectedVessel.lat],
                        zoom: 11,
                        essential: true
                    });
                }
            });

            // View Track on MapLibre
            btnViewTrack.addEventListener('click', async () => {
                if (!selectedVessel) return;
                btnViewTrack.textContent = 'Memuat Track...';
                btnViewTrack.disabled = true;

                const startDate = filterStart.value;
                const endDate = filterEnd.value;

                try {
                    const res = await fetch(`/api/gfw/vessels/${selectedVessel.id}/track?start_date=${startDate}&end_date=${endDate}`);
                    const json = await res.json();
                    btnViewTrack.disabled = false;
                    btnViewTrack.textContent = 'Lihat Track';

                    if (!json.success || !json.track) {
                        alert(json.message || 'Gagal mengambil data track.');
                        return;
                    }

                    if (trackInfoBox) {
                        trackInfoBox.classList.remove('hidden');
                        trackPointsCount.textContent = `${json.points_count} titik`;
                        trackFirstSeen.textContent = formatDate(json.first_detected);
                        trackLastSeen.textContent = formatDate(json.last_detected);
                        trackCoverage.textContent = json.approximate_coverage;
                    }

                    // Add/update track layers on MapLibre
                    if (map.getSource('vessel-track-src')) {
                        map.getSource('vessel-track-src').setData(json.track);
                    } else {
                        map.addSource('vessel-track-src', {
                            type: 'geojson',
                            data: json.track
                        });

                        map.addLayer({
                            id: 'vessel-track-line',
                            type: 'line',
                            source: 'vessel-track-src',
                            filter: ['==', '$type', 'LineString'],
                            paint: {
                                'line-color': '#06b6d4',
                                'line-width': 3,
                                'line-opacity': 0.9
                            }
                        });

                        map.addLayer({
                            id: 'vessel-track-points',
                            type: 'circle',
                            source: 'vessel-track-src',
                            filter: ['==', '$type', 'Point'],
                            paint: {
                                'circle-radius': 4,
                                'circle-color': '#22d3ee',
                                'circle-stroke-width': 1.5,
                                'circle-stroke-color': '#0f172a'
                            }
                        });
                    }

                    // Fit to track bounds
                    fitBoundsToGeoJson(json.track);

                } catch (e) {
                    btnViewTrack.disabled = false;
                    btnViewTrack.textContent = 'Lihat Track';
                    alert('Gagal memuat track: ' + e.message);
                }
            });

            // Live Monitoring Toggle (60s timer)
            btnToggleLive.addEventListener('click', () => {
                isLiveActive = !isLiveActive;
                if (isLiveActive) {
                    btnToggleLive.textContent = 'ON (60s)';
                    btnToggleLive.className = 'px-2.5 py-0.5 rounded text-[11px] font-bold bg-emerald-600 text-white transition';
                    livePulse.className = 'w-2 h-2 rounded-full bg-emerald-400 inline-block animate-ping';
                    if (liveTimer) {
                        clearInterval(liveTimer);
                        liveTimer = null;
                    }
                    liveTimer = setInterval(fetchVesselsData, 60000);
                } else {
                    btnToggleLive.textContent = 'OFF';
                    btnToggleLive.className = 'px-2.5 py-0.5 rounded text-[11px] font-bold bg-slate-700 text-slate-300 hover:bg-slate-600 transition';
                    livePulse.className = 'w-2 h-2 rounded-full bg-slate-500 inline-block';
                    if (liveTimer) {
                        clearInterval(liveTimer);
                        liveTimer = null;
                    }
                }
            });

            btnRetry?.addEventListener('click', fetchVesselsData);

            // Update Pagination UI
            function updatePaginationUI() {
                const currentPage = Math.floor(currentOffset / currentLimit) + 1;
                const totalPages = Math.ceil(totalVesselsCount / currentLimit) || 1;

                const startItem = totalVesselsCount > 0 ? currentOffset + 1 : 0;
                const endItem = Math.min(currentOffset + vesselsList.length, totalVesselsCount);

                paginationInfo.textContent = `Menampilkan ${startItem} - ${endItem} dari ${totalVesselsCount} kapal`;
                paginationPageDisplay.textContent = `Hal ${currentPage} / ${totalPages}`;

                btnPrev.disabled = currentOffset <= 0;
                btnNext.disabled = !hasMore && (currentOffset + currentLimit >= totalVesselsCount);
            }

            // Event Listeners: Pagination
            btnPrev.addEventListener('click', () => {
                if (currentOffset > 0) {
                    currentOffset = Math.max(0, currentOffset - currentLimit);
                    fetchVesselsData();
                }
            });

            btnNext.addEventListener('click', () => {
                if (hasMore || (currentOffset + currentLimit < totalVesselsCount)) {
                    currentOffset += currentLimit;
                    fetchVesselsData();
                }
            });

            tableLimitSelect.addEventListener('change', () => {
                currentLimit = parseInt(tableLimitSelect.value, 10);
                currentOffset = 0;
                fetchVesselsData();
            });

            // Event Listeners: Filters & Search
            btnApply.addEventListener('click', () => {
                currentOffset = 0;
                fetchVesselsData();
            });

            btnReset.addEventListener('click', () => {
                filterSearch.value = '';
                btnClearSearch.classList.add('hidden');
                filterType.value = '';
                filterFlag.value = '';
                filterActivity.value = '';
                if (filterStatus) filterStatus.value = '';
                currentOffset = 0;
                fetchVesselsData();
            });

            filterSearch.addEventListener('input', () => {
                if (filterSearch.value.trim().length > 0) {
                    btnClearSearch.classList.remove('hidden');
                } else {
                    btnClearSearch.classList.add('hidden');
                }
            });

            filterSearch.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    currentOffset = 0;
                    fetchVesselsData();
                }
            });

            btnClearSearch.addEventListener('click', () => {
                filterSearch.value = '';
                btnClearSearch.classList.add('hidden');
                currentOffset = 0;
                fetchVesselsData();
            });

            // Preset 7 Hari button
            document.querySelectorAll('.btn-preset-7d').forEach(btn => {
                btn.addEventListener('click', () => {
                    const today = new Date();
                    const priorDate = new Date();
                    priorDate.setDate(today.getDate() - 6);

                    filterEnd.value = today.toISOString().split('T')[0];
                    filterStart.value = priorDate.toISOString().split('T')[0];
                    currentOffset = 0;
                    fetchVesselsData();
                });
            });

            // Helper functions
            function formatDate(isoStr) {
                if (!isoStr) return '-';
                try {
                    const d = new Date(isoStr);
                    return d.toLocaleString('id-ID', {
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        timeZone: 'Asia/Jakarta'
                    }) + ' WIB';
                } catch (e) {
                    return isoStr;
                }
            }

            function formatAge(seconds) {
                if (seconds === null || seconds === undefined) return '-';
                if (seconds < 60) return `${seconds} detik yang lalu`;
                if (seconds < 3600) return `${Math.floor(seconds / 60)} menit yang lalu`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
                return `${Math.floor(seconds / 86400)} hari yang lalu`;
            }

            function escapeHtml(str) {
                if (str === null || str === undefined) return '';
                return String(str)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Start initialization
            initMap();
        });
    </script>
</x-app-layout>
