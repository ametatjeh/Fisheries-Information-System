<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Analysis\CatchEstimationController;
use App\Http\Controllers\Analysis\SamplingController;
use App\Http\Controllers\Analysis\StatisticController;
use App\Http\Controllers\Analysis\ValidationController;
use App\Http\Controllers\Api\BigZeeApiController;
use App\Http\Controllers\Api\PublicStatisticsController;
use App\Http\Controllers\Api\Rzwp3kSpatialAnalysisController;
use App\Http\Controllers\Api\Rzwp3kZoneController;
use App\Http\Controllers\Api\StatisticsApiController;
use App\Http\Controllers\Auth\ProviderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataCollection\CatchController;
use App\Http\Controllers\DataCollection\FishingEffortController;
use App\Http\Controllers\DataCollection\FishingTripController;
use App\Http\Controllers\DataCollection\LandingController;
use App\Http\Controllers\DataCollection\LogbookController;
use App\Http\Controllers\Gfw\GfwGatewayController;
use App\Http\Controllers\Gfw\GfwMonitoringController;
use App\Http\Controllers\Gfw\GfwObservatoryController;
use App\Http\Controllers\Gfw\GfwVesselMonitoringController;
use App\Http\Controllers\GFWController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\Master\FisherGroupController;
use App\Http\Controllers\Master\FishermanController;
use App\Http\Controllers\Master\FishingGearController;
use App\Http\Controllers\Master\FishingGroundController;
use App\Http\Controllers\Master\LandingSiteController;
use App\Http\Controllers\Master\SpeciesController;
use App\Http\Controllers\Master\VesselController;
use App\Http\Controllers\Master\WilayahController;
use App\Http\Controllers\Output\GisController;
use App\Http\Controllers\Output\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\OrganizationSettingsController;
use Illuminate\Support\Facades\Route;

// Halaman Utama & Menu Landing Page Publik
Route::get('/', [LandingPageController::class, 'index'])->name('home');
Route::get('/home/search', [LandingPageController::class, 'globalSearch'])->name('home.search');
Route::get('/workflow', [LandingPageController::class, 'workflow'])->name('landing.workflow');
Route::get('/data-flow', [LandingPageController::class, 'dataFlow'])->name('landing.data-flow');
Route::get('/statistik', [LandingPageController::class, 'statistik'])->name('landing.statistik');
Route::get('/statistik/species/search', [LandingPageController::class, 'searchSpecies'])->name('landing.statistik.species-search');
Route::get('/gis/data', [GisController::class, 'data'])->name('gis.data');
Route::get('/api/rzwp3k/zones', [Rzwp3kZoneController::class, 'index'])->name('api.rzwp3k.zones');
Route::get('/api/rzwp3k/spatial/fishing-grounds', [Rzwp3kSpatialAnalysisController::class, 'fishingGrounds'])->name('api.rzwp3k.spatial.fishing-grounds');
Route::get('/api/rzwp3k/spatial/gfw', [Rzwp3kSpatialAnalysisController::class, 'gfwActivities'])->name('api.rzwp3k.spatial.gfw');

// BIG (Badan Informasi Geospasial) Official Maritime Boundaries API
Route::prefix('api/gis')->name('api.gis.')->group(function () {
    Route::get('/big/zee', [BigZeeApiController::class, 'index'])->name('big.zee');
    Route::get('/big/zee/aceh', [BigZeeApiController::class, 'aceh'])->name('big.zee.aceh');
});

// Public Statistics Portal — endpoint khusus /statistik (data publik agregat, tanpa field operasional sensitif)
Route::prefix('api/public')->name('api.public.')->group(function () {
    Route::get('/gis/data', [PublicStatisticsController::class, 'gisData'])->name('gis.data');
});

// Backward compatibility: /map redirects to /dashboard/gis with authentication
Route::get('/map', function () {
    return redirect()->route('dashboard.gis');
})->middleware(['auth', 'permission:access.gis'])->name('map');

// Dashboard (perlu login)
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Ganti bahasa
Route::get('/language/{locale}', function (string $locale) {
    if (in_array($locale, ['id', 'en'])) {
        session()->put('locale', $locale);
    }

    return redirect()->back();
})->name('language.switch');

