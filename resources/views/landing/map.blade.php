{{-- ============================================================== --}}
{{-- PETA PERIKANAN — HALAMAN EKSPLORASI GIS SPASIAL LENGKAP         --}}
{{-- ============================================================== --}}
<div class="space-y-8">

    {{-- Page Header --}}
    <div class="glass-card p-6 sm:p-8 rounded-3xl border border-white/15 relative overflow-hidden">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 relative z-10">
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-xs font-mono font-bold tracking-wider">
                        GIS & SPATIAL EXPLORER
                    </span>
                    <span class="text-xs text-slate-400">MapLibre GL JS • OpenStreetMap • GFW Events</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-wide uppercase">
                    {{ __('Peta Perikanan') }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 mt-1">
                    {{ __('Eksplorasi data spasial perikanan Aceh & pemantauan aktivitas satelit Global Fishing Watch') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('gfw.monitoring') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-amber-300 glass-panel glass-panel-hover border border-amber-400/30 hover:border-amber-400/60 transition-all cursor-pointer">
                    <span>🛰️</span>
                    <span>GFW Monitoring</span>
                </a>
                <button type="button" id="btnToggleExplorerFgPanel" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-200 glass-panel glass-panel-hover border border-white/15 hover:border-purple-400/40 transition-all cursor-pointer">
                    <span>🧭</span>
                    <span>Master Fishing Ground</span>
                </button>
                <button type="button" id="btnResetExplorerGisView" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-bold text-white bg-ocean-600 hover:bg-ocean-500 shadow-lg shadow-ocean-600/30 border border-cyan-400/30 transition-all cursor-pointer">
                    <span>🎯</span>
                    <span>Reset Pandangan</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Layer Filter & Control Bar --}}
    <div class="glass-card p-4 sm:p-6 rounded-2xl border border-white/10 space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            
            {{-- Layer Toggles --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-400 mr-1">Layer Aktif:</span>

                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-rose-500/20 text-rose-300 border-rose-500/50" data-layer="efforts">
                    ● Fishing Effort
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-sky-500/20 text-sky-300 border-sky-500/50" data-layer="ports">
                    ● Landing Site / TPI
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-emerald-500/20 text-emerald-300 border-emerald-500/50" data-layer="vessels">
                    ● Kapal (Homeport)
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-amber-500/20 text-amber-300 border-amber-500/50" data-layer="logbooks">
                    ● Logbook Historis
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-purple-500/20 text-purple-300 border-purple-500/50" data-layer="grounds">
                    ● Fishing Ground
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-cyan-500/20 text-cyan-300 border-cyan-500/50" data-layer="wpp571">
                    ▱ WPP 571
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-blue-500/20 text-blue-300 border-blue-500/50" data-layer="wpp572">
                    ▱ WPP 572
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-emerald-500/20 text-emerald-300 border-emerald-500/50 flex items-center gap-1.5" data-layer="rzwp3k">
                    <span>▱ RZWP3K Aceh</span>
                    <span id="badgeCountExplorerRzwp3k" class="text-[10px] px-1.5 py-0.2 rounded-full bg-emerald-500/30 text-emerald-200 border border-emerald-400/40">0</span>
                </button>
                <button type="button" class="explorer-layer-toggle-btn active px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border cursor-pointer bg-amber-500/20 text-amber-300 border-amber-500/50 flex items-center gap-1.5" data-layer="gfw" id="toggleLayerGfwEvents">
                    <span>● GFW Fishing Events</span>
                    <span id="badgeCountExplorerGfwEvents" class="text-[10px] px-1.5 py-0.2 rounded-full bg-amber-500/30 text-amber-200 border border-amber-400/40 font-mono">0</span>
                </button>
            </div>

            {{-- RZWP3K Filter Dropdown --}}
            <div class="flex items-center gap-2">
                <label for="filterExplorerRzwp3kZoneType" class="text-xs text-slate-400 shrink-0">Kawasan RZWP3K:</label>
                <select id="filterExplorerRzwp3kZoneType" class="text-xs rounded-xl px-3 py-1.5 bg-slate-900 text-slate-200 border border-slate-700 focus:ring-1 focus:ring-emerald-400 focus:outline-none">
                    <option value="">Semua Kawasan</option>
                    <option value="KPU">KPU (Pemanfaatan Umum)</option>
                    <option value="KK">KK (Konservasi)</option>
                    <option value="AL">AL (Alur Laut)</option>
                    <option value="KSNT">KSNT (Strategis)</option>
                </select>
            </div>

        </div>

        {{-- GFW Filter, Temporal Control & Search Toolbar --}}
        <div id="gfwToolbarSection" class="pt-3 border-t border-slate-700/60 flex flex-wrap items-center justify-between gap-3 text-xs">
            {{-- Temporal Controls (Date Filter) --}}
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-bold text-amber-400 flex items-center gap-1 shrink-0">
                    <span>📅</span> Periode GFW:
                </span>
                <div class="flex items-center gap-1 bg-slate-900 border border-slate-700 rounded-xl px-2 py-1">
                    <span class="text-slate-400 text-[10px]">Mulai:</span>
                    <input type="date" id="inputGfwStartDate" value="2026-09-01" class="bg-transparent text-slate-200 text-xs focus:outline-none focus:text-amber-300 font-mono">
                </div>
                <div class="flex items-center gap-1 bg-slate-900 border border-slate-700 rounded-xl px-2 py-1">
                    <span class="text-slate-400 text-[10px]">Sampai:</span>
                    <input type="date" id="inputGfwEndDate" value="2026-09-07" class="bg-transparent text-slate-200 text-xs focus:outline-none focus:text-amber-300 font-mono">
                </div>
                <button type="button" id="btnApplyGfwFilter" class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all shadow-md shadow-amber-500/20 cursor-pointer flex items-center gap-1">
                    <span>Tampilkan Data</span>
                </button>
            </div>

                <div class="flex items-center gap-1">
                    <label for="selectGfwSpatialGround" class="text-slate-400 shrink-0 text-[11px]">Overlay FG:</label>
                    <select id="selectGfwSpatialGround" class="text-xs rounded-xl px-2 py-1 bg-slate-900 text-purple-300 border border-purple-500/40 focus:ring-1 focus:ring-purple-400 focus:outline-none">
                        <option value="">Nonaktif</option>
                        <option value="all">Semua Fishing Ground</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <label for="selectGfwLimit" class="text-slate-400 shrink-0 text-[11px]">Limit:</label>
                    <select id="selectGfwLimit" class="text-xs rounded-xl px-2 py-1 bg-slate-900 text-slate-200 border border-slate-700 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                        <option value="25">25</option>
                        <option value="50" selected>50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <label for="selectGfwEventType" class="text-slate-400 shrink-0 text-[11px]">Jenis Event:</label>
                    <select id="selectGfwEventType" class="text-xs rounded-xl px-2.5 py-1 bg-slate-900 text-slate-200 border border-slate-700 focus:ring-1 focus:ring-amber-400 focus:outline-none">
                        <option value="">Semua</option>
                        <option value="fishing">Fishing</option>
                    </select>
                </div>

                <div class="relative">
                    <input type="text" id="inputGfwVesselSearch" placeholder="Cari nama kapal / SSVID..." class="text-xs rounded-xl pl-7 pr-3 py-1 bg-slate-900 text-slate-200 placeholder-slate-500 border border-slate-700 focus:ring-1 focus:ring-amber-400 focus:outline-none w-44 sm:w-48">
                    <span class="absolute left-2 top-1 text-slate-500 text-xs pointer-events-none">🔍</span>
                </div>

                <button type="button" id="btnResetGfwFilter" class="px-3 py-1 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold transition-all cursor-pointer flex items-center gap-1">
                    <span>↺</span>
                    <span>Reset</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Full Explorer Map Container --}}
    <div class="relative w-full rounded-3xl overflow-hidden border border-slate-700/80 bg-slate-950 h-[520px] sm:h-[600px] lg:h-[680px] shadow-2xl" id="explorerGisMapWrapper">

        {{-- MapLibre GL JS Canvas --}}
        <div id="explorer-map-canvas" class="w-full h-full relative z-0"></div>

        {{-- GFW Events Information Panel --}}
        <div id="explorerGfwEventsPanel" class="absolute top-4 left-4 z-10 bg-slate-900/90 backdrop-blur-md border border-amber-500/40 rounded-2xl p-3.5 shadow-2xl text-xs text-slate-300 space-y-1.5 pointer-events-auto max-w-[280px] sm:max-w-xs transition-all">
            <div class="flex items-center justify-between border-b border-slate-800 pb-1.5 mb-1.5">
                <strong class="text-amber-400 font-bold flex items-center gap-1.5">
                    <span>📡</span> GFW Fishing Events
                </strong>
                <span id="gfwLoadingStatusBadge" class="text-[10px] px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 font-mono">
                    Memuat...
                </span>
            </div>
            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between text-slate-400">
                    <span>AOI:</span>
                    <span class="text-slate-200 font-medium text-right" id="gfwAoiLabel">ZEE Indonesia - Kawasan Aceh</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Periode (UTC):</span>
                    <span class="text-slate-200 font-mono text-[10px]" id="gfwPeriodLabel">01 Sep 2026 — 07 Sep 2026</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Total Event Upstream:</span>
                    <span class="text-amber-400 font-bold font-mono" id="gfwTotalCountLabel">-</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Batch Saat Ini (Diterima):</span>
                    <span class="text-slate-200 font-bold font-mono" id="gfwReturnedCountLabel">-</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Offset:</span>
                    <span class="text-sky-400 font-bold font-mono" id="gfwOffsetLabel">0</span>
                </div>
                <div class="flex justify-between text-slate-400">
                    <span>Setelah Filter:</span>
                    <span class="text-emerald-400 font-bold font-mono" id="gfwFilteredCountLabel">-</span>
                </div>
                <div class="flex justify-between text-slate-400" id="rowGfwSpatialMatches">
                    <span>Event dalam FG:</span>
                    <span class="text-purple-300 font-bold font-mono" id="gfwSpatialMatchLabel">-</span>
                </div>
                <div id="gfwSpatialNotice" class="hidden text-[10px] p-2 rounded-xl bg-purple-950/40 border border-purple-800/50 text-purple-200 leading-tight"></div>
                <div id="gfwPaginationHint" class="text-[10px] text-slate-400 italic pt-1 border-t border-slate-800/80">
                    Menampilkan event perikanan terdeteksi GFW API v3
                </div>
                {{-- Pagination Batch Navigation Controls --}}
                <div class="flex items-center justify-between gap-1.5 pt-1.5 border-t border-slate-800/80" id="gfwPaginationControls">
                    <button type="button" id="btnGfwPrevBatch" class="flex-1 py-1 px-2 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-slate-800 disabled:cursor-not-allowed text-[10px] font-semibold text-slate-200 transition-all border border-slate-700 flex items-center justify-center gap-1" disabled title="Batch Sebelumnya">
                        <span>← Sebelumnya</span>
                    </button>
                    <span id="gfwPageIndicator" class="text-[10px] font-mono text-slate-400 px-1 shrink-0">Hal. 1</span>
                    <button type="button" id="btnGfwNextBatch" class="flex-1 py-1 px-2 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-40 disabled:hover:bg-slate-800 disabled:cursor-not-allowed text-[10px] font-semibold text-slate-200 transition-all border border-slate-700 flex items-center justify-center gap-1" disabled title="Batch Berikutnya">
                        <span>Berikutnya →</span>
                    </button>
                </div>
                <div class="text-[9px] text-slate-500 italic pt-0.5">
                    * Pencarian kapal diterapkan pada batch aktif.
                </div>
            </div>
            {{-- Status Alert (Empty / Filtered Empty / Error) --}}
            <div id="gfwStatusAlert" class="hidden text-[11px] p-2 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-300">
            </div>
        </div>

        {{-- GFW Event Detail Panel (Selection View) --}}
        <div id="explorerGfwDetailPanel" class="hidden absolute top-4 right-4 z-20 bg-slate-900/95 backdrop-blur-md border border-amber-500/50 rounded-2xl p-4 shadow-2xl text-xs text-slate-300 space-y-2 pointer-events-auto max-w-[300px] sm:max-w-xs transition-all">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <strong class="text-amber-400 font-bold flex items-center gap-1.5 text-xs">
                    <span>📡</span> GFW EVENT DETAIL
                </strong>
                <button type="button" id="btnCloseGfwDetailPanel" class="text-slate-400 hover:text-white text-xs px-1.5 py-0.5 rounded-md hover:bg-slate-800 cursor-pointer font-bold" title="Tutup Detail">✕</button>
            </div>
            <div class="space-y-1.5 text-[11px]">
                <div>
                    <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Vessel:</span>
                    <div class="text-white font-bold text-xs" id="gfwDetailVesselName">-</div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">SSVID:</span>
                        <div class="text-sky-400 font-mono text-xs font-semibold" id="gfwDetailVesselSsvid">-</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Flag:</span>
                        <div class="text-slate-200 font-semibold" id="gfwDetailVesselFlag">-</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Vessel Type:</span>
                        <div class="text-slate-200" id="gfwDetailVesselType">-</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Event:</span>
                        <div class="text-emerald-400 font-semibold" id="gfwDetailEventType">-</div>
                    </div>
                </div>
                <div class="border-t border-slate-800/80 pt-1.5 space-y-1">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Start (UTC):</span>
                        <div class="text-slate-200 font-mono text-[10px]" id="gfwDetailStart">-</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">End (UTC):</span>
                        <div class="text-slate-200 font-mono text-[10px]" id="gfwDetailEnd">-</div>
                    </div>
                </div>
                <div class="border-t border-slate-800/80 pt-1.5 space-y-1">
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Distance from Shore:</span>
                        <div class="text-slate-200 font-medium" id="gfwDetailDistShore">-</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px] uppercase tracking-wider font-semibold">Distance from Port:</span>
                        <div class="text-slate-200 font-medium" id="gfwDetailDistPort">-</div>
                    </div>
                </div>
                {{-- GFW Spatial Overlay Section (Fishing Ground / RZWP3K) --}}
                <div id="gfwDetailSpatialSection" class="hidden border-t border-purple-500/30 pt-1.5 space-y-1 bg-purple-950/30 p-2 rounded-xl border border-purple-500/20">
                    <div class="text-purple-300 font-bold text-[11px] flex items-center gap-1" id="gfwDetailSpatialHeading">
                        <span>🧭</span> Analisis Spasial
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px]" id="gfwDetailSpatialTargetLabel">Wilayah / Zona:</span>
                        <div class="text-white font-semibold text-xs" id="gfwDetailSpatialGroundName">-</div>
                    </div>
                    <div>
                        <span class="text-slate-400 text-[10px]">Hubungan Spasial:</span>
                        <div class="text-emerald-400 font-medium text-[11px]" id="gfwDetailSpatialRelation">Within Zone</div>
                    </div>
                    <div class="text-[9px] text-slate-400 italic pt-0.5 leading-tight" id="gfwDetailSpatialDisclaimer">
                        * Informasi ini merupakan hasil analisis spasial berdasarkan koordinat event GFW dan geometry yang tersedia. Hasil ini tidak merupakan penetapan status legalitas aktivitas.
                    </div>
                </div>
                <div class="border-t border-slate-800/80 pt-1.5 font-mono text-[9px] text-slate-500 space-y-0.5">
                    <div>ID: <span id="gfwDetailEventId" class="text-slate-400 break-all">-</span></div>
                    <div>Dataset: <span id="gfwDetailDataset" class="text-slate-400">-</span></div>
                </div>
                <div class="pt-2">
                    <button type="button" id="btnDismissGfwDetail" class="w-full py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 text-xs font-semibold text-center cursor-pointer transition-colors">
                        Tutup Detail
                    </button>
                </div>
            </div>
        </div>

        {{-- RZWP3K Notice Overlay --}}
        <div id="explorerRzwp3kNotice" class="hidden absolute top-4 right-4 sm:max-w-xs bg-slate-900/95 backdrop-blur-md border border-emerald-500/40 rounded-2xl p-4 shadow-2xl z-20 transition-all text-xs text-slate-300 space-y-1.5">
            <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                <strong class="text-emerald-400 font-bold flex items-center gap-1.5">
                    <span>🗺️</span> RZWP3K Aceh (Qanun 1/2020)
                </strong>
                <button type="button" id="btnCloseExplorerRzwp3kNotice" class="text-slate-400 hover:text-white text-xs px-1 cursor-pointer">✕</button>
            </div>
            <p class="text-[11px] text-slate-300 leading-relaxed">
                Data geometri batas zonasi RZWP3K ditampilkan berdasarkan metadata resmi zonasi pesisir Aceh.
            </p>
        </div>

        {{-- Loading Spinner Overlay --}}
        <div id="explorerMapLoadingOverlay" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm z-20 flex flex-col items-center justify-center gap-3 transition-opacity">
            <div class="w-12 h-12 border-4 border-ocean-500/30 border-t-ocean-400 rounded-full animate-spin"></div>
            <span class="text-xs sm:text-sm font-semibold text-slate-200">Memuat Peta Perikanan Spasial...</span>
        </div>

        {{-- Error State Overlay --}}
        <div id="explorerMapErrorOverlay" class="hidden absolute inset-0 bg-slate-950/90 z-20 flex flex-col items-center justify-center p-6 text-center">
            <span class="text-4xl mb-2 text-rose-400">⚠️</span>
            <h5 class="text-base font-bold text-white mb-1">Gagal Memuat Data Spasial</h5>
            <p class="text-xs text-slate-400 max-w-sm mb-4">Gagal menghubungi server data spasial.</p>
            <button type="button" id="btnRetryExplorerGis" class="px-5 py-2.5 text-xs font-semibold rounded-xl bg-ocean-600 text-white hover:bg-ocean-500 transition-all cursor-pointer">
                Coba Lagi
            </button>
        </div>

        {{-- Floating Responsive Legend --}}
        <div class="absolute bottom-4 left-4 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-2xl p-3.5 shadow-2xl text-[11px] text-slate-300 space-y-1.5 pointer-events-auto hidden sm:block max-w-[280px]">
            <div class="font-bold text-white text-xs border-b border-slate-800 pb-1.5 mb-1.5 flex items-center justify-between">
                <span>Legenda Peta Spasial</span>
                <span class="text-[10px] text-slate-400 font-mono">MapLibre</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 border border-white inline-block shrink-0 shadow-sm shadow-amber-400/50"></span>
                <span class="truncate">GFW Fishing Event (Aktivitas Terdeteksi)</span>
            </div>
            <div class="flex items-center gap-2" id="legendItemGfwSpatial">
                <span class="w-3 h-3 rounded-full bg-yellow-400 border-2 border-white inline-block shrink-0 shadow-sm shadow-yellow-400/60 ring-2 ring-purple-500/50"></span>
                <span class="truncate">GFW × Fishing Ground (Intersection)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 border border-white inline-block shrink-0 shadow-sm shadow-rose-500/50"></span>
                <span class="truncate">Fishing Effort (Setting Penangkapan)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-sky-500 border border-white inline-block shrink-0 shadow-sm shadow-sky-500/50"></span>
                <span class="truncate">Landing Site / Pangkalan PPI</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-white inline-block shrink-0 shadow-sm shadow-emerald-500/50"></span>
                <span class="truncate">Homeport Kapal Perikanan</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 border border-white inline-block shrink-0 shadow-sm shadow-amber-500/50"></span>
                <span class="truncate">Logbook Trip Tangkapan</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-purple-500 border border-white inline-block shrink-0 shadow-sm shadow-purple-500/50"></span>
                <span class="truncate">Fishing Ground Aceh</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-sm border border-cyan-400 bg-cyan-500/30 inline-block shrink-0"></span>
                <span class="truncate">WPP 571 (Selat Malaka & Andaman)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-sm border border-blue-400 bg-blue-500/30 inline-block shrink-0"></span>
                <span class="truncate">WPP 572 (Samudera Hindia Barat)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-sm border border-emerald-400 bg-emerald-500/30 inline-block shrink-0"></span>
                <span class="truncate">RZWP3K Aceh (Zonasi Pesisir)</span>
            </div>
        </div>

    </div>

    {{-- Collapsible Master Fishing Ground Reference Panel --}}
    <div id="panelExplorerMasterFishingGround" class="hidden glass-card border border-purple-500/25 rounded-3xl p-6 space-y-4 transition-all">
        <div class="flex items-center justify-between">
            <h5 class="text-sm sm:text-base font-bold text-purple-300 flex items-center gap-2">
                <span>🧭</span> Master Daerah Penangkapan Ikan Aceh (WPP-NRI 571 & 572)
            </h5>
            <span class="text-[10px] sm:text-xs px-2.5 py-1 rounded-full bg-purple-500/20 text-purple-200 border border-purple-500/30 font-semibold">
                7 Wilayah Master Terdaftar
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Sabang & Weh</div>
                <div class="text-[11px] text-slate-400">Koordinat: 5.89° LU, 95.32° BT</div>
                <div class="text-[10px] text-cyan-300 mt-1 font-mono">WPP 571 / 572 • Samudera Hindia / Malaka</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Lampulo & Banda Aceh</div>
                <div class="text-[11px] text-slate-400">Koordinat: 5.58° LU, 95.31° BT</div>
                <div class="text-[10px] text-cyan-300 mt-1 font-mono">WPP 571 • Selat Malaka & Ujung Sumatera</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Idi Rayeuk (Aceh Timur)</div>
                <div class="text-[11px] text-slate-400">Koordinat: 4.97° LU, 97.77° BT</div>
                <div class="text-[10px] text-cyan-300 mt-1 font-mono">WPP 571 • Koridor Selat Malaka</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Meulaboh (Aceh Barat)</div>
                <div class="text-[11px] text-slate-400">Koordinat: 4.14° LU, 96.12° BT</div>
                <div class="text-[10px] text-blue-300 mt-1 font-mono">WPP 572 • Samudera Hindia Pesisir Barat</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Simeulue (Sinabang)</div>
                <div class="text-[11px] text-slate-400">Koordinat: 2.48° LU, 96.38° BT</div>
                <div class="text-[10px] text-blue-300 mt-1 font-mono">WPP 572 • Kepulauan Samudera Lepas</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Lhokseumawe & Pase</div>
                <div class="text-[11px] text-slate-400">Koordinat: 5.18° LU, 97.14° BT</div>
                <div class="text-[10px] text-cyan-300 mt-1 font-mono">WPP 571 • Teluk Lhokseumawe</div>
            </div>
            <div class="glass-panel p-3.5 rounded-xl border border-white/10">
                <div class="font-bold text-white mb-1">🌊 Perairan Aceh Selatan (Labuhan Haji)</div>
                <div class="text-[11px] text-slate-400">Koordinat: 3.55° LU, 97.02° BT</div>
                <div class="text-[10px] text-blue-300 mt-1 font-mono">WPP 572 • Samudera Hindia Bagian Selatan</div>
            </div>
        </div>
    </div>

