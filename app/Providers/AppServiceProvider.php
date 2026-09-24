<?php

namespace App\Providers;

use App\Models\OrganizationSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Super-admin & developer bypass semua permission check.
        // Artinya user dengan role 'super-admin' atau 'developer' otomatis bisa
        // mengakses SEMUA fitur tanpa perlu assign permission satu per satu.
        Gate::before(function ($user, $ability) {
            return $user->hasRole(['super-admin', 'Super Admin', 'developer']) ? true : null;
        });

        // Rate Limiter for Internal GFW API Gateway (60 requests per minute)
        RateLimiter::for('gfw-api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())->response(function () {
                return response()->json([
                    'success' => false,
                    'source' => 'global_fishing_watch',
                    'error' => 'Terlalu banyak permintaan API GFW. Batas kuota adalah 60 request/menit.',
                    'status' => 429,
                ], 429);
            });
        });

        // Bagikan identitas organisasi ke seluruh views Blade secara global
        view()->composer('*', function ($view) {
            try {
                if (Schema::hasTable('application_settings')) {
                    $view->with('currentOrganization', OrganizationSetting::getSettings());
                }
            } catch (\Throwable) {
                // Jangan gagalkan bootstrap jika database belum siap
            }
        });
    }
}
