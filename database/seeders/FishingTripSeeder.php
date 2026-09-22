<?php

namespace Database\Seeders;

use App\Models\Fisherman;
use App\Models\FishingGear;
use App\Models\FishingTrip;
use App\Models\LandingSite;
use App\Models\User;
use App\Models\Vessel;
use Illuminate\Database\Seeder;

class FishingTripSeeder extends Seeder
{
    /**
     * Seed master data operasional Trip Penangkapan Ikan (Fishing Trips) di Aceh.
     * WPPNRI 571 (Selat Malaka & Laut Andaman) dan WPPNRI 572 (Samudera Hindia Barat Sumatera).
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $admin = User::first();

        $trips = [
            // 1. KM Inka Mina 704 - Trip Samudera Hindia (WPPNRI 572) - Tervalidasi
            [
                'trip_number' => 'TRIP-202609-0001',
                'vessel_reg' => 'GT. 32 No. 814/Bda',
                'captain_nik' => '1171021508820002', // Pawang Bukhari
                'departure_site_code' => 'PPS-LMP',
                'landing_site_code' => 'PPS-LMP',
                'departure_date' => '2026-08-25 06:30:00',
                'return_date' => '2026-08-30 15:45:00',
                'crew_count' => 18,
                'fuel_consumption_liters' => 1850.00,
                'ice_consumption_kg' => 3500.00,
                'gear_code' => 'PS-01', // Purse Seine
                'fishing_ground_name' => 'Perairan Samudera Hindia Barat Aceh & ZEEI',
                'fma_code' => '572',
                'validation_status' => 'validated',
                'notes' => 'Trip penangkapan tuna dan cakalang. Cuaca laut baik, ombak 1.5 - 2.0 meter.',
            ],
            // 2. KM Lampulo Jaya Mandiri - Trip Pukat Cincin (WPPNRI 572) - Tervalidasi
            [
                'trip_number' => 'TRIP-202609-0002',
                'vessel_reg' => 'GT. 18 No. 422/Bda',
                'captain_nik' => '1171021508820002',
                'departure_site_code' => 'PPS-LMP',
                'landing_site_code' => 'PPS-LMP',
                'departure_date' => '2026-09-01 07:00:00',
                'return_date' => '2026-09-03 17:30:00',
                'crew_count' => 14,
                'fuel_consumption_liters' => 850.00,
                'ice_consumption_kg' => 2000.00,
                'gear_code' => 'PS-01',
                'fishing_ground_name' => 'Perairan Pulo Aceh - Pulo Rondo',
                'fma_code' => '572',
                'validation_status' => 'validated',
                'notes' => 'Hasil dominan tongkol krai dan kembung lelaki. Operasi malam hari menggunakan rumpon.',
            ],
            // 3. KM Malaka Rayeuk - Trip Pukat Selat Malaka (WPPNRI 571) - Tervalidasi
            [
                'trip_number' => 'TRIP-202609-0003',
                'vessel_reg' => 'GT. 24 No. 518/Idi',
                'captain_nik' => '1103042209800007', // Pawang Junaidi
                'departure_site_code' => 'PPN-IDI',
                'landing_site_code' => 'PPN-IDI',
                'departure_date' => '2026-08-28 05:00:00',
                'return_date' => '2026-09-02 11:20:00',
                'crew_count' => 16,
                'fuel_consumption_liters' => 1400.00,
                'ice_consumption_kg' => 2800.00,
                'gear_code' => 'PS-01',
                'fishing_ground_name' => 'Perairan Selat Malaka Sektor Aceh Timur',
                'fma_code' => '571',
                'validation_status' => 'validated',
                'notes' => 'Target pelagis kecil dan tenggiri. Logbook tercatat lengkap.',
            ],
            // 4. KM Selat Malaka 02 - Jaring Insang Hanyut (WPPNRI 571) - Submitted (Menunggu Verifikasi)
            [
                'trip_number' => 'TRIP-202609-0004',
                'vessel_reg' => 'GT. 12 No. 312/Idi',
                'captain_nik' => '1103042209800007',
                'departure_site_code' => 'PPN-IDI',
                'landing_site_code' => 'PPN-IDI',
                'departure_date' => '2026-09-04 06:00:00',
                'return_date' => '2026-09-06 14:00:00',
                'crew_count' => 8,
                'fuel_consumption_liters' => 520.00,
                'ice_consumption_kg' => 1200.00,
                'gear_code' => 'GN-01', // Gillnet
                'fishing_ground_name' => 'Pesisir Kuala Idi hingga Batas Perairan Lhokseumawe',
                'fma_code' => '571',
                'validation_status' => 'submitted',
                'notes' => 'Data pendaratan diajukan oleh enumerator lapangan PPN Idi.',
            ],
            // 5. KM Meulaboh Bahari - Boat Tep-Tep Rawai Tuna (WPPNRI 572) - Tervalidasi
            [
                'trip_number' => 'TRIP-202609-0005',
                'vessel_reg' => 'GT. 7 No. 109/Mbo',
                'captain_nik' => '1105011706840010', // Anwar Daud
                'departure_site_code' => 'PPI-UJB',
                'landing_site_code' => 'PPI-UJB',
                'departure_date' => '2026-09-02 05:30:00',
                'return_date' => '2026-09-04 16:15:00',
                'crew_count' => 5,
                'fuel_consumption_liters' => 280.00,
                'ice_consumption_kg' => 600.00,
                'gear_code' => 'LL-01', // Longline
                'fishing_ground_name' => 'Perairan Samudera Hindia Lepas Pantai Meulaboh',
                'fma_code' => '572',
                'validation_status' => 'validated',
                'notes' => 'Tangkapan utama tuna madidihang dan kakap merah.',
            ],
            // 6. KM Pulo Breueh Mandiri - Pulo Aceh (WPPNRI 572) - Submitted
            [
                'trip_number' => 'TRIP-202609-0006',
                'vessel_reg' => 'GT. 9 No. 204/Jno',
                'captain_nik' => '1106211402830008', // Ibrahim Cut Adek
                'departure_site_code' => 'PPI-LPY',
                'landing_site_code' => 'PPI-LPY',
                'departure_date' => '2026-09-05 06:00:00',
                'return_date' => '2026-09-07 13:45:00',
                'crew_count' => 6,
                'fuel_consumption_liters' => 350.00,
                'ice_consumption_kg' => 800.00,
                'gear_code' => 'LL-01',
                'fishing_ground_name' => 'Gosong Karang Barat Pulo Nasi & Pulo Breueh',
                'fma_code' => '572',
                'validation_status' => 'submitted',
                'notes' => 'Tangkapan kerapu dan kakap batu.',
            ],
            // 7. PMT Ulee Lheue Samudera - Pancing Ulur Harian (One-Day Fishing) - Tervalidasi
            [
                'trip_number' => 'TRIP-202609-0007',
                'vessel_reg' => 'GT. 3 No. 044/Bda',
                'captain_nik' => '1171011003850004', // Zulkarnain
                'departure_site_code' => 'TPI-ULH',
                'landing_site_code' => 'TPI-ULH',
                'departure_date' => '2026-09-07 05:00:00',
                'return_date' => '2026-09-07 18:00:00',
                'crew_count' => 2,
                'fuel_consumption_liters' => 45.00,
                'ice_consumption_kg' => 100.00,
                'gear_code' => 'HL-01', // Handline
                'fishing_ground_name' => 'Perairan Ulee Lheue - Alue Naga',
                'fma_code' => '572',
                'validation_status' => 'validated',
                'notes' => 'Operasi pancing satu hari (one-day fishing). Tangkapan ikan kuwe dan kembung.',
            ],
            // 8. KM Sabang Bahari Indah - Pulau Weh (WPPNRI 572) - Draft
            [
                'trip_number' => 'TRIP-202609-0008',
                'vessel_reg' => 'GT. 6 No. 128/Sbg',
                'captain_nik' => '1172012501880011', // Rustam
                'departure_site_code' => 'PPI-SBG',
                'landing_site_code' => 'PPI-SBG',
                'departure_date' => '2026-09-07 07:00:00',
                'return_date' => '2026-09-08 12:00:00',
                'crew_count' => 4,
                'fuel_consumption_liters' => 120.00,
                'ice_consumption_kg' => 250.00,
                'gear_code' => 'HL-01',
                'fishing_ground_name' => 'Perairan Karang Barat Pulau Weh',
                'fma_code' => '572',
                'validation_status' => 'draft',
                'notes' => 'Data awal diinput nahkoda, belum diajukan ke enumerator.',
            ],
            // 9. KM Inka Mina 704 - Trip Baru yang Sedang Berlangsung di Laut (Ongoing)
            [
                'trip_number' => 'TRIP-202609-0009',
                'vessel_reg' => 'GT. 32 No. 814/Bda',
                'captain_nik' => '1171021508820002',
                'departure_site_code' => 'PPS-LMP',
                'landing_site_code' => 'PPS-LMP',
                'departure_date' => '2026-09-07 06:00:00',
                'return_date' => null, // Masih di laut!
                'crew_count' => 18,
                'fuel_consumption_liters' => 2000.00,
                'ice_consumption_kg' => 4000.00,
                'gear_code' => 'PS-01',
                'fishing_ground_name' => 'Perairan Samudera Hindia ZEEI Barat Sumatera',
                'fma_code' => '572',
                'validation_status' => 'draft',
                'notes' => 'Kapal sedang aktif melaut (estimasi kembali 12 September 2026).',
            ],
        ];

        foreach ($trips as $t) {
            $vessel = Vessel::where('registration_number', $t['vessel_reg'])->first();
            $captain = Fisherman::where('nik', $t['captain_nik'])->first();
            $depSite = LandingSite::where('code', $t['departure_site_code'])->first();
            $lanSite = ! empty($t['landing_site_code'])
                ? LandingSite::where('code', $t['landing_site_code'])->first()
                : null;
            $gear = FishingGear::where('code', $t['gear_code'])->first();

            if (! $vessel || ! $depSite) {
                continue;
            }

            FishingTrip::updateOrCreate(
                ['trip_number' => $t['trip_number']],
                [
                    'vessel_id' => $vessel->id,
                    'captain_id' => $captain?->id,
                    'departure_site_id' => $depSite->id,
                    'landing_site_id' => $lanSite?->id,
                    'departure_date' => $t['departure_date'],
                    'return_date' => $t['return_date'],
                    'crew_count' => $t['crew_count'],
                    'fuel_consumption_liters' => $t['fuel_consumption_liters'],
                    'ice_consumption_kg' => $t['ice_consumption_kg'],
                    'primary_gear_id' => $gear?->id,
                    'fishing_ground_name' => $t['fishing_ground_name'],
                    'fma_code' => $t['fma_code'],
                    'validation_status' => $t['validation_status'],
                    'submitted_by' => $admin?->id,
                    'submitted_at' => $t['validation_status'] !== 'draft' ? now()->subDays(1) : null,
                    'validated_by' => $t['validation_status'] === 'validated' ? $admin?->id : null,
                    'validated_at' => $t['validation_status'] === 'validated' ? now() : null,
                    'notes' => $t['notes'],
                ]
            );
        }
    }
}
