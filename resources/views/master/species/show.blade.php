<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('master.species.index') }}" class="text-gray-500 hover:text-gray-700 flex items-center gap-2 text-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Kembali ke Master Species
            </a>
        </div>
        <div class="mt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-2xl text-gray-800 leading-tight italic font-serif">
                    {{ $species->scientific_name }}
                </h2>
                <div class="text-sm text-gray-500 mt-1 flex flex-wrap gap-4 items-center">
                    <div><span class="font-semibold text-gray-600">FAO Code:</span> <span class="font-mono text-ocean-700">{{ $species->fao_code ?? '—' }}</span></div>
                    <div>
                        <span class="font-semibold text-gray-600">Status:</span> 
                        @if($species->is_active)
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-emerald-100 text-emerald-700">ACTIVE</span>
                        @else
                            <span class="px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-700">INACTIVE</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-50 text-emerald-700 p-4 rounded-xl border border-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-6">
                    <!-- SECTION 1 — DATA ASFIS -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                        <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                1. DATA ASFIS
                            </h3>
                            <span class="text-xs text-gray-500">Read Only</span>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">FAO Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $species->fao_code ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Taxonomic Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $species->taxonomic_code ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">ISSCAAP</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->isscaap_code ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Scientific Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900 italic">{{ $species->scientific_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Author</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->author ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Family</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->family ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Higher Taxa</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->higher_taxa ?? '—' }}</dd>
                                </div>
                                <div class="sm:col-span-2 border-t border-gray-100 pt-4"></div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">English Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->english_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">French Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->french_name ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Spanish Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->spanish_name ?? '—' }}</dd>
                                </div>
                                <div class="sm:col-span-2 border-t border-gray-100 pt-4"></div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">FishStat Data</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->is_statistical_item ? 'Yes' : 'No' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">ASFIS Version</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->fao_version ?? '—' }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-xs font-medium text-gray-500 uppercase">ASFIS Source</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->fao_source ?? '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- SECTION 5 — INFORMASI SISTEM -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                        <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                5. INFORMASI SISTEM
                            </h3>
                            <span class="text-xs text-gray-500">Read Only</span>
                        </div>
                        <div class="p-6">
                            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">ID Species</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $species->id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Created At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->created_at ? $species->created_at->format('Y-m-d H:i:s') : '—' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-xs font-medium text-gray-500 uppercase">Updated At</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $species->updated_at ? $species->updated_at->format('Y-m-d H:i:s') : '—' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <form method="POST" action="{{ route('master.species.update', $species) }}" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- SECTION 2 — DATA INDONESIA -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                            <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                                    2. DATA INDONESIA
                                </h3>
                                <span class="text-xs text-gray-500">Editable</span>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Indonesia</label>
                                    <input type="text" name="local_name_id" value="{{ old('local_name_id', $species->local_name_id) }}" placeholder="—" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm placeholder-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Variasi Nama Indonesia</label>
                                    <textarea name="local_name_variants" rows="2" placeholder="—" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm placeholder-gray-300">{{ old('local_name_variants', $species->local_name_variants) }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Kode Indonesia</label>
                                    <input type="text" name="indonesia_code" value="{{ old('indonesia_code', $species->indonesia_code) }}" placeholder="—" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm placeholder-gray-300">
                                </div>
                                <div class="pt-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_indonesia" value="1" {{ old('is_indonesia', $species->is_indonesia) ? 'checked' : '' }} class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Status Indonesia (Aktif/Valid)</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 3 — DATA ACEH -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                            <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                                    3. DATA ACEH
                                </h3>
                                <span class="text-xs text-gray-500">Editable</span>
                            </div>
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nama Lokal Aceh</label>
                                    <input type="text" name="local_name_aceh" value="{{ old('local_name_aceh', $species->local_name_aceh) }}" placeholder="Belum diisi" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm placeholder-gray-300">
                                </div>
                            </div>
                        </div>

                        <!-- SECTION 4 — DATA APLIKASI -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100">
                            <div class="p-6 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    4. DATA APLIKASI
                                </h3>
                                <span class="text-xs text-gray-500">Editable</span>
                            </div>
                            <div class="p-6 space-y-4">
                                <div class="flex gap-6 py-2">
                                    <label class="inline-flex items-center">
                                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $species->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-600">Status (Active / Inactive)</span>
                                    </label>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                                    <input type="number" name="sort_order" value="{{ old('sort_order', $species->sort_order ?? 0) }}" placeholder="—" class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm placeholder-gray-300">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                                    <textarea name="notes" rows="2" placeholder="—" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm placeholder-gray-300">{{ old('notes', $species->notes) }}</textarea>
                                </div>
                            </div>
                            <div class="p-6 bg-gray-50 border-t border-gray-100 flex justify-end rounded-b-2xl">
                                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl shadow-sm transition-all focus:ring-4 focus:ring-blue-100">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
