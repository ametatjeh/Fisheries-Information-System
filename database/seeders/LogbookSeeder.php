<?php

namespace Database\Seeders;

use App\Models\FishingTrip;
use App\Models\Logbook;
use Illuminate\Database\Seeder;

class LogbookSeeder extends Seeder
{
    /**
     * Seed logbook harian kapal penangkap ikan di perairan Aceh (WPPNRI 571 & 572).
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $logEntries = [
            // --- TRIP-202609-0001: KM Inka Mina 704 (Samudera Hindia) ---
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-25',
                'log_time' => '07:30:00',
                'latitude' => 5.6125000,
                'longitude' => 95.3210000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.75,
                'sea_condition' => 'Tenang, angin timur laut 5 knot',
                'activity_description' => 'Kapal tolak dari dermaga PPS Lampulo Banda Aceh menuju fishing ground Samudera Hindia ZEEI. Mesin dan navigasi GPS radar normal.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-26',
                'log_time' => '04:15:00',
                'latitude' => 5.8942000,
                'longitude' => 94.6850000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.50,
                'sea_condition' => 'Sedang, arus ke barat laut 1.2 knot',
                'activity_description' => 'Tiba di koordinat rumpon laut dalam nomor 04. Persiapan lampu atraksi penarik kawanan tuna madidihang dan cakalang.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-26',
                'log_time' => '05:45:00',
                'latitude' => 5.8821000,
                'longitude' => 94.6710000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.25,
                'sea_condition' => 'Sedang, arus stabil',
                'activity_description' => 'Penurunan jaring lingkar pukat cincin (setting 1). Lingkaran jaring sempurna melingkari rumpon.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-26',
                'log_time' => '08:30:00',
                'latitude' => 5.8750000,
                'longitude' => 94.6650000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.20,
                'sea_condition' => 'Ombak beralun tenang',
                'activity_description' => 'Penarikan jaring (hauling 1) selesai. Estimasi tangkapan cakalang dan tuna madidihang sekitar 3.2 ton. Ikan langsung dimasukkan ke palka 1 dengan es curah.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-27',
                'log_time' => '17:00:00',
                'latitude' => 6.1205000,
                'longitude' => 94.3100000,
                'weather_condition' => 'hujan_ringan',
                'wave_height_meters' => 2.10,
                'sea_condition' => 'Kasar, angin kencang barat daya 16 knot',
                'activity_description' => 'Cuaca memburuk, hujan rintik disertai gelombang tinggi. Kapal melambat dan memposisikan haluan membelah ombak untuk keselamatan ABK.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-28',
                'log_time' => '06:00:00',
                'latitude' => 6.0500000,
                'longitude' => 94.4500000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.40,
                'sea_condition' => 'Kondisi laut membaik, cerah pagi',
                'activity_description' => 'Setting ke-2 pukat cincin di sekitar rumpon hanyut (FAD). Perkiraan hasil 2.8 ton madidihang.',
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'log_date' => '2026-08-30',
                'log_time' => '14:20:00',
                'latitude' => 5.5950000,
                'longitude' => 95.3180000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.60,
                'sea_condition' => 'Tenang, alur masuk kolam labuh',
                'activity_description' => 'Kapal memasuki alur pelayaran PPS Lampulo. Sandar di dermaga bongkar pukul 15.45 WIB dengan muatan palka aman.',
            ],

            // --- TRIP-202609-0002: KM Lampulo Jaya Mandiri (Pulo Aceh - Pulo Rondo) ---
            [
                'trip_number' => 'TRIP-202609-0002',
                'log_date' => '2026-09-01',
                'log_time' => '08:00:00',
                'latitude' => 5.5900000,
                'longitude' => 95.3150000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.80,
                'sea_condition' => 'Tenang, angin timur sepoi-sepoi',
                'activity_description' => 'Berlayar keluar kolam pelabuhan Lampulo menuju perairan Selat Benggala dan Pulo Breueh.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'log_date' => '2026-09-01',
                'log_time' => '21:30:00',
                'latitude' => 5.7200000,
                'longitude' => 95.0500000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.10,
                'sea_condition' => 'Arus pelan ke timur',
                'activity_description' => 'Nyalakan lampu genset penarik ikan di utara Pulo Nasi. Terdeteksi schooling ikan tongkol dan layang pada sonar/fishfinder.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'log_date' => '2026-09-02',
                'log_time' => '04:00:00',
                'latitude' => 5.7350000,
                'longitude' => 95.0620000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.90,
                'sea_condition' => 'Tenang',
                'activity_description' => 'Mulai operasi pukat cincin malam hari. Hauling selesai pukul 06.30 WIB dengan hasil tongkol krai dan selar.',
            ],
            [
                'trip_number' => 'TRIP-202609-0002',
                'log_date' => '2026-09-03',
                'log_time' => '16:00:00',
                'latitude' => 5.6020000,
                'longitude' => 95.3200000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.70,
                'sea_condition' => 'Kondisi perairan tenang',
                'activity_description' => 'Perjalanan pulang dan sandar di TPI Lampulo. ABK mulai membongkar hasil tangkapan untuk penimbangan enumerator.',
            ],

            // --- TRIP-202609-0003: KM Malaka Rayeuk (Selat Malaka - Lhokseumawe) ---
            [
                'trip_number' => 'TRIP-202609-0003',
                'log_date' => '2026-09-02',
                'log_time' => '09:00:00',
                'latitude' => 5.1850000,
                'longitude' => 97.1500000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.50,
                'sea_condition' => 'Laut Selat Malaka sangat tenang',
                'activity_description' => 'Tolak dari PPI Pusong Lhokseumawe menuju perairan 20 mil timur laut Lhokseumawe (WPPNRI 571).',
            ],
            [
                'trip_number' => 'TRIP-202609-0003',
                'log_date' => '2026-09-03',
                'log_time' => '15:20:00',
                'latitude' => 5.3400000,
                'longitude' => 97.3500000,
                'weather_condition' => 'hujan_lebat',
                'wave_height_meters' => 1.80,
                'sea_condition' => 'Hujan deras disertai angin ribut 20 knot',
                'activity_description' => 'Hujan lebat di perairan Selat Malaka. Operasi gillnet ditunda sementara hingga badai squall reda demi keselamatan operasional.',
            ],
            [
                'trip_number' => 'TRIP-202609-0003',
                'log_date' => '2026-09-04',
                'log_time' => '05:00:00',
                'latitude' => 5.3800000,
                'longitude' => 97.3900000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 0.90,
                'sea_condition' => 'Ombak mereda',
                'activity_description' => 'Hauling jaring insang hanyut (drift gillnet). Hasil tangkapan ikan tenggiri batang dan bawal hitam.',
            ],

            // --- TRIP-202609-0004: KM Selat Malaka 02 (Idi Rayeuk) ---
            [
                'trip_number' => 'TRIP-202609-0004',
                'log_date' => '2026-09-03',
                'log_time' => '06:45:00',
                'latitude' => 4.9600000,
                'longitude' => 97.7800000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.60,
                'sea_condition' => 'Tenang beriak kecil',
                'activity_description' => 'Keluar muara Kuala Idi Rayeuk Aceh Timur dengan 12 orang awak kapal.',
            ],
            [
                'trip_number' => 'TRIP-202609-0004',
                'log_date' => '2026-09-04',
                'log_time' => '10:00:00',
                'latitude' => 5.1200000,
                'longitude' => 98.1500000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.80,
                'sea_condition' => 'Arus ke arah tenggara',
                'activity_description' => 'Operasi pukat cincin teri dan layang. Suhu palka es stabil 0 derajat Celcius.',
            ],

            // --- TRIP-202609-0005: KM Meulaboh Bahari (Meulaboh - Samudera Hindia) ---
            [
                'trip_number' => 'TRIP-202609-0005',
                'log_date' => '2026-09-04',
                'log_time' => '08:15:00',
                'latitude' => 4.1400000,
                'longitude' => 96.1200000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.60,
                'sea_condition' => 'Gelombang khas pantai barat Sumatera',
                'activity_description' => 'Kapal bertolak dari PPI Ujong Baroh Meulaboh menuju perairan barat Nagan Raya dan perairan lepas.',
            ],
            [
                'trip_number' => 'TRIP-202609-0005',
                'log_date' => '2026-09-05',
                'log_time' => '07:30:00',
                'latitude' => 3.9800000,
                'longitude' => 95.8500000,
                'weather_condition' => 'badai',
                'wave_height_meters' => 2.80,
                'sea_condition' => 'Badai Samudera Hindia, angin barat 25 knot',
                'activity_description' => 'Menghadapi badai tropis lokal. Kecepatan kapal diturunkan ke 4 knot, seluruh peralatan diamankan di dek kapal.',
            ],
            [
                'trip_number' => 'TRIP-202609-0005',
                'log_date' => '2026-09-06',
                'log_time' => '09:00:00',
                'latitude' => 4.0500000,
                'longitude' => 95.9500000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.50,
                'sea_condition' => 'Gelombang mereda, cerah kembali',
                'activity_description' => 'Melanjutkan penangkapan pancing ulur (handline) ikan kakap merah dan kerapu batu.',
            ],

            // --- TRIP-202609-0006: KM Pulo Breueh Mandiri (Ulee Lheue) ---
            [
                'trip_number' => 'TRIP-202609-0006',
                'log_date' => '2026-09-04',
                'log_time' => '05:30:00',
                'latitude' => 5.5600000,
                'longitude' => 95.2800000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.70,
                'sea_condition' => 'Pagi tenang di Selat Benggala',
                'activity_description' => 'Kapal berangkat dari dermaga nelayan PPI Ulee Lheue menuju spot karang Pulo Aceh.',
            ],
            [
                'trip_number' => 'TRIP-202609-0006',
                'log_date' => '2026-09-05',
                'log_time' => '11:00:00',
                'latitude' => 5.6800000,
                'longitude' => 95.1200000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.00,
                'sea_condition' => 'Normal berombak',
                'activity_description' => 'Operasi pancing tonda dan pancing ulur. Menangkap cakalang dan lemadang.',
            ],

            // --- TRIP-202609-0007: PMT Ulee Lheue Samudera (One-Day Trip) ---
            [
                'trip_number' => 'TRIP-202609-0007',
                'log_date' => '2026-09-06',
                'log_time' => '06:00:00',
                'latitude' => 5.5620000,
                'longitude' => 95.2780000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.50,
                'sea_condition' => 'Tenang',
                'activity_description' => 'Perahu motor tempel berangkat melaut satu hari (one-day fishing) di perairan Teluk Banda Aceh.',
            ],
            [
                'trip_number' => 'TRIP-202609-0007',
                'log_date' => '2026-09-06',
                'log_time' => '11:45:00',
                'latitude' => 5.6200000,
                'longitude' => 95.2400000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.65,
                'sea_condition' => 'Tenang cerah',
                'activity_description' => 'Penarikan pancing ulur di dekat rumpon nelayan lokal. Mendapat kuwe/bubara dan kerapu.',
            ],
            [
                'trip_number' => 'TRIP-202609-0007',
                'log_date' => '2026-09-06',
                'log_time' => '16:30:00',
                'latitude' => 5.5610000,
                'longitude' => 95.2790000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.50,
                'sea_condition' => 'Tenang',
                'activity_description' => 'Kembali sandar di pantai Ulee Lheue dengan selamat.',
            ],

            // --- TRIP-202609-0008: KM Sabang Bahari Indah (Sabang) ---
            [
                'trip_number' => 'TRIP-202609-0008',
                'log_date' => '2026-09-05',
                'log_time' => '06:15:00',
                'latitude' => 5.8900000,
                'longitude' => 95.3250000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.00,
                'sea_condition' => 'Segar berangin laut',
                'activity_description' => 'Bertolak dari Pelabuhan Perikanan Ie Meulee Sabang menuju perairan utara Pulau Weh dan Pulo Rondo.',
            ],
            [
                'trip_number' => 'TRIP-202609-0008',
                'log_date' => '2026-09-06',
                'log_time' => '13:00:00',
                'latitude' => 6.0450000,
                'longitude' => 95.1100000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.30,
                'sea_condition' => 'Arus perbatasan Laut Andaman',
                'activity_description' => 'Operasi pancing ulur tuna sirip kuning di kedalaman 80-120 meter.',
            ],

            // --- TRIP-202609-0009: KM Inka Mina 704 (Trip Berjalan / In Progress) ---
            [
                'trip_number' => 'TRIP-202609-0009',
                'log_date' => '2026-09-06',
                'log_time' => '07:00:00',
                'latitude' => 5.6100000,
                'longitude' => 95.3200000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 0.80,
                'sea_condition' => 'Laut tenang pagi hari',
                'activity_description' => 'Kapal tolak untuk trip penangkapan baru menuju WPPNRI 572 Samudera Hindia ZEEI. Bahan bakar 1.900L dan es 4 ton terisi penuh.',
            ],
            [
                'trip_number' => 'TRIP-202609-0009',
                'log_date' => '2026-09-07',
                'log_time' => '14:00:00',
                'latitude' => 5.7500000,
                'longitude' => 94.8000000,
                'weather_condition' => 'berawan',
                'wave_height_meters' => 1.60,
                'sea_condition' => 'Sedang, kapal berlayar menuju koordinat rumpon A1',
                'activity_description' => 'Kapal sedang berlayar stabil menuju fishing ground rumpon A1 di Samudera Hindia barat Aceh.',
            ],
            [
                'trip_number' => 'TRIP-202609-0009',
                'log_date' => '2026-09-08',
                'log_time' => '08:45:00',
                'latitude' => 5.8200000,
                'longitude' => 94.6500000,
                'weather_condition' => 'cerah',
                'wave_height_meters' => 1.35,
                'sea_condition' => 'Tenang, ikan aktif berenang dekat rumpon',
                'activity_description' => 'Persiapan penurunan jaring pukat cincin putaran pertama. Terdeteksi kumpulan ikan cakalang dan tongkol.',
            ],
        ];

        foreach ($logEntries as $item) {
            $trip = FishingTrip::where('trip_number', $item['trip_number'])->first();

            if (! $trip) {
                continue;
            }

            Logbook::firstOrCreate(
                [
                    'fishing_trip_id' => $trip->id,
                    'log_date' => $item['log_date'],
                    'log_time' => $item['log_time'],
                ],
                [
                    'latitude' => $item['latitude'],
                    'longitude' => $item['longitude'],
                    'weather_condition' => $item['weather_condition'],
                    'wave_height_meters' => $item['wave_height_meters'],
                    'sea_condition' => $item['sea_condition'],
                    'activity_description' => $item['activity_description'],
                ]
            );
        }
    }
}
