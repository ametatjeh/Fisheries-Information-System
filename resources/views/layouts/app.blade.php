<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Perikanan') }}</title>
        <meta name="description" content="Sistem Data dan Statistik Perikanan — Mengelola data perikanan secara terintegrasi">
        <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Alpine.js Store untuk sidebar state --}}
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('sidebar', {
                    collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
                    mobileOpen: false,

                    toggle() {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('sidebar-collapsed', this.collapsed);
                    },

                    toggleMobile() {
                        this.mobileOpen = !this.mobileOpen;
                    },

                    closeMobile() {
                        this.mobileOpen = false;
                    }
                });
            });
        </script>

        <div x-data
             @resize.window="if (window.innerWidth >= 769 && $store.sidebar.mobileOpen) $store.sidebar.mobileOpen = false"
             class="min-h-screen">
            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main Content Area --}}
            <div class="main-content" :class="$store.sidebar.collapsed ? 'sidebar-collapsed' : ''">
                {{-- Top Header Bar --}}
                <header class="top-header">
                    <div class="flex items-center gap-2.5">
                        {{-- Toggle Sidebar Button --}}
                        <button type="button"
                                @click="window.innerWidth < 769 ? $store.sidebar.toggleMobile() : $store.sidebar.toggle()"
                                class="flex items-center justify-center w-8 h-8 rounded-lg text-ocean-300 hover:text-white hover:bg-white/10 focus:outline-none transition-colors"
                                :aria-expanded="window.innerWidth < 769 ? $store.sidebar.mobileOpen.toString() : (!$store.sidebar.collapsed).toString()"
                                aria-label="{{ __('Toggle Sidebar') }}"
                                title="{{ __('Toggle Sidebar') }}">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        {{-- Page Heading --}}
                        @isset($header)
                            <h1 class="text-xs sm:text-sm font-semibold text-white tracking-wide">
                                {{ $header }}
                            </h1>
                        @endisset
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Language Toggle (Ikon Bahasa) --}}
                        <a href="{{ route('language.switch', app()->getLocale() === 'id' ? 'en' : 'id') }}"
                           class="flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium text-ocean-200 hover:text-white hover:bg-white/10 transition-colors"
                           title="{{ __('Ganti Bahasa') }}">
                            @if(app()->getLocale() === 'id')
                                🇬🇧 <span class="hidden sm:inline">EN</span>
                            @else
                                🇮🇩 <span class="hidden sm:inline">ID</span>
                            @endif
                        </a>

                        {{-- User Dropdown --}}
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="flex items-center justify-center w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-ocean-200 hover:text-white border border-white/15 focus:outline-none transition ease-in-out duration-150 shadow-xs" title="{{ Auth::user()->name }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <div class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                    @if(Auth::user()->roles->count() > 0)
                                        <div class="text-xs text-ocean-600 font-medium mt-0.5">{{ Auth::user()->roles->pluck('name')->join(', ') }}</div>
                                    @endif
                                </div>

                                <x-dropdown-link :href="route('profile.edit')">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        <span>{{ __('Profil') }}</span>
                                    </div>
                                </x-dropdown-link>

                                {{-- Language Toggle di dalam Dropdown --}}
                                <x-dropdown-link :href="route('language.switch', app()->getLocale() === 'id' ? 'en' : 'id')">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs">{{ app()->getLocale() === 'id' ? '🇬🇧' : '🇮🇩' }}</span>
                                        <span>{{ app()->getLocale() === 'id' ? __('English Version') : __('Bahasa Indonesia') }}</span>
                                    </div>
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        <div class="flex items-center gap-2 text-rose-600">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                            </svg>
                                            <span>{{ __('Keluar') }}</span>
                                        </div>
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>

                {{-- Page Content --}}
                <main class="p-6">
                    {{-- Flash Messages --}}
                    @if(session('success'))
                        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="mb-4 px-3.5 py-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 dark:bg-emerald-950/40 dark:border-emerald-800/60 dark:text-emerald-200 flex items-center justify-between gap-3 shadow-xs" role="alert">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-900/60 dark:text-emerald-300">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-xs sm:text-sm font-medium leading-relaxed">{{ session('success') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="flex-shrink-0 text-emerald-500 hover:text-emerald-700 hover:bg-emerald-100/70 dark:text-emerald-400 dark:hover:text-emerald-200 dark:hover:bg-emerald-900/50 p-1 rounded-lg transition-colors" aria-label="Tutup notifikasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="mb-4 px-3.5 py-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 dark:bg-rose-950/40 dark:border-rose-800/60 dark:text-rose-200 flex items-center justify-between gap-3 shadow-xs" role="alert">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-100 text-rose-600 dark:bg-rose-900/60 dark:text-rose-300">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-xs sm:text-sm font-medium leading-relaxed">{{ session('error') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="flex-shrink-0 text-rose-500 hover:text-rose-700 hover:bg-rose-100/70 dark:text-rose-400 dark:hover:text-rose-200 dark:hover:bg-rose-900/50 p-1 rounded-lg transition-colors" aria-label="Tutup notifikasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div x-data="{ show: true }" x-show="show" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="mb-4 px-3.5 py-2.5 rounded-xl bg-sky-50 border border-sky-200 text-sky-900 dark:bg-sky-950/40 dark:border-sky-800/60 dark:text-sky-200 flex items-center justify-between gap-3 shadow-xs" role="alert">
                            <div class="flex items-center gap-2.5 min-w-0">
                                <span class="flex-shrink-0 inline-flex items-center justify-center w-5 h-5 rounded-full bg-sky-100 text-sky-600 dark:bg-sky-900/60 dark:text-sky-300">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-xs sm:text-sm font-medium leading-relaxed">{{ session('info') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="flex-shrink-0 text-sky-500 hover:text-sky-700 hover:bg-sky-100/70 dark:text-sky-400 dark:hover:text-sky-200 dark:hover:bg-sky-900/50 p-1 rounded-lg transition-colors" aria-label="Tutup notifikasi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif

                    {!! $slot ?? '' !!}
                    @yield('content')
                </main>

            </div>
        </div>

        @stack('scripts')
    </body>
</html>
