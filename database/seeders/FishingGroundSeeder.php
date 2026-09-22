<?php

namespace Database\Seeders;

use App\Models\FishingGround;
use App\Models\Wppnri;
use Illuminate\Database\Seeder;

class FishingGroundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Master Data Daerah Penangkapan Ikan (Fishing Ground) di wilayah perairan Aceh.
     * Bersumber dari literatur ilmiah dan studi oseanografi perikanan:
     * 1. Penelitian Daerah Penangkapan Ikan Pulo Aceh (Fakultas Kelautan dan Perikanan Universitas Syiah Kuala).
     * 2. Studi Daerah Penangkapan Armada Purse Seine PPS Lampulo (USK / IPB / DKP).
     * 3. Studi Pemetaan Potensi Fishing Ground Gurita (Octopus sp.) di Simeulue.
     *
     * Koordinat bernilai NULL karena entitas ini merupakan zona/area perairan referensi (bukan titik setting alat tangkap tunggal).
     */
    public function run(): void
    {
        $wpp571 = Wppnri::where('code', '571')->first();
        $wpp572 = Wppnri::where('code', '572')->first();

        $grounds = [
            [
                'code' => 'FG-ACEH-001',
                'name' => 'Perairan Ujong Pulo Breuh',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Zona daerah penangkapan ikan pelagis berdasarkan analisis sebaran klorofil-a dan suhu permukaan laut (SPL) di perairan utara Pulo Breuh, Pulo Aceh, Kabupaten Aceh Besar (Sumber: Studi FKP Universitas Syiah Kuala).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-002',
                'name' => 'Perairan Pulau Keureusik - Ujong Keumuroh',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Zona potensi penangkapan ikan (ZPPI) pelagis berdasarkan parameter oseanografi di perairan sebelah timur Pulau Keureusik hingga Ujong Keumuroh, Kepulauan Pulo Aceh (Sumber: Jurnal Ilmiah Mahasiswa Kelautan dan Perikanan USK).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-003',
                'name' => 'Perairan Pulo Aceh',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Daerah penangkapan ikan umum di gugusan Kepulauan Pulo Aceh, Kabupaten Aceh Besar, yang merupakan daerah tujuan penangkapan nelayan tradisional dan purse seine lokal.',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-004',
                'name' => 'Perairan Pesisir Pidie',
                'wppnri_id' => $wpp571?->id,
                'description' => 'Daerah penangkapan ikan pelagis kecil utama (ikan layang/Decapterus sp., tongkol, selar) armada purse seine berbasis Pelabuhan Perikanan Samudera (PPS) Lampulo di perairan Selat Malaka pesisir Kabupaten Pidie.',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-005',
                'name' => 'Perairan Barat Daya Pulau Weh - Pulau Beras',
                'wppnri_id' => $wpp571?->id,
                'description' => 'Daerah penangkapan ikan armada purse seine satu hari (one day fishing) di koridor perairan barat daya Pulau Weh dan Pulau Beras, batas oseanografi Selat Malaka dan Laut Andaman (WPPNRI 571).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-006',
                'name' => 'Perairan Karang Teupah Barat',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Daerah penangkapan perikanan karang dan habitat gurita (Octopus sp.) di perairan pesisir Kecamatan Teupah Barat, Kabupaten Simeulue, terdokumentasi dalam studi pemetaan potensi perikanan gurita Simeulue (WPPNRI 572).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-007',
                'name' => 'Perairan Karang Teupah Selatan',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Daerah penangkapan perikanan karang dan habitat utama gurita (Octopus sp.) nelayan artisanal pesisir di Kecamatan Teupah Selatan, Kabupaten Simeulue, Samudera Hindia (WPPNRI 572).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
            [
                'code' => 'FG-ACEH-008',
                'name' => 'Perairan Karang Alafan',
                'wppnri_id' => $wpp572?->id,
                'description' => 'Daerah penangkapan perikanan karang dan gurita (Octopus sp.) di pesisir barat laut Kabupaten Simeulue (Kecamatan Alafan) yang berbatasan langsung dengan laut lepas Samudera Hindia (WPPNRI 572).',
                'latitude' => null,
                'longitude' => null,
                'is_active' => true,
            ],
        ];

        foreach ($grounds as $ground) {
            FishingGround::updateOrCreate(
                ['code' => $ground['code']],
                $ground
            );
        }
    }
}
