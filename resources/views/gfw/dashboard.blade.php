<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🛰️</span>
            <span>{{ __('GFW Operational Dashboard (ZEE Indonesia — Kawasan Aceh)') }}</span>
        </div>
    </x-slot>

    {{-- MapLibre GL JS CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" crossorigin=""/>
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" crossorigin=""></script>

    {{-- Dark Mode Page Styles for GFW Dashboard --}}
    <style>
        /* Force dark background for page canvas */
        .main-content {
            background-color: #0b1120 !important;
        }

        /* MapLibre controls dark theme styling */
        .maplibregl-ctrl-group {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4) !important;
        }
        .maplibregl-ctrl-group button {
            border-bottom: 1px solid #334155 !important;
            background-color: transparent !important;
        }
        .maplibregl-ctrl-group button:hover {
            background-color: #334155 !important;
        }
        .maplibregl-ctrl-group button .maplibregl-ctrl-icon {
            filter: invert(1) brightness(1.5) !important;
        }
        .maplibregl-ctrl-scale {
            background-color: rgba(15, 23, 42, 0.85) !important;
            color: #cbd5e1 !important;
            border-color: #475569 !important;
            font-family: monospace !important;
            font-size: 10px !important;
        }

        /* MapLibre dark popup container */
        .maplibregl-popup-content {
            background-color: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.875rem !important;
            box-shadow: 0 12px 30px -4px rgba(0, 0, 0, 0.7) !important;
            padding: 0.85rem !important;
        }
        .maplibregl-popup-anchor-top .maplibregl-popup-tip { border-bottom-color: #0f172a !important; }
        .maplibregl-popup-anchor-bottom .maplibregl-popup-tip { border-top-color: #0f172a !important; }
        .maplibregl-popup-anchor-left .maplibregl-popup-tip { border-right-color: #0f172a !important; }
        .maplibregl-popup-anchor-right .maplibregl-popup-tip { border-left-color: #0f172a !important; }
        .maplibregl-popup-close-button {
            color: #94a3b8 !important;
            font-size: 16px !important;
            padding: 4px 8px !important;
            line-height: 1 !important;
        }
        .maplibregl-popup-close-button:hover {
            color: #ffffff !important;
            background-color: transparent !important;
        }

        /* Custom scrollbar for dark feed and lists */
        #activity-feed-list::-webkit-scrollbar,
        #alerts-feed-list::-webkit-scrollbar {
            width: 6px;
        }
        #activity-feed-list::-webkit-scrollbar-track,
        #alerts-feed-list::-webkit-scrollbar-track {
            background: rgba(30, 41, 59, 0.5);
            border-radius: 4px;
        }
        #activity-feed-list::-webkit-scrollbar-thumb,
        #alerts-feed-list::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.8);
            border-radius: 4px;
        }
        #activity-feed-list::-webkit-scrollbar-thumb:hover,
        #alerts-feed-list::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 1);
        }
    </style>

    <div class="space-y-6 text-slate-100">
        {{-- Provenance Banner & Live Controls --}}
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-indigo-900/50">
            <div class="absolute right-4 -bottom-6 text-9xl opacity-5 pointer-events-none select-none">🌐</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-3xl space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-semibold">
                            <span>🏛️</span>
                            <span>Batas ZEE: BIG Layer 10</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 text-[11px] font-semibold">
                            <span>🛰️</span>
                            <span>Observasi kapal dan aktivitas: Global Fishing Watch</span>
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[11px] font-semibold font-mono">
                            EPSG:4326
                        </span>
                    </div>

                    <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white flex flex-wrap items-center gap-2">
                        <span>GFW MONITORING DASHBOARD</span>
                        <span class="text-indigo-400 font-light">|</span>
                        <span class="text-indigo-200 text-base font-semibold">ZEE Indonesia — Kawasan Aceh (BIG Layer 10)</span>
                    </h2>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                        Pusat kendali operasional pemantauan terpadu maritim di wilayah ZEE Aceh. Mengintegrasikan observasi kapal terdeteksi, lintasan pergerakan (<em>Vessel Track</em>), indikasi penangkapan (<em>Fishing Activity</em>), pola menunggu (<em>Loitering</em>), dan sistem <em>Monitoring Alert</em> berbasis bukti spasial.
                    </p>
                </div>

                {{-- Live Monitoring & Timing Card --}}
                <div class="px-4 py-3 rounded-xl bg-slate-900/90 backdrop-blur-xs border border-indigo-500/30 text-xs max-w-sm shrink-0 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold text-slate-300 text-xs flex items-center gap-1.5">
                            <span id="live-pulse-indicator" class="w-2.5 h-2.5 rounded-full bg-slate-500 inline-block"></span>
                            <span>Pemantauan Operasional</span>
                        </span>
                        <button type="button" id="btn-refresh-dashboard" class="px-2.5 py-1 rounded-lg text-xs font-bold transition bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs flex items-center gap-1">
                            <span id="btn-toggle-live" class="sr-only">Toggle Live</span>
                            <span>🔄</span>
                            <span>Refresh Data</span>
                        </button>
                    </div>

                    <div class="space-y-1 text-[11px] border-t border-slate-800 pt-2">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Status Pembaruan:</span>
                            <span id="live-status-text" class="text-slate-200 font-mono">Siap</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Terakhir Diperbarui:</span>
                            <span id="meta-last-updated" class="text-indigo-300 font-mono">-</span>
                        </div>
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Usia Data Sumber:</span>
                            <span id="meta-data-age" class="text-slate-200 font-mono">-</span>
                        </div>
                    </div>

                    <div id="meta-delta-container" class="hidden pt-1.5 border-t border-slate-800">
                        <span id="meta-delta-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-950 text-indigo-300 border border-indigo-700 block text-center">
                            Memeriksa perubahan dataset...
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fallback / Warning Error Notice (Dark Theme) --}}
        <div id="gfw-dashboard-error-notice" class="hidden p-4 rounded-2xl bg-amber-950/40 border border-amber-800/80 text-amber-200 text-xs shadow-sm space-y-2">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <span class="text-2xl shrink-0 mt-0.5">⚠️</span>
                    <div class="space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-bold text-amber-200 text-sm" id="error-notice-title">Gagal memperbarui data dari GFW API</span>
                            <span id="dashboard-notice-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-900/80 text-amber-200 border border-amber-700/80 font-mono">
                                DATA TERAKHIR TERSEDIA
                            </span>
                        </div>
                        <p class="text-xs text-amber-300/90 font-medium leading-relaxed" id="error-notice-detail">
                            Menampilkan dataset berhasil terakhir. Data operasional tetap aman dan tidak direset.
                        </p>
                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 pt-1 border-t border-amber-800/50 text-[11px] text-amber-200">
                            <div>
                                <span class="text-amber-400">Data terakhir berhasil diperbarui:</span>
                                <strong id="dashboard-notice-last-updated" class="font-mono ml-1 text-amber-100">-</strong>
                                <span id="dashboard-notice-data-age" class="text-amber-300/80 text-[10px] font-medium ml-1"></span>
                            </div>
                            <div>
                                <span class="text-amber-400">Status:</span>
                                <strong class="text-amber-200 ml-1 uppercase font-bold">DATA TERAKHIR TERSEDIA</strong>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="btn-retry-fetch" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold text-xs transition shrink-0 shadow-xs flex items-center gap-1.5 self-end sm:self-center">
                    <span>🔄</span>
                    <span>Coba Lagi</span>
                </button>
            </div>
        </div>

        {{-- KPI Row: 8 Summary Cards (Dark Mode) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            {{-- 1. Total GFW Vessels --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block truncate">Total Vessels</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-detected-vessels" class="text-lg font-black text-white">...</h3>
                    <span class="text-xs">🚢</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Armada di ZEE</p>
            </div>

            {{-- 2. Active / Observed Vessels --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400 block truncate">Active Vessels</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-live-recent" class="text-lg font-black text-emerald-400">...</h3>
                    <span class="text-xs">🟢</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Observasi &lt; 72j</p>
            </div>

            {{-- 3. Fishing Events --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-400 block truncate">Fishing Events</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-fishing-activity" class="text-lg font-black text-cyan-400">...</h3>
                    <span class="text-xs">🎣</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Aktivitas tangkap</p>
            </div>

            {{-- 4. Track Points --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-400 block truncate">Track Points</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-track-points" class="text-lg font-black text-blue-400">...</h3>
                    <span class="text-xs">📍</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Titik posisi kapal</p>
            </div>

            {{-- 5. Loitering --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-400 block truncate">Loitering</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-loitering" class="text-lg font-black text-purple-400">...</h3>
                    <span class="text-xs">⏳</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Pola menunggu</p>
            </div>

            {{-- 6. Encounters --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition relative">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block truncate">Encounters</span>
                    <span class="text-[8px] font-bold px-1 py-0.2 rounded bg-amber-950/80 text-amber-300 border border-amber-800" title="Dataset GFW API tidak tersedia untuk credential saat ini">BLOCKED</span>
                </div>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-encounters" class="text-lg font-black text-amber-400">N/A</h3>
                    <span class="text-xs">🤝</span>
                </div>
                <p class="text-[9px] text-amber-300/80 mt-0.5 truncate font-medium" title="Upstream GFW API v3 mengembalikan 404 (Bukan berarti 0 event di laut)">API Not Available</p>
            </div>

            {{-- 7. Port Visits --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition relative">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-sky-400 block truncate">Port Visits</span>
                    <span class="text-[8px] font-bold px-1 py-0.2 rounded bg-sky-950/80 text-sky-300 border border-sky-800" title="Dataset GFW API tidak tersedia untuk credential saat ini">BLOCKED</span>
                </div>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-port-visits" class="text-lg font-black text-sky-400">N/A</h3>
                    <span class="text-xs">⚓</span>
                </div>
                <p class="text-[9px] text-sky-300/80 mt-0.5 truncate font-medium" title="Upstream GFW API v3 mengembalikan 404 (Bukan berarti 0 event di laut)">API Not Available</p>
            </div>

            {{-- 8. Monitoring Alerts --}}
            <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 hover:border-slate-700 transition">
                <span class="text-[10px] font-bold uppercase tracking-wider text-rose-400 block truncate">Alerts</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-alerts" class="text-lg font-black text-rose-400">...</h3>
                    <span class="text-xs">🚨</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-0.5 truncate">Perlu ditinjau</p>
            </div>
        </div>

        {{-- Filter Control Bar (Dark Theme) --}}
        <div class="bg-slate-900/90 p-4 rounded-2xl shadow-sm border border-slate-800 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                {{-- Search Vessel --}}
                <div class="md:col-span-3">
                    <label for="filter-search" class="block text-xs font-semibold text-slate-300 mb-1">
                        {{ __('Cari Kapal (Nama / MMSI / IMO)') }}
                    </label>
                    <input type="text"
                           id="filter-search"
                           placeholder="Ketik nama kapal, MMSI, atau IMO..."
                           class="w-full text-xs py-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- Start Date --}}
                <div class="md:col-span-2">
                    <label for="filter-start-date" class="block text-xs font-semibold text-slate-300 mb-1">
                        {{ __('Start Date') }}
                    </label>
                    <input type="date"
                           id="filter-start-date"
                           value="{{ $startDate }}"
                           class="w-full text-xs py-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 [color-scheme:dark]">
                </div>

                {{-- End Date --}}
                <div class="md:col-span-2">
                    <label for="filter-end-date" class="block text-xs font-semibold text-slate-300 mb-1">
                        {{ __('End Date (Maks 7 Hari)') }}
                    </label>
                    <input type="date"
                           id="filter-end-date"
                           value="{{ $endDate }}"
                           class="w-full text-xs py-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 [color-scheme:dark]">
                </div>

                {{-- Vessel Type --}}
                <div class="md:col-span-2">
                    <label for="filter-vessel-type" class="block text-xs font-semibold text-slate-300 mb-1">
                        {{ __('Tipe Kapal') }}
                    </label>
                    <select id="filter-vessel-type" class="w-full text-xs py-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" class="bg-slate-800 text-slate-100">{{ __('Semua Tipe') }}</option>
                        <option value="Fishing" class="bg-slate-800 text-slate-100">Fishing</option>
                        <option value="Carrier" class="bg-slate-800 text-slate-100">Carrier</option>
                        <option value="Support" class="bg-slate-800 text-slate-100">Support</option>
                        <option value="Bunker" class="bg-slate-800 text-slate-100">Bunker</option>
                        <option value="Tanker" class="bg-slate-800 text-slate-100">Tanker</option>
                        <option value="Cargo" class="bg-slate-800 text-slate-100">Cargo</option>
                        <option value="Passenger" class="bg-slate-800 text-slate-100">Passenger</option>
                        <option value="Other" class="bg-slate-800 text-slate-100">Other</option>
                        <option value="Unknown" class="bg-slate-800 text-slate-100">Unknown</option>
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="md:col-span-1">
                    <label for="filter-status" class="block text-xs font-semibold text-slate-300 mb-1">
                        {{ __('Status') }}
                    </label>
                    <select id="filter-status" class="w-full text-xs py-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="" class="bg-slate-800 text-slate-100">Semua</option>
                        <option value="LIVE" class="bg-slate-800 text-slate-100">LIVE (&lt;24h)</option>
                        <option value="RECENT" class="bg-slate-800 text-slate-100">RECENT</option>
                        <option value="STALE" class="bg-slate-800 text-slate-100">STALE</option>
                    </select>
                </div>

                {{-- Actions --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="button" id="btn-reset-filters" class="w-1/2 py-2 rounded-xl border border-slate-700 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition">
                        Reset
                    </button>
                    <button type="button" id="btn-apply-filters" class="w-1/2 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1">
                        <span id="filter-spinner" class="hidden">🔄</span>
                        <span>Filter</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Map with Floating Layer Controls (Dark Theme) --}}
        <div class="bg-slate-900/90 p-3 rounded-2xl shadow-sm border border-slate-800 relative">
            {{-- Map Canvas --}}
            <div id="gfw-dashboard-map" class="w-full h-[540px] rounded-xl overflow-hidden bg-slate-950 z-0"></div>

            {{-- Floating Layer Control Box --}}
            <div class="absolute top-6 right-6 z-10 bg-slate-900/95 text-white text-xs p-3.5 rounded-xl backdrop-blur-md border border-slate-700/80 shadow-2xl space-y-2.5 max-w-xs">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-1.5">
                    <span class="font-bold text-slate-100 flex items-center gap-1.5">
                        <span>🗺️</span>
                        <span>Kontrol Lapisan Peta</span>
                    </span>
                    <span class="text-[10px] text-slate-400">MapLibre GL</span>
                </div>

                <div class="space-y-1.5 text-[11px]">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-zee" checked class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-0">
                        <span class="font-semibold text-sky-400">Batas ZEE Aceh (BIG Layer 10)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-vessels" checked class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-0">
                        <span class="text-slate-200">Posisi Kapal (Vessels)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-track" checked class="rounded border-slate-600 bg-slate-800 text-cyan-400 focus:ring-0">
                        <span class="text-cyan-300">Lintasan Kapal (Track)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-fishing" checked class="rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-0">
                        <span class="text-emerald-300">Fishing Events</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-loitering" checked class="rounded border-slate-600 bg-slate-800 text-purple-500 focus:ring-0">
                        <span class="text-purple-300">Loitering</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-encounters" class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-0">
                        <span class="text-amber-300">Encounters</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-port-visits" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-0">
                        <span class="text-sky-300">Port Visits</span>
                    </label>
                </div>
            </div>

            {{-- Map Legend Footer --}}
            <div class="mt-2.5 px-2 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-300 border-t border-slate-800 pt-2">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-semibold text-slate-200">Simbol Peta:</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Fishing Event</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Loitering Event</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Encounter</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Port Visit</span>
                    <span class="flex items-center gap-1"><span class="w-3.5 h-1 bg-amber-500 rounded"></span> Vessel Track</span>
                    <span class="flex items-center gap-1"><span class="w-3.5 h-1 bg-blue-500 rounded"></span> Garis ZEE (BIG Layer 10)</span>
                </div>
                <div class="text-slate-400 font-mono text-[10px]">
                    Proyeksi: EPSG:4326 | Sumber Garis: Badan Informasi Geospasial (BIG Layer 10)
                </div>
            </div>
        </div>

        {{-- Bottom Split Layout: Activity & Alerts vs Vessel Table & Detail --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column (5 cols): Activity Feed & Monitoring Alerts Tabs --}}
            <div class="lg:col-span-5 space-y-4">
                <div class="bg-slate-900/90 rounded-2xl p-4 shadow-sm border border-slate-800 space-y-3">
                    {{-- Tab Navigation --}}
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-1.5" id="nav-tabs-container">
                            <button type="button" id="tab-btn-activities" class="px-3 py-1.5 rounded-xl font-bold text-xs bg-indigo-600 text-white shadow-xs transition flex items-center gap-1.5">
                                <span>⚡</span>
                                <span>Aktivitas Maritim</span>
                            </button>
                            <button type="button" id="tab-btn-alerts" class="px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700/70 transition flex items-center gap-1.5">
                                <span>🚨</span>
                                <span>Monitoring Alerts</span>
                                <span id="alerts-tab-badge" class="px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500 text-white">0</span>
                            </button>
                        </div>
                        <span id="tab-summary-count" class="text-[11px] font-mono text-slate-400">0 data</span>
                    </div>

                    {{-- Panel 1: Activity Feed (Stage 4, 6, 7, 8) --}}
                    <div id="panel-activities" class="space-y-2.5">
                        {{-- Activity Filter Chips --}}
                        <div class="flex flex-wrap items-center gap-1 text-[11px]" id="feed-filter-chips">
                            <button type="button" data-type="all" class="feed-chip px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-semibold transition">Semua</button>
                            <button type="button" data-type="fishing" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">Fishing</button>
                            <button type="button" data-type="loitering" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">Loitering</button>
                            <button type="button" data-type="encounter" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">Encounter</button>
                            <button type="button" data-type="port" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">Port</button>
                        </div>

                        {{-- Feed List --}}
                        <div id="activity-feed-list" class="space-y-2 max-h-[460px] overflow-y-auto pr-1 text-xs">
                            <div class="py-8 text-center text-slate-400">
                                <span class="animate-pulse">Memuat aktivitas maritim...</span>
                            </div>
                        </div>
                    </div>

                    {{-- Panel 2: Monitoring Alerts / Alarm (Stage 9) --}}
                    <div id="panel-alerts" class="hidden space-y-2.5">
                        <div class="p-2.5 rounded-xl bg-amber-950/40 border border-amber-800/80 text-amber-200 text-[11px] leading-relaxed">
                            <strong class="text-amber-100">Perlu Ditinjau:</strong> Alert adalah indikator analitik untuk ditinjau manusia, bukan merupakan keputusan pelanggaran hukum.
                        </div>

                        {{-- Severity Filter Chips --}}
                        <div class="flex flex-wrap items-center gap-1 text-[11px]" id="alert-filter-chips">
                            <button type="button" data-severity="all" class="alert-chip px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-semibold transition">Semua</button>
                            <button type="button" data-severity="WARNING" class="alert-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">WARNING</button>
                            <button type="button" data-severity="INFO" class="alert-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">INFO</button>
                            <button type="button" data-severity="CRITICAL" class="alert-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition">CRITICAL</button>
                        </div>

                        {{-- Alerts List --}}
                        <div id="alerts-feed-list" class="space-y-2 max-h-[440px] overflow-y-auto pr-1 text-xs">
                            <div class="py-8 text-center text-slate-400">
                                <span class="animate-pulse">Memuat monitoring alerts...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (7 cols): Vessel Profile & Intelligence + Vessel Table --}}
            <div class="lg:col-span-7 space-y-4">
                {{-- Vessel Detail & Intelligence Card (Dark Mode) --}}
                <div id="dashboard-vessel-detail" class="bg-slate-900/90 rounded-2xl p-4 shadow-sm border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Vessel Profile & Intelligence</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="detail-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                Pilih Kapal
                            </span>
                            <button type="button" id="btn-load-track" class="hidden px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-[11px] transition shadow-xs flex items-center gap-1">
                                <span>🗺️</span>
                                <span>Lihat Track</span>
                            </button>
                        </div>
                    </div>

                    <div id="vessel-detail-empty" class="py-6 text-center text-slate-400">
                        <span class="text-2xl block mb-1">🚢</span>
                        <p class="text-xs font-medium">Klik kapal pada peta atau tabel untuk melihat riwayat pergerakan & lintasan</p>
                    </div>

                    <div id="vessel-detail-body" class="hidden space-y-3">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                                <span class="text-[10px] text-slate-400 block">Nama Kapal</span>
                                <span id="vessel-name" class="font-bold text-slate-100 block truncate">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                                <span class="text-[10px] text-slate-400 block">MMSI</span>
                                <span id="vessel-mmsi" class="font-mono text-slate-200 block">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                                <span class="text-[10px] text-slate-400 block">IMO</span>
                                <span id="vessel-imo" class="font-mono text-slate-200 block">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                                <span class="text-[10px] text-slate-400 block">Bendera / Tipe</span>
                                <span id="vessel-flag-type" class="font-semibold text-slate-200 block truncate">-</span>
                            </div>
                        </div>

                        {{-- Track Info Box (Shown when track loaded - Stage 3) --}}
                        <div id="track-summary-box" class="hidden p-3 rounded-xl bg-amber-950/40 border border-amber-800/80 text-xs text-amber-200 space-y-1">
                            <div class="flex items-center justify-between font-bold text-amber-300 pb-1 border-b border-amber-800/50">
                                <span>Lintasan Pergerakan Kapal (Vessel Track)</span>
                                <span id="track-points-count" class="font-mono text-[11px] bg-amber-900/70 text-amber-200 border border-amber-700/60 px-1.5 py-0.5 rounded">0 titik</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                                <div>
                                    <span class="text-amber-400">Pertama Terdeteksi:</span>
                                    <span id="track-first-seen" class="font-medium text-amber-100 ml-1">-</span>
                                </div>
                                <div>
                                    <span class="text-amber-400">Terakhir Terdeteksi:</span>
                                    <span id="track-last-seen" class="font-medium text-amber-100 ml-1">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vessel Directory Table (Dark Mode) --}}
                <div class="bg-slate-900/90 rounded-2xl p-4 shadow-sm border border-slate-800 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📑</span>
                            <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Daftar Kapal di ZEE Aceh</h3>
                        </div>
                        <div class="text-[11px] text-slate-400 font-medium" id="table-record-count">
                            0 kapal
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-300">
                            <thead class="bg-slate-800/90 text-[10px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-700">
                                <tr>
                                    <th class="py-2.5 px-2.5">#</th>
                                    <th class="py-2.5 px-2">Kapal</th>
                                    <th class="py-2.5 px-2">MMSI</th>
                                    <th class="py-2.5 px-2">Bendera</th>
                                    <th class="py-2.5 px-2">Tipe</th>
                                    <th class="py-2.5 px-2">Status</th>
                                    <th class="py-2.5 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-table-body" class="divide-y divide-slate-800">
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-slate-400">
                                        <span class="animate-pulse">Memuat data armada...</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stage 9: Alert Detail Modal (Dark Mode) --}}
    <div id="alert-detail-modal" class="fixed inset-0 z-50 hidden bg-black/75 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-slate-900 rounded-2xl max-w-lg w-full p-5 shadow-2xl border border-slate-800 space-y-4 text-slate-100">
            <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span id="modal-alert-icon" class="text-xl">🚨</span>
                    <div>
                        <h3 id="modal-alert-title" class="text-sm font-bold text-slate-100">Detail Monitoring Alert</h3>
                        <p class="text-[10px] text-slate-400">Indikator pemantauan untuk ditinjau manusia</p>
                    </div>
                </div>
                <button type="button" id="btn-close-modal" class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-sm font-bold flex items-center justify-center border border-slate-700">
                    ✕
                </button>
            </div>

            <div class="p-3 rounded-xl bg-amber-950/50 border border-amber-800/80 text-amber-200 text-xs">
                <strong class="text-amber-100">Catatan Penting:</strong> Alert ini merupakan indikator pemantauan berdasarkan data analitik GFW. Ini <em>bukan</em> vonis atau bukti hukum terjadinya pelanggaran.
            </div>

            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Tingkat Severity</span>
                    <span id="modal-alert-severity" class="font-bold text-xs">-</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Status Review</span>
                    <span id="modal-alert-status" class="font-bold text-xs">-</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Kapal</span>
                    <span id="modal-alert-vessel" class="font-semibold text-slate-100 block truncate">-</span>
                </div>
                <div class="p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Waktu Terdeteksi</span>
                    <span id="modal-alert-time" class="font-mono text-slate-200 block text-[11px]">-</span>
                </div>
                <div class="col-span-2 p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Lokasi & Batas</span>
                    <span id="modal-alert-location" class="font-mono text-slate-200 block text-[11px]">-</span>
                </div>
                <div class="col-span-2 p-2 rounded-xl bg-slate-800/80 border border-slate-700/70">
                    <span class="text-[10px] text-slate-400 block">Alasan Indikator (Reason)</span>
                    <p id="modal-alert-reason" class="text-slate-200 text-xs mt-0.5 leading-relaxed">-</p>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-800">
                <div class="flex items-center gap-1.5">
                    <button type="button" id="btn-alert-ack" class="px-3 py-1.5 rounded-xl bg-indigo-950/80 hover:bg-indigo-900 text-indigo-300 border border-indigo-700/80 font-bold text-xs transition">
                        ✓ Tandai Ditinjau
                    </button>
                    <button type="button" id="btn-alert-resolve" class="px-3 py-1.5 rounded-xl bg-emerald-950/80 hover:bg-emerald-900 text-emerald-300 border border-emerald-700/80 font-bold text-xs transition">
                        ✓ Selesai
                    </button>
                </div>
                <button type="button" id="btn-modal-fly-map" class="px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition shadow-xs flex items-center gap-1">
                    <span>🗺️</span>
                    <span>Pusatkan Peta</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Frontend Javascript Controller --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // State
            let map = null;
            let vesselsList = [];
            let eventsList = [];
            let alertsList = [];
            let activeSelectedVessel = null;
            let currentFeedFilter = 'all';
            let currentAlertFilter = 'all';
            let activeTab = 'activities';
            let activeModalAlert = null;

            // DOM References
            const kpiDetected = document.getElementById('kpi-detected-vessels');
            const kpiLive = document.getElementById('kpi-live-recent');
            const kpiFishing = document.getElementById('kpi-fishing-activity');
            const kpiTrackPoints = document.getElementById('kpi-track-points');
            const kpiLoitering = document.getElementById('kpi-loitering');
            const kpiEncounters = document.getElementById('kpi-encounters');
            const kpiPort = document.getElementById('kpi-port-visits');
            const kpiAlerts = document.getElementById('kpi-alerts');

            const filterSearch = document.getElementById('filter-search');
            const filterStart = document.getElementById('filter-start-date');
            const filterEnd = document.getElementById('filter-end-date');
            const filterType = document.getElementById('filter-vessel-type');
            const filterStatus = document.getElementById('filter-status');
            const btnApply = document.getElementById('btn-apply-filters');
            const btnReset = document.getElementById('btn-reset-filters');
            const filterSpinner = document.getElementById('filter-spinner');
            const btnRefresh = document.getElementById('btn-refresh-dashboard');

            const APP_TIMEZONE = "{{ config('app.timezone', 'Asia/Jakarta') }}";
            const INITIAL_LAST_SUCCESSFUL_SYNC = @json($lastSuccessfulSync ?? null);
            let lastSuccessfulTimestamp = INITIAL_LAST_SUCCESSFUL_SYNC || localStorage.getItem('gfw_dashboard_last_success_ts') || null;
            let lastSuccessfulDataAge = localStorage.getItem('gfw_dashboard_last_success_age') ? parseInt(localStorage.getItem('gfw_dashboard_last_success_age'), 10) : null;

            const liveStatusText = document.getElementById('live-status-text');
            const metaLastUpdated = document.getElementById('meta-last-updated');
            const metaDataAge = document.getElementById('meta-data-age');

            const errorNotice = document.getElementById('gfw-dashboard-error-notice');
            const errorTitle = document.getElementById('error-notice-title');
            const errorDetail = document.getElementById('error-notice-detail');
            const noticeLastUpdated = document.getElementById('dashboard-notice-last-updated');
            const noticeDataAge = document.getElementById('dashboard-notice-data-age');
            const btnRetry = document.getElementById('btn-retry-fetch');

            const tabBtnActivities = document.getElementById('tab-btn-activities');
            const tabBtnAlerts = document.getElementById('tab-btn-alerts');
            const panelActivities = document.getElementById('panel-activities');
            const panelAlerts = document.getElementById('panel-alerts');
            const alertsTabBadge = document.getElementById('alerts-tab-badge');
            const tabSummaryCount = document.getElementById('tab-summary-count');

            const activityFeedList = document.getElementById('activity-feed-list');
            const alertsFeedList = document.getElementById('alerts-feed-list');
            const tableBody = document.getElementById('dashboard-table-body');
            const tableRecordCount = document.getElementById('table-record-count');

            const vesselDetailEmpty = document.getElementById('vessel-detail-empty');
            const vesselDetailBody = document.getElementById('vessel-detail-body');
            const detailStatusBadge = document.getElementById('detail-status-badge');
            const btnLoadTrack = document.getElementById('btn-load-track');
            const vesselNameEl = document.getElementById('vessel-name');
            const vesselMmsiEl = document.getElementById('vessel-mmsi');
            const vesselImoEl = document.getElementById('vessel-imo');
            const vesselFlagTypeEl = document.getElementById('vessel-flag-type');
            const trackSummaryBox = document.getElementById('track-summary-box');
            const trackPointsCount = document.getElementById('track-points-count');
            const trackFirstSeen = document.getElementById('track-first-seen');
            const trackLastSeen = document.getElementById('track-last-seen');

            // Layer Checkboxes
            const optZee = document.getElementById('layer-opt-zee');
            const optVessels = document.getElementById('layer-opt-vessels');
            const optTrack = document.getElementById('layer-opt-track');
            const optFishing = document.getElementById('layer-opt-fishing');
            const optLoitering = document.getElementById('layer-opt-loitering');
            const optEncounters = document.getElementById('layer-opt-encounters');
            const optPortVisits = document.getElementById('layer-opt-port-visits');

            // Modal elements
            const alertModal = document.getElementById('alert-detail-modal');
            const btnCloseModal = document.getElementById('btn-close-modal');
            const modalAlertSeverity = document.getElementById('modal-alert-severity');
            const modalAlertStatus = document.getElementById('modal-alert-status');
            const modalAlertVessel = document.getElementById('modal-alert-vessel');
            const modalAlertTime = document.getElementById('modal-alert-time');
            const modalAlertLocation = document.getElementById('modal-alert-location');
            const modalAlertReason = document.getElementById('modal-alert-reason');
            const btnAlertAck = document.getElementById('btn-alert-ack');
            const btnAlertResolve = document.getElementById('btn-alert-resolve');
            const btnModalFlyMap = document.getElementById('btn-modal-fly-map');

            // Initialize MapLibre Map with ESRI World Dark Gray Base
            function initMap() {
                map = new maplibregl.Map({
                    container: 'gfw-dashboard-map',
                    style: {
                        version: 8,
                        glyphs: 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf',
                        sources: {
                            'esri-dark': {
                                type: 'raster',
                                tiles: [
                                    'https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}'
                                ],
                                tileSize: 256,
                                attribution: '&copy; Esri, HERE, Garmin, © OpenStreetMap'
                            }
                        },
                        layers: [
                            {
                                id: 'esri-dark-layer',
                                type: 'raster',
                                source: 'esri-dark',
                                minzoom: 0,
                                maxzoom: 16
                            }
                        ]
                    },
                    center: [95.5, 4.5],
                    zoom: 6,
                    attributionControl: false
                });

                map.addControl(new maplibregl.NavigationControl(), 'top-left');
                map.addControl(new maplibregl.ScaleControl({ maxWidth: 100, unit: 'nautical' }), 'bottom-left');

                map.on('load', () => {
                    loadBigZeeBoundaries();
                    fetchDashboardData();
                });
            }

            // Load BIG ZEE Aceh boundary lines and polygon (Single authoritative source: BIG Layer 10)
            async function loadBigZeeBoundaries() {
                try {
                    const resPoly = await fetch('/api/gis/big/zee/aceh?polygon=1');
                    const polyJson = await resPoly.json();
                    if (polyJson && polyJson.success && polyJson.geojson && !map.getSource('big-zee-poly')) {
                        map.addSource('big-zee-poly', { type: 'geojson', data: polyJson.geojson });
                        map.addLayer({
                            id: 'big-zee-fill',
                            type: 'fill',
                            source: 'big-zee-poly',
                            paint: { 'fill-color': '#0284c7', 'fill-opacity': 0.12 }
                        });
                        map.addLayer({
                            id: 'big-zee-outline',
                            type: 'line',
                            source: 'big-zee-poly',
                            paint: { 'line-color': '#38bdf8', 'line-width': 1.5, 'line-opacity': 0.6 }
                        });
                    }
                } catch (e) {
                    console.warn('Gagal memuat poligon ZEE BIG:', e);
                }

                try {
                    const resLine = await fetch('/api/gis/big/zee/aceh');
                    const lineJson = await resLine.json();
                    if (lineJson && lineJson.type === 'FeatureCollection' && !map.getSource('big-zee-line')) {
                        map.addSource('big-zee-line', { type: 'geojson', data: lineJson });
                        map.addLayer({
                            id: 'big-zee-aceh-line',
                            type: 'line',
                            source: 'big-zee-line',
                            paint: { 'line-color': '#0ea5e9', 'line-width': 2.5 }
                        });
                    }
                } catch (e) {
                    console.warn('Gagal memuat garis batas BIG:', e);
                }
            }

            // Main Dashboard Fetcher
            async function fetchDashboardData(isRefresh = false) {
                filterSpinner.classList.remove('hidden');
                liveStatusText.textContent = 'Memuat data...';

                const params = new URLSearchParams({
                    start: filterStart.value,
                    end: filterEnd.value,
                    limit: 100,
                });

                if (isRefresh) params.append('refresh', '1');
                if (filterType.value) params.append('vessel_type', filterType.value);
                if (filterSearch.value.trim()) params.append('search', filterSearch.value.trim());

                try {
                    const response = await fetch(`/api/gfw/dashboard?${params.toString()}`);
                    const json = await response.json();
                    filterSpinner.classList.add('hidden');

                    if (!response.ok || !json.success) {
                        handleFetchFailure(json.message || 'Gagal memuat data dari Global Fishing Watch.');
                        return;
                    }

                    // Success - hide error notice
                    errorNotice.classList.add('hidden');
                    liveStatusText.textContent = 'Terkini (Siap)';

                    // Update KPIs (Stage 5.1)
                    kpiDetected.textContent = (json.kpi?.detected_vessels ?? json.kpi?.total_vessels ?? 0).toLocaleString();
                    kpiLive.textContent = (json.kpi?.live_recent ?? json.kpi?.active_vessels ?? 0).toLocaleString();
                    kpiFishing.textContent = (json.kpi?.fishing_activity ?? json.kpi?.fishing_events ?? 0).toLocaleString();
                    kpiTrackPoints.textContent = (json.kpi?.track_points ?? 0).toLocaleString();
                    kpiLoitering.textContent = (json.kpi?.loitering ?? 0).toLocaleString();
                    kpiEncounters.textContent = json.kpi?.encounters_status ? 'N/A' : (json.kpi?.encounters ?? 0).toLocaleString();
                    kpiPort.textContent = json.kpi?.port_visits_status ? 'N/A' : (json.kpi?.port_visits ?? 0).toLocaleString();
                    kpiAlerts.textContent = (json.kpi?.alerts ?? json.kpi?.alerts_count ?? (json.alerts ? json.alerts.length : 0)).toLocaleString();

                    // Update Timers & Metadata
                    metaLastUpdated.textContent = formatDate(json.last_updated);
                    const ageSec = json.data_age_seconds ?? 0;
                    metaDataAge.textContent = formatAge(ageSec);

                    // Store lists
                    vesselsList = json.vessels || [];
                    eventsList = json.activity_feed || [];
                    alertsList = json.alerts || [];

                    // Filter status locally if requested
                    let displayVessels = vesselsList;
                    if (filterStatus.value) {
                        displayVessels = vesselsList.filter(v => (v.status || '').toUpperCase() === filterStatus.value);
                    }

                    // Update Visuals
                    updateMapLayers(displayVessels, json.events || []);
                    renderActivityFeed(eventsList);
                    renderAlertsFeed(alertsList);
                    renderVesselsTable(displayVessels);

                    alertsTabBadge.textContent = alertsList.length;
                    updateTabCount();

                    // Track last successful timestamp
                    if (json.last_updated) {
                        lastSuccessfulTimestamp = json.last_updated;
                        lastSuccessfulDataAge = json.data_age_seconds ?? 0;
                        try {
                            localStorage.setItem('gfw_dashboard_last_success_ts', lastSuccessfulTimestamp);
                            localStorage.setItem('gfw_dashboard_last_success_age', String(lastSuccessfulDataAge));
                        } catch (e) {}
                    }
                    errorNotice.classList.add('hidden');
                    liveStatusText.textContent = 'DATA TERBARU';
                    liveStatusText.className = 'font-semibold font-mono text-[10px] text-emerald-400';

                } catch (err) {
                    filterSpinner.classList.add('hidden');
                    handleFetchFailure(err.message);
                }
            }

            // Error Resilience - Last Successful Data fallback
            function handleFetchFailure(msg) {
                if (vesselsList.length > 0 || eventsList.length > 0 || lastSuccessfulTimestamp) {
                    errorNotice.classList.remove('hidden');
                    errorTitle.textContent = 'Gagal memperbarui data dari GFW API';
                    errorDetail.textContent = 'Menampilkan dataset berhasil terakhir. Data operasional tetap aman dan tidak direset.';
                    if (noticeLastUpdated) {
                        noticeLastUpdated.textContent = lastSuccessfulTimestamp ? formatDate(lastSuccessfulTimestamp) : 'Dataset sebelumnya';
                    }
                    if (noticeDataAge) {
                        const ageSec = lastSuccessfulDataAge !== null ? lastSuccessfulDataAge : null;
                        noticeDataAge.textContent = ageSec !== null ? `(Sekitar ${formatAge(ageSec)})` : '';
                    }
                    liveStatusText.textContent = 'DATA TERAKHIR TERSEDIA';
                    liveStatusText.className = 'font-semibold font-mono text-[10px] text-amber-400';
                } else {
                    errorNotice.classList.add('hidden');
                    liveStatusText.textContent = 'BELUM TERSEDIA';
                    liveStatusText.className = 'font-semibold font-mono text-[10px] text-rose-400';
                }
            }

            // MapLibre GeoJSON update with Clustering and Event Layers
            function updateMapLayers(vessels, events) {
                if (!map) return;

                // 1. Vessels GeoJSON
                const vesselGeoJson = {
                    type: 'FeatureCollection',
                    features: vessels
                        .filter(v => v.lat !== null && v.lon !== null)
                        .map(v => ({
                            type: 'Feature',
                            geometry: { type: 'Point', coordinates: [v.lon, v.lat] },
                            properties: {
                                id: v.id,
                                name: v.name || 'Unnamed Vessel',
                                mmsi: v.mmsi || '-',
                                type: v.vessel_type || 'Unknown',
                                flag: v.flag || '-',
                                status: v.status || 'STALE',
                                activity: v.activity || 'Vessel Presence',
                                last_seen: v.last_seen || ''
                            }
                        }))
                };

                if (map.getSource('dashboard-vessels-src')) {
                    map.getSource('dashboard-vessels-src').setData(vesselGeoJson);
                } else {
                    map.addSource('dashboard-vessels-src', {
                        type: 'geojson',
                        data: vesselGeoJson,
                        cluster: true,
                        clusterMaxZoom: 10,
                        clusterRadius: 40
                    });

                    // Cluster circles
                    map.addLayer({
                        id: 'vessels-clusters',
                        type: 'circle',
                        source: 'dashboard-vessels-src',
                        filter: ['has', 'point_count'],
                        paint: {
                            'circle-color': '#4f46e5',
                            'circle-radius': ['step', ['get', 'point_count'], 14, 10, 20, 30, 26],
                            'circle-opacity': 0.9,
                            'circle-stroke-width': 2,
                            'circle-stroke-color': '#0f172a'
                        }
                    });

                    // Cluster count text
                    map.addLayer({
                        id: 'vessels-cluster-count',
                        type: 'symbol',
                        source: 'dashboard-vessels-src',
                        filter: ['has', 'point_count'],
                        layout: {
                            'text-field': '{point_count_abbreviated}',
                            'text-size': 11
                        },
                        paint: { 'text-color': '#ffffff' }
                    });

                    // Unclustered vessel points
                    map.addLayer({
                        id: 'unclustered-vessels',
                        type: 'circle',
                        source: 'dashboard-vessels-src',
                        filter: ['!', ['has', 'point_count']],
                        paint: {
                            'circle-radius': 5.5,
                            'circle-color': [
                                'match',
                                ['get', 'type'],
                                'Fishing', '#10b981',
                                'Carrier', '#a855f7',
                                'Tanker', '#f59e0b',
                                'Bunker', '#f59e0b',
                                'Cargo', '#0ea5e9',
                                '#94a3b8'
                            ],
                            'circle-stroke-width': 1.5,
                            'circle-stroke-color': '#0f172a'
                        }
                    });

                    // Click cluster to zoom
                    map.on('click', 'vessels-clusters', (e) => {
                        const features = map.queryRenderedFeatures(e.point, { layers: ['vessels-clusters'] });
                        const clusterId = features[0].properties.cluster_id;
                        map.getSource('dashboard-vessels-src').getClusterExpansionZoom(clusterId, (err, zoom) => {
                            if (err) return;
                            map.easeTo({ center: features[0].geometry.coordinates, zoom: zoom });
                        });
                    });

                    // Click unclustered point
                    map.on('click', 'unclustered-vessels', (e) => {
                        const props = e.features[0].properties;
                        selectVesselById(props.id);

                        new maplibregl.Popup({ offset: 12 })
                            .setLngLat(e.features[0].geometry.coordinates)
                            .setHTML(`
                                <div class="p-2 space-y-1.5 text-xs font-sans min-w-[200px] text-slate-100">
                                    <div class="font-bold text-slate-100 flex items-center justify-between gap-2 border-b border-slate-700/80 pb-1">
                                        <span class="truncate">${escapeHtml(props.name)}</span>
                                        ${renderVesselFlag(props.flag)}
                                    </div>
                                    <div class="text-[11px] text-slate-300">MMSI: <span class="font-mono text-slate-200">${escapeHtml(props.mmsi)}</span> • Tipe: ${escapeHtml(props.type)}</div>
                                    <div class="text-[10px] text-indigo-400 font-semibold">${escapeHtml(props.status)} • ${escapeHtml(props.activity)}</div>
                                    <div class="text-[10px] text-slate-400">Wilayah: ZEE Aceh (BIG Layer 10)</div>
                                </div>
                            `)
                            .addTo(map);
                    });
                }

                // 2. Activity Events Points (Stage 4 & Stage 6)
                const eventsGeoJson = {
                    type: 'FeatureCollection',
                    features: events
                        .filter(e => e.position && e.position.lat !== null && e.position.lon !== null)
                        .map(e => ({
                            type: 'Feature',
                            geometry: { type: 'Point', coordinates: [e.position.lon, e.position.lat] },
                            properties: {
                                id: e.id,
                                type: (e.type || 'activity').toLowerCase(),
                                vessel: e.vessel?.name || 'Kapal',
                                vessel_id: e.vessel?.id || '',
                                mmsi: e.vessel?.ssvid || '',
                                flag: e.vessel?.flag || '',
                                time: e.start || e.end || '',
                                lat: e.position.lat,
                                lon: e.position.lon
                            }
                        }))
                };

                if (map.getSource('dashboard-events-src')) {
                    map.getSource('dashboard-events-src').setData(eventsGeoJson);
                } else {
                    map.addSource('dashboard-events-src', { type: 'geojson', data: eventsGeoJson });

                    // Fishing Events layer (Emerald)
                    map.addLayer({
                        id: 'fishing-events-layer',
                        type: 'circle',
                        source: 'dashboard-events-src',
                        filter: ['==', ['get', 'type'], 'fishing'],
                        paint: {
                            'circle-radius': 5.5,
                            'circle-color': '#10b981',
                            'circle-stroke-width': 1.5,
                            'circle-stroke-color': '#0f172a'
                        }
                    });

                    // Loitering Events layer (Purple)
                    map.addLayer({
                        id: 'loitering-events-layer',
                        type: 'circle',
                        source: 'dashboard-events-src',
                        filter: ['==', ['get', 'type'], 'loitering'],
                        paint: {
                            'circle-radius': 5.5,
                            'circle-color': '#a855f7',
                            'circle-stroke-width': 1.5,
                            'circle-stroke-color': '#0f172a'
                        }
                    });

                    // Event Click Popup
                    const onEventClick = (e) => {
                        const props = e.features[0].properties;
                        new maplibregl.Popup({ offset: 12 })
                            .setLngLat(e.features[0].geometry.coordinates)
                            .setHTML(`
                                <div class="p-2 space-y-1.5 text-xs text-slate-100 min-w-[210px]">
                                    <div class="font-bold text-slate-100 text-sm border-b border-slate-700/80 pb-1">${escapeHtml(props.vessel)}</div>
                                    <div class="text-[11px] text-slate-300">MMSI: <span class="font-mono text-slate-200">${escapeHtml(props.mmsi)}</span> • Bendera: ${escapeHtml(props.flag)}</div>
                                    <div class="text-[11px] font-semibold ${props.type === 'loitering' ? 'text-purple-400' : 'text-emerald-400'}">
                                        Peristiwa: ${props.type === 'loitering' ? 'Loitering Event' : 'Fishing Activity'}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">${formatDate(props.time)}</div>
                                    <div class="text-[10px] text-slate-400">Koordinat: ${roundCoord(props.lat)}, ${roundCoord(props.lon)}</div>
                                    <div class="text-[10px] text-amber-200 bg-amber-950/70 border border-amber-800/80 p-1.5 rounded mt-1 leading-tight">Indikator pemantauan algoritmik untuk ditinjau manusia.</div>
                                </div>
                            `)
                            .addTo(map);
                    };

                    map.on('click', 'fishing-events-layer', onEventClick);
                    map.on('click', 'loitering-events-layer', onEventClick);
                }
            }

            // Layer Checkboxes Listener
            function bindLayerToggles() {
                const setVis = (layerId, isChecked) => {
                    if (map && map.getLayer(layerId)) {
                        map.setLayoutProperty(layerId, 'visibility', isChecked ? 'visible' : 'none');
                    }
                };

                optZee?.addEventListener('change', e => {
                    setVis('big-zee-fill', e.target.checked);
                    setVis('big-zee-outline', e.target.checked);
                    setVis('big-zee-aceh-line', e.target.checked);
                });

                optVessels?.addEventListener('change', e => {
                    setVis('vessels-clusters', e.target.checked);
                    setVis('vessels-cluster-count', e.target.checked);
                    setVis('unclustered-vessels', e.target.checked);
                });

                optTrack?.addEventListener('change', e => {
                    setVis('vessel-track-line', e.target.checked);
                    setVis('vessel-track-points', e.target.checked);
                });

                optFishing?.addEventListener('change', e => setVis('fishing-events-layer', e.target.checked));
                optLoitering?.addEventListener('change', e => setVis('loitering-events-layer', e.target.checked));
                optEncounters?.addEventListener('change', e => setVis('encounters-events-layer', e.target.checked));
                optPortVisits?.addEventListener('change', e => setVis('port-visits-events-layer', e.target.checked));
            }
            bindLayerToggles();

            // Activity Feed Rendering (Stage 4, 6)
            function renderActivityFeed(feed) {
                const filtered = feed.filter(item => {
                    if (currentFeedFilter === 'all') return true;
                    return (item.activity || '').toLowerCase().includes(currentFeedFilter) || (item.type || '').toLowerCase().includes(currentFeedFilter);
                });

                if (filtered.length === 0) {
                    activityFeedList.innerHTML = `
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Tidak ada aktivitas terdeteksi pada kategori ini.
                        </div>
                    `;
                    return;
                }

                activityFeedList.innerHTML = '';
                filtered.forEach(item => {
                    const el = document.createElement('div');
                    el.className = 'p-2.5 rounded-xl border border-slate-700/70 hover:border-indigo-500/60 bg-slate-800/60 hover:bg-slate-800/90 transition cursor-pointer space-y-1';
                    
                    let badgeColor = 'bg-slate-700/80 text-slate-200 border border-slate-600';
                    if (item.activity.includes('Fishing')) badgeColor = 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/80';
                    else if (item.activity.includes('Encounter')) badgeColor = 'bg-amber-950/80 text-amber-300 border border-amber-800/80';
                    else if (item.activity.includes('Loitering')) badgeColor = 'bg-purple-950/80 text-purple-300 border border-purple-800/80';
                    else if (item.activity.includes('Port')) badgeColor = 'bg-sky-950/80 text-sky-300 border border-sky-800/80';

                    el.innerHTML = `
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-100 truncate">${escapeHtml(item.vessel)}</span>
                            <span class="font-mono text-[10px] text-slate-400">${formatDate(item.time)}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="px-1.5 py-0.5 rounded font-bold ${badgeColor}">${escapeHtml(item.activity)}</span>
                            <span class="font-mono text-slate-400">${escapeHtml(item.location)}</span>
                        </div>
                    `;

                    el.addEventListener('click', () => {
                        if (item.lat !== null && item.lon !== null && map) {
                            map.flyTo({ center: [item.lon, item.lat], zoom: 9 });
                        }
                        if (item.vessel_id) {
                            selectVesselById(item.vessel_id);
                        }
                    });

                    activityFeedList.appendChild(el);
                });
            }

            // Monitoring Alerts Rendering (Stage 9)
            function renderAlertsFeed(alerts) {
                const filtered = alerts.filter(item => {
                    if (currentAlertFilter === 'all') return true;
                    return (item.severity || '').toUpperCase() === currentAlertFilter.toUpperCase();
                });

                if (filtered.length === 0) {
                    alertsFeedList.innerHTML = `
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Tidak ada alert pemantauan pada kategori ini.
                        </div>
                    `;
                    return;
                }

                alertsFeedList.innerHTML = '';
                filtered.forEach((item, idx) => {
                    const el = document.createElement('div');
                    el.className = 'p-3 rounded-xl border border-slate-700/70 hover:border-indigo-500/60 bg-slate-800/60 hover:bg-slate-800/90 transition cursor-pointer space-y-1.5 shadow-2xs';

                    let sevBadge = 'bg-blue-950/80 text-blue-300 border border-blue-800/80';
                    if (item.severity === 'WARNING') sevBadge = 'bg-amber-950/80 text-amber-300 border border-amber-800/80';
                    if (item.severity === 'CRITICAL') sevBadge = 'bg-rose-950/80 text-rose-300 border border-rose-800/80';

                    let statusBadge = 'bg-slate-700/80 text-slate-300 border border-slate-600';
                    if (item.status === 'NEW') statusBadge = 'bg-rose-950/80 text-rose-300 border border-rose-800/80';
                    else if (item.status === 'ACKNOWLEDGED') statusBadge = 'bg-amber-950/80 text-amber-300 border border-amber-800/80';
                    else if (item.status === 'RESOLVED') statusBadge = 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/80';

                    el.innerHTML = `
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1.5 truncate">
                                <span class="px-1.5 py-0.5 rounded text-[10px] font-black border ${sevBadge}">${escapeHtml(item.severity)}</span>
                                <span class="font-bold text-slate-100 truncate">${escapeHtml(item.title)}</span>
                            </div>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold ${statusBadge}">${escapeHtml(item.status)}</span>
                        </div>
                        <div class="text-[11px] text-slate-300 flex items-center justify-between">
                            <span>Kapal: <strong class="text-white">${escapeHtml(item.vessel)}</strong></span>
                            <span class="font-mono text-[10px] text-slate-400">${formatDate(item.time)}</span>
                        </div>
                        <div class="text-[10px] text-slate-400 flex items-center justify-between">
                            <span>Lokasi: ${escapeHtml(item.location)}</span>
                            <span class="text-indigo-400 font-semibold hover:underline">Tinjau Detail →</span>
                        </div>
                    `;

                    el.addEventListener('click', () => openAlertModal(item));
                    alertsFeedList.appendChild(el);
                });
            }

            // Open Alert Modal (Stage 9.4)
            function openAlertModal(alertItem) {
                activeModalAlert = alertItem;
                modalAlertSeverity.textContent = alertItem.severity;
                modalAlertSeverity.className = 'font-bold text-xs ' + (alertItem.severity === 'WARNING' ? 'text-amber-400' : (alertItem.severity === 'CRITICAL' ? 'text-rose-400' : 'text-blue-400'));
                modalAlertStatus.textContent = alertItem.status;
                modalAlertVessel.textContent = alertItem.vessel + (alertItem.mmsi ? ` (MMSI: ${alertItem.mmsi})` : '');
                modalAlertTime.textContent = formatDate(alertItem.time);
                modalAlertLocation.textContent = alertItem.location + ' (ZEE Aceh BIG Layer 10)';
                modalAlertReason.textContent = alertItem.reason || alertItem.description;

                alertModal.classList.remove('hidden');
            }

            btnCloseModal?.addEventListener('click', () => alertModal.classList.add('hidden'));

            btnAlertAck?.addEventListener('click', () => {
                if (!activeModalAlert) return;
                activeModalAlert.status = 'ACKNOWLEDGED';
                modalAlertStatus.textContent = 'ACKNOWLEDGED';
                renderAlertsFeed(alertsList);
            });

            btnAlertResolve?.addEventListener('click', () => {
                if (!activeModalAlert) return;
                activeModalAlert.status = 'RESOLVED';
                modalAlertStatus.textContent = 'RESOLVED';
                renderAlertsFeed(alertsList);
            });

            btnModalFlyMap?.addEventListener('click', () => {
                if (!activeModalAlert) return;
                alertModal.classList.add('hidden');
                if (activeModalAlert.lat !== null && activeModalAlert.lon !== null && map) {
                    map.flyTo({ center: [activeModalAlert.lon, activeModalAlert.lat], zoom: 9 });
                }
                if (activeModalAlert.vessel_id) {
                    selectVesselById(activeModalAlert.vessel_id);
                }
            });

            // Tab switching
            tabBtnActivities.addEventListener('click', () => {
                activeTab = 'activities';
                tabBtnActivities.className = 'px-3 py-1.5 rounded-xl font-bold text-xs bg-indigo-600 text-white shadow-xs transition flex items-center gap-1.5';
                tabBtnAlerts.className = 'px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700/70 transition flex items-center gap-1.5';
                panelActivities.classList.remove('hidden');
                panelAlerts.classList.add('hidden');
                updateTabCount();
            });

            tabBtnAlerts.addEventListener('click', () => {
                activeTab = 'alerts';
                tabBtnAlerts.className = 'px-3 py-1.5 rounded-xl font-bold text-xs bg-indigo-600 text-white shadow-xs transition flex items-center gap-1.5';
                tabBtnActivities.className = 'px-3 py-1.5 rounded-xl font-bold text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700/70 transition flex items-center gap-1.5';
                panelAlerts.classList.remove('hidden');
                panelActivities.classList.add('hidden');
                updateTabCount();
            });

            function updateTabCount() {
                if (activeTab === 'activities') {
                    tabSummaryCount.textContent = `${eventsList.length} peristiwa`;
                } else {
                    tabSummaryCount.textContent = `${alertsList.length} alerts`;
                }
            }

            // Feed Chip Buttons
            document.querySelectorAll('.feed-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.feed-chip').forEach(b => {
                        b.className = 'feed-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition';
                    });
                    btn.className = 'feed-chip px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-semibold transition';
                    currentFeedFilter = btn.dataset.type;
                    renderActivityFeed(eventsList);
                });
            });

            // Alert Chip Buttons
            document.querySelectorAll('.alert-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.alert-chip').forEach(b => {
                        b.className = 'alert-chip px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700/70 font-semibold transition';
                    });
                    btn.className = 'alert-chip px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-semibold transition';
                    currentAlertFilter = btn.dataset.severity;
                    renderAlertsFeed(alertsList);
                });
            });

            // Table Rendering
            function renderVesselsTable(vessels) {
                tableRecordCount.textContent = `${vessels.length} kapal`;

                if (vessels.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada data monitoring pada periode yang dipilih.
                                <span class="block text-[11px] text-slate-400 mt-1">Data monitoring bersumber dari satelit Global Fishing Watch dalam batas resmi ZEE Aceh BIG Layer 10. Tidak adanya data terdeteksi bukan berarti tidak ada kapal fisik di laut.</span>
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = '';
                vessels.forEach((v, index) => {
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-800/80 transition cursor-pointer border-b border-slate-800/70';

                    let statusBadge = 'bg-slate-800 text-slate-400 border border-slate-700';
                    if (v.status === 'LIVE') statusBadge = 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/80';
                    else if (v.status === 'RECENT') statusBadge = 'bg-sky-950/80 text-sky-300 border border-sky-800/80';

                    tr.innerHTML = `
                        <td class="py-2.5 px-2.5 font-mono text-slate-400 text-[11px]">${index + 1}</td>
                        <td class="py-2.5 px-2 font-bold text-slate-100">${escapeHtml(v.name || 'Unnamed')}</td>
                        <td class="py-2.5 px-2 font-mono text-slate-400">${escapeHtml(v.mmsi || '-')}</td>
                        <td class="py-2.5 px-2">${renderVesselFlag(v.flag)}</td>
                        <td class="py-2.5 px-2 text-slate-300">${escapeHtml(v.vessel_type || '-')}</td>
                        <td class="py-2.5 px-2">
                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ${statusBadge}">${escapeHtml(v.status || 'STALE')}</span>
                        </td>
                        <td class="py-2.5 px-2 text-right">
                            <button type="button" class="btn-select-vessel px-2 py-1 rounded bg-slate-800 hover:bg-indigo-600 text-slate-200 hover:text-white font-bold text-[10px] border border-slate-700 transition" data-id="${escapeHtml(v.id)}">
                                Pilih
                            </button>
                        </td>
                    `;

                    tr.addEventListener('click', () => selectVesselById(v.id));
                    tableBody.appendChild(tr);
                });
            }

            // Vessel Selection & Detail Intelligence
            function selectVesselById(vesselId) {
                const vessel = vesselsList.find(v => String(v.id) === String(vesselId));
                if (!vessel) return;

                activeSelectedVessel = vessel;

                vesselDetailEmpty.classList.add('hidden');
                vesselDetailBody.classList.remove('hidden');

                vesselNameEl.textContent = vessel.name || 'Unnamed Vessel';
                vesselMmsiEl.textContent = vessel.mmsi || '-';
                vesselImoEl.textContent = vessel.imo || '-';
                vesselFlagTypeEl.innerHTML = `<span class="inline-flex items-center gap-1.5 flex-wrap">${renderVesselFlag(vessel.flag)} <span class="text-slate-500">/</span> <span class="font-semibold text-slate-200">${escapeHtml(vessel.vessel_type || '-')}</span></span>`;

                detailStatusBadge.textContent = vessel.status || 'STALE';
                detailStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold ' +
                    (vessel.status === 'LIVE' ? 'bg-emerald-950/80 text-emerald-300 border border-emerald-800/80' : 'bg-slate-800 text-slate-300 border border-slate-700');

                btnLoadTrack.classList.remove('hidden');
                trackSummaryBox.classList.add('hidden');

                if (vessel.lat !== null && vessel.lon !== null && map) {
                    map.easeTo({ center: [vessel.lon, vessel.lat], zoom: 8 });
                }
            }

            // Load Movement Track for Selected Vessel (Stage 3)
            btnLoadTrack.addEventListener('click', async () => {
                if (!activeSelectedVessel) return;
                btnLoadTrack.textContent = 'Memuat Track...';
                btnLoadTrack.disabled = true;

                const startDate = filterStart.value;
                const endDate = filterEnd.value;

                try {
                    const res = await fetch(`/api/gfw/vessels/${activeSelectedVessel.id}/track?start_date=${startDate}&end_date=${endDate}`);
                    const json = await res.json();
                    btnLoadTrack.disabled = false;
                    btnLoadTrack.textContent = 'Lihat Track';

                    if (!json.success || !json.track) {
                        alert(json.message || 'Gagal memuat data lintasan.');
                        return;
                    }

                    // Display track stats
                    trackSummaryBox.classList.remove('hidden');
                    trackPointsCount.textContent = `${json.points_count} titik`;
                    trackFirstSeen.textContent = formatDate(json.first_detected);
                    trackLastSeen.textContent = formatDate(json.last_detected);

                    // Add track to map with distinct amber line
                    if (map.getSource('dashboard-track-src')) {
                        map.getSource('dashboard-track-src').setData(json.track);
                    } else {
                        map.addSource('dashboard-track-src', { type: 'geojson', data: json.track });

                        map.addLayer({
                            id: 'vessel-track-line',
                            type: 'line',
                            source: 'dashboard-track-src',
                            filter: ['==', '$type', 'LineString'],
                            paint: {
                                'line-color': '#f59e0b',
                                'line-width': 3,
                                'line-opacity': 0.95
                            }
                        });

                        map.addLayer({
                            id: 'vessel-track-points',
                            type: 'circle',
                            source: 'dashboard-track-src',
                            filter: ['==', '$type', 'Point'],
                            paint: {
                                'circle-radius': 4,
                                'circle-color': '#d97706',
                                'circle-stroke-width': 1.5,
                                'circle-stroke-color': '#ffffff'
                            }
                        });
                    }

                    // Fit map bounds to track
                    if (json.track.bbox && map) {
                        const bbox = json.track.bbox;
                        map.fitBounds([[bbox[0], bbox[1]], [bbox[2], bbox[3]]], { padding: 40, maxZoom: 11 });
                    }

                } catch (e) {
                    btnLoadTrack.disabled = false;
                    btnLoadTrack.textContent = 'Lihat Track';
                    alert('Koneksi terputus saat mengambil data track: ' + e.message);
                }
            });

            // Filter Buttons
            btnApply.addEventListener('click', () => fetchDashboardData(false));
            btnReset.addEventListener('click', () => {
                filterSearch.value = '';
                filterType.value = '';
                filterStatus.value = '';
                fetchDashboardData(false);
            });
            btnRetry?.addEventListener('click', () => fetchDashboardData(true));
            btnRefresh?.addEventListener('click', () => fetchDashboardData(true));

            // Helpers
            function formatDate(str) {
                if (!str) return '-';
                try {
                    const d = new Date(str);
                    return d.toLocaleString('id-ID', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit',
                        timeZone: APP_TIMEZONE,
                        timeZoneName: 'short'
                    });
                } catch { return str; }
            }

            function formatAge(seconds) {
                if (seconds === null || seconds === undefined) return '-';
                if (seconds < 60) return `${seconds} detik yang lalu`;
                if (seconds < 3600) return `${Math.floor(seconds / 60)} menit yang lalu`;
                if (seconds < 86400) return `${Math.floor(seconds / 3600)} jam yang lalu`;
                return `${Math.floor(seconds / 86400)} hari yang lalu`;
            }

            function roundCoord(c) {
                if (c === null || c === undefined || isNaN(c)) return '-';
                return Number(c).toFixed(4);
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

            // Country flag dictionary: ISO-3 alpha3 -> { alpha2, name, emoji }
            const COUNTRY_FLAG_MAP = {
                // Southeast Asia & East Asia
                'IDN': { alpha2: 'id', name: 'Indonesia', emoji: '🇮🇩' },
                'MYS': { alpha2: 'my', name: 'Malaysia', emoji: '🇲🇾' },
                'THA': { alpha2: 'th', name: 'Thailand', emoji: '🇹🇭' },
                'VNM': { alpha2: 'vn', name: 'Vietnam', emoji: '🇻🇳' },
                'SGP': { alpha2: 'sg', name: 'Singapura', emoji: '🇸🇬' },
                'PHL': { alpha2: 'ph', name: 'Filipina', emoji: '🇵🇭' },
                'BRN': { alpha2: 'bn', name: 'Brunei Darussalam', emoji: '🇧🇳' },
                'KHM': { alpha2: 'kh', name: 'Kamboja', emoji: '🇰🇭' },
                'MMR': { alpha2: 'mm', name: 'Myanmar', emoji: '🇲🇲' },
                'LAO': { alpha2: 'la', name: 'Laos', emoji: '🇱🇦' },
                'TLS': { alpha2: 'tl', name: 'Timor-Leste', emoji: '🇹🇱' },
                'CHN': { alpha2: 'cn', name: 'Tiongkok / China', emoji: '🇨🇳' },
                'TWN': { alpha2: 'tw', name: 'Taiwan', emoji: '🇹🇼' },
                'JPN': { alpha2: 'jp', name: 'Jepang', emoji: '🇯🇵' },
                'KOR': { alpha2: 'kr', name: 'Korea Selatan', emoji: '🇰🇷' },
                'PRK': { alpha2: 'kp', name: 'Korea Utara', emoji: '🇰🇵' },
                'HKG': { alpha2: 'hk', name: 'Hong Kong', emoji: '🇭🇰' },

                // South Asia & Indian Ocean
                'IND': { alpha2: 'in', name: 'India', emoji: '🇮🇳' },
                'BGD': { alpha2: 'bd', name: 'Bangladesh', emoji: '🇧🇩' },
                'LKA': { alpha2: 'lk', name: 'Sri Lanka', emoji: '🇱🇰' },
                'MDV': { alpha2: 'mv', name: 'Maldives', emoji: '🇲🇻' },
                'PAK': { alpha2: 'pk', name: 'Pakistan', emoji: '🇵🇰' },
                'SYC': { alpha2: 'sc', name: 'Seychelles', emoji: '🇸🇨' },
                'MUS': { alpha2: 'mu', name: 'Mauritius', emoji: '🇲🇺' },
                'MDG': { alpha2: 'mg', name: 'Madagaskar', emoji: '🇲🇬' },

                // Common Maritime Flags of Convenience (FoC) & Shipping
                'PAN': { alpha2: 'pa', name: 'Panama', emoji: '🇵🇦' },
                'LBR': { alpha2: 'lr', name: 'Liberia', emoji: '🇱🇷' },
                'MHL': { alpha2: 'mh', name: 'Kepulauan Marshall', emoji: '🇲🇭' },
                'BHS': { alpha2: 'bs', name: 'Bahamas', emoji: '🇧🇸' },
                'MLT': { alpha2: 'mt', name: 'Malta', emoji: '🇲🇹' },
                'CYP': { alpha2: 'cy', name: 'Siprus', emoji: '🇨🇾' },
                'VUT': { alpha2: 'vu', name: 'Vanuatu', emoji: '🇻🇺' },
                'BLZ': { alpha2: 'bz', name: 'Belize', emoji: '🇧🇿' },
                'HND': { alpha2: 'hn', name: 'Honduras', emoji: '🇭🇳' },
                'VCT': { alpha2: 'vc', name: 'St. Vincent & Grenadines', emoji: '🇻🇨' },
                'KNA': { alpha2: 'kn', name: 'St. Kitts & Nevis', emoji: '🇰🇳' },
                'ATG': { alpha2: 'ag', name: 'Antigua & Barbuda', emoji: '🇦🇬' },
                'CYM': { alpha2: 'ky', name: 'Kepulauan Cayman', emoji: '🇰🇾' },
                'BMU': { alpha2: 'bm', name: 'Bermuda', emoji: '🇧🇲' },
                'GIB': { alpha2: 'gi', name: 'Gibraltar', emoji: '🇬🇮' },
                'COK': { alpha2: 'ck', name: 'Kepulauan Cook', emoji: '🇨🇰' },
                'TUV': { alpha2: 'tv', name: 'Tuvalu', emoji: '🇹🇻' },
                'KIR': { alpha2: 'ki', name: 'Kiribati', emoji: '🇰🇮' },
                'TGO': { alpha2: 'tg', name: 'Togo', emoji: '🇹🇬' },
                'SLE': { alpha2: 'sl', name: 'Sierra Leone', emoji: '🇸🇱' },
                'COM': { alpha2: 'km', name: 'Komoro', emoji: '🇰🇲' },
                'PLW': { alpha2: 'pw', name: 'Palau', emoji: '🇵🇼' },
                'WSM': { alpha2: 'ws', name: 'Samoa', emoji: '🇼🇸' },
                'TON': { alpha2: 'to', name: 'Tonga', emoji: '🇹🇴' },
                'FJI': { alpha2: 'fj', name: 'Fiji', emoji: '🇫🇯' },
                'PNG': { alpha2: 'pg', name: 'Papua Nugini', emoji: '🇵🇬' },
                'SLB': { alpha2: 'sb', name: 'Kepulauan Solomon', emoji: '🇸🇧' },
                'FSM': { alpha2: 'fm', name: 'Mikronesia', emoji: '🇫🇲' },
                'NRU': { alpha2: 'nr', name: 'Nauru', emoji: '🇳🇷' },

                // Americas & Europe & Oceania
                'USA': { alpha2: 'us', name: 'Amerika Serikat', emoji: '🇺🇸' },
                'GBR': { alpha2: 'gb', name: 'Inggris Raya', emoji: '🇬🇧' },
                'RUS': { alpha2: 'ru', name: 'Rusia', emoji: '🇷🇺' },
                'AUS': { alpha2: 'au', name: 'Australia', emoji: '🇦🇺' },
                'NZL': { alpha2: 'nz', name: 'Selandia Baru', emoji: '🇳🇿' },
                'ESP': { alpha2: 'es', name: 'Spanyol', emoji: '🇪🇸' },
                'FRA': { alpha2: 'fr', name: 'Prancis', emoji: '🇫🇷' },
                'DEU': { alpha2: 'de', name: 'Jerman', emoji: '🇩🇪' },
                'NLD': { alpha2: 'nl', name: 'Belanda', emoji: '🇳🇱' },
                'ITA': { alpha2: 'it', name: 'Italia', emoji: '🇮🇹' },
                'PRT': { alpha2: 'pt', name: 'Portugal', emoji: '🇵🇹' },
                'GRC': { alpha2: 'gr', name: 'Yunani', emoji: '🇬🇷' },
                'NOR': { alpha2: 'no', name: 'Norwegia', emoji: '🇳🇴' },
                'DNK': { alpha2: 'dk', name: 'Denmark', emoji: '🇩🇰' },
                'SWE': { alpha2: 'se', name: 'Swedia', emoji: '🇸🇪' },
                'FIN': { alpha2: 'fi', name: 'Finlandia', emoji: '🇫🇮' },
                'TUR': { alpha2: 'tr', name: 'Turki', emoji: '🇹🇷' },
                'EGY': { alpha2: 'eg', name: 'Mesir', emoji: '🇪🇬' },
                'ZAF': { alpha2: 'za', name: 'Afrika Selatan', emoji: '🇿🇦' },
                'IRN': { alpha2: 'ir', name: 'Iran', emoji: '🇮🇷' },
                'SAU': { alpha2: 'sa', name: 'Arab Saudi', emoji: '🇸🇦' },
                'ARE': { alpha2: 'ae', name: 'Uni Emirat Arab', emoji: '🇦🇪' },
                'OMN': { alpha2: 'om', name: 'Oman', emoji: '🇴🇲' },
                'YEM': { alpha2: 'ye', name: 'Yaman', emoji: '🇾🇪' },
                'QAT': { alpha2: 'qa', name: 'Qatar', emoji: '🇶🇦' },
                'KWT': { alpha2: 'kw', name: 'Kuwait', emoji: '🇰🇼' },
                'BHR': { alpha2: 'bh', name: 'Bahrain', emoji: '🇧🇭' },
                'BRA': { alpha2: 'br', name: 'Brasil', emoji: '🇧🇷' },
                'ARG': { alpha2: 'ar', name: 'Argentina', emoji: '🇦🇷' },
                'CHL': { alpha2: 'cl', name: 'Chili', emoji: '🇨🇱' },
                'PER': { alpha2: 'pe', name: 'Peru', emoji: '🇵🇪' },
                'ECU': { alpha2: 'ec', name: 'Ekuador', emoji: '🇪🇨' },
                'COL': { alpha2: 'co', name: 'Kolombia', emoji: '🇨🇴' },
                'MEX': { alpha2: 'mx', name: 'Meksiko', emoji: '🇲🇽' },
                'CAN': { alpha2: 'ca', name: 'Kanada', emoji: '🇨🇦' },
            };

            function getCountryFlagInfo(rawFlag) {
                if (!rawFlag || typeof rawFlag !== 'string') {
                    return { alpha2: '', name: 'Tidak diketahui', emoji: '🌐' };
                }
                const code = rawFlag.trim().toUpperCase();
                if (COUNTRY_FLAG_MAP[code]) {
                    return COUNTRY_FLAG_MAP[code];
                }
                if (code.length === 2) {
                    const matchEntry = Object.entries(COUNTRY_FLAG_MAP).find(([k, v]) => v.alpha2.toUpperCase() === code);
                    if (matchEntry) return matchEntry[1];
                    try {
                        const codePoints = [...code].map(c => 127397 + c.charCodeAt(0));
                        return {
                            alpha2: code.toLowerCase(),
                            name: code,
                            emoji: String.fromCodePoint(...codePoints)
                        };
                    } catch (e) {
                        return { alpha2: code.toLowerCase(), name: code, emoji: '🌐' };
                    }
                }
                return { alpha2: '', name: code, emoji: '🌐' };
            }

            function renderVesselFlag(flagCode) {
                if (!flagCode || flagCode === '-' || flagCode === 'Not available' || flagCode === 'Unknown' || flagCode === 'N/A') {
                    return `<span class="inline-flex items-center gap-1 text-slate-400 font-mono text-[11px]">—</span>`;
                }
                const info = getCountryFlagInfo(flagCode);
                const alpha2 = info.alpha2 ? info.alpha2.toLowerCase() : '';
                const name = info.name || flagCode;
                const flagImg = alpha2 ? `<img src="https://flagcdn.com/20x15/${alpha2}.png" srcset="https://flagcdn.com/40x30/${alpha2}.png 2x" width="18" height="13" alt="${escapeHtml(flagCode)}" class="rounded-[2px] object-cover shadow-2xs shrink-0" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.classList.remove('hidden');" loading="lazy">` : '';
                const flagEmoji = `<span class="${alpha2 ? 'hidden ' : ''}text-xs leading-none shrink-0">${info.emoji}</span>`;

                return `<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-slate-800/90 hover:bg-slate-700/80 text-slate-200 border border-slate-700/80 transition-colors cursor-default" title="${escapeHtml(name)} (${escapeHtml(flagCode)})">
                    ${flagImg}
                    ${flagEmoji}
                    <span class="font-mono font-bold text-[11px] text-slate-200">${escapeHtml(flagCode)}</span>
                </span>`;
            }

            initMap();
        });
    </script>
</x-app-layout>
