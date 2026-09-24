<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>📦</span>
            <span>{{ __('Pengumpulan Data: Pendaratan Ikan (Fish Landings & Auction)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDetailModal: false,
        editItem: {},
        detailItem: { items: [] },
        showDetailModal: false,
        editItem: {},
        detailItem: { items: [] },
        editAction: '',
        formItems: [
            { fish_species_id: '', weight_kg: '', price_per_kg: '', quality_grade: 'A', fish_count: '' }
        ],

        addItemRow() {
            this.formItems.push({
                fish_species_id: '',
                weight_kg: '',
                price_per_kg: '',
                quality_grade: 'A',
                fish_count: ''
            });
        },

        removeItemRow(index) {
            if (this.formItems.length > 1) {
                this.formItems.splice(index, 1);
            }
        },

        calcTotalWeight() {
            return this.formItems.reduce((acc, curr) => acc + (parseFloat(curr.weight_kg) || 0), 0).toFixed(2);
        },

        calcTotalValue() {
            return this.formItems.reduce((acc, curr) => {
                const w = parseFloat(curr.weight_kg) || 0;
                const p = parseFloat(curr.price_per_kg) || 0;
                return acc + (w * p);
            }, 0);
        },

        formatRupiah(amount) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
        },

        openEdit(item, actionUrl) {
            this.editItem = Object.assign({}, item);
            this.editAction = actionUrl;
            this.showEditModal = true;
        },

        openDetail(item) {
            this.detailItem = Object.assign({}, item);
            this.showDetailModal = true;
        }
    }">


        @if (isset($errors) && $errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-semibold text-sm">
                    <span>⚠️</span>
                    <span>{{ __('Terdapat kesalahan input formulir:') }}</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Intro Header Banner --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">📦</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🌊</span>
                        <span>{{ __('Pelelangan & Pembongkaran Ikan Pelabuhan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Pendaratan Ikan (Fish Landings & Auction)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Registrasi manifest transaksi pendaratan ikan di pelabuhan & TPI: nomor manifest pembongkaran, pelabuhan bongkar, volume per spesies, mutu/grade kesegaran, nilai perputaran ekonomi lelang (Rp), dan partisipasi bakul ikan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Catat Pendaratan Baru') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 mb-6">
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Total Pendaratan') }}</span>
                <span class="text-2xl font-black text-slate-800 mt-1">{{ number_format($counts['total_landings']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Transaksi manifest') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-ocean-700 uppercase tracking-wider">{{ __('Volume Ikan') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-ocean-800">{{ number_format($counts['total_weight_kg']) }}</span>
                    <span class="text-xs font-bold text-slate-500">kg</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">≈ {{ $counts['total_weight_ton'] }} ton didaratkan</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-emerald-600 uppercase tracking-wider">{{ __('Nilai Transaksi') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-xl font-black text-emerald-600">Rp {{ number_format($counts['total_value_rp']) }}</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">≈ Rp {{ $counts['total_value_juta'] }} Juta lelang</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-teal-600 uppercase tracking-wider">{{ __('Rata-rata Transaksi') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-xl font-black text-teal-700">Rp {{ number_format($counts['avg_transaction_value']) }}</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Per manifest bongkar') }}</span>
            </div>

            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <span class="text-xs font-medium text-purple-600 uppercase tracking-wider">{{ __('Bakul / Pembeli') }}</span>
                <div class="flex items-baseline gap-1 mt-1">
                    <span class="text-2xl font-black text-purple-700">{{ number_format($counts['total_buyers']) }}</span>
                    <span class="text-xs font-bold text-slate-500">orang</span>
                </div>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Pedagang terlibat') }}</span>
            </div>
        </div>

        {{-- Filters & Search Toolbar --}}
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm mb-6">
            <form method="GET" action="{{ route('landings.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                {{-- Search Box --}}
                <div class="lg:col-span-2">
                    <label for="search_landing" class="sr-only">{{ __('Cari No. Manifest, Kapal, atau Trip') }}</label>
                    <div class="relative">
                        <input type="text"
                               id="search_landing"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="{{ __('Cari No. Manifest, Kapal, atau Trip') }}"
                               class="w-full pl-9 pr-3 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-sm">🔍</span>
                    </div>
                </div>

                {{-- Filter Pelabuhan / Landing Site --}}
                <div>
                    <label for="landing_site_id" class="sr-only">{{ __('Pelabuhan / TPI Pendaratan') }}</label>
                    <select id="landing_site_id" name="landing_site_id" class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <option value="">{{ __('Pilih Pelabuhan / TPI') }}</option>
                        @foreach($landingSites as $ls)
                            <option value="{{ $ls->id }}" {{ request('landing_site_id') == $ls->id ? 'selected' : '' }}>
                                [{{ $ls->type }}] {{ $ls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Tanggal --}}
                <div>
                    <label for="date_from" class="sr-only">{{ __('Tanggal Pendaratan Dari') }}</label>
                    <input type="date"
                           id="date_from"
                           name="date_from"
                           value="{{ request('date_from') }}"
                           title="{{ __('Tanggal Pendaratan Dari') }}"
                           class="w-full py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 bg-ocean-800 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors text-center">
                        {{ __('Filter') }}
                    </button>
                    @if(request()->hasAny(['search', 'landing_site_id', 'date_from', 'date_to']))
                        <a href="{{ route('landings.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-medium rounded-lg transition-colors" title="{{ __('Reset Filter') }}">
                            ✕
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Data Table --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">#</th>
                            <th class="px-4 py-3.5">{{ __('No. Manifest & Waktu') }}</th>
                            <th class="px-4 py-3.5">{{ __('Pelabuhan / TPI Bongkar') }}</th>
                            <th class="px-4 py-3.5">{{ __('Trip & Kapal Asal') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Volume Ikan') }}</th>
                            <th class="px-4 py-3.5 text-right">{{ __('Nilai Transaksi (Rp)') }}</th>
                            <th class="px-4 py-3.5">{{ __('Komoditas Dominan') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Bakul') }}</th>
                            <th class="px-4 py-3.5 text-center w-28">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($landings as $index => $item)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $landings->firstItem() + $index }}
                                </td>

                                {{-- No. Manifest & Waktu --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-mono text-xs font-bold text-ocean-800 bg-ocean-50 border border-ocean-200 px-2 py-0.5 rounded inline-block">
                                        {{ $item->landing_number }}
                                    </div>
                                    <div class="text-xs text-slate-600 font-semibold mt-1">
                                        {{ $item->landing_date ? $item->landing_date->format('d/m/Y') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        ⏰ {{ $item->landing_date ? $item->landing_date->format('H:i') . ' WIB' : '' }}
                                    </div>
                                </td>

                                {{-- Pelabuhan / TPI --}}
                                <td class="px-4 py-3">
                                    @if($item->landingSite)
                                        <div class="font-semibold text-xs text-slate-900 flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                                {{ $item->landingSite->type }}
                                            </span>
                                            <span>{{ $item->landingSite->name }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ $item->landingSite->regency->name ?? '' }}
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Tidak tercatat') }}</span>
                                    @endif
                                </td>

                                {{-- Trip & Kapal Asal --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($item->fishingTrip)
                                        <a href="{{ route('trips.index', ['search' => $item->fishingTrip->trip_number]) }}"
                                           class="font-mono text-xs font-bold text-ocean-700 hover:text-ocean-900 underline block">
                                            {{ $item->fishingTrip->trip_number }}
                                        </a>
                                        <div class="font-medium text-xs text-slate-900 mt-0.5 flex items-center gap-1">
                                            <span>🚢</span>
                                            <span>{{ $item->fishingTrip->vessel->name ?? '-' }}</span>
                                        </div>
                                        @if($item->fishingTrip->captain)
                                            <div class="text-[11px] text-slate-500">
                                                {{ __('Nahkoda') }}: {{ $item->fishingTrip->captain->name }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">{{ __('Pendaratan mandiri') }}</span>
                                    @endif
                                </td>

                                {{-- Volume Ikan --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-black text-slate-900">
                                        {{ number_format($item->total_weight_kg) }}
                                    </span>
                                    <span class="text-xs font-bold text-slate-500">kg</span>
                                    <div class="text-[11px] text-ocean-700 font-semibold mt-0.5">
                                        ≈ {{ round($item->total_weight_kg / 1000, 2) }} ton
                                    </div>
                                </td>

                                {{-- Nilai Transaksi --}}
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-sm font-black text-emerald-700">
                                        Rp {{ number_format($item->total_value_rp) }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        {{ $item->items_count ?? $item->items->count() }} {{ __('komoditas') }}
                                    </div>
                                </td>

                                {{-- Komoditas Dominan --}}
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($item->items->take(3) as $it)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                                <span>{{ $it->species->indonesian_name ?? '-' }}</span>
                                                <span class="font-bold text-slate-500">({{ number_format($it->weight_kg) }}kg)</span>
                                            </span>
                                        @endforeach
                                        @if($item->items->count() > 3)
                                            <span class="text-[10px] text-slate-500 self-center font-medium">+{{ $item->items->count() - 3 }} lagi</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Bakul --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold bg-purple-50 text-purple-700 border border-purple-200">
                                        <span>👥</span>
                                        <span>{{ $item->buyer_count ?? '-' }}</span>
                                    </span>
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Detail / Faktur Button --}}
                                        <button type="button"
                                                @click="openDetail({{ json_encode([
                                                    'id' => $item->id,
                                                    'landing_number' => $item->landing_number,
                                                    'landing_date' => $item->landing_date ? $item->landing_date->format('d F Y, H:i') . ' WIB' : '-',
                                                    'site_name' => ($item->landingSite ? '[' . $item->landingSite->type . '] ' . $item->landingSite->name : '-'),
                                                    'site_location' => $item->landingSite->regency->name ?? '-',
                                                    'trip_number' => $item->fishingTrip->trip_number ?? '-',
                                                    'vessel_name' => $item->fishingTrip->vessel->name ?? '-',
                                                    'captain_name' => $item->fishingTrip->captain->name ?? '-',
                                                    'total_weight_kg' => number_format($item->total_weight_kg),
                                                    'total_value_rp' => 'Rp ' . number_format($item->total_value_rp),
                                                    'buyer_count' => $item->buyer_count ?? '-',
                                                    'recorded_by' => $item->recordedBy->name ?? 'Administrator',
                                                    'notes' => $item->notes ?? '-',
                                                    'items' => $item->items->map(fn($it) => [
                                                        'species_name' => $it->species->local_name_id ?? '-',
                                                        'species_code' => $it->species->fao_code ?? '-',
                                                        'weight_kg' => number_format($it->weight_kg, 1),
                                                        'fish_count' => $it->fish_count ? number_format($it->fish_count) : '-',
                                                        'price_per_kg' => 'Rp ' . number_format($it->price_per_kg),
                                                        'total_price' => 'Rp ' . number_format($it->total_price),
                                                        'quality_grade' => $it->quality_grade ?? 'A',
                                                    ]),
                                                ]) }})"
                                                class="p-1.5 text-slate-500 hover:text-ocean-700 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Lihat Faktur Pendaratan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        </button>

                                        {{-- Edit Button --}}
                                        <button type="button"
                                                @click="openEdit({{ json_encode([
                                                    'id' => $item->id,
                                                    'landing_number' => $item->landing_number,
                                                    'fishing_trip_id' => $item->fishing_trip_id,
                                                    'landing_site_id' => $item->landing_site_id,
                                                    'landing_date' => $item->landing_date ? $item->landing_date->format('Y-m-d\TH:i') : '',
                                                    'buyer_count' => $item->buyer_count,
                                                    'notes' => $item->notes,
                                                ]) }}, '{{ route('landings.update', $item) }}')"
                                                class="p-1.5 text-slate-500 hover:text-amber-600 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Ubah Pendaratan') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        {{-- Delete Button --}}
                                        <form method="POST" action="{{ route('landings.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi pendaratan {{ $item->landing_number }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-rose-600 hover:bg-slate-100 rounded-lg transition-colors" title="{{ __('Hapus') }}">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-4xl mb-3">📦</div>
                                    <p class="text-base font-semibold text-slate-700">{{ __('Belum ada data transaksi pendaratan') }}</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        {{ __('Gunakan tombol Catat Pendaratan Baru di atas untuk membukukan transaksi pembongkaran dan pelelangan ikan.') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$landings" />
        </div>

        {{-- ======================================= --}}
        {{-- MODAL TAMBAH PENDARATAN                 --}}
        {{-- ======================================= --}}
        <div x-show="showCreateModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showCreateModal = false">
            <div class="bg-white rounded-2xl max-w-3xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showCreateModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">📦</span>
                        <h3 class="font-bold text-base">{{ __('Catat Transaksi Pendaratan Ikan Baru') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" action="{{ route('landings.store') }}" class="p-6 space-y-4">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Trip Penangkapan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Trip Asal Pendaratan') }}
                            </label>
                            <select name="fishing_trip_id" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pendaratan Mandiri / Tanpa Trip') }} --</option>
                                @foreach($trips as $t)
                                    <option value="{{ $t->id }}">
                                        {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Pelabuhan Pendaratan --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Pelabuhan / TPI Pembongkaran') }} <span class="text-rose-500">*</span>
                            </label>
                            <select name="landing_site_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                                <option value="">-- {{ __('Pilih Pelabuhan / TPI') }} --</option>
                                @foreach($landingSites as $ls)
                                    <option value="{{ $ls->id }}">
                                        [{{ $ls->type }}] {{ $ls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        {{-- Tanggal & Jam Pendaratan --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Pembongkaran Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="landing_date" value="{{ date('Y-m-d\TH:i') }}" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>

                        {{-- Jumlah Bakul --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Bakul / Pembeli') }}
                            </label>
                            <input type="number" name="buyer_count" placeholder="Contoh: 12" min="0" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Dynamic Line Items: Rincian Komoditas Ikan --}}
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-800">{{ __('Rincian Komoditas Ikan yang Didaratkan:') }}</span>
                            <button type="button" @click="addItemRow()" class="text-xs font-semibold text-ocean-700 hover:text-ocean-900 flex items-center gap-1 bg-white px-2 py-1 rounded border border-slate-200 shadow-2xs">
                                <span>➕</span> {{ __('Tambah Komoditas') }}
                            </button>
                        </div>

                        <div class="space-y-2">
                            <template x-for="(row, idx) in formItems" :key="idx">
                                <div class="grid grid-cols-1 sm:grid-cols-12 gap-2 bg-white p-2.5 rounded-lg border border-slate-200 items-center">
                                    {{-- Spesies Ikan (4 cols) --}}
                                    <div class="sm:col-span-4">
                                        <div x-data="{
                                            open: false,
                                            search: '',
                                            options: [],
                                            loading: false,
                                            selectedText: '',
                                            
                                            async fetchOptions() {
                                                this.loading = true;
                                                try {
                                                    const res = await fetch(`{{ route('master.species.search') }}?q=${encodeURIComponent(this.search)}&is_active=true`);
                                                    const json = await res.json();
                                                    this.options = json.data || [];
                                                } catch(e) { this.options = []; }
                                                this.loading = false;
                                            },
                                            selectOpt(opt) {
                                                row.fish_species_id = opt.id;
                                                this.selectedText = `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}`;
                                                this.open = false;
                                            }
                                        }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                                            <input type="hidden" :name="'items[' + idx + '][fish_species_id]'" x-model="row.fish_species_id" required>
                                            
                                            <div @click="open = !open" class="w-full text-xs border border-slate-300 rounded p-1.5 focus-within:ring-2 focus-within:ring-ocean-500 bg-white cursor-pointer flex justify-between items-center h-[30px]">
                                                <span x-text="selectedText || '-- Pilih Ikan --'" class="truncate" :class="selectedText ? 'text-slate-900' : 'text-slate-500'"></span>
                                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                            </div>

                                            <div x-show="open" @click.away="open = false" x-cloak class="absolute z-50 w-64 left-0 mt-1 bg-white border border-slate-200 rounded-lg shadow-lg">
                                                <div class="p-2 border-b border-slate-100">
                                                    <input type="text" x-model="search" @input.debounce.300ms="fetchOptions" placeholder="Cari FAO, Nama Lokal..." class="w-full text-xs border border-slate-300 rounded-md p-1.5 focus:ring-2 focus:ring-ocean-500">
                                                </div>
                                                <ul class="max-h-60 overflow-y-auto p-1">
                                                    <li x-show="loading" class="p-2 text-xs text-slate-500 text-center">Mencari...</li>
                                                    <li x-show="!loading && options.length === 0" class="p-2 text-xs text-slate-500 text-center">Tidak ditemukan</li>
                                                    <template x-for="opt in options" :key="opt.id">
                                                        <li @click="selectOpt(opt)" class="p-2 hover:bg-ocean-50 cursor-pointer rounded-md">
                                                            <div class="flex items-baseline gap-1.5">
                                                                <span class="font-mono text-[10px] font-bold px-1 py-0.5 rounded bg-ocean-100 text-ocean-800" x-text="opt.fao_code || '-'"></span>
                                                                <span class="font-bold text-slate-900 text-xs" x-text="opt.local_name_id || '-'"></span>
                                                            </div>
                                                        </li>
                                                    </template>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Berat Kg (2 cols) --}}
                                    <div class="sm:col-span-2">
                                        <input type="number" step="0.01" :name="'items[' + idx + '][weight_kg]'" x-model="row.weight_kg" placeholder="Berat kg" required class="w-full text-xs border border-slate-300 rounded p-1.5 font-bold">
                                    </div>

                                    {{-- Harga / Kg (3 cols) --}}
                                    <div class="sm:col-span-3">
                                        <input type="number" step="100" :name="'items[' + idx + '][price_per_kg]'" x-model="row.price_per_kg" placeholder="Harga/kg (Rp)" required class="w-full text-xs border border-slate-300 rounded p-1.5">
                                    </div>

                                    {{-- Mutu Grade (2 cols) --}}
                                    <div class="sm:col-span-2">
                                        <select :name="'items[' + idx + '][quality_grade]'" x-model="row.quality_grade" class="w-full text-xs border border-slate-300 rounded p-1.5">
                                            <option value="A">Grade A</option>
                                            <option value="B">Grade B</option>
                                            <option value="C">Grade C</option>
                                            <option value="reject">Reject</option>
                                        </select>
                                    </div>

                                    {{-- Hapus Tombol (1 col) --}}
                                    <div class="sm:col-span-1 text-center">
                                        <button type="button" @click="removeItemRow(idx)" class="text-rose-500 hover:text-rose-700 text-sm font-bold" title="Hapus Baris">✕</button>
                                    </div>
                                </div>
                            </template>
                        </div>

                        {{-- Total Live Calculation Preview --}}
                        <div class="flex items-center justify-between pt-2 border-t border-slate-200 text-xs">
                            <span class="text-slate-600 font-medium">
                                {{ __('Estimasi Total Berat:') }} <strong class="text-slate-900" x-text="calcTotalWeight() + ' kg'"></strong>
                            </span>
                            <span class="text-slate-600 font-medium">
                                {{ __('Estimasi Nilai Transaksi:') }} <strong class="text-emerald-700" x-text="formatRupiah(calcTotalValue())"></strong>
                            </span>
                        </div>
                    </div>

                    {{-- Catatan Pembongkaran --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Catatan / Berita Acara Pembongkaran') }}
                        </label>
                        <textarea name="notes" rows="2" placeholder="Kondisi palka, kelancaran lelang, tujuan distribusi hasil tangkapan..." class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Pendaratan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL UBAH (EDIT) PENDARATAN            --}}
        {{-- ======================================= --}}
        <div x-show="showEditModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showEditModal = false">
            <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showEditModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-amber-600 to-amber-700 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">✏️</span>
                        <h3 class="font-bold text-base">{{ __('Ubah Data Pendaratan Ikan') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" :action="editAction" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Trip Asal Pendaratan') }}
                        </label>
                        <select name="fishing_trip_id" x-model="editItem.fishing_trip_id" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            <option value="">-- {{ __('Pendaratan Mandiri / Tanpa Trip') }} --</option>
                            @foreach($trips as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->trip_number }} | {{ $t->vessel->name ?? 'Tanpa Kapal' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Pelabuhan / TPI Pembongkaran') }} <span class="text-rose-500">*</span>
                        </label>
                        <select name="landing_site_id" x-model="editItem.landing_site_id" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                            @foreach($landingSites as $ls)
                                <option value="{{ $ls->id }}">
                                    [{{ $ls->type }}] {{ $ls->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Waktu Pembongkaran Ikan') }} <span class="text-rose-500">*</span>
                            </label>
                            <input type="datetime-local" name="landing_date" x-model="editItem.landing_date" required class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">
                                {{ __('Jumlah Bakul / Pembeli') }}
                            </label>
                            <input type="number" name="buyer_count" x-model="editItem.buyer_count" min="0" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Catatan Pembongkaran') }}
                        </label>
                        <textarea name="notes" x-model="editItem.notes" rows="3" class="w-full text-sm border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showEditModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-sm rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-500 text-white font-medium text-sm rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Perubahan') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL FAKTUR / DETAIL MANIFEST          --}}
        {{-- ======================================= --}}
        <div x-show="showDetailModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showDetailModal = false">
            <div class="bg-white rounded-2xl max-w-2xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showDetailModal = false">
                {{-- Faktur Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-ocean-200 block font-semibold">{{ __('Faktur Manifest Pembongkaran') }}</span>
                        <h3 class="font-bold text-lg font-mono" x-text="detailItem.landing_number"></h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Faktur Body --}}
                <div class="p-6 space-y-4">
                    {{-- Metadata Transaksi --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Pelabuhan Bongkar') }}</span>
                            <span class="font-bold text-slate-900 text-sm mt-0.5 block" x-text="detailItem.site_name"></span>
                            <span class="text-[11px] text-slate-500" x-text="detailItem.site_location"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Trip & Kapal Asal') }}</span>
                            <span class="font-bold text-ocean-700 text-sm mt-0.5 block" x-text="detailItem.trip_number"></span>
                            <span class="text-[11px] text-slate-600 block" x-text="detailItem.vessel_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Waktu Pembongkaran') }}</span>
                            <span class="font-semibold text-slate-900 mt-0.5 block" x-text="detailItem.landing_date"></span>
                            <span class="text-[11px] text-slate-500 block" x-text="'Bakul: ' + detailItem.buyer_count + ' orang'"></span>
                        </div>
                    </div>

                    {{-- Tabel Rincian Komoditas Ikan --}}
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-100 text-slate-700 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2.5">#</th>
                                    <th class="px-3 py-2.5">{{ __('Komoditas Spesies') }}</th>
                                    <th class="px-3 py-2.5 text-center">{{ __('Mutu') }}</th>
                                    <th class="px-3 py-2.5 text-right">{{ __('Volume (kg)') }}</th>
                                    <th class="px-3 py-2.5 text-right">{{ __('Harga/Kg (Rp)') }}</th>
                                    <th class="px-3 py-2.5 text-right">{{ __('Subtotal (Rp)') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(item, i) in detailItem.items" :key="i">
                                    <tr class="hover:bg-slate-50/70">
                                        <td class="px-3 py-2 text-slate-400 text-center" x-text="i + 1"></td>
                                        <td class="px-3 py-2">
                                            <span class="font-bold text-slate-900" x-text="item.species_name"></span>
                                            <span class="font-mono text-[10px] text-slate-500 block" x-text="'[' + item.species_code + ']'"></span>
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold"
                                                  :class="{
                                                      'bg-emerald-100 text-emerald-800': item.quality_grade == 'A',
                                                      'bg-blue-100 text-blue-800': item.quality_grade == 'B',
                                                      'bg-amber-100 text-amber-800': item.quality_grade == 'C',
                                                      'bg-rose-100 text-rose-800': item.quality_grade == 'reject'
                                                  }"
                                                  x-text="'Grade ' + item.quality_grade">
                                            </span>
                                        </td>
                                        <td class="px-3 py-2 text-right font-semibold text-slate-900" x-text="item.weight_kg"></td>
                                        <td class="px-3 py-2 text-right text-slate-600" x-text="item.price_per_kg"></td>
                                        <td class="px-3 py-2 text-right font-bold text-emerald-700" x-text="item.total_price"></td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot class="bg-slate-50 font-bold border-t border-slate-200">
                                <tr>
                                    <td colspan="3" class="px-3 py-2.5 text-right uppercase text-[11px] text-slate-600">{{ __('Total Akumulasi:') }}</td>
                                    <td class="px-3 py-2.5 text-right font-black text-slate-900" x-text="detailItem.total_weight_kg + ' kg'"></td>
                                    <td></td>
                                    <td class="px-3 py-2.5 text-right font-black text-emerald-700 text-sm" x-text="detailItem.total_value_rp"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Catatan --}}
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                        <span class="text-slate-500 block font-semibold mb-0.5">{{ __('Catatan Petugas / Berita Acara:') }}</span>
                        <p class="text-slate-700" x-text="detailItem.notes"></p>
                    </div>

                    {{-- Actions --}}
                    <div class="pt-2 flex items-center justify-between">
                        <span class="text-[11px] text-slate-500" x-text="'Petugas Pencatat: ' + detailItem.recorded_by"></span>
                        <button type="button" @click="showDetailModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium text-sm rounded-lg transition-colors">
                            {{ __('Tutup Faktur') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
