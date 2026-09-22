<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Urutan penting: RoleSeeder harus dijalankan duluan
     * karena AdminSeeder membutuhkan role yang sudah ada.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,        // Role & permission
            AdminSeeder::class,       // User super-admin
            AcehProvinceSeeder::class, // Master wilayah Provinsi Aceh (Kode: 11)
            AcehRegencySeeder::class,  // Master 23 Kabupaten & Kota di Provinsi Aceh
            AcehDistrictSeeder::class, // Master Kecamatan di Provinsi Aceh
            AcehVillageSeeder::class,  // Master Gampong / Desa pesisir & pendaratan di Provinsi Aceh
            SpeciesSeeder::class,     // Master data jenis ikan (Pelagis, Demersal, Karang, Krustasea)
            FishingGearSeeder::class, // Master data alat penangkapan ikan (API)
            VesselTypeSeeder::class,  // Master data tipe armada kapal penangkap ikan
            LandingSiteSeeder::class, // Master data tempat pendaratan ikan (PPS, PPN, PPP, PPI, TPI)
            FisherGroupSeeder::class, // Master data kelompok usaha bersama nelayan (KUB)
            FishermanSeeder::class,   // Master data nelayan (Pelaku usaha penangkapan ikan)
            VesselSeeder::class,      // Master data armada kapal penangkap ikan
            FishingTripSeeder::class,   // Data pengumpulan trip penangkapan ikan
            LogbookSeeder::class,       // Data pengumpulan logbook harian kapal
            FishingEffortSeeder::class, // Data pengumpulan upaya penangkapan (fishing efforts)
            FishCatchSeeder::class,     // Data pengumpulan hasil tangkapan ikan (catches)
            LandingSeeder::class,       // Data pengumpulan pendaratan ikan (landings)
            ValidationLogSeeder::class, // Riwayat audit trail validasi data trip
            SamplingSeeder::class,      // Rencana program sampling & pengukuran morfometrik biologis
            ProductionStatisticSeeder::class, // Statistik produksi bulanan, estimasi tangkapan & CPUE
        ]);

    }
}
