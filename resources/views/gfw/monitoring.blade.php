<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>🛰️</span>
                <span>{{ __('GFW Vessel Monitoring (Peta Pemantauan Kapal Satelit AIS/VMS)') }}</span>
            </div>
        </div>
    </x-slot>

    {{-- Leaflet CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    {{-- Dark Mode Page Styles for GFW Monitoring --}}
    <style>
        .main-content {
            background-color: #0b1120 !important;
        }

        /* Leaflet dark theme controls */
        .leaflet-bar a {
            background-color: #1e293b !important;
            color: #e2e8f0 !important;
            border-bottom: 1px solid #334155 !important;
        }
        .leaflet-bar a:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
        }
        .leaflet-control-layers {
            background: #0f172a !important;
            color: #cbd5e1 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5) !important;
        }
        .leaflet-control-layers-expanded {
            padding: 8px 12px !important;
        }

        /* Leaflet dark popup */
        .leaflet-popup-content-wrapper {
            background: #0f172a !important;
            color: #f1f5f9 !important;
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.7) !important;
        }
        .leaflet-popup-tip {
            background: #0f172a !important;
        }
        .leaflet-popup-close-button {
            color: #94a3b8 !important;
        }
        .leaflet-popup-close-button:hover {
            color: #ffffff !important;
        }
    </style>

    <div class="space-y-6 text-slate-100">
        {{-- Banner Header & Freshness / Latency Notice --}}
        <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-indigo-900/50">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-5 pointer-events-none select-none">🛰️</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🛰️</span>
                        <span>{{ __('Global Fishing Watch (GFW) v3 Integration') }}</span>
                    </div>
                    <h2 class="text-lg sm:text-xl font-bold tracking-tight flex flex-wrap items-center gap-2">
                        <span>🛰️</span>
                        <span>{{ __('Pemantauan Aktivitas Kapal & Analisis Spasial Laut Lepas') }}</span>
                    </h2>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ __('Visualisasi data observasi pergerakan kapal (AIS/VMS), indikasi penangkapan (Apparent Fishing), pertemuan kapal (Potential Encounters), pola menunggu (Loitering), dan persinggahan pelabuhan (Port Visits) pada perairan Indonesia & Aceh.') }}
                    </p>
                </div>

                {{-- Latency & Provenance Notice Box --}}
                <div class="px-4 py-3 rounded-xl bg-amber-950/40 border border-amber-800/80 text-amber-200 text-xs max-w-md shrink-0">
                    <div class="font-bold flex items-center gap-1 text-amber-300">
                        <span>⚠️</span>
                        <span>{{ __('Pemberitahuan Latensi Data') }}</span>
                    </div>
                    <p class="mt-1 leading-normal text-amber-200/90 font-medium">
                        {{ $latencyNotice }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Main Monitoring Container (Filters Sidebar + Map Canvas) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Filter & Layer Control Sidebar (1 col on desktop) --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-slate-900/90 rounded-2xl p-5 shadow-sm border border-slate-800 space-y-5">
                    <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                        <div class="font-bold text-sm text-slate-100 flex items-center gap-2">
                            <span>⚙️</span>
                            <span>{{ __('Filter & Lapisan Peta') }}</span>
                        </div>
                        <button type="button" id="btn-refresh-all" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300 flex items-center gap-1">
                            <span>🔄</span>
                            <span>{{ __('Muat Ulang') }}</span>
                        </button>
                    </div>

                    {{-- Region Selector --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ __('Wilayah Geografis') }}
                        </label>
                        <select id="filter-region" class="w-full text-xs rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($regions as $reg)
                                <option value="{{ $reg['key'] }}" {{ $selectedRegionKey === $reg['key'] ? 'selected' : '' }}>
                                    {{ $reg['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div id="region-note" class="text-[11px] text-slate-400 mt-1 italic">
                            {{ $selectedRegion['provenance_note'] ?? '' }}
                        </div>
                    </div>

                    {{-- Date Range --}}
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Mulai') }}</label>
                            <input type="date" id="filter-start-date" value="{{ $startDate }}" class="w-full text-xs rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 [color-scheme:dark]">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1">{{ __('Selesai') }}</label>
                            <input type="date" id="filter-end-date" value="{{ $endDate }}" class="w-full text-xs rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 focus:border-indigo-500 focus:ring-indigo-500 [color-scheme:dark]">
                        </div>
                    </div>

                    {{-- Single Vessel Search (Tracks) --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">
                            {{ __('Lacak Kapal Tertentu (MMSI / Nama)') }}
                        </label>
                        <div class="flex gap-1.5">
                            <input type="text" id="filter-vessel-query" placeholder="Contoh: KM MEULABOH / 525001234" class="w-full text-xs rounded-xl bg-slate-800/90 border border-slate-700 text-slate-100 placeholder-slate-400 focus:border-indigo-500 focus:ring-indigo-500">
                            <button type="button" id="btn-search-vessel" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-semibold shrink-0">
                                🔍
                            </button>
                        </div>
                        <div id="vessel-search-result" class="text-[11px] mt-1 hidden"></div>
                    </div>

                    {{-- Layer Toggles --}}
                    <div class="pt-3 border-t border-slate-800 space-y-3">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
                            {{ __('Lapisan Peristiwa & Aktivitas') }}
                        </label>

                        <div class="space-y-2.5 text-xs">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-presence" checked class="rounded border-slate-600 bg-slate-800 text-emerald-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('GFW Vessel Presence') }}</span>
                                <span id="count-presence" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-fishing" checked class="rounded border-slate-600 bg-slate-800 text-rose-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-rose-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('Apparent Fishing Events') }}</span>
                                <span id="count-fishing" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-encounters" checked class="rounded border-slate-600 bg-slate-800 text-orange-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-orange-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('Potential Encounters') }}</span>
                                <span id="count-encounters" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-loitering" checked class="rounded border-slate-600 bg-slate-800 text-purple-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-purple-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('Loitering Events') }}</span>
                                <span id="count-loitering" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-port-visits" checked class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-amber-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('Port Visits') }}</span>
                                <span id="count-port-visits" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>

                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" id="layer-activity-tracks" checked class="rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-0">
                                <span class="w-3 h-3 rounded-full bg-cyan-500 shrink-0"></span>
                                <span class="font-medium text-slate-200">{{ __('Lintasan Track Kapal') }}</span>
                                <span id="count-tracks" class="ml-auto font-mono text-[10px] bg-slate-800 px-1.5 py-0.5 rounded text-slate-300 border border-slate-700">0</span>
                            </label>
                        </div>
                    </div>

                    <button type="button" id="btn-apply-filter" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl text-xs font-bold transition shadow-sm">
                        {{ __('Terapkan Filter Spasial') }}
                    </button>
                </div>
            </div>

            {{-- Map Canvas (3 cols on desktop) --}}
            <div class="lg:col-span-3 space-y-3">
                <div class="bg-slate-900/90 rounded-2xl p-3 shadow-sm border border-slate-800 relative">
                    {{-- Map Dimension Header Controls (Height Resize) --}}
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-2.5 mb-1.5 border-b border-slate-800 text-xs">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-100 flex items-center gap-1.5 text-xs">
                                <span>🗺️</span>
                                <span>{{ __('Peta Pemantauan Kapal') }}</span>
                            </span>
                            <span id="map-dimension-badge" class="font-mono text-[10px] text-indigo-300 font-semibold px-1.5 py-0.5 rounded bg-slate-800 border border-slate-700">640px</span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Vertical Height Presets --}}
                            <div class="flex items-center gap-1 bg-slate-800/80 p-0.5 rounded-lg border border-slate-700/80">
                                <span class="text-[10px] text-slate-400 px-1.5 font-semibold flex items-center gap-1">
                                    <span>↕️</span>
                                    <span class="hidden sm:inline">{{ __('Tinggi:') }}</span>
                                </span>
                                <button type="button" data-map-height="520" class="btn-preset-monitoring-height px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 transition border border-slate-700 cursor-pointer" title="520px">520px</button>
                                <button type="button" data-map-height="640" class="btn-preset-monitoring-height px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-600 text-white transition border border-indigo-500 cursor-pointer" title="640px">640px</button>
                                <button type="button" data-map-height="800" class="btn-preset-monitoring-height px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 transition border border-slate-700 cursor-pointer" title="800px">800px</button>
                                <button type="button" data-map-height="960" class="btn-preset-monitoring-height px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 hover:bg-indigo-600 hover:text-white text-slate-300 transition border border-slate-700 cursor-pointer" title="960px">960px</button>
                            </div>
                        </div>
                    </div>

                    {{-- Map Card Wrapper --}}
                    <div id="map-card-wrapper" class="relative" style="overflow: hidden; height: 640px; min-height: 380px; max-height: 1400px;">
                        {{-- Status / Notification Overlay --}}
                        <div id="map-status-overlay" class="absolute top-4 right-4 z-[1000] px-3 py-1.5 rounded-xl bg-slate-900/95 text-white text-xs font-medium shadow-lg backdrop-blur-xs border border-slate-700 hidden items-center gap-2">
                            <span id="map-status-icon">🔄</span>
                            <span id="map-status-text">{{ __('Loading GFW data...') }}</span>
                        </div>

                        {{-- Leaflet Map Element (Light Mode Canvas) --}}
                        <div id="gfw-map" class="w-full h-full rounded-xl z-0 bg-slate-100"></div>
                    </div>

                    {{-- Interactive Vertical Resize Handle Bar --}}
                    <div id="monitoring-map-resize-handle" class="group w-full py-2 mt-1.5 flex items-center justify-center cursor-row-resize select-none rounded-lg bg-slate-800/40 hover:bg-slate-800/90 active:bg-indigo-950/70 border border-slate-700/50 hover:border-indigo-500/60 transition-all shadow-xs" title="Klik dan geser ke atas/bawah untuk mengubah tinggi peta">
                        <div class="flex items-center gap-3 text-slate-400 group-hover:text-indigo-300 text-[11px] font-medium tracking-wide">
                            <span class="inline-block w-12 h-1 rounded-full bg-slate-600 group-hover:bg-indigo-400 transition-colors"></span>
                            <span class="flex items-center gap-1.5">
                                <span class="text-xs">↕️</span>
                                <span class="font-semibold text-slate-300 group-hover:text-white transition-colors">{{ __('Tarik Vertikal') }}</span>
                                <span id="monitoring-map-height-display" class="font-mono text-[10px] text-indigo-300 font-bold px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700/80">640px</span>
                            </span>
                            <span class="inline-block w-12 h-1 rounded-full bg-slate-600 group-hover:bg-indigo-400 transition-colors"></span>
                        </div>
                    </div>
                </div>

                {{-- Map Legend & Data Provenance Panel --}}
                <div class="bg-slate-900/90 rounded-2xl p-4 shadow-sm border border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs text-slate-300">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="font-bold text-slate-200">{{ __('Legenda:') }}</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Presence</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Apparent Fishing</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span> Potential Encounter</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Loitering</span>
                        <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Port Visit</span>
                    </div>
                    <div class="text-[11px] text-slate-400">
                        {{ __('Sumber Data:') }} <strong class="text-slate-200">{{ __('Global Fishing Watch API v3') }}</strong> &bull; {{ __('Diperbarui via Proxy Internal Laravel') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Frontend Map Logic using Internal Laravel GFW API --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Initialize Map
            const map = L.map('gfw-map', {
                center: [5.55, 95.32], // Default Aceh waters
                zoom: 7,
                minZoom: 3,
                maxZoom: 18,
            });

            // Base Layers (Light Mode Default: OpenStreetMap)
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors &bull; Data &copy; Global Fishing Watch',
                maxZoom: 19
            });

            const oceanLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri &bull; Data &copy; Global Fishing Watch',
                maxZoom: 13
            });

            const darkLayer = L.tileLayer('https://services.arcgisonline.com/ArcGIS/rest/services/Canvas/World_Dark_Gray_Base/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Esri, DeLorme, NAVTEQ &bull; Data &copy; Global Fishing Watch',
                maxZoom: 16
            });

            osmLayer.addTo(map);
            L.control.layers({
                'Peta Terang (OpenStreetMap)': osmLayer,
                'Peta Oseanografi (Esri Ocean)': oceanLayer,
                'Peta Gelap (Esri Dark)': darkLayer
            }, null, { position: 'topright' }).addTo(map);

            // Layer Groups
            const layerPresence = L.layerGroup().addTo(map);
            const layerFishing = L.layerGroup().addTo(map);
            const layerEncounters = L.layerGroup().addTo(map);
            const layerLoitering = L.layerGroup().addTo(map);
            const layerPortVisits = L.layerGroup().addTo(map);
            const layerTracks = L.layerGroup().addTo(map);
            const layerBoundary = L.layerGroup().addTo(map);

            // UI Elements
            const statusOverlay = document.getElementById('map-status-overlay');
            const statusText = document.getElementById('map-status-text');
            const statusIcon = document.getElementById('map-status-icon');
            const regionSelect = document.getElementById('filter-region');
            const startDateInput = document.getElementById('filter-start-date');
            const endDateInput = document.getElementById('filter-end-date');

            function showStatus(text, icon = '🔄', isError = false) {
                statusText.innerText = text;
                statusIcon.innerText = icon;
                statusOverlay.classList.remove('hidden');
                statusOverlay.classList.toggle('bg-rose-950/95', isError);
                statusOverlay.classList.toggle('border-rose-700', isError);
                statusOverlay.classList.toggle('bg-slate-900/95', !isError);
                statusOverlay.classList.toggle('border-slate-700', !isError);
            }

            function hideStatus() {
                statusOverlay.classList.add('hidden');
            }

            // 2. Fetch Regional Boundary & Zoom
            async function updateRegionBoundary(regionKey) {
                try {
                    layerBoundary.clearLayers();
                    const res = await fetch(`/api/gfw/regions/${encodeURIComponent(regionKey)}`);
                    const json = await res.json();
                    if (json.success && json.data) {
                        const reg = json.data;
                        if (reg.bounding_box && reg.bounding_box.length === 4) {
                            const [minLon, minLat, maxLon, maxLat] = reg.bounding_box;
                            const bounds = [[minLat, minLon], [maxLat, maxLon]];
                            L.rectangle(bounds, {
                                color: '#0284c7',
                                weight: 2.0,
                                fillOpacity: 0.05,
                                dashArray: '4, 4'
                            }).addTo(layerBoundary);
                            map.fitBounds(bounds, { padding: [20, 20] });
                        }
                    }
                } catch (e) {
                    console.warn('Error fetching region boundary', e);
                }
            }

            // Auto-handle map resize
            const mapCardWrapper = document.getElementById('map-card-wrapper');
            if (mapCardWrapper && window.ResizeObserver) {
                new ResizeObserver(() => {
                    map.invalidateSize();
                }).observe(mapCardWrapper);
            }

            // Map Dimension Controls (Height Presets + Drag Resize)
            (function initMonitoringMapDimensions() {
                const mapContainer = mapCardWrapper;
                const resizeHandle = document.getElementById('monitoring-map-resize-handle');
                const heightDisplay = document.getElementById('monitoring-map-height-display');
                const dimensionBadge = document.getElementById('map-dimension-badge');
                const presetHeightButtons = document.querySelectorAll('.btn-preset-monitoring-height');

                const MIN_HEIGHT = 380;
                const MAX_HEIGHT = 1400;
                let currentHeight = 640;

                const updateBadge = () => {
                    if (dimensionBadge) dimensionBadge.textContent = `${currentHeight}px`;
                };

                const applyHeight = (height, save = true) => {
                    const clamped = Math.max(MIN_HEIGHT, Math.min(MAX_HEIGHT, Math.round(height)));
                    currentHeight = clamped;
                    if (mapContainer) mapContainer.style.height = `${clamped}px`;
                    if (heightDisplay) heightDisplay.textContent = `${clamped}px`;
                    updateBadge();

                    presetHeightButtons.forEach(btn => {
                        const h = parseInt(btn.dataset.mapHeight, 10);
                        if (Math.abs(h - clamped) < 25) {
                            btn.classList.add('bg-indigo-600', 'text-white', 'border-indigo-500');
                            btn.classList.remove('bg-slate-800', 'text-slate-300', 'border-slate-700');
                        } else {
                            btn.classList.remove('bg-indigo-600', 'text-white', 'border-indigo-500');
                            btn.classList.add('bg-slate-800', 'text-slate-300', 'border-slate-700');
                        }
                    });

                    if (save) {
                        try { localStorage.setItem('gfw_monitoring_map_height', clamped); } catch (e) {}
                    }
                    map.invalidateSize();
                };

                // Restore saved height
                try {
                    const savedHeight = parseInt(localStorage.getItem('gfw_monitoring_map_height'), 10);
                    if (savedHeight && !isNaN(savedHeight) && savedHeight >= MIN_HEIGHT && savedHeight <= MAX_HEIGHT) {
                        applyHeight(savedHeight, false);
                    }
                } catch (e) {}

                // Preset height buttons
                presetHeightButtons.forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const h = parseInt(btn.dataset.mapHeight, 10);
                        if (h) applyHeight(h, true);
                    });
                });

                // Vertical drag handle
                if (resizeHandle && mapContainer) {
                    let startY = 0;
                    let startHeight = 0;
                    let isDragging = false;

                    const onMouseMove = (e) => {
                        if (!isDragging) return;
                        const clientY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : null);
                        if (clientY === null || clientY === undefined) return;
                        const deltaY = clientY - startY;
                        applyHeight(startHeight + deltaY, false);
                    };

                    const onMouseUp = () => {
                        if (!isDragging) return;
                        isDragging = false;
                        document.body.style.cursor = '';
                        document.body.style.userSelect = '';
                        window.removeEventListener('mousemove', onMouseMove);
                        window.removeEventListener('mouseup', onMouseUp);
                        window.removeEventListener('touchmove', onMouseMove);
                        window.removeEventListener('touchend', onMouseUp);

                        const finalHeight = parseInt(mapContainer.style.height, 10);
                        if (finalHeight) {
                            try { localStorage.setItem('gfw_monitoring_map_height', finalHeight); } catch (e) {}
                        }
                    };

                    const onMouseDown = (e) => {
                        isDragging = true;
                        startY = e.clientY ?? (e.touches && e.touches[0] ? e.touches[0].clientY : 0);
                        startHeight = mapContainer.offsetHeight;
                        document.body.style.cursor = 'row-resize';
                        document.body.style.userSelect = 'none';
                        window.addEventListener('mousemove', onMouseMove, { passive: true });
                        window.addEventListener('mouseup', onMouseUp);
                        window.addEventListener('touchmove', onMouseMove, { passive: true });
                        window.addEventListener('touchend', onMouseUp);
                    };

                    resizeHandle.addEventListener('mousedown', onMouseDown);
                    resizeHandle.addEventListener('touchstart', onMouseDown, { passive: true });
                }
            })();

            // 3. Load All Active GFW Layers
            async function loadGfwData() {
                const region = regionSelect.value;
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;

                showStatus('Loading GFW data...', '🔄');

                // Clear existing markers
                layerPresence.clearLayers();
                layerFishing.clearLayers();
                layerEncounters.clearLayers();
                layerLoitering.clearLayers();
                layerPortVisits.clearLayers();

                let totalObservations = 0;
                let hasError = false;

                try {
                    await updateRegionBoundary(region);

                    // A. Vessel Presence
                    if (document.getElementById('layer-presence').checked) {
                        const res = await fetch(`/api/gfw/activity/presence?region=${encodeURIComponent(region)}&start_date=${startDate}&end_date=${endDate}`);
                        const json = await res.json();
                        if (json.success && Array.isArray(json.data)) {
                            document.getElementById('count-presence').innerText = json.data.length;
                            totalObservations += json.data.length;
                            json.data.forEach(item => {
                                if (item.latitude && item.longitude) {
                                    const marker = L.circleMarker([item.latitude, item.longitude], {
                                        radius: 5.5,
                                        fillColor: '#10b981',
                                        color: '#ffffff',
                                        weight: 1.8,
                                        fillOpacity: 0.9
                                    });
                                    marker.bindPopup(`
                                        <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; color: #f1f5f9; min-width: 190px;">
                                             <strong style="color: #34d399;">🟢 GFW Vessel Presence</strong><br>
                                             <strong style="color: #94a3b8;">Vessel ID:</strong> <span style="font-family: monospace;">${item.gfw_vessel_id || 'N/A'}</span><br>
                                             <strong style="color: #94a3b8;">Posisi:</strong> <span style="font-family: monospace;">${item.latitude}, ${item.longitude}</span><br>
                                             <strong style="color: #94a3b8;">Waktu:</strong> ${item.observation_timestamp || item.observed_at || '-'}<br>
                                             <div style="margin-top: 6px; font-size: 10px; color: #64748b; border-top: 1px solid #334155; padding-top: 4px;">
                                                 Data Source: Global Fishing Watch
                                             </div>
                                         </div>
                                     `);
                                     marker.addTo(layerPresence);
                                 }
                             });
                         }
                     }

                     // B. Apparent Fishing Events
                     if (document.getElementById('layer-fishing').checked) {
                         const res = await fetch(`/api/gfw/events/fishing?region=${encodeURIComponent(region)}&start_date=${startDate}&end_date=${endDate}`);
                         const json = await res.json();
                         if (json.success && Array.isArray(json.data)) {
                             document.getElementById('count-fishing').innerText = json.data.length;
                             totalObservations += json.data.length;
                             json.data.forEach(item => {
                                 if (item.latitude && item.longitude) {
                                     const marker = L.circleMarker([item.latitude, item.longitude], {
                                         radius: 6,
                                         fillColor: '#f43f5e',
                                         color: '#ffffff',
                                         weight: 1.8,
                                         fillOpacity: 0.9
                                     });
                                     marker.bindPopup(`
                                         <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; max-width: 250px; color: #f1f5f9;">
                                             <strong style="color: #fb7185;">🎣 Apparent Fishing Event</strong><br>
                                             <strong style="color: #94a3b8;">Vessel ID:</strong> <span style="font-family: monospace;">${item.gfw_vessel_id || 'N/A'}</span><br>
                                             <strong style="color: #94a3b8;">Durasi:</strong> ${item.duration_hours || '-'} jam<br>
                                             <strong style="color: #94a3b8;">Confidence:</strong> ${item.confidence || '-'}<br>
                                             <strong style="color: #94a3b8;">Waktu:</strong> ${item.start_time || '-'} s/d ${item.end_time || '-'}<br>
                                             <div style="background: rgba(136, 19, 55, 0.4); color: #fecdd3; border: 1px solid rgba(225, 29, 72, 0.4); padding: 4px 6px; border-radius: 6px; font-size: 10px; margin-top: 6px;">
                                                 ⚠️ <em>Indikasi analitik algoritma pergerakan AIS/VMS, bukan verifikasi penangkapan faktual.</em>
                                             </div>
                                             <div style="margin-top: 4px; font-size: 10px; color: #64748b;">
                                                 Source: Global Fishing Watch
                                             </div>
                                         </div>
                                     `);
                                     marker.addTo(layerFishing);
                                 }
                             });
                         }
                     }

                     // C. Potential Encounters
                     if (document.getElementById('layer-encounters').checked) {
                         const res = await fetch(`/api/gfw/events/encounters?region=${encodeURIComponent(region)}&start_date=${startDate}&end_date=${endDate}`);
                         const json = await res.json();
                         if (json.success && Array.isArray(json.data)) {
                             document.getElementById('count-encounters').innerText = json.data.length;
                             totalObservations += json.data.length;
                             json.data.forEach(item => {
                                 if (item.latitude && item.longitude) {
                                     const marker = L.circleMarker([item.latitude, item.longitude], {
                                         radius: 6,
                                         fillColor: '#f97316',
                                         color: '#ffffff',
                                         weight: 1.8,
                                         fillOpacity: 0.9
                                     });
                                     marker.bindPopup(`
                                         <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; max-width: 250px; color: #f1f5f9;">
                                             <strong style="color: #fb923c;">🤝 Potential Encounter</strong><br>
                                             <strong style="color: #94a3b8;">Kapal 1:</strong> <span style="font-family: monospace;">${item.gfw_vessel_id || 'N/A'}</span><br>
                                             <strong style="color: #94a3b8;">Kapal 2:</strong> <span style="font-family: monospace;">${item.secondary_vessel_id || 'N/A'}</span><br>
                                             <strong style="color: #94a3b8;">Durasi:</strong> ${item.duration_hours || '-'} jam<br>
                                             <div style="background: rgba(154, 52, 18, 0.4); color: #fed7aa; border: 1px solid rgba(234, 88, 12, 0.4); padding: 4px 6px; border-radius: 6px; font-size: 10px; margin-top: 6px;">
                                                 ⚠️ <em>Kedekatan posisi dua kapal secara algoritmik, tidak dapat disimpulkan sebagai alih muatan (transshipment).</em>
                                             </div>
                                         </div>
                                     `);
                                     marker.addTo(layerEncounters);
                                 }
                             });
                         }
                     }

                     // D. Loitering Events
                     if (document.getElementById('layer-loitering').checked) {
                         const res = await fetch(`/api/gfw/events/loitering?region=${encodeURIComponent(region)}&start_date=${startDate}&end_date=${endDate}`);
                         const json = await res.json();
                         if (json.success && Array.isArray(json.data)) {
                             document.getElementById('count-loitering').innerText = json.data.length;
                             totalObservations += json.data.length;
                             json.data.forEach(item => {
                                 if (item.latitude && item.longitude) {
                                     const marker = L.circleMarker([item.latitude, item.longitude], {
                                         radius: 6,
                                         fillColor: '#9333ea',
                                         color: '#ffffff',
                                         weight: 1.8,
                                         fillOpacity: 0.9
                                     });
                                     marker.bindPopup(`
                                         <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; color: #f1f5f9;">
                                             <strong style="color: #c084fc;">⚓ Loitering Event</strong><br>
                                             <strong style="color: #94a3b8;">Vessel ID:</strong> <span style="font-family: monospace;">${item.gfw_vessel_id || 'N/A'}</span><br>
                                             <strong style="color: #94a3b8;">Durasi:</strong> ${item.duration_hours || '-'} jam<br>
                                             <div style="font-size: 10px; color: #64748b; margin-top: 4px;">Source: Global Fishing Watch</div>
                                         </div>
                                     `);
                                     marker.addTo(layerLoitering);
                                 }
                             });
                         }
                     }

                     // E. Port Visits
                     if (document.getElementById('layer-port-visits').checked) {
                         const res = await fetch(`/api/gfw/events/port-visits?region=${encodeURIComponent(region)}&start_date=${startDate}&end_date=${endDate}`);
                         const json = await res.json();
                         if (json.success && Array.isArray(json.data)) {
                             document.getElementById('count-port-visits').innerText = json.data.length;
                             totalObservations += json.data.length;
                             json.data.forEach(item => {
                                 if (item.latitude && item.longitude) {
                                     const marker = L.circleMarker([item.latitude, item.longitude], {
                                         radius: 6,
                                         fillColor: '#f59e0b',
                                         color: '#ffffff',
                                         weight: 1.8,
                                         fillOpacity: 0.9
                                     });
                                    marker.bindPopup(`
                                        <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; color: #f1f5f9;">
                                            <strong style="color: #fbbf24;">🚢 Port Visit</strong><br>
                                            <strong style="color: #94a3b8;">Pelabuhan:</strong> ${item.port_name || 'N/A'}<br>
                                            <strong style="color: #94a3b8;">Vessel ID:</strong> <span style="font-family: monospace;">${item.gfw_vessel_id || 'N/A'}</span><br>
                                            <strong style="color: #94a3b8;">Durasi:</strong> ${item.duration_hours || '-'} jam
                                        </div>
                                    `);
                                    marker.addTo(layerPortVisits);
                                }
                            });
                        }
                    }

                    if (totalObservations === 0) {
                        showStatus('No GFW observations found for the selected period and region.', 'ℹ️');
                        setTimeout(hideStatus, 4000);
                    } else {
                        hideStatus();
                    }
                } catch (err) {
                    console.error('GFW fetch failed', err);
                    showStatus('GFW data temporarily unavailable.', '⚠️', true);
                    setTimeout(hideStatus, 5000);
                }
            }

            // 4. Single Vessel Track Search
            document.getElementById('btn-search-vessel').addEventListener('click', async function () {
                const query = document.getElementById('filter-vessel-query').value.trim();
                const resultDiv = document.getElementById('vessel-search-result');
                if (!query) return;

                resultDiv.classList.remove('hidden');
                resultDiv.innerHTML = '<span class="text-indigo-400">Mencari kapal di gateway GFW...</span>';

                try {
                    const searchRes = await fetch(`/api/gfw/vessels?query=${encodeURIComponent(query)}`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const searchContentType = searchRes.headers.get('content-type') || '';
                    if (!searchContentType.includes('application/json')) {
                        throw new Error(`Server mengembalikan response non-JSON (HTTP ${searchRes.status}).`);
                    }
                    const searchJson = await searchRes.json();

                    if (searchJson.success && Array.isArray(searchJson.data) && searchJson.data.length > 0) {
                        const vessel = searchJson.data[0];
                        const vesselIdToFetch = vessel.gfw_vessel_id || vessel.id;
                        resultDiv.innerHTML = `<div class="p-2 bg-emerald-950/70 border border-emerald-800 text-emerald-200 rounded-lg">Ditemukan: <strong class="text-white">${vessel.name || vesselIdToFetch}</strong> (MMSI: ${vessel.mmsi || '-'})</div>`;

                        // Fetch track
                        const startDate = startDateInput.value;
                        const endDate = endDateInput.value;
                        const trackRes = await fetch(`/api/gfw/activity/vessels/${encodeURIComponent(vesselIdToFetch)}?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`, {
                            headers: { 'Accept': 'application/json' }
                        });
                        const trackContentType = trackRes.headers.get('content-type') || '';
                        if (!trackContentType.includes('application/json')) {
                            throw new Error(`Server mengembalikan response non-JSON (HTTP ${trackRes.status}).`);
                        }
                        const trackJson = await trackRes.json();

                        layerTracks.clearLayers();
                        if (trackJson.success && Array.isArray(trackJson.data) && trackJson.data.length > 0) {
                            document.getElementById('count-tracks').innerText = trackJson.data.length;
                            const latLngs = [];
                            trackJson.data.forEach(pt => {
                                if (pt.latitude && pt.longitude) {
                                    latLngs.push([pt.latitude, pt.longitude]);
                                    L.circleMarker([pt.latitude, pt.longitude], {
                                        radius: 4.5,
                                        fillColor: '#0284c7',
                                        color: '#ffffff',
                                        weight: 1.8,
                                        fillOpacity: 0.95
                                    }).bindPopup(`
                                        <div style="font-family: sans-serif; font-size: 11px; color: #f1f5f9;">
                                            <strong style="color: #38bdf8;">📍 Track Point</strong><br>
                                            <span style="color: #94a3b8;">Waktu:</span> ${pt.observation_timestamp || pt.observed_at || pt.timestamp || '-'}<br>
                                            <span style="color: #94a3b8;">Kecepatan:</span> ${pt.speed_knots !== null && pt.speed_knots !== undefined ? pt.speed_knots : (pt.speed || '-')} knots
                                        </div>
                                    `).addTo(layerTracks);
                                }
                            });

                            if (latLngs.length > 1) {
                                const polyline = L.polyline(latLngs, { color: '#f59e0b', weight: 3.5, opacity: 0.95 }).addTo(layerTracks);
                                map.fitBounds(polyline.getBounds(), { padding: [30, 30] });
                            }
                        } else {
                            const trackErrMsg = trackJson?.message || trackJson?.error || 'Tidak ada data lintasan kapal untuk periode ini.';
                            resultDiv.innerHTML += `<div class="mt-2 text-xs text-amber-300">ℹ️ ${trackErrMsg}</div>`;
                        }
                    } else {
                        resultDiv.innerHTML = '<span class="text-rose-400">Kapal tidak ditemukan pada data GFW.</span>';
                    }
                } catch (e) {
                    const isNetwork = e instanceof TypeError || (e.message && (e.message.includes('NetworkError') || e.message.includes('Failed to fetch')));
                    resultDiv.innerHTML = `<span class="text-rose-400">${isNetwork ? 'Koneksi terputus saat mengambil data track.' : (e.message || 'Gagal mencari kapal.')}</span>`;
                }
            });

            // 5. Layer visibility toggles
            document.getElementById('layer-presence').addEventListener('change', e => e.target.checked ? map.addLayer(layerPresence) : map.removeLayer(layerPresence));
            document.getElementById('layer-fishing').addEventListener('change', e => e.target.checked ? map.addLayer(layerFishing) : map.removeLayer(layerFishing));
            document.getElementById('layer-encounters').addEventListener('change', e => e.target.checked ? map.addLayer(layerEncounters) : map.removeLayer(layerEncounters));
            document.getElementById('layer-loitering').addEventListener('change', e => e.target.checked ? map.addLayer(layerLoitering) : map.removeLayer(layerLoitering));
            document.getElementById('layer-port-visits').addEventListener('change', e => e.target.checked ? map.addLayer(layerPortVisits) : map.removeLayer(layerPortVisits));
            document.getElementById('layer-activity-tracks').addEventListener('change', e => e.target.checked ? map.addLayer(layerTracks) : map.removeLayer(layerTracks));

            // 6. Buttons
            document.getElementById('btn-apply-filter').addEventListener('click', loadGfwData);
            document.getElementById('btn-refresh-all').addEventListener('click', loadGfwData);

            // Initial load
            loadGfwData();
        });
    </script>
</x-app-layout>
