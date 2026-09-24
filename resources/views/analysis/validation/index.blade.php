<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>✅</span>
            <span>{{ __('Analisis: Validasi & Kontrol Mutu Data (Data Verification Pipeline)') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showVerifyModal: false,
        showLogsModal: false,
        verifyItem: {},
        verifyAction: '',
        verifyStatus: 'validated',
        verifyNotes: '',
        rejectionReason: '',
        logsItem: { logs: [] },

        openVerify(item, actionUrl) {
            this.verifyItem = Object.assign({}, item);
            this.verifyAction = actionUrl;
            this.verifyStatus = item.validation_status === 'submitted' ? 'validated' : item.validation_status;
            this.verifyNotes = '';
            this.rejectionReason = item.rejection_reason || '';
            this.showVerifyModal = true;
        },

        openLogs(item) {
            this.logsItem = Object.assign({}, item);
            this.showLogsModal = true;
        }
    }">


        @if (isset($errors) && $errors->any())
            <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
                <div class="flex items-center gap-2 mb-2 font-semibold text-sm">
                    <span>⚠️</span>
                    <span>{{ __('Terdapat kesalahan verifikasi:') }}</span>
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
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">✅</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🛡️</span>
                        <span>{{ __('Penjaminan Mutu & Rekonsiliasi Data Tangkapan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Validasi & Kontrol Mutu Data (Data Quality Control)') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-4xl leading-relaxed">
                        {{ __('Workflow peninjauan keabsahan operasional melaut: verifikasi checklist dokumen (logbook, effort, tangkapan, pendaratan), rekonsiliasi kesesuaian bobot, persetujuan data tervalidasi, serta pencatatan jejak audit (audit trail).') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/10 text-xs font-medium border border-white/20">
                        <span>📋</span>
                        <span>{{ $counts['total_audit_logs'] }} {{ __('Aktivitas Audit Tercatat') }}</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Metric Summary Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-3 mb-6">
            {{-- Submitted (Perlu Verifikasi) --}}
            <div class="bg-white p-4 rounded-xl border {{ $counts['submitted_count'] > 0 ? 'border-amber-300 bg-amber-50/20' : 'border-slate-200' }} shadow-sm flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-amber-700 uppercase tracking-wider">{{ __('Perlu Verifikasi') }}</span>
                    <span class="text-sm">⏳</span>
                </div>
                <span class="text-2xl font-black text-amber-700 mt-1">{{ number_format($counts['submitted_count']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Menunggu keputusan') }}</span>
            </div>

            {{-- Validated --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-emerald-700 uppercase tracking-wider">{{ __('Tervalidasi') }}</span>
                    <span class="text-sm">✅</span>
                </div>
                <span class="text-2xl font-black text-emerald-700 mt-1">{{ number_format($counts['validated_count']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Data resmi disetujui') }}</span>
            </div>

            {{-- Draft --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">{{ __('Draf Enumerator') }}</span>
                    <span class="text-sm">📝</span>
                </div>
                <span class="text-2xl font-black text-slate-800 mt-1">{{ number_format($counts['draft_count']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Dalam pengisian') }}</span>
            </div>

            {{-- Rejected --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-rose-700 uppercase tracking-wider">{{ __('Ditolak / Revisi') }}</span>
                    <span class="text-sm">❌</span>
                </div>
                <span class="text-2xl font-black text-rose-700 mt-1">{{ number_format($counts['rejected_count']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Butuh perbaikan data') }}</span>
            </div>

            {{-- Total Trips --}}
            <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm flex flex-col col-span-2 md:col-span-4 lg:col-span-1">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-ocean-700 uppercase tracking-wider">{{ __('Total Trip') }}</span>
                    <span class="text-sm">🚢</span>
                </div>
                <span class="text-2xl font-black text-ocean-800 mt-1">{{ number_format($counts['total_trips']) }}</span>
                <span class="text-[11px] text-slate-500 mt-0.5">{{ __('Seluruh registrasi trip') }}</span>
            </div>
        </div>

        {{-- Status Filter Tabs & Search --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm mb-6 p-4">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Status Tabs --}}
                <div class="flex items-center gap-1 overflow-x-auto pb-2 md:pb-0">
                    @php
                        $currStatus = request('status', 'all');
                    @endphp
                    <a href="{{ route('analysis.validation.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $currStatus === 'all' ? 'bg-ocean-800 text-white shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        {{ __('Semua') }} ({{ $counts['total_trips'] }})
                    </a>

                    <a href="{{ route('analysis.validation.index', array_merge(request()->except('status'), ['status' => 'submitted'])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 {{ $currStatus === 'submitted' ? 'bg-amber-600 text-white shadow-2xs' : 'bg-amber-50 text-amber-800 hover:bg-amber-100 border border-amber-200' }}">
                        <span>⏳ {{ __('Perlu Verifikasi') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currStatus === 'submitted' ? 'bg-white text-amber-700' : 'bg-amber-200 text-amber-900' }}">
                            {{ $counts['submitted_count'] }}
                        </span>
                    </a>

                    <a href="{{ route('analysis.validation.index', array_merge(request()->except('status'), ['status' => 'validated'])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 {{ $currStatus === 'validated' ? 'bg-emerald-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>✅ {{ __('Tervalidasi') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currStatus === 'validated' ? 'bg-white text-emerald-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $counts['validated_count'] }}
                        </span>
                    </a>

                    <a href="{{ route('analysis.validation.index', array_merge(request()->except('status'), ['status' => 'draft'])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 {{ $currStatus === 'draft' ? 'bg-slate-700 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>📝 {{ __('Draf') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currStatus === 'draft' ? 'bg-white text-slate-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $counts['draft_count'] }}
                        </span>
                    </a>

                    <a href="{{ route('analysis.validation.index', array_merge(request()->except('status'), ['status' => 'rejected'])) }}"
                       class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors flex items-center gap-1.5 {{ $currStatus === 'rejected' ? 'bg-rose-600 text-white shadow-2xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' }}">
                        <span>❌ {{ __('Ditolak') }}</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currStatus === 'rejected' ? 'bg-white text-rose-700' : 'bg-slate-200 text-slate-700' }}">
                            {{ $counts['rejected_count'] }}
                        </span>
                    </a>
                </div>

                {{-- Search Box --}}
                <form method="GET" action="{{ route('analysis.validation.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ request('status', 'all') }}">
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Cari trip, kapal, nahkoda..."
                               class="w-64 pl-8 pr-3 py-1.5 text-xs border border-slate-200 rounded-lg focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500">
                        <span class="absolute left-2.5 top-2 text-slate-400 text-xs">🔍</span>
                    </div>
                    <button type="submit" class="px-3 py-1.5 bg-ocean-800 hover:bg-ocean-700 text-white text-xs font-semibold rounded-lg shadow-sm">
                        {{ __('Cari') }}
                    </button>
                    @if(request('search'))
                        <a href="{{ route('analysis.validation.index', ['status' => request('status', 'all')]) }}" class="px-2 py-1.5 bg-slate-100 text-slate-600 rounded-lg text-xs">✕</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- Verification Table --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">#</th>
                            <th class="px-4 py-3.5">{{ __('Nomor Trip & Waktu') }}</th>
                            <th class="px-4 py-3.5">{{ __('Kapal & Nahkoda') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Status Verifikasi') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Kelengkapan Dokumen') }}</th>
                            <th class="px-4 py-3.5 text-center">{{ __('Uji Rekonsiliasi Bobot') }}</th>
                            <th class="px-4 py-3.5">{{ __('Validator & Riwayat') }}</th>
                            <th class="px-4 py-3.5 text-center w-36">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($trips as $index => $trip)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-center text-xs text-slate-400">
                                    {{ $trips->firstItem() + $index }}
                                </td>

                                {{-- Nomor Trip & Waktu --}}
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="font-mono text-xs font-bold text-ocean-800 bg-ocean-50 border border-ocean-200 px-2 py-0.5 rounded inline-block">
                                        {{ $trip->trip_number }}
                                    </div>
                                    <div class="text-xs text-slate-700 font-medium mt-1">
                                        {{ $trip->departure_date ? $trip->departure_date->format('d/m/y') : '-' }} s/d {{ $trip->return_date ? $trip->return_date->format('d/m/y') : 'Melaut' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        WPPNRI {{ $trip->fma_code ?? '-' }}
                                    </div>
                                </td>

                                {{-- Kapal & Nahkoda --}}
                                <td class="px-4 py-3">
                                    <div class="font-semibold text-xs text-slate-900 flex items-center gap-1">
                                        <span>🚢</span>
                                        <span>{{ $trip->vessel->name ?? '-' }}</span>
                                    </div>
                                    @if($trip->captain)
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            {{ __('Nahkoda') }}: {{ $trip->captain->name }}
                                        </div>
                                    @endif
                                    <div class="text-[10px] text-slate-400 mt-0.5">
                                        {{ $trip->primaryGear->name ?? '-' }}
                                    </div>
                                </td>

                                {{-- Status Verifikasi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $vStatus = $trip->validation_status;
                                        $badgeClass = match($vStatus) {
                                            'validated' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'submitted' => 'bg-amber-50 text-amber-800 border-amber-300 font-bold animate-pulse',
                                            'rejected'  => 'bg-rose-50 text-rose-700 border-rose-200',
                                            default     => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                        $badgeText = match($vStatus) {
                                            'validated' => '✅ Tervalidasi',
                                            'submitted' => '⏳ Perlu Verifikasi',
                                            'rejected'  => '❌ Ditolak',
                                            default     => '📝 Draf',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs border {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                    @if($trip->rejection_reason)
                                        <div class="text-[10px] text-rose-600 mt-1 max-w-[150px] truncate mx-auto" title="{{ $trip->rejection_reason }}">
                                            ⚠️ {{ $trip->rejection_reason }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Kelengkapan Dokumen --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="grid grid-cols-2 gap-1 text-[11px] max-w-[180px] mx-auto">
                                        <div class="px-1.5 py-0.5 rounded {{ $trip->logbooks_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }}">
                                            📋 Log: {{ $trip->logbooks_count }}
                                        </div>
                                        <div class="px-1.5 py-0.5 rounded {{ $trip->fishing_efforts_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }}">
                                            ⚙️ Eff: {{ $trip->fishing_efforts_count }}
                                        </div>
                                        <div class="px-1.5 py-0.5 rounded {{ $trip->catches_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }}">
                                            🐟 Catch: {{ $trip->catches_count }}
                                        </div>
                                        <div class="px-1.5 py-0.5 rounded {{ $trip->landings_count > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 font-semibold' : 'bg-slate-100 text-slate-400' }}">
                                            📦 Land: {{ $trip->landings_count }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Rekonsiliasi Bobot --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    @php
                                        $cKg = (float) $trip->total_catch_kg;
                                        $lKg = (float) $trip->total_landing_kg;
                                        $matchPercent = ($cKg > 0 && $lKg > 0) ? round(($lKg / $cKg) * 100, 1) : null;
                                    @endphp
                                    <div class="text-xs font-mono">
                                        <span class="text-slate-600">{{ number_format($cKg) }} kg</span>
                                        <span class="text-slate-400">➔</span>
                                        <span class="font-bold text-slate-900">{{ number_format($lKg) }} kg</span>
                                    </div>
                                    @if($matchPercent !== null)
                                        <div class="mt-0.5">
                                            @if($matchPercent >= 95 && $matchPercent <= 105)
                                                <span class="inline-flex items-center text-[10px] font-bold text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded border border-emerald-200">
                                                    Match {{ $matchPercent }}%
                                                </span>
                                            @else
                                                <span class="inline-flex items-center text-[10px] font-bold text-amber-700 bg-amber-50 px-1.5 py-0.2 rounded border border-amber-200">
                                                    Diff {{ $matchPercent }}%
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400 italic">{{ __('Data parsial') }}</span>
                                    @endif
                                </td>

                                {{-- Validator & Riwayat --}}
                                <td class="px-4 py-3 whitespace-nowrap text-xs">
                                    @if($trip->validatedBy)
                                        <div class="font-semibold text-slate-900">
                                            {{ $trip->validatedBy->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500">
                                            {{ $trip->validated_at ? $trip->validated_at->format('d/m/Y H:i') : '-' }}
                                        </div>
                                    @elseif($trip->submittedBy)
                                        <div class="text-slate-600">
                                            Diajukan: {{ $trip->submittedBy->name }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">{{ __('Belum divalidasi') }}</span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1.5">
                                        {{-- Tombol Buka Modal Verifikasi --}}
                                        <button type="button"
                                                @click="openVerify({{ json_encode([
                                                    'id' => $trip->id,
                                                    'trip_number' => $trip->trip_number,
                                                    'vessel_name' => $trip->vessel->name ?? '-',
                                                    'captain_name' => $trip->captain->name ?? '-',
                                                    'gear_name' => $trip->primaryGear->name ?? '-',
                                                    'validation_status' => $trip->validation_status,
                                                    'rejection_reason' => $trip->rejection_reason,
                                                    'total_catch_kg' => number_format((float) $trip->total_catch_kg, 1),
                                                    'total_landing_kg' => number_format((float) $trip->total_landing_kg, 1),
                                                    'logbooks_count' => $trip->logbooks_count,
                                                    'efforts_count' => $trip->fishing_efforts_count,
                                                    'catches_count' => $trip->catches_count,
                                                    'landings_count' => $trip->landings_count,
                                                ]) }}, '{{ route('analysis.validation.update-status', $trip) }}')"
                                                class="px-2.5 py-1.5 bg-ocean-800 hover:bg-ocean-700 text-white font-medium text-xs rounded-lg shadow-2xs flex items-center gap-1 transition-colors">
                                            <span>⚖️</span>
                                            <span>{{ __('Verifikasi') }}</span>
                                        </button>

                                        {{-- Tombol Riwayat Audit Trail Log --}}
                                        <button type="button"
                                                @click="openLogs({{ json_encode([
                                                    'trip_number' => $trip->trip_number,
                                                    'vessel_name' => $trip->vessel->name ?? '-',
                                                    'logs' => $trip->validationLogs->map(fn($l) => [
                                                        'from' => $l->from_status,
                                                        'to' => $l->to_status,
                                                        'notes' => $l->notes,
                                                        'validator_name' => $l->validator->name ?? 'Pengawas',
                                                        'time' => $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : '-',
                                                    ]),
                                                ]) }})"
                                                class="p-1.5 text-slate-500 hover:text-ocean-700 hover:bg-slate-100 rounded-lg transition-colors"
                                                title="{{ __('Lihat Audit Trail Log') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-400">
                                    <div class="text-4xl mb-3">🛡️</div>
                                    <p class="text-base font-semibold text-slate-700">{{ __('Tidak ada data trip dalam kriteria ini') }}</p>
                                    <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">
                                        {{ __('Seluruh data operasional trip telah sesuai dengan filter status yang dipilih.') }}
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination & Dropdown Baris Data --}}
            <x-pagination :paginator="$trips" />
        </div>

        {{-- ======================================= --}}
        {{-- MODAL AKSI VERIFIKASI DATA TRIP         --}}
        {{-- ======================================= --}}
        <div x-show="showVerifyModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showVerifyModal = false">
            <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showVerifyModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">⚖️</span>
                        <div>
                            <span class="text-xs uppercase text-ocean-200 block font-semibold">{{ __('Kontrol Mutu & Keputusan Validasi') }}</span>
                            <h3 class="font-bold text-base font-mono" x-text="verifyItem.trip_number"></h3>
                        </div>
                    </div>
                    <button type="button" @click="showVerifyModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Form --}}
                <form method="POST" :action="verifyAction" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    {{-- Ringkasan Parameter Trip --}}
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 text-xs">
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Kapal & Nahkoda') }}</span>
                            <span class="font-bold text-slate-900 block mt-0.5" x-text="verifyItem.vessel_name"></span>
                            <span class="text-slate-500" x-text="'Nahkoda: ' + verifyItem.captain_name"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-medium">{{ __('Alat Tangkap Utama') }}</span>
                            <span class="font-bold text-slate-900 block mt-0.5" x-text="verifyItem.gear_name"></span>
                        </div>
                    </div>

                    {{-- Indikator Kelengkapan Dokumen --}}
                    <div class="border border-slate-200 rounded-xl p-3 bg-slate-50 text-xs">
                        <span class="font-bold text-slate-800 block mb-2">{{ __('Pemeriksaan Dokumen & Rekonsiliasi:') }}</span>
                        <div class="grid grid-cols-4 gap-2 text-center">
                            <div class="bg-white p-2 rounded border border-slate-200">
                                <span class="text-slate-400 block text-[10px] uppercase">Logbook</span>
                                <span class="font-bold text-slate-900 text-sm" x-text="verifyItem.logbooks_count"></span>
                            </div>
                            <div class="bg-white p-2 rounded border border-slate-200">
                                <span class="text-slate-400 block text-[10px] uppercase">Effort</span>
                                <span class="font-bold text-slate-900 text-sm" x-text="verifyItem.efforts_count"></span>
                            </div>
                            <div class="bg-white p-2 rounded border border-slate-200">
                                <span class="text-slate-400 block text-[10px] uppercase">Catch (Kg)</span>
                                <span class="font-bold text-slate-900 text-sm" x-text="verifyItem.total_catch_kg"></span>
                            </div>
                            <div class="bg-white p-2 rounded border border-slate-200">
                                <span class="text-slate-400 block text-[10px] uppercase">Landing (Kg)</span>
                                <span class="font-bold text-emerald-700 text-sm" x-text="verifyItem.total_landing_kg"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Pilihan Keputusan Validasi --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                            {{ __('Pilih Keputusan Verifikasi:') }} <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-3 gap-2">
                            <label class="cursor-pointer border p-3 rounded-xl text-center transition-all"
                                   :class="verifyStatus === 'validated' ? 'border-emerald-500 bg-emerald-50/50 text-emerald-900 font-bold shadow-2xs' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="validation_status" value="validated" x-model="verifyStatus" class="sr-only">
                                <span class="text-base block mb-0.5">✅</span>
                                <span class="text-xs block">{{ __('Setujui Data') }}</span>
                                <span class="text-[10px] text-slate-500 block font-normal">{{ __('Tervalidasi Resmi') }}</span>
                            </label>

                            <label class="cursor-pointer border p-3 rounded-xl text-center transition-all"
                                   :class="verifyStatus === 'rejected' ? 'border-rose-500 bg-rose-50/50 text-rose-900 font-bold shadow-2xs' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="validation_status" value="rejected" x-model="verifyStatus" class="sr-only">
                                <span class="text-base block mb-0.5">❌</span>
                                <span class="text-xs block">{{ __('Tolak / Revisi') }}</span>
                                <span class="text-[10px] text-slate-500 block font-normal">{{ __('Minta Perbaikan') }}</span>
                            </label>

                            <label class="cursor-pointer border p-3 rounded-xl text-center transition-all"
                                   :class="verifyStatus === 'draft' ? 'border-slate-500 bg-slate-50 text-slate-900 font-bold shadow-2xs' : 'border-slate-200 bg-white text-slate-600'">
                                <input type="radio" name="validation_status" value="draft" x-model="verifyStatus" class="sr-only">
                                <span class="text-base block mb-0.5">📝</span>
                                <span class="text-xs block">{{ __('Ke Draf') }}</span>
                                <span class="text-[10px] text-slate-500 block font-normal">{{ __('Status Awal') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Form Input Alasan Penolakan jika ditolak --}}
                    <div x-show="verifyStatus === 'rejected'">
                        <label class="block text-xs font-semibold text-rose-700 mb-1">
                            {{ __('Alasan Penolakan / Catatan Perbaikan Data:') }} <span class="text-rose-500">*</span>
                        </label>
                        <textarea name="rejection_reason" x-model="rejectionReason" rows="2" placeholder="Jelaskan alasan penolakan data, misal bobot pendaratan tidak sinkron, koordinat di luar izin..." class="w-full text-xs border border-rose-300 rounded-lg p-2.5 focus:ring-2 focus:ring-rose-500 focus:border-rose-500 bg-rose-50/30"></textarea>
                    </div>

                    {{-- Catatan Validator --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            {{ __('Catatan Pengawas / Validator:') }}
                        </label>
                        <textarea name="notes" x-model="verifyNotes" rows="2" placeholder="Catatan verifikasi mutu data untuk arsip audit trail..." class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"></textarea>
                    </div>

                    {{-- Modal Actions --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showVerifyModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium text-xs rounded-lg transition-colors">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-ocean-800 hover:bg-ocean-700 text-white font-medium text-xs rounded-lg shadow-sm transition-colors">
                            {{ __('Simpan Keputusan Verifikasi') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ======================================= --}}
        {{-- MODAL AUDIT TRAIL RIWAYAT VALIDASI      --}}
        {{-- ======================================= --}}
        <div x-show="showLogsModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
             @keydown.escape.window="showLogsModal = false">
            <div class="bg-white rounded-2xl max-w-xl w-full shadow-2xl overflow-hidden border border-slate-100 my-8"
                 @click.away="showLogsModal = false">
                {{-- Modal Header --}}
                <div class="px-6 py-4 bg-gradient-to-r from-ocean-800 to-ocean-900 text-white flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase text-ocean-200 block font-semibold">{{ __('Audit Trail & Linimasa Verifikasi') }}</span>
                        <h3 class="font-bold text-base font-mono" x-text="logsItem.trip_number"></h3>
                    </div>
                    <button type="button" @click="showLogsModal = false" class="text-white/70 hover:text-white text-lg">✕</button>
                </div>

                {{-- Modal Content: Timeline --}}
                <div class="p-6 space-y-4">
                    <div class="text-xs text-slate-500 font-medium flex items-center gap-1.5 pb-2 border-b border-slate-100">
                        <span>🚢</span>
                        <span>{{ __('Kapal') }}: <strong class="text-slate-900" x-text="logsItem.vessel_name"></strong></span>
                    </div>

                    <div class="space-y-3 max-h-80 overflow-y-auto pr-1">
                        <template x-for="(log, i) in logsItem.logs" :key="i">
                            <div class="flex gap-3 text-xs bg-slate-50 p-3 rounded-xl border border-slate-200/80">
                                <div class="text-base shrink-0 mt-0.5">
                                    <span x-show="log.to == 'validated'">✅</span>
                                    <span x-show="log.to == 'submitted'">⏳</span>
                                    <span x-show="log.to == 'rejected'">❌</span>
                                    <span x-show="log.to == 'draft'">📝</span>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-baseline justify-between">
                                        <span class="font-bold text-slate-900">
                                            Status: <span class="capitalize" x-text="log.from"></span> ➔ <span class="capitalize text-ocean-700" x-text="log.to"></span>
                                        </span>
                                        <span class="text-[11px] text-slate-400 font-mono" x-text="log.time"></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Petugas: <strong class="text-slate-700" x-text="log.validator_name"></strong>
                                    </div>
                                    <p class="text-xs text-slate-700 mt-1.5 bg-white p-2 rounded border border-slate-200" x-text="log.notes"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="!logsItem.logs || logsItem.logs.length === 0" class="py-8 text-center text-slate-400 text-xs">
                            {{ __('Belum ada riwayat perubahan status pada trip ini.') }}
                        </div>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="button" @click="showLogsModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-medium text-xs rounded-lg transition-colors">
                            {{ __('Tutup Riwayat') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
