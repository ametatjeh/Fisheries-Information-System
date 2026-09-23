@php
    $years = $years ?? collect();
    $wppnris = $wppnris ?? collect();
    $landingSites = $landingSites ?? collect();
    $fishingGears = $fishingGears ?? collect();
    $families = $families ?? collect();
    $selectedSpecies = $selectedSpecies ?? null;
    $stats = $stats ?? [];

    $defaultStats = [
        'trip_count' => 0,
        'vessel_count' => 0,
        'catch_weight' => 0,
        'species_count' => 0,
        'landing_trend' => ['labels' => [], 'data' => []],
        'cpue_trend' => ['labels' => [], 'data' => []],
        'species_catch' => ['labels' => [], 'data' => []],
        'length_frequency' => ['labels' => [], 'data' => []],
        'catch_by_gear' => ['labels' => [], 'data' => []],
        'catch_by_wpp' => ['labels' => [], 'data' => []],
        'fishing_ground' => ['points' => []],
    ];
    $stats = array_merge($defaultStats, $stats);

    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    // Detect active filters for UX tags
    $activeFilters = [];
    if (!empty($stats['filters']['tahun'])) {
        $activeFilters[] = ['label' => 'Tahun', 'value' => $stats['filters']['tahun']];
    }
    if (!empty($stats['filters']['bulan'])) {
        $activeFilters[] = ['label' => 'Bulan', 'value' => $months[$stats['filters']['bulan']] ?? $stats['filters']['bulan']];
    }
    if (!empty($stats['filters']['wppnri_id'])) {
        $val = $stats['filters']['wppnri_id'];
        $found = null;
        foreach ($wppnris as $item) {
            $arr = (array)$item;
            if (($arr['id'] ?? null) == $val) {
                $found = !empty($arr['code']) ? ('WPP-'.$arr['code']) : ($arr['name'] ?? null);
                break;
            }
        }
        $activeFilters[] = ['label' => 'WPP-RI', 'value' => $found ?? ('ID: '.$val)];
    }
    if (!empty($stats['filters']['landing_site_id'])) {
        $val = $stats['filters']['landing_site_id'];
        $found = null;
        foreach ($landingSites as $item) {
            $arr = (array)$item;
            if (($arr['id'] ?? null) == $val) {
                $found = $arr['name'] ?? null;
                break;
            }
        }
        $activeFilters[] = ['label' => 'Lokasi Pendaratan', 'value' => $found ?? ('ID: '.$val)];
    }
    if (!empty($stats['filters']['fishing_gear_id'])) {
        $val = $stats['filters']['fishing_gear_id'];
        $found = null;
        foreach ($fishingGears as $item) {
            $arr = (array)$item;
            if (($arr['id'] ?? null) == $val) {
                $found = $arr['name_id'] ?? null;
                break;
            }
        }
        $activeFilters[] = ['label' => 'Alat Tangkap', 'value' => $found ?? ('ID: '.$val)];
    }
    if (!empty($stats['filters']['family'])) {
        $activeFam = $stats['filters']['family'];
        $activeFamLabel = $activeFam;
        foreach ($families as $fItem) {
            $fVal = is_array($fItem) ? ($fItem['family'] ?? '') : (is_object($fItem) ? ($fItem->family ?? '') : (string)$fItem);
            $fTxt = is_array($fItem) ? ($fItem['label'] ?? $fVal) : (is_object($fItem) ? ($fItem->label ?? $fVal) : (string)$fItem);
            if ($fVal === $activeFam) {
                $activeFamLabel = $fTxt;
                break;
            }
        }
        $activeFilters[] = ['label' => 'Family', 'value' => $activeFamLabel];
    }
    if (!empty($selectedSpecies)) {
        $activeFilters[] = ['label' => 'Spesies', 'value' => $selectedSpecies['text'] ?? ('ID: '.$stats['filters']['species_id'])];
    }
@endphp

