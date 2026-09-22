<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <span class="text-lg">👥</span>
            <span>{{ __('Manajemen Akun & Hak Akses Pengguna') }}</span>
        </div>
    </x-slot>

    <div x-data="{
        showCreateModal: false,
        showEditModal: false,
        showDeleteModal: false,

        // Data untuk Edit Modal
        editUser: {
            id: null,
            name: '',
            email: '',
            role: 'viewer'
        },
        editActionUrl: '',

        // Data untuk Delete Modal
        deleteUser: {
            id: null,
            name: '',
            email: ''
        },
        deleteActionUrl: '',

        openEdit(user, role) {
            this.editUser = {
                id: user.id,
                name: user.name,
                email: user.email,
                role: role
            };
            this.editActionUrl = `/admin/users/${user.id}`;
            this.showEditModal = true;
        },

        openDelete(user) {
            this.deleteUser = {
                id: user.id,
                name: user.name,
                email: user.email
            };
            this.deleteActionUrl = `/admin/users/${user.id}`;
            this.showDeleteModal = true;
        }
    }" class="space-y-6">

        {{-- Intro Header Banner --}}
        <div class="bg-gradient-to-r from-ocean-800 via-ocean-900 to-ocean-950 text-white p-5 rounded-2xl mb-6 shadow-sm relative overflow-hidden">
            <div class="absolute right-4 -bottom-6 text-8xl opacity-10 pointer-events-none">👥</div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 relative z-10">
                <div class="flex-1 max-w-4xl">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-ocean-500/20 text-ocean-300 border border-ocean-500/30 text-xs font-semibold tracking-wide uppercase mb-2">
                        <span>🛡️</span>
                        <span>{{ __('Administrasi & Keamanan') }}</span>
                    </div>
                    <h2 class="text-xl font-bold tracking-tight">{{ __('Manajemen Akun & Hak Akses Pengguna') }}</h2>
                    <p class="text-ocean-200 text-xs sm:text-sm mt-1 max-w-3xl leading-relaxed">
                        {{ __('Kelola akun pengguna, penetapan peran (Role-Based Access Control / RBAC), serta izin operasional sistem pendataan perikanan.') }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button @click="showCreateModal = true"
                            class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm rounded-xl shadow-md transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>{{ __('Tambah Pengguna') }}</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Flash Messages Alert --}}
        @if (session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 shadow-sm">
            <div class="flex items-center gap-2 mb-2 font-semibold">
                <span>⚠️</span>
                <span>{{ __('Terdapat kesalahan pada isian form:') }}</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-700">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Ringkasan Metrik / Statistik Kartu --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            {{-- Total Pengguna --}}
            <a href="{{ route('admin.users.index') }}"
               class="p-4 rounded-xl border transition-all {{ empty(request('role')) ? 'bg-ocean-50/80 border-ocean-300 ring-2 ring-ocean-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-gray-500 font-medium mb-1">{{ __('Total Akun') }}</div>
                <div class="text-2xl font-bold text-gray-800 flex items-center justify-between">
                    <span>{{ number_format($metrics['total']) }}</span>
                    <span class="text-base opacity-75">👥</span>
                </div>
            </a>

            {{-- Super Admin --}}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['role' => 'super-admin'])) }}"
               class="p-4 rounded-xl border transition-all {{ request('role') === 'super-admin' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-purple-600 font-medium mb-1">{{ __('Super Admin') }}</div>
                <div class="text-2xl font-bold text-purple-900 flex items-center justify-between">
                    <span>{{ number_format($metrics['super_admin']) }}</span>
                    <span class="text-base">👑</span>
                </div>
            </a>

            {{-- Admin Data --}}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['role' => 'admin'])) }}"
               class="p-4 rounded-xl border transition-all {{ request('role') === 'admin' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-blue-600 font-medium mb-1">{{ __('Admin Data') }}</div>
                <div class="text-2xl font-bold text-blue-900 flex items-center justify-between">
                    <span>{{ number_format($metrics['admin']) }}</span>
                    <span class="text-base">🛡️</span>
                </div>
            </a>

            {{-- Verifikator --}}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['role' => 'verifikator'])) }}"
               class="p-4 rounded-xl border transition-all {{ request('role') === 'verifikator' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-amber-600 font-medium mb-1">{{ __('Verifikator') }}</div>
                <div class="text-2xl font-bold text-amber-900 flex items-center justify-between">
                    <span>{{ number_format($metrics['verifikator']) }}</span>
                    <span class="text-base">🔍</span>
                </div>
            </a>

            {{-- Petugas Lapangan --}}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['role' => 'petugas-lapangan'])) }}"
               class="p-4 rounded-xl border transition-all {{ request('role') === 'petugas-lapangan' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-emerald-600 font-medium mb-1">{{ __('Petugas Lapangan') }}</div>
                <div class="text-2xl font-bold text-emerald-900 flex items-center justify-between">
                    <span>{{ number_format($metrics['petugas_lapangan']) }}</span>
                    <span class="text-base">📋</span>
                </div>
            </a>

            {{-- Viewer --}}
            <a href="{{ route('admin.users.index', array_merge(request()->except(['page', 'role']), ['role' => 'viewer'])) }}"
               class="p-4 rounded-xl border transition-all {{ request('role') === 'viewer' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400/30' : 'bg-white border-gray-100 hover:border-gray-200 shadow-sm' }}">
                <div class="text-xs text-slate-600 font-medium mb-1">{{ __('Viewer') }}</div>
                <div class="text-2xl font-bold text-slate-800 flex items-center justify-between">
                    <span>{{ number_format($metrics['viewer']) }}</span>
                    <span class="text-base">👁️</span>
                </div>
            </a>
        </div>

        {{-- Toolbar: Pencarian, Filter Peran & Tombol Tambah --}}
        <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm mb-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                {{-- Input Pencarian --}}
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                    <input type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="{{ __('Cari nama pengguna atau alamat email...') }}"
                        class="w-full pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition-all">
                </div>

                {{-- Filter Peran --}}
                <div class="w-full sm:w-60">
                    <select name="role"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500 transition-all">
                        <option value="">{{ __('Semua Peran (Roles)') }}</option>
                        @foreach ($roles as $roleItem)
                        <option value="{{ $roleItem->name }}" {{ request('role') === $roleItem->name ? 'selected' : '' }}>
                            {{ $roleDefinitions[$roleItem->name]['icon'] ?? '👤' }} {{ $roleDefinitions[$roleItem->name]['name'] ?? $roleItem->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tombol Filter & Reset --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit"
                        class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors cursor-pointer">
                        {{ __('Filter') }}
                    </button>
                    @if(request('search') || request('role'))
                    <a href="{{ route('admin.users.index') }}"
                        class="px-3 py-2 text-sm text-red-600 hover:text-red-700 font-medium"
                        title="{{ __('Reset Filter') }}">
                        {{ __('Reset') }}
                    </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Tabel Daftar Pengguna --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <th scope="col" class="py-3.5 px-6">{{ __('Pengguna') }}</th>
                            <th scope="col" class="py-3.5 px-6">{{ __('Peran (Role)') }}</th>
                            <th scope="col" class="py-3.5 px-6 text-center">{{ __('Status Email') }}</th>
                            <th scope="col" class="py-3.5 px-6">{{ __('Terdaftar Sejak') }}</th>
                            <th scope="col" class="py-3.5 px-6 text-right">{{ __('Aksi') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                        @forelse ($users as $u)
                        @php
                        $primaryRole = $u->roles->first()?->name ?? 'viewer';
                        $roleMeta = $roleDefinitions[$primaryRole] ?? [
                            'name' => ucfirst($primaryRole),
                            'badge_class' => 'bg-slate-100 text-slate-700 border-slate-200',
                            'badge_dot' => 'bg-slate-400',
                            'icon' => '👤',
                        ];
                        $isCurrentUser = $u->id === auth()->id();
                        @endphp
                        <tr class="hover:bg-ocean-50/40 transition-colors">
                            {{-- Kolom Pengguna --}}
                            <td class="py-3.5 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-ocean-700 to-ocean-500 text-white font-bold flex items-center justify-center text-sm shadow-sm shrink-0">
                                        {{ strtoupper(substr($u->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900 flex items-center gap-2">
                                            <span>{{ $u->name }}</span>
                                            @if ($isCurrentUser)
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-ocean-50 text-ocean-700 border border-ocean-200">
                                                {{ __('Anda') }}
                                            </span>
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Kolom Peran (Role) --}}
                            <td class="py-3.5 px-6">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleMeta['badge_class'] }}">
                                    <span>{{ $roleMeta['icon'] }}</span>
                                    <span>{{ $roleMeta['name'] }}</span>
                                </span>
                            </td>

                            {{-- Kolom Status Email --}}
                            <td class="py-3.5 px-6 text-center">
                                @if ($u->email_verified_at)
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ __('Terverifikasi') }}
                                </span>
                                @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    {{ __('Belum Verifikasi') }}
                                </span>
                                @endif
                            </td>

                            {{-- Kolom Tanggal Dibuat --}}
                            <td class="py-3.5 px-6 text-xs text-gray-600">
                                <div class="font-medium text-gray-800">{{ $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-' }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">{{ $u->created_at ? $u->created_at->translatedFormat('H:i') . ' WIB' : '' }}</div>
                            </td>

                            {{-- Kolom Aksi --}}
                            <td class="py-3.5 px-6 text-right">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Tombol Edit --}}
                                    <button type="button"
                                        @click="openEdit({{ json_encode($u) }}, '{{ $primaryRole }}')"
                                        class="p-1.5 text-gray-400 hover:text-ocean-600 hover:bg-ocean-50 rounded-lg transition-colors cursor-pointer"
                                        title="{{ __('Ubah data dan hak akses') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                        </svg>
                                    </button>

                                    {{-- Tombol Hapus --}}
                                    @if ($isCurrentUser)
                                    <button type="button"
                                        disabled
                                        class="p-1.5 text-gray-300 cursor-not-allowed rounded-lg"
                                        title="{{ __('Anda tidak dapat menghapus akun Anda sendiri') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @else
                                    <button type="button"
                                        @click="openDelete({{ json_encode($u) }})"
                                        class="p-1.5 text-gray-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                        title="{{ __('Hapus akun pengguna ini') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <div class="text-4xl mb-2">👤</div>
                                <p class="font-medium text-gray-600">{{ __('Tidak ada data pengguna ditemukan.') }}</p>
                                <p class="text-xs text-gray-400 mt-1">{{ __('Coba gunakan kata kunci pencarian atau filter peran yang lain.') }}</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($users->hasPages())
            <div class="p-4 border-t border-gray-100 bg-white">
                {{ $users->links() }}
            </div>
            @endif
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH PENGGUNA BARU --}}
        {{-- ============================================================== --}}
        <div x-show="showCreateModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
            @keydown.escape.window="showCreateModal = false">
            <div @click.outside="showCreateModal = false"
                class="w-full max-w-lg rounded-2xl bg-white border border-gray-100 p-6 shadow-2xl text-gray-800 max-h-[90vh] overflow-y-auto">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👤</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Tambah Pengguna Baru') }}</h3>
                    </div>
                    <button type="button" @click="showCreateModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form Tambah Pengguna --}}
                <form method="POST" action="{{ route('admin.users.store') }}" class="mt-4 space-y-4">
                    @csrf

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lengkap') }} *</label>
                        <input type="text"
                            name="name"
                            required
                            placeholder="cth: Ahmad Faisal, S.Pi"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    {{-- Alamat Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Email') }} *</label>
                        <input type="email"
                            name="email"
                            required
                            placeholder="cth: mamet@nagakecil.site"
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    {{-- Peran / Role --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Peran (Role & Hak Akses)') }} *</label>
                        <select name="role"
                            required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            @foreach ($roles as $r)
                            <option value="{{ $r->name }}">
                                {{ $roleDefinitions[$r->name]['icon'] ?? '👤' }} {{ $roleDefinitions[$r->name]['name'] ?? $r->name }} — {{ $roleDefinitions[$r->name]['description'] ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Password & Konfirmasi --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Kata Sandi') }} *</label>
                            <input type="password"
                                name="password"
                                required
                                minlength="8"
                                placeholder="Minimal 8 karakter"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Konfirmasi Kata Sandi') }} *</label>
                            <input type="password"
                                name="password_confirmation"
                                required
                                minlength="8"
                                placeholder="Ulangi kata sandi"
                                class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                        </div>
                    </div>

                    {{-- Tombol Aksi Modal --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                        <button type="button"
                            @click="showCreateModal = false"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                            {{ __('Simpan Pengguna') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL EDIT PENGGUNA & PERAN --}}
        {{-- ============================================================== --}}
        <div x-show="showEditModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
            @keydown.escape.window="showEditModal = false">
            <div @click.outside="showEditModal = false"
                class="w-full max-w-lg rounded-2xl bg-white border border-gray-100 p-6 shadow-2xl text-gray-800 max-h-[90vh] overflow-y-auto">

                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">✏️</span>
                        <h3 class="text-lg font-bold text-gray-800">{{ __('Edit Data & Peran Pengguna') }}</h3>
                    </div>
                    <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form Edit Pengguna --}}
                <form method="POST" :action="editActionUrl" class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')

                    {{-- Nama Lengkap --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Nama Lengkap') }} *</label>
                        <input type="text"
                            name="name"
                            x-model="editUser.name"
                            required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    {{-- Alamat Email --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Alamat Email') }} *</label>
                        <input type="email"
                            name="email"
                            x-model="editUser.email"
                            required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                    </div>

                    {{-- Peran / Role --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">{{ __('Peran (Role & Hak Akses)') }} *</label>
                        <select name="role"
                            x-model="editUser.role"
                            required
                            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            @foreach ($roles as $r)
                            <option value="{{ $r->name }}">
                                {{ $roleDefinitions[$r->name]['icon'] ?? '👤' }} {{ $roleDefinitions[$r->name]['name'] ?? $r->name }} — {{ $roleDefinitions[$r->name]['description'] ?? '' }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Password Baru (Opsional) --}}
                    <div class="p-3.5 rounded-xl bg-gray-50 border border-gray-200 space-y-3">
                        <div class="text-xs text-ocean-700 font-semibold flex items-center gap-1.5">
                            <span>🔑</span>
                            <span>{{ __('Ubah Kata Sandi (Kosongkan jika tidak ingin diubah)') }}</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] text-gray-600 mb-1 font-medium">{{ __('Kata Sandi Baru') }}</label>
                                <input type="password"
                                    name="password"
                                    minlength="8"
                                    placeholder="Minimal 8 karakter"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-600 mb-1 font-medium">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
                                <input type="password"
                                    name="password_confirmation"
                                    minlength="8"
                                    placeholder="Ulangi kata sandi"
                                    class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-ocean-500/20 focus:border-ocean-500">
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Aksi Modal --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 mt-6">
                        <button type="button"
                            @click="showEditModal = false"
                            class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                            {{ __('Batal') }}
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                            {{ __('Perbarui Data') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL KONFIRMASI HAPUS PENGGUNA --}}
        {{-- ============================================================== --}}
        <div x-show="showDeleteModal"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs"
            @keydown.escape.window="showDeleteModal = false">
            <div @click.outside="showDeleteModal = false"
                class="w-full max-w-md rounded-2xl bg-white border border-gray-100 p-6 shadow-2xl text-gray-800">

                <div class="flex items-center gap-3 text-rose-600 mb-3">
                    <span class="p-3 rounded-xl bg-rose-50 text-2xl">🗑️</span>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ __('Konfirmasi Hapus Akun') }}</h3>
                        <p class="text-xs text-rose-600 font-medium">{{ __('Tindakan ini tidak dapat dibatalkan.') }}</p>
                    </div>
                </div>

                <p class="text-sm text-gray-600 mt-3">
                    {{ __('Apakah Anda yakin ingin menghapus akun pengguna') }}
                    <span class="font-bold text-gray-900" x-text="deleteUser.name"></span>
                    (<span class="text-ocean-600 font-medium text-xs" x-text="deleteUser.email"></span>)?
                </p>

                <form method="POST" :action="deleteActionUrl" class="mt-6 flex items-center justify-end gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button"
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 cursor-pointer">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold rounded-lg shadow-sm cursor-pointer">
                        {{ __('Ya, Hapus Akun') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>