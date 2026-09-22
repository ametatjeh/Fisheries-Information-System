<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xl">🔭</span>
                <span class="font-bold text-slate-800">{{ __('GFW Vessel Observatory — Aceh & Perairan Sekitarnya') }}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>📊</span>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('gfw.monitoring') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>🛰️</span>
                    <span>{{ __('GFW Monitoring') }}</span>
                </a>
                <a href="{{ route('gfw.vessels') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>🚢</span>
                    <span>{{ __('Vessel Monitoring') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- MapLibre GL JS CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" crossorigin=""/>
    <script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" crossorigin=""></script>

    <div class="space-y-6">
        {{-- Banner Header --}}
        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 text-white p-5 rounded-2xl shadow-sm relative overflow-hidden border border-emerald-900/40">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🔭</div>
            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div class="max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🔭</span>
                        <span>{{ __('GFW Vessel Observatory — Local Database') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                        <span>🔭</span>
                        <span>{{ __('GFW Vessel Observatory — Aceh & Perairan Sekitarnya') }}</span>
                    </h2>
                    <p class="text-emerald-200 text-xs sm:text-sm mt-1 leading-relaxed">
                        {{ __('Observasi kapal-kapal yang terdeteksi/teramati oleh Global Fishing Watch (GFW) di wilayah ZEE Indonesia Kawasan Aceh. Data disinkronisasi secara terkendali dari GFW API ke database lokal terisolasi.') }}
                    </p>
                </div>

                {{-- Latency & AOI Summary Badge --}}
                <div class="px-4 py-3 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-xs max-w-md shrink-0">
                    <div class="font-bold flex items-center gap-1 text-amber-300">
                        <span>⚠️</span>
                        <span>{{ __('Data Provenance & AOI') }}</span>
                    </div>
                    <p class="mt-1 leading-normal opacity-90">
                        {{ $latencyNotice }}
                    </p>
                    <div class="mt-2 pt-2 border-t border-amber-500/20 text-[11px] text-amber-300/90 flex items-center justify-between">
                        <span>AOI: <strong>{{ $aoiSummary['name'] ?? 'ZEE Indonesia - Kawasan Aceh' }}</strong></span>
                        <span class="font-mono text-[10px] bg-amber-400/20 px-1.5 py-0.5 rounded text-amber-200">EPSG:4326</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="observatory-kpi-cards">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200/80">
                <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">{{ __('Total Kapal') }}</div>
                <div class="text-2xl font-bold text-slate-800 mt-1" id="kpi-total-vessels">{{ $stats['total_vessels'] ?? 0 }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Tersimpan di Observatory') }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200/80">
                <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">{{ __('Total Presence') }}</div>
                <div class="text-2xl font-bold text-emerald-600 mt-1" id="kpi-total-presence">{{ number_format($stats['total_presence'] ?? 0) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Observasi titik spasial') }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200/80">
                <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">{{ __('Tipe Kapal') }}</div>
                <div class="text-2xl font-bold text-indigo-600 mt-1" id="kpi-vessel-types">{{ count($stats['vessels_by_type'] ?? []) }}</div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Kategori unik') }}</div>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-200/80">
                <div class="text-slate-500 text-xs font-semibold uppercase tracking-wider">{{ __('Sync Status') }}</div>
                <div class="text-2xl font-bold mt-1 {{ ($syncStatus['health'] ?? 'unknown') === 'healthy' ? 'text-emerald-600' : 'text-amber-600' }}" id="kpi-sync-health">
                    {{ ucfirst($syncStatus['health'] ?? 'Unknown') }}
                </div>
                <div class="text-[11px] text-slate-400 mt-0.5">{{ __('Total runs: ') }}{{ $syncStatus['total_runs'] ?? 0 }}</div>
            </div>
        </div>

        {{-- Main Content: Map + Vessel Table --}}
        <div class="grid grid-cols-1 xl:grid-cols-5 gap-6">

            {{-- Presence Map --}}
            <div class="xl:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-200/80 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">🗺️</span>
                        <h3 class="font-semibold text-sm text-slate-700">{{ __('Peta Presence Kapal Observatory') }}</h3>
                    </div>
                    <button type="button" id="btn-refresh-map" class="px-2.5 py-1 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 text-xs transition">
                        🔄 {{ __('Refresh') }}
                    </button>
                </div>
                <div id="observatory-map" style="height: 480px; width: 100%;"></div>
            </div>

            {{-- Vessel Type Breakdown & Sync Info --}}
            <div class="xl:col-span-2 space-y-4">
                {{-- Vessel Type Distribution --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2 mb-3">
                        <span>📊</span>
                        <span>{{ __('Distribusi Tipe Kapal') }}</span>
                    </h3>
                    <div class="space-y-2" id="vessel-type-distribution">
                        @forelse($stats['vessels_by_type'] ?? [] as $type => $count)
                            @php
                                $total = max(1, $stats['total_vessels'] ?? 1);
                                $pct = round(($count / $total) * 100, 1);
                                $colors = [
                                    'fishing' => 'bg-emerald-500',
                                    'cargo' => 'bg-blue-500',
                                    'tanker' => 'bg-amber-500',
                                    'passenger' => 'bg-purple-500',
                                    'tug' => 'bg-orange-500',
                                    'service' => 'bg-cyan-500',
                                    'carrier' => 'bg-red-500',
                                ];
                                $barColor = $colors[$type] ?? 'bg-slate-400';
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-semibold text-slate-600 w-24 truncate capitalize">{{ $type }}</span>
                                <div class="flex-1 bg-slate-100 rounded-full h-2">
                                    <div class="{{ $barColor }} h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-bold text-slate-700 w-10 text-right">{{ $count }}</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-4">{{ __('Belum ada data kapal.') }}</div>
                        @endforelse
                    </div>
                </div>

                {{-- Flag Distribution --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2 mb-3">
                        <span>🏴</span>
                        <span>{{ __('Distribusi Bendera') }}</span>
                    </h3>
                    <div class="flex flex-wrap gap-2" id="flag-distribution">
                        @forelse($stats['vessels_by_flag'] ?? [] as $flag => $count)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 text-xs font-semibold text-slate-700 border border-slate-200">
                                <span class="uppercase">{{ $flag }}</span>
                                <span class="ml-1.5 bg-slate-200 text-slate-600 px-1.5 py-0.5 rounded text-[10px]">{{ $count }}</span>
                            </span>
                        @empty
                            <span class="text-xs text-slate-400">{{ __('Belum ada data bendera.') }}</span>
                        @endforelse
                    </div>
                </div>

                {{-- Last Sync Summary --}}
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <h3 class="font-semibold text-sm text-slate-700 flex items-center gap-2 mb-3">
                        <span>🔄</span>
                        <span>{{ __('Sinkronisasi Terakhir') }}</span>
                    </h3>
                    @if($syncStatus['last_run'] ?? null)
                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ __('Status') }}</span>
                                <span class="font-semibold {{ ($syncStatus['last_run']['status'] ?? '') === 'success' ? 'text-emerald-600' : 'text-amber-600' }}">
                                    {{ ucfirst($syncStatus['last_run']['status'] ?? 'N/A') }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ __('Selesai') }}</span>
                                <span class="font-mono text-slate-700">{{ $syncStatus['last_run']['finished_at'] ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">{{ __('Records') }}</span>
                                <span class="font-semibold text-slate-700">{{ ($syncStatus['last_run']['records_found'] ?? 0) }} → {{ ($syncStatus['last_run']['records_saved'] ?? 0) }}</span>
                            </div>
                        </div>
                    @else
                        <div class="text-xs text-slate-400 text-center py-2">{{ __('Belum ada sinkronisasi.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Search & Filter Controls --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                <div class="md:col-span-4">
                    <label for="obs-search-input" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        {{ __('Cari Kapal (Nama / MMSI / IMO / GFW ID)') }}
                    </label>
                    <div class="relative">
                        <input type="text"
                               id="obs-search-input"
                               placeholder="Ketik nama kapal, MMSI, IMO..."
                               class="w-full text-xs pl-8 pr-8 py-2.5 rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 transition shadow-2xs">
                        <span class="absolute left-2.5 top-2.5 text-slate-400 text-xs pointer-events-none">🔍</span>
                        <button type="button" id="btn-clear-obs-search" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600 text-sm hidden font-bold">✕</button>
                    </div>
                </div>
                <div class="md:col-span-3">
                    <label for="obs-type-filter" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        {{ __('Tipe Kapal') }}
                    </label>
                    <select id="obs-type-filter" class="w-full text-xs py-2.5 rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 transition shadow-2xs">
                        <option value="">{{ __('Semua Tipe') }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label for="obs-flag-filter" class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">
                        {{ __('Bendera') }}
                    </label>
                    <select id="obs-flag-filter" class="w-full text-xs py-2.5 rounded-xl border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 transition shadow-2xs">
                        <option value="">{{ __('Semua Bendera') }}</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="button" id="btn-obs-search" class="w-full px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-xs flex items-center justify-center gap-1.5">
                        <span>🔍</span>
                        <span>{{ __('Cari') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Vessel Table --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-200/80 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-sm">🚢</span>
                    <h3 class="font-semibold text-sm text-slate-700">{{ __('Daftar Kapal Observatory') }}</h3>
                </div>
                <div class="flex items-center gap-2 text-xs text-slate-500" id="obs-table-meta">
                    <span id="obs-table-total">0</span> {{ __('kapal ditemukan') }}
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs" id="obs-vessel-table">
                    <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold cursor-pointer hover:text-slate-800" data-sort="name">{{ __('Nama') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('GFW ID') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('MMSI') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('IMO') }}</th>
                            <th class="px-4 py-3 text-left font-semibold cursor-pointer hover:text-slate-800" data-sort="vessel_type">{{ __('Tipe') }}</th>
                            <th class="px-4 py-3 text-left font-semibold cursor-pointer hover:text-slate-800" data-sort="flag">{{ __('Bendera') }}</th>
                            <th class="px-4 py-3 text-left font-semibold">{{ __('Panjang') }}</th>
                            <th class="px-4 py-3 text-left font-semibold cursor-pointer hover:text-slate-800" data-sort="last_synced_at">{{ __('Terakhir Sync') }}</th>
                            <th class="px-4 py-3 text-center font-semibold">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody id="obs-vessel-tbody" class="divide-y divide-slate-100 vessels-table-body" data-testid="vessels-table-body">
                        <tr>
                            <td colspan="10" class="px-4 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-3xl">🔭</span>
                                    <span>{{ __('Memuat data kapal observatory...') }}</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            {{-- Pagination --}}
            <div class="px-5 py-3 border-t border-slate-200/80 flex items-center justify-between" id="obs-pagination-controls">
                <div class="text-xs text-slate-500" id="obs-pagination-info">—</div>
                <div class="flex items-center gap-1" id="obs-pagination-buttons"></div>
            </div>
        </div>

        {{-- Vessel Detail Drawer --}}
        <div id="obs-vessel-drawer" class="fixed inset-0 z-50 hidden vessel-detail-drawer" data-testid="vessel-detail-drawer">
            <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" id="obs-drawer-overlay"></div>
            <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl transform transition-transform duration-300 translate-x-full overflow-y-auto" id="obs-drawer-panel">
                <div class="sticky top-0 bg-white border-b border-slate-200 px-5 py-4 flex items-center justify-between z-10">
                    <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                        <span>🚢</span>
                        <span id="obs-drawer-title">{{ __('Detail Kapal') }}</span>
                    </h3>
                    <button type="button" id="btn-close-obs-drawer" class="text-slate-400 hover:text-slate-600 text-lg font-bold transition">✕</button>
                </div>
                <div class="p-5 space-y-4" id="obs-drawer-content">
                    <div class="text-center py-8 text-slate-400 text-sm">{{ __('Memuat detail...') }}</div>
                </div>
            </div>
        </div>

        {{-- Disclaimer --}}
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/60 text-[11px] text-slate-500 leading-relaxed">
            <strong class="text-slate-600">{{ __('Disclaimer:') }}</strong>
            {{ __('Data Observatory berasal dari kapal-kapal yang terdeteksi/teramati oleh Global Fishing Watch (GFW) dalam Area of Interest (AOI) yang dikonfigurasi. Data telah disinkronisasi secara terkendali ke database lokal terisolasi (sistem_gfw) dan tidak bersifat real-time. Sistem ini tidak mengklaim memantau seluruh kapal fisik di perairan Aceh.') }}
        </div>
    </div>

    {{-- JavaScript --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_BASE = '/api/gfw/observatory';
        let currentPage = 1;
        let currentSearch = '';
        let currentType = '';
        let currentFlag = '';
        let mapInstance = null;
        let presenceMarkers = [];

        // === MAP INITIALIZATION ===
        function initMap() {
            mapInstance = new maplibregl.Map({
                container: 'observatory-map',
                style: { version: 8, sources: { osm: { type: 'raster', tiles: ['https://tile.openstreetmap.org/{z}/{x}/{y}.png'], tileSize: 256, attribution: '&copy; OpenStreetMap contributors' } }, layers: [{ id: 'osm', type: 'raster', source: 'osm' }] },
                center: [96.5, 4.5],
                zoom: 6,
                maxZoom: 16,
                minZoom: 3
            });
            mapInstance.addControl(new maplibregl.NavigationControl(), 'top-right');
            mapInstance.addControl(new maplibregl.ScaleControl({ maxWidth: 150, unit: 'metric' }), 'bottom-left');

            // Load AOI boundary
            fetch('/api/gfw/aoi/zee-indonesia-aceh')
                .then(r => r.json())
                .then(data => {
                    if (data?.data?.features || data?.data?.geometry) {
                        const geojson = data.data.features ? data.data : { type: 'FeatureCollection', features: [{ type: 'Feature', geometry: data.data.geometry || data.data, properties: {} }] };
                        mapInstance.on('load', () => {
                            if (mapInstance.getSource('aoi-boundary')) return;
                            mapInstance.addSource('aoi-boundary', { type: 'geojson', data: geojson });
                            mapInstance.addLayer({ id: 'aoi-fill', type: 'fill', source: 'aoi-boundary', paint: { 'fill-color': '#10b981', 'fill-opacity': 0.06 } });
                            mapInstance.addLayer({ id: 'aoi-line', type: 'line', source: 'aoi-boundary', paint: { 'line-color': '#10b981', 'line-width': 2, 'line-dasharray': [4, 2] } });
                        });
                    }
                })
                .catch(() => {});
        }

        function clearPresenceMarkers() {
            presenceMarkers.forEach(m => m.remove());
            presenceMarkers = [];
            if (mapInstance && mapInstance.getSource('presence-data')) {
                mapInstance.removeLayer('presence-heatmap');
                mapInstance.removeLayer('presence-points');
                mapInstance.removeSource('presence-data');
            }
        }

        function loadPresenceOnMap(gfwVesselId) {
            fetch(`${API_BASE}/vessels/${encodeURIComponent(gfwVesselId)}/presence?per_page=100`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data.length) return;
                    clearPresenceMarkers();
                    const features = res.data.map(p => ({
                        type: 'Feature',
                        geometry: { type: 'Point', coordinates: [p.longitude, p.latitude] },
                        properties: { observed_at: p.observed_at, speed: p.speed, course: p.course, vessel_type: p.vessel_type }
                    }));
                    const geojson = { type: 'FeatureCollection', features };

                    if (!mapInstance.isStyleLoaded()) {
                        mapInstance.on('load', () => addPresenceLayer(geojson, features));
                    } else {
                        addPresenceLayer(geojson, features);
                    }
                })
                .catch(() => {});
        }

        function addPresenceLayer(geojson, features) {
            if (mapInstance.getSource('presence-data')) {
                mapInstance.getSource('presence-data').setData(geojson);
            } else {
                mapInstance.addSource('presence-data', { type: 'geojson', data: geojson });
                mapInstance.addLayer({
                    id: 'presence-points', type: 'circle', source: 'presence-data',
                    paint: { 'circle-radius': 5, 'circle-color': '#10b981', 'circle-opacity': 0.8, 'circle-stroke-width': 1, 'circle-stroke-color': '#fff' }
                });
            }
            if (features.length > 0) {
                const bounds = new maplibregl.LngLatBounds();
                features.forEach(f => bounds.extend(f.geometry.coordinates));
                mapInstance.fitBounds(bounds, { padding: 60, maxZoom: 12 });
            }
        }

        // === VESSEL TABLE ===
        function loadVessels(page = 1) {
            currentPage = page;
            const params = new URLSearchParams({ page, per_page: 15 });
            if (currentSearch) params.set('search', currentSearch);
            if (currentType) params.set('vessel_type', currentType);
            if (currentFlag) params.set('flag', currentFlag);

            const tbody = document.getElementById('obs-vessel-tbody');
            tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-8 text-center text-slate-400"><span class="animate-pulse">⏳ {{ __("Memuat...") }}</span></td></tr>';

            fetch(`${API_BASE}/vessels?${params}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success) {
                        tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-8 text-center text-red-400">{{ __("Gagal memuat data.") }}</td></tr>';
                        return;
                    }
                    const vessels = res.data;
                    const meta = res.meta;
                    document.getElementById('obs-table-total').textContent = meta.total ?? 0;

                    if (!vessels.length) {
                        tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-12 text-center text-slate-400"><div class="flex flex-col items-center gap-2"><span class="text-3xl">🔭</span><span>{{ __("Tidak ada kapal ditemukan.") }}</span></div></td></tr>';
                        renderPagination(meta);
                        return;
                    }

                    tbody.innerHTML = vessels.map((v, i) => {
                        const idx = ((meta.current_page - 1) * meta.per_page) + i + 1;
                        return `<tr class="hover:bg-slate-50/80 cursor-pointer transition" data-vessel-id="${v.gfw_vessel_id}">
                            <td class="px-4 py-3 text-slate-400">${idx}</td>
                            <td class="px-4 py-3 font-semibold text-slate-800">${escHtml(v.name || v.ship_name || '—')}</td>
                            <td class="px-4 py-3 font-mono text-[10px] text-slate-500">${escHtml(v.gfw_vessel_id?.substring(0, 12) || '—')}…</td>
                            <td class="px-4 py-3 text-slate-600">${escHtml(v.mmsi || '—')}</td>
                            <td class="px-4 py-3 text-slate-600">${escHtml(v.imo || '—')}</td>
                            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 text-[10px] font-semibold capitalize">${escHtml(v.vessel_type || '—')}</span></td>
                            <td class="px-4 py-3 uppercase font-semibold text-slate-600">${escHtml(v.flag || '—')}</td>
                            <td class="px-4 py-3 text-slate-600">${v.length_m ? v.length_m + ' m' : '—'}</td>
                            <td class="px-4 py-3 font-mono text-[10px] text-slate-500">${v.last_synced_at ? new Date(v.last_synced_at).toLocaleDateString('id-ID') : '—'}</td>
                            <td class="px-4 py-3 text-center">
                                <button type="button" class="btn-vessel-detail px-2 py-1 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-[10px] font-semibold transition" data-vessel-id="${v.gfw_vessel_id}">Detail</button>
                            </td>
                        </tr>`;
                    }).join('');

                    renderPagination(meta);
                    attachRowListeners();
                })
                .catch(() => {
                    tbody.innerHTML = '<tr><td colspan="10" class="px-4 py-8 text-center text-red-400">{{ __("Error koneksi.") }}</td></tr>';
                });
        }

        function renderPagination(meta) {
            const info = document.getElementById('obs-pagination-info');
            const buttons = document.getElementById('obs-pagination-buttons');
            info.textContent = `Halaman ${meta.current_page} dari ${meta.last_page}`;
            buttons.innerHTML = '';

            if (meta.current_page > 1) {
                const prev = document.createElement('button');
                prev.className = 'px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 text-xs transition';
                prev.textContent = '← Prev';
                prev.addEventListener('click', () => loadVessels(meta.current_page - 1));
                buttons.appendChild(prev);
            }
            if (meta.current_page < meta.last_page) {
                const next = document.createElement('button');
                next.className = 'px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-600 text-xs transition';
                next.textContent = 'Next →';
                next.addEventListener('click', () => loadVessels(meta.current_page + 1));
                buttons.appendChild(next);
            }
        }

        function attachRowListeners() {
            document.querySelectorAll('#obs-vessel-tbody tr[data-vessel-id]').forEach(row => {
                row.addEventListener('click', function(e) {
                    if (e.target.closest('.btn-vessel-detail')) return;
                    openDrawer(this.dataset.vesselId);
                });
            });
            document.querySelectorAll('.btn-vessel-detail').forEach(btn => {
                btn.addEventListener('click', function() { openDrawer(this.dataset.vesselId); });
            });
        }

        // === VESSEL DETAIL DRAWER ===
        function openDrawer(gfwVesselId) {
            const drawer = document.getElementById('obs-vessel-drawer');
            const panel = document.getElementById('obs-drawer-panel');
            const content = document.getElementById('obs-drawer-content');
            const title = document.getElementById('obs-drawer-title');

            drawer.classList.remove('hidden');
            setTimeout(() => panel.classList.remove('translate-x-full'), 10);
            content.innerHTML = '<div class="text-center py-8 text-slate-400 text-sm animate-pulse">{{ __("Memuat detail kapal...") }}</div>';

            fetch(`${API_BASE}/vessels/${encodeURIComponent(gfwVesselId)}`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success || !res.data) {
                        content.innerHTML = '<div class="text-center py-8 text-red-400 text-sm">{{ __("Gagal memuat detail.") }}</div>';
                        return;
                    }
                    const d = res.data;
                    const v = d.vessel;
                    title.textContent = v.name || v.ship_name || 'Detail Kapal';

                    content.innerHTML = `
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                ${detailRow('Nama', v.name || v.ship_name)}
                                ${detailRow('GFW Vessel ID', v.gfw_vessel_id, true)}
                                ${detailRow('MMSI', v.mmsi)}
                                ${detailRow('IMO', v.imo)}
                                ${detailRow('Tipe', v.vessel_type)}
                                ${detailRow('Kelas', v.vessel_class)}
                                ${detailRow('Bendera', v.flag)}
                                ${detailRow('Gear Type', v.gear_type)}
                                ${detailRow('Panjang', v.length_m ? v.length_m + ' m' : null)}
                                ${detailRow('Tonase', v.tonnage_gt ? v.tonnage_gt + ' GT' : (v.gross_tonnage ? v.gross_tonnage + ' GT' : null))}
                                ${detailRow('Daya Mesin', v.engine_power_kw ? v.engine_power_kw + ' kW' : null)}
                                ${detailRow('Sumber', v.source)}
                            </div>
                            <div class="pt-3 border-t border-slate-200">
                                <div class="text-xs font-semibold text-slate-600 mb-2">📍 Presence (${d.presence_count ?? 0} titik)</div>
                                ${d.latest_presence ? `
                                    <div class="bg-emerald-50 rounded-lg p-3 text-xs space-y-1">
                                        <div class="flex justify-between"><span class="text-slate-500">Terakhir</span><span class="font-mono text-slate-700">${new Date(d.latest_presence.observed_at).toLocaleString('id-ID')}</span></div>
                                        <div class="flex justify-between"><span class="text-slate-500">Posisi</span><span class="font-mono text-slate-700">${d.latest_presence.latitude?.toFixed(4)}°, ${d.latest_presence.longitude?.toFixed(4)}°</span></div>
                                        <div class="flex justify-between"><span class="text-slate-500">Kecepatan</span><span class="font-mono text-slate-700">${d.latest_presence.speed ?? '—'} kn</span></div>
                                    </div>
                                ` : '<div class="text-xs text-slate-400">{{ __("Tidak ada data presence.") }}</div>'}
                            </div>
                            <button type="button" class="w-full mt-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition" id="btn-show-presence-map" data-vessel-id="${v.gfw_vessel_id}">
                                🗺️ {{ __('Tampilkan Presence di Peta') }}
                            </button>
                        </div>
                    `;

                    document.getElementById('btn-show-presence-map')?.addEventListener('click', function() {
                        loadPresenceOnMap(this.dataset.vesselId);
                        closeDrawer();
                    });
                })
                .catch(() => {
                    content.innerHTML = '<div class="text-center py-8 text-red-400 text-sm">{{ __("Error koneksi.") }}</div>';
                });
        }

        function closeDrawer() {
            const panel = document.getElementById('obs-drawer-panel');
            const drawer = document.getElementById('obs-vessel-drawer');
            panel.classList.add('translate-x-full');
            setTimeout(() => drawer.classList.add('hidden'), 300);
        }

        function detailRow(label, value, mono = false) {
            return `<div><div class="text-[10px] text-slate-400 uppercase font-semibold">${label}</div><div class="text-xs font-semibold text-slate-800 ${mono ? 'font-mono text-[10px] break-all' : ''} mt-0.5">${escHtml(value ?? '—')}</div></div>`;
        }

        function escHtml(str) {
            const div = document.createElement('div');
            div.textContent = String(str);
            return div.innerHTML;
        }

        // === FILTER DROPDOWNS ===
        function loadFilterOptions() {
            fetch(`${API_BASE}/stats`)
                .then(r => r.json())
                .then(res => {
                    if (!res.success) return;
                    const stats = res.data;
                    const typeSelect = document.getElementById('obs-type-filter');
                    const flagSelect = document.getElementById('obs-flag-filter');

                    Object.keys(stats.vessels_by_type || {}).forEach(t => {
                        const opt = document.createElement('option');
                        opt.value = t;
                        opt.textContent = t.charAt(0).toUpperCase() + t.slice(1) + ` (${stats.vessels_by_type[t]})`;
                        typeSelect.appendChild(opt);
                    });

                    Object.keys(stats.vessels_by_flag || {}).forEach(f => {
                        const opt = document.createElement('option');
                        opt.value = f;
                        opt.textContent = f.toUpperCase() + ` (${stats.vessels_by_flag[f]})`;
                        flagSelect.appendChild(opt);
                    });
                })
                .catch(() => {});
        }

        // === EVENT LISTENERS ===
        document.getElementById('btn-obs-search').addEventListener('click', () => {
            currentSearch = document.getElementById('obs-search-input').value.trim();
            currentType = document.getElementById('obs-type-filter').value;
            currentFlag = document.getElementById('obs-flag-filter').value;
            loadVessels(1);
        });

        document.getElementById('obs-search-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') document.getElementById('btn-obs-search').click();
        });

        document.getElementById('btn-clear-obs-search').addEventListener('click', () => {
            document.getElementById('obs-search-input').value = '';
            currentSearch = '';
            loadVessels(1);
        });

        document.getElementById('obs-search-input').addEventListener('input', function() {
            document.getElementById('btn-clear-obs-search').classList.toggle('hidden', !this.value);
        });

        document.getElementById('btn-close-obs-drawer').addEventListener('click', closeDrawer);
        document.getElementById('obs-drawer-overlay').addEventListener('click', closeDrawer);

        document.getElementById('btn-refresh-map').addEventListener('click', () => {
            clearPresenceMarkers();
        });

        // === INIT ===
        initMap();
        loadVessels(1);
        loadFilterOptions();
    });
    </script>
</x-app-layout>
