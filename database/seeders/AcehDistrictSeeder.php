<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Regency;
use Illuminate\Database\Seeder;

class AcehDistrictSeeder extends Seeder
{
    /**
     * Seed kecamatan resmi Kemendagri di 23 Kabupaten & Kota Provinsi Aceh.
     * Fokus mencakup seluruh kecamatan pesisir sentra perikanan, pelabuhan, dan wilayah daratan Aceh.
     *
     * Idempoten: menggunakan updateOrCreate agar aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $districtsByRegencyCode = [
            // 11.01 Kabupaten Aceh Selatan
            '11.01' => [
                ['code' => '11.01.01', 'name' => 'Bakongan'],
                ['code' => '11.01.03', 'name' => 'Tapaktuan'],
                ['code' => '11.01.04', 'name' => 'Samadua'],
                ['code' => '11.01.05', 'name' => 'Sawang'],
                ['code' => '11.01.06', 'name' => 'Meukek'],
                ['code' => '11.01.07', 'name' => 'Labuhan Haji'],
                ['code' => '11.01.14', 'name' => 'Labuhan Haji Barat'],
                ['code' => '11.01.15', 'name' => 'Bakongan Timur'],
            ],

            // 11.02 Kabupaten Aceh Tenggara
            '11.02' => [
                ['code' => '11.02.01', 'name' => 'Lawe Alas'],
                ['code' => '11.02.02', 'name' => 'Babussalam'],
                ['code' => '11.02.03', 'name' => 'Bambel'],
                ['code' => '11.02.04', 'name' => 'Badar'],
                ['code' => '11.02.12', 'name' => 'Babul Rahmah'],
            ],

            // 11.03 Kabupaten Aceh Timur
            '11.03' => [
                ['code' => '11.03.01', 'name' => 'Darul Aman'],
                ['code' => '11.03.04', 'name' => 'Idi Rayeuk'],
                ['code' => '11.03.07', 'name' => 'Peureulak'],
                ['code' => '11.03.11', 'name' => 'Sungai Raya'],
                ['code' => '11.03.13', 'name' => 'Simpang Ulim'],
                ['code' => '11.03.17', 'name' => 'Madat'],
                ['code' => '11.03.18', 'name' => 'Peureulak Timur'],
            ],

            // 11.04 Kabupaten Aceh Tengah
            '11.04' => [
                ['code' => '11.04.01', 'name' => 'Linge'],
                ['code' => '11.04.02', 'name' => 'Silih Nara'],
                ['code' => '11.04.03', 'name' => 'Bebesen'],
                ['code' => '11.04.08', 'name' => 'Lut Tawar'],
                ['code' => '11.04.10', 'name' => 'Kebayakan'],
            ],

            // 11.05 Kabupaten Aceh Barat
            '11.05' => [
                ['code' => '11.05.01', 'name' => 'Johan Pahlawan'],
                ['code' => '11.05.02', 'name' => 'Kaway XVI'],
                ['code' => '11.05.04', 'name' => 'Samatiga'],
                ['code' => '11.05.05', 'name' => 'Bubon'],
                ['code' => '11.05.06', 'name' => 'Arongan Lambalek'],
                ['code' => '11.05.07', 'name' => 'Meureubo'],
            ],

            // 11.06 Kabupaten Aceh Besar
            '11.06' => [
                ['code' => '11.06.01', 'name' => 'Lhoong'],
                ['code' => '11.06.02', 'name' => 'Lhoknga'],
                ['code' => '11.06.07', 'name' => 'Peukan Bada'],
                ['code' => '11.06.08', 'name' => 'Ingin Jaya'],
                ['code' => '11.06.12', 'name' => 'Mesjid Raya'],
                ['code' => '11.06.13', 'name' => 'Darussalam'],
                ['code' => '11.06.19', 'name' => 'Baitussalam'],
                ['code' => '11.06.21', 'name' => 'Pulo Aceh'],
            ],

            // 11.07 Kabupaten Pidie
            '11.07' => [
                ['code' => '11.07.03', 'name' => 'Batee'],
                ['code' => '11.07.13', 'name' => 'Kota Sigli'],
                ['code' => '11.07.15', 'name' => 'Pidie'],
                ['code' => '11.07.17', 'name' => 'Muara Tiga'],
                ['code' => '11.07.18', 'name' => 'Simpang Tiga'],
                ['code' => '11.07.19', 'name' => 'Kembang Tanjong'],
            ],

            // 11.08 Kabupaten Bireuen
            '11.08' => [
                ['code' => '11.08.01', 'name' => 'Samalanga'],
                ['code' => '11.08.04', 'name' => 'Jeumpa'],
                ['code' => '11.08.05', 'name' => 'Peudada'],
                ['code' => '11.08.06', 'name' => 'Kota Juang'],
                ['code' => '11.08.09', 'name' => 'Gandapura'],
                ['code' => '11.08.11', 'name' => 'Jangka'],
                ['code' => '11.08.14', 'name' => 'Kuala'],
            ],

            // 11.09 Kabupaten Aceh Utara
            '11.09' => [
                ['code' => '11.09.02', 'name' => 'Dewantara'],
                ['code' => '11.09.03', 'name' => 'Muara Batu'],
                ['code' => '11.09.11', 'name' => 'Seunuddon'],
                ['code' => '11.09.15', 'name' => 'Lhoksukon'],
                ['code' => '11.09.21', 'name' => 'Lapang'],
                ['code' => '11.09.22', 'name' => 'Tanah Pasir'],
            ],

            // 11.10 Kabupaten Aceh Barat Daya
            '11.10' => [
                ['code' => '11.10.01', 'name' => 'Blangpidie'],
                ['code' => '11.10.02', 'name' => 'Tangan-Tangan'],
                ['code' => '11.10.03', 'name' => 'Manggeng'],
                ['code' => '11.10.04', 'name' => 'Susoh'],
                ['code' => '11.10.05', 'name' => 'Kuala Batee'],
                ['code' => '11.10.06', 'name' => 'Babah Rot'],
            ],

            // 11.11 Kabupaten Gayo Lues
            '11.11' => [
                ['code' => '11.11.01', 'name' => 'Blangkejeren'],
                ['code' => '11.11.02', 'name' => 'Kutapanjang'],
                ['code' => '11.11.03', 'name' => 'Rikit Gaib'],
                ['code' => '11.11.08', 'name' => 'Putri Betung'],
            ],

            // 11.12 Kabupaten Aceh Tamiang
            '11.12' => [
                ['code' => '11.12.01', 'name' => 'Manyak Payed'],
                ['code' => '11.12.02', 'name' => 'Bendahara'],
                ['code' => '11.12.03', 'name' => 'Karang Baru'],
                ['code' => '11.12.04', 'name' => 'Seruway'],
                ['code' => '11.12.05', 'name' => 'Kota Kualasimpang'],
                ['code' => '11.12.07', 'name' => 'Banda Mulia'],
            ],

            // 11.13 Kabupaten Nagan Raya
            '11.13' => [
                ['code' => '11.13.01', 'name' => 'Kuala'],
                ['code' => '11.13.02', 'name' => 'Seunagan'],
                ['code' => '11.13.04', 'name' => 'Darul Makmur'],
                ['code' => '11.13.05', 'name' => 'Kuala Pesisir'],
                ['code' => '11.13.06', 'name' => 'Tadu Raya'],
                ['code' => '11.13.07', 'name' => 'Tripa Makmur'],
            ],

            // 11.14 Kabupaten Aceh Jaya
            '11.14' => [
                ['code' => '11.14.01', 'name' => 'Teunom'],
                ['code' => '11.14.02', 'name' => 'Krueng Sabee'],
                ['code' => '11.14.03', 'name' => 'Setia Bakti'],
                ['code' => '11.14.04', 'name' => 'Sampoiniet'],
                ['code' => '11.14.05', 'name' => 'Jaya'],
                ['code' => '11.14.06', 'name' => 'Panga'],
            ],

            // 11.15 Kabupaten Bener Meriah
            '11.15' => [
                ['code' => '11.15.01', 'name' => 'Pintu Rime Gayo'],
                ['code' => '11.15.03', 'name' => 'Bukit'],
                ['code' => '11.15.04', 'name' => 'Wih Pesam'],
                ['code' => '11.15.05', 'name' => 'Bandar'],
            ],

            // 11.16 Kabupaten Pidie Jaya
            '11.16' => [
                ['code' => '11.16.01', 'name' => 'Meureudu'],
                ['code' => '11.16.02', 'name' => 'Ulim'],
                ['code' => '11.16.03', 'name' => 'Jangka Buya'],
                ['code' => '11.16.04', 'name' => 'Bandar Dua'],
                ['code' => '11.16.05', 'name' => 'Meurah Dua'],
                ['code' => '11.16.06', 'name' => 'Bandar Baru'],
                ['code' => '11.16.07', 'name' => 'Panteraja'],
                ['code' => '11.16.08', 'name' => 'Trienggadeng'],
            ],

            // 11.17 Kabupaten Simeulue
            '11.17' => [
                ['code' => '11.17.01', 'name' => 'Simeulue Timur'],
                ['code' => '11.17.02', 'name' => 'Teupah Barat'],
                ['code' => '11.17.03', 'name' => 'Teupah Selatan'],
                ['code' => '11.17.04', 'name' => 'Simeulue Barat'],
                ['code' => '11.17.05', 'name' => 'Teluk Dalam'],
                ['code' => '11.17.06', 'name' => 'Salang'],
                ['code' => '11.17.08', 'name' => 'Teupah Tengah'],
            ],

            // 11.18 Kabupaten Aceh Singkil
            '11.18' => [
                ['code' => '11.18.01', 'name' => 'Singkil'],
                ['code' => '11.18.02', 'name' => 'Singkil Utara'],
                ['code' => '11.18.04', 'name' => 'Pulau Banyak'],
                ['code' => '11.18.08', 'name' => 'Kuala Baru'],
                ['code' => '11.18.11', 'name' => 'Pulau Banyak Barat'],
            ],

            // 11.71 Kota Banda Aceh
            '11.71' => [
                ['code' => '11.71.01', 'name' => 'Meuraxa'],
                ['code' => '11.71.02', 'name' => 'Kuta Raja'],
                ['code' => '11.71.03', 'name' => 'Kuta Alam'],
                ['code' => '11.71.04', 'name' => 'Baiturrahman'],
                ['code' => '11.71.05', 'name' => 'Syiah Kuala'],
                ['code' => '11.71.06', 'name' => 'Ulee Kareng'],
                ['code' => '11.71.07', 'name' => 'Jaya Baru'],
                ['code' => '11.71.08', 'name' => 'Banda Raya'],
                ['code' => '11.71.09', 'name' => 'Lueng Bata'],
            ],

            // 11.72 Kota Sabang
            '11.72' => [
                ['code' => '11.72.01', 'name' => 'Sukakarya'],
                ['code' => '11.72.02', 'name' => 'Sukajaya'],
                ['code' => '11.72.03', 'name' => 'Sukamakmue'],
            ],

            // 11.73 Kota Lhokseumawe
            '11.73' => [
                ['code' => '11.73.01', 'name' => 'Muara Dua'],
                ['code' => '11.73.02', 'name' => 'Banda Sakti'],
                ['code' => '11.73.03', 'name' => 'Blang Mangat'],
                ['code' => '11.73.04', 'name' => 'Muara Satu'],
            ],

            // 11.74 Kota Langsa
            '11.74' => [
                ['code' => '11.74.01', 'name' => 'Langsa Timur'],
                ['code' => '11.74.02', 'name' => 'Langsa Barat'],
                ['code' => '11.74.03', 'name' => 'Langsa Kota'],
                ['code' => '11.74.04', 'name' => 'Langsa Lama'],
                ['code' => '11.74.05', 'name' => 'Langsa Baro'],
            ],

            // 11.75 Kota Subulussalam
            '11.75' => [
                ['code' => '11.75.01', 'name' => 'Simpang Kiri'],
                ['code' => '11.75.02', 'name' => 'Penanggalan'],
                ['code' => '11.75.03', 'name' => 'Rundeng'],
                ['code' => '11.75.04', 'name' => 'Sultan Daulat'],
                ['code' => '11.75.05', 'name' => 'Longkib'],
            ],
        ];

        foreach ($districtsByRegencyCode as $regencyCode => $districts) {
            $regency = Regency::where('code', $regencyCode)->first();
            if (! $regency) {
                continue;
            }

            foreach ($districts as $d) {
                District::updateOrCreate(
                    ['code' => $d['code']],
                    [
                        'regency_id' => $regency->id,
                        'name' => $d['name'],
                    ]
                );
            }
        }

        // Hapus kecamatan yang tidak berada di bawah kabupaten/kota Provinsi Aceh (jika ada)
        District::whereDoesntHave('regency.province', function ($q) {
            $q->where('code', '11');
        })->delete();
    }
}
