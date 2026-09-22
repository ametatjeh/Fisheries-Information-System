<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ ($activeMenu ?? '') === 'map' ? ('Peta Perikanan | ' . config('app.name', 'Sistem Perikanan')) : (config('app.name', 'Sistem Perikanan') . ' — Tata Kelola Data Perikanan Tangkap') }}</title>
    <meta name="description" content="Sistem Informasi dan Statistik Perikanan Tangkap terintegrasi berbasis standar FAO ASFIS & CWP ISSCFG dengan pelacakan data lapangan, validasi, analisis CPUE, dan pemetaan spasial GIS.">
    <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Glassmorphism custom styling utilities */
        @keyframes float-slow {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(25px, -20px) scale(1.06); }
        }
        @keyframes float-reverse {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(-20px, 20px) scale(0.96); }
        }
        @keyframes float-subtle {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(15px, 15px); }
        }
        .animate-float-slow { animation: float-slow 16s ease-in-out infinite; }
        .animate-float-reverse { animation: float-reverse 20s ease-in-out infinite; }
        .animate-float-subtle { animation: float-subtle 14s ease-in-out infinite; }

        .glass-panel {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.09) 0%, rgba(255, 255, 255, 0.03) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.16);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }
        .glass-panel-hover:hover {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.15) 0%, rgba(255, 255, 255, 0.06) 100%);
            border-color: rgba(147, 197, 253, 0.5);
            box-shadow: 0 20px 40px -10px rgba(37, 99, 235, 0.35), inset 0 1px 1px 0 rgba(255, 255, 255, 0.3);
        }
        .glass-card {
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.75) 0%, rgba(15, 23, 42, 0.45) 100%);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.1);
        }
        .glass-orb {
            border-radius: 9999px;
            filter: blur(100px);
            opacity: 0.45;
            mix-blend-mode: screen;
            pointer-events: none;
        }
        .flow-glow-line {
            background: linear-gradient(180deg, #3b82f6 0%, #10b981 50%, #3b82f6 100%);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.5);
        }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased text-slate-100 bg-slate-950 min-h-screen flex flex-col selection:bg-ocean-500 selection:text-white relative overflow-x-hidden"
      x-data="{
          activeMenu: '{{ $activeMenu ?? 'beranda' }}',
          mobileMenuOpen: false,
          setMenu(name) {
              this.activeMenu = name;
              if (history.pushState) {
                  const url = name === 'beranda' ? '/' : '/' + (name === 'dataflow' ? 'data-flow' : name);
                  window.history.pushState({path: url}, '', url);
              }
              window.scrollTo({ top: 0, behavior: 'smooth' });
              if (name === 'statistik' || name === 'map' || name === 'beranda') {
                  setTimeout(() => {
                      window.dispatchEvent(new Event('resize'));
                      window.dispatchEvent(new CustomEvent(name + '-tab-activated'));
                  }, 150);
                  setTimeout(() => {
                      window.dispatchEvent(new Event('resize'));
                  }, 400);
              }
          }
      }"
      x-init="$watch('activeMenu', val => {
          if (val === 'statistik' || val === 'map' || val === 'beranda') {
              setTimeout(() => {
                  window.dispatchEvent(new Event('resize'));
                  window.dispatchEvent(new CustomEvent(val + '-tab-activated'));
              }, 200);
          }
      })">

    {{-- Background Ambient Gradient Lights (Glasses Glassmorphism) --}}
    <div class="fixed inset-0 pointer-events-none z-0 overflow-hidden">
        {{-- Floating colorful luminous glowing glass orbs --}}
        <div class="absolute -top-32 left-1/4 w-[520px] h-[520px] bg-gradient-to-tr from-cyan-500/25 to-blue-600/35 glass-orb animate-float-slow"></div>
        <div class="absolute top-1/4 -right-24 w-[560px] h-[560px] bg-gradient-to-bl from-emerald-500/20 via-teal-500/20 to-cyan-500/20 glass-orb animate-float-reverse"></div>
        <div class="absolute top-2/3 -left-32 w-[620px] h-[620px] bg-gradient-to-tr from-indigo-600/25 via-blue-600/20 to-teal-500/15 glass-orb animate-float-subtle"></div>
        <div class="absolute -bottom-24 right-1/4 w-[520px] h-[520px] bg-gradient-to-tl from-ocean-500/25 to-violet-600/20 glass-orb animate-float-slow"></div>

        {{-- Glassmorphism Ambient Mesh & Grid --}}
        <div class="absolute inset-0 bg-[linear-gradient(to_right,rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(to_bottom,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:3.5rem_3.5rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_40%,#000_70%,transparent_100%)]"></div>
        <div class="absolute inset-0 bg-gradient-to-b from-slate-950/70 via-slate-950/40 to-slate-950/90 backdrop-blur-[1px]"></div>
    </div>

    {{-- ============================================================== --}}
    {{-- TOP STICKY NAVBAR                                              --}}
    {{-- ============================================================== --}}
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-slate-950/80 border-b border-white/10 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">

                {{-- Logo & Brand --}}
                <a href="{{ route('home') }}" @click.prevent="setMenu('beranda')" class="flex items-center gap-3 group cursor-pointer">
                    <div class="w-11 h-11 rounded-xl bg-white p-1.5 shadow-lg shadow-ocean-500/20 flex items-center justify-center transform group-hover:scale-105 transition-transform duration-300">
                        <img src="{{ asset('Logo.png') }}" alt="Logo Sistem Perikanan" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="font-extrabold text-base sm:text-lg tracking-tight text-white flex items-center gap-1.5">
                            <span>IKAN KECIL</span>
                            <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-full bg-ocean-500/20 text-ocean-300 border border-ocean-500/30">V1.2</span>
                        </div>
                        <p class="text-[11px] text-ocean-300 font-medium tracking-wide">Integrated Fisheries Information System</p>
                    </div>
                </a>

                {{-- Desktop 4 Menus Navigation --}}
                <nav class="hidden md:flex items-center gap-4 p-2 px-3 rounded-2xl glass-panel">
                    {{-- 1. HOME --}}
                    <button type="button"
                            @click="setMenu('beranda')"
                            class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center"
                            :class="activeMenu === 'beranda' ? 'bg-ocean-600 text-white shadow-lg shadow-ocean-600/30' : 'text-slate-300 hover:text-white hover:bg-white/5'">
                        <span>HOME</span>
                    </button>

                    {{-- 2. CHART --}}
                    <button type="button"
                            @click="setMenu('workflow')"
                            class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer flex items-center justify-center"
                            :class="activeMenu === 'workflow' ? 'bg-ocean-600 text-white shadow-lg shadow-ocean-600/30' : 'text-slate-300 hover:text-white hover:bg-white/5'">
                        <span>CHART</span>
                    </button>

                    {{-- 3. DATA FLOW — hidden --}}

                    {{-- 4. STATISTIK --}}
                    <button type="button"
                            @click="setMenu('statistik')"
                            class="px-6 py-2.5 rounded-xl text-xs sm:text-sm font-bold uppercase tracking-wider transition-all cursor-pointer flex items-center gap-1.5"
                            :class="activeMenu === 'statistik' ? 'bg-ocean-600 text-white shadow-lg shadow-ocean-600/30' : 'text-slate-300 hover:text-white hover:bg-white/5'">
                        <span>📊</span>
                        <span>STATISTIK</span>
                    </button>
                </nav>

                {{-- Action Right (Auth CTA) --}}
                <div class="hidden sm:flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold uppercase tracking-wider text-white glass-panel glass-panel-hover border border-white/20 hover:border-cyan-400/50 shadow-lg shadow-ocean-600/20 transition-all transform hover:-translate-y-0.5">
                            <span>{{ __('DASHBOARD') }}</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center px-5 py-2.5 rounded-2xl text-xs sm:text-sm font-bold uppercase tracking-wider text-white glass-panel glass-panel-hover border border-white/20 hover:border-cyan-400/50 shadow-lg shadow-ocean-600/20 transition-all transform hover:-translate-y-0.5">
                            <span>{{ __('LOGIN') }}</span>
                        </a>
                    @endauth
                </div>

                {{-- Mobile Hamburger --}}
                <div class="flex md:hidden items-center gap-2">
                    <button type="button"
                            @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-xl glass-panel text-slate-300 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path x-show="mobileMenuOpen" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Dropdown Menu --}}
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-white/10 glass-card px-4 py-4 space-y-2">
            <button @click="setMenu('beranda'); mobileMenuOpen = false;" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center" :class="activeMenu === 'beranda' ? 'bg-ocean-600 text-white' : 'text-slate-300 hover:bg-white/5'">
                <span>HOME</span>
            </button>
            <button @click="setMenu('workflow'); mobileMenuOpen = false;" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center" :class="activeMenu === 'workflow' ? 'bg-ocean-600 text-white' : 'text-slate-300 hover:bg-white/5'">
                <span>CHART</span>
            </button>
            {{-- DATA FLOW mobile button — hidden --}}

            <button @click="setMenu('statistik'); mobileMenuOpen = false;" class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-bold uppercase tracking-wider flex items-center gap-2" :class="activeMenu === 'statistik' ? 'bg-ocean-600 text-white' : 'text-slate-300 hover:bg-white/5'">
                <span>📊</span> <span>STATISTIK</span>
            </button>
            <div class="pt-3 border-t border-white/10">
                @auth
                    <a href="{{ route('dashboard') }}" class="block text-center py-2.5 glass-panel text-white rounded-xl text-sm font-bold uppercase tracking-wider hover:bg-white/10">
                        {{ __('DASHBOARD') }}
                    </a>
                @else
                    <a href="{{ route('login') }}" class="block text-center py-2.5 glass-panel text-white rounded-xl text-sm font-bold uppercase tracking-wider hover:bg-white/10">
                        {{ __('LOGIN') }}
                    </a>
                @endauth
            </div>
        </div>
    </header>

    {{-- ============================================================== --}}
    {{-- MAIN VIEW CONTAINER                                            --}}
    {{-- ============================================================== --}}
    <main class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full flex flex-col">

        {{-- ========================================================== --}}
        {{-- PAGE 1: OPERATIONAL OVERVIEW & SYSTEM INTELLIGENCE (BERANDA)--}}
        {{-- ========================================================== --}}
        <section x-show="activeMenu === 'beranda'" x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="w-full">
            @include('landing.home')
        </section>

        {{-- ========================================================== --}}
        {{-- PAGE 2: WORK FLOW (GLASSMORPHISM ARCHITECTURE CHART)       --}}
        {{-- ========================================================== --}}
        <section x-show="activeMenu === 'workflow'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-12">
            
            {{-- Glassmorphism Circular 2x2 Square Flowchart Container --}}
            <div class="max-w-6xl mx-auto relative px-2 sm:px-4">
                
                {{-- Glowing Orbit Line in Background (Desktop) --}}
                <div class="hidden md:block absolute inset-6 lg:inset-10 rounded-[36px] border border-dashed border-cyan-500/25 pointer-events-none shadow-[inset_0_0_80px_rgba(6,182,212,0.08)]"></div>
                
                {{-- Central Circular Glowing Hub --}}
                <div class="hidden lg:flex absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-20 flex-col items-center justify-center w-36 h-36 rounded-full glass-card border-2 border-cyan-400/40 shadow-[0_0_60px_rgba(6,182,212,0.3)] backdrop-blur-xl text-center p-3">
                    <div class="text-2xl animate-spin [animation-duration:16s]">🔄</div>
                    <div class="text-[11px] font-extrabold text-white uppercase tracking-wider mt-1">Alur Melingkar</div>
                    <div class="text-[9px] text-cyan-300 font-semibold tracking-tight">Siklus Data</div>
                </div>

                {{-- Connector Badge 1 -> 2 (Top Center: Horizontal Right) --}}
                <div class="hidden md:flex absolute top-[-16px] left-1/2 -translate-x-1/2 z-30 items-center justify-center">
                    <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full glass-panel border border-cyan-400/40 bg-slate-950/90 text-cyan-300 text-xs font-bold shadow-lg shadow-cyan-500/20">
                        <span>Langkah 1</span>
                        <span class="text-sm font-black animate-pulse">→</span>
                        <span>Langkah 2</span>
                    </div>
                </div>

                {{-- Connector Badge 2 -> 3 (Right Center: Vertical Down) --}}
                <div class="hidden md:flex absolute top-1/2 -right-4 lg:-right-6 -translate-y-1/2 z-30 items-center justify-center">
                    <div class="flex flex-col items-center gap-1 px-2.5 py-3 rounded-full glass-panel border border-emerald-400/40 bg-slate-950/90 text-emerald-300 text-xs font-bold shadow-lg shadow-emerald-500/20">
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tahap 2</span>
                        <span class="text-sm font-black animate-bounce">↓</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tahap 3</span>
                    </div>
                </div>

                {{-- Connector Badge 3 -> 4 (Bottom Center: Horizontal Left) --}}
                <div class="hidden md:flex absolute bottom-[-16px] left-1/2 -translate-x-1/2 z-30 items-center justify-center">
                    <div class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full glass-panel border border-blue-400/40 bg-slate-950/90 text-blue-300 text-xs font-bold shadow-lg shadow-blue-500/20">
                        <span>Tahap 4</span>
                        <span class="text-sm font-black animate-pulse">←</span>
                        <span>Tahap 3</span>
                    </div>
                </div>

                {{-- Connector Badge 4 -> 1 (Left Center: Vertical Up - Closing the Loop!) --}}
                <div class="hidden md:flex absolute top-1/2 -left-4 lg:-left-6 -translate-y-1/2 z-30 items-center justify-center">
                    <div class="flex flex-col items-center gap-1 px-2.5 py-3 rounded-full glass-panel border border-cyan-400/40 bg-slate-950/90 text-cyan-300 text-xs font-bold shadow-lg shadow-cyan-500/20">
                        <span class="text-[10px] font-bold uppercase tracking-wider">Tahap 1</span>
                        <span class="text-sm font-black animate-bounce">↑</span>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Siklus 4</span>
                    </div>
                </div>

                {{-- 2x2 Square Grid --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-10 relative z-10">

                    {{-- STAGE 1: MASTER DATA (Top-Left on Desktop: col 1, row 1) --}}
                    <div class="md:col-start-1 md:row-start-1 flex flex-col">
                        <div class="w-full h-full glass-card glass-panel-hover p-6 sm:p-7 rounded-3xl shadow-2xl border border-white/20 flex flex-col justify-between relative group">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-500/30 text-blue-300 border border-blue-400/40 text-xs font-mono font-bold tracking-wider">TAHAP 01</span>
                                    <div class="w-8 h-8 rounded-lg bg-white p-1 shadow-md shadow-ocean-500/20 flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                        <img src="{{ asset('Logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black text-white tracking-wide text-center uppercase">{{ __('MASTER DATA') }}</h3>
                                <p class="text-xs text-center text-ocean-200 mt-1 mb-5">{{ __('Pondasi referensi data terstandardisasi nasional & internasional') }}</p>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5">
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">🗺️</div>
                                        <div class="text-xs font-bold text-white">{{ __('Wilayah') }}</div>
                                        <div class="text-[10px] text-slate-400">Prov / Kab / Kec</div>
                                    </div>
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">👨‍🌾</div>
                                        <div class="text-xs font-bold text-white">{{ __('Nelayan') }}</div>
                                        <div class="text-[10px] text-slate-400">KUB & Perorangan</div>
                                    </div>
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">🚢</div>
                                        <div class="text-xs font-bold text-white">{{ __('Kapal') }}</div>
                                        <div class="text-[10px] text-slate-400">GT, Mesin, Izin</div>
                                    </div>
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">🐟</div>
                                        <div class="text-xs font-bold text-white">{{ __('Jenis Ikan') }}</div>
                                        <div class="text-[10px] text-slate-400">FAO ASFIS (13.965)</div>
                                    </div>
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">🎣</div>
                                        <div class="text-xs font-bold text-white">{{ __('Alat Tangkap') }}</div>
                                        <div class="text-[10px] text-slate-400">FAO ISSCFG (88)</div>
                                    </div>
                                    <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                        <div class="text-base mb-1">⚓</div>
                                        <div class="text-xs font-bold text-white">{{ __('Landing Site') }}</div>
                                        <div class="text-[10px] text-slate-400">PPI & Pangkalan</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/10">
                                <div class="glass-panel p-2.5 rounded-xl text-center border border-white/10 hover:border-ocean-400/40 transition-colors">
                                    <span class="text-xs font-bold text-white">📍 {{ __('Fishing Ground') }}</span>
                                    <span class="text-[10px] text-slate-400 ml-1.5">— WPP-NRI 571 & 572</span>
                                </div>
                            </div>
                        </div>
                        {{-- Mobile Arrow Down --}}
                        <div class="md:hidden py-3 flex flex-col items-center text-cyan-400">
                            <span class="text-xl font-bold">↓ Menuju Tahap 02</span>
                        </div>
                    </div>

                    {{-- STAGE 2: DATA COLLECTION (Top-Right on Desktop: col 2, row 1) --}}
                    <div class="md:col-start-2 md:row-start-1 flex flex-col">
                        <div class="w-full h-full glass-card glass-panel-hover p-6 sm:p-7 rounded-3xl shadow-2xl border border-white/20 flex flex-col justify-between relative group">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/30 text-emerald-300 border border-emerald-400/40 text-xs font-mono font-bold tracking-wider">TAHAP 02</span>
                                    <div class="w-8 h-8 rounded-lg bg-white p-1 shadow-md shadow-emerald-500/20 flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                        <img src="{{ asset('Logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black text-white tracking-wide text-center uppercase">{{ __('DATA COLLECTION') }}</h3>
                                <p class="text-xs text-center text-emerald-200 mt-1 mb-5">{{ __('Perekaman operasional penangkapan ikan di perairan & pelabuhan') }}</p>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 hover:border-emerald-400/40 transition-colors">
                                        <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span>{{ __('Fishing Trip') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-300 mt-1">Keberangkatan & kedatangan kapal, durasi melaut, BBM.</p>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 hover:border-emerald-400/40 transition-colors">
                                        <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span>{{ __('Logbook') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-300 mt-1">Catatan harian koordinat setting/hauling dan cuaca.</p>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 hover:border-emerald-400/40 transition-colors">
                                        <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span>{{ __('Effort') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-300 mt-1">Jumlah mata pancing, tarikan jaring, dan jam operasi.</p>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 hover:border-emerald-400/40 transition-colors">
                                        <div class="text-sm font-bold text-white flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                            <span>{{ __('Catch') }}</span>
                                        </div>
                                        <p class="text-[11px] text-slate-300 mt-1">Komposisi berat (kg) dan jenis ikan tangkapan.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/10">
                                <div class="glass-panel p-2.5 rounded-xl border border-white/10 hover:border-emerald-400/40 transition-colors text-center">
                                    <span class="text-xs font-bold text-emerald-300">⚓ {{ __('Landing') }}</span>
                                    <span class="text-[11px] text-slate-300 ml-1.5">— Pendaratan di TPI, nilai produksi (Rp), dan mutu</span>
                                </div>
                            </div>
                        </div>
                        {{-- Mobile Arrow Down --}}
                        <div class="md:hidden py-3 flex flex-col items-center text-emerald-400">
                            <span class="text-xl font-bold">↓ Menuju Tahap 03</span>
                        </div>
                    </div>

                    {{-- STAGE 3: REPORTING (Bottom-Right on Desktop: col 2, row 2) --}}
                    <div class="md:col-start-2 md:row-start-2 flex flex-col">
                        <div class="w-full h-full glass-card glass-panel-hover p-6 sm:p-7 rounded-3xl shadow-2xl border border-white/20 flex flex-col justify-between relative group">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2.5 py-0.5 rounded-full bg-blue-500/30 text-blue-300 border border-blue-400/40 text-xs font-mono font-bold tracking-wider">TAHAP 03</span>
                                    <div class="w-8 h-8 rounded-lg bg-white p-1 shadow-md shadow-blue-500/20 flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                        <img src="{{ asset('Logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black text-white tracking-wide text-center uppercase">{{ __('REPORTING') }}</h3>
                                <p class="text-xs text-center text-blue-200 mt-1 mb-5">{{ __('Penyusunan laporan berkala multi-format untuk pemangku kepentingan') }}</p>
                                
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 text-center">
                                    <div class="glass-panel p-3 rounded-xl border border-white/10">
                                        <div class="text-base mb-1">📋</div>
                                        <div class="text-xs font-bold text-white">{{ __('Tabel') }}</div>
                                        <div class="text-[10px] text-slate-400">Data Tabular</div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10">
                                        <div class="text-base mb-1">📊</div>
                                        <div class="text-xs font-bold text-white">{{ __('Grafik') }}</div>
                                        <div class="text-[10px] text-slate-400">Tren Visual</div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10">
                                        <div class="text-base mb-1">📉</div>
                                        <div class="text-xs font-bold text-white">{{ __('Statistik') }}</div>
                                        <div class="text-[10px] text-slate-400">Kuartalan/Tahunan</div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10">
                                        <div class="text-base mb-1">📗</div>
                                        <div class="text-xs font-bold text-white">{{ __('Excel') }}</div>
                                        <div class="text-[10px] text-slate-400">Export XLSX</div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 col-span-2 sm:col-span-2">
                                        <div class="text-base mb-1">📕</div>
                                        <div class="text-xs font-bold text-white">{{ __('PDF') }}</div>
                                        <div class="text-[10px] text-slate-400">Cetak Dokumen Resmi</div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/10">
                                <div class="glass-panel p-2.5 rounded-xl border border-white/10 text-center">
                                    <span class="text-xs font-bold text-blue-300">📄 Format Ekspor Standar</span>
                                    <span class="text-[10px] text-slate-400 ml-1.5">— Kompatibel Satu Data Indonesia</span>
                                </div>
                            </div>
                        </div>
                        {{-- Mobile Arrow Down --}}
                        <div class="md:hidden py-3 flex flex-col items-center text-blue-400">
                            <span class="text-xl font-bold">↓ Menuju Tahap Akhir 04</span>
                        </div>
                    </div>

                    {{-- STAGE 4: DASHBOARD & GIS (Bottom-Left on Desktop: col 1, row 2) --}}
                    <div class="md:col-start-1 md:row-start-2 flex flex-col">
                        <div class="w-full h-full glass-card glass-panel-hover p-6 sm:p-7 rounded-3xl shadow-2xl border border-cyan-400/40 relative group ring-2 ring-cyan-500/20 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <span class="px-2.5 py-0.5 rounded-full bg-cyan-500/30 text-cyan-300 border border-cyan-400/40 text-xs font-mono font-bold tracking-wider">TAHAP AKHIR (04)</span>
                                    <div class="w-8 h-8 rounded-lg bg-white p-1 shadow-md shadow-cyan-500/20 flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                        <img src="{{ asset('Logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                                    </div>
                                </div>
                                <h3 class="text-2xl font-black text-white tracking-wide text-center uppercase">{{ __('DASHBOARD & GIS') }}</h3>
                                <p class="text-xs text-center text-cyan-200 mt-1 mb-5">{{ __('Visualisasi spasial geografi kelautan & penganalisisan sebaran sumber daya') }}</p>
                                
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-cyan-500/20 text-cyan-300 flex items-center justify-center text-lg">🚢</div>
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ __('Peta Kapal') }}</div>
                                            <div class="text-[10px] text-slate-400">Distribusi armada</div>
                                        </div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-emerald-500/20 text-emerald-300 flex items-center justify-center text-lg">📍</div>
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ __('Fishing Ground') }}</div>
                                            <div class="text-[10px] text-slate-400">Zona tangkap WPP</div>
                                        </div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-amber-500/20 text-amber-300 flex items-center justify-center text-lg">⚓</div>
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ __('Landing Site') }}</div>
                                            <div class="text-[10px] text-slate-400">Sebaran PPI/TPI</div>
                                        </div>
                                    </div>
                                    <div class="glass-panel p-3 rounded-xl border border-white/10 flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-lg bg-purple-500/20 text-purple-300 flex items-center justify-center text-lg">🌊</div>
                                        <div>
                                            <div class="text-xs font-bold text-white">{{ __('Sebaran Catch') }}</div>
                                            <div class="text-[10px] text-slate-400">Heatmap densitas</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-4 pt-3 border-t border-white/10">
                                <div class="glass-panel p-2.5 rounded-xl border border-white/10 text-center">
                                    <span class="text-xs font-bold text-cyan-300">📊 {{ __('Statistik Perikanan') }}</span>
                                    <span class="text-[11px] text-slate-300 ml-1.5">— Integrasi analitik spasial & produksi</span>
                                </div>
                            </div>
                        </div>
                        {{-- Mobile Circular Loop Indicator --}}
                        <div class="md:hidden py-3 flex flex-col items-center text-cyan-400 text-xs font-semibold">
                            <span>🔄 Melingkar kembali ke Tahap 01 (Master Data)</span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- KAMUS & DEFINISI — moved from home page                    --}}
            {{-- ========================================================== --}}
            <div class="glass-card p-6 sm:p-7 rounded-3xl border border-white/15 space-y-4"
                 x-data="{ activeDef: null }">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>📖</span>
                        <span>{{ __("Kamus & Definisi Entitas Perikanan (Data Dictionary)") }}</span>
                    </h3>
                    <span class="text-[10px] font-mono text-slate-400">{{ __("Standar Terminologi Domain") }}</span>
                </div>
                <div class="space-y-2 text-xs">
                    <div class="border border-white/10 rounded-xl overflow-hidden bg-slate-900/60">
                        <button @click="activeDef = (activeDef === 1 ? null : 1)" class="w-full p-3 text-left font-bold text-white flex items-center justify-between hover:bg-white/5 transition">
                            <span>⚓ Fishing Trip (Pelayaran Penangkapan Ikan)</span>
                            <span x-text="activeDef === 1 ? '▲' : '▼'" class="text-cyan-400 font-mono"></span>
                        </button>
                        <div x-show="activeDef === 1" x-cloak class="p-3 pt-0 text-slate-300 border-t border-white/5 leading-relaxed text-[11px]">
                            Satu siklus utuh pelayaran kapal penangkap ikan dari pelabuhan/pangkalan keberangkatan menuju perairan penangkapan hingga kembali mendarat di pelabuhan pendaratan (TPI) dengan membawa hasil tangkapan.
                        </div>
                    </div>
                    <div class="border border-white/10 rounded-xl overflow-hidden bg-slate-900/60">
                        <button @click="activeDef = (activeDef === 2 ? null : 2)" class="w-full p-3 text-left font-bold text-white flex items-center justify-between hover:bg-white/5 transition">
                            <span>🎯 Fishing Effort (Upaya Penangkapan / Setting)</span>
                            <span x-text="activeDef === 2 ? '▲' : '▼'" class="text-cyan-400 font-mono"></span>
                        </button>
                        <div x-show="activeDef === 2" x-cloak class="p-3 pt-0 text-slate-300 border-t border-white/5 leading-relaxed text-[11px]">
                            Besaran intensitas pengoperasian alat penangkap ikan, dihitung berdasarkan frekuensi penurunan alat (setting), durasi perendaman alat tangkap (soaking time dalam jam), jumlah mata pancing, atau panjang rentang jaring.
                        </div>
                    </div>
                    <div class="border border-white/10 rounded-xl overflow-hidden bg-slate-900/60">
                        <button @click="activeDef = (activeDef === 3 ? null : 3)" class="w-full p-3 text-left font-bold text-white flex items-center justify-between hover:bg-white/5 transition">
                            <span>🐟 Catch vs. Landing (Tangkapan & Pendaratan)</span>
                            <span x-text="activeDef === 3 ? '▲' : '▼'" class="text-cyan-400 font-mono"></span>
                        </button>
                        <div x-show="activeDef === 3" x-cloak class="p-3 pt-0 text-slate-300 border-t border-white/5 leading-relaxed text-[11px]">
                            <strong>Catch</strong> adalah volume fisik ikan yang tertangkap saat alat ditarik di laut (termasuk ikan target, bycatch, dan discard). <strong>Landing</strong> adalah volume ikan yang berhasil dibawa ke darat dan ditimbang di Tempat Pendaratan Ikan (TPI) untuk transaksi pelelangan/pemasaran.
                        </div>
                    </div>
                    <div class="border border-white/10 rounded-xl overflow-hidden bg-slate-900/60">
                        <button @click="activeDef = (activeDef === 4 ? null : 4)" class="w-full p-3 text-left font-bold text-white flex items-center justify-between hover:bg-white/5 transition">
                            <span>📊 CPUE (Catch Per Unit Effort)</span>
                            <span x-text="activeDef === 4 ? '▲' : '▼'" class="text-cyan-400 font-mono"></span>
                        </button>
                        <div x-show="activeDef === 4" x-cloak class="p-3 pt-0 text-slate-300 border-t border-white/5 leading-relaxed text-[11px]">
                            Indikator baku produktivitas dan kelimpahan stok ikan, dihitung dari rasio total berat tangkapan (kg) dibagi total upaya tangkap (jam operasi alat atau siklus trip pelayaran).
                        </div>
                    </div>
                    <div class="border border-white/10 rounded-xl overflow-hidden bg-slate-900/60">
                        <button @click="activeDef = (activeDef === 5 ? null : 5)" class="w-full p-3 text-left font-bold text-white flex items-center justify-between hover:bg-white/5 transition">
                            <span>🛡️ RZWP3K (Rencana Zonasi Wilayah Pesisir dan Pulau-Pulau Kecil)</span>
                            <span x-text="activeDef === 5 ? '▲' : '▼'" class="text-cyan-400 font-mono"></span>
                        </button>
                        <div x-show="activeDef === 5" x-cloak class="p-3 pt-0 text-slate-300 border-t border-white/5 leading-relaxed text-[11px]">
                            Rencana tata ruang laut pesisir Aceh berdasarkan Qanun Aceh No. 1 Tahun 2020 yang membagi ruang laut menjadi Kawasan Pemanfaatan Umum (KPU), Kawasan Konservasi (KK), Kawasan Strategis Nasional Tertentu (KSNT), dan Alur Laut (AL).
                        </div>
                    </div>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- SUMBER DATA — moved from home page                         --}}
            {{-- ========================================================== --}}
            <div class="glass-card p-6 sm:p-7 rounded-3xl border border-white/15 space-y-4 text-xs">
                <div class="flex items-center justify-between pb-3 border-b border-white/10">
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span>🌐</span>
                        <span>{{ __("Sumber Data, Metodologi & Batasan Sistem") }}</span>
                    </h3>
                    <span class="text-[10px] font-mono text-slate-400">{{ __("Metadata Transparan") }}</span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-slate-300 leading-relaxed text-[11px]">
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/5 space-y-2">
                        <div class="font-bold text-cyan-300 text-xs">🏛️ Data Internal & Verifikasi Lapangan</div>
                        <p>Data primer dicatat langsung oleh enumerator TPI dan nakhoda kapal, mencakup logbook perikanan tangkap, timbangan pendaratan ikan, dan sampling biologi yang diverifikasi berjenjang oleh syahbandar perikanan.</p>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-slate-900/70 border border-white/5 space-y-2">
                        <div class="font-bold text-emerald-300 text-xs">🛰️ Data Eksternal & Standar Internasional</div>
                        <p>Mengadopsi standar taksonomi <strong>FAO ASFIS</strong>, klasifikasi alat tangkap <strong>ISSCFG Annex M</strong>, batas maritim <strong>WPP-NRI 571/572</strong>, zonasi <strong>Qanun Aceh No. 1/2020</strong>, dan proksi observasi satelit AIS/VMS <strong>Global Fishing Watch (GFW) v3</strong>.</p>
                    </div>
                </div>
                <div class="pt-3 border-t border-white/5 flex flex-wrap items-center justify-between gap-3 text-[10px] text-slate-400 font-mono">
                    <span>Wilayah: Perairan Aceh & ZEE Indonesia (WPP 571 & 572)</span>
                    <span>CRS: WGS84 / EPSG:4326</span>
                    <span>Disclaimer: Tampilan spasial merupakan alat bantu analisis teknis.</span>
                </div>
            </div>

            {{-- ========================================================== --}}
            {{-- DOKUMEN RUJUKAN — moved from data-flow page               --}}
            {{-- ========================================================== --}}
            <div class="glass-card p-6 sm:p-8 rounded-3xl border border-white/15 space-y-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center text-2xl shadow-inner">🌐</div>
                    <div>
                        <h3 class="font-bold text-white text-lg">{{ __("Dokumen Rujukan di Aplikasi Ini") }}</h3>
                        <p class="text-xs text-slate-400">{{ __("Pedoman klasifikasi resmi global perikanan tangkap") }}</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                    <a href="https://www.fao.org/fishery/docs/DOCUMENT/cwp/handbook/annex/AnnexM2fishinggear.pdf" target="_blank" class="glass-panel p-4 rounded-xl border border-white/10 hover:border-blue-400/40 flex items-center justify-between group transition-all">
                        <div class="space-y-0.5">
                            <div class="text-sm font-bold text-white group-hover:text-ocean-300 transition-colors">FAO CWP ISSCFG Annex M</div>
                            <div class="text-xs text-slate-400">Klasifikasi Alat Tangkap Rev.1 (2016) &bull; Berkas PDF Resmi FAO</div>
                        </div>
                        <span class="text-ocean-400 text-base group-hover:translate-x-1 transition-transform">↗</span>
                    </a>
                    <a href="https://www.fao.org/fishery/en/collection/asfis" target="_blank" class="glass-panel p-4 rounded-xl border border-white/10 hover:border-blue-400/40 flex items-center justify-between group transition-all">
                        <div class="space-y-0.5">
                            <div class="text-sm font-bold text-white group-hover:text-ocean-300 transition-colors">FAO ASFIS List of Species</div>
                            <div class="text-xs text-slate-400">13.965 Spesies Statistik Perikanan &bull; Web Database FAO</div>
                        </div>
                        <span class="text-ocean-400 text-base group-hover:translate-x-1 transition-transform">↗</span>
                    </a>
                </div>
            </div>

        </section>



        {{-- DATA FLOW section — hidden
        <section x-show="activeMenu === 'dataflow'" ...>
        </section>
        --}}

        {{-- ========================================================== --}}
        {{-- PAGE 4: STATISTIK PUBLIK                                   --}}

        {{-- ========================================================== --}}
        <section x-show="activeMenu === 'statistik'" x-cloak x-transition:enter="transition ease-out duration-300 transform opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="flex-1 w-full">
            @include('landing.statistik')
        </section>

    </main>

    {{-- ============================================================== --}}
    {{-- FOOTER                                                         --}}
    {{-- ============================================================== --}}
    <footer class="border-t border-white/10 bg-slate-950/80 backdrop-blur-xl mt-auto relative z-10 text-xs text-slate-400 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-white p-1 shadow-md shrink-0 flex items-center justify-center">
                        <img src="{{ asset('Logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <div class="font-extrabold text-base sm:text-lg tracking-tight text-white">IKAN KECIL</div>
                    </div>
                </div>

                {{-- Social Links --}}
                <div class="flex items-center gap-3">
                    <a href="https://github.com/ametatjeh" target="_blank" rel="noopener noreferrer"
                       title="GitHub"
                       class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:text-white hover:bg-white/10 hover:border-white/20 transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="https://discord.com/login" target="_blank" rel="noopener noreferrer"
                       title="Discord"
                       class="w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-slate-300 hover:text-indigo-400 hover:bg-indigo-500/10 hover:border-indigo-400/30 transition-all">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.001.022.015.043.03.056a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                    </a>
                </div>

                <div class="text-slate-400 text-center md:text-right text-[11px]">
                    Copyright &copy; {{ date('Y') }} <a href="https://nagakecil.site/" target="_blank" rel="noopener noreferrer" class="font-bold text-blue-400 hover:text-blue-300 transition-colors">nagakecil</a>. All Rights Reserved.
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
