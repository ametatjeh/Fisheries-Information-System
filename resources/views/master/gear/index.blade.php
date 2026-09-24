<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🎣</span>
            <span>{{ __('Master Data Alat Tangkap') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showStatusModal: false,
        showBulkModal: false,
        bulkAction: 'activate',
        statusTarget: { id: null, name: '', is_active: false, actionUrl: '' },
        editItem: {},
        editAction: '',
        selectedIds: [],
        currentPageIds: {{ json_encode($gears->pluck('id')->values()->all()) }},
        get selectedCount() {
            return this.selectedIds.length;
        },
        get isAllSelected() {
            return this.currentPageIds.length > 0 && this.currentPageIds.every(id => this.selectedIds.includes(id));
        },
        get isIndeterminate() {
            return this.selectedIds.length > 0 && !this.isAllSelected;
        },
        toggleSelectAll(e) {
            if (e.target.checked) {
                this.selectedIds = [...new Set([...this.selectedIds, ...this.currentPageIds])];
            } else {
                this.selectedIds = this.selectedIds.filter(id => !this.currentPageIds.includes(id));
            }
        },
        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(i => i !== id);
            } else {
                this.selectedIds.push(id);
            }
        },
        openBulkModal(action) {
            if (this.selectedIds.length === 0) return;
            this.bulkAction = action;
            this.showBulkModal = true;
        },
        openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editAction = actionUrl;
            this.showEditModal = true;
        },
        openStatusModal(item, actionUrl) {
            this.statusTarget = {
                id: item.id,
                name: item.name + (item.code ? ' (' + item.code + ')' : ''),
                is_active: item.is_active,
                actionUrl: actionUrl
            };
            this.showStatusModal = true;
        }
    }">
        {{-- Intro Header Banner --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🎣</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>⚓</span>
                        <span>{{ __('Klasifikasi Alat Penangkapan Ikan (FAO ISSCFG & Lokal)') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Katalog Alat Tangkap & Metode Penangkapan') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Mengacu pada standar FAO/CWP International Standard Statistical Classification of Fishing Gear (ISSCFG Annex M) serta varian operasional lokal nelayan Aceh (Pukat Langgar, Pukat Darat, Rawai Tuna, Pancing Ulur, Bubu).') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Alat Tangkap') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            <a href="{{ route('master.gears.index') }}"
               class="p-3.5 rounded-xl border transition-all {{ empty($category) && empty($source) && empty($status) ? 'bg-ocean-50/80 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="text-xl font-bold text-gray-800">{{ $counts['total'] }}</div>
                <div class="text-xs font-medium text-gray-500 mt-0.5">{{ __('Semua Alat Tangkap') }}</div>
            </a>

            <a href="{{ route('master.gears.index', array_merge(request()->except(['page', 'source']), ['source' => 'LOCAL'])) }}"
               class="p-3.5 rounded-xl border transition-all {{ request('source') === 'LOCAL' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['local'] }}</span>
                    <span class="text-base">📍</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-0.5">{{ __('Operasional Lokal') }}</div>
            </a>

            <a href="{{ route('master.gears.index', array_merge(request()->except(['page', 'source']), ['source' => 'FAO_ISSCFG'])) }}"
               class="p-3.5 rounded-xl border transition-all {{ request('source') === 'FAO_ISSCFG' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-blue-700">{{ $counts['fao'] }}</span>
                    <span class="text-base">🌐</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-0.5">{{ __('Standar FAO ISSCFG') }}</div>
            </a>

            <a href="{{ route('master.gears.index', array_merge(request()->except(['page', 'status']), ['status' => 'active'])) }}"
               class="p-3.5 rounded-xl border transition-all {{ request('status') === 'active' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-emerald-700">{{ $counts['active'] }}</span>
                    <span class="text-base">✅</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-0.5">{{ __('Status Aktif') }}</div>
            </a>

            <a href="{{ route('master.gears.index', array_merge(request()->except(['page', 'status']), ['status' => 'inactive'])) }}"
               class="p-3.5 rounded-xl border transition-all {{ request('status') === 'inactive' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-amber-700">{{ $counts['inactive'] }}</span>
                    <span class="text-base">⏸️</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-0.5">{{ __('Status Nonaktif') }}</div>
            </a>

            <a href="{{ route('master.gears.index', ['category' => 'jaring_lingkar']) }}"
               class="p-3.5 rounded-xl border transition-all {{ $category === 'jaring_lingkar' ? 'bg-cyan-50 border-cyan-300 ring-2 ring-cyan-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xl font-bold text-cyan-700">{{ $counts['jaring_lingkar'] }}</span>
                    <span class="text-base">⭕</span>
                </div>
                <div class="text-xs font-medium text-gray-600 mt-0.5">{{ __('Jaring Lingkar') }}</div>
            </a>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.gears.index') }}" class="flex flex-col lg:flex-row gap-3 items-stretch lg:items-center">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari kode lokal (PS-01), ISSCFG (01.1), singkatan (PS), nama...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5">
                    {{-- Filter Kategori --}}
                    <select name="category" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Kategori') }}</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ $category === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Filter Source --}}
                    <select name="source" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Source') }}</option>
                        <option value="LOCAL" {{ request('source') === 'LOCAL' ? 'selected' : '' }}>{{ __('Lokal') }}</option>
                        <option value="FAO_ISSCFG" {{ request('source') === 'FAO_ISSCFG' ? 'selected' : '' }}>{{ __('FAO/ISSCFG') }}</option>
                    </select>

                    {{-- Filter Level --}}
                    <select name="level" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Level') }}</option>
                        <option value="1" {{ request('level') == '1' ? 'selected' : '' }}>{{ __('Level 1 — Major Group') }}</option>
                        <option value="2" {{ request('level') == '2' ? 'selected' : '' }}>{{ __('Level 2 — Main Gear') }}</option>
                        <option value="3" {{ request('level') == '3' ? 'selected' : '' }}>{{ __('Level 3 — Detailed Gear') }}</option>
                        <option value="4" {{ request('level') == '4' ? 'selected' : '' }}>{{ __('Level 4 — Lokal') }}</option>
                    </select>

                    {{-- Filter Status --}}
                    <select name="status" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                    </select>

                    {{-- Pilihan Per Page --}}
                    <select name="per_page" class="py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / hal</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / hal</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / hal</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / hal</option>
                        <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250 / hal</option>
                        <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500 / hal</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors cursor-pointer">
                        {{ __('Filter') }}
                    </button>
                    @if($search || $category || request('status') || request('source') || request('level') || request('per_page'))
                        <a href="{{ route('master.gears.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bulk Action Bar --}}
        <div x-show="selectedCount > 0" x-transition class="bg-ocean-50 border border-ocean-200 rounded-xl p-3 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-xs">
            <div class="flex items-center gap-2 text-sm text-ocean-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span x-text="selectedCount + ' data alat tangkap dipilih'"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="openBulkModal('activate')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Aktifkan') }}
                </button>
                <button type="button"
                        @click="openBulkModal('deactivate')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    {{ __('Nonaktifkan') }}
                </button>
            </div>
        </div>

        {{-- Info Baris Data --}}
        <div class="mb-3 text-sm text-gray-500 flex items-center justify-between">
            <div>
                @if($gears->total() > 0)
                    {{ __('Menampilkan :from–:to dari :total alat tangkap', [
                        'from' => $gears->firstItem(),
                        'to' => $gears->lastItem(),
                        'total' => number_format($gears->total(), 0, ',', '.')
                    ]) }}
                @else
                    {{ __('Menampilkan 0 data alat tangkap') }}
                @endif
            </div>
        </div>

        {{-- Gears Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[980px]">
                    <thead>
                        <tr class="bg-ocean-900 border-b border-ocean-950 text-xs font-semibold text-white uppercase tracking-wider">
                            <th class="py-3.5 px-3 w-10 text-center text-white">#</th>
                            <th class="py-3.5 px-3 w-10 text-center">
                                 <input type="checkbox"
                                        @change="toggleSelectAll($event)"
                                        :checked="isAllSelected"
                                        :indeterminate="isIndeterminate"
                                        class="rounded border-ocean-600 bg-ocean-800 text-ocean-400 focus:ring-ocean-300 h-4 w-4 cursor-pointer">
                            </th>
                            <th class="py-3.5 px-3 w-24 text-white">{{ __('Kode') }}</th>
                            <th class="py-3.5 px-3 w-20 text-center text-white">{{ __('ISSCFG') }}</th>
                            <th class="py-3.5 px-3 w-16 text-center text-white">{{ __('Abbr') }}</th>
                            <th class="py-3.5 px-3 w-20 text-center text-white">{{ __('Level') }}</th>
                            <th class="py-3.5 px-4 text-white">{{ __('Nama Alat Tangkap') }}</th>
                            <th class="py-3.5 px-3 text-white">{{ __('Nama Lokal') }}</th>
                            <th class="py-3.5 px-3 text-white">{{ __('Kategori') }}</th>
                            <th class="py-3.5 px-3 text-center w-24 text-white">{{ __('Source') }}</th>
                            <th class="py-3.5 px-3 text-center w-20 text-white">{{ __('Kapal') }}</th>
                            <th class="py-3.5 px-3 text-center w-24 text-white">{{ __('Status') }}</th>
                            <th class="py-3.5 px-3 text-right w-24 text-white">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($gears as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors {{ $item->source === 'LOCAL' ? 'bg-emerald-50/15' : '' }}">
                                {{-- Nomor Urut Paginated --}}
                                <td class="py-3.5 px-3 text-center text-gray-400 font-mono text-xs">
                                    {{ $gears->firstItem() + $index }}
                                </td>

                                {{-- Checkbox --}}
                                <td class="py-3.5 px-3 text-center">
                                    <input type="checkbox"
                                           :checked="selectedIds.includes({{ $item->id }})"
                                           @change="toggleSelect({{ $item->id }})"
                                           class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500 h-4 w-4 cursor-pointer">
                                </td>

                                {{-- Kode --}}
                                <td class="py-3.5 px-3">
                                    <span class="font-mono font-bold px-2 py-0.5 rounded text-xs tracking-wider {{ $item->source === 'LOCAL' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-ocean-50 text-ocean-700 border border-ocean-200' }}">
                                        {{ $item->code }}
                                    </span>
                                </td>

                                {{-- ISSCFG Code --}}
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->isscfg_code)
                                        <span class="font-mono text-xs font-semibold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $item->isscfg_code }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Standard Abbreviation --}}
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->standard_abbreviation)
                                        <span class="font-mono text-xs font-bold text-gray-800">
                                            {{ $item->standard_abbreviation }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Level Badge --}}
                                <td class="py-3.5 px-3 text-center whitespace-nowrap">
                                    @php
                                        $levelBadge = match($item->level) {
                                            1 => 'bg-slate-100 text-slate-700 border-slate-300',
                                            2 => 'bg-sky-100 text-sky-800 border-sky-200',
                                            3 => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            4 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            default => 'bg-gray-100 text-gray-700 border-gray-200',
                                        };
                                        $levelLabel = match($item->level) {
                                            1 => 'Major Group',
                                            2 => 'Main Gear',
                                            3 => 'Detailed Gear',
                                            4 => 'Lokal',
                                            default => 'Tingkat ' . $item->level,
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $levelBadge }}" title="{{ $levelLabel }}">
                                        Level {{ $item->level }}
                                    </span>
                                </td>

                                {{-- Nama Alat Tangkap --}}
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-gray-900 leading-snug">{{ $item->name }}</div>
                                    @if($item->name_en && $item->name_en !== $item->name)
                                        <div class="text-xs text-gray-500 italic mt-0.5">{{ $item->name_en }}</div>
                                    @endif
                                    @if($item->parent)
                                        <div class="text-[11px] text-ocean-600 mt-0.5 flex items-center gap-1">
                                            <span>↳</span>
                                            <span>Induk: {{ $item->parent->name_en ?? $item->parent->name }} ({{ $item->parent->isscfg_code }})</span>
                                        </div>
                                    @endif
                                </td>

                                {{-- Nama Lokal Aceh --}}
                                <td class="py-3.5 px-3">
                                    @if($item->local_name)
                                        <span class="font-medium text-emerald-800 text-xs bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            {{ $item->local_name }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">-</span>
                                    @endif
                                </td>

                                {{-- Kategori --}}
                                <td class="py-3.5 px-3">
                                    @php
                                        $badgeStyles = match($item->category) {
                                            'jaring_lingkar' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'pancing'        => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'jaring_insang'  => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                            'jaring_tarik'   => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'perangkap'      => 'bg-rose-100 text-rose-800 border-rose-200',
                                            'jaring_angkat'  => 'bg-cyan-100 text-cyan-800 border-cyan-200',
                                            'jaring_hela'    => 'bg-indigo-100 text-indigo-800 border-indigo-200',
                                            'alat_jatuh'     => 'bg-teal-100 text-teal-800 border-teal-200',
                                            default          => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                        $catLabel = $categories[$item->category] ?? ucfirst($item->category);
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full border {{ $badgeStyles }}">
                                        {{ $catLabel }}
                                    </span>
                                </td>

                                {{-- Source --}}
                                <td class="py-3.5 px-3 text-center">
                                    @if($item->source === 'LOCAL')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                            <span>📍</span>
                                            <span>Lokal</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                            <span>🌐</span>
                                            <span>FAO</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Armada Kapal --}}
                                <td class="py-3.5 px-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ $item->vessels_count }}
                                    </span>
                                </td>

                                {{-- Active Status Toggle --}}
                                <td class="py-3.5 px-3 text-center">
                                    <button type="button"
                                            @click="openStatusModal({{ json_encode(['id' => $item->id, 'name' => $item->name, 'is_active' => (bool)$item->is_active]) }}, '{{ route('master.gears.toggle-status', $item->id) }}')"
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold transition-all cursor-pointer shadow-xs {{ $item->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200 border border-emerald-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200 border border-gray-200' }}"
                                            title="{{ __('Klik untuk ubah status aktif/nonaktif') }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $item->is_active ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                                        <span>{{ $item->is_active ? __('Aktif') : __('Nonaktif') }}</span>
                                    </button>
                                </td>

                                {{-- Aksi --}}
                                <td class="py-3.5 px-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ route('master.gears.update', $item->id) }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors cursor-pointer"
                                                title="{{ __('Ubah Alat Tangkap') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Button (Hanya jika belum digunakan oleh armada kapal/trip) --}}
                                        <form method="POST" action="{{ route('master.gears.destroy', $item->id) }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data alat tangkap :name?', ['name' => $item->name]) }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
                                                    title="{{ __('Hapus') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">🎣</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data alat tangkap ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba sesuaikan kata kunci pencarian atau reset filter Anda.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$gears" />
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH ALAT TANGKAP                                         --}}
        {{-- ================================================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             @keydown.escape.window="showCreateModal = false">
            <div @click.away="showCreateModal = false"
                 class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-gray-100 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🎣</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Alat Tangkap Baru') }}</h3>
                    </div>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.gears.store') }}" class="mt-4 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Unik Alat Tangkap') }} *</label>
                            <input type="text"
                                   name="code"
                                   required
                                   maxlength="20"
                                   placeholder="Contoh: PS-03, LL-03"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <span class="text-[11px] text-gray-400 mt-1 block">{{ __('Kode baku operasional kapal/trip.') }}</span>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kategori Kelompok') }} *</label>
                            <select name="category" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($categories as $k => $lbl)
                                    <option value="{{ $k }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Standar ISSCFG') }}</label>
                            <input type="text"
                                   name="isscfg_code"
                                   maxlength="20"
                                   placeholder="Contoh: 01.1, 09.32"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Singkatan Standar (Abbr)') }}</label>
                            <input type="text"
                                   name="standard_abbreviation"
                                   maxlength="10"
                                   placeholder="Contoh: PS, LLD, OTB"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Alat Penangkap Ikan') }} *</label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Contoh: Pukat Cincin Pelagis Besar"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Bahasa Inggris (FAO)') }}</label>
                            <input type="text"
                                   name="name_en"
                                   placeholder="Contoh: Purse seines"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lokal Aceh') }}</label>
                            <input type="text"
                                   name="local_name"
                                   placeholder="Contoh: Pukat Langgar"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Source / Sumber Data') }} *</label>
                            <select name="source" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="LOCAL" selected>{{ __('LOCAL (Operasional Lokal)') }}</option>
                                <option value="FAO_ISSCFG">{{ __('FAO_ISSCFG (Master Standar)') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Induk Standar FAO (Opsional/Legacy)') }}</label>
                            <select name="parent_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Tanpa Induk / Mandiri') }} --</option>
                                @foreach($faoParents as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->isscfg_code }} - {{ $p->name_en }} ({{ $p->standard_abbreviation ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Mapping Referensi ISSCFG (Opsional)') }}</label>
                        <select name="fao_isscfg_gear_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">-- {{ __('Belum Di-mapping') }} --</option>
                            @foreach($faoIsscfgReferences as $ref)
                                <option value="{{ $ref->id }}">
                                    {{ $ref->isscfg_code }} - {{ $ref->name_en }} {{ $ref->name_id ? ' / ' . $ref->name_id : '' }} ({{ $ref->standard_abbreviation ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Deskripsi Spesifikasi & Penggunaan') }}</label>
                        <textarea name="description"
                                  rows="3"
                                  placeholder="Contoh: Digunakan oleh armada kapal motor > 15 GT dengan sasaran ikan pelagis besar."
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500">
                            <span class="text-sm font-medium text-gray-700">{{ __('Aktifkan alat tangkap ini untuk pendataan armada kapal & trip') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                            {{ __('Simpan Alat Tangkap') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL UBAH ALAT TANGKAP                                           --}}
        {{-- ================================================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-2xl max-w-xl w-full p-6 shadow-xl border border-gray-100 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Ubah Data Alat Tangkap') }}</h3>
                    </div>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Unik Alat Tangkap') }} *</label>
                            <input type="text"
                                   name="code"
                                   x-model="editItem.code"
                                   required
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kategori Kelompok') }} *</label>
                            <select name="category" x-model="editItem.category" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($categories as $k => $lbl)
                                    <option value="{{ $k }}">{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Standar ISSCFG') }}</label>
                            <input type="text"
                                   name="isscfg_code"
                                   x-model="editItem.isscfg_code"
                                   maxlength="20"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Singkatan Standar (Abbr)') }}</label>
                            <input type="text"
                                   name="standard_abbreviation"
                                   x-model="editItem.standard_abbreviation"
                                   maxlength="10"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Alat Penangkap Ikan') }} *</label>
                        <input type="text"
                               name="name"
                               x-model="editItem.name"
                               required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Bahasa Inggris (FAO)') }}</label>
                            <input type="text"
                                   name="name_en"
                                   x-model="editItem.name_en"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lokal Aceh') }}</label>
                            <input type="text"
                                   name="local_name"
                                   x-model="editItem.local_name"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Source / Sumber Data') }} *</label>
                            <select name="source" x-model="editItem.source" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="LOCAL">{{ __('LOCAL (Operasional Lokal)') }}</option>
                                <option value="FAO_ISSCFG">{{ __('FAO_ISSCFG (Master Standar)') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Induk Standar FAO (Opsional/Legacy)') }}</label>
                            <select name="parent_id" x-model="editItem.parent_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Tanpa Induk / Mandiri') }} --</option>
                                @foreach($faoParents as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->isscfg_code }} - {{ $p->name_en }} ({{ $p->standard_abbreviation ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Mapping Referensi ISSCFG (Opsional)') }}</label>
                        <select name="fao_isscfg_gear_id" x-model="editItem.fao_isscfg_gear_id" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">-- {{ __('Belum Di-mapping') }} --</option>
                            @foreach($faoIsscfgReferences as $ref)
                                <option value="{{ $ref->id }}">
                                    {{ $ref->isscfg_code }} - {{ $ref->name_en }} {{ $ref->name_id ? ' / ' . $ref->name_id : '' }} ({{ $ref->standard_abbreviation ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Deskripsi Spesifikasi & Penggunaan') }}</label>
                        <textarea name="description"
                                  x-model="editItem.description"
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500"></textarea>
                    </div>

                    <div class="pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" x-model="editItem.is_active" class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500">
                            <span class="text-sm font-medium text-gray-700">{{ __('Aktifkan alat tangkap ini dalam pendataan') }}</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL KONFIRMASI STATUS (ACTIVE / INACTIVE POPUP)                 --}}
        {{-- ================================================================= --}}
        <div x-show="showStatusModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
             @keydown.escape.window="showStatusModal = false">
            <div @click.away="showStatusModal = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 text-center animate-in fade-in zoom-in-95 duration-150">

                {{-- Icon Badge --}}
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-3xl shadow-inner transition-colors"
                     :class="statusTarget.is_active ? 'bg-amber-50 text-amber-600 border border-amber-200' : 'bg-emerald-50 text-emerald-600 border border-emerald-200'">
                    <span x-show="statusTarget.is_active">⏸️</span>
                    <span x-show="!statusTarget.is_active">✅</span>
                </div>

                {{-- Title --}}
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    <span x-show="statusTarget.is_active">{{ __('Nonaktifkan Alat Tangkap?') }}</span>
                    <span x-show="!statusTarget.is_active">{{ __('Aktifkan Alat Tangkap?') }}</span>
                </h3>

                {{-- Target Gear Name --}}
                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    {{ __('Apakah Anda yakin ingin mengubah status operasional alat tangkap:') }}
                    <br>
                    <span class="font-bold text-gray-900 text-base mt-2 inline-block bg-gray-50 px-3.5 py-1.5 rounded-xl border border-gray-200" x-text="statusTarget.name"></span>
                </p>

                {{-- Information Box --}}
                <div class="p-3.5 rounded-xl text-xs text-left mb-6"
                     :class="statusTarget.is_active ? 'bg-amber-50/80 border border-amber-200 text-amber-800' : 'bg-emerald-50/80 border border-emerald-200 text-emerald-800'">
                    <div class="flex items-start gap-2.5">
                        <span class="text-base leading-none">💡</span>
                        <div class="leading-normal">
                            <span x-show="statusTarget.is_active">
                                {{ __('Alat tangkap yang berstatus Nonaktif tidak akan muncul dalam pilihan baru, tetapi seluruh data riwayat operasional tetap aman.') }}
                            </span>
                            <span x-show="!statusTarget.is_active">
                                {{ __('Alat tangkap akan kembali aktif dan dapat dipilih dalam pendataan armada kapal serta pencatatan trip penangkapan.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3">
                    <button type="button"
                            @click="showStatusModal = false"
                            class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                        {{ __('Batal') }}
                    </button>

                    <form method="POST" :action="statusTarget.actionUrl" class="inline w-full sm:w-auto">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="statusTarget.is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                            <span x-show="statusTarget.is_active">⏸️ {{ __('Ya, Nonaktifkan') }}</span>
                            <span x-show="!statusTarget.is_active">✅ {{ __('Ya, Aktifkan') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL KONFIRMASI BULK ACTION (AKTIFKAN / NONAKTIFKAN MASSAL)       --}}
        {{-- ================================================================= --}}
        <div x-show="showBulkModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-xs p-4"
             @keydown.escape.window="showBulkModal = false">
            <div @click.away="showBulkModal = false"
                 class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 text-center animate-in fade-in zoom-in-95 duration-150">

                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center text-3xl shadow-inner transition-colors"
                     :class="bulkAction === 'activate' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-amber-50 text-amber-600 border border-amber-200'">
                    <span x-show="bulkAction === 'activate'">✅</span>
                    <span x-show="bulkAction === 'deactivate'">⏸️</span>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    <span x-show="bulkAction === 'activate'">{{ __('Aktifkan :count Alat Tangkap?', ['count' => '']) }}</span>
                    <span x-show="bulkAction === 'deactivate'">{{ __('Nonaktifkan :count Alat Tangkap?', ['count' => '']) }}</span>
                    <span class="text-ocean-700" x-text="selectedCount + ' data'"></span>
                </h3>

                <p class="text-sm text-gray-600 mb-4 leading-relaxed">
                    <span x-show="bulkAction === 'activate'">
                        {{ __('Seluruh alat tangkap yang dipilih akan diaktifkan dan dapat digunakan dalam pendataan operasional baru.') }}
                    </span>
                    <span x-show="bulkAction === 'deactivate'">
                        {{ __('Alat tangkap yang dinonaktifkan tidak akan muncul pada input baru. Data riwayat kapal & trip yang telah menggunakannya TIDAK AKAN DIHAPUS.') }}
                    </span>
                </p>

                <form method="POST" action="{{ route('master.gears.bulk-status') }}">
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="action" :value="bulkAction">
                    <template x-for="id in selectedIds" :key="id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>

                    <div class="flex items-center justify-end gap-3 mt-6">
                        <button type="button"
                                @click="showBulkModal = false"
                                class="w-full sm:w-auto px-4 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
                                :class="bulkAction === 'activate' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700'">
                            <span x-show="bulkAction === 'activate'">✅ {{ __('Ya, Aktifkan') }}</span>
                            <span x-show="bulkAction === 'deactivate'">⏸️ {{ __('Ya, Nonaktifkan') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