</div>

{{-- Map Explorer Scripts --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        initExplorerFisheriesMap();
    });

    window.addEventListener('map-tab-activated', function() {
        setTimeout(function() {
            if (window._explorerMap) {
                window._explorerMap.resize();
            } else {
                initExplorerFisheriesMap();
            }
        }, 150);
    });

    function initExplorerFisheriesMap() {
        const container = document.getElementById('explorer-map-canvas');
        if (!container || window._explorerMapInitialized) return;

        // Base MapLibre Style using OpenStreetMap Raster Tiles
        const mapStyle = {
            version: 8,
            sources: {
                'osm-basemap': {
                    type: 'raster',
                    tiles: [
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png'
                    ],
                    tileSize: 256,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors'
                }
            },
            layers: [
                {
                    id: 'osm-basemap-layer',
                    type: 'raster',
                    source: 'osm-basemap',
                    minzoom: 0,
                    maxzoom: 19
                }
            ]
        };

        const wpp571GeoJson = {
            type: 'FeatureCollection',
            features: [{
                type: 'Feature',
                properties: {
                    code: '571',
                    name: 'WPPNRI 571 (Selat Malaka dan Laut Andaman)',
                    description: 'Wilayah Pengelolaan Perikanan Negara Republik Indonesia 571 mencakup Selat Malaka dan Laut Andaman.'
                },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [95.0, 1.5],
                        [104.5, 1.5],
                        [104.5, 6.0],
                        [95.0, 6.0],
                        [95.0, 1.5]
                    ]]
                }
            }]
        };

        const wpp572GeoJson = {
            type: 'FeatureCollection',
            features: [{
                type: 'Feature',
                properties: {
                    code: '572',
                    name: 'WPPNRI 572 (Samudera Hindia Sebelah Barat Sumatera)',
                    description: 'Wilayah Pengelolaan Perikanan Negara Republik Indonesia 572 mencakup Samudera Hindia Barat Sumatera dan Selat Sunda.'
                },
                geometry: {
                    type: 'Polygon',
                    coordinates: [[
                        [91.0, -6.0],
                        [103.0, -6.0],
                        [103.0, 6.0],
                        [91.0, 6.0],
                        [91.0, -6.0]
                    ]]
                }
            }]
        };

        const map = new maplibregl.Map({
            container: container,
            style: mapStyle,
            center: [95.32, 5.55],
            zoom: 6.8,
            minZoom: 4,
            maxZoom: 18,
            pitchWithRotate: false,
            attributionControl: true
        });

        window._explorerMap = map;
        window._explorerMapInitialized = true;

        map.addControl(new maplibregl.NavigationControl({ showCompass: true, showZoom: true }), 'top-right');
        map.addControl(new maplibregl.ScaleControl({ maxWidth: 120, unit: 'metric' }), 'bottom-right');

        const popup = new maplibregl.Popup({
            closeButton: true,
            closeOnClick: true,
            maxWidth: '340px'
        });

        let cachedBounds = null;

        const layerMapping = {
            efforts: ['layer-exp-efforts-circle'],
            ports: ['layer-exp-ports-circle'],
            vessels: ['layer-exp-vessels-circle'],
            logbooks: ['layer-exp-logbooks-circle'],
            grounds: ['layer-exp-grounds-circle'],
            wpp571: ['layer-exp-wpp-571-fill', 'layer-exp-wpp-571-line'],
            wpp572: ['layer-exp-wpp-572-fill', 'layer-exp-wpp-572-line'],
            rzwp3k: ['layer-exp-rzwp3k-fill', 'layer-exp-rzwp3k-line'],
            gfw: ['gfw-events-layer', 'gfw-events-highlight-layer', 'gfw-fishing-ground-spatial-layer']
        };

        let rawGfwEvents = [];
        let rawGfwSpatialMatches = [];
        let currentGfwLimit = 50;
        let currentGfwOffset = 0;
        let currentGfwNextOffset = null;
        let currentGfwTotalUpstream = 0;
        let currentGfwReturnedCount = 0;
        let selectedGfwEventId = null;
        let isGfwLoading = false;
        let gfwRequestSeq = 0;

        // Format ISO UTC string to readable UTC label
        function formatUtcDate(isoStr) {
            if (!isoStr || isoStr === 'Tidak tersedia' || isoStr === 'null') return 'Tidak tersedia';
            try {
                const d = new Date(isoStr);
                const time = d.getTime();
                if (!Number.isFinite(time)) return isoStr;
                const day = String(d.getUTCDate()).padStart(2, '0');
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                const month = months[d.getUTCMonth()];
                const year = d.getUTCFullYear();
                const hours = String(d.getUTCHours()).padStart(2, '0');
                const mins = String(d.getUTCMinutes()).padStart(2, '0');
                return `${day} ${month} ${year} ${hours}:${mins} UTC`;
            } catch (err) {
                return isoStr;
            }
        }

        // Validate date range format, calendar existence, start <= end, and max 7 days
        function validateGfwDateRange(startStr, endStr) {
            if (!startStr || !endStr) {
                return { valid: false, message: 'Tanggal mulai dan tanggal akhir wajib diisi.' };
            }
            const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
            if (!dateRegex.test(startStr) || !dateRegex.test(endStr)) {
                return { valid: false, message: 'Format tanggal harus YYYY-MM-DD.' };
            }
            const sParts = startStr.split('-').map(Number);
            const eParts = endStr.split('-').map(Number);
            const sDate = new Date(Date.UTC(sParts[0], sParts[1] - 1, sParts[2]));
            const eDate = new Date(Date.UTC(eParts[0], eParts[1] - 1, eParts[2]));

            if (!Number.isFinite(sDate.getTime()) || !Number.isFinite(eDate.getTime()) ||
                sDate.getUTCFullYear() !== sParts[0] || sDate.getUTCMonth() !== sParts[1] - 1 || sDate.getUTCDate() !== sParts[2] ||
                eDate.getUTCFullYear() !== eParts[0] || eDate.getUTCMonth() !== eParts[1] - 1 || eDate.getUTCDate() !== eParts[2]) {
                return { valid: false, message: 'Tanggal yang dimasukkan tidak valid.' };
            }

            if (sDate.getTime() > eDate.getTime()) {
                return { valid: false, message: 'start_date harus lebih kecil atau sama dengan end_date.' };
            }

            const diffDays = Math.round((eDate.getTime() - sDate.getTime()) / (1000 * 60 * 60 * 24)) + 1;
            if (diffDays > 7) {
                return { valid: false, message: 'Rentang tanggal tidak boleh lebih dari 7 hari.' };
            }

            return { valid: true };
        }

        // Clear GFW selected event highlight and detail panel
        function clearGfwSelection() {
            selectedGfwEventId = null;
            if (map.getLayer('gfw-events-highlight-layer')) {
                map.setFilter('gfw-events-highlight-layer', ['==', ['get', 'id'], '']);
            }
            $('#explorerGfwDetailPanel').addClass('hidden');
            $('#gfwDetailSpatialSection').addClass('hidden');
            popup.remove();
        }

        // Select GFW event and show detail panel + popup + map highlight
        function selectGfwEvent(props, coords) {
            selectedGfwEventId = props.id;
            if (map.getLayer('gfw-events-highlight-layer')) {
                map.setFilter('gfw-events-highlight-layer', ['==', ['get', 'id'], selectedGfwEventId]);
            }

            const vesselName = (props.vessel_name && props.vessel_name !== 'null') ? props.vessel_name : 'Tidak tersedia';
            const vesselSsvid = (props.vessel_ssvid && props.vessel_ssvid !== 'null') ? props.vessel_ssvid : 'Tidak tersedia';
            const vesselFlag = (props.vessel_flag && props.vessel_flag !== 'null') ? props.vessel_flag : 'Tidak tersedia';
            const vesselType = (props.vessel_type && props.vessel_type !== 'null') ? props.vessel_type : 'Tidak tersedia';
            const eventType = (props.type && props.type !== 'null') ? props.type : 'fishing';
            const eventId = (props.id && props.id !== 'null') ? props.id : 'Tidak tersedia';
            const startStr = formatUtcDate(props.start);
            const endStr = formatUtcDate(props.end);
            const shoreDistStart = (props.start_distance_shore_km && props.start_distance_shore_km !== 'null' && props.start_distance_shore_km !== 'Tidak tersedia') ? props.start_distance_shore_km + ' km' : 'Tidak tersedia';
            const shoreDistEnd = (props.end_distance_shore_km && props.end_distance_shore_km !== 'null' && props.end_distance_shore_km !== 'Tidak tersedia') ? props.end_distance_shore_km + ' km' : 'Tidak tersedia';
            const portDistStart = (props.start_distance_port_km && props.start_distance_port_km !== 'null' && props.start_distance_port_km !== 'Tidak tersedia') ? props.start_distance_port_km + ' km' : 'Tidak tersedia';
            const portDistEnd = (props.end_distance_port_km && props.end_distance_port_km !== 'null' && props.end_distance_port_km !== 'Tidak tersedia') ? props.end_distance_port_km + ' km' : 'Tidak tersedia';
            const dataset = (props.dataset && props.dataset !== 'null') ? props.dataset : 'public-global-fishing-events:latest';

            // Populate detail panel
            $('#gfwDetailVesselName').text(vesselName);
            $('#gfwDetailVesselSsvid').text(vesselSsvid);
            $('#gfwDetailVesselFlag').text(vesselFlag);
            $('#gfwDetailVesselType').text(vesselType);
            $('#gfwDetailEventType').text(eventType);
            $('#gfwDetailStart').text(startStr);
            $('#gfwDetailEnd').text(endStr);
            $('#gfwDetailDistShore').text(`${shoreDistStart} → ${shoreDistEnd}`);
            $('#gfwDetailDistPort').text(`${portDistStart} → ${portDistEnd}`);
            $('#gfwDetailEventId').text(eventId);
            $('#gfwDetailDataset').text(dataset);

            if (props.zone_name || props.rzwp3k_zone_name) {
                $('#gfwDetailSpatialHeading').html('<span>🧭</span> Analisis Spasial RZWP3K');
                $('#gfwDetailSpatialTargetLabel').text('Zona RZWP3K:');
                $('#gfwDetailSpatialGroundName').text(props.zone_name || props.rzwp3k_zone_name);
                $('#gfwDetailSpatialRelation').text(props.spatial_relation || 'Within RZWP3K Zone');
                $('#gfwDetailSpatialDisclaimer').text('* Informasi ini merupakan hasil analisis spasial berdasarkan koordinat event GFW dan geometry RZWP3K yang tersedia. Hasil ini tidak merupakan penetapan status legalitas aktivitas.');
                $('#gfwDetailSpatialSection').removeClass('hidden');
            } else if (props.fishing_ground_name) {
                $('#gfwDetailSpatialHeading').html('<span>🧭</span> Analisis Spasial Fishing Ground');
                $('#gfwDetailSpatialTargetLabel').text('Daerah Tangkapan:');
                $('#gfwDetailSpatialGroundName').text(props.fishing_ground_name);
                $('#gfwDetailSpatialRelation').text(props.spatial_relation || 'Within Fishing Ground');
                $('#gfwDetailSpatialDisclaimer').text('* Informasi ini merupakan hasil analisis spasial berdasarkan koordinat event dan geometry Fishing Ground yang tersedia. Hasil ini tidak merupakan penetapan status legalitas aktivitas.');
                $('#gfwDetailSpatialSection').removeClass('hidden');
            } else {
                $('#gfwDetailSpatialSection').addClass('hidden');
            }

            $('#explorerGfwDetailPanel').removeClass('hidden');

            // Render interactive popup
            popup.setLngLat(coords).setHTML(`
                <div style="font-size: 11px; line-height: 1.5; color: #f1f5f9; font-family: sans-serif;">
                    <div style="font-weight: bold; color: #fbbf24; font-size: 12px; margin-bottom: 4px; border-bottom: 1px solid rgba(251,191,36,0.3); padding-bottom: 2px;">
                        📡 GFW FISHING EVENT
                    </div>
                    <div><span style="color: #94a3b8;">Vessel:</span> <strong style="color: #ffffff;">${vesselName}</strong></div>
                    <div><span style="color: #94a3b8;">SSVID:</span> <span style="font-family: monospace; color: #38bdf8;">${vesselSsvid}</span></div>
                    <div><span style="color: #94a3b8;">Flag:</span> <span style="color: #f1f5f9;">${vesselFlag}</span></div>
                    <div><span style="color: #94a3b8;">Vessel Type:</span> <span style="color: #f1f5f9;">${vesselType}</span></div>
                    <div><span style="color: #94a3b8;">Event Type:</span> <span style="color: #34d399;">${eventType}</span></div>
                    <div><span style="color: #94a3b8;">Start:</span> <span style="color: #f1f5f9;">${startStr}</span></div>
                    <div><span style="color: #94a3b8;">End:</span> <span style="color: #f1f5f9;">${endStr}</span></div>
                    <div style="margin-top: 4px; padding-top: 4px; border-top: 1px solid #334155;">
                        <div><span style="color: #94a3b8;">Start Dist from Shore:</span> ${shoreDistStart}</div>
                        <div><span style="color: #94a3b8;">End Dist from Shore:</span> ${shoreDistEnd}</div>
                        <div><span style="color: #94a3b8;">Start Dist from Port:</span> ${portDistStart}</div>
                        <div><span style="color: #94a3b8;">End Dist from Port:</span> ${portDistEnd}</div>
                    </div>
                    <div style="margin-top: 4px; font-size: 9px; color: #64748b; font-family: monospace;">
                        <div>ID: ${eventId}</div>
                        <div>Dataset: ${dataset}</div>
                    </div>
                </div>
            `).addTo(map);
        }

        // Render features into MapLibre source
        function renderGfwEvents(eventsList) {
            const badge = $('#gfwLoadingStatusBadge');
            const alertBox = $('#gfwStatusAlert');
            const filteredCount = eventsList.length;

            $('#gfwFilteredCountLabel').text(filteredCount);
            $('#badgeCountExplorerGfwEvents').text(filteredCount);

            const features = [];
            const seenIds = {};

            eventsList.forEach(function(ev) {
                if (!ev) return;
                const eventId = ev.id || ('event-' + Math.random());
                if (seenIds[eventId]) return;
                seenIds[eventId] = true;

                const lat = (ev.position && typeof ev.position.lat === 'number') ? ev.position.lat : null;
                const lon = (ev.position && typeof ev.position.lon === 'number') ? ev.position.lon : null;

                // Coordinate validity: lat [-90, 90], lon [-180, 180]
                if (lat === null || lon === null || lat < -90 || lat > 90 || lon < -180 || lon > 180) {
                    return;
                }

                features.push({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [lon, lat]
                    },
                    properties: {
                        id: eventId,
                        type: ev.type || 'fishing',
                        start: ev.start || 'Tidak tersedia',
                        end: ev.end || 'Tidak tersedia',
                        vessel_name: (ev.vessel && ev.vessel.name) ? ev.vessel.name : 'Tidak tersedia',
                        vessel_ssvid: (ev.vessel && ev.vessel.ssvid) ? ev.vessel.ssvid : 'Tidak tersedia',
                        vessel_flag: (ev.vessel && ev.vessel.flag) ? ev.vessel.flag : 'Tidak tersedia',
                        vessel_type: (ev.vessel && ev.vessel.type) ? ev.vessel.type : 'Tidak tersedia',
                        dataset: ev.dataset || 'public-global-fishing-events:latest',
                        start_distance_shore_km: (ev.distances && ('startDistanceFromShoreKm' in ev.distances) && ev.distances.startDistanceFromShoreKm !== null) ? ev.distances.startDistanceFromShoreKm : 'Tidak tersedia',
                        end_distance_shore_km: (ev.distances && ('endDistanceFromShoreKm' in ev.distances) && ev.distances.endDistanceFromShoreKm !== null) ? ev.distances.endDistanceFromShoreKm : 'Tidak tersedia',
                        start_distance_port_km: (ev.distances && ('startDistanceFromPortKm' in ev.distances) && ev.distances.startDistanceFromPortKm !== null) ? Math.round(ev.distances.startDistanceFromPortKm * 10) / 10 : 'Tidak tersedia',
                        end_distance_port_km: (ev.distances && ('endDistanceFromPortKm' in ev.distances) && ev.distances.endDistanceFromPortKm !== null) ? Math.round(ev.distances.endDistanceFromPortKm * 10) / 10 : 'Tidak tersedia'
                    }
                });
            });

            const gfwGeoJson = {
                type: 'FeatureCollection',
                features: features
            };

            if (map.getSource('gfw-events-source')) {
                map.getSource('gfw-events-source').setData(gfwGeoJson);
            }

            if (rawGfwEvents.length === 0) {
                badge.text('0 Event').removeClass('bg-amber-500/20 text-amber-300 border-amber-500/30 bg-emerald-500/20 text-emerald-300 border-emerald-500/30').addClass('bg-slate-700 text-slate-300 border-slate-600');
                alertBox.removeClass('hidden').html('<span class="text-amber-400 font-semibold">Info:</span> Tidak ada event GFW pada periode dan AOI yang dipilih.');
            } else if (filteredCount === 0) {
                badge.text('0 Terpilih').removeClass('bg-amber-500/20 text-amber-300 border-amber-500/30 bg-emerald-500/20 text-emerald-300 border-emerald-500/30').addClass('bg-slate-700 text-slate-300 border-slate-600');
                alertBox.removeClass('hidden').html('<span class="text-amber-400 font-semibold">Filter:</span> Tidak ada event yang sesuai dengan filter.');
            } else {
                badge.text(`${filteredCount} / ${currentGfwTotalUpstream} Event`).removeClass('bg-amber-500/20 text-amber-300 border-amber-500/30 bg-slate-700 text-slate-300 border-slate-600')
                    .addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/30');
                alertBox.addClass('hidden').empty();
            }

            // If selected event was filtered out, remove selection
            if (selectedGfwEventId) {
                const stillPresent = features.some(f => f.properties.id === selectedGfwEventId);
                if (!stillPresent) {
                    clearGfwSelection();
                }
            }
        }

        // Apply client-side filters (Event Type & Vessel search) without extra API calls
        function applyGfwFilters() {
            const selectedType = $('#selectGfwEventType').val();
            const searchTerm = ($('#inputGfwVesselSearch').val() || '').trim().toLowerCase();

            const filtered = rawGfwEvents.filter(function(ev) {
                if (!ev) return false;
                if (selectedType && ev.type !== selectedType) {
                    return false;
                }
                if (searchTerm) {
                    const vName = (ev.vessel && ev.vessel.name) ? String(ev.vessel.name).toLowerCase() : '';
                    const vSsvid = (ev.vessel && ev.vessel.ssvid) ? String(ev.vessel.ssvid).toLowerCase() : '';
                    if (!vName.includes(searchTerm) && !vSsvid.includes(searchTerm)) {
                        return false;
                    }
                }
                return true;
            });

            renderGfwEvents(filtered);
        }

        function loadGfwEventsData(customStartDate, customEndDate, customOffset, customLimit) {
            if (isGfwLoading) return;

            const startDate = customStartDate || $('#inputGfwStartDate').val() || '2026-09-01';
            const endDate = customEndDate || $('#inputGfwEndDate').val() || '2026-09-07';
            const offset = (customOffset != null) ? Math.max(0, parseInt(customOffset, 10)) : currentGfwOffset;
            const limit = (customLimit != null) ? Math.max(1, Math.min(100, parseInt(customLimit, 10))) : currentGfwLimit;

            const valResult = validateGfwDateRange(startDate, endDate);
            const alertBox = $('#gfwStatusAlert');
            if (!valResult.valid) {
                alertBox.removeClass('hidden').html(`<span class="text-rose-400 font-semibold">Perhatian:</span> ${valResult.message}`);
                return;
            }

            isGfwLoading = true;
            gfwRequestSeq++;
            const activeReqId = gfwRequestSeq;

            $('#btnApplyGfwFilter').prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
            $('#btnGfwPrevBatch').prop('disabled', true);
            $('#btnGfwNextBatch').prop('disabled', true);
            $('#selectGfwLimit').prop('disabled', true);

            const badge = $('#gfwLoadingStatusBadge');
            badge.text('Memuat...').removeClass('bg-rose-500/20 text-rose-300 border-rose-500/30 bg-emerald-500/20 text-emerald-300 border-emerald-500/30 bg-slate-700 text-slate-300 border-slate-600')
                .addClass('bg-amber-500/20 text-amber-300 border-amber-500/30');
            alertBox.addClass('hidden').empty();

            const url = '{{ route('api.gfw.events.zee-indonesia-aceh') }}?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate) + '&limit=' + encodeURIComponent(limit) + '&offset=' + encodeURIComponent(offset);

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(res) {
                if (!res.ok) {
                    let errMsg = 'Gagal memuat data GFW.';
                    if (res.status === 401) errMsg = 'Autentikasi GFW gagal.';
                    else if (res.status === 422) errMsg = 'Parameter tanggal/pagination GFW tidak valid.';
                    else if (res.status === 429) errMsg = 'Batas permintaan GFW tercapai. Silakan coba lagi nanti.';
                    else if (res.status === 502) errMsg = 'Server GFW sedang mengalami gangguan.';
                    else if (res.status === 503) errMsg = 'Tidak dapat terhubung ke layanan GFW.';
                    throw new Error(errMsg);
                }
                return res.json();
            })
            .then(function(data) {
                if (activeReqId !== gfwRequestSeq) {
                    return; // Ignore stale responses
                }

                isGfwLoading = false;
                $('#btnApplyGfwFilter').prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                $('#selectGfwLimit').prop('disabled', false);

                if (!data || !data.success) {
                    throw new Error(data && data.message ? data.message : 'Gagal memuat data GFW.');
                }

                currentGfwTotalUpstream = ('event_count' in data) ? data.event_count : 0;
                currentGfwReturnedCount = ('returned_count' in data) ? data.returned_count : (data.events ? data.events.length : 0);
                rawGfwEvents = data.events || [];

                if (data.pagination) {
                    currentGfwOffset = ('offset' in data.pagination) ? data.pagination.offset : offset;
                    currentGfwLimit = ('limit' in data.pagination) ? data.pagination.limit : limit;
                    currentGfwNextOffset = ('next_offset' in data.pagination) ? data.pagination.next_offset : null;
                } else {
                    currentGfwOffset = offset;
                    currentGfwLimit = limit;
                    currentGfwNextOffset = (currentGfwOffset + currentGfwReturnedCount < currentGfwTotalUpstream) ? currentGfwOffset + currentGfwReturnedCount : null;
                }

                $('#gfwTotalCountLabel').text(currentGfwTotalUpstream);
                $('#gfwReturnedCountLabel').text(currentGfwReturnedCount);
                $('#gfwOffsetLabel').text(currentGfwOffset);
                $('#gfwPeriodLabel').text(`${startDate} — ${endDate}`);

                // Page and batch indicators
                const pageNum = Math.floor(currentGfwOffset / currentGfwLimit) + 1;
                $('#gfwPageIndicator').text(`Hal. ${pageNum}`);

                if (currentGfwReturnedCount === 0) {
                    $('#gfwPaginationHint').text(`Menampilkan 0 event dari total ${currentGfwTotalUpstream}`);
                } else if (currentGfwOffset === 0 && currentGfwReturnedCount === currentGfwTotalUpstream) {
                    $('#gfwPaginationHint').text(`Menampilkan seluruh ${currentGfwReturnedCount} event dari total ${currentGfwTotalUpstream}`);
                } else {
                    const fromIdx = currentGfwOffset + 1;
                    const toIdx = currentGfwOffset + currentGfwReturnedCount;
                    $('#gfwPaginationHint').text(`Menampilkan event ${fromIdx}–${toIdx} dari total ${currentGfwTotalUpstream}`);
                }

                // Update pagination buttons enabled/disabled states
                $('#btnGfwPrevBatch').prop('disabled', currentGfwOffset <= 0);
                $('#btnGfwNextBatch').prop('disabled', currentGfwNextOffset === null);

                // Dynamically populate selectGfwEventType options
                const eventTypes = {};
                rawGfwEvents.forEach(function(ev) {
                    if (ev && ev.type) {
                        eventTypes[ev.type] = true;
                    }
                });
                const currentSelectedType = $('#selectGfwEventType').val();
                let optionsHtml = '<option value="">Semua</option>';
                Object.keys(eventTypes).forEach(function(type) {
                    const capType = type.charAt(0).toUpperCase() + type.slice(1);
                    optionsHtml += `<option value="${type}">${capType}</option>`;
                });
                $('#selectGfwEventType').html(optionsHtml);
                if (eventTypes[currentSelectedType]) {
                    $('#selectGfwEventType').val(currentSelectedType);
                }

                applyGfwFilters();

                // If spatial overlay is active, sync with new batch
                if ($('#selectGfwSpatialGround').val()) {
                    loadGfwSpatialOverlay(startDate, endDate, currentGfwOffset, currentGfwLimit);
                }
            })
            .catch(function(err) {
                if (activeReqId !== gfwRequestSeq) {
                    return;
                }
                isGfwLoading = false;
                $('#btnApplyGfwFilter').prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                $('#selectGfwLimit').prop('disabled', false);
                $('#btnGfwPrevBatch').prop('disabled', currentGfwOffset <= 0);
                $('#btnGfwNextBatch').prop('disabled', currentGfwNextOffset === null);

                console.warn('GFW Events notice:', err.message || err);
                badge.text('Gagal').removeClass('bg-amber-500/20 text-amber-300 border-amber-500/30 bg-emerald-500/20 text-emerald-300 border-emerald-500/30')
                    .addClass('bg-rose-500/20 text-rose-300 border-rose-500/30');
                alertBox.removeClass('hidden').html(`<span class="text-rose-400 font-semibold">Perhatian:</span> ${err.message || 'Gagal memuat data GFW.'}`);
            });
        }

        // Load GFW Spatial Overlay with Master Fishing Grounds
        function loadGfwSpatialOverlay(customStartDate, customEndDate, customOffset, customLimit, customFgId) {
            const selectedFg = (customFgId != null) ? customFgId : $('#selectGfwSpatialGround').val();
            if (!selectedFg) {
                clearGfwSpatialMatches();
                return;
            }

            const startDate = customStartDate || $('#inputGfwStartDate').val() || '2026-09-01';
            const endDate = customEndDate || $('#inputGfwEndDate').val() || '2026-09-07';
            const offset = (customOffset != null) ? Math.max(0, parseInt(customOffset, 10)) : currentGfwOffset;
            const limit = (customLimit != null) ? Math.max(1, Math.min(100, parseInt(customLimit, 10))) : currentGfwLimit;

            let url = '{{ route('api.gfw.spatial.fishing-grounds') }}?start_date=' + encodeURIComponent(startDate) + '&end_date=' + encodeURIComponent(endDate) + '&limit=' + encodeURIComponent(limit) + '&offset=' + encodeURIComponent(offset);
            if (selectedFg !== 'all' && selectedFg !== '') {
                url += '&fishing_ground_id=' + encodeURIComponent(selectedFg);
            }

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (!data || !data.success) {
                    clearGfwSpatialMatches();
                    return;
                }

                rawGfwSpatialMatches = data.matches || [];
                const matchCount = data.spatial_match_count || 0;
                $('#gfwSpatialMatchLabel').text(`${matchCount} Event`);

                // Check geometry status
                const grounds = data.fishing_grounds || [];
                const hasReadyGround = grounds.some(g => g.geometry_status === 'READY');

                const noticeBox = $('#gfwSpatialNotice');
                if (!hasReadyGround && grounds.length > 0) {
                    noticeBox.removeClass('hidden').html('Analisis spasial belum tersedia karena geometry Master Fishing Ground belum tersedia/terverifikasi.');
                } else if (matchCount === 0) {
                    noticeBox.removeClass('hidden').html('Tidak ditemukan GFW Fishing Event yang berada di dalam Fishing Ground pada batch dan periode yang dipilih.');
                } else {
                    noticeBox.addClass('hidden').empty();
                }

                renderGfwSpatialMatches(rawGfwSpatialMatches);
            })
            .catch(err => {
                console.warn('GFW Spatial Overlay notice:', err);
                clearGfwSpatialMatches();
            });
        }

        function renderGfwSpatialMatches(matchesList) {
            const features = [];
            matchesList.forEach(function(m) {
                if (!m || m.latitude == null || m.longitude == null) return;
                features.push({
                    type: 'Feature',
                    geometry: {
                        type: 'Point',
                        coordinates: [m.longitude, m.latitude]
                    },
                    properties: {
                        id: m.event_id || ('spatial-' + Math.random()),
                        fishing_ground_id: m.fishing_ground_id,
                        fishing_ground_name: m.fishing_ground_name,
                        type: m.event_type || 'fishing',
                        spatial_relation: m.spatial_relation || 'within',
                        vessel_name: m.vessel_name || 'Tidak tersedia',
                        vessel_ssvid: m.ssvid || 'Tidak tersedia',
                        vessel_flag: m.flag || 'Tidak tersedia',
                        start: m.start || 'Tidak tersedia',
                        end: m.end || 'Tidak tersedia',
                        dataset: m.dataset || 'public-global-fishing-events:latest'
                    }
                });
            });

            if (map.getSource('gfw-fishing-ground-spatial-source')) {
                map.getSource('gfw-fishing-ground-spatial-source').setData({
                    type: 'FeatureCollection',
                    features: features
                });
            }
        }

        function clearGfwSpatialMatches() {
            rawGfwSpatialMatches = [];
            $('#gfwSpatialMatchLabel').text('-');
            $('#gfwSpatialNotice').addClass('hidden').empty();
            if (map.getSource('gfw-fishing-ground-spatial-source')) {
                map.getSource('gfw-fishing-ground-spatial-source').setData({
                    type: 'FeatureCollection',
                    features: []
                });
            }
        }

        function selectGfwSpatialMatch(props, coords) {
            selectGfwEvent(props, coords);

            $('#gfwDetailSpatialGroundName').text(props.fishing_ground_name || 'Master Fishing Ground');
            $('#gfwDetailSpatialRelation').text('Within Fishing Ground');
            $('#gfwDetailSpatialSection').removeClass('hidden');

            const vesselName = (props.vessel_name && props.vessel_name !== 'null') ? props.vessel_name : 'Tidak tersedia';
            const vesselSsvid = (props.vessel_ssvid && props.vessel_ssvid !== 'null') ? props.vessel_ssvid : 'Tidak tersedia';
            const vesselFlag = (props.vessel_flag && props.vessel_flag !== 'null') ? props.vessel_flag : 'Tidak tersedia';
            const startStr = formatUtcDate(props.start);
            const endStr = formatUtcDate(props.end);
            const eventId = (props.id && props.id !== 'null') ? props.id : 'Tidak tersedia';
            const dataset = (props.dataset && props.dataset !== 'null') ? props.dataset : 'public-global-fishing-events:latest';
            const fgName = props.fishing_ground_name || 'Master Fishing Ground';

            popup.setLngLat(coords).setHTML(`
                <div style="font-size: 11px; line-height: 1.5; color: #f1f5f9; font-family: sans-serif;">
                    <div style="font-weight: bold; color: #facc15; font-size: 12px; margin-bottom: 4px; border-bottom: 1px solid rgba(250,204,21,0.3); padding-bottom: 2px; display: flex; align-items: center; gap: 4px;">
                        <span>◎</span> GFW × FISHING GROUND MATCH
                    </div>
                    <div><span style="color: #c084fc;">Daerah Tangkapan:</span> <strong style="color: #ffffff;">${fgName}</strong></div>
                    <div><span style="color: #94a3b8;">Hubungan Spasial:</span> <span style="color: #34d399; font-weight: 600;">Within Fishing Ground</span></div>
                    <div><span style="color: #94a3b8;">Vessel:</span> <strong style="color: #ffffff;">${vesselName}</strong></div>
                    <div><span style="color: #94a3b8;">SSVID:</span> <span style="font-family: monospace; color: #38bdf8;">${vesselSsvid}</span></div>
                    <div><span style="color: #94a3b8;">Flag:</span> <span style="color: #f1f5f9;">${vesselFlag}</span></div>
                    <div><span style="color: #94a3b8;">Start:</span> <span style="color: #f1f5f9;">${startStr}</span></div>
                    <div><span style="color: #94a3b8;">End:</span> <span style="color: #f1f5f9;">${endStr}</span></div>
                    <div style="margin-top: 4px; font-size: 9px; color: #64748b; font-family: monospace; border-top: 1px solid #334155; padding-top: 4px;">
                        <div>ID: ${eventId}</div>
                        <div>Dataset: ${dataset}</div>
                    </div>
                    <div style="margin-top: 4px; font-size: 9px; color: #94a3b8; font-style: italic; border-top: 1px solid #334155; padding-top: 3px;">
                        * Analisis spasial matematis titik koordinat terhadap polygon master.
                    </div>
                </div>
            `).addTo(map);
        }

        function loadSpatialData() {
            $('#explorerMapLoadingOverlay').removeClass('opacity-0 pointer-events-none');
            $('#explorerMapErrorOverlay').addClass('hidden');

            Promise.all([
                fetch('{{ route('gis.data') }}', { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()),
                fetch('{{ route('api.rzwp3k.zones') }}', { headers: { 'Accept': 'application/geo+json, application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(r => r.json()).catch(() => ({ features: [] }))
            ])
            .then(([res, rzwp3kRes]) => {
                $('#explorerMapLoadingOverlay').addClass('opacity-0 pointer-events-none');

                const gEfforts = (res && res.efforts) ? res.efforts : { type: 'FeatureCollection', features: [] };
                const gPorts = (res && res.ports) ? res.ports : { type: 'FeatureCollection', features: [] };
                const gVessels = (res && res.vessels) ? res.vessels : { type: 'FeatureCollection', features: [] };
                const gLogbooks = (res && res.logbooks) ? res.logbooks : { type: 'FeatureCollection', features: [] };
                const gGrounds = (res && res.grounds) ? res.grounds : { type: 'FeatureCollection', features: [] };
                const gRzwp3k = (rzwp3kRes && rzwp3kRes.features) ? rzwp3kRes : { type: 'FeatureCollection', features: [] };

                if (map.getSource('src-exp-efforts')) map.getSource('src-exp-efforts').setData(gEfforts);
                if (map.getSource('src-exp-ports')) map.getSource('src-exp-ports').setData(gPorts);
                if (map.getSource('src-exp-vessels')) map.getSource('src-exp-vessels').setData(gVessels);
                if (map.getSource('src-exp-logbooks')) map.getSource('src-exp-logbooks').setData(gLogbooks);
                if (map.getSource('src-exp-grounds')) map.getSource('src-exp-grounds').setData(gGrounds);
                if (map.getSource('src-exp-rzwp3k')) map.getSource('src-exp-rzwp3k').setData(gRzwp3k);

                $('#badgeCountExplorerRzwp3k').text(gRzwp3k.features ? gRzwp3k.features.length : 0);

                // Populate Master Fishing Grounds dropdown for spatial overlay
                if (res && res.master_fishing_grounds && Array.isArray(res.master_fishing_grounds)) {
                    let fgOpts = '<option value="">Nonaktif</option><option value="all">Semua Fishing Ground</option>';
                    res.master_fishing_grounds.forEach(function(fg) {
                        fgOpts += `<option value="${fg.id}">${fg.name}</option>`;
                    });
                    $('#selectGfwSpatialGround').html(fgOpts);
                }

                const bounds = new maplibregl.LngLatBounds();
                let hasCoords = false;

                const addFeaturesToBounds = (fc) => {
                    if (fc && fc.features) {
                        fc.features.forEach(f => {
                            if (f.geometry && f.geometry.type === 'Point' && f.geometry.coordinates) {
                                bounds.extend(f.geometry.coordinates);
                                hasCoords = true;
                            }
                        });
                    }
                };

                addFeaturesToBounds(gEfforts);
                addFeaturesToBounds(gPorts);
                addFeaturesToBounds(gVessels);
                addFeaturesToBounds(gLogbooks);
                addFeaturesToBounds(gGrounds);

                if (hasCoords) {
                    cachedBounds = bounds;
                    map.fitBounds(bounds, { padding: 50, maxZoom: 12, duration: 1000 });
                }

                loadGfwEventsData();
            })
            .catch(err => {
                console.error("GIS Explorer Error:", err);
                $('#explorerMapLoadingOverlay').addClass('opacity-0 pointer-events-none');
                $('#explorerMapErrorOverlay').removeClass('hidden');
                loadGfwEventsData();
            });
        }

        map.on('load', function() {
            map.addSource('src-exp-wpp-571', { type: 'geojson', data: wpp571GeoJson });
            map.addSource('src-exp-wpp-572', { type: 'geojson', data: wpp572GeoJson });
            map.addSource('src-exp-rzwp3k', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('src-exp-grounds', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('src-exp-logbooks', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('src-exp-vessels', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('src-exp-ports', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('src-exp-efforts', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('gfw-events-source', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
            map.addSource('gfw-fishing-ground-spatial-source', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });

            // WPP Layers
            map.addLayer({
                id: 'layer-exp-wpp-571-fill',
                type: 'fill',
                source: 'src-exp-wpp-571',
                layout: { visibility: 'visible' },
                paint: { 'fill-color': '#06b6d4', 'fill-opacity': 0.08 }
            });
            map.addLayer({
                id: 'layer-exp-wpp-571-line',
                type: 'line',
                source: 'src-exp-wpp-571',
                layout: { visibility: 'visible' },
                paint: { 'line-color': '#06b6d4', 'line-width': 1.5, 'line-dasharray': [3, 2] }
            });

            map.addLayer({
                id: 'layer-exp-wpp-572-fill',
                type: 'fill',
                source: 'src-exp-wpp-572',
                layout: { visibility: 'visible' },
                paint: { 'fill-color': '#3b82f6', 'fill-opacity': 0.08 }
            });
            map.addLayer({
                id: 'layer-exp-wpp-572-line',
                type: 'line',
                source: 'src-exp-wpp-572',
                layout: { visibility: 'visible' },
                paint: { 'line-color': '#3b82f6', 'line-width': 1.5, 'line-dasharray': [3, 2] }
            });

            // RZWP3K Layers
            map.addLayer({
                id: 'layer-exp-rzwp3k-fill',
                type: 'fill',
                source: 'src-exp-rzwp3k',
                layout: { visibility: 'visible' },
                paint: {
                    'fill-color': [
                        'match',
                        ['get', 'zone_type'],
                        'KPU', '#10b981',
                        'KK', '#06b6d4',
                        'AL', '#f59e0b',
                        'KSNT', '#8b5cf6',
                        '#14b8a6'
                    ],
                    'fill-opacity': 0.22
                }
            });
            map.addLayer({
                id: 'layer-exp-rzwp3k-line',
                type: 'line',
                source: 'src-exp-rzwp3k',
                layout: { visibility: 'visible' },
                paint: {
                    'line-color': '#10b981',
                    'line-width': 1.2,
                    'line-dasharray': [2, 2]
                }
            });

            // Point Circles
            map.addLayer({
                id: 'layer-exp-grounds-circle',
                type: 'circle',
                source: 'src-exp-grounds',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': 7,
                    'circle-color': '#a855f7',
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#ffffff'
                }
            });
            map.addLayer({
                id: 'layer-exp-logbooks-circle',
                type: 'circle',
                source: 'src-exp-logbooks',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': 6,
                    'circle-color': '#f59e0b',
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#ffffff'
                }
            });
            map.addLayer({
                id: 'layer-exp-vessels-circle',
                type: 'circle',
                source: 'src-exp-vessels',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': 6,
                    'circle-color': '#10b981',
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#ffffff'
                }
            });
            map.addLayer({
                id: 'layer-exp-ports-circle',
                type: 'circle',
                source: 'src-exp-ports',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': 7,
                    'circle-color': '#0284c7',
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#ffffff'
                }
            });
            map.addLayer({
                id: 'layer-exp-efforts-circle',
                type: 'circle',
                source: 'src-exp-efforts',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': 6,
                    'circle-color': '#f43f5e',
                    'circle-stroke-width': 1.5,
                    'circle-stroke-color': '#ffffff'
                }
            });

            // GFW Fishing Events x Fishing Ground Spatial Intersection Layer
            map.addLayer({
                id: 'gfw-fishing-ground-spatial-layer',
                type: 'circle',
                source: 'gfw-fishing-ground-spatial-source',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': [
                        'interpolate', ['linear'], ['zoom'],
                        4, 6,
                        7, 9,
                        12, 14
                    ],
                    'circle-color': '#eab308',
                    'circle-stroke-width': 2.5,
                    'circle-stroke-color': '#ffffff',
                    'circle-opacity': 0.95
                }
            });

            // GFW Fishing Events Selected Highlight Ring
            map.addLayer({
                id: 'gfw-events-highlight-layer',
                type: 'circle',
                source: 'gfw-events-source',
                filter: ['==', ['get', 'id'], ''],
                paint: {
                    'circle-radius': [
                        'interpolate', ['linear'], ['zoom'],
                        4, 7,
                        7, 11,
                        12, 16
                    ],
                    'circle-color': 'rgba(56, 189, 248, 0.3)',
                    'circle-stroke-width': 2.5,
                    'circle-stroke-color': '#38bdf8'
                }
            });

            // GFW Fishing Events Layer
            map.addLayer({
                id: 'gfw-events-layer',
                type: 'circle',
                source: 'gfw-events-source',
                layout: { visibility: 'visible' },
                paint: {
                    'circle-radius': [
                        'interpolate', ['linear'], ['zoom'],
                        4, 4,
                        7, 6.5,
                        12, 10
                    ],
                    'circle-color': '#f59e0b',
                    'circle-stroke-width': 2,
                    'circle-stroke-color': '#ffffff',
                    'circle-opacity': 0.95
                }
            });

            // GFW Spatial Match Click Handler
            map.on('click', 'gfw-fishing-ground-spatial-layer', function(e) {
                if (!e.features || !e.features[0]) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();
                selectGfwSpatialMatch(p, coords);
            });

            // GFW Fishing Events Click / Selection Handler
            map.on('click', 'gfw-events-layer', function(e) {
                if (!e.features || !e.features[0]) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();
                selectGfwEvent(p, coords);
            });

            // Popups for other layers
            map.on('click', 'layer-exp-efforts-circle', function(e) {
                const p = e.features[0].properties;
                popup.setLngLat(e.lngLat).setHTML(`
                    <div style="font-size: 11px; line-height: 1.4; color: #f1f5f9;">
                        <strong style="color: #f43f5e; font-size: 12px;">🔴 FISHING EFFORT</strong>
                        <div><strong>Vessel:</strong> ${p.vessel_name || '-'}</div>
                        <div><strong>Gear:</strong> ${p.gear_name || '-'}</div>
                        <div><strong>Date:</strong> ${p.date || '-'}</div>
                        <div><strong>Catch Total:</strong> ${p.catch_total || '0'} kg</div>
                    </div>
                `).addTo(map);
            });

            map.on('click', 'layer-exp-ports-circle', function(e) {
                const p = e.features[0].properties;
                popup.setLngLat(e.lngLat).setHTML(`
                    <div style="font-size: 11px; line-height: 1.4; color: #f1f5f9;">
                        <strong style="color: #38bdf8; font-size: 12px;">⚓ PELABUHAN / TPI</strong>
                        <div style="font-size: 13px; font-weight: bold; color: #fff;">${p.name || '-'}</div>
                        <div><strong>Tipe:</strong> ${p.type || '-'}</div>
                        <div><strong>Kab/Kota:</strong> ${p.regency || '-'}</div>
                    </div>
                `).addTo(map);
            });

            map.on('click', 'layer-exp-vessels-circle', function(e) {
                const p = e.features[0].properties;
                popup.setLngLat(e.lngLat).setHTML(`
                    <div style="font-size: 11px; line-height: 1.4; color: #f1f5f9;">
                        <strong style="color: #34d399; font-size: 12px;">🚢 HOMEPORT KAPAL</strong>
                        <div style="font-size: 13px; font-weight: bold; color: #fff;">${p.name || '-'}</div>
                        <div><strong>Tonnage:</strong> ${p.tonnage ? p.tonnage + ' GT' : '-'}</div>
                        <div><strong>Gear:</strong> ${p.gear || '-'}</div>
                    </div>
                `).addTo(map);
            });

            map.on('click', 'layer-exp-grounds-circle', function(e) {
                const p = e.features[0].properties;
                popup.setLngLat(e.lngLat).setHTML(`
                    <div style="font-size: 11px; line-height: 1.4; color: #f1f5f9;">
                        <strong style="color: #c084fc; font-size: 12px;">🟣 FISHING GROUND</strong>
                        <div style="font-size: 13px; font-weight: bold; color: #fff;">${p.name || '-'}</div>
                        <div><strong>Kode:</strong> ${p.code || '-'}</div>
                        <div><strong>WPP:</strong> ${p.wpp || '-'}</div>
                    </div>
                `).addTo(map);
            });

            map.on('click', 'layer-exp-rzwp3k-fill', function(e) {
                const p = e.features[0].properties;
                popup.setLngLat(e.lngLat).setHTML(`
                    <div style="font-size: 11px; line-height: 1.4; color: #f1f5f9;">
                        <strong style="color: #34d399; font-size: 12px;">🗺️ RZWP3K ACEH</strong>
                        <div style="font-size: 13px; font-weight: bold; color: #fff;">${p.name || 'Zona RZWP3K'}</div>
                        <div><strong>Kode:</strong> ${p.code || '-'}</div>
                        <div><strong>Kawasan:</strong> ${p.zone_type || '-'}</div>
                        <div><strong>Subzona:</strong> ${p.subzone_type || '-'}</div>
                    </div>
                `).addTo(map);
            });

            // Interactive layer cursor
            ['layer-exp-efforts-circle', 'layer-exp-ports-circle', 'layer-exp-vessels-circle', 'layer-exp-logbooks-circle', 'layer-exp-grounds-circle', 'layer-exp-rzwp3k-fill', 'gfw-events-layer', 'gfw-fishing-ground-spatial-layer'].forEach(id => {
                map.on('mouseenter', id, () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', id, () => { map.getCanvas().style.cursor = ''; });
            });

            loadSpatialData();
        });

        // Layer toggle handler
        $('.explorer-layer-toggle-btn').on('click', function() {
            const layerKey = $(this).data('layer');
            const targetLayers = layerMapping[layerKey];
            if (!targetLayers) return;

            const isCurrentlyActive = $(this).hasClass('active');
            targetLayers.forEach(layerId => {
                if (map.getLayer(layerId)) {
                    map.setLayoutProperty(layerId, 'visibility', isCurrentlyActive ? 'none' : 'visible');
                }
            });

            if (isCurrentlyActive) {
                $(this).removeClass('active bg-rose-500/20 bg-sky-500/20 bg-emerald-500/20 bg-amber-500/20 bg-purple-500/20 bg-cyan-500/20 bg-blue-500/20 text-rose-300 text-sky-300 text-emerald-300 text-amber-300 text-purple-300 text-cyan-300 text-blue-300 border-rose-500/50 border-sky-500/50 border-emerald-500/50 border-amber-500/50 border-purple-500/50 border-cyan-500/50 border-blue-500/50')
                    .addClass('bg-slate-800 text-slate-400 border-slate-700 opacity-60');
            } else {
                $(this).addClass('active').removeClass('bg-slate-800 text-slate-400 border-slate-700 opacity-60');
                if (layerKey === 'efforts') $(this).addClass('bg-rose-500/20 text-rose-300 border-rose-500/50');
                if (layerKey === 'ports') $(this).addClass('bg-sky-500/20 text-sky-300 border-sky-500/50');
                if (layerKey === 'vessels') $(this).addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/50');
                if (layerKey === 'logbooks') $(this).addClass('bg-amber-500/20 text-amber-300 border-amber-500/50');
                if (layerKey === 'grounds') $(this).addClass('bg-purple-500/20 text-purple-300 border-purple-500/50');
                if (layerKey === 'wpp571') $(this).addClass('bg-cyan-500/20 text-cyan-300 border-cyan-500/50');
                if (layerKey === 'wpp572') $(this).addClass('bg-blue-500/20 text-blue-300 border-blue-500/50');
                if (layerKey === 'rzwp3k') $(this).addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/50');
                if (layerKey === 'gfw') $(this).addClass('bg-amber-500/20 text-amber-300 border-amber-500/50');
            }
        });

        // Filter GFW temporal range on submit button (resets offset to 0)
        $('#btnApplyGfwFilter').on('click', function() {
            currentGfwOffset = 0;
            clearGfwSelection();
            loadGfwEventsData($('#inputGfwStartDate').val(), $('#inputGfwEndDate').val(), 0, currentGfwLimit);
        });

        // Filter GFW Event Type dropdown change
        $('#selectGfwEventType').on('change', function() {
            applyGfwFilters();
        });

        // Search Vessel by name / SSVID input
        $('#inputGfwVesselSearch').on('input', function() {
            applyGfwFilters();
        });

        // Spatial FG Overlay dropdown change
        $('#selectGfwSpatialGround').on('change', function() {
            clearGfwSelection();
            loadGfwSpatialOverlay();
        });

        // Limit change handler (resets offset to 0)
        $('#selectGfwLimit').on('change', function() {
            const newLimit = parseInt($(this).val(), 10) || 50;
            currentGfwLimit = newLimit;
            currentGfwOffset = 0;
            clearGfwSelection();
            loadGfwEventsData(null, null, 0, newLimit);
        });

        // Next Batch Navigation
        $('#btnGfwNextBatch').on('click', function() {
            if (isGfwLoading || currentGfwNextOffset === null) return;
            clearGfwSelection();
            loadGfwEventsData(null, null, currentGfwNextOffset, currentGfwLimit);
        });

        // Previous Batch Navigation
        $('#btnGfwPrevBatch').on('click', function() {
            if (isGfwLoading || currentGfwOffset <= 0) return;
            const prevOffset = Math.max(0, currentGfwOffset - currentGfwLimit);
            clearGfwSelection();
            loadGfwEventsData(null, null, prevOffset, currentGfwLimit);
        });

        // Reset GFW filters
        $('#btnResetGfwFilter').on('click', function() {
            $('#inputGfwStartDate').val('2026-09-01');
            $('#inputGfwEndDate').val('2026-09-07');
            $('#selectGfwLimit').val('50');
            $('#selectGfwEventType').val('');
            $('#selectGfwSpatialGround').val('');
            $('#inputGfwVesselSearch').val('');
            currentGfwLimit = 50;
            currentGfwOffset = 0;
            clearGfwSelection();
            clearGfwSpatialMatches();
            loadGfwEventsData('2026-09-01', '2026-09-07', 0, 50);
        });

        // Dismiss / Close GFW Detail Panel
        $('#btnCloseGfwDetailPanel, #btnDismissGfwDetail').on('click', function() {
            clearGfwSelection();
        });

        // Filter RZWP3K
        $('#filterExplorerRzwp3kZoneType').on('change', function() {
            const selectedType = $(this).val();
            const url = '{{ route('api.rzwp3k.zones') }}' + (selectedType ? ('?zone_type=' + encodeURIComponent(selectedType)) : '');
            fetch(url, { headers: { 'Accept': 'application/geo+json, application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(res => res.json())
                .then(rzData => {
                    const rzFeatures = (rzData && rzData.features) ? rzData.features : [];
                    $('#badgeCountExplorerRzwp3k').text(rzFeatures.length);
                    if (map.getSource('src-exp-rzwp3k')) {
                        map.getSource('src-exp-rzwp3k').setData(rzData);
                    }
                })
                .catch(e => console.warn('RZWP3K filter notice:', e));
        });

        $('#btnToggleExplorerFgPanel').on('click', function() {
            $('#panelExplorerMasterFishingGround').slideToggle(200);
        });

        $('#btnResetExplorerGisView').on('click', function() {
            if (cachedBounds) {
                map.fitBounds(cachedBounds, { padding: 50, duration: 1000 });
            } else {
                map.flyTo({ center: [95.32, 5.55], zoom: 6.8, duration: 1000 });
            }
        });

        $('#btnRetryExplorerGis').on('click', function() {
            loadSpatialData();
        });

        $('#btnCloseExplorerRzwp3kNotice').on('click', function() {
            $('#explorerRzwp3kNotice').addClass('hidden');
        });

        setTimeout(function() { map.resize(); }, 300);
        window.addEventListener('resize', function() { map.resize(); });
    }
</script>
