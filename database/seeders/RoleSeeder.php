<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Seed role dan permission sesuai spesifikasi hierarki sistem:
     *
     * 1. SUPER ADMIN      : Semua akses (Bypass via Gate::before)
     * 2. ADMIN            : Master Data, Analisis Statistik, Laporan
     * 3. VERIFIKATOR      : Validasi Data
     * 4. PETUGAS LAPANGAN : Nelayan, Trip, Logbook, Catch (Tangkapan/Upaya/Pendaratan)
     * 5. VIEWER           : Dashboard + Laporan
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ======================================
        // PERMISSIONS — Hak akses modul
        // ======================================
        $permissions = [
            'access.dashboard',
            'access.master',      // Wilayah, Species, Gears, Landing Sites, Groups, Vessels
            'access.fishermen',   // Master Nelayan
            'access.trips',       // Trip Penangkapan
            'access.logbooks',    // Logbook Kapal
            'access.catches',     // Hasil Tangkapan, Upaya Tangkap, Pendaratan
            'access.validation',  // Validasi Data
            'access.sampling',    // Sampling & Biologi
            'access.statistics',  // Statistik Perikanan
            'access.reports',     // Laporan & Ekspor
            'access.gis',         // GIS / Peta Perikanan
        ];

        foreach ($permissions as $permName) {
            Permission::firstOrCreate(['name' => $permName]);
        }

        // ======================================
        // ROLES & ASSIGNMENT
        // ======================================

        // 1. SUPER ADMIN (Semua Akses via Gate::before)
        $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        $superAdmin->syncPermissions($permissions);

        // 2. ADMIN: Master Data, Statistik, Laporan, GIS
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'access.dashboard',
            'access.master',
            'access.fishermen',
            'access.sampling',
            'access.statistics',
            'access.reports',
            'access.gis',
        ]);

        // 3. VERIFIKATOR: Validasi Data
        $verifikator = Role::firstOrCreate(['name' => 'verifikator']);
        $verifikator->syncPermissions([
            'access.dashboard',
            'access.validation',
        ]);
        // Alias legacy validator
        $validator = Role::firstOrCreate(['name' => 'validator']);
        $validator->syncPermissions([
            'access.dashboard',
            'access.validation',
        ]);

        // 4. PETUGAS LAPANGAN: Nelayan, Trip, Logbook, Catch, GIS
        $petugas = Role::firstOrCreate(['name' => 'petugas-lapangan']);
        $petugas->syncPermissions([
            'access.dashboard',
            'access.fishermen',
            'access.trips',
            'access.logbooks',
            'access.catches',
            'access.gis',
        ]);
        // Alias legacy enumerator
        $enumerator = Role::firstOrCreate(['name' => 'enumerator']);
        $enumerator->syncPermissions([
            'access.dashboard',
            'access.fishermen',
            'access.trips',
            'access.logbooks',
            'access.catches',
            'access.gis',
        ]);

        // 5. VIEWER: Dashboard + Laporan + GIS
        $viewer = Role::firstOrCreate(['name' => 'viewer']);
        $viewer->syncPermissions([
            'access.dashboard',
            'access.reports',
            'access.gis',
        ]);
    }
}
