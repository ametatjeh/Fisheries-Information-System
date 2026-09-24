<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🗺️</span>
            <span>{{ __('Master Daerah Penangkapan Ikan (Fishing Ground)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        editItem: {},
        editAction: '',
        openEdit(item, url) {
            this.editItem = { ...item };
            this.editAction = url;
            this.showEditModal = true;
        }
    }" class="space-y-6">



        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
                <div class="font-bold mb-1">{{ __('Terjadi kesalahan input:') }}</div>
                <ul class="list-disc list-inside text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">{{ __('Total Fishing Ground') }}</div>
                <div class="text-2xl font-bold font-mono text-slate-900 mt-1">{{ number_format($counts['total']) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-[11px] font-semibold text-emerald-600 uppercase tracking-wider">{{ __('Status Aktif') }}</div>
                <div class="text-2xl font-bold font-mono text-emerald-600 mt-1">{{ number_format($counts['active']) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-[11px] font-semibold text-ocean-600 uppercase tracking-wider">{{ __('Berkoordinat GPS') }}</div>
                <div class="text-2xl font-bold font-mono text-ocean-600 mt-1">{{ number_format($counts['with_coords']) }}</div>
            </div>
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-[11px] font-semibold text-purple-600 uppercase tracking-wider">{{ __('WPPNRI Terkait') }}</div>
                <div class="text-2xl font-bold font-mono text-purple-600 mt-1">{{ number_format($counts['wpp_count']) }}</div>
            </div>
        </div>

        {{-- Filter & Action Bar --}}
        <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs">
            <form method="GET" action="{{ route('master.fishing-grounds.index') }}" class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex flex-wrap items-center gap-3 flex-1">
                    <label for="filter_search" class="sr-only">{{ __('Cari Daerah Penangkapan') }}</label>
                    <input type="text" id="filter_search" name="search" value="{{ $search }}"
                           aria-label="{{ __('Cari nama daerah, kode, atau keterangan') }}"
                           title="{{ __('Cari nama daerah, kode, atau keterangan') }}"
                           placeholder="{{ __('Cari nama daerah, kode, atau keterangan...') }}"
                           class="text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500 w-full md:w-64">

                    <label for="filter_wppnri_id" class="sr-only">{{ __('Filter WPP-NRI') }}</label>
                    <select id="filter_wppnri_id" name="wppnri_id"
                            aria-label="{{ __('Filter WPP-NRI') }}"
                            title="{{ __('Filter WPP-NRI') }}"
                            class="text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua WPP-NRI') }}</option>
                        @foreach($wppList as $w)
                            <option value="{{ $w->id }}" {{ $wppnriId == $w->id ? 'selected' : '' }}>
                                {{ $w->code }} - {{ $w->name }}
                            </option>
                        @endforeach
                    </select>

                    <label for="filter_status" class="sr-only">{{ __('Filter Status') }}</label>
                    <select id="filter_status" name="status"
                            aria-label="{{ __('Filter Status') }}"
                            title="{{ __('Filter Status') }}"
                            class="text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        <option value="">{{ __('Semua Status') }}</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>{{ __('Aktif') }}</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>{{ __('Nonaktif') }}</option>
                    </select>

                    <button type="submit" class="px-4 py-2 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white text-xs font-semibold transition shadow-xs">
                        {{ __('Filter') }}
                    </button>

                    @if($search || $wppnriId || $status !== null && $status !== '')
                        <a href="{{ route('master.fishing-grounds.index') }}" class="text-xs font-semibold text-rose-600 hover:text-rose-700 underline">
                            {{ __('Reset') }}
                        </a>
                    @endif
                </div>

                <div>
                    <button type="button" @click="showCreateModal = true"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition shadow-xs">
                        <span>➕</span>
                        <span>{{ __('Tambah Daerah Penangkapan') }}</span>
                    </button>
                </div>
            </form>
        </div>

        {{-- Table List --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-ocean-900 text-white uppercase font-semibold text-[11px] border-b border-ocean-950">
                        <tr>
                            <th class="px-4 py-3.5 text-white">{{ __('Kode') }}</th>
                            <th class="px-4 py-3.5 text-white">{{ __('Nama Daerah Penangkapan') }}</th>
                            <th class="px-4 py-3.5 text-white">{{ __('WPP-NRI') }}</th>
                            <th class="px-4 py-3.5 text-white">{{ __('Koordinat Titik') }}</th>
                            <th class="px-4 py-3.5 text-center text-white">{{ __('Trip Terkait') }}</th>
                            <th class="px-4 py-3.5 text-center text-white">{{ __('Status') }}</th>
                            <th class="px-4 py-3.5 text-right text-white">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($grounds as $ground)
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="px-4 py-3 font-mono font-bold text-slate-800">{{ $ground->code ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-slate-900">{{ $ground->name }}</div>
                                    @if($ground->description)
                                        <div class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">{{ $ground->description }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($ground->wppnri)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-cyan-100 text-cyan-800">
                                            WPP {{ $ground->wppnri->code }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-[11px]">
                                    @if($ground->latitude && $ground->longitude)
                                        <span class="text-ocean-700 font-semibold">{{ (float)$ground->latitude }}°, {{ (float)$ground->longitude }}°</span>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Belum diisi') }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-slate-700">
                                    {{ number_format($ground->fishing_trips_count) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('master.fishing-grounds.toggle-status', $ground) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold transition {{ $ground->is_active ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                                            {{ $ground->is_active ? __('Aktif') : __('Nonaktif') }}
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-2">
                                        <button type="button"
                                                @click="openEdit({{ json_encode($ground) }}, '{{ route('master.fishing-grounds.update', $ground) }}')"
                                                class="px-2.5 py-1 rounded-lg border border-slate-200 hover:bg-slate-100 text-slate-700 text-[11px] font-medium transition">
                                            {{ __('Edit') }}
                                        </button>

                                        @if($ground->fishing_trips_count == 0)
                                            <form method="POST" action="{{ route('master.fishing-grounds.destroy', $ground) }}" onsubmit="return confirm('Hapus daerah penangkapan ini?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-2.5 py-1 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 text-[11px] font-medium transition">
                                                    {{ __('Hapus') }}
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[10px] text-slate-400 italic" title="Terkunci oleh data trip penangkapan">🔒 Terkunci</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">🌊</div>
                                    <div class="text-xs font-semibold">{{ __('Belum ada data master Daerah Penangkapan Ikan.') }}</div>
                                    <div class="text-[11px] text-slate-400 mt-1">{{ __('Klik tombol "Tambah Daerah Penangkapan" untuk menambahkan referensi baru.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$grounds" />
        </div>

        {{-- Create Modal --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showCreateModal = false">
            <div @click.away="showCreateModal = false"
                 class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 my-8">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🗺️</span>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Tambah Daerah Penangkapan Baru') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" action="{{ route('master.fishing-grounds.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="create_name" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nama Daerah Penangkapan') }} *</label>
                        <input type="text" id="create_name" name="name" required placeholder="Contoh: Perairan Karang Barat Pulo Weh"
                               title="{{ __('Nama Daerah Penangkapan') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="create_code" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Kode / Singkatan') }}</label>
                            <input type="text" id="create_code" name="code" placeholder="Misal: FG-ACEH-01"
                                   title="{{ __('Kode / Singkatan') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                        <div>
                            <label for="create_wppnri_id" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('WPP-NRI') }}</label>
                            <select id="create_wppnri_id" name="wppnri_id"
                                    aria-label="{{ __('WPP-NRI') }}"
                                    title="{{ __('Pilih WPP-NRI') }}"
                                    class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Pilih WPP-NRI') }}</option>
                                @foreach($wppList as $w)
                                    <option value="{{ $w->id }}">{{ $w->code }} - {{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="create_latitude" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Latitude (Lintang)') }}</label>
                            <input type="number" id="create_latitude" step="0.0000001" name="latitude" min="-90" max="90" placeholder="5.820000"
                                   title="{{ __('Latitude (Lintang)') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                        <div>
                            <label for="create_longitude" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Longitude (Bujur)') }}</label>
                            <input type="number" id="create_longitude" step="0.0000001" name="longitude" min="-180" max="180" placeholder="95.250000"
                                   title="{{ __('Longitude (Bujur)') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                    </div>

                    <div>
                        <label for="create_description" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Keterangan / Karakteristik Perairan') }}</label>
                        <textarea id="create_description" name="description" rows="2" placeholder="Catatan karakteristik perairan, batimetri, atau musim tangkap..."
                                  title="{{ __('Keterangan / Karakteristik Perairan') }}"
                                  class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500"></textarea>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded text-ocean-600 focus:ring-ocean-500">
                            <span class="text-xs text-slate-700 font-medium">{{ __('Aktifkan daerah penangkapan ini') }}</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                            {{ __('Simpan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Edit Modal --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4 overflow-y-auto"
             @keydown.escape.window="showEditModal = false">
            <div @click.away="showEditModal = false"
                 class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 my-8">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🗺️</span>
                        <h3 class="text-sm font-bold text-slate-800">{{ __('Edit Daerah Penangkapan') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form method="POST" :action="editAction" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label for="edit_name" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nama Daerah Penangkapan') }} *</label>
                        <input type="text" id="edit_name" name="name" x-model="editItem.name" required
                               title="{{ __('Nama Daerah Penangkapan') }}"
                               class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_code" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Kode / Singkatan') }}</label>
                            <input type="text" id="edit_code" name="code" x-model="editItem.code"
                                   title="{{ __('Kode / Singkatan') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                        <div>
                            <label for="edit_wppnri_id" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('WPP-NRI') }}</label>
                            <select id="edit_wppnri_id" name="wppnri_id" x-model="editItem.wppnri_id"
                                    aria-label="{{ __('WPP-NRI') }}"
                                    title="{{ __('Pilih WPP-NRI') }}"
                                    class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                                <option value="">{{ __('Pilih WPP-NRI') }}</option>
                                @foreach($wppList as $w)
                                    <option value="{{ $w->id }}">{{ $w->code }} - {{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="edit_latitude" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Latitude (Lintang)') }}</label>
                            <input type="number" id="edit_latitude" step="0.0000001" name="latitude" min="-90" max="90" x-model="editItem.latitude"
                                   title="{{ __('Latitude (Lintang)') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                        <div>
                            <label for="edit_longitude" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Longitude (Bujur)') }}</label>
                            <input type="number" id="edit_longitude" step="0.0000001" name="longitude" min="-180" max="180" x-model="editItem.longitude"
                                   title="{{ __('Longitude (Bujur)') }}"
                                   class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500">
                        </div>
                    </div>

                    <div>
                        <label for="edit_description" class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Keterangan / Karakteristik Perairan') }}</label>
                        <textarea id="edit_description" name="description" rows="2" x-model="editItem.description"
                                  title="{{ __('Keterangan / Karakteristik Perairan') }}"
                                  class="w-full text-xs rounded-xl border-slate-300 focus:border-ocean-500 focus:ring-ocean-500"></textarea>
                    </div>

                    <div>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" :checked="editItem.is_active" class="rounded text-ocean-600 focus:ring-ocean-500">
                            <span class="text-xs text-slate-700 font-medium">{{ __('Aktifkan daerah penangkapan ini') }}</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-50 transition cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white text-xs font-semibold shadow-xs transition cursor-pointer">
                            {{ __('Perbarui Data') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
