<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🌍</span>
            <span>{{ __('Master Data Wilayah') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        editItem: {},
        editAction: '',
        openEdit(item, actionUrl) {
            this.editItem = item;
            this.editAction = actionUrl;
            this.showEditModal = true;
        }
    }">
        {{-- Aceh Focus Banner & Intro --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-4 text-7xl opacity-10 pointer-events-none">⚓</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>📍</span>
                        <span>{{ __('Fokus Wilayah: Provinsi Aceh') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Hierarki Wilayah Administrasi & Perikanan') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Mengelola data 23 Kabupaten/Kota di Aceh beserta kecamatan dan gampong sentra perikanan tangkap, pangkalan pendaratan ikan (PPI/PPS), dan perairan pesisir.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>
                            @if($tab === 'kecamatan') {{ __('Tambah Kecamatan') }}
                            @elseif($tab === 'desa') {{ __('Tambah Gampong / Desa') }}
                            @else {{ __('Tambah Kab/Kota') }}
                            @endif
                        </span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <a href="{{ route('master.wilayah.index', ['tab' => 'kabupaten']) }}"
               class="p-4 rounded-xl border transition-all {{ $tab === 'kabupaten' ? 'bg-ocean-50/70 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-lg font-bold">🏢</div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xl font-bold text-gray-800">{{ number_format($counts['kabupaten']) }}</span>
                            <span class="text-xs text-emerald-600 font-semibold bg-emerald-50 px-1.5 py-0.5 rounded">Aceh</span>
                        </div>
                        <div class="text-xs font-medium text-gray-500">{{ __('Kabupaten / Kota di Aceh') }}</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('master.wilayah.index', ['tab' => 'kecamatan']) }}"
               class="p-4 rounded-xl border transition-all {{ $tab === 'kecamatan' ? 'bg-ocean-50/70 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-lg font-bold">🏘️</div>
                    <div>
                        <div class="text-xl font-bold text-gray-800">{{ number_format($counts['kecamatan']) }}</div>
                        <div class="text-xs font-medium text-gray-500">{{ __('Kecamatan di Aceh') }}</div>
                    </div>
                </div>
            </a>

            <a href="{{ route('master.wilayah.index', ['tab' => 'desa']) }}"
               class="p-4 rounded-xl border transition-all {{ $tab === 'desa' ? 'bg-ocean-50/70 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200' }}">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-lg font-bold">🏡</div>
                    <div>
                        <div class="text-xl font-bold text-gray-800">{{ number_format($counts['desa']) }}</div>
                        <div class="text-xs font-medium text-gray-500">{{ __('Gampong / Desa di Aceh') }}</div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Navigation Tabs --}}
        <div class="border-b border-gray-200 mb-6">
            <nav class="flex space-x-6">
                <a href="{{ route('master.wilayah.index', ['tab' => 'kabupaten']) }}"
                   class="py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 {{ $tab === 'kabupaten' ? 'border-ocean-600 text-ocean-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span>🏢 {{ __('Kabupaten / Kota') }}</span>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $tab === 'kabupaten' ? 'bg-ocean-100 text-ocean-700 font-semibold' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['kabupaten'] }}
                    </span>
                </a>
                <a href="{{ route('master.wilayah.index', ['tab' => 'kecamatan']) }}"
                   class="py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 {{ $tab === 'kecamatan' ? 'border-ocean-600 text-ocean-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span>🏘️ {{ __('Kecamatan') }}</span>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $tab === 'kecamatan' ? 'bg-ocean-100 text-ocean-700 font-semibold' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['kecamatan'] }}
                    </span>
                </a>
                <a href="{{ route('master.wilayah.index', ['tab' => 'desa']) }}"
                   class="py-3 px-1 border-b-2 font-medium text-sm flex items-center gap-2 {{ $tab === 'desa' ? 'border-ocean-600 text-ocean-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <span>🏡 {{ __('Gampong / Desa') }}</span>
                    <span class="px-2 py-0.5 text-xs rounded-full {{ $tab === 'desa' ? 'bg-ocean-100 text-ocean-700 font-semibold' : 'bg-gray-100 text-gray-600' }}">
                        {{ $counts['desa'] }}
                    </span>
                </a>
            </nav>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6">
            <form method="GET" action="{{ route('master.wilayah.index') }}" class="flex flex-col sm:flex-row gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="{{ __('Cari nama wilayah atau kode...') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                </div>

                {{-- Parent filters --}}
                @if($tab === 'kecamatan')
                    <div class="w-full sm:w-64">
                        <select name="regency_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">{{ __('Semua Kab/Kota di Aceh') }}</option>
                            @foreach($regencies as $r)
                                <option value="{{ $r->id }}" {{ request('regency_id') == $r->id ? 'selected' : '' }}>
                                    {{ $r->code }} - {{ ucfirst($r->type) }} {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @elseif($tab === 'desa')
                    <div class="w-full sm:w-64">
                        <select name="district_id" onchange="this.form.submit()" class="w-full py-2 px-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            <option value="">{{ __('Semua Kecamatan') }}</option>
                            @foreach($districts as $d)
                                <option value="{{ $d->id }}" {{ request('district_id') == $d->id ? 'selected' : '' }}>
                                    {{ $d->code }} - {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg transition-colors">
                        {{ __('Filter') }}
                    </button>
                    @if($search || request('regency_id') || request('district_id'))
                        <a href="{{ route('master.wilayah.index', ['tab' => $tab]) }}" class="px-3 py-2 text-sm text-red-600 hover:text-red-700">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th class="py-3 px-4 w-16 text-center">#</th>
                            <th class="py-3 px-4 w-32">{{ __('Kode') }}</th>
                            <th class="py-3 px-4">{{ __('Nama Wilayah') }}</th>
                            @if($tab === 'kecamatan')
                                <th class="py-3 px-4">{{ __('Kabupaten / Kota') }}</th>
                                <th class="py-3 px-4">{{ __('Provinsi') }}</th>
                            @elseif($tab === 'desa')
                                <th class="py-3 px-4 w-28">{{ __('Kode Pos') }}</th>
                                <th class="py-3 px-4">{{ __('Kecamatan') }}</th>
                                <th class="py-3 px-4">{{ __('Kabupaten / Kota') }}</th>
                            @else
                                <th class="py-3 px-4 w-32">{{ __('Tipe') }}</th>
                                <th class="py-3 px-4">{{ __('Provinsi') }}</th>
                            @endif
                            <th class="py-3 px-4 text-right w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse($items as $index => $item)
                            <tr class="hover:bg-ocean-50/40 transition-colors">
                                <td class="py-3 px-4 text-center text-gray-400 font-mono text-xs">
                                    {{ $items->firstItem() + $index }}
                                </td>
                                <td class="py-3 px-4 font-mono font-medium text-ocean-700">
                                    <span class="px-2 py-0.5 rounded bg-ocean-50 border border-ocean-100">
                                        {{ $item->code }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 font-semibold text-gray-900">
                                    {{ $item->name }}
                                </td>

                                @if($tab === 'kecamatan')
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $item->regency ? ucfirst($item->regency->type).' '.$item->regency->name : '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">
                                        {{ $item->regency->province->name ?? 'Aceh' }}
                                    </td>
                                @elseif($tab === 'desa')
                                    <td class="py-3 px-4 font-mono text-xs text-gray-500">
                                        {{ $item->postal_code ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $item->district->name ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-gray-500 text-xs">
                                        {{ $item->district->regency ? ucfirst($item->district->regency->type).' '.$item->district->regency->name : '-' }}
                                    </td>
                                @else
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $item->type === 'kota' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ ucfirst($item->type) }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-gray-600">
                                        {{ $item->province->name ?? 'Aceh' }}
                                    </td>
                                @endif

                                <td class="py-3 px-4 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Edit Button --}}
                                        @php
                                            $updateRoute = match($tab) {
                                                'kabupaten' => route('master.wilayah.regency.update', $item->id),
                                                'kecamatan' => route('master.wilayah.district.update', $item->id),
                                                'desa'      => route('master.wilayah.village.update', $item->id),
                                                default     => route('master.wilayah.province.update', $item->id),
                                            };
                                            $deleteRoute = match($tab) {
                                                'kabupaten' => route('master.wilayah.regency.destroy', $item->id),
                                                'kecamatan' => route('master.wilayah.district.destroy', $item->id),
                                                'desa'      => route('master.wilayah.village.destroy', $item->id),
                                                default     => route('master.wilayah.province.destroy', $item->id),
                                            };
                                        @endphp
                                        <button @click="openEdit({{ json_encode($item) }}, '{{ $updateRoute }}')"
                                                class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors"
                                                title="{{ __('Ubah') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </button>

                                        {{-- Delete Form --}}
                                        <form method="POST" action="{{ $deleteRoute }}" onsubmit="return confirm('{{ __('Apakah Anda yakin ingin menghapus data wilayah ini?') }}');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
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
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="text-4xl mb-2">🗺️</div>
                                    <p class="font-medium text-gray-600">{{ __('Tidak ada data wilayah ditemukan.') }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ __('Coba ubah kata kunci pencarian atau filter yang digunakan.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$items" />
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL TAMBAH DATA                                                 --}}
        {{-- ================================================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             @keydown.escape.window="showCreateModal = false">
            <div @click.away="showCreateModal = false"
                 class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-100">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">
                        @if($tab === 'kecamatan') {{ __('Tambah Kecamatan') }}
                        @elseif($tab === 'desa') {{ __('Tambah Gampong / Desa') }}
                        @else {{ __('Tambah Kabupaten / Kota') }}
                        @endif
                    </h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST"
                      action="{{ match($tab) {
                          'kecamatan' => route('master.wilayah.district.store'),
                          'desa'      => route('master.wilayah.village.store'),
                          default     => route('master.wilayah.regency.store'),
                      } }}"
                      class="mt-4 space-y-4">
                    @csrf

                    @if($tab === 'kecamatan')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kabupaten / Kota') }} *</label>
                            <select name="regency_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kab/Kota') }} --</option>
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ $r->code }} - {{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($tab === 'desa')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kecamatan') }} *</label>
                            <select name="district_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Kecamatan') }} --</option>
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}">{{ $d->code }} - {{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="province_id" value="{{ $aceh?->id }}">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tipe Wilayah') }} *</label>
                            <select name="type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="kabupaten">{{ __('Kabupaten') }}</option>
                                <option value="kota">{{ __('Kota') }}</option>
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Wilayah') }} *</label>
                        <input type="text"
                               name="code"
                               required
                               placeholder="{{ $tab === 'provinsi' ? 'Contoh: 11' : ($tab === 'kabupaten' ? 'Contoh: 11.19' : ($tab === 'kecamatan' ? 'Contoh: 11.71.07' : 'Contoh: 11.71.02.2005')) }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        <span class="text-xs text-gray-400 mt-1 block">{{ __('Gunakan kode resmi Kemendagri / BPS.') }}</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            @if($tab === 'desa') {{ __('Nama Gampong / Desa') }} *
                            @else {{ __('Nama Wilayah') }} *
                            @endif
                        </label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="{{ $tab === 'desa' ? 'Contoh: Gampong Deah Glumpang' : __('Masukkan nama wilayah') }}"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    @if($tab === 'desa')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Pos (Opsional)') }}</label>
                            <input type="text"
                                   name="postal_code"
                                   placeholder="Contoh: 23122"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Data') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================= --}}
        {{-- MODAL UBAH DATA                                                   --}}
        {{-- ================================================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-gray-100">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ __('Ubah Data Wilayah') }}
                    </h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    @if($tab === 'kecamatan')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kabupaten / Kota') }} *</label>
                            <select name="regency_id" x-model="editItem.regency_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($regencies as $r)
                                    <option value="{{ $r->id }}">{{ $r->code }} - {{ ucfirst($r->type) }} {{ $r->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @elseif($tab === 'desa')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kecamatan') }} *</label>
                            <select name="district_id" x-model="editItem.district_id" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                @foreach($districts as $d)
                                    <option value="{{ $d->id }}">{{ $d->code }} - {{ $d->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="province_id" :value="editItem.province_id || '{{ $aceh?->id }}'">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Tipe Wilayah') }} *</label>
                            <select name="type" x-model="editItem.type" required class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                                <option value="kabupaten">{{ __('Kabupaten') }}</option>
                                <option value="kota">{{ __('Kota') }}</option>
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Wilayah') }} *</label>
                        <input type="text"
                               name="code"
                               x-model="editItem.code"
                               required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">
                            @if($tab === 'desa') {{ __('Nama Gampong / Desa') }} *
                            @else {{ __('Nama Wilayah') }} *
                            @endif
                        </label>
                        <input type="text"
                               name="name"
                               x-model="editItem.name"
                               required
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    @if($tab === 'desa')
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kode Pos (Opsional)') }}</label>
                            <input type="text"
                                   name="postal_code"
                                   x-model="editItem.postal_code"
                                   class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    @endif

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
