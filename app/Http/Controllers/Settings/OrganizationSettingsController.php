<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\OrganizationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrganizationSettingsController extends Controller
{
    /**
     * Tampilkan formulir pengaturan identitas organisasi.
     */
    public function edit(): View
    {
        $this->authorizeAdmin();

        $setting = OrganizationSetting::getSettings();

        return view('settings.organization', compact('setting'));
    }

    /**
     * Perbarui data identitas organisasi.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'organization_name' => ['required', 'string', 'max:255'],
            'organization_type' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'address' => ['nullable', 'string'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        $setting = OrganizationSetting::getSettings();

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo_path'] = $path;
        }

        // Pastikan nilai kosong disimpan sebagai null
        $validated['organization_type'] = ! empty(trim((string) ($validated['organization_type'] ?? '')))
            ? trim($validated['organization_type'])
            : null;

        $setting->update($validated);
        OrganizationSetting::clearCache();

        return redirect()->route('settings.organization.edit')
            ->with('success', __('Identitas organisasi berhasil diperbarui.'));
    }

    /**
     * Verifikasi hak akses administrator untuk mengubah pengaturan.
     */
    protected function authorizeAdmin(): void
    {
        $user = auth()->user();

        abort_unless(
            $user && ($user->hasRole(['super-admin', 'Super Admin', 'developer', 'admin']) || $user->can('manage.settings')),
            403,
            __('Anda tidak memiliki izin untuk mengelola pengaturan organisasi.')
        );
    }
}