<div class="space-y-8 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-ocean-300 to-teal-200 tracking-tight">
            STATISTIK PERIKANAN
        </h2>
        <p class="mt-4 text-base text-slate-300 max-w-2xl mx-auto">
            Visualisasi dan ringkasan data perikanan berdasarkan periode, wilayah, alat tangkap, dan spesies.
        </p>
    </div>

    {{-- Filter Form --}}
    <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-6 shadow-xl relative z-30">
        <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
            <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                <span>🔍</span> FILTER STATISTIK
            </h3>
            @if(count($activeFilters) > 0)
                <span class="text-xs text-ocean-300 bg-ocean-500/10 border border-ocean-500/20 px-2.5 py-1 rounded-full">
                    {{ count($activeFilters) }} Filter Aktif
                </span>
            @endif
        </div>

        <form method="GET" action="/statistik" class="space-y-5">
            {{-- To keep activeMenu=statistik on submit --}}
            <input type="hidden" name="page" value="statistik">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Tahun --}}
                <div>
                    <label for="filter_tahun" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>📅</span> Tahun
                    </label>
                    <div class="relative">
                        <select id="filter_tahun" name="tahun" aria-label="Filter Tahun" title="Filter Tahun" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua Tahun</option>
                            @php
                                $selectedYear = $stats['filters']['tahun'] ?? request('tahun') ?? request('year');
                            @endphp
                            @foreach($years as $yr)
                                @php
                                    $yrVal = null;
                                    if (is_numeric($yr)) {
                                        $yrVal = (int) $yr;
                                    } elseif (is_array($yr)) {
                                        $yrVal = isset($yr['year']) && is_numeric($yr['year']) ? (int) $yr['year'] : (isset($yr[0]) && is_numeric($yr[0]) ? (int) $yr[0] : null);
                                    } elseif (is_object($yr) && isset($yr->year) && is_numeric($yr->year)) {
                                        $yrVal = (int) $yr->year;
                                    }
                                @endphp
                                @if(!empty($yrVal))
                                    <option value="{{ $yrVal }}" {{ (string)$selectedYear === (string)$yrVal ? 'selected' : '' }}>{{ $yrVal }}</option>
                                @endif
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Bulan --}}
                <div>
                    <label for="filter_bulan" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>🗓️</span> Bulan
                    </label>
                    <div class="relative">
                        <select id="filter_bulan" name="bulan" aria-label="Filter Bulan" title="Filter Bulan" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua Bulan</option>
                            @foreach($months as $num => $name)
                                <option value="{{ $num }}" {{ (request('bulan') == $num || request('month') == $num) ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- WPP-RI --}}
                <div>
                    <label for="filter_wppnri_id" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>🗺️</span> WPP-RI
                    </label>
                    <div class="relative">
                        <select id="filter_wppnri_id" name="wppnri_id" aria-label="Filter WPP-RI" title="Filter WPP-RI" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua WPP-RI</option>
                            @foreach($wppnris as $wpp)
                                @php
                                    $wppArr = (array)$wpp;
                                    $wppId = $wppArr['id'] ?? (is_scalar($wpp) ? (string)$wpp : '');
                                    $wppCode = $wppArr['code'] ?? '';
                                    $wppName = $wppArr['name'] ?? (is_scalar($wpp) ? (string)$wpp : '');
                                @endphp
                                <option value="{{ $wppId }}" {{ (request('wppnri_id') == $wppId || request('wilayah') == $wppId) ? 'selected' : '' }}>{{ $wppCode ? $wppCode . ' - ' : '' }}{{ $wppName }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Landing Site --}}
                <div>
                    <label for="filter_landing_site_id" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>⚓</span> Lokasi Pendaratan
                    </label>
                    <div class="relative">
                        <select id="filter_landing_site_id" name="landing_site_id" aria-label="Filter Lokasi Pendaratan" title="Filter Lokasi Pendaratan" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua Lokasi</option>
                            @foreach($landingSites as $site)
                                @php
                                    $siteArr = (array)$site;
                                    $siteId = $siteArr['id'] ?? (is_scalar($site) ? (string)$site : '');
                                    $siteName = $siteArr['name'] ?? (is_scalar($site) ? (string)$site : '');
                                @endphp
                                <option value="{{ $siteId }}" {{ (request('landing_site_id') == $siteId || request('site') == $siteId) ? 'selected' : '' }}>{{ $siteName }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Alat Tangkap --}}
                <div>
                    <label for="filter_fishing_gear_id" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>🎣</span> Alat Tangkap (Gear)
                    </label>
                    <div class="relative">
                        <select id="filter_fishing_gear_id" name="fishing_gear_id" aria-label="Filter Alat Tangkap (Gear)" title="Filter Alat Tangkap (Gear)" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua Alat Tangkap</option>
                            @foreach($fishingGears as $gear)
                                @php
                                    $gearArr = (array)$gear;
                                    $gearId = $gearArr['id'] ?? (is_scalar($gear) ? (string)$gear : '');
                                    $gearName = $gearArr['name_id'] ?? (is_scalar($gear) ? (string)$gear : '');
                                    $gearCode = $gearArr['isscfg_code'] ?? '';
                                @endphp
                                <option value="{{ $gearId }}" {{ (request('fishing_gear_id') == $gearId || request('gear') == $gearId) ? 'selected' : '' }}>{{ $gearName }} {{ $gearCode ? "({$gearCode})" : '' }}</option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Family --}}
                <div>
                    <label for="filter_family" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>🧬</span> Family Ikan
                    </label>
                    <div class="relative">
                        <select id="filter_family" name="family" aria-label="Filter Family Ikan" title="Filter Family Ikan" class="custom-filter-select w-full appearance-none bg-slate-900/95 hover:bg-slate-900 border border-slate-700/80 hover:border-cyan-500/60 focus:border-cyan-400 focus:ring-2 focus:ring-cyan-400/25 rounded-xl pl-3.5 pr-10 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer outline-none transition-all">
                            <option value="">Semua Family</option>
                            @php
                                $groupedFamilies = collect($families)->groupBy(function($item) {
                                    return is_array($item) ? ($item['group'] ?? 'Semua') : (is_object($item) ? ($item->group ?? 'Semua') : 'Semua');
                                });
                            @endphp
                            @foreach($groupedFamilies as $groupName => $items)
                                @if($groupedFamilies->count() > 1 && $groupName !== 'Semua')
                                    <optgroup label="{{ $groupName }}">
                                 @endif
                                @foreach($items as $fam)
                                    @php
                                        $famVal = is_array($fam) ? ($fam['family'] ?? '') : (is_object($fam) ? ($fam->family ?? '') : (string)$fam);
                                        $famLbl = is_array($fam) ? ($fam['label'] ?? $famVal) : (is_object($fam) ? ($fam->label ?? $famVal) : (string)$fam);
                                    @endphp
                                    <option value="{{ $famVal }}" {{ request('family') == $famVal ? 'selected' : '' }}>{{ $famLbl }}</option>
                                @endforeach
                                @if($groupedFamilies->count() > 1 && $groupName !== 'Semua')
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-cyan-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Species Searchable Custom Dropdown (Alpine.js) --}}
                <div class="md:col-span-2 relative z-50"
                     x-data="{
                         open: false,
                         search: '',
                         loading: false,
                         options: [],
                         selectedId: '{{ $selectedSpecies['id'] ?? request('species_id') ?? '' }}',
                         selectedText: '{{ addslashes($selectedSpecies['text'] ?? '') }}',
                         init() {
                             const familySelect = document.getElementById('filter_family');
                             if (familySelect) {
                                 familySelect.addEventListener('change', () => {
                                     this.selectedId = '';
                                     this.selectedText = '';
                                     this.options = [];
                                     if (this.open) {
                                         this.fetchOptions();
                                     }
                                 });
                             }
                         },
                         async fetchOptions() {
                             this.loading = true;
                             const familyVal = document.getElementById('filter_family')?.value || '';
                             try {
                                 const url = new URL('{{ route('landing.statistik.species-search') }}', window.location.origin);
                                 if (this.search) url.searchParams.set('q', this.search);
                                 if (familyVal) url.searchParams.set('family', familyVal);
                                 const res = await fetch(url, {
                                     headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                 });
                                 this.options = await res.json();
                             } catch (e) {
                                 this.options = [];
                             } finally {
                                 this.loading = false;
                             }
                         },
                         selectOption(opt) {
                             if (!opt) {
                                 this.selectedId = '';
                                 this.selectedText = '';
                             } else {
                                 this.selectedId = opt.id;
                                 this.selectedText = opt.text || (opt.fao_code + ' - ' + (opt.local_name_id || opt.scientific_name));
                             }
                             this.open = false;
                         }
                     }">
                    <label for="species_id_search_btn" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center gap-1.5">
                        <span>🐟</span> Spesies Ikan
                    </label>
                    <input type="hidden" name="species_id" :value="selectedId">
                    <div class="relative">
                        <!-- Main trigger button -->
                        <div id="species_id_search_btn"
                             @click="open = !open; if(open && options.length === 0) fetchOptions(); $nextTick(() => { if(open && $refs.speciesSearchInput) $refs.speciesSearchInput.focus(); })"
                             class="w-full flex items-center justify-between bg-[#0b1329] hover:bg-[#0f172a] border border-slate-700/90 hover:border-cyan-500/80 focus-within:border-cyan-400 focus-within:ring-2 focus-within:ring-cyan-400/25 rounded-xl pl-3.5 pr-3 py-2.5 text-sm text-white font-medium shadow-inner cursor-pointer transition-all">
                            <span x-text="selectedText || 'Semua Spesies Ikan'"
                                  :class="selectedId ? 'text-white font-semibold' : 'text-slate-400'"
                                  class="truncate pr-2"></span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <button type="button"
                                        x-show="selectedId"
                                        @click.stop="selectOption(null)"
                                        title="Hapus pilihan spesies"
                                        class="p-1 rounded-md text-slate-400 hover:text-rose-400 hover:bg-slate-800 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                <svg class="w-4 h-4 text-cyan-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        <!-- Dropdown panel -->
                        <div x-show="open"
                             @click.away="open = false"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-98"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                             x-transition:leave-end="opacity-0 translate-y-1 scale-98"
                             class="absolute z-50 left-0 right-0 mt-2 bg-[#0b1329] border border-cyan-500/50 rounded-xl shadow-2xl shadow-black/90 overflow-hidden">
                            <!-- Search input bar -->
                            <div class="p-2.5 border-b border-slate-800 bg-[#070d1e] flex items-center gap-2">
                                <span class="text-cyan-400 text-sm">🔍</span>
                                <input type="text"
                                       x-ref="speciesSearchInput"
                                       x-model="search"
                                       @input.debounce.250ms="fetchOptions"
                                       placeholder="Ketik kode FAO, nama lokal, atau ilmiah..."
                                       class="w-full bg-[#0b1329] border border-slate-700 rounded-lg px-3 py-2 text-xs sm:text-sm text-white placeholder-slate-400 focus:outline-none focus:border-cyan-400 focus:ring-1 focus:ring-cyan-400">
                                <button type="button" x-show="search" @click="search = ''; fetchOptions();" class="text-xs text-slate-400 hover:text-white px-1.5 py-1 rounded bg-slate-800">✕</button>
                            </div>

                            <!-- Options list -->
                            <ul class="max-h-64 overflow-y-auto p-1.5 divide-y divide-slate-800/50">
                                <li @click="selectOption(null)"
                                    class="px-3.5 py-2.5 rounded-lg text-xs sm:text-sm cursor-pointer transition-colors flex items-center justify-between"
                                    :class="!selectedId ? 'bg-ocean-600 text-white font-semibold' : 'text-slate-200 hover:bg-ocean-600 hover:text-white'">
                                    <span>-- Semua Spesies Ikan --</span>
                                    <span x-show="!selectedId" class="text-white font-bold">✓</span>
                                </li>

                                <li x-show="loading" class="px-3.5 py-4 text-center text-xs sm:text-sm text-cyan-300 flex items-center justify-center gap-2">
                                    <svg class="animate-spin h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Memuat spesies...</span>
                                </li>

                                <template x-for="opt in options" :key="opt.id">
                                    <li @click="selectOption(opt)"
                                        class="px-3.5 py-2.5 rounded-lg text-xs sm:text-sm cursor-pointer transition-colors flex items-center justify-between gap-2 group"
                                        :class="selectedId == opt.id ? 'bg-ocean-600 text-white font-semibold' : 'text-slate-100 hover:bg-ocean-600 hover:text-white'">
                                        <div class="flex items-center gap-2.5 truncate">
                                            <span class="px-2 py-0.5 rounded font-mono text-[11px] font-bold shrink-0 transition-colors"
                                                  :class="selectedId == opt.id ? 'bg-white/20 text-white border border-white/30' : 'bg-slate-800 border border-slate-700 text-cyan-300 group-hover:bg-ocean-700 group-hover:border-ocean-500 group-hover:text-white'"
                                                  x-text="opt.fao_code || '-'"></span>
                                            <span class="truncate font-medium" x-text="opt.local_name_id || opt.scientific_name || opt.text"></span>
                                            <span x-show="opt.scientific_name && opt.local_name_id"
                                                  class="text-[11px] italic truncate transition-colors"
                                                  :class="selectedId == opt.id ? 'text-ocean-100' : 'text-slate-400 group-hover:text-ocean-100'"
                                                  x-text="'(' + opt.scientific_name + ')'"></span>
                                        </div>
                                        <span x-show="selectedId == opt.id" class="text-white font-bold shrink-0">✓</span>
                                    </li>
                                </template>

                                <li x-show="!loading && options.length === 0 && search" class="px-3.5 py-4 text-center text-xs sm:text-sm text-slate-300">
                                    Tidak ada spesies yang sesuai dengan pencarian "<span x-text="search" class="text-white font-semibold"></span>"
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-white/5">
                <button type="submit" class="px-5 py-2.5 bg-ocean-600 hover:bg-ocean-500 text-white text-sm font-semibold rounded-xl shadow-lg transition-colors">
                    Tampilkan Statistik
                </button>
                <a href="/statistik?page=statistik" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium rounded-xl transition-colors">
                    Reset Filter
                </a>
            </div>

            @if(count($activeFilters) > 0)
                <div class="flex flex-wrap items-center gap-2 pt-3 mt-3 border-t border-white/10 text-xs">
                    <span class="text-slate-400 font-medium">Filter Aktif:</span>
                    @foreach($activeFilters as $af)
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-ocean-500/20 text-ocean-300 border border-ocean-500/30">
                            <strong class="mr-1 text-slate-300">{{ $af['label'] }}:</strong> {{ $af['value'] }}
                        </span>
                    @endforeach
                    <a href="/statistik?page=statistik" class="text-xs text-rose-400 hover:text-rose-300 underline ml-2">Hapus Filter</a>
                </div>
            @endif
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-6 relative z-10">
        {{-- Total Fishing Trip --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 shadow-lg flex flex-col justify-between">
            <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Total Fishing Trip</span>
                <span class="text-base" title="Fishing Trip">🚢</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ number_format($stats['trip_count'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-400 font-medium">Trip</span>
            </div>
        </div>

        {{-- Total Vessel --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 shadow-lg flex flex-col justify-between">
            <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Total Vessel</span>
                <span class="text-base" title="Armada Kapal">⛵</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ number_format($stats['vessel_count'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-400 font-medium">Kapal</span>
            </div>
        </div>

        {{-- Total Catch --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 shadow-lg flex flex-col justify-between">
            <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Total Catch</span>
                <span class="text-base" title="Hasil Tangkapan">🐟</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ number_format($stats['catch_weight'] ?? 0, 2, ',', '.') }}</span>
                <span class="text-xs text-slate-400 font-medium">kg</span>
            </div>
        </div>

        {{-- Total Species --}}
        <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl p-5 shadow-lg flex flex-col justify-between">
            <div class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-2 flex items-center justify-between">
                <span>Total Species</span>
                <span class="text-base" title="Keanekaragaman Spesies">🐠</span>
            </div>
            <div class="flex items-baseline gap-1.5">
                <span class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ number_format($stats['species_count'] ?? 0, 0, ',', '.') }}</span>
                <span class="text-xs text-slate-400 font-medium">Spesies</span>
            </div>
        </div>
    </div>


    {{-- Public Data Disclaimer --}}
    <div class="flex items-start gap-3 bg-yellow-500/20 text-yellow-200 px-4 py-3.5 text-xs border border-yellow-500/40 rounded-xl">
        <span class="text-base shrink-0 mt-0.5">⚠️</span>
        <span>
            <strong>DEMO DATA</strong> — Data yang ditampilkan pada halaman ini adalah <strong>data demonstrasi</strong> dan bukan data produksi resmi.
            Seluruh angka, grafik, dan statistik bersifat simulasi untuk keperluan pengembangan dan presentasi sistem.
        </span>
    </div>


    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart 1: Total Catch --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Total Produksi Tangkapan (Kg)</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-sky-500/20 text-sky-300 border border-sky-500/30 shrink-0">Per Bulan</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Akumulasi bobot tangkapan per bulan berdasarkan operasi penangkapan</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartLandingTrend"></canvas>
                <div id="emptyLandingTrend" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">📊</span>
                    Belum ada data produksi tangkapan untuk filter ini.
                </div>
            </div>
        </div>
        
        {{-- Chart 2: Catch by Species --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Top Catch berdasarkan Species (Kg)</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 shrink-0">Catch (kg)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">10 spesies dengan akumulasi volume tangkapan tertinggi</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartSpeciesCatch"></canvas>
                <div id="emptySpeciesCatch" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">🐟</span>
                    Belum ada data spesies untuk filter ini.
                </div>
            </div>
        </div>

        {{-- Chart 3: Catch Composition --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Komposisi Catch (%)</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 shrink-0">Proporsi (%)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Persentase kontribusi spesies terhadap total volume tangkapan</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartCatchComp"></canvas>
                <div id="emptyCatchComp" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">🥧</span>
                    Belum ada data komposisi untuk filter ini.
                </div>
            </div>
        </div>

        {{-- Chart 4: CPUE --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">CPUE Trend (Kg/Jam)</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30 shrink-0">kg/jam</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Catch Per Unit Effort (Catch ÷ Durasi Fishing Effort) per bulan operasi</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartCpueTrend"></canvas>
                <div id="emptyCpueTrend" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">⏱️</span>
                    Belum ada data CPUE untuk filter ini.
                </div>
            </div>
            <div class="text-[11px] text-slate-400 mt-2 bg-slate-800/40 p-2 rounded-lg border border-slate-700/50">
                ℹ️ Catatan: CPUE menunjukkan rata-rata laju tangkapan per jam operasi alat tangkap tercatat (kg/jam).
            </div>
        </div>

        {{-- Chart 5: Length Frequency --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Frekuensi Panjang Ikan</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-300 border border-purple-500/30 shrink-0">Fork Length (cm)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Distribusi ukuran panjang cagak ikan berdasarkan data pengukuran biologis sampel (ekor)</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartLengthFreq"></canvas>
                <div id="emptyLengthFreq" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">📏</span>
                    Belum ada data frekuensi panjang untuk filter ini.
                </div>
            </div>
        </div>

        {{-- Chart 6: Catch by Gear --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Catch berdasarkan Fishing Gear</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-pink-500/20 text-pink-300 border border-pink-500/30 shrink-0">Catch (kg)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Total hasil tangkapan berdasarkan alat tangkap (ISSCFG)</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartCatchGear"></canvas>
                <div id="emptyCatchGear" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">⚓</span>
                    Belum ada data catch by gear untuk filter ini.
                </div>
            </div>
        </div>

        {{-- Chart 7: Catch by WPP --}}
        <div class="bg-slate-900/60 backdrop-blur-md border border-slate-700 rounded-2xl p-6 shadow-inner flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h4 class="text-base sm:text-lg font-bold text-slate-100">Catch berdasarkan WPP/Wilayah</h4>
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-teal-500/20 text-teal-300 border border-teal-500/30 shrink-0">WPP-RI (kg)</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Total hasil tangkapan menurut Wilayah Pengelolaan Perikanan (WPP-RI)</p>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartCatchWpp"></canvas>
                <div id="emptyCatchWpp" class="hidden absolute inset-0 flex flex-col items-center justify-center text-slate-400 text-sm">
                    <span class="text-2xl mb-1">🗺️</span>
                    Belum ada data catch by WPP untuk filter ini.
                </div>
            </div>
            <div class="text-[11px] text-slate-400 mt-2 bg-slate-800/40 p-2 rounded-lg border border-slate-700/50">
                ℹ️ Catatan: Grafik WPP hanya menampilkan tangkapan yang memiliki asosiasi WPP. Data tangkapan tanpa WPP tidak ditempatkan ke wilayah secara fiktif.
            </div>
        </div>

        {{-- Chart 8: GIS Map --}}
        <div class="bg-slate-900/70 backdrop-blur-xl border border-slate-700/80 rounded-3xl p-5 sm:p-7 shadow-2xl flex flex-col justify-between lg:col-span-2 space-y-5">
            {{-- Header Peta --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                        <h4 class="text-lg sm:text-xl font-extrabold text-white flex items-center gap-2">
                            <span>🗺️</span> Fishing Ground / Fishing Effort Location
                        </h4>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">Lokasi Fishing Effort</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30">WPP-NRI 571 & 572</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Provinsi Aceh</span>
                    </div>
                    <p class="text-xs text-slate-400 max-w-3xl">
                        Peta spasial sebaran koordinat setting alat tangkap aktual dari fishing effort, pelabuhan pendaratan, homeport armada kapal, dan logbook historis di perairan Aceh.
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" id="btnToggleFgPanel" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl bg-purple-500/20 text-purple-300 border border-purple-500/30 hover:bg-purple-500/30 transition-colors" aria-expanded="false" aria-controls="panelMasterFishingGround">
                        <span>🧭</span> Master Fishing Ground (<span id="badge-master-fg-count">7</span>)
                    </button>
                    <button type="button" id="btnResetGisView" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-slate-800 text-slate-300 border border-slate-700 hover:bg-slate-700 hover:text-white transition-colors" title="Kembalikan posisi peta ke seluruh Aceh">
                        <span>🔄</span> Reset Peta
                    </button>
                </div>
            </div>

            {{-- GIS Analytical Summary Bar --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5 pt-1">
                {{-- 1. Effort --}}
                <div class="bg-slate-800/50 border border-rose-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-rose-300 uppercase tracking-wider flex items-center justify-between">
                        <span>Effort Terpetakan</span>
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white" id="statSummaryEfforts">10</div>
                        <div class="text-[10px] text-slate-400 leading-tight">dari 62 total transaksi</div>
                    </div>
                </div>

                {{-- 2. Ports --}}
                <div class="bg-slate-800/50 border border-sky-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-sky-300 uppercase tracking-wider flex items-center justify-between">
                        <span>Landing Site</span>
                        <span class="w-2 h-2 rounded-full bg-sky-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white" id="statSummaryPorts">12</div>
                        <div class="text-[10px] text-slate-400 leading-tight">pelabuhan & TPI aktif</div>
                    </div>
                </div>

                {{-- 3. Vessels --}}
                <div class="bg-slate-800/50 border border-emerald-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-emerald-300 uppercase tracking-wider flex items-center justify-between">
                        <span>Homeport Kapal</span>
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white" id="statSummaryVessels">19</div>
                        <div class="text-[10px] text-slate-400 leading-tight">armada terdaftar</div>
                    </div>
                </div>

                {{-- 4. Logbooks --}}
                <div class="bg-slate-800/50 border border-amber-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-amber-300 uppercase tracking-wider flex items-center justify-between">
                        <span>Logbook Historis</span>
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white" id="statSummaryLogbooks">29</div>
                        <div class="text-[10px] text-slate-400 leading-tight">titik observasi historis</div>
                    </div>
                </div>

                {{-- 5. Master FG --}}
                <div class="bg-slate-800/50 border border-purple-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-purple-300 uppercase tracking-wider flex items-center justify-between">
                        <span>Master Ground</span>
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white" id="statSummaryGrounds">7</div>
                        <div class="text-[10px] text-slate-400 leading-tight">0 bergeometri formal</div>
                    </div>
                </div>

                {{-- 6. WPP --}}
                <div class="bg-slate-800/50 border border-cyan-500/30 rounded-2xl p-2.5 sm:p-3 flex flex-col justify-between">
                    <div class="text-[10px] font-bold text-cyan-300 uppercase tracking-wider flex items-center justify-between">
                        <span>WPP Terkait</span>
                        <span class="w-2 h-2 rounded-sm bg-cyan-500"></span>
                    </div>
                    <div class="mt-1">
                        <div class="text-lg sm:text-xl font-black text-white">571 & 572</div>
                        <div class="text-[10px] text-slate-400 leading-tight">Selat Malaka & Sam. Hindia</div>
                    </div>
                </div>
            </div>

            {{-- Layer Controls Bar --}}
            <div class="flex flex-wrap items-center gap-2 pt-1" role="region" aria-label="Kontrol Layer GIS">
                <span class="text-xs font-bold text-slate-300 flex items-center gap-1 mr-1">
                    <span>🎛️</span> Layer Aktif:
                </span>

                {{-- 1. Effort Toggle --}}
                <button type="button" id="toggleEfforts" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-rose-500/20 text-rose-300 border-rose-500/50 shadow-sm cursor-pointer" data-layer="efforts" aria-pressed="true" aria-label="Toggle layer Fishing Effort">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                    <span>Fishing Effort</span>
                    <span id="badgeCountEfforts" class="text-[10px] px-1.5 py-0.2 rounded-full bg-rose-950/60 text-rose-200 border border-rose-500/40">0</span>
                </button>

                {{-- 2. Ports Toggle --}}
                <button type="button" id="togglePorts" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-sky-500/20 text-sky-300 border-sky-500/50 shadow-sm cursor-pointer" data-layer="ports" aria-pressed="true" aria-label="Toggle layer Landing Site">
                    <span class="w-2.5 h-2.5 rounded-full bg-sky-500 inline-block"></span>
                    <span>Landing Site / TPI</span>
                    <span id="badgeCountPorts" class="text-[10px] px-1.5 py-0.2 rounded-full bg-sky-950/60 text-sky-200 border border-sky-500/40">0</span>
                </button>

                {{-- 3. Vessels Toggle --}}
                <button type="button" id="toggleVessels" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-emerald-500/20 text-emerald-300 border-emerald-500/50 shadow-sm cursor-pointer" data-layer="vessels" aria-pressed="true" aria-label="Toggle layer Homeport Kapal">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Homeport Kapal</span>
                    <span id="badgeCountVessels" class="text-[10px] px-1.5 py-0.2 rounded-full bg-emerald-950/60 text-emerald-200 border border-emerald-500/40">0</span>
                </button>

                {{-- 4. Logbooks Toggle --}}
                <button type="button" id="toggleLogbooks" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-amber-500/20 text-amber-300 border-amber-500/50 shadow-sm cursor-pointer" data-layer="logbooks" aria-pressed="true" aria-label="Toggle layer Logbook Historis">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                    <span>Logbook Historis</span>
                    <span id="badgeCountLogbooks" class="text-[10px] px-1.5 py-0.2 rounded-full bg-amber-950/60 text-amber-200 border border-amber-500/40">0</span>
                </button>

                {{-- 5. Master Grounds Toggle (jika ada titik) --}}
                <button type="button" id="toggleGrounds" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-purple-500/20 text-purple-300 border-purple-500/50 shadow-sm cursor-pointer" data-layer="grounds" aria-pressed="true" aria-label="Toggle layer Fishing Ground">
                    <span class="w-2.5 h-2.5 rounded-full bg-purple-500 inline-block"></span>
                    <span>Fishing Ground</span>
                    <span id="badgeCountGrounds" class="text-[10px] px-1.5 py-0.2 rounded-full bg-purple-950/60 text-purple-200 border border-purple-500/40">0</span>
                </button>

                {{-- 6. WPP 571 Toggle --}}
                <button type="button" id="toggleWpp571" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-cyan-500/20 text-cyan-300 border-cyan-500/50 shadow-sm cursor-pointer" data-layer="wpp571" aria-pressed="true" aria-label="Toggle polygon WPP 571">
                    <span class="w-2.5 h-2.5 rounded-sm border border-cyan-400 bg-cyan-500/30 inline-block"></span>
                    <span>WPP 571</span>
                </button>

                {{-- 7. WPP 572 Toggle --}}
                <button type="button" id="toggleWpp572" class="layer-toggle-btn active inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-blue-500/20 text-blue-300 border-blue-500/50 shadow-sm cursor-pointer" data-layer="wpp572" aria-pressed="true" aria-label="Toggle polygon WPP 572">
                    <span class="w-2.5 h-2.5 rounded-sm border border-blue-400 bg-blue-500/30 inline-block"></span>
                    <span>WPP 572</span>
                </button>

                {{-- 8. RZWP3K Aceh Toggle (Geometry-Ready / Default OFF) --}}
                <button type="button" id="toggleRzwp3k" class="layer-toggle-btn inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition-all border bg-slate-800 text-slate-400 border-slate-700 opacity-60 shadow-sm cursor-pointer" data-layer="rzwp3k" aria-pressed="false" aria-label="Toggle zonasi RZWP3K Aceh">
                    <span class="w-2.5 h-2.5 rounded-sm border border-emerald-400 bg-emerald-500/30 inline-block"></span>
                    <span>RZWP3K Aceh</span>
                    <span id="badgeCountRzwp3k" class="text-[10px] px-1.5 py-0.2 rounded-full bg-emerald-950/60 text-emerald-200 border border-emerald-500/40">0</span>
                </button>

                {{-- 9. RZWP3K Zone Type Quick Filter --}}
                <div class="inline-flex items-center gap-1 bg-slate-900/80 border border-slate-700/80 rounded-xl px-2.5 py-1 text-xs">
                    <label for="filterRzwp3kZoneType" class="text-[11px] text-slate-400 font-medium">Zonasi:</label>
                    <select id="filterRzwp3kZoneType" aria-label="Filter Tipe Kawasan RZWP3K" class="bg-slate-950 text-emerald-300 text-xs font-semibold rounded-lg px-2 py-0.5 border border-slate-700 focus:border-emerald-500 outline-none cursor-pointer">
                        <option value="">Semua Kawasan</option>
                        <option value="KPU">KPU (Pemanfaatan Umum)</option>
                        <option value="KK">KK (Konservasi)</option>
                        <option value="AL">AL (Alur Laut)</option>
                        <option value="KSNT">KSNT (Strategis)</option>
                    </select>
                </div>
            </div>

            {{-- Map Container Wrapper --}}
            <div class="relative w-full rounded-2xl overflow-hidden border border-slate-700/80 bg-slate-950 h-[450px] sm:h-[520px] lg:h-[580px] shadow-inner" id="gisMapWrapper">

                {{-- MapLibre GL JS Map Element --}}
                <div id="fisheries-map" data-target="mapFishingGround" class="w-full h-full relative z-0"></div>

                {{-- RZWP3K Empty State Notice --}}
                <div id="rzwp3kEmptyNotice" class="hidden absolute top-3 left-3 right-3 sm:left-auto sm:right-3 sm:max-w-md bg-slate-900/95 backdrop-blur-md border border-emerald-500/40 rounded-xl p-3 shadow-2xl z-20 transition-all text-xs text-slate-300 space-y-1.5">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-1.5">
                        <strong class="text-emerald-400 font-bold flex items-center gap-1.5">
                            <span>🗺️</span> RZWP3K Aceh (Qanun 1/2020)
                        </strong>
                        <button type="button" id="btnCloseRzwp3kNotice" class="text-slate-400 hover:text-white text-xs px-1 cursor-pointer" aria-label="Tutup notifikasi">✕</button>
                    </div>
                    <p class="text-[11px] text-slate-300 leading-relaxed">
                        Data geometri resmi RZWP3K belum tersedia untuk ditampilkan pada peta.
                    </p>
                    <p class="text-[10px] text-slate-400 leading-snug">
                        Katalog 15 zona telah aktif dalam sistem. Poligon batas zonasi akan tampil secara otomatis setelah file vektor resmi terverifikasi dan diimpor.
                    </p>
                </div>

                {{-- Loading Spinner Overlay --}}
                <div id="mapLoadingOverlay" class="absolute inset-0 bg-slate-950/75 backdrop-blur-sm z-20 flex flex-col items-center justify-center gap-3 transition-opacity">
                    <div class="w-10 h-10 border-4 border-ocean-500/30 border-t-ocean-400 rounded-full animate-spin"></div>
                    <span class="text-xs font-semibold text-slate-200">Memuat data GIS...</span>
                </div>

                {{-- Error State Overlay --}}
                <div id="mapErrorOverlay" class="hidden absolute inset-0 bg-slate-950/90 z-20 flex flex-col items-center justify-center p-6 text-center">
                    <span class="text-3xl mb-2 text-rose-400">⚠️</span>
                    <h5 class="text-sm font-bold text-white mb-1">Gagal Memuat Data GIS</h5>
                    <p class="text-xs text-slate-400 max-w-sm mb-4">Data GIS tidak dapat dimuat.</p>
                    <button type="button" id="btnRetryGis" class="px-4 py-2 text-xs font-semibold rounded-xl bg-ocean-600 text-white hover:bg-ocean-500 transition-all cursor-pointer">
                        Coba Lagi
                    </button>
                </div>

                {{-- Empty State Overlay --}}
                <div id="emptyMapFishingGround" class="hidden absolute inset-0 bg-slate-950/80 backdrop-blur-sm z-10 flex flex-col items-center justify-center p-6 text-center text-slate-400">
                    <span class="text-3xl mb-2 text-amber-400">📍</span>
                    <h5 class="text-sm font-bold text-slate-200 mb-1">Belum ada data koordinat GIS</h5>
                    <p class="text-xs text-slate-400 max-w-sm">Tidak ditemukan data spasial berkoordinat valid untuk kombinasi filter yang dipilih.</p>
                </div>

                {{-- Floating Responsive Legend --}}
                <div class="absolute bottom-3 left-3 z-10 bg-slate-900/90 backdrop-blur-md border border-slate-700/80 rounded-xl p-2.5 shadow-xl text-[11px] text-slate-300 space-y-1 pointer-events-auto hidden sm:block max-w-[260px]">
                    <div class="font-bold text-white text-[11px] border-b border-slate-800 pb-1 mb-1">Legenda Peta (MapLibre)</div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 border border-white inline-block shrink-0"></span>
                        <span class="truncate">Fishing Effort (Setting Aktual)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-sky-500 border border-white inline-block shrink-0"></span>
                        <span class="truncate">Landing Site / TPI</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-white inline-block shrink-0"></span>
                        <span class="truncate">Homeport Kapal</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 border border-white inline-block shrink-0"></span>
                        <span class="truncate">Logbook Historis</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-500 border border-white inline-block shrink-0"></span>
                        <span class="truncate">Fishing Ground</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm border border-cyan-400 bg-cyan-500/30 inline-block shrink-0"></span>
                        <span class="truncate">WPP 571 (Selat Malaka)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm border border-blue-400 bg-blue-500/30 inline-block shrink-0"></span>
                        <span class="truncate">WPP 572 (Samudera Hindia)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-sm border border-emerald-400 bg-emerald-500/30 inline-block shrink-0"></span>
                        <span class="truncate">RZWP3K Aceh (Zonasi Pesisir)</span>
                    </div>
                </div>

            </div>

            {{-- Collapsible Master Fishing Ground Aceh Panel --}}
            <div id="panelMasterFishingGround" class="hidden bg-slate-800/40 border border-purple-500/20 rounded-2xl p-4 sm:p-5 space-y-3 transition-all">
                <div class="flex items-center justify-between">
                    <h5 class="text-sm font-bold text-purple-300 flex items-center gap-2">
                        <span>🧭</span> Master Daerah Penangkapan Ikan Aceh (WPP-NRI 571 & 572)
                    </h5>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-200 border border-purple-500/30">
                        7 Wilayah Master Terdaftar
                    </span>
                </div>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Daftar di bawah ini merupakan data master referensi daerah penangkapan ikan resmi untuk Provinsi Aceh. Sesuai prinsip integritas Satu Data Perikanan, koordinat titik atau poligon tidak direkayasa (tetap NULL) karena belum memiliki batas delimitasi oseanografi formal.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 pt-1" id="masterFgListContainer">
                    {{-- Diisi secara dinamis via JS dari master_fishing_grounds --}}
                </div>
            </div>

            {{-- Ringkasan Analisis Spasial WPP --}}
            <div class="bg-slate-800/40 border border-cyan-500/20 rounded-2xl p-4 sm:p-5 space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-700/60 pb-3">
                    <div>
                        <h5 class="text-sm font-bold text-cyan-300 flex items-center gap-2">
                            <span>📊</span> Analisis Spasial Berbasis Wilayah Pengelolaan (WPP-NRI)
                        </h5>
                        <p class="text-xs text-slate-400">Ringkasan hasil tangkapan observasi, upaya penangkapan, durasi operasi, dan estimasi CPUE rata-rata per wilayah maritim.</p>
                    </div>
                    <span class="text-[10px] font-semibold px-2.5 py-1 rounded-full bg-cyan-950 text-cyan-200 border border-cyan-700/50 shrink-0 self-start sm:self-center">
                        Formula CPUE: Catch (kg) / Durasi (Jam)
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-1" id="wppAnalysisListContainer">
                    {{-- Diisi secara dinamis via JS dari wpp_analysis --}}
                </div>
            </div>

            {{-- Tabel Ringkasan Lapisan Data GIS --}}
            <div class="bg-slate-800/40 border border-slate-700/60 rounded-2xl p-4 sm:p-5 space-y-3">
                <div class="flex items-center justify-between border-b border-slate-700/60 pb-2.5">
                    <h5 class="text-sm font-bold text-slate-200 flex items-center gap-2">
                        <span>📋</span> Matriks Integritas Data Spasial GIS Aceh
                    </h5>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-700/60 text-slate-300">
                        Audit Kualitas Spasial
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="text-[11px] uppercase bg-slate-900/60 text-slate-400 border-b border-slate-700/80">
                            <tr>
                                <th scope="col" class="py-2.5 px-3">Lapisan Data (Layer)</th>
                                <th scope="col" class="py-2.5 px-3">Klasifikasi Data</th>
                                <th scope="col" class="py-2.5 px-3 text-center">Total Database</th>
                                <th scope="col" class="py-2.5 px-3 text-center">Terpetakan (GPS)</th>
                                <th scope="col" class="py-2.5 px-3">Status Geometri & Batasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/80 text-[11px]">
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-rose-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span> Fishing Effort
                                </td>
                                <td class="py-2 px-3 text-slate-400">Transaksi Penangkapan</td>
                                <td class="py-2 px-3 text-center font-bold text-white">62</td>
                                <td class="py-2 px-3 text-center font-bold text-rose-300"><span id="tblCountEfforts">10</span></td>
                                <td class="py-2 px-3 text-slate-400">Titik setting GPS aktual (<span id="tblCountNullEfforts">52</span> data berstatus NULL tanpa titik buatan)</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-sky-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-sky-500 shrink-0"></span> Landing Site / TPI
                                </td>
                                <td class="py-2 px-3 text-slate-400">Master Lokasi Pendaratan</td>
                                <td class="py-2 px-3 text-center font-bold text-white">13</td>
                                <td class="py-2 px-3 text-center font-bold text-sky-300"><span id="tblCountPorts">12</span></td>
                                <td class="py-2 px-3 text-slate-400">12 fasilitas pelabuhan & TPI aktif berkoordinat valid (1 nonaktif)</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-emerald-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span> Homeport Kapal
                                </td>
                                <td class="py-2 px-3 text-slate-400">Master Armada Terdaftar</td>
                                <td class="py-2 px-3 text-center font-bold text-white">19</td>
                                <td class="py-2 px-3 text-center font-bold text-emerald-300"><span id="tblCountVessels">19</span></td>
                                <td class="py-2 px-3 text-slate-400">Pangkalan registrasi asal armada terdaftar (Bukan radar AIS/Live Tracking)</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-amber-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span> Logbook Historis
                                </td>
                                <td class="py-2 px-3 text-slate-400">Catatan Operasional</td>
                                <td class="py-2 px-3 text-center font-bold text-white">29</td>
                                <td class="py-2 px-3 text-center font-bold text-amber-300"><span id="tblCountLogbooks">29</span></td>
                                <td class="py-2 px-3 text-slate-400">Titik observasi penangkapan historis pada catatan logbook masa lampau</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-purple-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-purple-500 shrink-0"></span> Master Fishing Ground
                                </td>
                                <td class="py-2 px-3 text-slate-400">Master Referensi Daerah</td>
                                <td class="py-2 px-3 text-center font-bold text-white">8</td>
                                <td class="py-2 px-3 text-center font-bold text-purple-300">0</td>
                                <td class="py-2 px-3 text-slate-400">7 master aktif Aceh; koordinat belum memiliki batas formal (Tanpa koordinat dummy)</td>
                            </tr>
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-2 px-3 font-semibold text-cyan-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-sm bg-cyan-500 shrink-0"></span> WPP 571 & 572
                                </td>
                                <td class="py-2 px-3 text-slate-400">Batas Pengelolaan Spasial</td>
                                <td class="py-2 px-3 text-center font-bold text-white">2</td>
                                <td class="py-2 px-3 text-center font-bold text-cyan-300">2 Poligon</td>
                                <td class="py-2 px-3 text-slate-400">WPPNRI 571 (Selat Malaka) dan WPPNRI 572 (Samudera Hindia Barat Sumatera)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Catatan Integritas GIS --}}
            <div class="text-[11px] text-slate-400 bg-slate-800/40 p-3 rounded-xl border border-slate-700/50 space-y-1.5 leading-relaxed">
                <div class="font-bold text-slate-200 flex items-center gap-1.5">
                    <span>🛡️</span> Prinsip Integritas Data Spasial & Batasan Visualisasi GIS Aceh:
                </div>
                <ul class="list-disc list-inside space-y-1 text-slate-400 pl-1">
                    <li><strong>Fishing Effort:</strong> Menampilkan titik setting aktual (<code class="text-rose-300">latitude_setting</code>, <code class="text-rose-300">longitude_setting</code>). Data effort yang tidak memiliki koordinat (<span id="textNullEfforts">52 data NULL</span>) secara ketat <em>tidak difalsifikasi</em> menjadi titik buatan.</li>
                    <li><strong>Homeport Kapal:</strong> Merupakan pelabuhan pangkalan resmi tempat kapal terdaftar di Aceh. <em>Bukan merupakan live tracking kapal / radar AIS real-time</em>.</li>
                    <li><strong>Logbook Historis:</strong> Merupakan titik koordinat pengisian logbook penangkapan pada masa lampau, bukan posisi kapal saat ini.</li>
                    <li><strong>Master Fishing Ground:</strong> Merupakan entitas master terpisah dari fishing effort. Seluruh 7 fishing ground referensi berada dalam lingkup perairan Aceh (WPP 571 Selat Malaka dan WPP 572 Samudera Hindia).</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Metodologi & Batasan Statistik --}}
    <div class="bg-slate-900/40 border border-slate-800 rounded-2xl p-6 text-xs text-slate-400 leading-relaxed space-y-3">
        <h5 class="text-sm font-semibold text-slate-200 flex items-center gap-2">
            <span>ℹ️</span> Panduan Interpretasi & Batasan Data Statistik
        </h5>
        <p>
            Dashboard ini menyajikan visualisasi data operasional perikanan tangkap berdasarkan catatan logbook aktual (fishing logbook), pengambilan sampel biologis, dan data pendaratan pelabuhan.
        </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-400 pt-1">
            <div class="bg-slate-800/30 p-3 rounded-xl border border-slate-700/40">
                <strong class="text-slate-200 block mb-1">Total Catch vs Total Landing</strong>
                <span><em class="text-ocean-300">Total Catch</em> merepresentasikan estimasi hasil tangkapan di laut berdasarkan tanggal keberangkatan kapal (<em>departure_date</em>). Sedangkan data pendaratan di pelabuhan (<em>Total Landing</em>) menggunakan tanggal pendaratan aktual (<em>landing_date</em>).</span>
            </div>
            <div class="bg-slate-800/30 p-3 rounded-xl border border-slate-700/40">
                <strong class="text-slate-200 block mb-1">CPUE (Catch Per Unit Effort)</strong>
                <span>Dihitung murni sebagai rasio bobot tangkapan terhadap durasi operasional alat tangkap (<em>kg/jam</em>). Metrik ini mengukur laju hasil tangkapan per jam upaya tanpa pembobotan ekonomi atau efisiensi armada.</span>
            </div>
            <div class="bg-slate-800/30 p-3 rounded-xl border border-slate-700/40">
                <strong class="text-slate-200 block mb-1">Cakupan Wilayah Pengelolaan (WPP-RI)</strong>
                <span>Grafik WPP hanya memetakan trip yang memiliki asosiasi kode WPP resmi. Data tangkapan historis yang tidak memiliki metadata WPP tidak diasosiasikan secara fiktif ke wilayah mana pun.</span>
            </div>
            <div class="bg-slate-800/30 p-3 rounded-xl border border-slate-700/40">
                <strong class="text-slate-200 block mb-1">Koordinat Fishing Effort (GIS)</strong>
                <span>Sebaran titik pada peta GIS adalah titik koordinat setting alat tangkap aktual yang terekam pada instrumen fishing effort, bukan batas administratif area fishing ground.</span>
            </div>
            <div class="bg-slate-800/30 p-3 rounded-xl border border-slate-700/40 md:col-span-2">
                <strong class="text-slate-200 block mb-1">Observed Catch vs Estimated Catch (Estimasi & Raising Factor)</strong>
                <span>Angka pada dashboard publik ini menyajikan <em class="text-ocean-300">Observed Catch</em> (tangkapan observasi riil dari logbook dan pendaratan terverifikasi). Taksiran total produksi seluruh populasi armada yang diekstrapolasi menggunakan metodologi <em>Raising Factor</em> dikelola terpisah pada modul analisis internal dan tidak menggantikan data observasi riil secara otomatis.</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* MapLibre GL JS Custom Glassmorphism & Dark Styling */
    .maplibregl-popup {
        z-index: 30 !important;
    }
    .maplibregl-popup-content {
        background: #0b1329 !important;
        color: #f1f5f9 !important;
        border: 1px solid rgba(56, 189, 248, 0.3) !important;
        border-radius: 0.875rem !important;
        padding: 14px !important;
        box-shadow: 0 20px 30px -10px rgba(0, 0, 0, 0.7) !important;
        font-family: ui-sans-serif, system-ui, sans-serif !important;
    }
    .maplibregl-popup-anchor-top .maplibregl-popup-tip { border-bottom-color: #0b1329 !important; }
    .maplibregl-popup-anchor-bottom .maplibregl-popup-tip { border-top-color: #0b1329 !important; }
    .maplibregl-popup-anchor-left .maplibregl-popup-tip { border-right-color: #0b1329 !important; }
    .maplibregl-popup-anchor-right .maplibregl-popup-tip { border-left-color: #0b1329 !important; }
    .maplibregl-popup-anchor-top-left .maplibregl-popup-tip { border-bottom-color: #0b1329 !important; }
    .maplibregl-popup-anchor-top-right .maplibregl-popup-tip { border-bottom-color: #0b1329 !important; }
    .maplibregl-popup-anchor-bottom-left .maplibregl-popup-tip { border-top-color: #0b1329 !important; }
    .maplibregl-popup-anchor-bottom-right .maplibregl-popup-tip { border-top-color: #0b1329 !important; }
    .maplibregl-popup-close-button {
        color: #94a3b8 !important;
        padding: 4px 8px !important;
        font-size: 16px !important;
        right: 4px !important;
        top: 4px !important;
    }
    .maplibregl-popup-close-button:hover {
        color: #ffffff !important;
        background: transparent !important;
    }
    .maplibregl-ctrl-group {
        background: rgba(11, 19, 41, 0.85) !important;
        border: 1px solid rgba(255, 255, 255, 0.15) !important;
        backdrop-filter: blur(8px) !important;
        border-radius: 0.75rem !important;
        overflow: hidden !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5) !important;
    }
    .maplibregl-ctrl-group button {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    .maplibregl-ctrl-group button .maplibregl-ctrl-icon {
        filter: invert(1) hue-rotate(180deg) !important;
    }
    .maplibregl-ctrl-scale {
        background: rgba(11, 19, 41, 0.8) !important;
        color: #94a3b8 !important;
        border: 1px solid rgba(255, 255, 255, 0.2) !important;
        border-top: none !important;
        font-size: 10px !important;
        font-weight: 600 !important;
    }

    /* Custom Styled Filter Dropdowns */
    .custom-filter-select {
        background-color: #0b1329 !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    .custom-filter-select:hover {
        border-color: #0284c7 !important;
    }
    .custom-filter-select:focus {
        border-color: #38bdf8 !important;
        box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.25) !important;
    }
    .custom-filter-select option {
        background-color: #0b1329 !important;
        color: #f1f5f9 !important;
        padding: 10px 14px !important;
    }
    .custom-filter-select optgroup {
        background-color: #080e1e !important;
        color: #38bdf8 !important;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: 0.03em;
        padding: 6px 10px;
    }
    .custom-filter-select option:checked {
        background-color: #0284c7 !important;
        color: #ffffff !important;
        font-weight: 600;
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.css" crossorigin=""/>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/maplibre-gl@4.7.1/dist/maplibre-gl.js" crossorigin=""></script>

<script>
    $(document).ready(function() {
        // Initialize Charts
        Chart.defaults.color = '#94a3b8'; // text-slate-400
        Chart.defaults.font.family = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif';

        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            },
            scales: {
                x: { grid: { color: '#334155', drawBorder: false }, ticks: { color: '#cbd5e1' } },
                y: { grid: { color: '#334155', drawBorder: false }, ticks: { color: '#cbd5e1' } }
            }
        };

        const doughnutOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#e2e8f0' } }
            }
        };

        const landingData = @json($stats['landing_trend'] ?? ['labels' => [], 'data' => []]);
        const speciesData = @json($stats['species_catch'] ?? ['labels' => [], 'data' => []]);
        const cpueData = @json($stats['cpue_trend'] ?? ['labels' => [], 'data' => []]);
        const lengthFreqData = @json($stats['length_frequency'] ?? ['labels' => [], 'data' => []]);
        const catchGearData = @json($stats['catch_by_gear'] ?? ['labels' => [], 'data' => []]);
        const catchWppData = @json($stats['catch_by_wpp'] ?? ['labels' => [], 'data' => []]);
        const mapData = @json($stats['fishing_ground'] ?? ['points' => []]);

        // Chart 1: Catch Trend
        if (landingData.labels && landingData.labels.length > 0) {
            new Chart(document.getElementById('chartLandingTrend'), {
                type: 'bar',
                data: {
                    labels: landingData.labels,
                    datasets: [{
                        label: 'Total Catch (Kg)',
                        data: landingData.data,
                        backgroundColor: 'rgba(14, 165, 233, 0.7)',
                        borderColor: 'rgba(14, 165, 233, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.parsed.y || 0);
                                    return ` Total Catch: ${val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} kg`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            $('#chartLandingTrend').hide();
            $('#emptyLandingTrend').removeClass('hidden');
        }

        // Chart 2: Species Catch
        if (speciesData.labels && speciesData.labels.length > 0) {
            new Chart(document.getElementById('chartSpeciesCatch'), {
                type: 'bar',
                data: {
                    labels: speciesData.labels,
                    datasets: [{
                        label: 'Catch (Kg)',
                        data: speciesData.data,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: 'rgba(16, 185, 129, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartOptions,
                    indexAxis: 'y', // horizontal bar
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.parsed.x || 0);
                                    return ` Catch: ${val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} kg`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            $('#chartSpeciesCatch').hide();
            $('#emptySpeciesCatch').removeClass('hidden');
        }

        // Chart 3: Catch Composition (Doughnut)
        if (speciesData.labels && speciesData.labels.length > 0) {
            const colors = ['#0ea5e9', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#f43f5e', '#14b8a6', '#6366f1', '#a855f7', '#64748b'];
            new Chart(document.getElementById('chartCatchComp'), {
                type: 'doughnut',
                data: {
                    labels: speciesData.labels,
                    datasets: [{
                        data: speciesData.data,
                        backgroundColor: colors.slice(0, speciesData.data.length),
                        borderWidth: 0
                    }]
                },
                options: {
                    ...doughnutOptions,
                    plugins: {
                        ...doughnutOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const val = Number(context.parsed || 0);
                                    const pct = total > 0 ? ((val / total) * 100).toFixed(2) : '0.00';
                                    return ` ${context.label}: ${pct}% (${val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} kg)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            $('#chartCatchComp').hide();
            $('#emptyCatchComp').removeClass('hidden');
        }

        // Chart 4: CPUE Trend
        if (cpueData.labels && cpueData.labels.length > 0) {
            new Chart(document.getElementById('chartCpueTrend'), {
                type: 'line',
                data: {
                    labels: cpueData.labels,
                    datasets: [{
                        label: 'CPUE (Kg/Jam)',
                        data: cpueData.data,
                        backgroundColor: 'rgba(245, 158, 11, 0.2)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 2,
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.parsed.y || 0);
                                    return ` CPUE: ${val.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} kg/jam`;
                                }
                            }
                        }
                    },
                    scales: {
                        ...chartOptions.scales,
                        y: {
                            ...chartOptions.scales.y,
                            title: { display: true, text: 'CPUE (kg/jam)', color: '#94a3b8' }
                        }
                    }
                }
            });
        } else {
            $('#chartCpueTrend').hide();
            $('#emptyCpueTrend').removeClass('hidden');
        }

        // Chart 5: Length Frequency (Bar / Histogram)
        if (lengthFreqData.labels && lengthFreqData.labels.length > 0) {
            new Chart(document.getElementById('chartLengthFreq'), {
                type: 'bar',
                data: {
                    labels: lengthFreqData.labels,
                    datasets: [{
                        label: 'Frekuensi (Ekor)',
                        data: lengthFreqData.data,
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderColor: 'rgba(139, 92, 246, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return ` Frekuensi: ${context.parsed.y} ekor`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1' },
                            title: { display: true, text: 'Panjang Ikan (Fork Length, cm)', color: '#94a3b8' }
                        },
                        y: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1', precision: 0 },
                            title: { display: true, text: 'Frekuensi (Ekor)', color: '#94a3b8' }
                        }
                    }
                }
            });
        } else {
            $('#chartLengthFreq').hide();
            $('#emptyLengthFreq').removeClass('hidden');
        }

        // Chart 6: Catch by Gear (Horizontal Bar)
        if (catchGearData.labels && catchGearData.labels.length > 0) {
            new Chart(document.getElementById('chartCatchGear'), {
                type: 'bar',
                data: {
                    labels: catchGearData.labels,
                    datasets: [{
                        label: 'Catch (kg)',
                        data: catchGearData.data,
                        backgroundColor: 'rgba(236, 72, 153, 0.7)',
                        borderColor: 'rgba(236, 72, 153, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartOptions,
                    indexAxis: 'y',
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.parsed.x || 0);
                                    return ` Catch: ${val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} kg`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1' },
                            title: { display: true, text: 'Catch (kg)', color: '#94a3b8' }
                        },
                        y: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1' },
                            title: { display: true, text: 'Fishing Gear', color: '#94a3b8' }
                        }
                    }
                }
            });
        } else {
            $('#chartCatchGear').hide();
            $('#emptyCatchGear').removeClass('hidden');
        }

        // Chart 7: Catch by WPP (Bar Chart)
        if (catchWppData.labels && catchWppData.labels.length > 0) {
            new Chart(document.getElementById('chartCatchWpp'), {
                type: 'bar',
                data: {
                    labels: catchWppData.labels,
                    datasets: [{
                        label: 'Catch (kg)',
                        data: catchWppData.data,
                        backgroundColor: 'rgba(20, 184, 166, 0.7)',
                        borderColor: 'rgba(20, 184, 166, 1)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    ...chartOptions,
                    plugins: {
                        ...chartOptions.plugins,
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const val = Number(context.parsed.y || 0);
                                    return ` Catch: ${val.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 })} kg`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1' },
                            title: { display: true, text: 'Wilayah Pengelolaan Perikanan (WPP-RI)', color: '#94a3b8' }
                        },
                        y: {
                            grid: { color: '#334155', drawBorder: false },
                            ticks: { color: '#cbd5e1' },
                            title: { display: true, text: 'Catch (kg)', color: '#94a3b8' }
                        }
                    }
                }
            });
        } else {
            $('#chartCatchWpp').hide();
            $('#emptyCatchWpp').removeClass('hidden');
        }

        // ==========================================================
        // Chart 8: Multi-Layer MapLibre GL JS GIS Map (Provinsi Aceh)
        // ==========================================================
        const mapElement = document.getElementById('fisheries-map') || document.getElementById('mapFishingGround');
        if (mapElement) {
            // Base style: OpenStreetMap Raster Tiles via MapLibre Style Specification
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

            // Definisi Geometri WPP 571 & 572 Resmi
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

            // Inisialisasi MapLibre Map (Fokus Wilayah Aceh: [lng, lat])
            const map = new maplibregl.Map({
                container: mapElement,
                style: mapStyle,
                center: [95.32, 5.55],
                zoom: 6.8,
                minZoom: 4,
                maxZoom: 18,
                pitchWithRotate: false,
                attributionControl: true
            });

            // Navigation & Scale Controls
            map.addControl(new maplibregl.NavigationControl({ showCompass: true, showZoom: true }), 'top-right');
            map.addControl(new maplibregl.ScaleControl({ maxWidth: 120, unit: 'metric' }), 'bottom-right');

            // Shared popup instance
            const popup = new maplibregl.Popup({
                closeButton: true,
                closeOnClick: true,
                maxWidth: '300px'
            });

            let cachedBounds = null;

            // Helper format angka desimal / berat
            function formatKg(val) {
                return Number(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }) + ' kg';
            }

            // Layer Visibility Map
            const layerMapping = {
                efforts: ['layer-efforts-circle'],
                ports: ['layer-ports-circle'],
                vessels: ['layer-vessels-circle'],
                logbooks: ['layer-logbooks-circle'],
                grounds: ['layer-grounds-circle'],
                wpp571: ['layer-wpp-571-fill', 'layer-wpp-571-line'],
                wpp572: ['layer-wpp-572-fill', 'layer-wpp-572-line'],
                rzwp3k: ['layer-rzwp3k-fill', 'layer-rzwp3k-line']
            };


            // Fungsi Memuat Data GIS via API Public Statistics
            function loadGisSpatialData() {
                $('#mapLoadingOverlay').removeClass('hidden');
                $('#mapErrorOverlay').addClass('hidden');

                const searchParams = window.location.search || '';
                const apiUrl = '{{ route("api.public.gis.data") }}' + searchParams;

                fetch(apiUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => {
                    if (!res.ok) throw new Error('HTTP status ' + res.status);
                    return res.json();
                })
                .then(data => {
                    const boundsCoordinates = [];

                    // 1. DATA FISHING EFFORT (Setting Alat Tangkap Aktual)
                    const effortsList = data.efforts || data.fishing_efforts || [];
                    const effortsFeatures = [];
                    effortsList.forEach(item => {
                        const lat = parseFloat(item.lat_setting);
                        const lng = parseFloat(item.lng_setting);
                        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && lat !== 0 && lng !== 0) {
                            effortsFeatures.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lng, lat] },
                                properties: {
                                    id: item.id,
                                    trip_code: item.trip_code || '-',
                                    vessel: item.vessel || '-',
                                    captain: item.captain || '-',
                                    gear: item.gear || '-',
                                    port: item.port || '-',
                                    regency: item.regency || '-',
                                    wpp_code: item.wpp_code || '-',
                                    setting_time: item.setting_time || '-',
                                    duration_hours: item.duration_hours || 0,
                                    catch_kg: item.catch_kg || 0,
                                    species_summary: (item.species_list && item.species_list.length > 0)
                                        ? item.species_list.map(s => `${s.name} (${formatKg(s.weight_kg)})`).join(', ')
                                        : '',
                                    lat_setting: lat,
                                    lng_setting: lng
                                }
                            });
                            boundsCoordinates.push([lng, lat]);
                        }
                    });

                    // 2. DATA PELABUHAN / TPI (Landing Sites)
                    const portsList = data.ports || data.landing_sites || [];
                    const portsFeatures = [];
                    portsList.forEach(item => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lng);
                        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && lat !== 0 && lng !== 0) {
                            portsFeatures.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lng, lat] },
                                properties: {
                                    id: item.id,
                                    name: item.name || '-',
                                    type: item.type || 'TPI',
                                    code: item.code || '-',
                                    regency: item.regency || '-',
                                    province: item.province || 'Aceh',
                                    vessels_count: item.vessels_count || 0,
                                    landings_count: item.landings_count || 0,
                                    address: item.address || '-',
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            boundsCoordinates.push([lng, lat]);
                        }
                    });

                    // 3. DATA HOMEPORT KAPAL (Pangkalan Asal - Bukan Live Tracking)
                    const vesselsList = data.vessels || data.homeports || [];
                    const vesselsFeatures = [];
                    vesselsList.forEach(item => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lng);
                        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && lat !== 0 && lng !== 0) {
                            vesselsFeatures.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lng, lat] },
                                properties: {
                                    id: item.id,
                                    name: item.name || '-',
                                    gt: item.gt ? (item.gt + ' GT') : '-',
                                    registration: item.registration || '-',
                                    type: item.type || '-',
                                    gear: item.gear || '-',
                                    homeport: item.homeport || '-',
                                    regency: item.regency || '-',
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            boundsCoordinates.push([lng, lat]);
                        }
                    });

                    // 4. DATA LOGBOOK HISTORIS (Bukan Real-time)
                    const logbooksList = data.logbooks || data.logbook_points || [];
                    const logbooksFeatures = [];
                    logbooksList.forEach(item => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lng);
                        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && lat !== 0 && lng !== 0) {
                            logbooksFeatures.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lng, lat] },
                                properties: {
                                    id: item.id,
                                    date: item.date || '-',
                                    trip_code: item.trip_code || '-',
                                    vessel: item.vessel || '-',
                                    time: item.time || '-',
                                    weather: item.weather || '-',
                                    wave_height: item.wave_height || 0,
                                    activity: item.activity || '-',
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            boundsCoordinates.push([lng, lat]);
                        }
                    });

                    // 5. DATA MASTER FISHING GROUNDS (Titik valid)
                    const groundsList = data.fishing_grounds || [];
                    const groundsFeatures = [];
                    groundsList.forEach(item => {
                        const lat = parseFloat(item.lat);
                        const lng = parseFloat(item.lng);
                        if (Number.isFinite(lat) && Number.isFinite(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180 && lat !== 0 && lng !== 0) {
                            groundsFeatures.push({
                                type: 'Feature',
                                geometry: { type: 'Point', coordinates: [lng, lat] },
                                properties: {
                                    id: item.id,
                                    name: item.name || '-',
                                    code: item.code || '-',
                                    wpp_info: item.wpp_code || item.wpp_name || '-',
                                    description: item.description || '-',
                                    lat: lat,
                                    lng: lng
                                }
                            });
                            boundsCoordinates.push([lng, lat]);
                        }
                    });

                    // Update MapLibre GeoJSON Sources
                    if (map.getSource('source-efforts')) {
                        map.getSource('source-efforts').setData({ type: 'FeatureCollection', features: effortsFeatures });
                    }
                    if (map.getSource('source-ports')) {
                        map.getSource('source-ports').setData({ type: 'FeatureCollection', features: portsFeatures });
                    }
                    if (map.getSource('source-vessels')) {
                        map.getSource('source-vessels').setData({ type: 'FeatureCollection', features: vesselsFeatures });
                    }
                    if (map.getSource('source-logbooks')) {
                        map.getSource('source-logbooks').setData({ type: 'FeatureCollection', features: logbooksFeatures });
                    }
                    if (map.getSource('source-grounds')) {
                        map.getSource('source-grounds').setData({ type: 'FeatureCollection', features: groundsFeatures });
                    }

                    // 6. RZWP3K ACEH GEOJSON LOAD (Geometry-Ready / Safe Fallback)
                    fetch('{{ route("api.rzwp3k.zones") }}', {
                        headers: { 'Accept': 'application/geo+json, application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.json())
                    .then(rzData => {
                        const rzFeatures = (rzData && rzData.features) ? rzData.features : [];
                        $('#badgeCountRzwp3k').text(rzFeatures.length);
                        if (map.getSource('source-rzwp3k')) {
                            map.getSource('source-rzwp3k').setData(rzData);
                        }
                    })
                    .catch(e => {
                        console.warn('RZWP3K GeoJSON notice:', e);
                    });

                    // Update Badge Counters & Summary Cards
                    const mappedEfforts = effortsFeatures.length;
                    const mappedPorts = portsFeatures.length;
                    const mappedVessels = vesselsFeatures.length;
                    const mappedLogbooks = logbooksFeatures.length;
                    const mappedGrounds = groundsFeatures.length;

                    $('#badgeCountEfforts').text(mappedEfforts);
                    $('#badgeCountPorts').text(mappedPorts);
                    $('#badgeCountVessels').text(mappedVessels);
                    $('#badgeCountLogbooks').text(mappedLogbooks);
                    $('#badgeCountGrounds').text(mappedGrounds);

                    $('#statSummaryEfforts').text(mappedEfforts);
                    $('#statSummaryPorts').text(mappedPorts);
                    $('#statSummaryVessels').text(mappedVessels);
                    $('#statSummaryLogbooks').text(mappedLogbooks);
                    $('#statSummaryGrounds').text(data.master_fishing_grounds ? data.master_fishing_grounds.length : 7);

                    $('#tblCountEfforts').text(mappedEfforts);
                    $('#tblCountPorts').text(mappedPorts);
                    $('#tblCountVessels').text(mappedVessels);
                    $('#tblCountLogbooks').text(mappedLogbooks);
                    $('#tblCountNullEfforts').text(Math.max(0, 62 - mappedEfforts));

                    // Master Fishing Grounds Reference Drawer List
                    const masterFgList = data.master_fishing_grounds || [];
                    $('#badge-master-fg-count').text(masterFgList.length);

                    const $container = $('#masterFgListContainer');
                    $container.empty();
                    if (masterFgList.length > 0) {
                        masterFgList.forEach(fg => {
                            const cardHtml = `
                                <div class="bg-slate-900/60 border border-purple-500/30 rounded-xl p-3 space-y-1.5 text-xs">
                                    <div class="flex items-center justify-between">
                                        <strong class="text-white text-xs font-bold">${fg.name}</strong>
                                        <span class="text-[9px] font-mono px-1.5 py-0.5 rounded bg-purple-950 text-purple-200 border border-purple-700/50">${fg.wpp_code || '-'}</span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 leading-snug">${fg.description || '-'}</div>
                                    <div class="pt-1 flex items-center justify-between text-[10px] border-t border-slate-800">
                                        <span class="text-amber-400 font-medium">⚠️ ${fg.geometry_status}</span>
                                        <span class="text-slate-500 font-mono">${fg.code}</span>
                                    </div>
                                </div>
                            `;
                            $container.append(cardHtml);
                        });
                    } else {
                        $container.html('<div class="text-xs text-slate-400 col-span-full">Tidak ada Master Fishing Ground terdaftar untuk filter ini.</div>');
                    }

                    // WPP Spatial Analysis Cards Rendering
                    const wppAnalysisList = data.wpp_analysis || [];
                    const $wppContainer = $('#wppAnalysisListContainer');
                    $wppContainer.empty();
                    if (wppAnalysisList.length > 0) {
                        wppAnalysisList.forEach(w => {
                            const topSpeciesStr = (w.top_species && w.top_species.length > 0)
                                ? w.top_species.map(s => `${s.name} (${formatKg(s.weight_kg)})`).join(', ')
                                : 'Belum tercatat';

                            const wppCardHtml = `
                                <div class="bg-slate-900/60 border border-cyan-500/30 rounded-xl p-3.5 space-y-2 text-xs">
                                    <div class="flex items-center justify-between border-b border-slate-800 pb-2">
                                        <strong class="text-white text-xs font-bold">${w.name}</strong>
                                        <span class="text-[9px] font-mono px-2 py-0.5 rounded bg-cyan-950 text-cyan-200 border border-cyan-700/50">WPP ${w.code || '-'}</span>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2 pt-0.5 text-[11px]">
                                        <div>
                                            <div class="text-slate-400 text-[10px]">Tangkapan Observasi:</div>
                                            <div class="font-bold text-cyan-300">${formatKg(w.catch_kg)} <span class="text-[10px] font-normal text-slate-400">(${w.catch_ton} Ton)</span></div>
                                        </div>
                                        <div>
                                            <div class="text-slate-400 text-[10px]">Laju CPUE Rata-rata:</div>
                                            <div class="font-bold text-emerald-300">${w.cpue_hourly || w.cpue || 0} <span class="text-[10px] font-normal text-slate-400">kg/jam</span></div>
                                        </div>
                                        <div>
                                            <div class="text-slate-400 text-[10px]">Trip Operasional:</div>
                                            <div class="font-semibold text-slate-200">${w.trips || 0} Trip</div>
                                        </div>
                                        <div>
                                            <div class="text-slate-400 text-[10px]">Effort Terpetakan:</div>
                                            <div class="font-semibold text-rose-300">${w.efforts || 0} Titik (${w.duration_hours || 0} Jam)</div>
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-slate-400 pt-1.5 border-t border-slate-800/80">
                                        <strong class="text-slate-300">Spesies Dominan:</strong> ${topSpeciesStr}
                                    </div>
                                </div>
                            `;
                            $wppContainer.append(wppCardHtml);
                        });
                    } else {
                        $wppContainer.html('<div class="text-xs text-slate-400 col-span-full">Tidak ada data agregasi WPP untuk filter ini.</div>');
                    }

                    // Handle FitBounds & Empty State
                    if (boundsCoordinates.length === 0) {
                        $('#emptyMapFishingGround').removeClass('hidden');
                        cachedBounds = null;
                    } else {
                        $('#emptyMapFishingGround').addClass('hidden');
                        const bounds = new maplibregl.LngLatBounds();
                        boundsCoordinates.forEach(coord => bounds.extend(coord));
                        cachedBounds = bounds;
                        map.fitBounds(bounds, { padding: 45, maxZoom: 12, duration: 1000 });
                    }

                    $('#mapLoadingOverlay').addClass('hidden');
                    setTimeout(() => map.resize(), 200);
                })
                .catch(err => {
                    console.error("GIS MapLibre load error:", err);
                    $('#mapLoadingOverlay').addClass('hidden');
                    $('#mapErrorOverlay').removeClass('hidden');
                });
            }

            // Inisialisasi Layer & Event Listener saat MapLibre siap
            map.on('load', function() {
                // 1. Sources
                map.addSource('source-wpp-571', { type: 'geojson', data: wpp571GeoJson });
                map.addSource('source-wpp-572', { type: 'geojson', data: wpp572GeoJson });
                map.addSource('source-rzwp3k', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addSource('source-grounds', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addSource('source-logbooks', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addSource('source-vessels', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addSource('source-ports', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });
                map.addSource('source-efforts', { type: 'geojson', data: { type: 'FeatureCollection', features: [] } });

                // 2. WPP Layers (Polygon Fill & Outline)
                map.addLayer({
                    id: 'layer-wpp-571-fill',
                    type: 'fill',
                    source: 'source-wpp-571',
                    layout: { visibility: 'visible' },
                    paint: { 'fill-color': '#06b6d4', 'fill-opacity': 0.08 }
                });
                map.addLayer({
                    id: 'layer-wpp-571-line',
                    type: 'line',
                    source: 'source-wpp-571',
                    layout: { visibility: 'visible' },
                    paint: { 'line-color': '#06b6d4', 'line-width': 1.5, 'line-dasharray': [3, 2] }
                });

                map.addLayer({
                    id: 'layer-wpp-572-fill',
                    type: 'fill',
                    source: 'source-wpp-572',
                    layout: { visibility: 'visible' },
                    paint: { 'fill-color': '#3b82f6', 'fill-opacity': 0.08 }
                });
                map.addLayer({
                    id: 'layer-wpp-572-line',
                    type: 'line',
                    source: 'source-wpp-572',
                    layout: { visibility: 'visible' },
                    paint: { 'line-color': '#3b82f6', 'line-width': 1.5, 'line-dasharray': [3, 2] }
                });

                // 3. RZWP3K Aceh Layers (Underneath Point Circles)
                map.addLayer({
                    id: 'layer-rzwp3k-fill',
                    type: 'fill',
                    source: 'source-rzwp3k',
                    layout: { visibility: 'none' },
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
                    id: 'layer-rzwp3k-line',
                    type: 'line',
                    source: 'source-rzwp3k',
                    layout: { visibility: 'none' },
                    paint: {
                        'line-color': [
                            'match',
                            ['get', 'zone_type'],
                            'KPU', '#059669',
                            'KK', '#0891b2',
                            'AL', '#d97706',
                            'KSNT', '#7c3aed',
                            '#0d9488'
                        ],
                        'line-width': 1.5,
                        'line-dasharray': [2, 1.5]
                    }
                });

                // 4. Master Grounds Circle Layer
                map.addLayer({
                    id: 'layer-grounds-circle',
                    type: 'circle',
                    source: 'source-grounds',
                    layout: { visibility: 'visible' },
                    paint: {
                        'circle-radius': 7.5,
                        'circle-color': '#a855f7',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 1.5,
                        'circle-opacity': 0.9
                    }
                });

                // 5. Logbooks Circle Layer
                map.addLayer({
                    id: 'layer-logbooks-circle',
                    type: 'circle',
                    source: 'source-logbooks',
                    layout: { visibility: 'visible' },
                    paint: {
                        'circle-radius': 5.5,
                        'circle-color': '#f59e0b',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 1.5,
                        'circle-opacity': 0.85
                    }
                });

                // 6. Vessels Homeport Circle Layer
                map.addLayer({
                    id: 'layer-vessels-circle',
                    type: 'circle',
                    source: 'source-vessels',
                    layout: { visibility: 'visible' },
                    paint: {
                        'circle-radius': 6.5,
                        'circle-color': '#10b981',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 1.5,
                        'circle-opacity': 0.85
                    }
                });

                // 7. Ports / Landing Sites Circle Layer
                map.addLayer({
                    id: 'layer-ports-circle',
                    type: 'circle',
                    source: 'source-ports',
                    layout: { visibility: 'visible' },
                    paint: {
                        'circle-radius': 8.5,
                        'circle-color': '#0284c7',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 2,
                        'circle-opacity': 0.9
                    }
                });

                // 8. Fishing Effort Circle Layer
                map.addLayer({
                    id: 'layer-efforts-circle',
                    type: 'circle',
                    source: 'source-efforts',
                    layout: { visibility: 'visible' },
                    paint: {
                        'circle-radius': 7,
                        'circle-color': '#ef4444',
                        'circle-stroke-color': '#ffffff',
                        'circle-stroke-width': 1.5,
                        'circle-opacity': 0.9
                    }
                });

                // Load initial data
                loadGisSpatialData();
            });

            // Popups & Interactions per Layer
            // A. Fishing Effort Popup
            map.on('click', 'layer-efforts-circle', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                let speciesHtml = '';
                if (p.species_summary) {
                    speciesHtml = `<div style="font-size: 10px; background: rgba(255,255,255,0.06); padding: 4px 6px; border-radius: 4px; margin-top: 5px; color: #cbd5e1;"><strong>Komposisi Spesies:</strong> ${p.species_summary}</div>`;
                }

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #ef4444; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #f87171; font-size: 12px;">🔴 Fishing Effort (Setting)</strong>
                            <span style="font-size: 9px; background: rgba(239,68,68,0.2); color: #fca5a5; padding: 1px 4px; border-radius: 4px; font-weight: 700;">${p.wpp_code || '-'}</span>
                        </div>
                        <div><strong>Alat Tangkap:</strong> ${p.gear || 'Alat Tangkap'}</div>
                        <div><strong>Wilayah (WPP):</strong> ${p.wpp_name || '-'}</div>
                        <div><strong>Jumlah Setting:</strong> <span style="color: #38bdf8; font-weight: 700;">${p.effort_count || 1}</span> kali operasi</div>
                        <div><strong>Total Durasi:</strong> ${p.total_hours || 0} jam</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 5px;">
                            ℹ️ Data agregat. Identitas kapal/trip tidak ditampilkan.
                        </div>
                    </div>
                `;
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });


            // B. Ports Popup
            map.on('click', 'layer-ports-circle', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #0284c7; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #38bdf8; font-size: 12px;">⚓ ${p.type} ${p.name}</strong>
                            <div style="font-size: 10px; color: #94a3b8;">${p.regency}, ${p.province}</div>
                        </div>
                        <div><strong>Kode Pelabuhan:</strong> ${p.code || '-'}</div>
                        <div><strong>Kapal Terdaftar (Homeport):</strong> ${p.vessels_count || 0} unit</div>
                        <div><strong>Aktivitas Pendaratan:</strong> ${p.landings_count || 0} kali</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 5px; font-family: monospace;">
                            Koordinat: ${Number(p.lat).toFixed(4)}, ${Number(p.lng).toFixed(4)}
                        </div>
                    </div>
                `;
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });

            // C. Vessels Homeport Popup
            map.on('click', 'layer-vessels-circle', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #10b981; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #34d399; font-size: 12px;">🚢 ${p.name}</strong>
                            <span style="font-size: 9px; background: rgba(16,185,129,0.2); color: #6ee7b7; padding: 1px 4px; border-radius: 4px;">${p.gt}</span>
                        </div>
                        <div><strong>Tanda Selar / Registrasi:</strong> ${p.registration || '-'}</div>
                        <div><strong>Tipe Kapal:</strong> ${p.type || '-'}</div>
                        <div><strong>Alat Tangkap Utama:</strong> ${p.gear || '-'}</div>
                        <div><strong>Pangkalan / Homeport:</strong> ${p.homeport || '-'} (${p.regency || '-'})</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 5px; font-family: monospace;">
                            Pangkalan: ${Number(p.lat).toFixed(4)}, ${Number(p.lng).toFixed(4)}
                        </div>
                    </div>
                `;
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });

            // D. Logbooks Historis Popup
            map.on('click', 'layer-logbooks-circle', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #f59e0b; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #fbbf24; font-size: 12px;">📋 Logbook Historis</strong>
                            <div style="font-size: 10px; color: #94a3b8;">Tanggal: ${p.date || '-'}</div>
                        </div>
                        <div><strong>Trip:</strong> ${p.trip_code || '-'}</div>
                        <div><strong>Kapal:</strong> ${p.vessel || '-'}</div>
                        <div><strong>Waktu Log:</strong> ${p.time || '-'}</div>
                        <div><strong>Cuaca:</strong> ${p.weather || '-'} (Gelombang ${p.wave_height || 0}m)</div>
                        <div><strong>Aktivitas:</strong> ${p.activity || '-'}</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 5px; font-family: monospace;">
                            GPS Log: ${Number(p.lat).toFixed(4)}, ${Number(p.lng).toFixed(4)}
                        </div>
                    </div>
                `;
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });

            // E. Master Grounds Popup
            map.on('click', 'layer-grounds-circle', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const coords = e.features[0].geometry.coordinates.slice();

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #a855f7; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #c084fc; font-size: 12px;">🟣 Master Fishing Ground</strong>
                            <div style="font-size: 13px; font-weight: 800; color: #ffffff;">${p.name}</div>
                        </div>
                        <div><strong>Kode:</strong> ${p.code || '-'}</div>
                        <div><strong>Wilayah:</strong> ${p.wpp_info || '-'}</div>
                        <div><strong>Deskripsi:</strong> ${p.description || '-'}</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 5px; font-family: monospace;">
                            Koordinat: ${Number(p.lat).toFixed(4)}, ${Number(p.lng).toFixed(4)}
                        </div>
                    </div>
                `;
                popup.setLngLat(coords).setHTML(html).addTo(map);
            });

            // F. WPP 571 & 572 Click Popups
            map.on('click', 'layer-wpp-571-fill', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #06b6d4; padding-bottom: 4px; margin-bottom: 4px;">
                            <strong style="color: #22d3ee; font-size: 12px;">🌐 ${p.name}</strong>
                        </div>
                        <div>${p.description}</div>
                    </div>
                `;
                popup.setLngLat(e.lngLat).setHTML(html).addTo(map);
            });

            map.on('click', 'layer-wpp-572-fill', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9;">
                        <div style="border-bottom: 2px solid #3b82f6; padding-bottom: 4px; margin-bottom: 4px;">
                            <strong style="color: #60a5fa; font-size: 12px;">🌐 ${p.name}</strong>
                        </div>
                        <div>${p.description}</div>
                    </div>
                `;
                popup.setLngLat(e.lngLat).setHTML(html).addTo(map);
            });

            // G. RZWP3K Aceh Click Popup
            map.on('click', 'layer-rzwp3k-fill', function(e) {
                if (!e.features.length) return;
                const p = e.features[0].properties;
                const areaText = (p.area_ha && Number(p.area_ha) > 0)
                    ? (Number(p.area_ha).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' ha')
                    : 'Tidak tersedia';
                const subzoneText = p.subzone_type || 'Tidak tersedia';
                const statusText = p.status || 'legal_active';
                const sourceText = p.source || 'Dinas Kelautan dan Perikanan Aceh';
                const legalBasisText = p.legal_basis || 'Qanun Aceh Nomor 1 Tahun 2020';

                const html = `
                    <div style="font-size: 11px; line-height: 1.45; color: #f1f5f9; min-width: 220px;">
                        <div style="border-bottom: 2px solid #10b981; padding-bottom: 4px; margin-bottom: 6px;">
                            <strong style="color: #34d399; font-size: 12px;">🗺️ RZWP3K ACEH</strong>
                            <div style="font-size: 13px; font-weight: 800; color: #ffffff;">${p.name || 'Zona RZWP3K'}</div>
                        </div>
                        <div><strong>Kode:</strong> <span style="font-family: monospace; color: #6ee7b7;">${p.code || '-'}</span></div>
                        <div><strong>Kawasan:</strong> <span style="font-weight: 700; color: #a7f3d0;">${p.zone_type || '-'}</span></div>
                        <div><strong>Subzona:</strong> ${subzoneText}</div>
                        <div><strong>Luas:</strong> ${areaText}</div>
                        <div><strong>Status:</strong> <span style="font-size: 9px; padding: 1px 4px; border-radius: 4px; background: rgba(16,185,129,0.2); color: #a7f3d0;">${statusText}</span></div>
                        <div><strong>Sumber:</strong> ${sourceText}</div>
                        <div><strong>Dasar Hukum:</strong> ${legalBasisText}</div>
                        <div style="font-size: 9px; color: #94a3b8; margin-top: 6px; padding-top: 4px; border-top: 1px dashed rgba(255,255,255,0.15); line-height: 1.3;">
                            <em>Informasi ini merupakan informasi zonasi spasial. Tampilan spasial tidak dengan sendirinya menentukan status legalitas suatu aktivitas atau kapal.</em>
                        </div>
                    </div>
                `;
                popup.setLngLat(e.lngLat).setHTML(html).addTo(map);
            });

            // Cursor Pointer Handlers on Hover
            const interactiveLayers = [
                'layer-efforts-circle',
                'layer-ports-circle',
                'layer-vessels-circle',
                'layer-logbooks-circle',
                'layer-grounds-circle',
                'layer-wpp-571-fill',
                'layer-wpp-572-fill',
                'layer-rzwp3k-fill'
            ];
            interactiveLayers.forEach(layerId => {
                map.on('mouseenter', layerId, () => { map.getCanvas().style.cursor = 'pointer'; });
                map.on('mouseleave', layerId, () => { map.getCanvas().style.cursor = ''; });
            });

            // Layer Toggle Click Handlers
            $('.layer-toggle-btn').on('click', function() {
                const layerKey = $(this).data('layer');
                const targetLayers = layerMapping[layerKey];
                if (!targetLayers) return;

                const isCurrentlyActive = $(this).hasClass('active');
                $(this).attr('aria-pressed', (!isCurrentlyActive).toString());

                targetLayers.forEach(layerId => {
                    if (map.getLayer(layerId)) {
                        map.setLayoutProperty(layerId, 'visibility', isCurrentlyActive ? 'none' : 'visible');
                    }
                });

                if (isCurrentlyActive) {
                    $(this).removeClass('active bg-rose-500/20 bg-sky-500/20 bg-emerald-500/20 bg-amber-500/20 bg-purple-500/20 bg-cyan-500/20 bg-blue-500/20 text-rose-300 text-sky-300 text-emerald-300 text-amber-300 text-purple-300 text-cyan-300 text-blue-300 border-rose-500/50 border-sky-500/50 border-emerald-500/50 border-amber-500/50 border-purple-500/50 border-cyan-500/50 border-blue-500/50')
                           .addClass('bg-slate-800 text-slate-400 border-slate-700 opacity-60');
                    if (layerKey === 'rzwp3k') {
                        $('#rzwp3kEmptyNotice').addClass('hidden');
                    }
                } else {
                    $(this).addClass('active').removeClass('bg-slate-800 text-slate-400 border-slate-700 opacity-60');
                    if (layerKey === 'efforts') $(this).addClass('bg-rose-500/20 text-rose-300 border-rose-500/50');
                    if (layerKey === 'ports') $(this).addClass('bg-sky-500/20 text-sky-300 border-sky-500/50');
                    if (layerKey === 'vessels') $(this).addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/50');
                    if (layerKey === 'logbooks') $(this).addClass('bg-amber-500/20 text-amber-300 border-amber-500/50');
                    if (layerKey === 'grounds') $(this).addClass('bg-purple-500/20 text-purple-300 border-purple-500/50');
                    if (layerKey === 'wpp571') $(this).addClass('bg-cyan-500/20 text-cyan-300 border-cyan-500/50');
                    if (layerKey === 'wpp572') $(this).addClass('bg-blue-500/20 text-blue-300 border-blue-500/50');
                    if (layerKey === 'rzwp3k') {
                        $(this).addClass('bg-emerald-500/20 text-emerald-300 border-emerald-500/50');
                        const rzwp3kCount = parseInt($('#badgeCountRzwp3k').text() || '0', 10);
                        if (rzwp3kCount === 0) {
                            $('#rzwp3kEmptyNotice').removeClass('hidden');
                        }
                    }
                }
            });

            // Close RZWP3K Notice button
            $('#btnCloseRzwp3kNotice').on('click', function() {
                $('#rzwp3kEmptyNotice').addClass('hidden');
            });

            // RZWP3K Zone Type Filter change handler
            $('#filterRzwp3kZoneType').on('change', function() {
                const selectedType = $(this).val();
                const url = '{{ route("api.rzwp3k.zones") }}' + (selectedType ? ('?zone_type=' + encodeURIComponent(selectedType)) : '');
                fetch(url, {
                    headers: { 'Accept': 'application/geo+json, application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(res => res.json())
                .then(rzData => {
                    const rzFeatures = (rzData && rzData.features) ? rzData.features : [];
                    $('#badgeCountRzwp3k').text(rzFeatures.length);
                    if (map.getSource('source-rzwp3k')) {
                        map.getSource('source-rzwp3k').setData(rzData);
                    }
                })
                .catch(e => {
                    console.warn('RZWP3K filter change notice:', e);
                });
            });

            // Master Fishing Ground Drawer Toggle

            $('#btnToggleFgPanel').on('click', function() {
                $('#panelMasterFishingGround').slideToggle(200);
            });

            // Reset View Button
            $('#btnResetGisView').on('click', function() {
                if (cachedBounds) {
                    map.fitBounds(cachedBounds, { padding: 45, duration: 1000 });
                } else {
                    map.flyTo({ center: [95.32, 5.55], zoom: 6.8, duration: 1000 });
                }
            });

            // Retry Button
            $('#btnRetryGis').on('click', function() {
                loadGisSpatialData();
            });

            // Auto Resizing & Responsiveness Handlers
            setTimeout(function() { map.resize(); }, 300);
            setTimeout(function() { map.resize(); }, 800);
            window.addEventListener('resize', function() { map.resize(); });
            window.addEventListener('statistik-tab-activated', function() {
                setTimeout(function() { map.resize(); }, 100);
                setTimeout(function() { map.resize(); }, 350);
            });

            if (window.ResizeObserver) {
                const resizeObserver = new ResizeObserver(function() {
                    map.resize();
                });
                const mapWrapper = document.getElementById('gisMapWrapper');
                if (mapWrapper) resizeObserver.observe(mapWrapper);
            }
        }
    });
</script>
