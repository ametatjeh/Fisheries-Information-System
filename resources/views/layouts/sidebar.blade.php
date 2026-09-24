@php
    $authUser = auth()->user();

    // Hak akses navigasi sidebar konsisten 1-to-1 dengan route middleware Spatie / Gate::before
    $canMaster = (bool) $authUser?->can('access.master');
    $canFishermen = (bool) $authUser?->can('access.fishermen');
    $canTrips = (bool) $authUser?->can('access.trips');
    $canLogbooks = (bool) $authUser?->can('access.logbooks');
    $canCatches = (bool) $authUser?->can('access.catches');
    $canValidation = (bool) $authUser?->can('access.validation');
    $canSampling = (bool) $authUser?->can('access.sampling');
    $canStatistics = (bool) $authUser?->can('access.statistics');
    $canReports = (bool) $authUser?->can('access.reports');
    $canGis = (bool) $authUser?->can('access.gis');
    $canAdmin = (bool) ($authUser?->hasRole(['super-admin', 'Super Admin', 'developer']) || $authUser?->can('admin.users.index'));
@endphp

{{-- Sidebar Navigasi Utama --}}
{{-- Alpine.js digunakan untuk toggle collapse --}}
<aside
    x-data
    :class="{
        'collapsed': $store.sidebar.collapsed,
        'mobile-open': $store.sidebar.mobileOpen
    }"
    class="sidebar"
    id="main-sidebar"
