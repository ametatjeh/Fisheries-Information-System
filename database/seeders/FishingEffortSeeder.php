<?php

namespace Database\Seeders;

use App\Models\FishingEffort;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use Illuminate\Database\Seeder;

class FishingEffortSeeder extends Seeder
{
    /**
     * Seed master data upaya penangkapan ikan (Fishing Efforts / Setting & Hauling) di Aceh.
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $efforts = [
            // --- TRIP-202609-0001: KM Inka Mina 704 (Purse Seine Pelagis Besar) ---
            [
                'trip_number' => 'TRIP-202609-0001',
                'gear_code' => 'PS-01',
                'setting_number' => 1,
                'setting_date' => '2026-08-26 05:45:00',
                'hauling_date' => '2026-08-26 08:30:00',
                'duration_hours' => 2.75,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 750.00,
                'latitude_setting' => 5.8821000,
                'longitude_setting' => 94.6710000,
                'latitude_hauling' => 5.8750000,
                'longitude_hauling' => 94.6650000,
            ],
            [
                'trip_number' => 'TRIP-202609-0001',
                'gear_code' => 'PS-01',
                'setting_number' => 2,
                'setting_date' => '2026-08-28 06:00:00',
                'hauling_date' => '2026-08-28 09:15:00',
                'duration_hours' => 3.25,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 750.00,
                'latitude_setting' => 6.0500000,
                'longitude_setting' => 94.4500000,
                'latitude_hauling' => 6.0420000,
                'longitude_hauling' => 94.4400000,
            ],

            // --- TRIP-202609-0002: KM Lampulo Jaya Mandiri (Purse Seine Pelagis Kecil) ---
            [
                'trip_number' => 'TRIP-202609-0002',
                'gear_code' => 'PS-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-02 04:00:00',
                'hauling_date' => '2026-09-02 06:30:00',
                'duration_hours' => 2.50,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 500.00,
                'latitude_setting' => 5.7350000,
                'longitude_setting' => 95.0620000,
                'latitude_hauling' => 5.7300000,
                'longitude_hauling' => 95.0550000,
            ],

            // --- TRIP-202609-0003: KM Malaka Rayeuk (Drift Gillnet Selat Malaka) ---
            [
                'trip_number' => 'TRIP-202609-0003',
                'gear_code' => 'GN-01', // Jaring Insang Hanyut
                'setting_number' => 1,
                'setting_date' => '2026-09-03 21:00:00',
                'hauling_date' => '2026-09-04 05:00:00',
                'duration_hours' => 8.00,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 1200.00,
                'latitude_setting' => 5.3700000,
                'longitude_setting' => 97.3800000,
                'latitude_hauling' => 5.3800000,
                'longitude_hauling' => 97.3900000,
            ],

            // --- TRIP-202609-0004: KM Selat Malaka 02 (Purse Seine Teri & Layang) ---
            [
                'trip_number' => 'TRIP-202609-0004',
                'gear_code' => 'PS-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-04 10:00:00',
                'hauling_date' => '2026-09-04 12:30:00',
                'duration_hours' => 2.50,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 450.00,
                'latitude_setting' => 5.1200000,
                'longitude_setting' => 98.1500000,
                'latitude_hauling' => 5.1150000,
                'longitude_hauling' => 98.1400000,
            ],

            // --- TRIP-202609-0005: KM Meulaboh Bahari (Handline Pancing Ulur Demersal) ---
            [
                'trip_number' => 'TRIP-202609-0005',
                'gear_code' => 'HL-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-06 09:00:00',
                'hauling_date' => '2026-09-06 14:00:00',
                'duration_hours' => 5.00,
                'setting_count' => 12,
                'hook_count' => 48,
                'net_length_meters' => null,
                'latitude_setting' => 4.0500000,
                'longitude_setting' => 95.9500000,
                'latitude_hauling' => 4.0450000,
                'longitude_hauling' => 95.9400000,
            ],

            // --- TRIP-202609-0006: KM Pulo Breueh Mandiri (Pancing Tonda & Ulur) ---
            [
                'trip_number' => 'TRIP-202609-0006',
                'gear_code' => 'HL-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-05 11:00:00',
                'hauling_date' => '2026-09-05 15:30:00',
                'duration_hours' => 4.50,
                'setting_count' => 8,
                'hook_count' => 24,
                'net_length_meters' => null,
                'latitude_setting' => 5.6800000,
                'longitude_setting' => 95.1200000,
                'latitude_hauling' => 5.6750000,
                'longitude_hauling' => 95.1100000,
            ],

            // --- TRIP-202609-0007: PMT Ulee Lheue Samudera (Handline Pesisir) ---
            [
                'trip_number' => 'TRIP-202609-0007',
                'gear_code' => 'HL-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-06 11:45:00',
                'hauling_date' => '2026-09-06 15:00:00',
                'duration_hours' => 3.25,
                'setting_count' => 6,
                'hook_count' => 12,
                'net_length_meters' => null,
                'latitude_setting' => 5.6200000,
                'longitude_setting' => 95.2400000,
                'latitude_hauling' => 5.6180000,
                'longitude_hauling' => 95.2350000,
            ],

            // --- TRIP-202609-0008: KM Sabang Bahari Indah (Tuna Longline) ---
            [
                'trip_number' => 'TRIP-202609-0008',
                'gear_code' => 'LL-01', // Tuna Longline
                'setting_number' => 1,
                'setting_date' => '2026-09-06 13:00:00',
                'hauling_date' => '2026-09-06 18:00:00',
                'duration_hours' => 5.00,
                'setting_count' => 1,
                'hook_count' => 250,
                'net_length_meters' => null,
                'latitude_setting' => 6.0450000,
                'longitude_setting' => 95.1100000,
                'latitude_hauling' => 6.0350000,
                'longitude_hauling' => 95.0950000,
            ],

            // --- TRIP-202609-0009: KM Inka Mina 704 (Trip Berjalan - Setting 1) ---
            [
                'trip_number' => 'TRIP-202609-0009',
                'gear_code' => 'PS-01',
                'setting_number' => 1,
                'setting_date' => '2026-09-08 08:45:00',
                'hauling_date' => '2026-09-08 11:30:00',
                'duration_hours' => 2.75,
                'setting_count' => 1,
                'hook_count' => null,
                'net_length_meters' => 800.00,
                'latitude_setting' => 5.8200000,
                'longitude_setting' => 94.6500000,
                'latitude_hauling' => 5.8150000,
                'longitude_hauling' => 94.6400000,
            ],
        ];

        foreach ($efforts as $item) {
            $trip = FishingTrip::where('trip_number', $item['trip_number'])->first();
            $gear = FishingGear::where('code', $item['gear_code'])->first();

            if (! $trip || ! $gear) {
                continue;
            }

            FishingEffort::firstOrCreate(
                [
                    'fishing_trip_id' => $trip->id,
                    'setting_number' => $item['setting_number'],
                ],
                [
                    'fishing_gear_id' => $gear->id,
                    'setting_date' => $item['setting_date'],
                    'hauling_date' => $item['hauling_date'],
                    'duration_hours' => $item['duration_hours'],
                    'setting_count' => $item['setting_count'],
                    'hook_count' => $item['hook_count'],
                    'net_length_meters' => $item['net_length_meters'],
                    'latitude_setting' => $item['latitude_setting'],
                    'longitude_setting' => $item['longitude_setting'],
                    'latitude_hauling' => $item['latitude_hauling'],
                    'longitude_hauling' => $item['longitude_hauling'],
                ]
            );
        }
    }
}
