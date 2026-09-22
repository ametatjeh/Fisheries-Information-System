<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\FisherGroup;
use App\Models\Fisherman;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

class FishermanSeeder extends Seeder
{
    /**
     * Seed data master nelayan di sentra perikanan Aceh.
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $aceh = Province::where('code', '11')->first();
        if (! $aceh) {
            return;
        }

        $fishermen = [
            // 1. Lampulo - Banda Aceh (Pemilik Kapal Purse Seine)
            [
                'nik' => '1171021204780001',
                'kusuka_number' => 'KUSUKA-1171-2022-0001',
                'name' => 'Tgk. H. Syamsuddin Mahmud',
                'gender' => 'L',
                'birth_place' => 'Banda Aceh',
                'birth_date' => '1978-04-12',
                'phone' => '081269112233',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2001', // Lampulo
                'address' => 'Jl. TPI Lampulo No. 45, Banda Aceh',
                'group_code' => 'KUB-BA-001',
                'fisher_type' => 'pemilik',
                'is_active' => true,
            ],
            // 2. Lampulo - Banda Aceh (Nahkoda Kapal Cakalang/Tongkol)
            [
                'nik' => '1171021508820002',
                'kusuka_number' => 'KUSUKA-1171-2022-0002',
                'name' => 'Pawang Bukhari Ahmad',
                'gender' => 'L',
                'birth_place' => 'Sigli',
                'birth_date' => '1982-08-15',
                'phone' => '081360223344',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2001', // Lampulo
                'address' => 'Gampong Lampulo Lorong Panglima, Banda Aceh',
                'group_code' => 'KUB-BA-001',
                'fisher_type' => 'nahkoda_jurumudi',
                'is_active' => true,
            ],
            // 3. Lampulo - Banda Aceh (ABK Kapal Purse Seine)
            [
                'nik' => '1171022011950003',
                'kusuka_number' => 'KUSUKA-1171-2023-0015',
                'name' => 'Muhammad Rizal, S.Kel',
                'gender' => 'L',
                'birth_place' => 'Banda Aceh',
                'birth_date' => '1995-11-20',
                'phone' => '085277334455',
                'regency_code' => '11.71',
                'dist_code' => '11.71.02', // Kuta Raja
                'vill_code' => '11.71.02.2001', // Lampulo
                'address' => 'Gampong Lampulo, Kec. Kuta Raja, Banda Aceh',
                'group_code' => 'KUB-BA-001',
                'fisher_type' => 'abk',
                'is_active' => true,
            ],
            // 4. Ulee Lheue - Banda Aceh (Nahkoda Perahu Pancing Ulur Tuna)
            [
                'nik' => '1171011003850004',
                'kusuka_number' => 'KUSUKA-1171-2022-0008',
                'name' => 'Pawang Zulkarnain',
                'gender' => 'L',
                'birth_place' => 'Banda Aceh',
                'birth_date' => '1985-03-10',
                'phone' => '081362445566',
                'regency_code' => '11.71',
                'dist_code' => '11.71.01', // Meuraxa
                'vill_code' => '11.71.01.2001', // Ulee Lheue
                'address' => 'Komp. Perumahan Nelayan Ulee Lheue, Meuraxa, Banda Aceh',
                'group_code' => 'KUB-BA-002',
                'fisher_type' => 'nahkoda_jurumudi',
                'is_active' => true,
            ],
            // 5. Deah Glumpang - Banda Aceh (Nelayan Tanpa Perahu / Pukat Darat)
            [
                'nik' => '1171010507900005',
                'kusuka_number' => 'KUSUKA-1171-2023-0022',
                'name' => 'Ilyas Saputra',
                'gender' => 'L',
                'birth_place' => 'Meulaboh',
                'birth_date' => '1990-07-05',
                'phone' => '085361556677',
                'regency_code' => '11.71',
                'dist_code' => '11.71.01', // Meuraxa
                'vill_code' => '11.71.01.2002', // Deah Glumpang
                'address' => 'Pesisir Pantai Deah Glumpang, Meuraxa, Banda Aceh',
                'group_code' => 'KUB-BA-002',
                'fisher_type' => 'nelayan_tanpa_perahu',
                'is_active' => true,
            ],
            // 6. Idi Rayeuk - Aceh Timur (Pemilik Kapal Gillnet/Pukat Langgar)
            [
                'nik' => '1103041805720006',
                'kusuka_number' => 'KUSUKA-1103-2021-0003',
                'name' => 'H. Marzuki Yahya',
                'gender' => 'L',
                'birth_place' => 'Idi',
                'birth_date' => '1972-05-18',
                'phone' => '081263667788',
                'regency_code' => '11.03',
                'dist_code' => '11.03.04', // Idi Rayeuk
                'vill_code' => '11.03.04.2001', // Kuala Idi
                'address' => 'Kuala Idi Rayeuk No. 20, Kab. Aceh Timur',
                'group_code' => 'KUB-AT-001',
                'fisher_type' => 'pemilik',
                'is_active' => true,
            ],
            // 7. Idi Rayeuk - Aceh Timur (Nahkoda Kapal Pukat Malaka)
            [
                'nik' => '1103042209800007',
                'kusuka_number' => 'KUSUKA-1103-2022-0011',
                'name' => 'Pawang Junaidi',
                'gender' => 'L',
                'birth_place' => 'Idi Rayeuk',
                'birth_date' => '1980-09-22',
                'phone' => '082165778899',
                'regency_code' => '11.03',
                'dist_code' => '11.03.04', // Idi Rayeuk
                'vill_code' => '11.03.04.2001', // Kuala Idi
                'address' => 'Lorong TPI Kuala Idi, Aceh Timur',
                'group_code' => 'KUB-AT-001',
                'fisher_type' => 'nahkoda_jurumudi',
                'is_active' => true,
            ],
            // 8. Pulo Aceh - Aceh Besar (Pemilik & Nahkoda Boat Rawai Dasar)
            [
                'nik' => '1106211402830008',
                'kusuka_number' => 'KUSUKA-1106-2022-0005',
                'name' => 'Ibrahim Cut Adek',
                'gender' => 'L',
                'birth_place' => 'Lampuyang',
                'birth_date' => '1983-02-14',
                'phone' => '082276889900',
                'regency_code' => '11.06',
                'dist_code' => '11.06.21', // Pulo Aceh
                'vill_code' => '11.06.21.2002', // Lampuyang
                'address' => 'Dermaga PPI Lampuyang, Pulo Breueh, Aceh Besar',
                'group_code' => 'KUB-AB-001',
                'fisher_type' => 'pemilik',
                'is_active' => true,
            ],
            // 9. Kajhu - Aceh Besar (ABK & Penyelam Gurita Tradisional)
            [
                'nik' => '1106190812920009',
                'kusuka_number' => 'KUSUKA-1106-2023-0034',
                'name' => 'Faisal Mahdi',
                'gender' => 'L',
                'birth_place' => 'Banda Aceh',
                'birth_date' => '1992-12-08',
                'phone' => '081370990011',
                'regency_code' => '11.06',
                'dist_code' => '11.06.19', // Baitussalam
                'vill_code' => '11.06.19.2002', // Kajhu
                'address' => 'Gampong Kajhu Lorong Rawa Pesisir, Aceh Besar',
                'group_code' => 'KUB-AB-002',
                'fisher_type' => 'abk',
                'is_active' => true,
            ],
            // 10. Johan Pahlawan - Aceh Barat (Nahkoda Longline Tuna Meulaboh)
            [
                'nik' => '1105011706840010',
                'kusuka_number' => 'KUSUKA-1105-2021-0009',
                'name' => 'Pawang Anwar Daud',
                'gender' => 'L',
                'birth_place' => 'Meulaboh',
                'birth_date' => '1984-06-17',
                'phone' => '081260113355',
                'regency_code' => '11.05',
                'dist_code' => '11.05.01', // Johan Pahlawan
                'vill_code' => '11.05.01.2001', // Ujong Baroh
                'address' => 'Jl. Merak Ujong Baroh, Johan Pahlawan, Aceh Barat',
                'group_code' => 'KUB-ABAR-001',
                'fisher_type' => 'nahkoda_jurumudi',
                'is_active' => true,
            ],
            // 11. Iboih - Sabang (Nelayan Tangkap Tradisional & Pemandu Bahari)
            [
                'nik' => '1172012501880011',
                'kusuka_number' => 'KUSUKA-1172-2022-0014',
                'name' => 'Teuku Rustam Efendi',
                'gender' => 'L',
                'birth_place' => 'Sabang',
                'birth_date' => '1988-01-25',
                'phone' => '085260224466',
                'regency_code' => '11.72',
                'dist_code' => '11.72.01', // Sukakarya
                'vill_code' => '11.72.01.2001', // Iboih
                'address' => 'Pesisir Teupin Layeu Gampong Iboih, Kota Sabang',
                'group_code' => 'KUB-SBG-001',
                'fisher_type' => 'pemilik',
                'is_active' => true,
            ],
            // 12. Simeulue Timur - Simeulue (Nelayan Penyelam Lobster & Teripang)
            [
                'nik' => '1117013009860012',
                'kusuka_number' => 'KUSUKA-1117-2022-0007',
                'name' => 'Safrijal Tanjung',
                'gender' => 'L',
                'birth_place' => 'Sinabang',
                'birth_date' => '1986-09-30',
                'phone' => '082276335577',
                'regency_code' => '11.17',
                'dist_code' => '11.17.01', // Simeulue Timur
                'vill_code' => '11.17.01.2001', // Suka Jaya
                'address' => 'Pesisir Dermaga Sinabang, Gampong Suka Jaya, Simeulue',
                'group_code' => 'KUB-SML-001',
                'fisher_type' => 'abk',
                'is_active' => true,
            ],
            // 13. Singkil - Aceh Singkil (Nelayan Perahu Dayung / Pancing)
            [
                'nik' => '1118010404940013',
                'kusuka_number' => 'KUSUKA-1118-2023-0019',
                'name' => 'Hendra Manik',
                'gender' => 'L',
                'birth_place' => 'Singkil',
                'birth_date' => '1994-04-04',
                'phone' => '081362446688',
                'regency_code' => '11.18',
                'dist_code' => '11.18.01', // Singkil
                'vill_code' => '11.18.01.2001', // Pulo Sarok
                'address' => 'Pesisir Dermaga Singkil, Gampong Pulo Sarok, Aceh Singkil',
                'group_code' => 'KUB-SKL-001',
                'fisher_type' => 'nelayan_tanpa_perahu',
                'is_active' => true,
            ],
        ];

        foreach ($fishermen as $f) {
            $regency = Regency::where('code', $f['regency_code'])->first();
            if (! $regency) {
                continue;
            }

            $district = ! empty($f['dist_code'])
                ? District::where('code', $f['dist_code'])->first()
                : null;

            $village = ! empty($f['vill_code'])
                ? Village::where('code', $f['vill_code'])->first()
                : null;

            $group = ! empty($f['group_code'])
                ? FisherGroup::where('code', $f['group_code'])->first()
                : null;

            Fisherman::updateOrCreate(
                ['nik' => $f['nik']],
                [
                    'kusuka_number' => $f['kusuka_number'],
                    'name' => $f['name'],
                    'gender' => $f['gender'],
                    'birth_place' => $f['birth_place'],
                    'birth_date' => $f['birth_date'],
                    'phone' => $f['phone'],
                    'province_id' => $aceh->id,
                    'regency_id' => $regency->id,
                    'district_id' => $district?->id,
                    'village_id' => $village?->id,
                    'address' => $f['address'],
                    'fisher_group_id' => $group?->id,
                    'fisher_type' => $f['fisher_type'],
                    'is_active' => $f['is_active'],
                ]
            );
        }
    }
}
