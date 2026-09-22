<?php

namespace Database\Seeders;

use App\Models\Fisherman;
use App\Models\FishingGear;
use App\Models\LandingSite;
use App\Models\Vessel;
use App\Models\VesselType;
use Illuminate\Database\Seeder;

class VesselSeeder extends Seeder
{
    /**
     * Seed master data kapal penangkap ikan (Vessels).
     * Idempoten: aman dijalankan berulang kali.
     */
    public function run(): void
    {
        $vessels = [
            // 1. KM Inka Mina 704 - Kapal Samudera Purse Seine (PPS Lampulo)
            [
                'name' => 'KM. Inka Mina 704',
                'registration_number' => 'GT. 32 No. 814/Bda',
                'owner_nik' => '1171021204780001', // Syamsuddin
                'type_code' => 'KM-05',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 32.50,
                'length' => 19.80,
                'width' => 4.60,
                'depth' => 2.10,
                'engine_power_hp' => 280.00,
                'engine_brand' => 'Mitsubishi 8DC9',
                'build_year' => 2018,
                'homeport_code' => 'PPS-LMP',
                'gear_code' => 'PS-01', // Purse Seine
                'is_active' => true,
            ],
            // 2. KM Lampulo Jaya - Kapal Pukat Cincin (PPS Lampulo)
            [
                'name' => 'KM. Lampulo Jaya Mandiri',
                'registration_number' => 'GT. 18 No. 422/Bda',
                'owner_nik' => '1171021508820002', // Pawang Bukhari
                'type_code' => 'KM-03',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 18.00,
                'length' => 15.50,
                'width' => 3.80,
                'depth' => 1.70,
                'engine_power_hp' => 160.00,
                'engine_brand' => 'Yanmar 6CX-GTYE',
                'build_year' => 2019,
                'homeport_code' => 'PPS-LMP',
                'gear_code' => 'PS-01',
                'is_active' => true,
            ],
            // 3. KM Malaka Rayeuk - Pukat Cincin Selat Malaka (PPN Idi Rayeuk)
            [
                'name' => 'KM. Malaka Rayeuk',
                'registration_number' => 'GT. 24 No. 518/Idi',
                'owner_nik' => '1103041805720006', // Marzuki
                'type_code' => 'KM-03',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 24.00,
                'length' => 17.20,
                'width' => 4.10,
                'depth' => 1.85,
                'engine_power_hp' => 210.00,
                'engine_brand' => 'Nissan Diesel FE6',
                'build_year' => 2017,
                'homeport_code' => 'PPN-IDI',
                'gear_code' => 'PS-01',
                'is_active' => true,
            ],
            // 4. KM Selat Malaka 02 - Gillnet (PPN Idi Rayeuk)
            [
                'name' => 'KM. Selat Malaka 02',
                'registration_number' => 'GT. 12 No. 312/Idi',
                'owner_nik' => '1103041805720006', // Marzuki
                'type_code' => 'KM-03',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 12.00,
                'length' => 13.40,
                'width' => 3.20,
                'depth' => 1.40,
                'engine_power_hp' => 115.00,
                'engine_brand' => 'Yanmar 4CHE',
                'build_year' => 2020,
                'homeport_code' => 'PPN-IDI',
                'gear_code' => 'GN-01', // Gillnet
                'is_active' => true,
            ],
            // 5. Boat Tep-Tep Meulaboh - Boat Sedang Khas Aceh (PPI Ujong Baroh)
            [
                'name' => 'KM. Meulaboh Bahari (Boat Tep-Tep)',
                'registration_number' => 'GT. 7 No. 109/Mbo',
                'owner_nik' => '1105011706840010', // Anwar Daud
                'type_code' => 'KM-02',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 7.50,
                'length' => 11.20,
                'width' => 2.60,
                'depth' => 1.20,
                'engine_power_hp' => 68.00,
                'engine_brand' => 'Dongfeng 60 PK Inboard',
                'build_year' => 2021,
                'homeport_code' => 'PPI-UJB',
                'gear_code' => 'LL-01', // Longline
                'is_active' => true,
            ],
            // 6. KM Pulo Breueh - Rawai Dasar Pulo Aceh (PPI Lampuyang)
            [
                'name' => 'KM. Pulo Breueh Mandiri',
                'registration_number' => 'GT. 9 No. 204/Jno',
                'owner_nik' => '1106211402830008', // Ibrahim Cut Adek
                'type_code' => 'KM-02',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 8.80,
                'length' => 12.10,
                'width' => 2.80,
                'depth' => 1.30,
                'engine_power_hp' => 85.00,
                'engine_brand' => 'Mitsubishi 4D34',
                'build_year' => 2019,
                'homeport_code' => 'PPI-LPY',
                'gear_code' => 'LL-01',
                'is_active' => true,
            ],
            // 7. Boat Pancing Ulee Lheue - Perahu Motor Tempel (TPI Ulee Lheue)
            [
                'name' => 'PMT. Ulee Lheue Samudera',
                'registration_number' => 'GT. 3 No. 044/Bda',
                'owner_nik' => '1171011003850004', // Zulkarnain
                'type_code' => 'PMT-01',
                'vessel_type' => 'motor_tempel',
                'gross_tonnage' => 2.80,
                'length' => 8.40,
                'width' => 1.60,
                'depth' => 0.80,
                'engine_power_hp' => 40.00,
                'engine_brand' => 'Yamaha 40 PK 2-Tak (Double)',
                'build_year' => 2022,
                'homeport_code' => 'TPI-ULH',
                'gear_code' => 'HL-01', // Handline
                'is_active' => true,
            ],
            // 8. KM Sabang Bahari - Pancing Tonda Pulau Weh (PPI Sabang)
            [
                'name' => 'KM. Sabang Bahari Indah',
                'registration_number' => 'GT. 6 No. 128/Sbg',
                'owner_nik' => '1172012501880011', // Rustam
                'type_code' => 'KM-02',
                'vessel_type' => 'kapal_motor',
                'gross_tonnage' => 5.60,
                'length' => 10.50,
                'width' => 2.40,
                'depth' => 1.10,
                'engine_power_hp' => 55.00,
                'engine_brand' => 'Yanmar TS 230 Inboard',
                'build_year' => 2021,
                'homeport_code' => 'PPI-SBG',
                'gear_code' => 'HL-01',
                'is_active' => true,
            ],
            // 9. Jukung / Sampan Pesisir Lhoknga - Perahu Tanpa Motor (TPI Lhoknga)
            [
                'name' => 'Jukung Nelayan Pesisir Lhoknga',
                'registration_number' => 'REG-JUK-1106-001',
                'owner_nik' => '1106211402830008', // Ibrahim
                'type_code' => 'PTM-01',
                'vessel_type' => 'tanpa_motor',
                'gross_tonnage' => 0.00,
                'length' => 5.20,
                'width' => 1.10,
                'depth' => 0.50,
                'engine_power_hp' => null,
                'engine_brand' => null,
                'build_year' => 2020,
                'homeport_code' => 'TPI-LKN',
                'gear_code' => 'BS-01', // Pukat Pantai
                'is_active' => true,
            ],
        ];

        foreach ($vessels as $v) {
            $owner = Fisherman::where('nik', $v['owner_nik'])->first();
            $homeport = LandingSite::where('code', $v['homeport_code'])->first();
            $gear = FishingGear::where('code', $v['gear_code'])->first();
            $vType = VesselType::where('code', $v['type_code'])->first();

            Vessel::updateOrCreate(
                ['registration_number' => $v['registration_number']],
                [
                    'name' => $v['name'],
                    'owner_id' => $owner?->id,
                    'vessel_type' => $v['vessel_type'],
                    'vessel_type_id' => $vType?->id,
                    'gross_tonnage' => $v['gross_tonnage'],
                    'length' => $v['length'],
                    'width' => $v['width'],
                    'depth' => $v['depth'],
                    'engine_power_hp' => $v['engine_power_hp'],
                    'engine_brand' => $v['engine_brand'],
                    'build_year' => $v['build_year'],
                    'homeport_site_id' => $homeport?->id,
                    'primary_gear_id' => $gear?->id,
                    'is_active' => $v['is_active'],
                ]
            );
        }
    }
}