>
    {{-- Brand / Logo & Mobile Close Button --}}
    <div class="sidebar-header-wrapper">
        <a href="{{ route('dashboard') }}"
           class="sidebar-brand group no-underline"
           @click="if ($store.sidebar.collapsed) { $event.preventDefault(); $store.sidebar.toggle(); }"
           :title="$store.sidebar.collapsed ? '{{ __('Perluas Menu') }}' : '{{ __('Dashboard') }}'">
            <div class="sidebar-brand-icon"
                 :title="$store.sidebar.collapsed ? '{{ __('Perluas Menu') }}' : '{{ __('Dashboard') }}'">
                {{-- Logo saat menu terbuka normal --}}
                <img class="sidebar-brand-logo"
                     src="{{ $currentOrganization?->logo_url ?? asset('Logo.png') }}" 
                     alt="Logo">

                {{-- Ikon Toggle Sidebar saat menu di-minimize --}}
                <svg class="sidebar-toggle-icon w-5 h-5 text-white" 
                     fill="none" 
                     stroke="currentColor" 
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </div>
            <div class="sidebar-brand-text">
                <div>Sistem Perikanan</div>
                <div class="text-xs font-normal text-ocean-400">Data & Statistik</div>
            </div>
        </a>

        {{-- Tombol Tutup Sidebar di Mobile (X) - disembunyikan sesuai permintaan --}}
        <button type="button"
                @click="$store.sidebar.mobileOpen = false"
                class="sidebar-close-btn hidden"
                style="display: none !important;"
                title="{{ __('Tutup Menu') }}"
                aria-label="{{ __('Tutup Menu') }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Menu Utama: Dashboard --}}
    <div class="sidebar-section">
        <div class="sidebar-section-title">{{ __('Menu Utama') }}</div>

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <span class="sidebar-link-icon">🏠</span>
            <span class="sidebar-link-text">{{ __('Dashboard') }}</span>
        </a>
    </div>

    {{-- Master Data --}}
    @if($canMaster || $canFishermen)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Master Data') }}</div>

            @if($canMaster)
                <a href="{{ route('master.wilayah.index') }}" class="sidebar-link {{ request()->routeIs('master.wilayah.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🌍</span>
                    <span class="sidebar-link-text">{{ __('Wilayah') }}</span>
                </a>
                <a href="{{ route('master.species.index') }}" class="sidebar-link {{ request()->routeIs('master.species.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🐠</span>
                    <span class="sidebar-link-text">{{ __('Jenis Ikan') }}</span>
                </a>
            @endif

            @if($canFishermen)
                <a href="{{ route('master.fishermen.index') }}" class="sidebar-link {{ request()->routeIs('master.fishermen.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">👨‍🌾</span>
                    <span class="sidebar-link-text">{{ __('Nelayan') }}</span>
                </a>
            @endif

            @if($canMaster)
                <a href="{{ route('master.fisher-groups.index') }}" class="sidebar-link {{ request()->routeIs('master.fisher-groups.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">👥</span>
                    <span class="sidebar-link-text">{{ __('Kelompok Nelayan') }}</span>
                </a>
                <a href="{{ route('master.vessels.index') }}" class="sidebar-link {{ request()->routeIs('master.vessels.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🚢</span>
                    <span class="sidebar-link-text">{{ __('Kapal') }}</span>
                </a>
                <a href="{{ route('master.gears.index') }}" class="sidebar-link {{ request()->routeIs('master.gears.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🎣</span>
                    <span class="sidebar-link-text">{{ __('Alat Tangkap') }}</span>
                </a>
                <a href="{{ route('master.landing-sites.index') }}" class="sidebar-link {{ request()->routeIs('master.landing-sites.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">⚓</span>
                    <span class="sidebar-link-text">{{ __('Landing Site') }}</span>
                </a>
                <a href="{{ route('master.fishing-grounds.index') }}" class="sidebar-link {{ request()->routeIs('master.fishing-grounds.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🌐</span>
                    <span class="sidebar-link-text">{{ __('Fishing Ground') }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- Pengumpulan Data --}}
    @if($canTrips || $canLogbooks || $canCatches)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Pengumpulan Data') }}</div>

            @if($canTrips)
                <a href="{{ route('trips.index') }}" class="sidebar-link {{ request()->routeIs('trips.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📝</span>
                    <span class="sidebar-link-text">{{ __('Trip Penangkapan') }}</span>
                </a>
            @endif

            @if($canLogbooks)
                <a href="{{ route('logbooks.index') }}" class="sidebar-link {{ request()->routeIs('logbooks.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📋</span>
                    <span class="sidebar-link-text">{{ __('Logbook') }}</span>
                </a>
            @endif

            @if($canCatches)
                <a href="{{ route('efforts.index') }}" class="sidebar-link {{ request()->routeIs('efforts.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">⚙️</span>
                    <span class="sidebar-link-text">{{ __('Fishing Effort') }}</span>
                </a>
                <a href="{{ route('catches.index') }}" class="sidebar-link {{ request()->routeIs('catches.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🐟</span>
                    <span class="sidebar-link-text">{{ __('Hasil Tangkapan') }}</span>
                </a>
                <a href="{{ route('landings.index') }}" class="sidebar-link {{ request()->routeIs('landings.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📦</span>
                    <span class="sidebar-link-text">{{ __('Pendaratan') }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- Analisis --}}
    @if($canValidation || $canSampling || $canStatistics)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Analisis') }}</div>

            @if($canValidation)
                <a href="{{ route('analysis.validation.index') }}" class="sidebar-link {{ request()->routeIs('analysis.validation.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">✅</span>
                    <span class="sidebar-link-text">{{ __('Validasi') }}</span>
                </a>
            @endif

            @if($canSampling)
                <a href="{{ route('analysis.sampling.index') }}" class="sidebar-link {{ request()->routeIs('analysis.sampling.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🔬</span>
                    <span class="sidebar-link-text">{{ __('Sampling') }}</span>
                </a>
            @endif

            @if($canStatistics)
                <a href="{{ route('analysis.estimations.index') }}" class="sidebar-link {{ request()->routeIs('analysis.estimations.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📈</span>
                    <span class="sidebar-link-text">{{ __('Estimasi') }}</span>
                </a>
                <a href="{{ route('analysis.statistics.index') }}" class="sidebar-link {{ request()->routeIs('analysis.statistics.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📊</span>
                    <span class="sidebar-link-text">{{ __('Statistik') }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- Output / Laporan & GIS --}}
    @if($canReports || $canGis)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Output') }}</div>

            @if($canReports)
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">📄</span>
                    <span class="sidebar-link-text">{{ __('Laporan') }}</span>
                </a>
            @endif

            @if($canGis)
                <a href="{{ route('dashboard.gis') }}" class="sidebar-link {{ request()->routeIs('dashboard.gis') || request()->routeIs('gis.*') ? 'active' : '' }}">
                    <span class="sidebar-link-icon">🗺️</span>
                    <span class="sidebar-link-text">{{ __('Peta Terpadu GIS') }}</span>
                </a>
            @endif
        </div>
    @endif

    {{-- GFW Satellite Observatory --}}
    @if($canGis)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('GFW Satellite') }}</div>

            <a href="{{ route('gfw.monitoring') }}" class="sidebar-link {{ request()->routeIs('gfw.monitoring') ? 'active' : '' }}">
                <span class="sidebar-link-icon">🛰️</span>
                <span class="sidebar-link-text">{{ __('GFW Monitoring') }}</span>
            </a>
            <a href="{{ route('gfw.vessels') }}" class="sidebar-link {{ request()->routeIs('gfw.vessels') || request()->routeIs('gfw.observatory') ? 'active' : '' }}" title="{{ __('GFW Vessel Monitoring') }}">
                <span class="sidebar-link-icon">🔭</span>
                <span class="sidebar-link-text">{{ __('GFW Vessel Observatory') }}</span>
                <span class="sr-only">{{ __('GFW Vessel Monitoring') }}</span>
            </a>
        </div>
    @endif

    {{-- Pengaturan Sistem (Khusus Super Admin / Admin) --}}
    @if($canAdmin)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Pengaturan') }}</div>
            <a href="{{ route('settings.organization.edit') }}" class="sidebar-link {{ request()->routeIs('settings.organization.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon">🏢</span>
                <span class="sidebar-link-text">{{ __('Identitas Organisasi') }}</span>
            </a>
            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span class="sidebar-link-icon">👥</span>
                <span class="sidebar-link-text">{{ __('Manajemen Pengguna') }}</span>
            </a>
        </div>
    @endif

    {{-- Profil Akun --}}
    <div class="sidebar-section" style="margin-top: auto; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 0.5rem; padding-bottom: 2rem;">
        <a href="{{ route('profile.edit') }}"
           class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
            <span class="sidebar-link-icon">⚙️</span>
            <span class="sidebar-link-text">{{ __('Profil Akun') }}</span>
        </a>
    </div>
</aside>

{{-- Mobile overlay --}}
<div x-data
     x-cloak
     x-show="$store.sidebar.mobileOpen"
     x-transition:enter="transition-opacity ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition-opacity ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click="$store.sidebar.mobileOpen = false"
     class="sidebar-overlay"
     :class="$store.sidebar.mobileOpen ? 'active' : ''"
     aria-hidden="true">
</div>
