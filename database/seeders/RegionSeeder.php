<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Seed master data wilayah:
     * - 38 Provinsi Indonesia
     * - 23 Kabupaten & Kota LENGKAP di Provinsi Aceh (11)
     * - Kecamatan dan Gampong pesisir sentra perikanan di Aceh
     *
     * Idempoten: aman dijalankan ulang tanpa membuat duplikasi data.
     */
    public function run(): void
    {
        // 1. Data 38 Provinsi di Indonesia
        $provinces = [
            ['code' => '11', 'name' => 'Aceh'],
            ['code' => '12', 'name' => 'Sumatera Utara'],
            ['code' => '13', 'name' => 'Sumatera Barat'],
            ['code' => '14', 'name' => 'Riau'],
            ['code' => '15', 'name' => 'Jambi'],
            ['code' => '16', 'name' => 'Sumatera Selatan'],
            ['code' => '17', 'name' => 'Bengkulu'],
            ['code' => '18', 'name' => 'Lampung'],
            ['code' => '19', 'name' => 'Kepulauan Bangka Belitung'],
            ['code' => '21', 'name' => 'Kepulauan Riau'],
            ['code' => '31', 'name' => 'DKI Jakarta'],
            ['code' => '32', 'name' => 'Jawa Barat'],
            ['code' => '33', 'name' => 'Jawa Tengah'],
            ['code' => '34', 'name' => 'DI Yogyakarta'],
            ['code' => '35', 'name' => 'Jawa Timur'],
            ['code' => '36', 'name' => 'Banten'],
            ['code' => '51', 'name' => 'Bali'],
            ['code' => '52', 'name' => 'Nusa Tenggara Barat'],
            ['code' => '53', 'name' => 'Nusa Tenggara Timur'],
            ['code' => '61', 'name' => 'Kalimantan Barat'],
            ['code' => '62', 'name' => 'Kalimantan Tengah'],
            ['code' => '63', 'name' => 'Kalimantan Selatan'],
            ['code' => '64', 'name' => 'Kalimantan Timur'],
            ['code' => '65', 'name' => 'Kalimantan Utara'],
            ['code' => '71', 'name' => 'Sulawesi Utara'],
            ['code' => '72', 'name' => 'Sulawesi Tengah'],
            ['code' => '73', 'name' => 'Sulawesi Selatan'],
            ['code' => '74', 'name' => 'Sulawesi Tenggara'],
            ['code' => '75', 'name' => 'Gorontalo'],
            ['code' => '76', 'name' => 'Sulawesi Barat'],
            ['code' => '81', 'name' => 'Maluku'],
            ['code' => '82', 'name' => 'Maluku Utara'],
            ['code' => '91', 'name' => 'Papua'],
            ['code' => '92', 'name' => 'Papua Barat'],
            ['code' => '93', 'name' => 'Papua Selatan'],
            ['code' => '94', 'name' => 'Papua Tengah'],
            ['code' => '95', 'name' => 'Papua Pegunungan'],
            ['code' => '96', 'name' => 'Papua Barat Daya'],
        ];

        foreach ($provinces as $p) {
            Province::firstOrCreate(
                ['code' => $p['code']],
                ['name' => $p['name']]
            );
        }

        // 2. Data 23 Kabupaten/Kota Lengkap di Provinsi Aceh (Kode: 11)
        $aceh = Province::where('code', '11')->first();
        if ($aceh) {
            $regenciesAceh = [
                ['code' => '11.01', 'name' => 'Aceh Selatan', 'type' => 'kabupaten'],
                ['code' => '11.02', 'name' => 'Aceh Tenggara', 'type' => 'kabupaten'],
                ['code' => '11.03', 'name' => 'Aceh Timur', 'type' => 'kabupaten'],
                ['code' => '11.04', 'name' => 'Aceh Tengah', 'type' => 'kabupaten'],
                ['code' => '11.05', 'name' => 'Aceh Barat', 'type' => 'kabupaten'],
                ['code' => '11.06', 'name' => 'Aceh Besar', 'type' => 'kabupaten'],
                ['code' => '11.07', 'name' => 'Pidie', 'type' => 'kabupaten'],
                ['code' => '11.08', 'name' => 'Bireuen', 'type' => 'kabupaten'],
                ['code' => '11.09', 'name' => 'Aceh Utara', 'type' => 'kabupaten'],
                ['code' => '11.10', 'name' => 'Aceh Barat Daya', 'type' => 'kabupaten'],
                ['code' => '11.11', 'name' => 'Gayo Lues', 'type' => 'kabupaten'],
                ['code' => '11.12', 'name' => 'Aceh Tamiang', 'type' => 'kabupaten'],
                ['code' => '11.13', 'name' => 'Nagan Raya', 'type' => 'kabupaten'],
                ['code' => '11.14', 'name' => 'Aceh Jaya', 'type' => 'kabupaten'],
                ['code' => '11.15', 'name' => 'Bener Meriah', 'type' => 'kabupaten'],
                ['code' => '11.16', 'name' => 'Pidie Jaya', 'type' => 'kabupaten'],
                ['code' => '11.17', 'name' => 'Simeulue', 'type' => 'kabupaten'],
                ['code' => '11.18', 'name' => 'Aceh Singkil', 'type' => 'kabupaten'],
                ['code' => '11.71', 'name' => 'Banda Aceh', 'type' => 'kota'],
                ['code' => '11.72', 'name' => 'Sabang', 'type' => 'kota'],
                ['code' => '11.73', 'name' => 'Lhokseumawe', 'type' => 'kota'],
                ['code' => '11.74', 'name' => 'Langsa', 'type' => 'kota'],
                ['code' => '11.75', 'name' => 'Subulussalam', 'type' => 'kota'],
            ];

            foreach ($regenciesAceh as $r) {
                Regency::updateOrCreate(
                    ['code' => $r['code']],
                    ['province_id' => $aceh->id, 'name' => $r['name'], 'type' => $r['type']]
                );
            }

            // 3. Kecamatan & Gampong Sentra Perikanan Pesisir di Aceh

            // A. Kota Banda Aceh (11.71) - Sentra Pelabuhan Perikanan Samudera (PPS) Lampulo
            $bandaAceh = Regency::where('code', '11.71')->first();
            if ($bandaAceh) {
                $districtsBA = [
                    ['code' => '11.71.01', 'name' => 'Meuraxa'],
                    ['code' => '11.71.02', 'name' => 'Kuta Raja'],
                    ['code' => '11.71.03', 'name' => 'Kuta Alam'],
                    ['code' => '11.71.04', 'name' => 'Baiturrahman'],
                    ['code' => '11.71.05', 'name' => 'Syiah Kuala'],
                    ['code' => '11.71.06', 'name' => 'Ulee Kareng'],
                ];
                foreach ($districtsBA as $d) {
                    $dist = District::updateOrCreate(
                        ['code' => $d['code']],
                        ['regency_id' => $bandaAceh->id, 'name' => $d['name']]
                    );

                    // Lokasi PPS Lampulo di Kuta Raja
                    if ($d['code'] === '11.71.02') {
                        $gampongs = [
                            ['code' => '11.71.02.2001', 'name' => 'Gampong Lampulo', 'postal_code' => '23122'],
                            ['code' => '11.71.02.2002', 'name' => 'Gampong Peulanggahan', 'postal_code' => '23121'],
                            ['code' => '11.71.02.2003', 'name' => 'Gampong Jawa', 'postal_code' => '23123'],
                            ['code' => '11.71.02.2004', 'name' => 'Gampong Pande', 'postal_code' => '23124'],
                        ];
                        foreach ($gampongs as $g) {
                            Village::updateOrCreate(
                                ['code' => $g['code']],
                                ['district_id' => $dist->id, 'name' => $g['name'], 'postal_code' => $g['postal_code']]
                            );
                        }
                    }

                    // Pesisir Ulee Lheue di Meuraxa
                    if ($d['code'] === '11.71.01') {
                        $gampongs = [
                            ['code' => '11.71.01.2001', 'name' => 'Gampong Ulee Lheue', 'postal_code' => '23232'],
                            ['code' => '11.71.01.2002', 'name' => 'Gampong Deah Glumpang', 'postal_code' => '23233'],
                            ['code' => '11.71.01.2003', 'name' => 'Gampong Alue Deah Teungoh', 'postal_code' => '23234'],
                            ['code' => '11.71.01.2004', 'name' => 'Gampong Lambung', 'postal_code' => '23235'],
                        ];
                        foreach ($gampongs as $g) {
                            Village::updateOrCreate(
                                ['code' => $g['code']],
                                ['district_id' => $dist->id, 'name' => $g['name'], 'postal_code' => $g['postal_code']]
                            );
                        }
                    }
                }
            }

            // B. Kabupaten Aceh Besar (11.06) - Wilayah Pesisir & Pulo Aceh
            $acehBesar = Regency::where('code', '11.06')->first();
            if ($acehBesar) {
                $districtsAB = [
                    ['code' => '11.06.01', 'name' => 'Lhoong'],
                    ['code' => '11.06.02', 'name' => 'Lhoknga'],
                    ['code' => '11.06.07', 'name' => 'Peukan Bada'],
                    ['code' => '11.06.19', 'name' => 'Baitussalam'],
                    ['code' => '11.06.21', 'name' => 'Pulo Aceh'],
                ];
                foreach ($districtsAB as $d) {
                    $dist = District::updateOrCreate(
                        ['code' => $d['code']],
                        ['regency_id' => $acehBesar->id, 'name' => $d['name']]
                    );

                    if ($d['code'] === '11.06.19') { // Baitussalam
                        $gampongs = [
                            ['code' => '11.06.19.2001', 'name' => 'Gampong Cadek', 'postal_code' => '23373'],
                            ['code' => '11.06.19.2002', 'name' => 'Gampong Kajhu', 'postal_code' => '23373'],
                        ];
                        foreach ($gampongs as $g) {
                            Village::updateOrCreate(
                                ['code' => $g['code']],
                                ['district_id' => $dist->id, 'name' => $g['name'], 'postal_code' => $g['postal_code']]
                            );
                        }
                    }

                    if ($d['code'] === '11.06.21') { // Pulo Aceh
                        $gampongs = [
                            ['code' => '11.06.21.2001', 'name' => 'Gampong Gugop', 'postal_code' => '23991'],
                            ['code' => '11.06.21.2002', 'name' => 'Gampong Lampuyang', 'postal_code' => '23991'],
                        ];
                        foreach ($gampongs as $g) {
                            Village::updateOrCreate(
                                ['code' => $g['code']],
                                ['district_id' => $dist->id, 'name' => $g['name'], 'postal_code' => $g['postal_code']]
                            );
                        }
                    }
                }
            }

            // C. Kota Sabang (11.72) - Pulau Weh (WPPNRI 572)
            $sabang = Regency::where('code', '11.72')->first();
            if ($sabang) {
                $dist = District::updateOrCreate(
                    ['code' => '11.72.01'],
                    ['regency_id' => $sabang->id, 'name' => 'Sukakarya']
                );
                Village::updateOrCreate(
                    ['code' => '11.72.01.2001'],
                    ['district_id' => $dist->id, 'name' => 'Gampong Iboih', 'postal_code' => '23518']
                );
            }

            // D. Kabupaten Aceh Timur (11.03) - Sentra PPI Kuala Idi
            $acehTimur = Regency::where('code', '11.03')->first();
            if ($acehTimur) {
                $dist = District::updateOrCreate(
                    ['code' => '11.03.04'],
                    ['regency_id' => $acehTimur->id, 'name' => 'Idi Rayeuk']
                );
                Village::updateOrCreate(
                    ['code' => '11.03.04.2001'],
                    ['district_id' => $dist->id, 'name' => 'Gampong Kuala Idi', 'postal_code' => '24454']
                );
            }

            // E. Kabupaten Aceh Barat (11.05) - Sentra PPI Ujong Baroh Meulaboh
            $acehBarat = Regency::where('code', '11.05')->first();
            if ($acehBarat) {
                $dist = District::updateOrCreate(
                    ['code' => '11.05.01'],
                    ['regency_id' => $acehBarat->id, 'name' => 'Johan Pahlawan']
                );
                Village::updateOrCreate(
                    ['code' => '11.05.01.2001'],
                    ['district_id' => $dist->id, 'name' => 'Gampong Ujong Baroh', 'postal_code' => '23611']
                );
            }

            // F. Kabupaten Simeulue (11.17) - Kepulauan Samudera Hindia (Sentra Lobster & Tuna)
            $simeulue = Regency::where('code', '11.17')->first();
            if ($simeulue) {
                $dist = District::updateOrCreate(
                    ['code' => '11.17.01'],
                    ['regency_id' => $simeulue->id, 'name' => 'Simeulue Timur']
                );
                Village::updateOrCreate(
                    ['code' => '11.17.01.2001'],
                    ['district_id' => $dist->id, 'name' => 'Gampong Suka Jaya', 'postal_code' => '23891']
                );
            }

            // G. Kabupaten Aceh Singkil (11.18) - Pesisir & Kepulauan Banyak
            $singkil = Regency::where('code', '11.18')->first();
            if ($singkil) {
                $dist = District::updateOrCreate(
                    ['code' => '11.18.01'],
                    ['regency_id' => $singkil->id, 'name' => 'Singkil']
                );
                Village::updateOrCreate(
                    ['code' => '11.18.01.2001'],
                    ['district_id' => $dist->id, 'name' => 'Gampong Pulo Sarok', 'postal_code' => '24785']
                );
            }
        }
    }
}
