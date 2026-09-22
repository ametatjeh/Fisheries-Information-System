<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\FisherGroup;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

class FisherGroupSeeder extends Seeder
{
    /**
     * Seed data master Kelompok Nelayan / Kelompok Usaha Bersama (KUB) di Aceh.
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $aceh = Province::where('code', '11')->first();
        if (! $aceh) {
            return;
        }

        $groups = [
            // 1. Kota Banda Aceh - Gampong Lampulo
            [
                'code' => 'KUB-BA-001',
                'name' => 'KUB Mina Bahari Lampulo',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2001', // Lampulo
                'leader_name' => 'Teuku Zulkifli, S.Kel',
                'phone' => '081269012345',
                'address' => 'Jl. Pelabuhan Lampulo No. 14, Gampong Lampulo, Kec. Kuta Raja, Banda Aceh',
                'established_date' => '2016-03-15',
                'total_members' => 28,
            ],
            // 2. Kota Banda Aceh - Gampong Ulee Lheue
            [
                'code' => 'KUB-BA-002',
                'name' => 'KUB Samudera Jaya Meuraxa',
                'regency_code' => '11.71',
                'dist_code' => '11.71.01', // Meuraxa
                'vill_code' => '11.71.01.2001', // Ulee Lheue
                'leader_name' => 'Muhammad Nur',
                'phone' => '081360123890',
                'address' => 'Komp. TPI Dermaga Nelayan Ulee Lheue, Kec. Meuraxa, Banda Aceh',
                'established_date' => '2018-07-20',
                'total_members' => 22,
            ],
            // 3. Kota Banda Aceh - Gampong Jawa
            [
                'code' => 'KUB-BA-003',
                'name' => 'KUB Pesisir Kuala Cangkoi',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2003', // Gampong Jawa
                'leader_name' => 'Mukhlis Effendi',
                'phone' => '085277112233',
                'address' => 'Lorong Nelayan Pesisir, Gampong Jawa, Kec. Kuta Raja, Banda Aceh',
                'established_date' => '2020-01-10',
                'total_members' => 18,
            ],
            // 4. Kab. Aceh Besar - Pulo Aceh (Gampong Lampuyang)
            [
                'code' => 'KUB-AB-001',
                'name' => 'KUB Pulo Breueh Sejahtera',
                'regency_code' => '11.06',
                'dist_code' => '11.06.21', // Pulo Aceh
                'vill_code' => '11.06.21.2002', // Lampuyang
                'leader_name' => 'Ibrahim Syah',
                'phone' => '082165439870',
                'address' => 'Dermaga PPI Lampuyang, Gampong Lampuyang, Kec. Pulo Aceh, Aceh Besar',
                'established_date' => '2017-05-12',
                'total_members' => 25,
            ],
            // 5. Kab. Aceh Besar - Baitussalam (Gampong Kajhu)
            [
                'code' => 'KUB-AB-002',
                'name' => 'KUB Karang Bahari Kajhu',
                'regency_code' => '11.06',
                'dist_code' => '11.06.19', // Baitussalam
                'vill_code' => '11.06.19.2002', // Kajhu
                'leader_name' => 'Tgk. Hasan Basri',
                'phone' => '085361224455',
                'address' => 'Jl. Pesisir Pasir Putih, Gampong Kajhu, Kec. Baitussalam, Aceh Besar',
                'established_date' => '2019-09-08',
                'total_members' => 20,
            ],
            // 6. Kab. Aceh Besar - Lhoknga
            [
                'code' => 'KUB-AB-003',
                'name' => 'KUB Nelayan Ombak Rindu Lhoknga',
                'regency_code' => '11.06',
                'dist_code' => '11.06.02', // Lhoknga
                'vill_code' => null,
                'leader_name' => 'Saiful Bahri',
                'phone' => '081370984512',
                'address' => 'Pantai Lampuuk / Lhoknga, Kec. Lhoknga, Kab. Aceh Besar',
                'established_date' => '2015-11-25',
                'total_members' => 30,
            ],
            // 7. Kab. Aceh Timur - Idi Rayeuk (Kuala Idi)
            [
                'code' => 'KUB-AT-001',
                'name' => 'KUB Pukat Malaka Idi Rayeuk',
                'regency_code' => '11.03',
                'dist_code' => '11.03.04', // Idi Rayeuk
                'vill_code' => '11.03.04.2001', // Kuala Idi
                'leader_name' => 'Panglima Laot Cut Marzuki',
                'phone' => '081263445566',
                'address' => 'Pangkalan PPN Idi Rayeuk, Gampong Kuala Idi, Aceh Timur',
                'established_date' => '2014-04-18',
                'total_members' => 45,
            ],
            // 8. Kab. Aceh Barat - Johan Pahlawan (Ujong Baroh)
            [
                'code' => 'KUB-ABAR-001',
                'name' => 'KUB Samudera Meulaboh',
                'regency_code' => '11.05',
                'dist_code' => '11.05.01', // Johan Pahlawan
                'vill_code' => '11.05.01.2001', // Ujong Baroh
                'leader_name' => 'Drs. Usman Ali',
                'phone' => '081260889900',
                'address' => 'Komplek PPI Ujong Baroh, Johan Pahlawan, Meulaboh, Aceh Barat',
                'established_date' => '2016-08-14',
                'total_members' => 35,
            ],
            // 9. Kota Sabang - Sukakarya (Gampong Iboih)
            [
                'code' => 'KUB-SBG-001',
                'name' => 'KUB Weh Bahari Lestari',
                'regency_code' => '11.72',
                'dist_code' => '11.72.01', // Sukakarya
                'vill_code' => '11.72.01.2001', // Iboih
                'leader_name' => 'Fahrurrazi, S.Pi',
                'phone' => '085260114477',
                'address' => 'Pesisir Pantai Teupin Layeu, Gampong Iboih, Kota Sabang',
                'established_date' => '2018-02-28',
                'total_members' => 24,
            ],
            // 10. Kab. Simeulue - Simeulue Timur (Suka Jaya)
            [
                'code' => 'KUB-SML-001',
                'name' => 'KUB Lobster Sinabang Mandiri',
                'regency_code' => '11.17',
                'dist_code' => '11.17.01', // Simeulue Timur
                'vill_code' => '11.17.01.2001', // Suka Jaya
                'leader_name' => 'Arman Yulis',
                'phone' => '082276554433',
                'address' => 'Pesisir Dermaga Sinabang, Gampong Suka Jaya, Simeulue Timur, Simeulue',
                'established_date' => '2019-10-15',
                'total_members' => 26,
            ],
            // 11. Kab. Aceh Singkil - Singkil (Pulo Sarok)
            [
                'code' => 'KUB-SKL-001',
                'name' => 'KUB Banyak Mandiri Singkil',
                'regency_code' => '11.18',
                'dist_code' => '11.18.01', // Singkil
                'vill_code' => '11.18.01.2001', // Pulo Sarok
                'leader_name' => 'Zainuddin Berutu',
                'phone' => '081362778899',
                'address' => 'Dermaga TPI Pulo Sarok, Kec. Singkil, Kab. Aceh Singkil',
                'established_date' => '2021-06-22',
                'total_members' => 22,
            ],
        ];

        foreach ($groups as $g) {
            $regency = Regency::where('code', $g['regency_code'])->first();
            if (! $regency) {
                continue;
            }

            $district = ! empty($g['dist_code'])
                ? District::where('code', $g['dist_code'])->first()
                : null;

            $village = ! empty($g['vill_code'])
                ? Village::where('code', $g['vill_code'])->first()
                : null;

            FisherGroup::updateOrCreate(
                ['code' => $g['code']],
                [
                    'name' => $g['name'],
                    'regency_id' => $regency->id,
                    'district_id' => $district?->id,
                    'village_id' => $village?->id,
                    'leader_name' => $g['leader_name'],
                    'phone' => $g['phone'],
                    'address' => $g['address'],
                    'established_date' => $g['established_date'],
                    'total_members' => $g['total_members'],
                ]
            );
        }
    }
}
