<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-600 via-indigo-600 to-blue-700 text-white flex items-center justify-center shadow-xs">
                    <span class="text-lg">🛰️</span>
                </div>
                <div>
                    <h1 class="text-base font-bold text-slate-800 tracking-tight leading-tight">
                        {{ __('GFW Operational Dashboard') }}
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        {{ __('ZEE Indonesia — Kawasan Aceh') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('gfw.vessels') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
                    <span>🚢</span>
                    <span>{{ __('Vessel Observatory') }}</span>
                </a>
                <a href="{{ route('dashboard.gis') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
                    <span>🗺️</span>
                    <span>{{ __('Peta GIS') }}</span>
                </a>
                <a href="{{ route('gfw.monitoring') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-2xs flex items-center gap-1.5">
                    <span>📡</span>
                    <span>{{ __('Workspace Satelit') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- MapLibre GL JS CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" crossorigin=""/>
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" crossorigin=""></script>

    <div class="space-y-6">
        {{-- Provenance Banner & Live Controls --}}
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-indigo-900/40">
            <div class="absolute right-4 -bottom-6 text-9xl opacity-5 pointer-events-none select-none">🌐</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="max-w-3xl space-y-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[11px] font-semibold">
                            <span>🏛️</span>
                            <span>Batas ZEE: BIG</span>
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
                        <span>GFW OPERATIONAL DASHBOARD</span>
                        <span class="text-indigo-400 font-light">|</span>
                        <span class="text-indigo-200 text-base font-semibold">ZEE Indonesia — Kawasan Aceh</span>
                    </h2>

                    <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-2xl">
                        Pusat kendali operasional pemantauan terpadu aktivitas maritim perikanan di wilayah ZEE Aceh. Mengintegrasikan deteksi kehadiran kapal, klasifikasi armada berbendera, indikasi penangkapan (<em>Fishing Activity</em>), perjumpaan (<em>Encounter</em>), dan persinggahan pelabuhan (<em>Port Visit</em>).
                    </p>
                </div>

                {{-- Live Monitoring & Timing Card --}}
                <div class="px-4 py-3 rounded-xl bg-slate-900/90 backdrop-blur-xs border border-indigo-500/30 text-xs max-w-sm shrink-0 space-y-2.5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold text-slate-300 text-xs flex items-center gap-1.5">
                            <span id="live-pulse-indicator" class="w-2.5 h-2.5 rounded-full bg-slate-500 inline-block"></span>
                            <span>Live Monitoring</span>
                        </span>
                        <button type="button" id="btn-toggle-live" class="px-3 py-1 rounded-lg text-xs font-bold transition bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700">
                            OFF
                        </button>
                    </div>

                    <div class="space-y-1 text-[11px] border-t border-slate-800 pt-2">
                        <div class="flex items-center justify-between text-slate-400">
                            <span>Status Pembaruan:</span>
                            <span id="live-status-text" class="text-slate-200 font-mono">Manual (Siap)</span>
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

        {{-- Fallback / Warning Error Notice --}}
        <div id="gfw-dashboard-error-notice" class="hidden p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-lg">⚠️</span>
                <div>
                    <span class="font-bold text-amber-100">Gagal memperbarui data dari GFW API.</span>
                    <p class="text-[11px] text-amber-300 mt-0.5">Menampilkan dataset berhasil terakhir. Data operasional tetap aman dan tidak direset ke 0.</p>
                </div>
            </div>
            <button type="button" id="btn-retry-fetch" class="px-3.5 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs transition shrink-0">
                🔄 Coba Lagi
            </button>
        </div>

        {{-- KPI Row: 6 Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3.5">
            {{-- Detected Vessels --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Detected Vessels</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-detected-vessels" class="text-xl font-black text-slate-800">...</h3>
                    <span class="text-xs">🚢</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Armada di ZEE Aceh</p>
            </div>

            {{-- Live / Recent --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Live / Recent</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-live-recent" class="text-xl font-black text-emerald-600">...</h3>
                    <span class="text-xs">🟢</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Observasi &lt; 72 jam</p>
            </div>

            {{-- Fishing Activity --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-600">Fishing Activity</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-fishing-activity" class="text-xl font-black text-cyan-600">...</h3>
                    <span class="text-xs">🎣</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Indikasi penangkapan</p>
            </div>

            {{-- Encounters --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Encounters</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-encounters" class="text-xl font-black text-amber-600">...</h3>
                    <span class="text-xs">🤝</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Kedekatan antar kapal</p>
            </div>

            {{-- Loitering --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600">Loitering</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-loitering" class="text-xl font-black text-purple-600">...</h3>
                    <span class="text-xs">⏳</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Pola menunggu di laut</p>
            </div>

            {{-- Port Visits --}}
            <div class="bg-white p-3.5 rounded-2xl shadow-2xs border border-slate-200/80">
                <span class="text-[10px] font-bold uppercase tracking-wider text-sky-600">Port Visits</span>
                <div class="flex items-baseline justify-between mt-1">
                    <h3 id="kpi-port-visits" class="text-xl font-black text-sky-600">...</h3>
                    <span class="text-xs">⚓</span>
                </div>
                <p class="text-[10px] text-slate-400 mt-0.5">Persinggahan pelabuhan</p>
            </div>
        </div>

        {{-- Filter Control Bar --}}
        <div class="bg-white p-4 rounded-2xl shadow-2xs border border-slate-200/80 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-12 gap-3 items-end">
                {{-- Search Vessel --}}
                <div class="md:col-span-3">
                    <label for="filter-search" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Cari Kapal (Nama / MMSI / IMO)') }}
                    </label>
                    <input type="text"
                           id="filter-search"
                           placeholder="Ketik nama kapal, MMSI, atau IMO..."
                           class="w-full text-xs py-2 rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500">
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

                {{-- Vessel Type --}}
                <div class="md:col-span-2">
                    <label for="filter-vessel-type" class="block text-xs font-semibold text-slate-700 mb-1">
                        {{ __('Tipe Kapal') }}
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

                {{-- Actions --}}
                <div class="md:col-span-2 flex items-center gap-2">
                    <button type="button" id="btn-reset-filters" class="w-1/2 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold transition">
                        Reset
                    </button>
                    <button type="button" id="btn-apply-filters" class="w-1/2 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold transition shadow-xs flex items-center justify-center gap-1">
                        <span id="filter-spinner" class="hidden">🔄</span>
                        <span>Filter</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Main Map with Floating Layer Controls --}}
        <div class="bg-white p-3 rounded-2xl shadow-2xs border border-slate-200/80 relative">
            {{-- Map Canvas --}}
            <div id="gfw-dashboard-map" class="w-full h-[540px] rounded-xl overflow-hidden bg-slate-950 z-0"></div>

            {{-- Floating Layer Control Box --}}
            <div class="absolute top-6 right-6 z-10 bg-slate-900/90 text-white text-xs p-3.5 rounded-xl backdrop-blur-md border border-slate-700 shadow-xl space-y-2.5 max-w-xs">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-1.5">
                    <span class="font-bold text-slate-200 flex items-center gap-1.5">
                        <span>🗺️</span>
                        <span>Kontrol Lapisan Peta</span>
                    </span>
                    <span class="text-[10px] text-slate-400">MapLibre GL</span>
                </div>

                <div class="space-y-1.5 text-[11px]">
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-zee" checked class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-0">
                        <span class="font-semibold text-sky-400">Batas ZEE Aceh (BIG)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-vessels" checked class="rounded border-slate-600 bg-slate-800 text-indigo-500 focus:ring-0">
                        <span class="text-slate-200">Posisi Kapal (Vessels)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-track" class="rounded border-slate-600 bg-slate-800 text-cyan-400 focus:ring-0">
                        <span class="text-cyan-300">Histori Lintasan (Track)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-fishing" class="rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-0">
                        <span class="text-emerald-300">Fishing Activity</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-encounters" class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-0">
                        <span class="text-amber-300">Encounters</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-loitering" class="rounded border-slate-600 bg-slate-800 text-purple-500 focus:ring-0">
                        <span class="text-purple-300">Loitering</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer hover:text-indigo-300">
                        <input type="checkbox" id="layer-opt-port-visits" class="rounded border-slate-600 bg-slate-800 text-sky-500 focus:ring-0">
                        <span class="text-sky-300">Port Visits</span>
                    </label>
                </div>
            </div>

            {{-- Map Legend Footer --}}
            <div class="mt-2.5 px-2 flex flex-wrap items-center justify-between gap-3 text-[11px] text-slate-600 border-t border-slate-100 pt-2">
                <div class="flex flex-wrap items-center gap-3">
                    <span class="font-semibold text-slate-700">Simbol:</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Fishing</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Carrier</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Tanker/Bunker</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-sky-500"></span> Cargo</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-slate-500"></span> Other</span>
                    <span class="flex items-center gap-1"><span class="w-3.5 h-1 bg-cyan-400 rounded"></span> Vessel Track</span>
                    <span class="flex items-center gap-1"><span class="w-3.5 h-1 bg-blue-600 rounded"></span> Garis ZEE (BIG)</span>
                </div>
                <div class="text-slate-400 font-mono text-[10px]">
                    Proyeksi: EPSG:4326 | Sumber Garis: Badan Informasi Geospasial
                </div>
            </div>
        </div>

        {{-- Bottom Split Layout: Activity Feed & Alerts vs Vessel Table & Detail --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            {{-- Left Column (5 cols): Activity Feed & Alerts --}}
            <div class="lg:col-span-5 space-y-4">
                {{-- Activity Feed Container --}}
                <div class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-base">⚡</span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Recent Activities & Alerts</h3>
                        </div>
                        <span id="activity-feed-count" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">
                            0 events
                        </span>
                    </div>

                    {{-- Activity Filter Chips --}}
                    <div class="flex flex-wrap items-center gap-1 text-[11px]" id="feed-filter-chips">
                        <button type="button" data-type="all" class="feed-chip px-2.5 py-1 rounded-lg bg-indigo-600 text-white font-semibold transition">Semua</button>
                        <button type="button" data-type="fishing" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">Fishing</button>
                        <button type="button" data-type="encounter" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">Encounter</button>
                        <button type="button" data-type="loitering" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">Loitering</button>
                        <button type="button" data-type="port" class="feed-chip px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold transition">Port</button>
                    </div>

                    {{-- Feed List --}}
                    <div id="activity-feed-list" class="space-y-2 max-h-[460px] overflow-y-auto pr-1 text-xs">
                        <div class="py-8 text-center text-slate-400">
                            <span class="animate-pulse">Memuat aktivitas...</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column (7 cols): Vessel Table & Detail Intelligence --}}
            <div class="lg:col-span-7 space-y-4">
                {{-- Vessel Detail & Intelligence Card --}}
                <div id="dashboard-vessel-detail" class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📋</span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Vessel Profile & Intelligence</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <span id="detail-status-badge" class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600">
                                Pilih Kapal
                            </span>
                            <button type="button" id="btn-load-track" class="hidden px-2.5 py-1 rounded-lg bg-cyan-600 hover:bg-cyan-700 text-white font-bold text-[11px] transition shadow-xs flex items-center gap-1">
                                <span>🗺️</span>
                                <span>Lihat Track</span>
                            </button>
                        </div>
                    </div>

                    <div id="vessel-detail-empty" class="py-6 text-center text-slate-400">
                        <span class="text-2xl block mb-1">🚢</span>
                        <p class="text-xs font-medium">Klik kapal pada peta atau tabel untuk melihat data intelligence & riwayat posisi</p>
                    </div>

                    <div id="vessel-detail-body" class="hidden space-y-3">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block">Nama Kapal</span>
                                <span id="vessel-name" class="font-bold text-slate-800 block truncate">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block">MMSI</span>
                                <span id="vessel-mmsi" class="font-mono text-slate-700 block">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block">IMO</span>
                                <span id="vessel-imo" class="font-mono text-slate-700 block">-</span>
                            </div>
                            <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-[10px] text-slate-400 block">Bendera / Tipe</span>
                                <span id="vessel-flag-type" class="font-semibold text-slate-700 block truncate">-</span>
                            </div>
                        </div>

                        {{-- Track Info Box (Shown when track loaded) --}}
                        <div id="track-summary-box" class="hidden p-3 rounded-xl bg-cyan-50 border border-cyan-200 text-xs text-cyan-950 space-y-1">
                            <div class="flex items-center justify-between font-bold text-cyan-900 pb-1 border-b border-cyan-200/60">
                                <span>Histori Pergerakan (Observed Track)</span>
                                <span id="track-points-count" class="font-mono text-[11px] bg-cyan-200/60 px-1.5 py-0.5 rounded">0 titik</span>
                            </div>
                            <div class="grid grid-cols-2 gap-2 text-[11px] pt-1">
                                <div>
                                    <span class="text-cyan-700">Pertama Terdeteksi:</span>
                                    <span id="track-first-seen" class="font-medium text-cyan-900 ml-1">-</span>
                                </div>
                                <div>
                                    <span class="text-cyan-700">Terakhir Terdeteksi:</span>
                                    <span id="track-last-seen" class="font-medium text-cyan-900 ml-1">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Vessel Directory Table --}}
                <div class="bg-white rounded-2xl p-4 shadow-2xs border border-slate-200/80 space-y-3">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📑</span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Daftar Kapal di ZEE Aceh</h3>
                        </div>
                        <div class="text-[11px] text-slate-500 font-medium" id="table-record-count">
                            0 kapal
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider text-slate-500 border-b border-slate-200">
                                <tr>
                                    <th class="py-2 px-2.5">Kapal</th>
                                    <th class="py-2 px-2">MMSI</th>
                                    <th class="py-2 px-2">Bendera</th>
                                    <th class="py-2 px-2">Tipe</th>
                                    <th class="py-2 px-2">Status</th>
                                    <th class="py-2 px-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="dashboard-table-body" class="divide-y divide-slate-100">
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-slate-400">
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

    {{-- Frontend Javascript Controller --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // State
            let map = null;
            let vesselsList = [];
            let eventsList = [];
            let activeSelectedVessel = null;
            let isLiveActive = false;
            let liveTimer = null;
            let previousVesselsMap = new Map();
            let currentFeedFilter = 'all';

            // DOM References
            const kpiDetected = document.getElementById('kpi-detected-vessels');
            const kpiLive = document.getElementById('kpi-live-recent');
            const kpiFishing = document.getElementById('kpi-fishing-activity');
            const kpiEncounters = document.getElementById('kpi-encounters');
            const kpiLoitering = document.getElementById('kpi-loitering');
            const kpiPort = document.getElementById('kpi-port-visits');

            const filterSearch = document.getElementById('filter-search');
            const filterStart = document.getElementById('filter-start-date');
            const filterEnd = document.getElementById('filter-end-date');
            const filterType = document.getElementById('filter-vessel-type');
            const filterStatus = document.getElementById('filter-status');
            const btnApply = document.getElementById('btn-apply-filters');
            const btnReset = document.getElementById('btn-reset-filters');
            const filterSpinner = document.getElementById('filter-spinner');

            const btnToggleLive = document.getElementById('btn-toggle-live');
            const livePulse = document.getElementById('live-pulse-indicator');
            const liveStatusText = document.getElementById('live-status-text');
            const metaLastUpdated = document.getElementById('meta-last-updated');
            const metaDataAge = document.getElementById('meta-data-age');
            const metaDeltaContainer = document.getElementById('meta-delta-container');
            const metaDeltaBadge = document.getElementById('meta-delta-badge');

            const errorNotice = document.getElementById('gfw-dashboard-error-notice');
            const btnRetry = document.getElementById('btn-retry-fetch');

            const activityFeedList = document.getElementById('activity-feed-list');
            const activityFeedCount = document.getElementById('activity-feed-count');
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
            const optEncounters = document.getElementById('layer-opt-encounters');
            const optLoitering = document.getElementById('layer-opt-loitering');
            const optPortVisits = document.getElementById('layer-opt-port-visits');

            // Initialize MapLibre
            function initMap() {
                map = new maplibregl.Map({
                    container: 'gfw-dashboard-map',
                    style: {
                        version: 8,
                        glyphs: 'https://demotiles.maplibre.org/font/{fontstack}/{range}.pbf',
                        sources: {
                            'osm-tiles': {
                                type: 'raster',
                                tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'],
                                tileSize: 256,
                                attribution: '&copy; OpenStreetMap contributors'
                            }
                        },
                        layers: [
                            {
                                id: 'osm-layer',
                                type: 'raster',
                                source: 'osm-tiles',
                                minzoom: 0,
                                maxzoom: 19
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

            // Load BIG ZEE Aceh boundary lines and polygon
            async function loadBigZeeBoundaries() {
                try {
                    const res = await fetch('/api/gfw/aoi/zee-indonesia-aceh?geojson=1');
                    const json = await res.json();
                    if (json.success && json.geojson && !map.getSource('big-zee-poly')) {
                        map.addSource('big-zee-poly', { type: 'geojson', data: json.geojson });
                        map.addLayer({
                            id: 'big-zee-fill',
                            type: 'fill',
                            source: 'big-zee-poly',
                            paint: { 'fill-color': '#0284c7', 'fill-opacity': 0.08 }
                        });
                        map.addLayer({
                            id: 'big-zee-outline',
                            type: 'line',
                            source: 'big-zee-poly',
                            paint: { 'line-color': '#0284c7', 'line-width': 2, 'line-dasharray': [3, 1] }
                        });
                    }
                } catch (e) {
                    console.warn('Gagal memuat poligon ZEE:', e);
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
                            paint: { 'line-color': '#2563eb', 'line-width': 2.5 }
                        });
                    }
                } catch (e) {
                    console.warn('Gagal memuat garis batas BIG:', e);
                }
            }

            // Main Dashboard Fetcher
            async function fetchDashboardData() {
                filterSpinner.classList.remove('hidden');

                const params = new URLSearchParams({
                    start: filterStart.value,
                    end: filterEnd.value,
                    limit: 100,
                });

                if (filterType.value) params.append('vessel_type', filterType.value);
                if (filterSearch.value.trim()) params.append('search', filterSearch.value.trim());

                try {
                    const response = await fetch(`/api/gfw/dashboard?${params.toString()}`);
                    const json = await response.json();
                    filterSpinner.classList.add('hidden');

                    if (!response.ok || !json.success) {
                        handleFetchFailure(json.message || 'Gagal memuat data dari GFW.');
                        return;
                    }

                    // Success - hide error notice
                    errorNotice.classList.add('hidden');

                    // Delta detection
                    detectDatasetDeltas(json.vessels || []);

                    // Update KPIs
                    kpiDetected.textContent = (json.kpi?.detected_vessels ?? 0).toLocaleString();
                    kpiLive.textContent = (json.kpi?.live_recent ?? 0).toLocaleString();
                    kpiFishing.textContent = (json.kpi?.fishing_activity ?? 0).toLocaleString();
                    kpiEncounters.textContent = (json.kpi?.encounters ?? 0).toLocaleString();
                    kpiLoitering.textContent = (json.kpi?.loitering ?? 0).toLocaleString();
                    kpiPort.textContent = (json.kpi?.port_visits ?? 0).toLocaleString();

                    // Update Timers & Metadata
                    metaLastUpdated.textContent = formatDate(json.last_updated);
                    const ageSec = json.data_age_seconds ?? 0;
                    metaDataAge.textContent = formatAge(ageSec);

                    // Store lists
                    vesselsList = json.vessels || [];
                    eventsList = json.activity_feed || [];

                    // Filter status locally if requested
                    let displayVessels = vesselsList;
                    if (filterStatus.value) {
                        displayVessels = vesselsList.filter(v => (v.status || '').toUpperCase() === filterStatus.value);
                    }

                    // Update Visuals
                    updateMapLayers(displayVessels, json.events || []);
                    renderActivityFeed(eventsList);
                    renderVesselsTable(displayVessels);

                } catch (err) {
                    filterSpinner.classList.add('hidden');
                    handleFetchFailure(err.message);
                }
            }

            // Error Resilience - Last Successful Data fallback
            function handleFetchFailure(msg) {
                errorNotice.classList.remove('hidden');
                liveStatusText.textContent = 'Gagal memperbarui (Menampilkan data terakhir)';
                liveStatusText.classList.add('text-amber-400');
            }

            // Delta Detection between fetches
            function detectDatasetDeltas(newVessels) {
                if (previousVesselsMap.size > 0) {
                    let newCount = 0;
                    let movedCount = 0;

                    const currentIds = new Set();
                    newVessels.forEach(v => {
                        currentIds.add(String(v.id));
                        if (!previousVesselsMap.has(String(v.id))) {
                            newCount++;
                        } else {
                            const prev = previousVesselsMap.get(String(v.id));
                            if (prev.lat !== v.lat || prev.lon !== v.lon) {
                                movedCount++;
                            }
                        }
                    });

                    let missingCount = 0;
                    previousVesselsMap.forEach((_, id) => {
                        if (!currentIds.has(id)) missingCount++;
                    });

                    if (newCount > 0 || movedCount > 0 || missingCount > 0) {
                        const parts = [];
                        if (newCount > 0) parts.push(`+${newCount} kapal baru`);
                        if (movedCount > 0) parts.push(`${movedCount} posisi berubah`);
                        if (missingCount > 0) parts.push(`${missingCount} tidak terdeteksi pada snapshot terkini`);

                        metaDeltaBadge.textContent = parts.join(' • ');
                        metaDeltaContainer.classList.remove('hidden');
                    }
                }

                // Update Map
                previousVesselsMap.clear();
                newVessels.forEach(v => previousVesselsMap.set(String(v.id), v));
            }

            // MapLibre GeoJSON update with Clustering
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
                            'circle-opacity': 0.85,
                            'circle-stroke-width': 2,
                            'circle-stroke-color': '#ffffff'
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
                                '#64748b'
                            ],
                            'circle-stroke-width': 1.5,
                            'circle-stroke-color': '#ffffff'
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
                                <div class="p-2 space-y-1 text-xs">
                                    <div class="font-bold text-slate-800">${escapeHtml(props.name)}</div>
                                    <div class="text-[11px] text-slate-500">MMSI: ${escapeHtml(props.mmsi)} • Tipe: ${escapeHtml(props.type)}</div>
                                    <div class="text-[10px] text-indigo-600 font-semibold">${escapeHtml(props.status)} • ${escapeHtml(props.activity)}</div>
                                </div>
                            `)
                            .addTo(map);
                    });
                }

                // 2. Activity Events Points
                const eventsGeoJson = {
                    type: 'FeatureCollection',
                    features: events
                        .filter(e => e.position && e.position.lat !== null && e.position.lon !== null)
                        .map(e => ({
                            type: 'Feature',
                            geometry: { type: 'Point', coordinates: [e.position.lon, e.position.lat] },
                            properties: {
                                id: e.id,
                                type: e.type || 'activity',
                                vessel: e.vessel?.name || 'Kapal',
                                time: e.start || e.end || ''
                            }
                        }))
                };

                if (map.getSource('dashboard-events-src')) {
                    map.getSource('dashboard-events-src').setData(eventsGeoJson);
                } else {
                    map.addSource('dashboard-events-src', { type: 'geojson', data: eventsGeoJson });
                    map.addLayer({
                        id: 'events-layer',
                        type: 'circle',
                        source: 'dashboard-events-src',
                        layout: { visibility: 'none' }, // Default OFF per requirement
                        paint: {
                            'circle-radius': 4.5,
                            'circle-color': '#e11d48',
                            'circle-stroke-width': 1,
                            'circle-stroke-color': '#ffffff'
                        }
                    });
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

                optFishing?.addEventListener('change', e => setVis('events-layer', e.target.checked));
                optEncounters?.addEventListener('change', e => setVis('events-layer', e.target.checked));
                optLoitering?.addEventListener('change', e => setVis('events-layer', e.target.checked));
                optPortVisits?.addEventListener('change', e => setVis('events-layer', e.target.checked));
            }
            bindLayerToggles();

            // Activity Feed Rendering
            function renderActivityFeed(feed) {
                activityFeedCount.textContent = `${feed.length} events`;

                const filtered = feed.filter(item => {
                    if (currentFeedFilter === 'all') return true;
                    return (item.activity || '').toLowerCase().includes(currentFeedFilter);
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
                    el.className = 'p-2.5 rounded-xl border border-slate-100 hover:border-indigo-200 bg-slate-50/60 hover:bg-indigo-50/40 transition cursor-pointer space-y-1';
                    
                    let badgeColor = 'bg-slate-200 text-slate-700';
                    if (item.activity.includes('Fishing')) badgeColor = 'bg-emerald-100 text-emerald-800';
                    else if (item.activity.includes('Encounter')) badgeColor = 'bg-amber-100 text-amber-800';
                    else if (item.activity.includes('Loitering')) badgeColor = 'bg-purple-100 text-purple-800';
                    else if (item.activity.includes('Port')) badgeColor = 'bg-sky-100 text-sky-800';

                    el.innerHTML = `
                        <div class="flex items-center justify-between text-[11px]">
                            <span class="font-bold text-slate-800 truncate">${escapeHtml(item.vessel)}</span>
                            <span class="font-mono text-[10px] text-slate-400">${formatDate(item.time)}</span>
                        </div>
                        <div class="flex items-center justify-between text-[10px]">
                            <span class="px-1.5 py-0.5 rounded font-bold ${badgeColor}">${escapeHtml(item.activity)}</span>
                            <span class="font-mono text-slate-500">${escapeHtml(item.location)}</span>
                        </div>
                    `;

                    el.addEventListener('click', () => {
                        if (item.lat !== null && item.lon !== null && map) {
                            map.flyTo({ center: [item.lon, item.lat], zoom: 10 });
                        }
                        if (item.vessel_id) {
                            selectVesselById(item.vessel_id);
                        }
                    });

                    activityFeedList.appendChild(el);
                });
            }

            // Feed Chip Buttons
            document.querySelectorAll('.feed-chip').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.feed-chip').forEach(b => {
                        b.classList.remove('bg-indigo-600', 'text-white');
                        b.classList.add('bg-slate-100', 'text-slate-700');
                    });
                    btn.classList.add('bg-indigo-600', 'text-white');
                    btn.classList.remove('bg-slate-100', 'text-slate-700');

                    currentFeedFilter = btn.dataset.type;
                    renderActivityFeed(eventsList);
                });
            });

            // Table Rendering
            function renderVesselsTable(vessels) {
                tableRecordCount.textContent = `${vessels.length} kapal`;

                if (vessels.length === 0) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400 text-xs">
                                Tidak ada kapal terdeteksi pada periode dan filter ini.
                            </td>
                        </tr>
                    `;
                    return;
                }

                tableBody.innerHTML = '';
                vessels.slice(0, 50).forEach(v => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-slate-50 transition cursor-pointer';

                    let statusClass = 'bg-slate-100 text-slate-600';
                    if (v.status === 'LIVE') statusClass = 'bg-emerald-100 text-emerald-800';
                    else if (v.status === 'RECENT') statusClass = 'bg-sky-100 text-sky-800';

                    row.innerHTML = `
                        <td class="py-2 px-2.5 font-bold text-slate-800 truncate max-w-[140px]">${escapeHtml(v.name || 'Unnamed')}</td>
                        <td class="py-2 px-2 font-mono text-slate-600 text-[11px]">${escapeHtml(v.mmsi || '-')}</td>
                        <td class="py-2 px-2 font-medium text-slate-700">${escapeHtml(v.flag || '-')}</td>
                        <td class="py-2 px-2 text-[11px]">${escapeHtml(v.vessel_type || 'Unknown')}</td>
                        <td class="py-2 px-2">
                            <span class="inline-flex px-1.5 py-0.5 rounded text-[9px] font-bold ${statusClass}">${escapeHtml(v.status || 'STALE')}</span>
                        </td>
                        <td class="py-2 px-2 text-right">
                            <button type="button" class="btn-select-v px-2 py-0.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-[10px] transition">
                                Detail ↗
                            </button>
                        </td>
                    `;

                    row.addEventListener('click', () => {
                        selectVessel(v);
                        if (v.lat !== null && v.lon !== null && map) {
                            map.flyTo({ center: [v.lon, v.lat], zoom: 9 });
                        }
                    });

                    tableBody.appendChild(row);
                });
            }

            // Vessel Selection
            function selectVesselById(id) {
                const vessel = vesselsList.find(v => String(v.id) === String(id));
                if (vessel) selectVessel(vessel);
            }

            function selectVessel(v) {
                activeSelectedVessel = v;
                vesselDetailEmpty.classList.add('hidden');
                vesselDetailBody.classList.remove('hidden');
                btnLoadTrack.classList.remove('hidden');

                vesselNameEl.textContent = v.name || 'Unnamed Vessel';
                vesselMmsiEl.textContent = v.mmsi || 'N/A';
                vesselImoEl.textContent = v.imo || 'N/A';
                vesselFlagTypeEl.textContent = `${v.flag || 'N/A'} • ${v.vessel_type || 'Unknown'}`;

                detailStatusBadge.textContent = v.status || 'STALE';
                detailStatusBadge.className = 'px-2 py-0.5 rounded text-[10px] font-bold ' + 
                    (v.status === 'LIVE' ? 'bg-emerald-100 text-emerald-800' : (v.status === 'RECENT' ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-700'));

                trackSummaryBox.classList.add('hidden');
            }

            // Load & Render Track
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

                    // Add track to map
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
                                'line-color': '#06b6d4',
                                'line-width': 3,
                                'line-opacity': 0.9
                            }
                        });

                        map.addLayer({
                            id: 'vessel-track-points',
                            type: 'circle',
                            source: 'dashboard-track-src',
                            filter: ['==', '$type', 'Point'],
                            paint: {
                                'circle-radius': 3.5,
                                'circle-color': '#22d3ee',
                                'circle-stroke-width': 1,
                                'circle-stroke-color': '#0f172a'
                            }
                        });
                    }

                    // Turn on track checkbox
                    if (optTrack) optTrack.checked = true;

                } catch (e) {
                    btnLoadTrack.disabled = false;
                    btnLoadTrack.textContent = 'Lihat Track';
                    alert('Koneksi terputus saat mengambil data track: ' + e.message);
                }
            });

            // Live Monitoring Toggle
            btnToggleLive.addEventListener('click', () => {
                isLiveActive = !isLiveActive;
                if (isLiveActive) {
                    btnToggleLive.textContent = 'ON (60s)';
                    btnToggleLive.className = 'px-3 py-1 rounded-lg text-xs font-bold transition bg-emerald-600 text-white shadow-xs';
                    livePulse.className = 'w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block animate-ping';
                    liveStatusText.textContent = 'Aktif (Auto-refresh 60 detik)';
                    liveStatusText.className = 'text-emerald-300 font-mono';

                    // Start 60-second timer
                    if (liveTimer) {
                        clearInterval(liveTimer);
                        liveTimer = null;
                    }
                    liveTimer = setInterval(fetchDashboardData, 60000);
                } else {
                    btnToggleLive.textContent = 'OFF';
                    btnToggleLive.className = 'px-3 py-1 rounded-lg text-xs font-bold transition bg-slate-800 text-slate-300 hover:bg-slate-700 border border-slate-700';
                    livePulse.className = 'w-2.5 h-2.5 rounded-full bg-slate-500 inline-block';
                    liveStatusText.textContent = 'Manual (Siap)';
                    liveStatusText.className = 'text-slate-200 font-mono';

                    if (liveTimer) {
                        clearInterval(liveTimer);
                        liveTimer = null;
                    }
                }
            });

            // Filter Buttons
            btnApply.addEventListener('click', fetchDashboardData);
            btnReset.addEventListener('click', () => {
                filterSearch.value = '';
                filterType.value = '';
                filterStatus.value = '';
                fetchDashboardData();
            });
            btnRetry?.addEventListener('click', fetchDashboardData);

            // Helpers
            function formatDate(str) {
                if (!str) return '-';
                try {
                    const d = new Date(str);
                    return d.toLocaleString('id-ID', {
                        year: 'numeric', month: 'short', day: 'numeric',
                        hour: '2-digit', minute: '2-digit',
                        timeZone: 'Asia/Jakarta'
                    }) + ' WIB';
                } catch { return str; }
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

            initMap();
        });
    </script>
</x-app-layout>
