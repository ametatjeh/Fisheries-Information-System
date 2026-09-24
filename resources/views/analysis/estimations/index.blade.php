<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">📊</span>
                <span class="font-bold text-white">{{ __('Analisis: Estimasi Tangkapan (Catch Estimation & Raising Factor)') }}</span>
            </div>
            <button type="button" @click="showGenerateModal = true" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg shadow-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Hitung Estimasi Otomatis') }}
            </button>
        </div>
    </x-slot>

    <div x-data="{
        showGenerateModal: false,
        showStatusModal: false,
        activeItem: {},
        statusAction: '',
        newStatus: 'validated',
        statusNotes: '',

        openStatusModal(item, url) {
            this.activeItem = Object.assign({}, item);
            this.statusAction = url;
            this.newStatus = item.status === 'draft' ? 'validated' : item.status;
            this.statusNotes = '';
            this.showStatusModal = true;
        }
    }" class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">





            <!-- 1. KPI Cards Ringkasan Estimasi -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Total Estimasi Tangkapan') }}</p>
                    <p class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400 mt-1">
                        {{ number_format($counts['total_estimated_kg'], 2, ',', '.') }} <span class="text-sm font-medium text-gray-500">kg</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($counts['total_estimated_kg'] / 1000, 2, ',', '.') }} ton populasi perikanan</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Tangkapan Tersampel (Riil)') }}</p>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                        {{ number_format($counts['total_sampled_kg'], 2, ',', '.') }} <span class="text-sm font-medium text-gray-500">kg</span>
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Basis data observasi sampling enumerator</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Rata-rata Raising Factor') }}</p>
                    <p class="text-2xl font-extrabold text-amber-500 mt-1">
                        {{ number_format($counts['avg_raising_factor'], 4, ',', '.') }}x
                    </p>
                    <p class="text-xs text-gray-500 mt-1">Faktor ekstrapolasi sampel ke populasi armada</p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">{{ __('Status Verifikasi') }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                            {{ $counts['validated_count'] }} Valid
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            {{ $counts['draft_count'] }} Draf
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                            {{ $counts['rejected_count'] }} Ditolak
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Total {{ $counts['total_estimations'] }} baris estimasi</p>
                </div>
            </div>

            <!-- 2. Form Filter -->
            <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
                <form method="GET" action="{{ route('analysis.estimations.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label for="estimation_filter_year" class="sr-only">{{ __('Pilih Tahun') }}</label>
                        <select id="estimation_filter_year" name="year" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Tahun') }}</option>
                            @for ($y = 2026; $y >= 2024; $y--)
                                <option value="{{ $y }}" {{ ($filters['year'] ?? 2026) == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    <div>
                        <label for="estimation_filter_month" class="sr-only">{{ __('Pilih Bulan') }}</label>
                        <select id="estimation_filter_month" name="month" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Bulan') }}</option>
                            @foreach ([1=>'Jan', 2=>'Feb', 3=>'Mar', 4=>'Apr', 5=>'Mei', 6=>'Jun', 7=>'Jul', 8=>'Agu', 9=>'Sep', 10=>'Okt', 11=>'Nov', 12=>'Des'] as $num => $name)
                                <option value="{{ $num }}" {{ ($filters['month'] ?? '') == $num ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estimation_filter_landing_site" class="sr-only">{{ __('Pilih Pangkalan / TPI') }}</label>
                        <select id="estimation_filter_landing_site" name="landing_site_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Pangkalan / TPI') }}</option>
                            @foreach ($landingSites as $site)
                                <option value="{{ $site->id }}" {{ ($filters['landing_site_id'] ?? '') == $site->id ? 'selected' : '' }}>{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estimation_filter_fishing_gear" class="sr-only">{{ __('Pilih Alat Tangkap') }}</label>
                        <select id="estimation_filter_fishing_gear" name="fishing_gear_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Alat Tangkap') }}</option>
                            @foreach ($gears as $gear)
                                <option value="{{ $gear->id }}" {{ ($filters['fishing_gear_id'] ?? '') == $gear->id ? 'selected' : '' }}>{{ $gear->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estimation_filter_fish_species" class="sr-only">{{ __('Pilih Spesies Ikan') }}</label>
                        <select id="estimation_filter_fish_species" name="fish_species_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Spesies Ikan') }}</option>
                            @foreach ($species as $sp)
                                <option value="{{ $sp->id }}" {{ ($filters['fish_species_id'] ?? '') == $sp->id ? 'selected' : '' }}>{{ $sp->local_name_id ?: $sp->scientific_name }} ({{ $sp->fao_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="estimation_filter_status" class="sr-only">{{ __('Pilih Status Validasi') }}</label>
                        <select id="estimation_filter_status" name="status" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Status Validasi') }}</option>
                            <option value="validated" {{ ($filters['status'] ?? '') === 'validated' ? 'selected' : '' }}>{{ __('Validated') }}</option>
                            <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>{{ __('Draft') }}</option>
                            <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                        </select>
                    </div>

                    <div class="col-span-full flex justify-end gap-2 mt-2">
                        <a href="{{ route('analysis.estimations.index') }}" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">{{ __('Reset') }}</a>
                        <button type="submit" class="px-5 py-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 rounded-lg text-sm font-semibold hover:bg-gray-800 transition">{{ __('Filter Data') }}</button>
                    </div>
                </form>
            </div>

            <!-- 3. Tabel Data Estimasi -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-600 dark:text-gray-300 font-semibold text-xs uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-3 text-center w-12">#</th>
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-left">Wilayah & Pangkalan</th>
                                <th class="px-4 py-3 text-left">Alat & Spesies</th>
                                <th class="px-4 py-3 text-right">Sampel (kg)</th>
                                <th class="px-4 py-3 text-center">Raising Factor</th>
                                <th class="px-4 py-3 text-right">Estimasi (kg)</th>
                                <th class="px-4 py-3 text-right">Trip & CPUE</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse ($estimations as $index => $item)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition">
                                    <td class="px-4 py-3 text-center font-mono text-gray-400 text-xs">{{ $estimations->firstItem() + $index }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                        {{ DateTime::createFromFormat('!m', $item->month)->format('M') }} {{ $item->year }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item->landingSite->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->regency->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $item->fishSpecies->local_name_id ?? $item->fishSpecies->scientific_name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->fishingGear->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-300">
                                        {{ number_format($item->sampled_catch_kg, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-amber-600 dark:text-amber-400">
                                        {{ number_format($item->raising_factor, 4, ',', '.') }}x
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ number_format($item->estimated_catch_kg, 2, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="text-xs text-gray-900 dark:text-white font-medium">{{ $item->estimated_effort_trips }} trips</div>
                                        <div class="text-xs text-gray-500">{{ number_format($item->cpue, 2, ',', '.') }} kg/trip</div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($item->status === 'validated')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">
                                                ✓ Validated
                                            </span>
                                        @elseif ($item->status === 'draft')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                Draf
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300">
                                                Ditolak
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button" @click="openStatusModal({{ json_encode($item) }}, '{{ route('analysis.estimations.update-status', $item) }}')" class="p-1.5 text-gray-600 hover:text-indigo-600 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition" title="Ubah Status Validasi">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </button>

                                            @if ($item->status !== 'validated')
                                                <form method="POST" action="{{ route('analysis.estimations.destroy', $item) }}" onsubmit="return confirm('Hapus baris estimasi ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg transition" title="Hapus">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                        {{ __('Tidak ada data estimasi yang sesuai filter.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination & Dropdown Baris Data --}}
                <x-pagination :paginator="$estimations" />
            </div>

        </div>

        <!-- 4. Modal Generate Estimasi Otomatis -->
        <div x-show="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="showGenerateModal = false" class="bg-white dark:bg-gray-800 w-full max-w-lg rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>⚡</span>
                        <span>{{ __('Hitung Otomatis Estimasi Tangkapan') }}</span>
                    </h3>
                    <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
                </div>

                <form method="POST" action="{{ route('analysis.estimations.generate') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Tahun Sasaran *') }}</label>
                        <select name="year" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Tahun') }}</option>
                            <option value="2026" selected>2026</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Bulan (Opsional, kosongkan untuk 1 tahun penuh)') }}</label>
                        <select name="month" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Bulan (Semua Bulan)') }}</option>
                            @foreach ([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Pangkalan / TPI (Opsional)') }}</label>
                        <select name="landing_site_id" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Pangkalan / TPI') }}</option>
                            @foreach ($landingSites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="p-3 rounded-lg bg-indigo-50 dark:bg-indigo-950/30 text-indigo-800 dark:text-indigo-300 text-xs leading-relaxed">
                        Metode kalkulasi mengekstrak data tangkapan observasi lapangan (sampel), mengagregasi total trip aktif pada stratum yang sama, serta menghitung Raising Factor ($R = N / n$) dan taksiran total tangkapan. Seluruh hasil baru akan disimpan dengan status <strong>Draf</strong> untuk diverifikasi verifikator.
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t dark:border-gray-700">
                        <button type="button" @click="showGenerateModal = false" class="px-4 py-2 border rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">{{ __('Batal') }}</button>
                        <button type="submit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg text-sm transition">{{ __('Mulai Hitung') }}</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 5. Modal Ubah Status Validasi -->
        <div x-show="showStatusModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm" x-cloak>
            <div @click.away="showStatusModal = false" class="bg-white dark:bg-gray-800 w-full max-w-md rounded-2xl shadow-xl overflow-hidden p-6 space-y-4">
                <div class="flex items-center justify-between border-b pb-3 dark:border-gray-700">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>🛡️</span>
                        <span>{{ __('Verifikasi Estimasi Tangkapan') }}</span>
                    </h3>
                    <button @click="showStatusModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">&times;</button>
                </div>

                <form method="POST" :action="statusAction" class="space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Status Verifikasi') }} *</label>
                        <select name="status" x-model="newStatus" required class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                            <option value="">{{ __('Pilih Status Verifikasi') }}</option>
                            <option value="validated">{{ __('Validated (Setujui Resmi)') }}</option>
                            <option value="draft">{{ __('Draft (Draf)') }}</option>
                            <option value="rejected">{{ __('Rejected (Tolak)') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">{{ __('Catatan Verifikator') }}</label>
                        <textarea name="notes" x-model="statusNotes" rows="3" class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700" placeholder="{{ __('Masukkan catatan verifikasi (alasan persetujuan/penolakan)...') }}"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t dark:border-gray-700">
                        <button type="button" @click="showStatusModal = false" class="px-4 py-2 border rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">{{ __('Batal') }}</button>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-sm transition">{{ __('Simpan Verifikasi') }}</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
