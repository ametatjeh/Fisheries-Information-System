<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem Perikanan') }} - Masuk</title>
        <meta name="description" content="Sistem Data dan Statistik Perikanan — Masuk ke Sistem">
        <link rel="icon" type="image/png" href="{{ asset('Logo.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=outfit:400,500,600,700,800|inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            *, *::before, *::after {
                box-sizing: border-box;
            }

            :root {
                --primary: #0284c7;
                --primary-hover: #0369a1;
                --primary-glow: rgba(14, 165, 233, 0.45);
                --accent-cyan: #06b6d4;
                --bg-deep: #070b14;
                --glass-bg: rgba(13, 20, 38, 0.65);
                --glass-border: rgba(255, 255, 255, 0.12);
                --glass-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7), 0 0 0 1px rgba(255, 255, 255, 0.08);
            }

            /* Fullscreen Vanta Canvas Container */
            #vanta-background {
                position: fixed;
                inset: 0;
                width: 100vw;
                height: 100vh;
                z-index: 0;
                background: radial-gradient(circle at 50% 50%, #0a172e 0%, #050a12 100%);
            }

            /* Ambient Glow Blobs behind glass */
            .ambient-glow-1 {
                position: fixed;
                top: 20%;
                left: 25%;
                width: 380px;
                height: 380px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(14, 165, 233, 0.28) 0%, rgba(14, 165, 233, 0) 70%);
                filter: blur(55px);
                z-index: 1;
                pointer-events: none;
                animation: pulseGlow 8s ease-in-out infinite alternate;
            }

            .ambient-glow-2 {
                position: fixed;
                bottom: 15%;
                right: 25%;
                width: 420px;
                height: 420px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(6, 182, 212, 0.22) 0%, rgba(6, 182, 212, 0) 70%);
                filter: blur(60px);
                z-index: 1;
                pointer-events: none;
                animation: pulseGlow 10s ease-in-out infinite alternate-reverse;
            }

            @keyframes pulseGlow {
                0% { transform: scale(0.9) translate(-10px, -10px); opacity: 0.7; }
                100% { transform: scale(1.15) translate(15px, 15px); opacity: 1; }
            }

            /* Disable background 3D canvas and glow animation on mobile (< 768px) */
            @media (max-width: 767.98px) {
                #vanta-background {
                    display: none !important;
                }
                .ambient-glow-1,
                .ambient-glow-2 {
                    display: none !important;
                    animation: none !important;
                }
            }

            /* Viewport Layout */
            .login-wrapper {
                position: relative;
                z-index: 10;
                width: 100%;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px 16px;
                box-sizing: border-box;
            }

            /* Glassmorphism Card Container */
            .glass-card {
                position: relative;
                width: 100%;
                max-width: 460px;
                background: var(--glass-bg);
                -webkit-backdrop-filter: blur(24px) saturate(190%);
                backdrop-filter: blur(24px) saturate(190%);
                border: 1px solid var(--glass-border);
                border-radius: 24px;
                padding: 36px 32px 28px;
                box-shadow: var(--glass-shadow);
                animation: cardAppear 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
                overflow: hidden;
            }

            /* Top shine gradient highlight */
            .glass-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), rgba(14, 165, 233, 0.9), rgba(6, 182, 212, 0.8), transparent);
            }

            /* Subtle radial corner glow */
            .glass-card::after {
                content: '';
                position: absolute;
                top: -100px;
                right: -100px;
                width: 220px;
                height: 220px;
                border-radius: 50%;
                background: radial-gradient(circle, rgba(14, 165, 233, 0.15) 0%, transparent 70%);
                pointer-events: none;
            }

            @keyframes cardAppear {
                0% {
                    opacity: 0;
                    transform: translateY(24px) scale(0.97);
                }
                100% {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            /* Brand Header & Logo */
            .brand-header {
                text-align: center;
                margin-bottom: 24px;
            }

            .logo-wrap {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 78px;
                height: 78px;
                border-radius: 5px;
                background: rgba(255, 255, 255, 0.08);
                border: 1px solid rgba(255, 255, 255, 0.2);
                -webkit-backdrop-filter: blur(12px);
                backdrop-filter: blur(12px);
                box-shadow: 0 12px 28px rgba(0, 0, 0, 0.4), inset 0 1px 1px rgba(255, 255, 255, 0.25);
                margin-bottom: 14px;
                position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .logo-wrap:hover {
                transform: translateY(-3px) scale(1.05);
                box-shadow: 0 16px 36px var(--primary-glow), inset 0 1px 1px rgba(255, 255, 255, 0.4);
            }

            .logo-wrap img {
                width: 52px;
                height: 52px;
                object-fit: contain;
                filter: drop-shadow(0 4px 8px rgba(0,0,0,0.35));
            }

            .brand-title {
                font-size: 23px;
                font-weight: 800;
                letter-spacing: -0.02em;
                color: #ffffff;
                line-height: 1.2;
                margin-bottom: 4px;
                background: linear-gradient(135deg, #ffffff 40%, #bae6fd 100%);
                -webkit-background-clip: text;
                background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .brand-subtitle {
                font-size: 13px;
                color: #94a3b8;
                font-weight: 400;
            }

            /* Submit Button with Shimmer */
            /* Glasses Glassmorphism Submit Button with dynamic hover glow */
            .btn-submit {
                position: relative;
                width: 100%;
                height: 48px;
                background: linear-gradient(135deg, rgba(2, 132, 199, 0.5) 0%, rgba(14, 165, 233, 0.35) 50%, rgba(6, 182, 212, 0.5) 100%);
                -webkit-backdrop-filter: blur(20px);
                backdrop-filter: blur(20px);
                color: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.35);
                border-radius: 14px;
                font-size: 14.5px;
                font-weight: 700;
                letter-spacing: -0.01em;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
                box-shadow: 0 10px 25px -5px rgba(2, 132, 199, 0.45), inset 0 1px 1px 0 rgba(255, 255, 255, 0.5);
                transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
                overflow: hidden;
            }

            .btn-submit::before {
                content: '';
                position: absolute;
                top: 0;
                left: -120%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.45), transparent);
                transition: 0.6s ease;
            }

            .btn-submit:hover {
                transform: translateY(-2.5px) scale(1.015);
                background: linear-gradient(135deg, rgba(2, 132, 199, 0.72) 0%, rgba(14, 165, 233, 0.55) 50%, rgba(6, 182, 212, 0.72) 100%);
                border-color: rgba(255, 255, 255, 0.65);
                box-shadow: 0 18px 36px -6px rgba(14, 165, 233, 0.65), 0 0 24px rgba(6, 182, 212, 0.45), inset 0 1px 2px 0 rgba(255, 255, 255, 0.75);
                color: #ffffff;
            }

            .btn-submit:hover::before {
                left: 120%;
            }

            .btn-submit:active {
                transform: translateY(0) scale(0.98);
                box-shadow: 0 6px 18px rgba(14, 165, 233, 0.4), inset 0 1px 1px 0 rgba(255, 255, 255, 0.3);
            }

            /* Glasses Glassmorphism Back Button */
            .btn-glass-back {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.12) 0%, rgba(255, 255, 255, 0.05) 100%);
                -webkit-backdrop-filter: blur(16px);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.22);
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25), inset 0 1px 1px rgba(255, 255, 255, 0.35);
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .btn-glass-back:hover {
                transform: translateY(-2px) scale(1.05);
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.22) 0%, rgba(14, 165, 233, 0.25) 100%);
                border-color: rgba(56, 189, 248, 0.6);
                color: #ffffff !important;
                box-shadow: 0 8px 20px -4px rgba(14, 165, 233, 0.45), 0 0 15px rgba(56, 189, 248, 0.35), inset 0 1px 1px rgba(255, 255, 255, 0.6);
            }

            .btn-glass-back:active {
                transform: translateY(0) scale(0.96);
            }

            /* Glasses Glassmorphism Secondary Button (Google SSO) */
            .btn-glass-secondary {
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, rgba(255, 255, 255, 0.03) 100%);
                -webkit-backdrop-filter: blur(16px);
                backdrop-filter: blur(16px);
                border: 1px solid rgba(255, 255, 255, 0.18);
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25), inset 0 1px 1px rgba(255, 255, 255, 0.2);
                color: #e2e8f0;
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            }

            .btn-glass-secondary:hover {
                transform: translateY(-2px) scale(1.015);
                background: linear-gradient(135deg, rgba(255, 255, 255, 0.16) 0%, rgba(255, 255, 255, 0.08) 100%);
                border-color: rgba(255, 255, 255, 0.4);
                color: #ffffff;
                box-shadow: 0 12px 28px -5px rgba(0, 0, 0, 0.5), 0 0 18px rgba(255, 255, 255, 0.15), inset 0 1px 1px rgba(255, 255, 255, 0.45);
            }

            .btn-glass-secondary:active {
                transform: translateY(0) scale(0.98);
            }
        </style>
    </head>
    <body class="font-sans antialiased text-white bg-[#060a12] overflow-x-hidden">
        {{-- Vanta.js 3D Ocean Waves Canvas --}}
        <div id="vanta-background"></div>

        {{-- Ambient Glowing Blobs --}}
        <div class="ambient-glow-1"></div>
        <div class="ambient-glow-2"></div>

        <div class="login-wrapper">
            <div class="glass-card">
                {{-- Tombol Kembali (Sudut Kiri Atas Card) --}}
                <a href="{{ route('home') }}" 
                   class="btn-glass-back absolute top-4 left-4 z-20 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-slate-200 hover:text-white text-xs font-semibold backdrop-blur-md transition-all shadow-sm group"
                   title="{{ __('Kembali ke Beranda') }}">
                    <svg class="w-3.5 h-3.5 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    <span>{{ __('Kembali') }}</span>
                </a>

                {{-- Header / Logo --}}
                <div class="brand-header">
                    <a href="/" class="inline-block">
                        <div class="logo-wrap">
                            <img src="{{ asset('Logo.png') }}" alt="Logo">
                        </div>
                    </a>
                    <h1 class="brand-title">Sistem Perikanan</h1>
                    <p class="brand-subtitle">{{ __('Pangkalan Data & Statistik Perikanan Tangkap') }}</p>
                </div>

                {{-- Slot Form Content --}}
                {{ $slot }}

                {{-- Footer --}}
                <div class="mt-6 pt-4 border-t border-white/10 text-center text-xs text-ocean-400">
                    Copyright &copy; {{ date('Y') }} <a href="https://nagakecil.site/" target="_blank" rel="noopener noreferrer" class="text-ocean-300 hover:text-white underline underline-offset-2 transition-colors">nagakecil</a>. All Rights Reserved.
                </div>
            </div>
        </div>

        {{-- Three.js & Vanta.js Waves Animation Scripts --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r134/three.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.waves.min.js"></script>

        <script>
            let vantaEffect = null;
            function initVanta() {
                // Khusus tampilan mobile (< 768px), jangan render efek animasi 3D Vanta
                if (window.innerWidth < 768) {
                    if (vantaEffect) {
                        try {
                            vantaEffect.destroy();
                        } catch (e) {}
                        vantaEffect = null;
                    }
                    return;
                }

                if (vantaEffect) {
                    return;
                }

                if (window.VANTA && window.VANTA.WAVES) {
                    try {
                        vantaEffect = window.VANTA.WAVES({
                            el: "#vanta-background",
                            mouseControls: true,
                            touchControls: true,
                            gyroControls: false,
                            minHeight: 200.00,
                            minWidth: 200.00,
                            scale: 1.00,
                            scaleMobile: 1.00,
                            color: 0x071529,         // Ocean Deep Navy base
                            shininess: 45.00,        // Glossy wave crests
                            waveHeight: 19.00,       // Elegant wave height
                            waveSpeed: 0.75,         // Smooth wave movement
                            zoom: 0.88               // Perspective
                        });
                    } catch (e) {
                        console.warn("Vanta.js Waves init error:", e);
                    }
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                initVanta();
            });

            window.addEventListener('resize', () => {
                initVanta();
            });

            window.addEventListener('beforeunload', () => {
                if (vantaEffect) {
                    vantaEffect.destroy();
                }
            });
        </script>
    </body>
</html>
