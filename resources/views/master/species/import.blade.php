<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('master.species.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <span>🐠</span>
            <span>{{ __('Import Data Spesies Ikan') }}</span>
        </div>
    </x-slot>



        @if(!isset($previewData))
            {{-- Bagian Upload --}}
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <h3 class="text-lg font-bold text-gray-800 mb-2">{{ __('Panduan Import') }}</h3>
                <p class="text-sm text-gray-600 mb-6">
                    {{ __('Pastikan data yang Anda upload menggunakan template resmi kami. Kolom dengan tanda bintang (*) wajib diisi. Data yang sudah ada di sistem (berdasarkan Kode FAO) akan dideteksi sebagai duplikat dan tidak akan ditimpa.') }}
                </p>

                <div class="mb-8">
                    <a href="{{ route('master.species.import.template') }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-800 hover:bg-gray-700 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>{{ __('Download Template Excel') }}</span>
                    </a>
                </div>

                <hr class="border-gray-100 mb-6">

                <form method="POST" action="{{ route('master.species.import.preview') }}" enctype="multipart/form-data" class="max-w-2xl">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Pilih File Excel (.xlsx / .xls)') }}</label>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-8 h-8 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <p class="mb-1 text-sm text-gray-500"><span class="font-semibold">{{ __('Klik untuk upload') }}</span> {{ __('atau drag & drop') }}</p>
                                    <p class="text-xs text-gray-400">XLSX, XLS (Max: 5MB)</p>
                                </div>
                                <input type="file" name="file" class="hidden" accept=".xlsx, .xls" required />
                            </label>
                        </div>
                        @error('file')
                            <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="px-6 py-2.5 bg-ocean-600 hover:bg-ocean-700 text-white font-medium text-sm rounded-xl shadow-md transition-all">
                            {{ __('Upload & Validasi') }}
                        </button>
                    </div>
                </form>
            </div>
        @else
            {{-- Bagian Preview --}}
            @php
                $summary = $previewData['summary'];
                $data = $previewData['data'];
            @endphp
            
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Preview Import Species') }}</h3>
                        <p class="text-sm text-gray-500 mt-1">{{ __('Mohon tinjau hasil validasi di bawah ini sebelum menyimpan data ke sistem.') }}</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="text-center px-4 py-2 bg-gray-50 rounded-xl border border-gray-100">
                            <div class="text-2xl font-bold text-gray-700">{{ $summary['total'] }}</div>
                            <div class="text-xs font-semibold text-gray-500 uppercase">{{ __('Total Baris') }}</div>
                        </div>
                        <div class="text-center px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                            <div class="text-2xl font-bold text-emerald-600">{{ $summary['valid'] }}</div>
                            <div class="text-xs font-semibold text-emerald-600 uppercase">{{ __('Valid') }}</div>
                        </div>
                        <div class="text-center px-4 py-2 bg-amber-50 rounded-xl border border-amber-100">
                            <div class="text-2xl font-bold text-amber-600">{{ $summary['duplicate'] }}</div>
                            <div class="text-xs font-semibold text-amber-600 uppercase">{{ __('Duplikat') }}</div>
                        </div>
                        <div class="text-center px-4 py-2 bg-red-50 rounded-xl border border-red-100">
                            <div class="text-2xl font-bold text-red-600">{{ $summary['error'] }}</div>
                            <div class="text-xs font-semibold text-red-600 uppercase">{{ __('Error') }}</div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto rounded-xl border border-gray-200 mb-6 max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-gray-50 sticky top-0 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 font-semibold text-gray-600 border-b w-16 text-center">{{ __('Baris') }}</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 border-b">{{ __('Kode FAO') }}</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 border-b">{{ __('Nama Indonesia') }}</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 border-b">{{ __('Status') }}</th>
                                <th class="px-4 py-3 font-semibold text-gray-600 border-b">{{ __('Keterangan') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($data as $row)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-3 text-center text-gray-500 font-mono text-xs">{{ $row['row'] }}</td>
                                    <td class="px-4 py-3 font-mono font-medium text-gray-800">{{ $row['code'] ?? '-' }}</td>
                                    <td class="px-4 py-3 text-gray-800">{{ $row['indonesian_name'] ?? '-' }}</td>
                                    <td class="px-4 py-3">
                                        @if($row['status'] === 'Valid')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-700">Valid</span>
                                        @elseif($row['status'] === 'Duplikat')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-amber-100 text-amber-700">Duplikat</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-semibold bg-red-100 text-red-700">Error</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 text-xs whitespace-normal max-w-xs">{{ $row['message'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                    <a href="{{ route('master.species.import.view') }}" class="px-5 py-2.5 border border-gray-200 rounded-xl text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        {{ __('Batal & Upload Ulang') }}
                    </a>
                    
                    @if($summary['valid'] > 0)
                        <form method="POST" action="{{ route('master.species.import.process') }}">
                            @csrf
                            <input type="hidden" name="session_id" value="{{ $importSessionId }}">
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-medium text-sm rounded-xl shadow-md transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ __('Import :count Data Valid', ['count' => $summary['valid']]) }}
                            </button>
                        </form>
                    @else
                        <button disabled class="px-6 py-2.5 bg-gray-300 text-gray-500 font-medium text-sm rounded-xl cursor-not-allowed">
                            {{ __('Tidak ada data valid') }}
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
