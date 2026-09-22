<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;

class ProviderController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // If user exists but doesn't have google_id, update it
                if (! $user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                    ]);
                }
                Auth::login($user);
            } else {
                // Create a new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'email_verified_at' => now(), // Assume Google accounts are verified
                ]);
                Auth::login($user);
            }

            // Pastikan user memiliki role agar authorization tidak undefined
            if ($user->roles->isEmpty()) {
                Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
                $hasSuperAdmin = User::role('super-admin')->exists();
                $defaultRoleName = $hasSuperAdmin ? 'viewer' : 'super-admin';
                $role = Role::firstOrCreate(['name' => $defaultRoleName, 'guard_name' => 'web']);
                $user->assignRole($role);
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect()->route('login')->withErrors(['google' => 'Gagal masuk menggunakan Google. Silakan coba lagi.']);
        }
    }
}