// Route yang butuh autentikasi
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==========================================
    // 1. MASTER DATA
    // ==========================================
    // Master Wilayah, Species, Gears, Landing Sites, Groups, Vessels (Admin & Super Admin)
    Route::middleware('permission:access.master')->group(function () {
        // Master Data Wilayah
        Route::prefix('master/wilayah')->name('master.wilayah.')->group(function () {
            Route::get('/', [WilayahController::class, 'index'])->name('index');
            Route::post('/province', [WilayahController::class, 'storeProvince'])->name('province.store');
            Route::put('/province/{province}', [WilayahController::class, 'updateProvince'])->name('province.update');
            Route::delete('/province/{province}', [WilayahController::class, 'destroyProvince'])->name('province.destroy');
            Route::post('/regency', [WilayahController::class, 'storeRegency'])->name('regency.store');
            Route::put('/regency/{regency}', [WilayahController::class, 'updateRegency'])->name('regency.update');
            Route::delete('/regency/{regency}', [WilayahController::class, 'destroyRegency'])->name('regency.destroy');
            Route::post('/district', [WilayahController::class, 'storeDistrict'])->name('district.store');
            Route::put('/district/{district}', [WilayahController::class, 'updateDistrict'])->name('district.update');
            Route::delete('/district/{district}', [WilayahController::class, 'destroyDistrict'])->name('district.destroy');
            Route::post('/village', [WilayahController::class, 'storeVillage'])->name('village.store');
            Route::put('/village/{village}', [WilayahController::class, 'updateVillage'])->name('village.update');
            Route::delete('/village/{village}', [WilayahController::class, 'destroyVillage'])->name('village.destroy');
            Route::get('/api/regencies/{province}', [WilayahController::class, 'apiRegencies'])->name('api.regencies');
            Route::get('/api/districts/{regency}', [WilayahController::class, 'apiDistricts'])->name('api.districts');
            Route::get('/api/villages/{district}', [WilayahController::class, 'apiVillages'])->name('api.villages');
        });

        // Master Data Jenis Ikan
        Route::prefix('master/species')->name('master.species.')->group(function () {
            Route::get('/search', [SpeciesController::class, 'search'])->name('search');
            Route::get('/import', [SpeciesController::class, 'importView'])->name('import.view');
            Route::get('/import/template', [SpeciesController::class, 'importTemplate'])->name('import.template');
            Route::post('/import/preview', [SpeciesController::class, 'importPreview'])->name('import.preview');
            Route::post('/import/process', [SpeciesController::class, 'importProcess'])->name('import.process');
            Route::get('/export', [SpeciesController::class, 'export'])->name('export');
            Route::post('/bulk-update-status', [SpeciesController::class, 'bulkUpdateStatus'])->name('bulk-update-status');
            Route::get('/', [SpeciesController::class, 'index'])->name('index');
            Route::post('/', [SpeciesController::class, 'store'])->name('store');
            Route::get('/{species}', [SpeciesController::class, 'show'])->name('show');
            Route::put('/{species}', [SpeciesController::class, 'update'])->name('update');
            Route::delete('/{species}', [SpeciesController::class, 'destroy'])->name('destroy');
            Route::patch('/{species}/toggle-status', [SpeciesController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Master Data Alat Tangkap
        Route::prefix('master/gears')->name('master.gears.')->group(function () {
            Route::get('/', [FishingGearController::class, 'index'])->name('index');
            Route::post('/', [FishingGearController::class, 'store'])->name('store');
            Route::patch('/bulk-status', [FishingGearController::class, 'bulkUpdateStatus'])->name('bulk-status');
            Route::put('/{gear}', [FishingGearController::class, 'update'])->name('update');
            Route::delete('/{gear}', [FishingGearController::class, 'destroy'])->name('destroy');
            Route::patch('/{gear}/toggle-status', [FishingGearController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Master Data Tempat Pendaratan Ikan (Landing Sites)
        Route::prefix('master/landing-sites')->name('master.landing-sites.')->group(function () {
            Route::get('/', [LandingSiteController::class, 'index'])->name('index');
            Route::post('/', [LandingSiteController::class, 'store'])->name('store');
            Route::put('/{landingSite}', [LandingSiteController::class, 'update'])->name('update');
            Route::delete('/{landingSite}', [LandingSiteController::class, 'destroy'])->name('destroy');
            Route::patch('/{landingSite}/toggle-status', [LandingSiteController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Master Data Daerah Penangkapan (Fishing Grounds)
        Route::prefix('master/fishing-grounds')->name('master.fishing-grounds.')->group(function () {
            Route::get('/', [FishingGroundController::class, 'index'])->name('index');
            Route::post('/', [FishingGroundController::class, 'store'])->name('store');
            Route::put('/{fishingGround}', [FishingGroundController::class, 'update'])->name('update');
            Route::delete('/{fishingGround}', [FishingGroundController::class, 'destroy'])->name('destroy');
            Route::patch('/{fishingGround}/toggle-status', [FishingGroundController::class, 'toggleStatus'])->name('toggle-status');
        });

        // Master Data Kelompok Nelayan (Fisher Groups / KUB)
        Route::prefix('master/fisher-groups')->name('master.fisher-groups.')->group(function () {
            Route::get('/', [FisherGroupController::class, 'index'])->name('index');
            Route::post('/', [FisherGroupController::class, 'store'])->name('store');
            Route::put('/{fisherGroup}', [FisherGroupController::class, 'update'])->name('update');
            Route::delete('/{fisherGroup}', [FisherGroupController::class, 'destroy'])->name('destroy');
        });

        // Master Data Kapal (Vessels)
        Route::prefix('master/vessels')->name('master.vessels.')->group(function () {
            Route::get('/', [VesselController::class, 'index'])->name('index');
            Route::post('/', [VesselController::class, 'store'])->name('store');
            Route::put('/{vessel}', [VesselController::class, 'update'])->name('update');
            Route::delete('/{vessel}', [VesselController::class, 'destroy'])->name('destroy');
            Route::patch('/{vessel}/toggle-status', [VesselController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    // Master Data Nelayan (Admin, Petugas Lapangan, Super Admin)
    Route::middleware('permission:access.fishermen')->group(function () {
        Route::prefix('master/fishermen')->name('master.fishermen.')->group(function () {
            Route::get('/', [FishermanController::class, 'index'])->name('index');
            Route::post('/', [FishermanController::class, 'store'])->name('store');
            Route::put('/{fisherman}', [FishermanController::class, 'update'])->name('update');
            Route::delete('/{fisherman}', [FishermanController::class, 'destroy'])->name('destroy');
            Route::patch('/{fisherman}/toggle-status', [FishermanController::class, 'toggleStatus'])->name('toggle-status');
        });
    });

    // ==========================================
    // 2. PENGUMPULAN DATA (DATA COLLECTION)
    // ==========================================
    // Trip Penangkapan (Petugas Lapangan, Super Admin)
    Route::middleware('permission:access.trips')->group(function () {
        Route::prefix('trips')->name('trips.')->group(function () {
            Route::get('/', [FishingTripController::class, 'index'])->name('index');
            Route::post('/', [FishingTripController::class, 'store'])->name('store');
            Route::put('/{trip}', [FishingTripController::class, 'update'])->name('update');
            Route::delete('/{trip}', [FishingTripController::class, 'destroy'])->name('destroy');
            Route::patch('/{trip}/validate', [FishingTripController::class, 'validateTrip'])->name('validate');
        });
    });

    // Logbook Kapal (Petugas Lapangan, Super Admin)
    Route::middleware('permission:access.logbooks')->group(function () {
        Route::prefix('logbooks')->name('logbooks.')->group(function () {
            Route::get('/', [LogbookController::class, 'index'])->name('index');
            Route::post('/', [LogbookController::class, 'store'])->name('store');
            Route::put('/{logbook}', [LogbookController::class, 'update'])->name('update');
            Route::delete('/{logbook}', [LogbookController::class, 'destroy'])->name('destroy');
        });
    });

    // Catches, Fishing Effort & Landings (Petugas Lapangan, Super Admin)
    Route::middleware('permission:access.catches')->group(function () {
        // Fishing Efforts
        Route::prefix('efforts')->name('efforts.')->group(function () {
            Route::get('/', [FishingEffortController::class, 'index'])->name('index');
            Route::post('/', [FishingEffortController::class, 'store'])->name('store');
            Route::put('/{effort}', [FishingEffortController::class, 'update'])->name('update');
            Route::delete('/{effort}', [FishingEffortController::class, 'destroy'])->name('destroy');
        });

        // Fish Catches
        Route::prefix('catches')->name('catches.')->group(function () {
            Route::get('/', [CatchController::class, 'index'])->name('index');
            Route::post('/', [CatchController::class, 'store'])->name('store');
            Route::put('/{catch}', [CatchController::class, 'update'])->name('update');
            Route::delete('/{catch}', [CatchController::class, 'destroy'])->name('destroy');
        });

        // Fish Landings
        Route::prefix('landings')->name('landings.')->group(function () {
            Route::get('/', [LandingController::class, 'index'])->name('index');
            Route::post('/', [LandingController::class, 'store'])->name('store');
            Route::put('/{landing}', [LandingController::class, 'update'])->name('update');
            Route::delete('/{landing}', [LandingController::class, 'destroy'])->name('destroy');
        });
    });

    // ==========================================
    // 3. ANALISIS DATA
    // ==========================================
    // Validasi Data (Verifikator, Super Admin)
    Route::middleware('permission:access.validation')->group(function () {
        Route::prefix('analysis/validation')->name('analysis.validation.')->group(function () {
            Route::get('/', [ValidationController::class, 'index'])->name('index');
            Route::patch('/{trip}/status', [ValidationController::class, 'updateStatus'])->name('update-status');
            Route::get('/logs', [ValidationController::class, 'logs'])->name('logs');
            Route::get('/{trip}/audit', [ValidationController::class, 'audit'])->name('audit');
        });
    });

    // Sampling Biologi (Admin, Super Admin)
    Route::middleware('permission:access.sampling')->group(function () {
        Route::prefix('analysis/sampling')->name('analysis.sampling.')->group(function () {
            Route::get('/', [SamplingController::class, 'index'])->name('index');
            Route::post('/samples', [SamplingController::class, 'storeSample'])->name('samples.store');
            Route::put('/samples/{sample}', [SamplingController::class, 'updateSample'])->name('samples.update');
            Route::delete('/samples/{sample}', [SamplingController::class, 'destroySample'])->name('samples.destroy');
            Route::get('/samples/{sample}/specimens', [SamplingController::class, 'getSpecimens'])->name('samples.specimens');
            Route::post('/samples/{sample}/specimens', [SamplingController::class, 'storeMeasurement'])->name('samples.specimens.store');
            Route::delete('/measurements/{measurement}', [SamplingController::class, 'destroyMeasurement'])->name('measurements.destroy');
            Route::post('/plans', [SamplingController::class, 'storePlan'])->name('plans.store');
            Route::put('/plans/{plan}', [SamplingController::class, 'updatePlan'])->name('plans.update');
            Route::delete('/plans/{plan}', [SamplingController::class, 'destroyPlan'])->name('plans.destroy');
        });
    });

    // Statistik Perikanan (Admin, Super Admin)
    Route::middleware('permission:access.statistics')->group(function () {
        Route::prefix('analysis/statistics')->name('analysis.statistics.')->group(function () {
            Route::get('/', [StatisticController::class, 'index'])->name('index');
        });

        // Estimasi Tangkapan & Raising Factor
        Route::prefix('analysis/estimations')->name('analysis.estimations.')->group(function () {
            Route::get('/', [CatchEstimationController::class, 'index'])->name('index');
            Route::post('/generate', [CatchEstimationController::class, 'generate'])->name('generate');
            Route::patch('/{estimation}/status', [CatchEstimationController::class, 'updateStatus'])->name('update-status');
            Route::delete('/{estimation}', [CatchEstimationController::class, 'destroy'])->name('destroy');
        });

        // Statistics API (JSON endpoints for programmatic access)
        Route::prefix('api/statistics')->name('api.statistics.')->group(function () {
            Route::get('/kpi', [StatisticsApiController::class, 'kpi'])->name('kpi');
            Route::get('/production', [StatisticsApiController::class, 'production'])->name('production');
            Route::get('/trend', [StatisticsApiController::class, 'trend'])->name('trend');
            Route::get('/cpue', [StatisticsApiController::class, 'cpue'])->name('cpue');
        });
    });

    // ==========================================
    // 4. OUTPUT / LAPORAN
    // ==========================================
    // Laporan & Ekspor (Admin, Viewer, Super Admin)
    Route::middleware('permission:access.reports')->group(function () {
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/export', [ReportController::class, 'export'])->name('export');
            Route::get('/print', [ReportController::class, 'print'])->name('print');
        });
    });

    // GIS / Peta Perikanan (Admin, Petugas Lapangan, Viewer, Super Admin)
    Route::middleware('permission:access.gis')->group(function () {
        Route::get('/dashboard/gis', [GisController::class, 'index'])->name('dashboard.gis');
        Route::prefix('gis')->name('gis.')->group(function () {
            Route::get('/', [GisController::class, 'index'])->name('index');
        });
    });

    // GFW Satellite (Khusus Super Admin dan Administrator Data)
    Route::middleware(['role_or_permission:super-admin|admin|access.gfw'])->group(function () {
        Route::get('/gfw/monitoring', [GfwMonitoringController::class, 'index'])->name('gfw.monitoring');
        Route::get('/gfw/vessels', [GfwVesselMonitoringController::class, 'index'])->name('gfw.vessels');
        Route::get('/gfw/dashboard', [GfwVesselMonitoringController::class, 'dashboard'])->name('gfw.dashboard');
        Route::get('/gfw/observatory', [GfwObservatoryController::class, 'index'])->name('gfw.observatory');
    });

    // ==========================================
    // 5. ADMINISTRASI SISTEM (KHUSUS SUPER ADMIN)
    // ==========================================
    Route::middleware('role:super-admin')->prefix('admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // ==========================================
    // 6. PENGATURAN SISTEM
    // ==========================================
    Route::middleware('role:super-admin|admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/organization', [OrganizationSettingsController::class, 'edit'])->name('organization.edit');
        Route::put('/organization', [OrganizationSettingsController::class, 'update'])->name('organization.update');
    });
});

// SSO Google
Route::get('/auth/google/redirect', [ProviderController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [ProviderController::class, 'callback'])->name('auth.google.callback');

// Global Fishing Watch (GFW) Internal API Gateway with Rate Limiting (Single entry point for frontend)
Route::prefix('api/gfw')->name('api.gfw.')->middleware(['throttle:gfw-api'])->group(function () {
    // Health & Connectivity & Testing
    Route::get('/health', [GfwGatewayController::class, 'health'])->name('health');
    Route::get('/test', [GFWController::class, 'test'])->name('test');
    Route::get('/aoi/zee-aceh', [GFWController::class, 'zeeAcehAoi'])->name('aoi.zee-aceh');
    Route::get('/aoi/zee-indonesia-aceh', [GFWController::class, 'zeeIndonesiaAcehAoi'])->name('aoi.zee-indonesia-aceh');
    Route::get('/events/zee-indonesia-aceh', [GFWController::class, 'eventsZeeIndonesiaAceh'])->name('events.zee-indonesia-aceh');
    Route::get('/vessels/zee-indonesia-aceh', [GFWController::class, 'vesselsZeeIndonesiaAceh'])->name('vessels.zee-indonesia-aceh');
    Route::get('/vessels/{vessel}/track', [GFWController::class, 'vesselTrack'])->name('vessels.track');
    Route::get('/dashboard', [GFWController::class, 'dashboardSummary'])->name('dashboard.summary');
    Route::get('/spatial/fishing-grounds', [GFWController::class, 'spatialFishingGrounds'])->name('spatial.fishing-grounds');

    // Vessel Identity & Search
    Route::get('/vessels', [GfwGatewayController::class, 'vessels'])->name('vessels.index');
    Route::get('/vessels/search', [GfwGatewayController::class, 'vessels'])->name('vessels.search');
    Route::get('/vessels/{id}', [GfwGatewayController::class, 'vesselShow'])->name('vessels.show');

    // Geographic & Maritime Regions
    Route::get('/regions', [GfwGatewayController::class, 'regions'])->name('regions.index');
    Route::get('/regions/{key}', [GfwGatewayController::class, 'regionShow'])->name('regions.show');
    Route::post('/regions/validate', [GfwGatewayController::class, 'validateGeometry'])->name('regions.validate');

    // Activity & Presence
    Route::get('/activity', [GfwGatewayController::class, 'activity'])->name('activity.index');
    Route::get('/activity/presence', [GfwGatewayController::class, 'activity'])->name('activity.presence');
    Route::get('/activity/vessels/{id}', [GfwGatewayController::class, 'vesselActivity'])->name('activity.vessel');

    // Events (Apparent Fishing, Encounters, Loitering, Port Visits)
    Route::get('/events', [GfwGatewayController::class, 'events'])->name('events.index');
    Route::get('/events/fishing', [GfwGatewayController::class, 'eventFishing'])->name('events.fishing');
    Route::get('/events/encounters', [GfwGatewayController::class, 'eventEncounters'])->name('events.encounters');
    Route::get('/events/loitering', [GfwGatewayController::class, 'eventLoitering'])->name('events.loitering');
    Route::get('/events/port-visits', [GfwGatewayController::class, 'eventPortVisits'])->name('events.port-visits');
    Route::get('/events/{id}', [GfwGatewayController::class, 'eventShow'])->name('events.show');

    // GFW Observatory — Local Database API (sistem_gfw)
    Route::prefix('observatory')->name('observatory.')->group(function () {
        Route::get('/vessels', [GfwObservatoryController::class, 'vessels'])->name('vessels.index');
        Route::get('/vessels/{gfwVesselId}', [GfwObservatoryController::class, 'vesselShow'])->name('vessels.show');
        Route::get('/vessels/{gfwVesselId}/presence', [GfwObservatoryController::class, 'vesselPresence'])->name('vessels.presence');
        Route::get('/stats', [GfwObservatoryController::class, 'stats'])->name('stats');
        Route::get('/sync-runs', [GfwObservatoryController::class, 'syncRuns'])->name('sync-runs');
        Route::get('/sync-status', [GfwObservatoryController::class, 'syncStatus'])->name('sync-status');
    });
});

require __DIR__.'/auth.php';
