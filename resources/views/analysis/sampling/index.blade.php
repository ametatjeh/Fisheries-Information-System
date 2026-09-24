<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>🔬</span>
            <span>{{ __('Analisis: Sampling & Pemantauan Biologi Perikanan (Fish Morphometrics & Sampling)') }}</span>
        </div>
    </x-slot>

<div class="space-y-6">

    {{-- Banner Header --}}
    <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
        <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">🔬</div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
            <div class="flex-1 max-w-4xl">
                <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                    <span>🧬</span>
                    <span>{{ __('Analisis Biologi & Pengukuran Morfometrik') }}</span>
                </div>
                <h2 class="text-xl font-bold tracking-tight flex items-center gap-2">
                    <span>🔬</span>
                    <span>{{ __('Sampling & Struktur Ukuran Ikan') }}</span>
                </h2>
                <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                    {{ __('Pengelolaan program rencana sampling (Sampling Plans), pencatatan batch sampel pendaratan, serta inspeksi morfometri panjang (FL/TL), bobot, dan Tingkat Kematangan Gonad (TKG) spesimen.') }}
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                        onclick="openCreateSampleModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-ocean-600 hover:bg-ocean-500 text-white text-sm font-semibold shadow-md transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ __('Tambah Sampel') }}</span>
                </button>
                <button type="button"
                        onclick="openCreatePlanModal()"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white text-sm font-semibold backdrop-blur-sm border border-white/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <span>{{ __('Rencana Sampling Baru') }}</span>
                </button>
            </div>
        </div>

        {{-- 5 Kartu Metrik Sampling --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mt-6 pt-6 border-t border-white/10 relative z-10">
            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="text-xs text-ocean-200 font-medium">{{ __('Program Sampling') }}</div>
                <div class="text-xl sm:text-2xl font-bold text-white mt-1">{{ number_format($totalPlans) }}</div>
                <div class="text-xs text-emerald-400 mt-0.5 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    {{ $activePlans }} {{ __('Program Aktif') }}
                </div>
            </div>

            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="text-xs text-ocean-200 font-medium">{{ __('Batch Sampel') }}</div>
                <div class="text-xl sm:text-2xl font-bold text-white mt-1">{{ number_format($totalSamples) }}</div>
                <div class="text-xs text-ocean-300 mt-0.5">{{ __('Tersebar di Pelabuhan') }}</div>
            </div>

            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="text-xs text-ocean-200 font-medium">{{ __('Spesimen Terukur') }}</div>
                <div class="text-xl sm:text-2xl font-bold text-white mt-1">{{ number_format($totalSpecimens) }}</div>
                <div class="text-xs text-cyan-300 mt-0.5">{{ __('Individu Ikan') }}</div>
            </div>

            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm">
                <div class="text-xs text-ocean-200 font-medium">{{ __('Rata-rata FL') }}</div>
                <div class="text-xl sm:text-2xl font-bold text-white mt-1">{{ number_format($avgForkLength, 1) }} <span class="text-xs font-normal text-ocean-200">cm</span></div>
                <div class="text-xs text-sky-300 mt-0.5">{{ __('Panjang Cagak') }}</div>
            </div>

            <div class="p-3.5 rounded-xl bg-white/5 border border-white/10 backdrop-blur-sm col-span-2 sm:col-span-1">
                <div class="text-xs text-ocean-200 font-medium">{{ __('Matang Gonad (TKG IV-V)') }}</div>
                <div class="text-xl sm:text-2xl font-bold text-white mt-1">{{ $matureRatio }}%</div>
                <div class="text-xs text-amber-300 mt-0.5">{{ __('Siap Memijah / Spawning') }}</div>
            </div>
        </div>
    </div>



    @if (isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <div class="font-semibold mb-1">{{ __('Terjadi kesalahan input:') }}</div>
            <ul class="list-disc list-inside space-y-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Tab Navigasi & Filter Toolbar --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('analysis.sampling.index', ['tab' => 'samples']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-2 {{ $tab === 'samples' ? 'bg-ocean-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    <span>🐟</span>
                    <span>{{ __('Daftar Batch Sampel & Morfometrik') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'samples' ? 'bg-ocean-700 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $samples->total() }}</span>
                </a>
                <a href="{{ route('analysis.sampling.index', ['tab' => 'plans']) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-200 flex items-center gap-2 {{ $tab === 'plans' ? 'bg-ocean-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                    <span>📋</span>
                    <span>{{ __('Rencana Program Sampling') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'plans' ? 'bg-ocean-700 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $plans->total() }}</span>
                </a>
            </div>

            <div class="text-xs text-slate-500">
                @if ($tab === 'samples')
                    {{ __('Menampilkan batch pengambilan sampel biologis di pelabuhan/TPI.') }}
                @else
                    {{ __('Menampilkan target dan realisasi program sampling WPPNRI.') }}
                @endif
            </div>
        </div>

        {{-- Filter Toolbar --}}
        <form method="GET" action="{{ route('analysis.sampling.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="relative">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="{{ $tab === 'samples' ? __('Cari kode/trip/kapal...') : __('Cari kode/judul program...') }}"
                       class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white transition" />
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <div>
                <select name="landing_site_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white transition">
                    <option value="">{{ __('Semua Pelabuhan / TPI') }}</option>
                    @foreach ($landingSites as $site)
                        <option value="{{ $site->id }}" {{ request('landing_site_id') == $site->id ? 'selected' : '' }}>
                            {{ $site->name }} ({{ $site->site_type }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($tab === 'samples')
                <div>
                    <select name="sampling_plan_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white transition">
                        <option value="">{{ __('Semua Program Sampling') }}</option>
                        @foreach ($plansList as $p)
                            <option value="{{ $p->id }}" {{ request('sampling_plan_id') == $p->id ? 'selected' : '' }}>
                                {{ $p->code }} - {{ Str::limit($p->title, 25) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div x-data="{
                    open: false,
                    search: '',
                    options: [],
                    loading: false,
                    selectedText: '{{ request('species_text', '') }}',
                    
                    async fetchOptions() {
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ route('master.species.search') }}?q=${encodeURIComponent(this.search)}`);
                            const json = await res.json();
                            this.options = json.data || [];
                        } catch(e) { this.options = []; }
                        this.loading = false;
                    },
                    selectOpt(opt) {
                        $refs.hiddenInput.value = opt ? opt.id : '';
                        $refs.hiddenText.value = opt ? `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}` : '';
                        this.selectedText = opt ? `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}` : '';
                        this.open = false;
                    }
                }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                    <input type="hidden" name="species_id" x-ref="hiddenInput" value="{{ request('species_id') }}">
                    <input type="hidden" name="species_text" x-ref="hiddenText" value="{{ request('species_text') }}">
                    
                    <div @click="open = !open" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus-within:ring-2 focus-within:ring-ocean-500 focus-within:bg-white transition cursor-pointer flex justify-between items-center h-[34px]">
                        <span x-text="selectedText || '{{ __('Semua Spesies Ikan') }}'" class="truncate" :class="selectedText ? 'text-slate-900' : 'text-slate-500'"></span>
                        <div class="flex items-center gap-1">
                            <button type="button" x-show="selectedText" @click.stop="selectOpt(null)" class="text-slate-400 hover:text-rose-500">✕</button>
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
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
            @else
                <div x-data="{
                    open: false,
                    search: '',
                    options: [],
                    loading: false,
                    selectedText: '{{ request('species_text', '') }}',
                    
                    async fetchOptions() {
                        this.loading = true;
                        try {
                            const res = await fetch(`{{ route('master.species.search') }}?q=${encodeURIComponent(this.search)}`);
                            const json = await res.json();
                            this.options = json.data || [];
                        } catch(e) { this.options = []; }
                        this.loading = false;
                    },
                    selectOpt(opt) {
                        $refs.hiddenInput.value = opt ? opt.id : '';
                        $refs.hiddenText.value = opt ? `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}` : '';
                        this.selectedText = opt ? `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}` : '';
                        this.open = false;
                    }
                }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                    <input type="hidden" name="species_id" x-ref="hiddenInput" value="{{ request('species_id') }}">
                    <input type="hidden" name="species_text" x-ref="hiddenText" value="{{ request('species_text') }}">
                    
                    <div @click="open = !open" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus-within:ring-2 focus-within:ring-ocean-500 focus-within:bg-white transition cursor-pointer flex justify-between items-center h-[34px]">
                        <span x-text="selectedText || '{{ __('Target Spesies') }}'" class="truncate" :class="selectedText ? 'text-slate-900' : 'text-slate-500'"></span>
                        <div class="flex items-center gap-1">
                            <button type="button" x-show="selectedText" @click.stop="selectOpt(null)" class="text-slate-400 hover:text-rose-500">✕</button>
                            <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
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

                <div>
                    <select name="status" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white transition">
                        <option value="">{{ __('Semua Status Rencana') }}</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>{{ __('Aktif (Active)') }}</option>
                        <option value="planned" {{ request('status') === 'planned' ? 'selected' : '' }}>{{ __('Direncanakan (Planned)') }}</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>{{ __('Selesai (Completed)') }}</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>{{ __('Dibatalkan (Cancelled)') }}</option>
                    </select>
                </div>
            @endif

            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 py-2 px-3 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-semibold transition">
                    {{ __('Terapkan') }}
                </button>
                <a href="{{ route('analysis.sampling.index', ['tab' => $tab]) }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition">
                    {{ __('Reset') }}
                </a>
            </div>
        </form>
    </div>

    {{-- KONTEN TAB 1: DAFTAR BATCH SAMPEL --}}
    @if ($tab === 'samples')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="py-3.5 px-4">{{ __('Kode Sampel') }}</th>
                            <th class="py-3.5 px-4">{{ __('Tanggal & TPI') }}</th>
                            <th class="py-3.5 px-4">{{ __('Program / Trip Kapal') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Spesimen') }}</th>
                            <th class="py-3.5 px-4 text-right">{{ __('Berat Sampel') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Rata-rata FL') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Komposisi Spesies') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse ($samples as $sample)
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="py-3.5 px-4 font-mono font-bold text-ocean-700">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-ocean-50 text-ocean-800 border border-ocean-200">
                                        <span>🔬</span>
                                        <span>{{ $sample->sample_code }}</span>
                                    </div>
                                    @if ($sample->enumerator)
                                        <div class="text-[11px] font-sans font-normal text-slate-400 mt-1 flex items-center gap-1">
                                            <span>👤</span>
                                            <span>{{ $sample->enumerator->name }}</span>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-slate-900">{{ $sample->sample_date ? $sample->sample_date->format('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                        <span>📍</span>
                                        <span>{{ $sample->landingSite ? $sample->landingSite->name : '-' }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    @if ($sample->samplingPlan)
                                        <div class="text-slate-900 font-medium line-clamp-1" title="{{ $sample->samplingPlan->title }}">
                                            {{ $sample->samplingPlan->title }}
                                        </div>
                                        <div class="text-[10px] text-ocean-600 font-mono mt-0.5">
                                            {{ $sample->samplingPlan->code }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Sampling Mandiri') }}</span>
                                    @endif

                                    @if ($sample->fishingTrip)
                                        <div class="text-[11px] text-slate-600 font-mono mt-1 flex items-center gap-1">
                                            <span>🚢</span>
                                            <span>{{ $sample->fishingTrip->trip_number }}</span>
                                            @if ($sample->fishingTrip->vessel)
                                                <span class="text-slate-400 font-sans">({{ $sample->fishingTrip->vessel->name }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">
                                        {{ $sample->total_specimens }} {{ __('ekor') }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-right font-mono font-semibold text-slate-900">
                                    {{ number_format($sample->total_weight_kg, 2) }} kg
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    @if ($sample->avg_fl)
                                        <span class="font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                            {{ number_format($sample->avg_fl, 1) }} cm
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex flex-wrap items-center justify-center gap-1">
                                        @php
                                            $sampleSpecies = $sample->biologicalMeasurements->pluck('fishSpecies')->filter()->unique('id');
                                        @endphp
                                        @forelse ($sampleSpecies as $sp)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 border border-slate-200" title="{{ $sp->scientific_name }}">
                                                {{ $sp->fao_code ?: Str::limit($sp->local_name_id, 10) }}
                                            </span>
                                        @empty
                                            <span class="text-slate-400 text-[11px] italic">{{ __('Belum diukur') }}</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        {{-- Tombol Detail Morfometrik Biologis --}}
                                        <button type="button"
                                                onclick="openSpecimensModal({{ $sample->id }})"
                                                class="px-2.5 py-1.5 rounded-lg bg-ocean-50 text-ocean-700 hover:bg-ocean-100 border border-ocean-200 text-xs font-semibold flex items-center gap-1 transition"
                                                title="{{ __('Lihat & Tambah Spesimen Morfometrik') }}">
                                            <span>🔬</span>
                                            <span>{{ __('Morfometrik') }}</span>
                                        </button>

                                        {{-- Tombol Ubah --}}
                                        <button type="button"
                                                onclick="openEditSampleModal({{ json_encode($sample) }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-ocean-600 hover:bg-slate-100 transition"
                                                title="{{ __('Ubah Sampel') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <button type="button"
                                                onclick="confirmDeleteSample({{ $sample->id }}, '{{ $sample->sample_code }}')"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="{{ __('Hapus Sampel') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">🐟</div>
                                    <div class="text-sm font-semibold text-slate-600">{{ __('Belum ada data batch sampel yang tercatat') }}</div>
                                    <div class="text-xs mt-1">{{ __('Silakan tambah sampel baru menggunakan tombol di atas.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$samples" />
        </div>
    @endif

    {{-- KONTEN TAB 2: RENCANA PROGRAM SAMPLING --}}
    @if ($tab === 'plans')
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs uppercase tracking-wider font-semibold">
                            <th class="py-3.5 px-4">{{ __('Kode & Program') }}</th>
                            <th class="py-3.5 px-4">{{ __('Lokasi TPI & Target Spesies') }}</th>
                            <th class="py-3.5 px-4">{{ __('Periode Pelaksanaan') }}</th>
                            <th class="py-3.5 px-4">{{ __('Target vs Realisasi') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Metode') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Status') }}</th>
                            <th class="py-3.5 px-4 text-center">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse ($plans as $plan)
                            @php
                                $progressPercent = $plan->target_sample_size > 0
                                    ? min(100, round(($plan->measured_specimens_count / $plan->target_sample_size) * 100))
                                    : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/75 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-ocean-50 text-ocean-800 border border-ocean-200 font-mono font-bold text-xs mb-1">
                                        <span>📋</span>
                                        <span>{{ $plan->code }}</span>
                                    </div>
                                    <div class="font-semibold text-slate-900 text-sm">{{ $plan->title }}</div>
                                    @if ($plan->notes)
                                        <div class="text-[11px] text-slate-500 mt-1 line-clamp-1 italic">{{ $plan->notes }}</div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-900 flex items-center gap-1">
                                        <span>📍</span>
                                        <span>{{ $plan->landingSite ? $plan->landingSite->name : '-' }}</span>
                                    </div>
                                    <div class="text-[11px] text-ocean-700 mt-0.5 flex items-center gap-1 font-semibold">
                                        <span>🐟</span>
                                        <span>{{ $plan->targetSpecies ? $plan->targetSpecies->local_name_id : __('Multi Spesies') }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">{{ $plan->start_date ? $plan->start_date->format('d M Y') : '-' }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">s/d {{ $plan->end_date ? $plan->end_date->format('d M Y') : '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="font-semibold text-slate-900">{{ number_format($plan->measured_specimens_count ?: 0) }} / {{ number_format($plan->target_sample_size) }} ekor</span>
                                        <span class="font-bold text-ocean-700">{{ $progressPercent }}%</span>
                                    </div>
                                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-gradient-to-r from-ocean-500 to-emerald-500 h-2 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-1">{{ $plan->samples_count }} {{ __('batch sampel tercatat') }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 capitalize">
                                        {{ $plan->sampling_method }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    @if ($plan->status === 'active')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            {{ __('Aktif') }}
                                        </span>
                                    @elseif ($plan->status === 'planned')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-50 text-sky-700 border border-sky-200">
                                            {{ __('Rencana') }}
                                        </span>
                                    @elseif ($plan->status === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-300">
                                            {{ __('Selesai') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                                            {{ __('Batal') }}
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button"
                                                onclick="openEditPlanModal({{ json_encode($plan) }})"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-ocean-600 hover:bg-slate-100 transition"
                                                title="{{ __('Ubah Rencana') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                        <button type="button"
                                                onclick="confirmDeletePlan({{ $plan->id }}, '{{ $plan->code }}')"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-rose-600 hover:bg-rose-50 transition"
                                                title="{{ __('Hapus Rencana') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">📋</div>
                                    <div class="text-sm font-semibold text-slate-600">{{ __('Belum ada rencana program sampling yang terdaftar') }}</div>
                                    <div class="text-xs mt-1">{{ __('Klik tombol "Rencana Sampling Baru" untuk membuat program baru.') }}</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$plans" />
        </div>
    @endif
</div>

{{-- MODAL DETAIL SPESIMEN & FORM MORFOMETRIK (MODAL SPESIMENS) --}}
<div id="modalSpecimens" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            {{-- Modal Header --}}
            <div class="p-5 sm:p-6 bg-gradient-to-r from-ocean-800 to-ocean-950 text-white flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl">🔬</span>
                        <h2 class="text-lg font-bold" id="specimenModalTitle">{{ __('Pengukuran Morfometrik Biologi Ikan') }}</h2>
                    </div>
                    <div class="text-xs text-ocean-200 mt-1 flex flex-wrap items-center gap-3" id="specimenModalMeta">
                        <!-- Loaded dynamically -->
                    </div>
                </div>
                <button type="button" onclick="closeSpecimensModal()" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 space-y-6">
                {{-- Form Tambah Spesimen Cepat --}}
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200">
                    <div class="font-semibold text-xs text-slate-800 uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span>➕</span>
                        <span>{{ __('Input Pengukuran Spesimen Individu') }}</span>
                    </div>

                    <form id="formAddSpecimen" method="POST" action="" class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2.5">
                        @csrf
                        <div class="col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Spesies Ikan *') }}</label>
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
                                    $refs.hiddenInput.value = opt.id;
                                    this.selectedText = `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}`;
                                    this.open = false;
                                }
                            }" x-init="$watch('open', v => { if(v && options.length===0) fetchOptions(); })" class="relative">
                                <input type="hidden" name="fish_species_id" x-ref="hiddenInput" required>
                                
                                <div @click="open = !open" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus-within:ring-2 focus-within:ring-ocean-500 cursor-pointer flex justify-between items-center h-[28px]">
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

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1" title="Fork Length / Panjang Cagak">{{ __('FL (cm)') }}</label>
                            <input type="number" step="0.1" name="fork_length_cm" placeholder="45.0" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1" title="Total Length / Panjang Total">{{ __('TL (cm)') }}</label>
                            <input type="number" step="0.1" name="total_length_cm" placeholder="48.5" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Berat (g)') }}</label>
                            <input type="number" step="1" name="weight_gram" placeholder="1850" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500" />
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">{{ __('Sex') }}</label>
                            <select name="sex" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500">
                                <option value="undetermined">? (Belum)</option>
                                <option value="female">♀ Betina</option>
                                <option value="male">♂ Jantan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1" title="Tingkat Kematangan Gonad 1-5">{{ __('TKG (1-5)') }}</label>
                            <select name="gonad_maturity_stage" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-ocean-500">
                                <option value="">-</option>
                                <option value="1">I (Dara)</option>
                                <option value="2">II (Berkembang)</option>
                                <option value="3">III (Matang Awal)</option>
                                <option value="4">IV (Matang)</option>
                                <option value="5">V (Salin/Spent)</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2 px-3 bg-ocean-600 hover:bg-ocean-700 text-white font-semibold rounded-lg text-xs transition">
                                {{ __('+ Tambah') }}
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Tabel Daftar Spesimen Terukur --}}
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <div class="max-h-80 overflow-y-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead class="bg-slate-50 border-b border-slate-200 sticky top-0 text-slate-600 uppercase font-semibold text-[11px]">
                                <tr>
                                    <th class="py-2.5 px-3">#</th>
                                    <th class="py-2.5 px-3">{{ __('Spesies') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('FL (cm)') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('TL (cm)') }}</th>
                                    <th class="py-2.5 px-3 text-right">{{ __('Berat (g)') }}</th>
                                    <th class="py-2.5 px-3 text-center">{{ __('Sex') }}</th>
                                    <th class="py-2.5 px-3 text-center">{{ __('TKG') }}</th>
                                    <th class="py-2.5 px-3">{{ __('Catatan / Lambung') }}</th>
                                    <th class="py-2.5 px-3 text-center">{{ __('Aksi') }}</th>
                                </tr>
                            </thead>
                            <tbody id="specimenTableBody" class="divide-y divide-slate-100 text-slate-700">
                                <tr>
                                    <td colspan="9" class="py-8 text-center text-slate-400">{{ __('Memuat data spesimen...') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end">
                <button type="button" onclick="closeSpecimensModal()" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-semibold transition">
                    {{ __('Tutup') }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / UBAH BATCH SAMPEL --}}
<div id="modalSampleForm" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-5 bg-gradient-to-r from-ocean-800 to-ocean-950 text-white flex items-center justify-between">
                <h3 class="text-base font-bold flex items-center gap-2" id="sampleModalTitle">
                    <span>🔬</span>
                    <span>{{ __('Tambah Batch Sampel Baru') }}</span>
                </h3>
                <button type="button" onclick="closeSampleModal()" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="formSample" method="POST" action="{{ route('analysis.sampling.samples.store') }}" class="p-6 space-y-4">
                @csrf
                <div id="sampleMethodPut"></div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Kode Batch Sampel *') }}</label>
                    <input type="text"
                           id="input_sample_code"
                           name="sample_code"
                           required
                           placeholder="misal SMP-202609-0001"
                           class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Rencana Program Induk') }}</label>
                        <select id="input_sampling_plan_id" name="sampling_plan_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            <option value="">{{ __('Non-Program / Mandiri') }}</option>
                            @foreach ($plansList as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} - {{ Str::limit($p->title, 20) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Trip Penangkapan (Opsional)') }}</label>
                        <select id="input_fishing_trip_id" name="fishing_trip_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            <option value="">{{ __('Tidak Terikat Trip') }}</option>
                            @foreach ($tripsList as $trip)
                                <option value="{{ $trip->id }}">{{ $trip->trip_number }} - {{ $trip->vessel ? $trip->vessel->name : '-' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Lokasi Pelabuhan / TPI *') }}</label>
                        <select id="input_landing_site_id" name="landing_site_id" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            @foreach ($landingSites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Sampling *') }}</label>
                        <input type="date"
                               id="input_sample_date"
                               name="sample_date"
                               required
                               value="{{ date('Y-m-d') }}"
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Petugas Enumerator') }}</label>
                        <select id="input_enumerator_id" name="enumerator_id" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            @foreach ($enumerators as $user)
                                <option value="{{ $user->id }}" {{ $user->id == auth()->id() ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Berat Sampel Keranjang (kg)') }}</label>
                        <input type="number"
                               step="0.01"
                               id="input_total_weight_kg"
                               name="total_weight_kg"
                               placeholder="0.00"
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Catatan Pengambilan Sampel') }}</label>
                    <textarea id="input_notes" name="notes" rows="2" placeholder="Catatan kondisi ikan, es, atau palka..." class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closeSampleModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        {{ __('Simpan Sampel') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TAMBAH / UBAH RENCANA SAMPLING (SAMPLING PLAN) --}}
<div id="modalPlanForm" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
            <div class="p-5 bg-gradient-to-r from-ocean-800 to-ocean-950 text-white flex items-center justify-between">
                <h3 class="text-base font-bold flex items-center gap-2" id="planModalTitle">
                    <span>📋</span>
                    <span>{{ __('Rencana Program Sampling Baru') }}</span>
                </h3>
                <button type="button" onclick="closePlanModal()" class="text-white/70 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <form id="formPlan" method="POST" action="{{ route('analysis.sampling.plans.store') }}" class="p-6 space-y-4">
                @csrf
                <div id="planMethodPut"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Kode Rencana *') }}</label>
                        <input type="text"
                               id="input_plan_code"
                               name="code"
                               required
                               placeholder="SMP-PLAN-2026-001"
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Nama / Judul Program *') }}</label>
                        <input type="text"
                               id="input_plan_title"
                               name="title"
                               required
                               placeholder="Program Pemantauan Biologi..."
                               class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Lokasi TPI Sasaran *') }}</label>
                        <select id="input_plan_landing_site_id" name="landing_site_id" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            @foreach ($landingSites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
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
                                document.getElementById('input_plan_target_species_id').value = opt ? opt.id : '';
                                this.selectedText = opt ? `[${opt.fao_code || '-'}] ${opt.local_name_id || '-'}` : '';
                                this.open = false;
                            }
                        }" x-init="
                            $watch('open', v => { if(v && options.length===0) fetchOptions(); });
                            // Watch for changes on the hidden input (e.g. from edit modal script)
                            const inputElem = document.getElementById('input_plan_target_species_id');
                            const observer = new MutationObserver((mutations) => {
                                mutations.forEach((mutation) => {
                                    if(mutation.type === 'attributes' && mutation.attributeName === 'data-text') {
                                        selectedText = inputElem.getAttribute('data-text');
                                    }
                                });
                            });
                            observer.observe(inputElem, {attributes: true});
                        " class="relative">
                            <input type="hidden" id="input_plan_target_species_id" name="target_species_id" value="">
                            
                            <div @click="open = !open" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus-within:ring-2 focus-within:ring-ocean-500 focus-within:bg-white transition cursor-pointer flex justify-between items-center h-[34px]">
                                <span x-text="selectedText || '{{ __('Semua / Multi Spesies') }}'" class="truncate" :class="selectedText ? 'text-slate-900' : 'text-slate-500'"></span>
                                <div class="flex items-center gap-1">
                                    <button type="button" x-show="selectedText" @click.stop="selectOpt(null)" class="text-slate-400 hover:text-rose-500">✕</button>
                                    <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
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
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Mulai *') }}</label>
                        <input type="date" id="input_plan_start_date" name="start_date" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Tanggal Selesai *') }}</label>
                        <input type="date" id="input_plan_end_date" name="end_date" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Target Spesimen *') }}</label>
                        <input type="number" id="input_plan_target_sample_size" name="target_sample_size" required value="100" min="1" class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Metode Sampling *') }}</label>
                        <select id="input_plan_sampling_method" name="sampling_method" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            <option value="stratified">Stratified (Bertingkat)</option>
                            <option value="random">Random (Acak Sederhana)</option>
                            <option value="systematic">Systematic (Sistematik)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Status Rencana *') }}</label>
                        <select id="input_plan_status" name="status" required class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white">
                            <option value="planned">Direncanakan (Planned)</option>
                            <option value="active" selected>Aktif Berjalan (Active)</option>
                            <option value="completed">Selesai (Completed)</option>
                            <option value="cancelled">Dibatalkan (Cancelled)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">{{ __('Catatan / Metodologi') }}</label>
                    <textarea id="input_plan_notes" name="notes" rows="2" placeholder="Catatan metodologi pemantauan..." class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-ocean-500 focus:bg-white"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button type="button" onclick="closePlanModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        {{ __('Simpan Rencana') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
<div id="modalDelete" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm">
    <div class="min-h-screen px-4 py-8 flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md p-6 text-center animate-in fade-in zoom-in-95 duration-200">
            <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 mx-auto flex items-center justify-center text-xl mb-4">
                ⚠️
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-2">{{ __('Konfirmasi Penghapusan Data') }}</h3>
            <p class="text-xs text-slate-500 mb-6" id="deleteModalMessage">
                {{ __('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.') }}
            </p>

            <form id="formDelete" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold transition">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-semibold shadow-sm transition">
                        {{ __('Ya, Hapus Data') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // --- BATCH SAMPLE MODAL LOGIC ---
    function openCreateSampleModal() {
        document.getElementById('sampleModalTitle').innerHTML = '<span>🔬</span><span>{{ __("Tambah Batch Sampel Baru") }}</span>';
        document.getElementById('formSample').action = "{{ route('analysis.sampling.samples.store') }}";
        document.getElementById('sampleMethodPut').innerHTML = '';

        document.getElementById('input_sample_code').value = '';
        document.getElementById('input_sample_code').removeAttribute('readonly');
        document.getElementById('input_sampling_plan_id').value = '';
        document.getElementById('input_fishing_trip_id').value = '';
        document.getElementById('input_landing_site_id').selectedIndex = 0;
        document.getElementById('input_sample_date').value = "{{ date('Y-m-d') }}";
        document.getElementById('input_total_weight_kg').value = '';
        document.getElementById('input_notes').value = '';

        document.getElementById('modalSampleForm').classList.remove('hidden');
    }

    function openEditSampleModal(sample) {
        document.getElementById('sampleModalTitle').innerHTML = '<span>✏️</span><span>{{ __("Ubah Batch Sampel") }}: ' + sample.sample_code + '</span>';
        document.getElementById('formSample').action = "/analysis/sampling/samples/" + sample.id;
        document.getElementById('sampleMethodPut').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('input_sample_code').value = sample.sample_code;
        document.getElementById('input_sample_code').setAttribute('readonly', 'readonly');
        document.getElementById('input_sampling_plan_id').value = sample.sampling_plan_id || '';
        document.getElementById('input_fishing_trip_id').value = sample.fishing_trip_id || '';
        document.getElementById('input_landing_site_id').value = sample.landing_site_id || '';
        document.getElementById('input_sample_date').value = sample.sample_date ? sample.sample_date.substring(0, 10) : '';
        document.getElementById('input_total_weight_kg').value = sample.total_weight_kg || '';
        document.getElementById('input_notes').value = sample.notes || '';

        document.getElementById('modalSampleForm').classList.remove('hidden');
    }

    function closeSampleModal() {
        document.getElementById('modalSampleForm').classList.add('hidden');
    }

    function confirmDeleteSample(id, code) {
        document.getElementById('deleteModalMessage').innerText = 'Apakah Anda yakin ingin menghapus batch sampel ' + code + ' beserta seluruh spesimen yang telah diukur?';
        document.getElementById('formDelete').action = "/analysis/sampling/samples/" + id;
        document.getElementById('modalDelete').classList.remove('hidden');
    }

    // --- SAMPLING PLAN MODAL LOGIC ---
    function openCreatePlanModal() {
        document.getElementById('planModalTitle').innerHTML = '<span>📋</span><span>{{ __("Rencana Program Sampling Baru") }}</span>';
        document.getElementById('formPlan').action = "{{ route('analysis.sampling.plans.store') }}";
        document.getElementById('planMethodPut').innerHTML = '';

        document.getElementById('input_plan_code').value = '';
        document.getElementById('input_plan_code').removeAttribute('readonly');
        document.getElementById('input_plan_title').value = '';
        document.getElementById('input_plan_landing_site_id').selectedIndex = 0;
        document.getElementById('input_plan_target_species_id').value = '';
        document.getElementById('input_plan_start_date').value = "{{ date('Y-m-d') }}";
        document.getElementById('input_plan_end_date').value = "{{ date('Y-m-d', strtotime('+3 months')) }}";
        document.getElementById('input_plan_target_sample_size').value = 100;
        document.getElementById('input_plan_sampling_method').value = 'stratified';
        document.getElementById('input_plan_status').value = 'active';
        document.getElementById('input_plan_notes').value = '';

        document.getElementById('modalPlanForm').classList.remove('hidden');
    }

    function openEditPlanModal(plan) {
        document.getElementById('planModalTitle').innerHTML = '<span>✏️</span><span>{{ __("Ubah Rencana") }}: ' + plan.code + '</span>';
        document.getElementById('formPlan').action = "/analysis/sampling/plans/" + plan.id;
        document.getElementById('planMethodPut').innerHTML = '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('input_plan_code').value = plan.code;
        document.getElementById('input_plan_code').setAttribute('readonly', 'readonly');
        document.getElementById('input_plan_title').value = plan.title;
        document.getElementById('input_plan_landing_site_id').value = plan.landing_site_id;
        document.getElementById('input_plan_target_species_id').value = plan.target_species_id || '';
        if (plan.target_species) {
            document.getElementById('input_plan_target_species_id').setAttribute('data-text', `[${plan.target_species.fao_code || '-'}] ${plan.target_species.local_name_id || '-'}`);
        } else {
            document.getElementById('input_plan_target_species_id').setAttribute('data-text', '');
        }
        document.getElementById('input_plan_start_date').value = plan.start_date ? plan.start_date.substring(0, 10) : '';
        document.getElementById('input_plan_end_date').value = plan.end_date ? plan.end_date.substring(0, 10) : '';
        document.getElementById('input_plan_target_sample_size').value = plan.target_sample_size;
        document.getElementById('input_plan_sampling_method').value = plan.sampling_method;
        document.getElementById('input_plan_status').value = plan.status;
        document.getElementById('input_plan_notes').value = plan.notes || '';

        document.getElementById('modalPlanForm').classList.remove('hidden');
    }

    function closePlanModal() {
        document.getElementById('modalPlanForm').classList.add('hidden');
    }

    function confirmDeletePlan(id, code) {
        document.getElementById('deleteModalMessage').innerText = 'Apakah Anda yakin ingin menghapus rencana program sampling ' + code + '?';
        document.getElementById('formDelete').action = "/analysis/sampling/plans/" + id;
        document.getElementById('modalDelete').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('modalDelete').classList.add('hidden');
    }

    // --- SPECIMENS INSPECT & MEASUREMENT MODAL ---
    function openSpecimensModal(sampleId) {
        document.getElementById('modalSpecimens').classList.remove('hidden');
        document.getElementById('formAddSpecimen').action = "/analysis/sampling/samples/" + sampleId + "/specimens";

        const tbody = document.getElementById('specimenTableBody');
        tbody.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-slate-400">{{ __("Memuat data spesimen morfometrik...") }}</td></tr>';

        fetch('/analysis/sampling/samples/' + sampleId + '/specimens')
            .then(res => res.json())
            .then(data => {
                const sample = data.sample;
                const items = data.measurements;

                document.getElementById('specimenModalTitle').innerText = 'Pengukuran Morfometrik: ' + sample.sample_code;

                const meta = document.getElementById('specimenModalMeta');
                meta.innerHTML = `
                    <span>📍 ${sample.landing_site ? sample.landing_site.name : '-'}</span>
                    <span>📅 ${sample.sample_date ? sample.sample_date.substring(0, 10) : '-'}</span>
                    <span>⚖️ Total: ${sample.total_weight_kg || 0} kg (${sample.total_specimens || 0} ekor)</span>
                    ${sample.fishing_trip ? `<span>🚢 Trip: ${sample.fishing_trip.trip_number}</span>` : ''}
                `;

                if (!items || items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-slate-400">{{ __("Belum ada spesimen terukur pada batch sampel ini. Silakan input pada form di atas.") }}</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                items.forEach((item, index) => {
                    const speciesName = item.fish_species ? `[${item.fish_species.fao_code || '-'}] ${item.fish_species.local_name_id || '-'}` : '-';
                    const sexBadge = item.sex === 'female'
                        ? '<span class="px-2 py-0.5 rounded bg-rose-50 text-rose-700 font-semibold border border-rose-200">♀ Betina</span>'
                        : (item.sex === 'male'
                            ? '<span class="px-2 py-0.5 rounded bg-sky-50 text-sky-700 font-semibold border border-sky-200">♂ Jantan</span>'
                            : '<span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600">? Belum</span>');

                    let tkgBadge = '-';
                    if (item.gonad_maturity_stage) {
                        const tkgClass = (item.gonad_maturity_stage >= 4)
                            ? 'bg-amber-50 text-amber-800 border-amber-300 font-bold'
                            : 'bg-emerald-50 text-emerald-800 border-emerald-200';
                        tkgBadge = `<span class="px-2 py-0.5 rounded border ${tkgClass}">TKG ${item.gonad_maturity_stage}</span>`;
                    }

                    const row = document.createElement('tr');
                    row.className = 'hover:bg-slate-50 transition-colors';
                    row.innerHTML = `
                        <td class="py-2.5 px-3 font-mono font-bold text-slate-500">${item.specimen_number || (index + 1)}</td>
                        <td class="py-2.5 px-3 font-semibold text-slate-900">${speciesName}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-semibold text-emerald-700">${item.fork_length_cm ? item.fork_length_cm + ' cm' : '-'}</td>
                        <td class="py-2.5 px-3 text-right font-mono text-slate-700">${item.total_length_cm ? item.total_length_cm + ' cm' : '-'}</td>
                        <td class="py-2.5 px-3 text-right font-mono font-semibold text-slate-900">${item.weight_gram ? Number(item.weight_gram).toLocaleString() + ' g' : '-'}</td>
                        <td class="py-2.5 px-3 text-center">${sexBadge}</td>
                        <td class="py-2.5 px-3 text-center">${tkgBadge}</td>
                        <td class="py-2.5 px-3 text-slate-500 italic line-clamp-1">${item.notes || (item.stomach_fullness ? 'Lambung: ' + item.stomach_fullness + '/5' : '-')}</td>
                        <td class="py-2.5 px-3 text-center">
                            <form method="POST" action="/analysis/sampling/measurements/${item.id}" onsubmit="return confirm('Hapus spesimen ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded text-rose-500 hover:bg-rose-50 hover:text-rose-700 transition" title="Hapus spesimen">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                </button>
                            </form>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            })
            .catch(err => {
                console.error(err);
                tbody.innerHTML = '<tr><td colspan="9" class="py-8 text-center text-rose-500">{{ __("Gagal memuat data spesimen.") }}</td></tr>';
            });
    }

    function closeSpecimensModal() {
        document.getElementById('modalSpecimens').classList.add('hidden');
    }

    // Auto-open inspect modal if redirected with inspect_sample_id query parameter
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const inspectId = urlParams.get('inspect_sample_id');
        if (inspectId) {
            openSpecimensModal(inspectId);
        }
    });
</script>
</x-app-layout>

