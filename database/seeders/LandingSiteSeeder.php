<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\LandingSite;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

class LandingSiteSeeder extends Seeder
{
    /**
     * Seed master data pangkalan / tempat pendaratan ikan (Landing Sites):
     * - PPS: Pelabuhan Perikanan Samudera (cth: PPS Lampulo Banda Aceh)
     * - PPN: Pelabuhan Perikanan Nusantara (cth: PPN Idi Rayeuk Aceh Timur)
     * - PPP: Pelabuhan Perikanan Pantai (cth: PPP Labuhan Haji Aceh Selatan)
     * - PPI: Pangkalan Pendaratan Ikan (cth: PPI Ujong Baroh, PPI Lampuyang, PPI Sabang, PPI Simeulue)
     * - TPI: Tempat Pelelangan Ikan (cth: TPI Ulee Lheue, TPI Deah Glumpang, TPI Lhoknga, TPI Kajhu, TPI Singkil)
     * - Tradisional: Pangkalan Pendaratan Tradisional Nelayan Pesisir
     *
     * Idempoten: aman dijalankan berulang kali tanpa duplikasi data.
     */
    public function run(): void
    {
        $aceh = Province::where('code', '11')->first();
        if (! $aceh) {
            return;
        }

        $sites = [
            // 1. PPS Lampulo - Banda Aceh (Pangkalan Terbesar Kelas A di Aceh & WPPNRI 571/572)
            [
                'code' => 'PPS-LMP',
                'name' => 'PPS Lampulo (Pelabuhan Perikanan Samudera)',
                'site_type' => 'PPS',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2001', // Lampulo
                'address' => 'Kawasan Industri Pelabuhan Perikanan Samudera Lampulo, Kec. Kuta Raja, Kota Banda Aceh',
                'latitude' => 5.5866120,
                'longitude' => 95.3262450,
                'is_active' => true,
            ],

            // 2. TPI Ulee Lheue - Banda Aceh
            [
                'code' => 'TPI-ULH',
                'name' => 'TPI Ulee Lheue',
                'site_type' => 'TPI',
                'regency_code' => '11.71',
                'dist_code' => '11.71.01', // Meuraxa
                'vill_code' => '11.71.01.2001', // Ulee Lheue
                'address' => 'Komplek Dermaga Wisata & Nelayan Ulee Lheue, Kec. Meuraxa, Kota Banda Aceh',
                'latitude' => 5.5562000,
                'longitude' => 95.2921000,
                'is_active' => true,
            ],

            // 3. TPI Deah Glumpang - Banda Aceh
            [
                'code' => 'TPI-DGP',
                'name' => 'TPI Deah Glumpang',
                'site_type' => 'TPI',
                'regency_code' => '11.71',
                'dist_code' => '11.71.01', // Meuraxa
                'vill_code' => '11.71.01.2002', // Deah Glumpang
                'address' => 'Gampong Deah Glumpang, Kec. Meuraxa, Kota Banda Aceh',
                'latitude' => 5.5615000,
                'longitude' => 95.2850000,
                'is_active' => true,
            ],

            // 4. PPN Idi Rayeuk - Aceh Timur (Sentra Pendaratan Selat Malaka)
            [
                'code' => 'PPN-IDI',
                'name' => 'PPN Idi Rayeuk (Pelabuhan Perikanan Nusantara)',
                'site_type' => 'PPN',
                'regency_code' => '11.03',
                'dist_code' => '11.03.04', // Idi Rayeuk
                'vill_code' => '11.03.04.2001', // Kuala Idi
                'address' => 'Kuala Idi Rayeuk, Kec. Idi Rayeuk, Kab. Aceh Timur',
                'latitude' => 4.9754100,
                'longitude' => 97.7712300,
                'is_active' => true,
            ],

            // 5. PPI Ujong Baroh - Aceh Barat (Pantai Barat Selatan Aceh)
            [
                'code' => 'PPI-UJB',
                'name' => 'PPI Ujong Baroh Meulaboh',
                'site_type' => 'PPI',
                'regency_code' => '11.05',
                'dist_code' => '11.05.01', // Johan Pahlawan
                'vill_code' => '11.05.01.2001', // Ujong Baroh
                'address' => 'Gampong Ujong Baroh, Kec. Johan Pahlawan, Meulaboh, Kab. Aceh Barat',
                'latitude' => 4.1432000,
                'longitude' => 96.1287000,
                'is_active' => true,
            ],

            // 6. PPI Lampuyang - Pulo Aceh, Aceh Besar
            [
                'code' => 'PPI-LPY',
                'name' => 'PPI Lampuyang (Pulo Aceh)',
                'site_type' => 'PPI',
                'regency_code' => '11.06',
                'dist_code' => '11.06.21', // Pulo Aceh
                'vill_code' => '11.06.21.2002', // Lampuyang
                'address' => 'Gampong Lampuyang, Pulo Breueh, Kec. Pulo Aceh, Kab. Aceh Besar',
                'latitude' => 5.6667000,
                'longitude' => 95.1167000,
                'is_active' => true,
            ],

            // 7. TPI Lhoknga - Aceh Besar
            [
                'code' => 'TPI-LKN',
                'name' => 'TPI Lhoknga',
                'site_type' => 'TPI',
                'regency_code' => '11.06',
                'dist_code' => '11.06.02', // Lhoknga
                'vill_code' => null,
                'address' => 'Pantai Lhoknga, Kec. Lhoknga, Kab. Aceh Besar',
                'latitude' => 5.4851000,
                'longitude' => 95.2422000,
                'is_active' => true,
            ],

            // 8. TPI Kajhu - Aceh Besar
            [
                'code' => 'TPI-KJH',
                'name' => 'TPI Kajhu Baitussalam',
                'site_type' => 'TPI',
                'regency_code' => '11.06',
                'dist_code' => '11.06.19', // Baitussalam
                'vill_code' => '11.06.19.2002', // Kajhu
                'address' => 'Gampong Kajhu, Kec. Baitussalam, Kab. Aceh Besar',
                'latitude' => 5.5992000,
                'longitude' => 95.3688000,
                'is_active' => true,
            ],

            // 9. PPI Sabang / Iboih - Kota Sabang (Pulau Weh)
            [
                'code' => 'PPI-SBG',
                'name' => 'PPI Iboih Sabang',
                'site_type' => 'PPI',
                'regency_code' => '11.72',
                'dist_code' => '11.72.01', // Sukakarya
                'vill_code' => '11.72.01.2001', // Iboih
                'address' => 'Gampong Iboih, Kec. Sukakarya, Kota Sabang',
                'latitude' => 5.8672000,
                'longitude' => 95.2589000,
                'is_active' => true,
            ],

            // 10. PPP Labuhan Haji - Aceh Selatan
            [
                'code' => 'PPP-LBH',
                'name' => 'PPP Labuhan Haji (Pelabuhan Perikanan Pantai)',
                'site_type' => 'PPP',
                'regency_code' => '11.01',
                'dist_code' => null,
                'vill_code' => null,
                'address' => 'Pesisir Dermaga Labuhan Haji, Kab. Aceh Selatan',
                'latitude' => 3.5510000,
                'longitude' => 97.0210000,
                'is_active' => true,
            ],

            // 11. PPI Suka Jaya Simeulue - Kepulauan Simeulue
            [
                'code' => 'PPI-SML',
                'name' => 'PPI Suka Jaya Sinabang',
                'site_type' => 'PPI',
                'regency_code' => '11.17',
                'dist_code' => '11.17.01', // Simeulue Timur
                'vill_code' => '11.17.01.2001', // Suka Jaya
                'address' => 'Gampong Suka Jaya, Sinabang, Kec. Simeulue Timur, Kab. Simeulue',
                'latitude' => 2.4789000,
                'longitude' => 96.3811000,
                'is_active' => true,
            ],

            // 12. TPI Pulo Sarok - Aceh Singkil
            [
                'code' => 'TPI-SKL',
                'name' => 'TPI Pulo Sarok Singkil',
                'site_type' => 'TPI',
                'regency_code' => '11.18',
                'dist_code' => '11.18.01', // Singkil
                'vill_code' => '11.18.01.2001', // Pulo Sarok
                'address' => 'Gampong Pulo Sarok, Kec. Singkil, Kab. Aceh Singkil',
                'latitude' => 2.2798000,
                'longitude' => 96.7589000,
                'is_active' => true,
            ],

            // 13. Pangkalan Pendaratan Tradisional Gugop - Pulo Aceh
            [
                'code' => 'PPT-GGP',
                'name' => 'Pangkalan Pendaratan Tradisional Gugop',
                'site_type' => 'pangkalan_pendaratan_tradisional',
                'regency_code' => '11.06',
                'dist_code' => '11.06.21', // Pulo Aceh
                'vill_code' => '11.06.21.2001', // Gugop
                'address' => 'Pantai Gampong Gugop, Pulo Breueh, Kec. Pulo Aceh, Kab. Aceh Besar',
                'latitude' => 5.6720000,
                'longitude' => 95.0980000,
                'is_active' => true,
            ],
        ];

        foreach ($sites as $s) {
            $regency = Regency::where('code', $s['regency_code'])->first();
            if (! $regency) {
                continue;
            }

            $district = ! empty($s['dist_code'])
                ? District::where('code', $s['dist_code'])->first()
                : null;

            $village = ! empty($s['vill_code'])
                ? Village::where('code', $s['vill_code'])->first()
                : null;

            LandingSite::updateOrCreate(
                ['code' => $s['code']],
                [
                    'name' => $s['name'],
                    'site_type' => $s['site_type'],
                    'province_id' => $aceh->id,
                    'regency_id' => $regency->id,
                    'district_id' => $district?->id,
                    'village_id' => $village?->id,
                    'address' => $s['address'],
                    'latitude' => $s['latitude'],
                    'longitude' => $s['longitude'],
                    'is_active' => $s['is_active'],
                ]
            );
        }
    }
}
