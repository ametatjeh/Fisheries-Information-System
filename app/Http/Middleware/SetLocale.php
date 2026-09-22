<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk menerapkan pilihan bahasa dari session.
 *
 * Cara kerja:
 * 1. Cek apakah ada 'locale' di session (diset saat user klik toggle bahasa)
 * 2. Jika ada, terapkan bahasa tersebut ke aplikasi
 * 3. Jika tidak ada, gunakan bahasa default dari config (id)
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (in_array($locale, ['id', 'en'])) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
