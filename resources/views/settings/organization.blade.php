<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span>⚙️</span>
            <span>{{ __('Pengaturan Identitas Organisasi') }}</span>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-6 bg-slate-50/80 border-b border-gray-200 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900 tracking-wide uppercase flex items-center gap-2">
                        <span>🏢</span>
                        <span>IDENTITAS ORGANISASI</span>
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kelola data identitas dan branding pemilik sistem aplikasi secara terpadu.
                    </p>
                </div>
            </div>

            <form action="{{ route('settings.organization.update') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm shadow-xs">
                        <div class="font-bold mb-1">{{ __('Terjadi kesalahan validasi data:') }}</div>
                        <ul class="list-disc list-inside text-xs space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- 1. Nama Organisasi/Pemilik --}}
                <div class="space-y-1.5">
                    <label for="organization_name" class="block text-sm font-semibold text-gray-800">
                        Nama Organisasi/Pemilik <span class="text-rose-500">*</span>
                    </label>
                    <input type="text"
                           id="organization_name"
                           name="organization_name"
                           value="{{ old('organization_name', $setting->organization_name) }}"
                           required
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('organization_name') border-rose-400 @enderror">
                    @error('organization_name')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 2. Jenis Organisasi (Input Manual, Text Only, No Dropdown, Placeholder Tepat) --}}
                <div class="space-y-1.5">
                    <label for="organization_type" class="block text-sm font-semibold text-gray-800">
                        Jenis Organisasi
                    </label>
                    <input type="text"
                           id="organization_type"
                           name="organization_type"
                           value="{{ old('organization_type', $setting->organization_type) }}"
                           placeholder="Instansi, Lembaga, Forum"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('organization_type') border-rose-400 @enderror">
                    @error('organization_type')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 3. Logo Organisasi --}}
                <div class="space-y-2">
                    <label for="logo" class="block text-sm font-semibold text-gray-800">
                        Logo Organisasi
                    </label>

                    <div class="flex items-center gap-5 p-4 rounded-xl border border-gray-200 bg-slate-50/50">
                        <div class="w-16 h-16 rounded-xl border border-gray-200 bg-white p-2 flex items-center justify-center shrink-0 shadow-xs overflow-hidden">
                            <img id="logo-preview"
                                 src="{{ $setting->logo_url }}"
                                 alt="Logo Preview"
                                 class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="flex-1 space-y-1">
                            <input type="file"
                                   id="logo"
                                   name="logo"
                                   accept="image/png,image/jpeg,image/jpg,image/webp"
                                   onchange="previewImage(this)"
                                   class="block w-full text-xs text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-ocean-600 file:text-white hover:file:bg-ocean-700 file:cursor-pointer transition">
                            <p class="text-[11px] text-gray-500">Mendukung format PNG, JPG, JPEG, atau WEBP (Maksimal 2 MB).</p>
                        </div>
                    </div>
                    @error('logo')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 4. Alamat --}}
                <div class="space-y-1.5">
                    <label for="address" class="block text-sm font-semibold text-gray-800">
                        Alamat
                    </label>
                    <textarea id="address"
                              name="address"
                              rows="3"
                              class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('address') border-rose-400 @enderror">{{ old('address', $setting->address) }}</textarea>
                    @error('address')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 5. Website --}}
                <div class="space-y-1.5">
                    <label for="website" class="block text-sm font-semibold text-gray-800">
                        Website
                    </label>
                    <input type="url"
                           id="website"
                           name="website"
                           placeholder="https://example.org"
                           value="{{ old('website', $setting->website) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('website') border-rose-400 @enderror">
                    @error('website')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 6. Email --}}
                <div class="space-y-1.5">
                    <label for="email" class="block text-sm font-semibold text-gray-800">
                        Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="info@example.org"
                           value="{{ old('email', $setting->email) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('email') border-rose-400 @enderror">
                    @error('email')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 7. Telepon --}}
                <div class="space-y-1.5">
                    <label for="phone" class="block text-sm font-semibold text-gray-800">
                        Telepon
                    </label>
                    <input type="text"
                           id="phone"
                           name="phone"
                           placeholder="+62 ..."
                           value="{{ old('phone', $setting->phone) }}"
                           class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-gray-900 text-sm focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition shadow-xs @error('phone') border-rose-400 @enderror">
                    @error('phone')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="pt-4 border-t border-gray-200 flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-ocean-600 hover:bg-ocean-700 text-white font-bold text-sm transition shadow-sm hover:shadow flex items-center gap-2">
                        <span>💾</span>
                        <span>SIMPAN PERUBAHAN</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('logo-preview');
                    if (preview) {
                        preview.src = e.target.result;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</x-app-layout>
