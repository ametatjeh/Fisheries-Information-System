<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span>🗺️</span>
                <span>{{ __('Sistem Informasi Geografis (GIS) & Analisis Spasial Perikanan') }}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('dashboard') }}" class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>📊</span>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('gfw.monitoring') }}" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs transition shadow-xs flex items-center gap-1.5">
                    <span>🛰️</span>
                    <span>{{ __('GFW Monitoring') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Leaflet CSS & JS via CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <div class="space-y-6">
        {{-- Banner Header --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🗺️</div>
            <div class="relative z-10">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex-1 max-w-4xl">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                            <span>🌐</span>
                            <span>{{ __('Pemetaan Spasial Sumber Daya, Fishing Ground & Aktivitas Penangkapan') }}</span>
                        </div>
                        <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                            <span>🗺️</span>
                            <span>{{ __('Peta Geospasial Perikanan Tangkap') }}</span>
                        </h2>
                        <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                            {{ __('Visualisasi spasial sebaran pelabuhan & TPI, daerah penangkapan ikan (fishing ground master), koordinat aktual setting alat tangkap (fishing effort), pangkalan kapal (homeport), serta analisis berbasis WPP-NRI 571 & 572.') }}
                        </p>
                    </div>

                    {{-- Quick Layer Badges / Statistics --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 shrink-0">
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-ocean-200 uppercase tracking-wider font-semibold">Pelabuhan/TPI</div>
                            <div class="text-lg font-bold font-mono text-white mt-0.5">{{ number_format($stats['total_ports']) }}</div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-emerald-200 uppercase tracking-wider font-semibold">Titik Setting</div>
                            <div class="text-lg font-bold font-mono text-emerald-300 mt-0.5">{{ number_format($stats['total_effort_points']) }}</div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-cyan-200 uppercase tracking-wider font-semibold">Fishing Ground</div>
                            <div class="text-lg font-bold font-mono text-cyan-300 mt-0.5">{{ number_format($stats['total_fishing_grounds']) }}</div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-indigo-200 uppercase tracking-wider font-semibold">Kapal Homeport</div>
                            <div class="text-lg font-bold font-mono text-indigo-300 mt-0.5">{{ number_format($stats['total_vessels']) }}</div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-amber-200 uppercase tracking-wider font-semibold">Posisi Logbook</div>
                            <div class="text-lg font-bold font-mono text-amber-300 mt-0.5">{{ number_format($stats['total_logbook_points']) }}</div>
                        </div>
                        <div class="px-3 py-2 rounded-xl bg-white/10 border border-white/15 backdrop-blur-sm">
                            <div class="text-[10px] text-purple-200 uppercase tracking-wider font-semibold">WPP Terpantau</div>
                            <div class="text-lg font-bold font-mono text-purple-300 mt-0.5">WPP 571/572</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Interactive Multi-Criteria Filters --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <form method="GET" action="{{ route('gis.index') }}" class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-sm font-bold text-slate-800">
                        <svg class="w-4 h-4 text-ocean-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <span>{{ __('Filter Spasial & Analisis Koordinat') }}</span>
                    </div>

                    @if(array_filter($filters))
                        <a href="{{ route('gis.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline">
                            {{ __('Reset Seluruh Filter') }}
                        </a>
                    @endif
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    {{-- Tahun --}}
                    <div>
                        <label for="gis_filter_year" class="sr-only">{{ __('Pilih Tahun') }}</label>
                        <select id="gis_filter_year" name="year" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Tahun') }}</option>
                            @foreach($yearsList as $yr)
                                <option value="{{ $yr }}" {{ ($filters['year'] ?? '') == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Bulan --}}
                    <div>
                        <label for="gis_filter_month" class="sr-only">{{ __('Pilih Bulan') }}</label>
                        <select id="gis_filter_month" name="month" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Bulan') }}</option>
                            @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $mNum => $mName)
                                <option value="{{ $mNum }}" {{ ($filters['month'] ?? '') == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- WPP-NRI --}}
                    <div>
                        <label for="gis_filter_wppnri" class="sr-only">{{ __('Pilih Wilayah WPP') }}</label>
                        <select id="gis_filter_wppnri" name="wppnri_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Wilayah WPP') }}</option>
                            @foreach($wppList as $w)
                                <option value="{{ $w->id }}" {{ ($filters['wppnri_id'] ?? '') == $w->id ? 'selected' : '' }}>
                                    {{ $w->code }} - {{ $w->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Alat Tangkap --}}
                    <div>
                        <label for="gis_filter_gear" class="sr-only">{{ __('Pilih Alat Tangkap') }}</label>
                        <select id="gis_filter_gear" name="gear_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Alat Tangkap') }}</option>
                            @foreach($gearsList as $gear)
                                <option value="{{ $gear->id }}" {{ ($filters['gear_id'] ?? '') == $gear->id ? 'selected' : '' }}>
                                    {{ $gear->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Ikan / Spesies --}}
                    <div>
                        <label for="gis_filter_species" class="sr-only">{{ __('Pilih Spesies Ikan') }}</label>
                        <select id="gis_filter_species" name="species_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Spesies Ikan') }}</option>
                            @foreach($speciesList as $sp)
                                <option value="{{ $sp->id }}" {{ ($filters['species_id'] ?? '') == $sp->id ? 'selected' : '' }}>
                                    {{ $sp->local_name_id ?: $sp->scientific_name }} ({{ $sp->fao_code ?: '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pelabuhan / Landing Site --}}
                    <div>
                        <label for="gis_filter_landing_site" class="sr-only">{{ __('Pilih Pelabuhan / TPI') }}</label>
                        <select id="gis_filter_landing_site" name="landing_site_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Pelabuhan / TPI') }}</option>
                            @foreach($landingSites as $site)
                                <option value="{{ $site->id }}" {{ ($filters['landing_site_id'] ?? '') == $site->id ? 'selected' : '' }}>
                                    {{ $site->name }} ({{ $site->site_type ?? 'TPI' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Kabupaten / Kota --}}
                    <div>
                        <label for="gis_filter_regency" class="sr-only">{{ __('Pilih Kabupaten / Kota') }}</label>
                        <select id="gis_filter_regency" name="regency_id" class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                            <option value="">{{ __('Pilih Kabupaten / Kota') }}</option>
                            @foreach($regenciesList as $reg)
                                <option value="{{ $reg->id }}" {{ ($filters['regency_id'] ?? '') == $reg->id ? 'selected' : '' }}>
                                    {{ $reg->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Dari Tanggal --}}
                    <div>
                        <label for="gis_start_date" class="sr-only">{{ __('Dari Tanggal') }}</label>
                        <input type="date" id="gis_start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}"
                               title="{{ __('Dari Tanggal') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                    </div>

                    {{-- Sampai Tanggal --}}
                    <div>
                        <label for="gis_end_date" class="sr-only">{{ __('Sampai Tanggal') }}</label>
                        <input type="date" id="gis_end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}"
                               title="{{ __('Sampai Tanggal') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex items-end">
                        <button type="submit"
                                class="w-full py-2.5 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white text-xs font-semibold transition shadow-xs flex items-center justify-center gap-1.5">
                            <span>🔍</span>
                            <span>{{ __('Terapkan Filter') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Fast Layer Toggles --}}
                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-4 text-xs font-medium text-slate-700">
                        <span class="font-bold text-slate-900 uppercase text-[11px] tracking-wider">{{ __('Layer Spasial:') }}</span>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="toggle-ports" checked class="rounded text-sky-600 focus:ring-sky-500 w-4 h-4">
                            <span>⚓ Pelabuhan/TPI ({{ $geoData['counts']['ports'] }})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="toggle-efforts" checked class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                            <span>🎣 Titik Setting Aktual ({{ $geoData['counts']['efforts'] }})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="toggle-fishing-grounds" checked class="rounded text-cyan-600 focus:ring-cyan-500 w-4 h-4">
                            <span>🌐 Fishing Ground Master ({{ $geoData['counts']['fishing_grounds'] }})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="toggle-vessels" checked class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                            <span>🚢 Pangkalan Kapal ({{ $geoData['counts']['vessels'] }})</span>
                        </label>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" id="toggle-logbooks" checked class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                            <span>🧭 Logbook Kapal ({{ $geoData['counts']['logbooks'] }})</span>
                        </label>
                    </div>

                    <div class="text-[11px] text-slate-400">
                        <span>Koordinat NULL / 0 tidak dirender demi integritas data spasial</span>
                    </div>
                </div>
            </form>
        </div>

        {{-- Map Layout Grid: Map Canvas (left/main) + Spatial Inspector & Directory (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Main Map Canvas (Span 3 cols) --}}
            <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden relative">
                {{-- Map Toolbar Overlay --}}
                <div class="p-3 bg-slate-50 border-b border-slate-200/80 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-slate-700">{{ __('Tipe Peta:') }}</span>
                        <div class="inline-flex rounded-lg border border-slate-300 p-0.5 bg-white">
                            <button type="button" id="btn-osm" class="px-2.5 py-1 rounded text-xs font-semibold bg-ocean-600 text-white shadow-sm">
                                Standar (OSM)
                            </button>
                            <button type="button" id="btn-ocean" class="px-2.5 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900">
                                Laut (Esri Ocean)
                            </button>
                            <button type="button" id="btn-satellite" class="px-2.5 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900">
                                Satelit (Esri)
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" id="btn-reset-view"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 font-medium transition text-xs shadow-xs">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                            </svg>
                            <span>{{ __('Reset Posisi Peta') }}</span>
                        </button>
                    </div>
                </div>

                {{-- The Map DIV --}}
                <div id="fisheries-map" style="height: 620px; width: 100%; z-index: 10;"></div>

                {{-- Map Legend Overlay --}}
                <div class="absolute bottom-4 left-4 z-20 bg-white/95 backdrop-blur-sm p-3 rounded-xl border border-slate-200 shadow-md text-[11px] space-y-1.5 pointer-events-auto max-w-xs">
                    <div class="font-bold text-slate-800 uppercase tracking-wider text-[10px] pb-1 border-b border-slate-100">
                        {{ __('Legenda Titik Spasial') }}
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-sky-600 flex items-center justify-center text-[9px] text-white font-bold">⚓</span>
                        <span class="text-slate-700 font-medium">Pelabuhan / Pangkalan TPI</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-emerald-600 flex items-center justify-center text-[9px] text-white font-bold">🎣</span>
                        <span class="text-slate-700 font-medium">Titik Setting Aktual (Effort)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-cyan-600 flex items-center justify-center text-[9px] text-white font-bold">🌐</span>
                        <span class="text-slate-700 font-medium">Fishing Ground Master</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-indigo-600 flex items-center justify-center text-[9px] text-white font-bold">🚢</span>
                        <span class="text-slate-700 font-medium">Armada Kapal (Homeport Terdaftar)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-full bg-amber-500 flex items-center justify-center text-[9px] text-white font-bold">🧭</span>
                        <span class="text-slate-700 font-medium">Posisi Logbook Pelayaran</span>
                    </div>
                </div>
            </div>

            {{-- Spatial Inspector & Object Explorer (Span 1 col) --}}
            <div class="space-y-4" x-data="{ activeDirectoryTab: 'ports' }">
                {{-- Quick Selected Point Detail --}}
                <div id="inspector-card" class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center justify-between">
                        <span>{{ __('Inspektur Objek') }}</span>
                        <span id="inspector-badge" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600">Pilih Objek</span>
                    </div>
                    <div id="inspector-content">
                        <div class="text-center py-6 text-slate-400">
                            <div class="text-3xl mb-2">🎯</div>
                            <p class="text-xs leading-relaxed">
                                {{ __('Klik salah satu marker di peta (Pelabuhan, Setting, Fishing Ground, Kapal, atau Logbook) untuk menginspeksi koordinat & data spesifik.') }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Fast Directory Explorer with Tabs --}}
                <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80">
                    <div class="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider">{{ __('Direktori Lokasi') }}</div>
                        <div class="flex gap-1">
                            <button type="button" @click="activeDirectoryTab = 'ports'"
                                    :class="activeDirectoryTab === 'ports' ? 'bg-ocean-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold transition" title="Pelabuhan / TPI">⚓ TPI</button>
                            <button type="button" @click="activeDirectoryTab = 'efforts'"
                                    :class="activeDirectoryTab === 'efforts' ? 'bg-emerald-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold transition" title="Titik Setting Aktual">🎣 Setting</button>
                            <button type="button" @click="activeDirectoryTab = 'vessels'"
                                    :class="activeDirectoryTab === 'vessels' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold transition" title="Kapal Homeport">🚢 Kapal</button>
                        </div>
                    </div>

                    {{-- Tab: Pelabuhan / TPI --}}
                    <div x-show="activeDirectoryTab === 'ports'" class="space-y-2 max-h-[300px] overflow-y-auto">
                        @forelse($geoData['ports'] as $p)
                            <div class="p-2.5 rounded-xl border border-slate-100 hover:border-ocean-300 hover:bg-ocean-50/50 cursor-pointer transition flex items-center justify-between port-quick-item"
                                 data-lat="{{ $p['lat'] }}" data-lng="{{ $p['lng'] }}" data-name="{{ $p['name'] }}">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800">{{ $p['name'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $p['regency'] }} • <span class="font-mono text-ocean-700 font-semibold">{{ $p['type'] }}</span></div>
                                </div>
                                <span class="text-sm">⚓</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-3">Tidak ada pelabuhan sesuai filter</div>
                        @endforelse
                    </div>

                    {{-- Tab: Titik Setting Aktual --}}
                    <div x-show="activeDirectoryTab === 'efforts'" style="display: none;" class="space-y-2 max-h-[300px] overflow-y-auto">
                        @forelse($geoData['efforts'] as $eff)
                            <div class="p-2.5 rounded-xl border border-slate-100 hover:border-emerald-300 hover:bg-emerald-50/50 cursor-pointer transition flex items-center justify-between effort-quick-item"
                                 data-lat="{{ $eff['lat_setting'] }}" data-lng="{{ $eff['lng_setting'] }}" data-trip="{{ $eff['trip_code'] }}">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800">{{ $eff['vessel'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $eff['gear'] }} • <span class="font-bold text-emerald-700">{{ number_format($eff['catch_kg']) }} kg</span></div>
                                </div>
                                <span class="text-sm">🎣</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-3">Tidak ada titik setting terdata</div>
                        @endforelse
                    </div>

                    {{-- Tab: Kapal Homeport --}}
                    <div x-show="activeDirectoryTab === 'vessels'" style="display: none;" class="space-y-2 max-h-[300px] overflow-y-auto">
                        @forelse($geoData['vessels'] as $v)
                            <div class="p-2.5 rounded-xl border border-slate-100 hover:border-indigo-300 hover:bg-indigo-50/50 cursor-pointer transition flex items-center justify-between vessel-quick-item"
                                 data-lat="{{ $v['lat'] }}" data-lng="{{ $v['lng'] }}" data-name="{{ $v['name'] }}">
                                <div>
                                    <div class="text-xs font-semibold text-slate-800">{{ $v['name'] }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $v['homeport'] }} • <span class="font-mono text-indigo-700 font-semibold">{{ $v['gt'] }} GT</span></div>
                                </div>
                                <span class="text-sm">🚢</span>
                            </div>
                        @empty
                            <div class="text-xs text-slate-400 text-center py-3">Tidak ada kapal terdata</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Section: WPP-NRI Spatial Analysis (Stage 12.6) --}}
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200/80">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100 mb-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-cyan-100 text-cyan-800 text-[10px] font-bold uppercase mb-1">
                        <span>📊</span>
                        <span>{{ __('Analisis Spasial Multi-Dimensi WPP-NRI') }}</span>
                    </div>
                    <h3 class="text-sm font-bold text-slate-900">{{ __('Rekapitulasi Produksi & Upaya per Wilayah Pengelolaan Perikanan') }}</h3>
                    <p class="text-xs text-slate-500 mt-0.5">
                        {{ __('Agregasi terpisah tanpa duplikasi (Catch × Trip × Effort) serta penanganan transparan data WPP belum diisi.') }}
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($geoData['wpp_analysis'] as $wpp)
                    <div class="p-4 rounded-2xl border {{ $wpp['code'] === '-' ? 'border-amber-200 bg-amber-50/30' : 'border-slate-200 bg-slate-50/50' }} hover:shadow-xs transition">
                        <div class="flex items-center justify-between pb-2 border-b {{ $wpp['code'] === '-' ? 'border-amber-100' : 'border-slate-200' }} mb-3">
                            <div>
                                <span class="text-xs font-bold text-slate-900">{{ $wpp['name'] }}</span>
                                <span class="block text-[10px] font-mono text-slate-400">Kode: {{ $wpp['code'] }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold {{ $wpp['code'] === '-' ? 'bg-amber-100 text-amber-800' : 'bg-cyan-100 text-cyan-800' }}">
                                {{ $wpp['code'] === '-' ? 'Belum Terpetakan' : 'Resmi WPP' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                            <div class="bg-white p-2.5 rounded-xl border border-slate-100">
                                <div class="text-[10px] text-slate-400">Total Tangkapan:</div>
                                <div class="font-mono font-bold text-slate-800 text-sm mt-0.5">{{ number_format($wpp['catch_kg'], 1) }} kg</div>
                                <div class="text-[10px] text-slate-400 font-mono">({{ $wpp['catch_ton'] }} Ton)</div>
                            </div>
                            <div class="bg-white p-2.5 rounded-xl border border-slate-100">
                                <div class="text-[10px] text-slate-400">Total Pelayaran:</div>
                                <div class="font-mono font-bold text-slate-800 text-sm mt-0.5">{{ number_format($wpp['trips']) }} Trip</div>
                                <div class="text-[10px] text-ocean-600 font-semibold mt-0.5">CPUE: {{ number_format($wpp['cpue'], 1) }} kg/trip</div>
                            </div>
                            <div class="bg-white p-2.5 rounded-xl border border-slate-100">
                                <div class="text-[10px] text-slate-400">Titik Koordinat Setting:</div>
                                <div class="font-mono font-bold text-emerald-700 text-sm mt-0.5">{{ number_format($wpp['efforts']) }} Titik</div>
                            </div>
                            <div class="bg-white p-2.5 rounded-xl border border-slate-100">
                                <div class="text-[10px] text-slate-400">Jam Operasi (Effort):</div>
                                <div class="font-mono font-bold text-slate-800 text-sm mt-0.5">{{ number_format($wpp['duration_hours'], 1) }} Jam</div>
                            </div>
                        </div>

                        @if(!empty($wpp['top_species']))
                            <div class="pt-2 border-t {{ $wpp['code'] === '-' ? 'border-amber-100' : 'border-slate-200' }}">
                                <div class="text-[10px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">{{ __('Spesies Unggulan WPP:') }}</div>
                                <div class="space-y-1">
                                    @foreach($wpp['top_species'] as $sp)
                                        <div class="flex items-center justify-between text-[11px] bg-white px-2 py-1 rounded-lg border border-slate-100">
                                            <span class="font-medium text-slate-700 truncate max-w-[140px]">{{ $sp['name'] }}</span>
                                            <span class="font-mono font-bold text-slate-900">{{ number_format($sp['weight_kg']) }} kg</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-3 text-center py-6 text-slate-400 text-xs">
                        Tidak ada data analitik WPP yang memenuhi kriteria filter.
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- Map Initialization Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const geoData = @json($geoData);
            const centerLat = geoData.center.lat || 5.55;
            const centerLng = geoData.center.lng || 95.32;

            // 1. Initialize Map
            const map = L.map('fisheries-map', {
                center: [centerLat, centerLng],
                zoom: 8,
                zoomControl: true,
            });

            // 2. Base Tile Layers
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            const oceanLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Ocean/World_Ocean_Base/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 13,
                attribution: 'Tiles &copy; Esri &mdash; Sources: GEBCO, NOAA, CHS, OSU, UNH, CSUMB, National Geographic, DeLorme, NAVTEQ, and Esri'
            });

            const satLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                maxZoom: 18,
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });

            let currentTile = osmLayer;

            // Basemap Switchers
            document.getElementById('btn-osm').addEventListener('click', function() {
                map.removeLayer(currentTile);
                osmLayer.addTo(map);
                currentTile = osmLayer;
                updateButtonStyles(this);
            });
            document.getElementById('btn-ocean').addEventListener('click', function() {
                map.removeLayer(currentTile);
                oceanLayer.addTo(map);
                currentTile = oceanLayer;
                updateButtonStyles(this);
            });
            document.getElementById('btn-satellite').addEventListener('click', function() {
                map.removeLayer(currentTile);
                satLayer.addTo(map);
                currentTile = satLayer;
                updateButtonStyles(this);
            });

            function updateButtonStyles(activeBtn) {
                ['btn-osm', 'btn-ocean', 'btn-satellite'].forEach(id => {
                    const btn = document.getElementById(id);
                    btn.className = 'px-2.5 py-1 rounded text-xs font-medium text-slate-600 hover:text-slate-900';
                });
                activeBtn.className = 'px-2.5 py-1 rounded text-xs font-semibold bg-ocean-600 text-white shadow-sm';
            }

            document.getElementById('btn-reset-view').addEventListener('click', () => {
                map.setView([centerLat, centerLng], 8, { animate: true });
            });

            // 3. Custom Marker Icons
            function createIcon(emoji, bgClass) {
                return L.divIcon({
                    html: `<div style="display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:50%;background:${bgClass};color:white;font-size:14px;box-shadow:0 2px 6px rgba(0,0,0,0.3);border:2px solid white;">${emoji}</div>`,
                    className: '',
                    iconSize: [28, 28],
                    iconAnchor: [14, 14],
                    popupAnchor: [0, -14],
                });
            }

            const portIcon = createIcon('⚓', '#0284c7');
            const effortIcon = createIcon('🎣', '#059669');
            const fgIcon = createIcon('🌐', '#0891b2');
            const vesselIcon = createIcon('🚢', '#4f46e5');
            const logbookIcon = createIcon('🧭', '#d97706');

            // 4. Feature Groups for Layers
            const portsGroup = L.featureGroup();
            const effortsGroup = L.featureGroup();
            const groundsGroup = L.featureGroup();
            const vesselsGroup = L.featureGroup();
            const logbooksGroup = L.featureGroup();

            const inspectorContent = document.getElementById('inspector-content');
            const inspectorBadge = document.getElementById('inspector-badge');

            // Layer 1: Ports / Landing Sites
            geoData.ports.forEach(port => {
                const marker = L.marker([port.lat, port.lng], { icon: portIcon });
                const popupHtml = `
                    <div style="font-family:sans-serif;font-size:12px;min-width:200px;">
                        <div style="font-weight:700;color:#0284c7;font-size:13px;">⚓ ${port.name}</div>
                        <div style="color:#64748b;margin:2px 0 6px 0;">${port.type} &bull; ${port.regency}, ${port.province}</div>
                        <div style="font-family:monospace;font-size:11px;background:#f8fafc;padding:4px 6px;border-radius:4px;border:1px solid #e2e8f0;margin-bottom:6px;">
                            ${port.lat.toFixed(5)}°, ${port.lng.toFixed(5)}°
                        </div>
                        <div style="font-size:11px;color:#334155;">
                            <div>Kapal Berpangkalan: <strong>${port.vessels_count} unit</strong></div>
                            <div>Pendaratan Dicatat: <strong>${port.landings_count} kali</strong></div>
                        </div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    inspectorBadge.textContent = 'Pelabuhan / TPI';
                    inspectorBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-100 text-sky-800';
                    inspectorContent.innerHTML = `
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-sm text-slate-800">${port.name}</div>
                            <div class="text-slate-500">${port.address || '-'}</div>
                            <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-2 text-[11px]">
                                <div><span class="text-slate-400">Kode:</span> <span class="font-mono font-semibold">${port.code}</span></div>
                                <div><span class="text-slate-400">Tipe:</span> <span class="font-semibold text-ocean-600">${port.type}</span></div>
                                <div><span class="text-slate-400">Kabupaten:</span> <span>${port.regency}</span></div>
                                <div><span class="text-slate-400">Provinsi:</span> <span>${port.province}</span></div>
                                <div><span class="text-slate-400">Kapal Asal:</span> <span class="font-bold">${port.vessels_count}</span></div>
                                <div><span class="text-slate-400">Total Pendaratan:</span> <span class="font-bold">${port.landings_count}</span></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400">Koordinat GPS Pangkalan:</div>
                                <div class="font-mono text-ocean-700 font-bold">${port.lat.toFixed(6)}°, ${port.lng.toFixed(6)}°</div>
                            </div>
                        </div>
                    `;
                });
                portsGroup.addLayer(marker);
            });

            // Layer 2: Actual Fishing Efforts
            geoData.efforts.forEach(eff => {
                const marker = L.marker([eff.lat_setting, eff.lng_setting], { icon: effortIcon });
                const speciesSummary = eff.species_list && eff.species_list.length > 0
                    ? eff.species_list.map(s => `${s.name} (${Number(s.weight_kg).toFixed(0)}kg)`).slice(0, 3).join(', ')
                    : 'Tidak ada rincian tangkapan';

                const popupHtml = `
                    <div style="font-family:sans-serif;font-size:12px;min-width:210px;">
                        <div style="font-weight:700;color:#059669;font-size:13px;">🎣 Setting #${eff.setting_num} (Trip ${eff.trip_code})</div>
                        <div style="color:#0f172a;font-weight:600;margin-top:2px;">${eff.vessel}</div>
                        <div style="color:#64748b;font-size:11px;">${eff.gear} &bull; ${eff.port}</div>
                        <div style="margin:4px 0;font-size:11px;color:#059669;font-weight:700;">
                            Hasil Tangkap Setting: ${Number(eff.catch_kg).toLocaleString('id-ID')} kg
                        </div>
                        <div style="font-size:10px;color:#64748b;border-top:1px dashed #e2e8f0;padding-top:4px;">
                            WPP: ${eff.wpp_name} | Durasi: ${eff.duration_hours} Jam
                        </div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    inspectorBadge.textContent = 'Actual Fishing Effort';
                    inspectorBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800';
                    
                    let speciesHtml = '';
                    if (eff.species_list && eff.species_list.length > 0) {
                        speciesHtml = '<div class="space-y-1 mt-1">' + eff.species_list.map(s => `
                            <div class="flex justify-between text-[11px] bg-slate-50 px-1.5 py-0.5 rounded">
                                <span>${s.name}</span>
                                <span class="font-mono font-bold text-emerald-700">${Number(s.weight_kg).toFixed(1)} kg</span>
                            </div>
                        `).join('') + '</div>';
                    } else {
                        speciesHtml = '<div class="text-[11px] text-slate-400 italic">Nihil / Belum ditimbang</div>';
                    }

                    inspectorContent.innerHTML = `
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-sm text-slate-800">${eff.vessel}</div>
                            <div class="text-slate-500">${eff.captain} &bull; Trip: <span class="font-mono font-semibold">${eff.trip_code}</span></div>
                            <div class="pt-2 border-t border-slate-100 space-y-1 text-[11px]">
                                <div><span class="text-slate-400">Alat Tangkap:</span> <span class="font-semibold text-emerald-700">${eff.gear}</span></div>
                                <div><span class="text-slate-400">WPP-NRI:</span> <span class="font-semibold text-ocean-700">${eff.wpp_name}</span></div>
                                <div><span class="text-slate-400">Waktu Setting:</span> <span>${eff.setting_time}</span></div>
                                <div><span class="text-slate-400">Durasi Operasi:</span> <span class="font-bold font-mono">${eff.duration_hours} Jam</span></div>
                                <div><span class="text-slate-400">Pelabuhan Asal:</span> <span>${eff.port} (${eff.regency})</span></div>
                            </div>
                            <div class="pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400 font-semibold mb-1">Tangkapan Siklus Setting:</div>
                                <div class="text-sm font-bold font-mono text-emerald-600 mb-1">${Number(eff.catch_kg).toLocaleString('id-ID', {minimumFractionDigits: 1})} kg</div>
                                ${speciesHtml}
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400">Koordinat Setting GPS:</div>
                                <div class="font-mono text-emerald-700 font-bold">${eff.lat_setting.toFixed(6)}°, ${eff.lng_setting.toFixed(6)}°</div>
                            </div>
                        </div>
                    `;
                });
                effortsGroup.addLayer(marker);
            });

            // Layer 3: Fishing Grounds Reference
            geoData.fishing_grounds.forEach(fg => {
                const marker = L.marker([fg.lat, fg.lng], { icon: fgIcon });
                const popupHtml = `
                    <div style="font-family:sans-serif;font-size:12px;min-width:180px;">
                        <div style="font-weight:700;color:#0891b2;font-size:13px;">🌐 ${fg.name}</div>
                        <div style="color:#64748b;font-size:11px;margin:2px 0 4px 0;">${fg.wpp_code} - ${fg.wpp_name}</div>
                        <div style="font-size:11px;color:#334155;">Trip Penangkapan Terkait: <strong>${fg.trips_count}</strong></div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    inspectorBadge.textContent = 'Fishing Ground';
                    inspectorBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-100 text-cyan-800';
                    inspectorContent.innerHTML = `
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-sm text-slate-800">${fg.name}</div>
                            <div class="text-slate-500">${fg.description}</div>
                            <div class="pt-2 border-t border-slate-100 space-y-1 text-[11px]">
                                <div><span class="text-slate-400">Kode:</span> <span class="font-mono">${fg.code}</span></div>
                                <div><span class="text-slate-400">WPP:</span> <span class="font-semibold text-cyan-700">${fg.wpp_code}</span></div>
                                <div><span class="text-slate-400">Trip Terhubung:</span> <span class="font-bold font-mono">${fg.trips_count}</span></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400">Koordinat Referensi:</div>
                                <div class="font-mono text-cyan-700 font-bold">${fg.lat.toFixed(6)}°, ${fg.lng.toFixed(6)}°</div>
                            </div>
                        </div>
                    `;
                });
                groundsGroup.addLayer(marker);
            });

            // Layer 4: Vessel Homeports
            geoData.vessels.forEach(v => {
                const marker = L.marker([v.lat, v.lng], { icon: vesselIcon });
                const popupHtml = `
                    <div style="font-family:sans-serif;font-size:12px;min-width:180px;">
                        <div style="font-weight:700;color:#4f46e5;font-size:13px;">🚢 ${v.name}</div>
                        <div style="color:#64748b;font-size:11px;margin:2px 0 4px 0;">Reg: ${v.registration} &bull; ${v.gt} GT</div>
                        <div style="font-size:11px;color:#334155;">Pangkalan: <strong>${v.homeport}</strong></div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    inspectorBadge.textContent = 'Pangkalan Kapal';
                    inspectorBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-indigo-100 text-indigo-800';
                    inspectorContent.innerHTML = `
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-sm text-slate-800">${v.name}</div>
                            <div class="text-slate-500 font-mono text-[11px]">No. Reg: ${v.registration}</div>
                            <div class="pt-2 border-t border-slate-100 space-y-1 text-[11px]">
                                <div><span class="text-slate-400">Tipe Armada:</span> <span class="font-semibold">${v.type}</span></div>
                                <div><span class="text-slate-400">Tonase Kotor:</span> <span class="font-bold font-mono">${v.gt} GT</span></div>
                                <div><span class="text-slate-400">Alat Tangkap Utama:</span> <span>${v.gear}</span></div>
                                <div><span class="text-slate-400">Pangkalan Resmi:</span> <span class="font-bold text-indigo-700">${v.homeport}</span></div>
                                <div><span class="text-slate-400">Kabupaten:</span> <span>${v.regency}</span></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400">Koordinat Pangkalan:</div>
                                <div class="font-mono text-indigo-700 font-bold">${v.lat.toFixed(6)}°, ${v.lng.toFixed(6)}°</div>
                                <div class="text-[10px] text-slate-400 mt-0.5 italic">*Bukan posisi tracking live</div>
                            </div>
                        </div>
                    `;
                });
                vesselsGroup.addLayer(marker);
            });

            // Layer 5: Logbooks
            geoData.logbooks.forEach(log => {
                const marker = L.marker([log.lat, log.lng], { icon: logbookIcon });
                const popupHtml = `
                    <div style="font-family:sans-serif;font-size:12px;min-width:180px;">
                        <div style="font-weight:700;color:#d97706;font-size:13px;">🧭 Logbook Kapal</div>
                        <div style="color:#0f172a;font-weight:600;">${log.vessel}</div>
                        <div style="color:#64748b;font-size:11px;">${log.date} ${log.time}</div>
                        <div style="margin-top:4px;font-size:11px;color:#d97706;">Cuaca: ${log.weather} (Gelombang: ${log.wave_height}m)</div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                marker.on('click', () => {
                    inspectorBadge.textContent = 'Logbook Pelayaran';
                    inspectorBadge.className = 'px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-100 text-amber-800';
                    inspectorContent.innerHTML = `
                        <div class="space-y-2 text-xs">
                            <div class="font-bold text-sm text-slate-800">${log.vessel}</div>
                            <div class="text-slate-500">Trip: <span class="font-mono">${log.trip_code}</span> &bull; ${log.port}</div>
                            <div class="pt-2 border-t border-slate-100 space-y-1 text-[11px]">
                                <div><span class="text-slate-400">Tanggal/Waktu:</span> <span>${log.date} - ${log.time}</span></div>
                                <div><span class="text-slate-400">Aktivitas:</span> <span class="font-semibold">${log.activity}</span></div>
                                <div><span class="text-slate-400">Kondisi Cuaca:</span> <span>${log.weather}</span></div>
                                <div><span class="text-slate-400">Tinggi Gelombang:</span> <span class="font-mono font-bold">${log.wave_height} meter</span></div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-slate-100">
                                <div class="text-[10px] text-slate-400">Posisi GPS Logbook:</div>
                                <div class="font-mono text-amber-700 font-bold">${log.lat.toFixed(6)}°, ${log.lng.toFixed(6)}°</div>
                            </div>
                        </div>
                    `;
                });
                logbooksGroup.addLayer(marker);
            });

            // 5. Add layers to map
            portsGroup.addTo(map);
            effortsGroup.addTo(map);
            groundsGroup.addTo(map);
            vesselsGroup.addTo(map);
            logbooksGroup.addTo(map);

            // Layer Toggle Handlers
            document.getElementById('toggle-ports').addEventListener('change', (e) => {
                if (e.target.checked) map.addLayer(portsGroup);
                else map.removeLayer(portsGroup);
            });
            document.getElementById('toggle-efforts').addEventListener('change', (e) => {
                if (e.target.checked) map.addLayer(effortsGroup);
                else map.removeLayer(effortsGroup);
            });
            document.getElementById('toggle-fishing-grounds').addEventListener('change', (e) => {
                if (e.target.checked) map.addLayer(groundsGroup);
                else map.removeLayer(groundsGroup);
            });
            document.getElementById('toggle-vessels').addEventListener('change', (e) => {
                if (e.target.checked) map.addLayer(vesselsGroup);
                else map.removeLayer(vesselsGroup);
            });
            document.getElementById('toggle-logbooks').addEventListener('change', (e) => {
                if (e.target.checked) map.addLayer(logbooksGroup);
                else map.removeLayer(logbooksGroup);
            });

            // Fast Port / Effort / Vessel List Click to Fly
            document.querySelectorAll('.port-quick-item, .effort-quick-item, .vessel-quick-item').forEach(el => {
                el.addEventListener('click', () => {
                    const lat = parseFloat(el.dataset.lat);
                    const lng = parseFloat(el.dataset.lng);
                    if (lat && lng) {
                        map.flyTo([lat, lng], 13, { duration: 1.2 });
                    }
                });
            });

            // Fit Bounds if layers exist
            const allFeatures = L.featureGroup([portsGroup, effortsGroup, groundsGroup, vesselsGroup, logbooksGroup]);
            if (allFeatures.getLayers().length > 0) {
                map.fitBounds(allFeatures.getBounds(), { padding: [40, 40], maxZoom: 11 });
            }
        });
    </script>
</x-app-layout>
