<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Village;
use Illuminate\Database\Seeder;

class AcehVillageSeeder extends Seeder
{
    /**
     * Seed Gampong/Desa resmi di Provinsi Aceh.
     * Mencakup sentra pendaratan ikan (TPI/PPI/PPN/PPS), pesisir, kepulauan, dan sentra nelayan di Aceh.
     *
     * Idempoten: menggunakan updateOrCreate agar aman dijalankan berulang kali tanpa duplikasi.
     */
    public function run(): void
    {
        $villagesByDistrictCode = [
            // 11.71.02 Kuta Raja (Sentra PPS Lampulo)
            '11.71.02' => [
                ['code' => '11.71.02.2001', 'name' => 'Gampong Lampulo', 'postal_code' => '23122'],
                ['code' => '11.71.02.2002', 'name' => 'Gampong Peulanggahan', 'postal_code' => '23121'],
                ['code' => '11.71.02.2003', 'name' => 'Gampong Jawa', 'postal_code' => '23123'],
                ['code' => '11.71.02.2004', 'name' => 'Gampong Pande', 'postal_code' => '23124'],
                ['code' => '11.71.02.2005', 'name' => 'Gampong Keudah', 'postal_code' => '23127'],
            ],

            // 11.71.01 Meuraxa (Pesisir Ulee Lheue)
            '11.71.01' => [
                ['code' => '11.71.01.2001', 'name' => 'Gampong Ulee Lheue', 'postal_code' => '23232'],
                ['code' => '11.71.01.2002', 'name' => 'Gampong Deah Glumpang', 'postal_code' => '23233'],
                ['code' => '11.71.01.2003', 'name' => 'Gampong Alue Deah Teungoh', 'postal_code' => '23234'],
                ['code' => '11.71.01.2004', 'name' => 'Gampong Lambung', 'postal_code' => '23235'],
                ['code' => '11.71.01.2005', 'name' => 'Gampong Gampong Pie', 'postal_code' => '23233'],
            ],

            // 11.71.05 Syiah Kuala (Alue Naga / Krueng Cut)
            '11.71.05' => [
                ['code' => '11.71.05.2001', 'name' => 'Gampong Alue Naga', 'postal_code' => '23115'],
                ['code' => '11.71.05.2002', 'name' => 'Gampong Deah Raya', 'postal_code' => '23116'],
                ['code' => '11.71.05.2003', 'name' => 'Gampong Tibang', 'postal_code' => '23114'],
                ['code' => '11.71.05.2004', 'name' => 'Gampong Jeulingke', 'postal_code' => '23114'],
            ],

            // 11.71.03 Kuta Alam
            '11.71.03' => [
                ['code' => '11.71.03.2001', 'name' => 'Gampong Lampurit', 'postal_code' => '23126'],
                ['code' => '11.71.03.2002', 'name' => 'Gampong Mulia', 'postal_code' => '23123'],
                ['code' => '11.71.03.2003', 'name' => 'Gampong Peunayong', 'postal_code' => '23122'],
            ],

            // 11.71.04 Baiturrahman
            '11.71.04' => [
                ['code' => '11.71.04.2001', 'name' => 'Gampong Peuniti', 'postal_code' => '23241'],
                ['code' => '11.71.04.2002', 'name' => 'Gampong Ateuk Munjeng', 'postal_code' => '23244'],
            ],

            // 11.72.01 Sukakarya (Kota Sabang - Iboih & Kota Ateueh)
            '11.72.01' => [
                ['code' => '11.72.01.2001', 'name' => 'Gampong Iboih', 'postal_code' => '23518'],
                ['code' => '11.72.01.2002', 'name' => 'Gampong Kuta Ateueh', 'postal_code' => '23511'],
                ['code' => '11.72.01.2003', 'name' => 'Gampong Kuta Barat', 'postal_code' => '23512'],
            ],

            // 11.72.02 Sukajaya (Kota Sabang - Balohan & Anoi Itam)
            '11.72.02' => [
                ['code' => '11.72.02.2001', 'name' => 'Gampong Balohan', 'postal_code' => '23521'],
                ['code' => '11.72.02.2002', 'name' => 'Gampong Anoi Itam', 'postal_code' => '23522'],
            ],

            // 11.72.03 Sukamakmue (Kota Sabang)
            '11.72.03' => [
                ['code' => '11.72.03.2001', 'name' => 'Gampong Paya Seunara', 'postal_code' => '23514'],
            ],

            // 11.06.19 Baitussalam (Aceh Besar)
            '11.06.19' => [
                ['code' => '11.06.19.2001', 'name' => 'Gampong Cadek', 'postal_code' => '23373'],
                ['code' => '11.06.19.2002', 'name' => 'Gampong Kajhu', 'postal_code' => '23373'],
                ['code' => '11.06.19.2003', 'name' => 'Gampong Miruek Taman', 'postal_code' => '23373'],
            ],

            // 11.06.21 Pulo Aceh (Aceh Besar - Sentra Perikanan Pulau Terluar)
            '11.06.21' => [
                ['code' => '11.06.21.2001', 'name' => 'Gampong Gugop', 'postal_code' => '23991'],
                ['code' => '11.06.21.2002', 'name' => 'Gampong Lampuyang', 'postal_code' => '23991'],
                ['code' => '11.06.21.2003', 'name' => 'Gampong Deudap', 'postal_code' => '23991'],
                ['code' => '11.06.21.2004', 'name' => 'Gampong Ulee Paya', 'postal_code' => '23991'],
            ],

            // 11.06.02 Lhoknga (Aceh Besar)
            '11.06.02' => [
                ['code' => '11.06.02.2001', 'name' => 'Gampong Mon Ikeun', 'postal_code' => '23353'],
                ['code' => '11.06.02.2002', 'name' => 'Gampong Lampuuk', 'postal_code' => '23353'],
            ],

            // 11.06.07 Peukan Bada (Aceh Besar)
            '11.06.07' => [
                ['code' => '11.06.07.2001', 'name' => 'Gampong Lamguron', 'postal_code' => '23354'],
                ['code' => '11.06.07.2002', 'name' => 'Gampong Ulee Pata', 'postal_code' => '23354'],
            ],

            // 11.06.12 Mesjid Raya (Aceh Besar - Pelabuhan Malahayati Krueng Raya)
            '11.06.12' => [
                ['code' => '11.06.12.2001', 'name' => 'Gampong Krueng Raya', 'postal_code' => '23381'],
                ['code' => '11.06.12.2002', 'name' => 'Gampong Durung', 'postal_code' => '23381'],
            ],

            // 11.07.13 Kota Sigli (Pidie)
            '11.07.13' => [
                ['code' => '11.07.13.2001', 'name' => 'Gampong Kuala Pidie', 'postal_code' => '24115'],
                ['code' => '11.07.13.2002', 'name' => 'Gampong Kramat Luar', 'postal_code' => '24114'],
            ],

            // 11.07.03 Batee (Pidie)
            '11.07.03' => [
                ['code' => '11.07.03.2001', 'name' => 'Gampong Kulee', 'postal_code' => '24152'],
            ],

            // 11.07.17 Muara Tiga (Pidie - Laweung)
            '11.07.17' => [
                ['code' => '11.07.17.2001', 'name' => 'Gampong Suka Jaya Laweung', 'postal_code' => '24153'],
            ],

            // 11.16.01 Meureudu (Pidie Jaya)
            '11.16.01' => [
                ['code' => '11.16.01.2001', 'name' => 'Gampong Kota Meureudu', 'postal_code' => '24186'],
                ['code' => '11.16.01.2002', 'name' => 'Gampong Meunasah Balek', 'postal_code' => '24186'],
            ],

            // 11.16.07 Panteraja (Pidie Jaya)
            '11.16.07' => [
                ['code' => '11.16.07.2001', 'name' => 'Gampong Keude Panteraja', 'postal_code' => '24185'],
            ],

            // 11.08.05 Peudada (Bireuen - Pangkalan Nelayan)
            '11.08.05' => [
                ['code' => '11.08.05.2001', 'name' => 'Gampong Meunasah Pulo', 'postal_code' => '24261'],
                ['code' => '11.08.05.2002', 'name' => 'Gampong Alue Gandai', 'postal_code' => '24261'],
            ],

            // 11.08.04 Jeumpa (Bireuen - Kuala Jeumpa)
            '11.08.04' => [
                ['code' => '11.08.04.2001', 'name' => 'Gampong Kuala Jeumpa', 'postal_code' => '24251'],
            ],

            // 11.08.11 Jangka (Bireuen)
            '11.08.11' => [
                ['code' => '11.08.11.2001', 'name' => 'Gampong Jangka Mesjid', 'postal_code' => '24264'],
            ],

            // 11.09.02 Dewantara (Aceh Utara - Bangka Jaya)
            '11.09.02' => [
                ['code' => '11.09.02.2001', 'name' => 'Gampong Bangka Jaya', 'postal_code' => '24354'],
                ['code' => '11.09.02.2002', 'name' => 'Gampong Tambon Baroh', 'postal_code' => '24354'],
            ],

            // 11.09.11 Seunuddon (Aceh Utara)
            '11.09.11' => [
                ['code' => '11.09.11.2001', 'name' => 'Gampong Ulee Rubek Timu', 'postal_code' => '24393'],
            ],

            // 11.09.21 Lapang (Aceh Utara - Kuala Keureuto)
            '11.09.21' => [
                ['code' => '11.09.21.2001', 'name' => 'Gampong Kuala Keureuto', 'postal_code' => '24381'],
            ],

            // 11.73.02 Banda Sakti (Kota Lhokseumawe - Pusong)
            '11.73.02' => [
                ['code' => '11.73.02.2001', 'name' => 'Gampong Pusong Lama', 'postal_code' => '24313'],
                ['code' => '11.73.02.2002', 'name' => 'Gampong Pusong Baru', 'postal_code' => '24314'],
                ['code' => '11.73.02.2003', 'name' => 'Gampong Hagu Barat Laut', 'postal_code' => '24312'],
            ],

            // 11.73.04 Muara Satu (Kota Lhokseumawe)
            '11.73.04' => [
                ['code' => '11.73.04.2001', 'name' => 'Gampong Blang Panyang', 'postal_code' => '24352'],
            ],

            // 11.03.04 Idi Rayeuk (Aceh Timur - Sentra PPN / PPI Kuala Idi)
            '11.03.04' => [
                ['code' => '11.03.04.2001', 'name' => 'Gampong Kuala Idi', 'postal_code' => '24454'],
                ['code' => '11.03.04.2002', 'name' => 'Gampong Tanoh Anou', 'postal_code' => '24454'],
                ['code' => '11.03.04.2003', 'name' => 'Gampong Keude Blang', 'postal_code' => '24454'],
            ],

            // 11.03.07 Peureulak (Aceh Timur)
            '11.03.07' => [
                ['code' => '11.03.07.2001', 'name' => 'Gampong Kuala Leuge', 'postal_code' => '24453'],
            ],

            // 11.74.02 Langsa Barat (Kota Langsa - Pelabuhan Kuala Langsa)
            '11.74.02' => [
                ['code' => '11.74.02.2001', 'name' => 'Gampong Kuala Langsa', 'postal_code' => '24415'],
                ['code' => '11.74.02.2002', 'name' => 'Gampong Sungai Pauh', 'postal_code' => '24415'],
            ],

            // 11.74.01 Langsa Timur (Kota Langsa)
            '11.74.01' => [
                ['code' => '11.74.01.2001', 'name' => 'Gampong Sungai Lueng', 'postal_code' => '24417'],
            ],

            // 11.12.04 Seruway (Aceh Tamiang)
            '11.12.04' => [
                ['code' => '11.12.04.2001', 'name' => 'Gampong Kuala Peunaga', 'postal_code' => '24473'],
                ['code' => '11.12.04.2002', 'name' => 'Gampong Muka Sungai Kuruk', 'postal_code' => '24473'],
            ],

            // 11.12.02 Bendahara (Aceh Tamiang)
            '11.12.02' => [
                ['code' => '11.12.02.2001', 'name' => 'Gampong Teluk Kemiri', 'postal_code' => '24472'],
            ],

            // 11.14.02 Krueng Sabee (Aceh Jaya)
            '11.14.02' => [
                ['code' => '11.14.02.2001', 'name' => 'Gampong Datar Luas', 'postal_code' => '23654'],
                ['code' => '11.14.02.2002', 'name' => 'Gampong Mon Mata', 'postal_code' => '23654'],
            ],

            // 11.14.03 Setia Bakti (Aceh Jaya)
            '11.14.03' => [
                ['code' => '11.14.03.2001', 'name' => 'Gampong Lhok Buya', 'postal_code' => '23655'],
                ['code' => '11.14.03.2002', 'name' => 'Gampong Sawang', 'postal_code' => '23655'],
            ],

            // 11.14.04 Sampoiniet (Aceh Jaya)
            '11.14.04' => [
                ['code' => '11.14.04.2001', 'name' => 'Gampong Lhok Kruet', 'postal_code' => '23653'],
            ],

            // 11.05.01 Johan Pahlawan (Aceh Barat - PPI Meulaboh)
            '11.05.01' => [
                ['code' => '11.05.01.2001', 'name' => 'Gampong Ujong Baroh', 'postal_code' => '23611'],
                ['code' => '11.05.01.2002', 'name' => 'Gampong Suak Ribee', 'postal_code' => '23615'],
                ['code' => '11.05.01.2003', 'name' => 'Gampong Padang Seurahet', 'postal_code' => '23613'],
            ],

            // 11.05.04 Samatiga (Aceh Barat)
            '11.05.04' => [
                ['code' => '11.05.04.2001', 'name' => 'Gampong Suak Pandan', 'postal_code' => '23652'],
            ],

            // 11.05.07 Meureubo (Aceh Barat)
            '11.05.07' => [
                ['code' => '11.05.07.2001', 'name' => 'Gampong Peunaga Pasi', 'postal_code' => '23681'],
            ],

            // 11.13.05 Kuala Pesisir (Nagan Raya)
            '11.13.05' => [
                ['code' => '11.13.05.2001', 'name' => 'Gampong Suak Puntong', 'postal_code' => '23661'],
                ['code' => '11.13.05.2002', 'name' => 'Gampong Kuala Baro', 'postal_code' => '23661'],
            ],

            // 11.13.01 Kuala (Nagan Raya)
            '11.13.01' => [
                ['code' => '11.13.01.2001', 'name' => 'Gampong Ujong Patihah', 'postal_code' => '23661'],
            ],

            // 11.10.04 Susoh (Aceh Barat Daya - PPI Ujong Serangga)
            '11.10.04' => [
                ['code' => '11.10.04.2001', 'name' => 'Gampong Padang Baru', 'postal_code' => '23765'],
                ['code' => '11.10.04.2002', 'name' => 'Gampong Pulau Kayu', 'postal_code' => '23765'],
            ],

            // 11.10.01 Blangpidie (Aceh Barat Daya)
            '11.10.01' => [
                ['code' => '11.10.01.2001', 'name' => 'Gampong Meudang Ara', 'postal_code' => '23764'],
            ],

            // 11.01.03 Tapaktuan (Aceh Selatan)
            '11.01.03' => [
                ['code' => '11.01.03.2001', 'name' => 'Gampong Pasar', 'postal_code' => '23711'],
                ['code' => '11.01.03.2002', 'name' => 'Gampong Lhok Bengkuang', 'postal_code' => '23714'],
                ['code' => '11.01.03.2003', 'name' => 'Gampong Hilir', 'postal_code' => '23712'],
            ],

            // 11.01.07 Labuhan Haji (Aceh Selatan)
            '11.01.07' => [
                ['code' => '11.01.07.2001', 'name' => 'Gampong Pasar Labuhan Haji', 'postal_code' => '23761'],
            ],

            // 11.01.06 Meukek (Aceh Selatan)
            '11.01.06' => [
                ['code' => '11.01.06.2001', 'name' => 'Gampong Kuta Buloh', 'postal_code' => '23754'],
            ],

            // 11.17.01 Simeulue Timur (Simeulue - Sentra PPI Sinabang)
            '11.17.01' => [
                ['code' => '11.17.01.2001', 'name' => 'Gampong Suka Jaya', 'postal_code' => '23891'],
                ['code' => '11.17.01.2002', 'name' => 'Gampong Sinabang', 'postal_code' => '23891'],
                ['code' => '11.17.01.2003', 'name' => 'Gampong Air Dingin', 'postal_code' => '23891'],
            ],

            // 11.17.02 Teupah Barat (Simeulue)
            '11.17.02' => [
                ['code' => '11.17.02.2001', 'name' => 'Gampong Salur', 'postal_code' => '23898'],
            ],

            // 11.18.01 Singkil (Aceh Singkil - Pelabuhan Pulo Sarok)
            '11.18.01' => [
                ['code' => '11.18.01.2001', 'name' => 'Gampong Pulo Sarok', 'postal_code' => '24785'],
                ['code' => '11.18.01.2002', 'name' => 'Gampong Kilangan', 'postal_code' => '24785'],
                ['code' => '11.18.01.2003', 'name' => 'Gampong Pasar', 'postal_code' => '24785'],
            ],

            // 11.18.04 Pulau Banyak (Aceh Singkil)
            '11.18.04' => [
                ['code' => '11.18.04.2001', 'name' => 'Gampong Pulau Balai', 'postal_code' => '24791'],
                ['code' => '11.18.04.2002', 'name' => 'Gampong Teluk Nibung', 'postal_code' => '24791'],
            ],

            // 11.18.11 Pulau Banyak Barat (Aceh Singkil)
            '11.18.11' => [
                ['code' => '11.18.11.2001', 'name' => 'Gampong Haloban', 'postal_code' => '24792'],
            ],

            // 11.75.01 Simpang Kiri (Kota Subulussalam)
            '11.75.01' => [
                ['code' => '11.75.01.2001', 'name' => 'Gampong Subulussalam', 'postal_code' => '24782'],
                ['code' => '11.75.01.2002', 'name' => 'Gampong Pegayo', 'postal_code' => '24782'],
            ],

            // 11.04.08 Lut Tawar (Aceh Tengah - Danau Laut Tawar)
            '11.04.08' => [
                ['code' => '11.04.08.2001', 'name' => 'Gampong Takengon Barat', 'postal_code' => '24511'],
                ['code' => '11.04.08.2002', 'name' => 'Gampong Hakim Bale Bujang', 'postal_code' => '24512'],
            ],

            // 11.15.03 Bukit (Bener Meriah)
            '11.15.03' => [
                ['code' => '11.15.03.2001', 'name' => 'Gampong Simpang Tiga Redelong', 'postal_code' => '24582'],
            ],

            // 11.11.01 Blangkejeren (Gayo Lues)
            '11.11.01' => [
                ['code' => '11.11.01.2001', 'name' => 'Gampong Kota Blangkejeren', 'postal_code' => '24653'],
            ],

            // 11.02.02 Babussalam (Aceh Tenggara)
            '11.02.02' => [
                ['code' => '11.02.02.2001', 'name' => 'Gampong Kutacane', 'postal_code' => '24651'],
            ],
        ];

        foreach ($villagesByDistrictCode as $districtCode => $villages) {
            $district = District::where('code', $districtCode)->first();
            if (! $district) {
                continue;
            }

            foreach ($villages as $v) {
                Village::updateOrCreate(
                    ['code' => $v['code']],
                    [
                        'district_id' => $district->id,
                        'name' => $v['name'],
                        'postal_code' => $v['postal_code'] ?? null,
                    ]
                );
            }
        }

        // Hapus desa/kelurahan yang tidak berada di bawah kecamatan Provinsi Aceh (jika ada)
        Village::whereDoesntHave('district.regency.province', function ($q) {
            $q->where('code', '11');
        })->delete();
    }
}
