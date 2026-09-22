<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🐠</span>
            <span>{{ __('Master Data Jenis Ikan (FAO ASFIS)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showStatusModal: false,
        statusTarget: { id: null, name: '', is_active: false, actionUrl: '' },
        openStatusModal(item, actionUrl) {
            this.statusTarget = {
                id: item.id,
                name: item.name,
                is_active: item.is_active,
                actionUrl: actionUrl
            };
            this.showStatusModal = true;
        },

        {{-- Bulk Action State --}}
        selectedIds: [],
        showBulkModal: false,
        bulkAction: '',

        get selectedCount() {
            return this.selectedIds.length;
        },

        toggleSelectAll(event) {
            if (event.target.checked) {
                this.selectedIds = {{ Js::from($species->pluck('id')) }};
            } else {
                this.selectedIds = [];
            }
        },

        get isAllSelected() {
            return this.selectedIds.length === {{ $species->count() }} && this.selectedIds.length > 0;
        },

        get isIndeterminate() {
            return this.selectedIds.length > 0 && this.selectedIds.length < {{ $species->count() }};
        },

        openBulkModal(action) {
            if (this.selectedIds.length === 0) return;
            this.bulkAction = action;
            this.showBulkModal = true;
        },

        submitBulkAction() {
            this.$refs.bulkForm.submit();
        }
    }">
        {{-- Intro Header Banner --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🐟</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Katalog Spesies Sumber Daya Ikan (SDI)') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Daftar Jenis Ikan (Standar FAO ASFIS)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Referensi master berbasis ASFIS 2026. Data FAO bersifat read-only. Data lokal Indonesia/Aceh dapat disesuaikan tanpa merusak struktur utama taksonomi internasional.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    {{-- <a href="{{ route('master.species.create') ?? '#' }}" onclick="alert('Untuk menambah ASFIS, gunakan Import ASFIS. Form ini untuk data custom lokal (TBD).'); return false;"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Spesies Custom') }}</span>
                    </a> --}}
                    
                    <a href="{{ route('master.species.import.view') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <span>{{ __('Import / Update ASFIS') }}</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.species.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari FAO Code / Scientific Name / English Name / Family') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                <div class="w-full sm:w-auto flex flex-wrap gap-2">
                    <select name="status" class="w-32 py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                    </select>

                    <select name="fishstat" class="w-28 py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua') }}</option>
                        <option value="yes" {{ request('fishstat') === 'yes' ? 'selected' : '' }}>{{ __('FishStat YES') }}</option>
                        <option value="no" {{ request('fishstat') === 'no' ? 'selected' : '' }}>{{ __('FishStat NO') }}</option>
                    </select>

                    <select name="isscaap" class="w-36 py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="">{{ __('Semua ISSCAAP') }}</option>
                        @foreach($isscaapList as $isscaap_code)
                            <option value="{{ $isscaap_code }}" {{ request('isscaap') == $isscaap_code ? 'selected' : '' }}>{{ $isscaap_code }}</option>
                        @endforeach
                    </select>

                    <select name="per_page" class="w-20 py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                        <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
                        <option value="500" {{ $perPage == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Cari') }}
                    </button>
                    <a href="{{ route('master.species.index') }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                        {{ __('Reset') }}
                    </a>
                </div>
            </form>
        </div>

        {{-- Bulk Action Bar --}}
        <div x-show="selectedCount > 0" x-transition class="bg-ocean-50 border border-ocean-200 rounded-xl p-3 mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-2 text-sm text-ocean-800 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <span x-text="selectedCount + ' data dipilih'"></span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button"
                        @click="openBulkModal('activate')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ __('Aktifkan') }}
                </button>
                <button type="button"
                        @click="openBulkModal('deactivate')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    {{ __('Nonaktifkan') }}
                </button>
            </div>
        </div>

        <div class="mb-3 text-sm text-gray-500">
            @if($species->total() > 0)
                Menampilkan {{ $species->firstItem() }}–{{ $species->lastItem() }} dari {{ number_format($species->total(), 0, ',', '.') }} species
            @else
                Menampilkan 0 species
            @endif
        </div>

        {{-- Species Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-12 text-center">{{ __('No') }}</th>
                            <th class="py-3 px-4 w-12 text-center">
                                <input type="checkbox"
                                       @change="toggleSelectAll($event)"
                                       :checked="isAllSelected"
                                       :indeterminate="isIndeterminate"
                                       class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500 h-4 w-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4 w-28">{{ __('FAO Code') }}</th>
                            <th class="py-3 px-4">{{ __('Scientific Name') }}</th>
                            <th class="py-3 px-4">{{ __('English Name') }}</th>
                            <th class="py-3 px-4">{{ __('Nama Indonesia / Aceh') }}</th>
                            <th class="py-3 px-4">{{ __('Family') }}</th>
                            <th class="py-3 px-4">{{ __('ISSCAAP') }}</th>
                            <th class="py-3 px-4">{{ __('Status') }}</th>
                            <th class="py-3 px-4 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($species as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors" :class="selectedIds.includes({{ $item->id }}) ? 'bg-ocean-50/60' : ''">
                                <td class="py-3.5 px-4 text-center text-xs text-gray-400 font-mono">
                                    {{ $species->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <input type="checkbox"
                                           value="{{ $item->id }}"
                                           x-model.number="selectedIds"
                                           class="rounded border-gray-300 text-ocean-600 focus:ring-ocean-500 h-4 w-4 cursor-pointer">
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold px-2.5 py-1 rounded-md bg-ocean-50 text-ocean-700 border border-ocean-200 text-xs tracking-wider">
                                        {{ $item->fao_code ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-gray-900 italic font-serif">{{ $item->scientific_name }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-gray-700">
                                    {{ $item->english_name ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($item->local_name_id)
                                        <div class="font-medium text-gray-900 flex items-center gap-1">
                                            {{ $item->local_name_id }}
                                        </div>
                                    @endif
                                    @if($item->local_name_aceh)
                                        <div class="text-xs font-medium text-amber-800 flex items-center gap-1 mt-0.5">
                                            {{ $item->local_name_aceh }}
                                        </div>
                                    @endif
                                    @if(!$item->local_name_id && !$item->local_name_aceh)
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-gray-900 text-xs">{{ $item->family ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-gray-900 text-xs">ISSCAAP: {{ $item->isscaap_code ?? '—' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if($item->is_active)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-700">ACTIVE</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 text-gray-700">INACTIVE</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- View Button --}}
                                        <a href="{{ route('master.species.show', $item) }}"
                                                class="px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 font-medium text-xs rounded-lg transition-colors border border-blue-200"
                                                title="{{ __('Lihat Detail') }}">
                                            {{ __('Lihat Detail') }}
                                        </a>
                                        {{-- Toggle Status Button --}}
                                        <button type="button" 
                                                @click="openStatusModal({ id: {{ $item->id }}, name: @js($item->local_name_id ?? $item->scientific_name), is_active: {{ $item->is_active ? 'true' : 'false' }} }, '{{ route('master.species.toggle-status', $item) }}')"
                                                class="px-3 py-1.5 font-medium text-xs rounded-lg transition-colors border {{ $item->is_active ? 'bg-amber-50 text-amber-600 hover:bg-amber-100 hover:text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100 hover:text-emerald-700 border-emerald-200' }}"
                                                title="{{ $item->is_active ? __('Nonaktifkan') : __('Aktifkan') }}">
                                            {{ $item->is_active ? __('Nonaktifkan') : __('Aktifkan') }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">🐠</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ditemukan species yang sesuai dengan pencarian/filter.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($species->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $species->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Toggle Status (single item — existing) --}}
        <div x-show="showStatusModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showStatusModal" x-transition.opacity class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showStatusModal" x-transition class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" :action="statusTarget.actionUrl">
                        @csrf
                        @method('PATCH')
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" :class="statusTarget.is_active ? 'bg-amber-100' : 'bg-emerald-100'">
                                    <svg class="h-6 w-6" :class="statusTarget.is_active ? 'text-amber-600' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="statusTarget.is_active ? 'Nonaktifkan Species' : 'Aktifkan Species'"></h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Apakah Anda yakin ingin <span x-text="statusTarget.is_active ? 'menonaktifkan' : 'mengaktifkan'"></span> <strong x-text="statusTarget.name"></strong>?
                                            <br><br>
                                            <span x-text="statusTarget.is_active ? 'Species yang dinonaktifkan tidak akan muncul dalam pilihan master data transaksi baru, namun data lama tetap valid.' : 'Species ini akan dapat digunakan kembali pada transaksi.'"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm" :class="statusTarget.is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'" x-text="statusTarget.is_active ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'">
                            </button>
                            <button type="button" @click="showStatusModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Bulk Action --}}
        <div x-show="showBulkModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showBulkModal" x-transition.opacity class="fixed inset-0 transition-opacity" aria-hidden="true">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div x-show="showBulkModal" x-transition class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form method="POST" action="{{ route('master.species.bulk-update-status') }}" x-ref="bulkForm">
                        @csrf
                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <input type="hidden" name="action" :value="bulkAction">

                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10" :class="bulkAction === 'deactivate' ? 'bg-amber-100' : 'bg-emerald-100'">
                                    <svg class="h-6 w-6" :class="bulkAction === 'deactivate' ? 'text-amber-600' : 'text-emerald-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                                        <span x-text="bulkAction === 'deactivate' ? 'Nonaktifkan' : 'Aktifkan'"></span>
                                        <span x-text="selectedCount"></span> jenis ikan?
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500" x-show="bulkAction === 'deactivate'">
                                            Data tidak akan dihapus. Species yang dinonaktifkan tidak akan muncul dalam pilihan master data transaksi baru, namun data historis tetap dipertahankan.
                                        </p>
                                        <p class="text-sm text-gray-500" x-show="bulkAction === 'activate'">
                                            Species yang diaktifkan akan dapat digunakan kembali pada transaksi baru.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm"
                                    :class="bulkAction === 'deactivate' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'">
                                <span x-text="bulkAction === 'deactivate' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                            </button>
                            <button type="button" @click="showBulkModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
