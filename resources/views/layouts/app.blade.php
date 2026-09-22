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
                    }
                });
            });
        </script>

        <div x-data class="min-h-screen">
            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main Content Area --}}
            <div class="main-content" :class="$store.sidebar.collapsed ? 'sidebar-collapsed' : ''">
                {{-- Top Header Bar --}}
                <header class="top-header">
                    <div class="flex items-center gap-2.5">
                        {{-- Toggle Sidebar Button --}}
                        <button @click="window.innerWidth < 769 ? $store.sidebar.toggleMobile() : $store.sidebar.toggle()"
                                class="p-1 rounded-lg text-ocean-300 hover:text-white hover:bg-white/10 transition-colors"
                                title="{{ __('Toggle Sidebar') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">✅</span>
                                <span class="text-sm font-semibold">{{ session('success') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-700 text-sm font-bold p-1 rounded-lg hover:bg-emerald-100 transition">✕</button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">⚠️</span>
                                <span class="text-sm font-semibold">{{ session('error') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-700 text-sm font-bold p-1 rounded-lg hover:bg-rose-100 transition">✕</button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div x-data="{ show: true }" x-show="show" x-transition class="mb-5 p-4 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 flex items-center justify-between shadow-sm" role="alert">
                            <div class="flex items-center gap-2.5">
                                <span class="text-xl">ℹ️</span>
                                <span class="text-sm font-semibold">{{ session('info') }}</span>
                            </div>
                            <button type="button" @click="show = false" class="text-sky-500 hover:text-sky-700 text-sm font-bold p-1 rounded-lg hover:bg-sky-100 transition">✕</button>
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
