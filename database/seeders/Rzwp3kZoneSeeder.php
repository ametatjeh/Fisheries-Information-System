<?php

namespace Database\Seeders;

use App\Models\Regency;
use App\Models\Rzwp3kZone;
use Illuminate\Database\Seeder;

class Rzwp3kZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Katalog Resmi Zona RZWP3K Aceh berdasarkan Qanun Aceh Nomor 1 Tahun 2020.
     * Tidak menghasilkan geometri/koordinat fiktif (geometry bernilai NULL sampai diimpor dari GeoJSON resmi).
     */
    public function run(): void
    {
        $regencies = Regency::pluck('id', 'name')->all();

        $zones = [
            // =========================================================================
            // 1. KAWASAN PEMANFAATAN UMUM (KPU)
            // =========================================================================
            [
                'code' => 'KPU-PT-01',
                'parent_code' => 'KPU',
                'name' => 'Zona Perikanan Tangkap Pantai Timur-Utara Aceh (WPP 571)',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PT',
                'description' => 'Alokasi ruang pemanfaatan penangkapan ikan pelagis dan demersal di perairan Selat Malaka dan Laut Andaman.',
                'regency_id' => $regencies['Kota Banda Aceh'] ?? null,
                'area_ha' => null, // Sesuai lampiran matriks koordinat
                'metadata' => [
                    'category' => 'Perikanan Tangkap',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 14',
                    'allowed_activities' => ['Penangkapan ikan ramah lingkungan', 'Pelayaran kapal perikanan', 'Riset perikanan tangkap'],
                    'prohibited_activities' => ['Penggunaan alat tangkap destruktif (trawl terlarang, bom, racun)', 'Pencemaran limbah cair'],
                    'notes' => 'Katalog resmi Qanun Aceh 1/2020; geometri resmi dimuat via importer spasial.',
                ],
            ],
            [
                'code' => 'KPU-PT-02',
                'parent_code' => 'KPU',
                'name' => 'Zona Perikanan Tangkap Pantai Barat-Selatan Aceh (WPP 572)',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PT',
                'description' => 'Alokasi ruang penangkapan ikan pelagis besar (tuna, cakalang, tongkol) di perairan Samudera Hindia.',
                'regency_id' => $regencies['Aceh Barat'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Perikanan Tangkap',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 14',
                    'allowed_activities' => ['Penangkapan ikan dengan pancing ulur, rawai tuna, pukat cincin', 'Operasi kapal motor'],
                    'prohibited_activities' => ['Alat tangkap merusak karang'],
                    'notes' => 'Katalog resmi Qanun Aceh 1/2020.',
                ],
            ],
            [
                'code' => 'KPU-PB-01',
                'parent_code' => 'KPU',
                'name' => 'Zona Perikanan Budidaya Laut & Pesisir Aceh',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PB',
                'description' => 'Alokasi perairan pesisir terlindung untuk budidaya laut (keramba jaring apung, rumput laut, kerapu, kakap putih).',
                'regency_id' => $regencies['Aceh Besar'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Perikanan Budidaya',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 15',
                    'allowed_activities' => ['Keramba jaring apung (KJA)', 'Budidaya rumput laut', 'Pendederan benih'],
                    'prohibited_activities' => ['Penggunaan pakan dan bahan kimia berbahaya', 'Pembuangan limbah beracun'],
                ],
            ],
            [
                'code' => 'KPU-W-01',
                'parent_code' => 'KPU',
                'name' => 'Zona Pariwisata Bahari Sabang & Pulo Aceh',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-W',
                'description' => 'Peruntukan ruang rekreasi pantai, wisata selam (diving), snorkeling, dan olahraga air berkelanjutan.',
                'regency_id' => $regencies['Kota Sabang'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Pariwisata Bahari',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 16',
                    'allowed_activities' => ['Wisata selam dan snorkeling', 'Ekowisata bahari', 'Olahraga selancar'],
                    'prohibited_activities' => ['Pengambilan karang dan biota hias dilindungi', 'Buang jangkar di atas terumbu karang'],
                ],
            ],
            [
                'code' => 'KPU-PL-01',
                'parent_code' => 'KPU',
                'name' => 'Zona Pelabuhan Perikanan Samudera (PPS) Lampulo & Sekitarnya',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-PL',
                'description' => 'Fasilitas tambat labuh, dermaga pendaratan, alur kolam pelabuhan perikanan, dan docking kapal.',
                'regency_id' => $regencies['Kota Banda Aceh'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Pelabuhan Perikanan',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 17',
                    'allowed_activities' => ['Aktivitas bongkar muat hasil tangkapan', 'Tambat labuh kapal perikanan', 'Perbaikan dan pemeliharaan armada'],
                    'prohibited_activities' => ['Pemasangan alat tangkap permanen di alur kolam pelabuhan'],
                ],
            ],
            [
                'code' => 'KPU-HM-01',
                'parent_code' => 'KPU',
                'name' => 'Zona Ekosistem Mangrove & Sempadan Pantai Pesisir Aceh',
                'zone_type' => 'KPU',
                'subzone_type' => 'KPU-HM',
                'description' => 'Sabuk hijau pelindung abrasi pantai, daerah asuhan (nursery ground), dan penahan tsunami.',
                'regency_id' => $regencies['Aceh Tamiang'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Ekosistem Mangrove',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 19',
                    'allowed_activities' => ['Rehabilitasi mangrove', 'Riset ekosistem pesisir', 'Silvofishery ramah lingkungan'],
                    'prohibited_activities' => ['Penebangan hutan mangrove secara ilegal', 'Konversi tanpa izin'],
                ],
            ],

            // =========================================================================
            // 2. KAWASAN KONSERVASI (KK)
            // =========================================================================
            [
                'code' => 'KK-KKP-01',
                'parent_code' => 'KK',
                'name' => 'Kawasan Konservasi Perairan Daerah (KKPD) Pesisir Timur & Barat Aceh',
                'zone_type' => 'KK',
                'subzone_type' => 'KK-KKP',
                'description' => 'Kawasan konservasi perairan untuk perlindungan habitat terumbu karang, padang lamun, dan pemijahan ikan.',
                'regency_id' => $regencies['Aceh Jaya'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Konservasi Perairan',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 22',
                    'allowed_activities' => ['Riset dan pendidikan lingkungan', 'Wisata bahari terbatas', 'Perikanan tangkap tradisional berizin di zona pemanfaatan'],
                    'prohibited_activities' => ['Penangkapan ikan pada Zona Inti', 'Destruksi habitat karang'],
                ],
            ],
            [
                'code' => 'KK-KKL-01',
                'parent_code' => 'KK',
                'name' => 'Kawasan Konservasi Pesisir dan Pulau-Pulau Kecil (KKP3K) Kepulauan Banyak & Simeulue',
                'zone_type' => 'KK',
                'subzone_type' => 'KK-KKL',
                'description' => 'Konservasi pulau-pulau kecil, peneluran penyu, dan perlindungan megafauna laut di barat Sumatera.',
                'regency_id' => $regencies['Aceh Singkil'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Konservasi Pesisir & Pulau Kecil',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 23',
                    'allowed_activities' => ['Ekowisata pulau terisolasi', 'Patroli pengawasan sumberdaya', 'Kearifan lokal Panglima Laot'],
                    'prohibited_activities' => ['Eksploitasi penyu dan telur penyu', 'Kerusakan terumbu karang'],
                ],
            ],
            [
                'code' => 'KK-TWA-01',
                'parent_code' => 'KK',
                'name' => 'Taman Wisata Alam (TWA) Laut Sabang - Pulau Weh',
                'zone_type' => 'KK',
                'subzone_type' => 'KK-TWA',
                'description' => 'Kawasan pelestarian alam perairan untuk tujuan pariwisata alam dan rekreasi bahari.',
                'regency_id' => $regencies['Kota Sabang'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Taman Wisata Alam Laut',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 24',
                    'allowed_activities' => ['Wisata selam berizin', 'Fotografi bawah air', 'Monitoring terumbu karang'],
                    'prohibited_activities' => ['Penangkapan ikan dengan jaring komersial besar'],
                ],
            ],
            [
                'code' => 'KK-SP-01',
                'parent_code' => 'KK',
                'name' => 'Zona Suaka Perikanan & Perlindungan Pemijahan Ikan (Spawning Ground)',
                'zone_type' => 'KK',
                'subzone_type' => 'KK-SP',
                'description' => 'Zona perlindungan mutlak bagi pemulihan populasi induk ikan dan tempat asuhan larva alami.',
                'regency_id' => $regencies['Aceh Selatan'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Suaka Perikanan',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 25',
                    'allowed_activities' => ['Riset ilmiah terakreditasi', 'Pengawasan perikanan DKP'],
                    'prohibited_activities' => ['Segala bentuk penangkapan ikan komersial', 'Aktivitas ekstraktif'],
                ],
            ],

            // =========================================================================
            // 3. ALUR LAUT (AL)
            // =========================================================================
            [
                'code' => 'AL-P-01',
                'parent_code' => 'AL',
                'name' => 'Zona Alur Pelayaran Kapal & Perlintasan Maritim Aceh',
                'zone_type' => 'AL',
                'subzone_type' => 'AL-P',
                'description' => 'Koridor alur laut lalu lintas kapal niaga, kapal penumpang perintis, dan armada perikanan tangkap.',
                'regency_id' => null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Alur Pelayaran',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 28',
                    'allowed_activities' => ['Navigasi pelayaran kapal', 'Pemasangan rambu suar navigasi'],
                    'prohibited_activities' => ['Pemasangan rumpon atau alat tangkap statis di alur pelayaran', 'Pembuangan jangkar di koridor kabel'],
                ],
            ],
            [
                'code' => 'AL-PK-01',
                'parent_code' => 'AL',
                'name' => 'Zona Koridor Pipa dan/atau Kabel Telekomunikasi Bawah Laut',
                'zone_type' => 'AL',
                'subzone_type' => 'AL-PK',
                'description' => 'Koridor pengamanan infrastruktur kabel serat optik bawah laut dan pipa utilitas energi.',
                'regency_id' => null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Pipa / Kabel Bawah Laut',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 29',
                    'allowed_activities' => ['Pemeliharaan kabel bawah laut oleh operator berizin'],
                    'prohibited_activities' => ['Aktivitas penangkapan ikan dengan pukat hela dasar (trawl)', 'Lego jangkar di atas jalur kabel'],
                ],
            ],
            [
                'code' => 'AL-M-01',
                'parent_code' => 'AL',
                'name' => 'Zona Alur Migrasi Biota Laut & Mamalia Laut Aceh',
                'zone_type' => 'AL',
                'subzone_type' => 'AL-M',
                'description' => 'Koridor lintasan alami migrasi mamalia laut (paus, lumba-lumba), pari manta, dan penyu di perairan Aceh.',
                'regency_id' => null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Alur Migrasi Biota Laut',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 30',
                    'allowed_activities' => ['Pengamatan biota laut terkontrol', 'Riset jalur migrasi'],
                    'prohibited_activities' => ['Perburuan dan penangkapan biota laut dilindungi', 'Pemasangan jaring penghalang migrasi'],
                ],
            ],

            // =========================================================================
            // 4. KAWASAN STRATEGIS NASIONAL TERTENTU (KSNT)
            // =========================================================================
            [
                'code' => 'KSNT-PPKT-01',
                'parent_code' => 'KSNT',
                'name' => 'Zona Pulau-Pulau Kecil Terluar (PPKT) Pulo Rondo & Pulau Benggala',
                'zone_type' => 'KSNT',
                'subzone_type' => 'KSNT-PPKT',
                'description' => 'Zona kedaulatan, pertahanan, dan titik pangkal kepulauan Indonesia di ujung barat Nusantara.',
                'regency_id' => $regencies['Kota Sabang'] ?? null,
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Pulau Kecil Terluar',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 32 & Keppres PPKT',
                    'allowed_activities' => ['Pertahanan dan keamanan negara', 'Navigasi perbatasan internasional', 'Penelitian teritorial'],
                    'prohibited_activities' => ['Aktivitas perusakan lingkungan perbatasan', 'Pemanfaatan ruang tanpa izin pemerintah'],
                ],
            ],
            [
                'code' => 'KSNT-PPKT-02',
                'parent_code' => 'KSNT',
                'name' => 'Zona Pulau-Pulau Kecil Terluar (PPKT) Pulau Simeulue Cut, Salaut Besar, & Pulau Raya',
                'zone_type' => 'KSNT',
                'subzone_type' => 'KSNT-PPKT',
                'description' => 'Zona perbatasan dan pulau kecil terluar di Samudera Hindia sebelah barat Sumatera.',
                'regency_id' => $regencies['Kabupaten Simeulue'] ?? ($regencies['Simeulue'] ?? null),
                'area_ha' => null,
                'metadata' => [
                    'category' => 'Pulau Kecil Terluar',
                    'legal_ref' => 'Qanun Aceh 1/2020 Pasal 32',
                    'allowed_activities' => ['Pengawasan perbatasan', 'Ekowisata perbatasan terkontrol', 'Perikanan nelayan lokal'],
                    'prohibited_activities' => ['Perusakan patok batas dan ekosistem pulau'],
                ],
            ],
        ];

        foreach ($zones as $zoneData) {
            Rzwp3kZone::updateOrCreate(
                ['code' => $zoneData['code']],
                array_merge($zoneData, [
                    'source' => 'DKP Aceh / Bappeda Aceh',
                    'source_document' => 'Qanun Aceh No. 1 Tahun 2020',
                    'legal_basis' => 'Qanun Aceh Nomor 1 Tahun 2020 tentang RZWP3K Aceh 2020-2040',
                    'valid_from' => '2020-01-13',
                    'valid_until' => '2040-01-13',
                    'status' => 'legal_active',
                    'geometry' => null, // Tanpa koordinat palsu; diimpor via GeoJSON resmi
                ])
            );
        }
    }
}
