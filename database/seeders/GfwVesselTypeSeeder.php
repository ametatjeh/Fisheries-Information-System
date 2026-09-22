<?php

namespace Database\Seeders;

use App\Models\Gfw\GfwVesselType;
use Illuminate\Database\Seeder;

class GfwVesselTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            // Top-level categories
            [
                'code' => 'fishing',
                'name' => 'Kapal Penangkap Ikan (Fishing Vessel)',
                'parent_type' => null,
                'description' => 'Kapal yang digunakan untuk operasi penangkapan sumber daya ikan.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'cargo',
                'name' => 'Kapal Kargo (Cargo Vessel)',
                'parent_type' => null,
                'description' => 'Kapal pengangkut barang komersial termasuk peti kemas dan curah.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'tanker',
                'name' => 'Kapal Tanker (Tanker Vessel)',
                'parent_type' => null,
                'description' => 'Kapal pengangkut cairan atau gas curah (minyak, kimia, LNG).',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'passenger',
                'name' => 'Kapal Penumpang (Passenger Vessel)',
                'parent_type' => null,
                'description' => 'Kapal feri penyeberangan atau kapal pesiar komersial.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'tug',
                'name' => 'Kapal Tunda (Tugboat)',
                'parent_type' => null,
                'description' => 'Kapal pemandu dan penarik untuk manuver kapal di pelabuhan atau laut lepas.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'service',
                'name' => 'Kapal Layanan (Service Vessel)',
                'parent_type' => null,
                'description' => 'Kapal riset, suplai lepas pantai, pengeruk, atau kapal patroli kepelautan.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'carrier',
                'name' => 'Kapal Pengangkut Hasil Laut (Carrier Vessel)',
                'parent_type' => null,
                'description' => 'Kapal berpendingin (reefer) pengangkut dan penampung hasil tangkapan di laut.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'other',
                'name' => 'Lainnya (Other)',
                'parent_type' => null,
                'description' => 'Kapal yang tidak tergolong ke dalam kategori utama standar.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'unknown',
                'name' => 'Tidak Teridentifikasi (Unknown)',
                'parent_type' => null,
                'description' => 'Kapal dengan transmisi AIS/registri yang belum terklasifikasi.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],

            // Fishing sub-types / gear classifications recognized by GFW
            [
                'code' => 'trawler',
                'name' => 'Pukat Hela (Trawler)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap ikan dengan alat tangkap pukat hela (bottom/pelagic trawl).',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'longliner',
                'name' => 'Rawai (Longliner)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap ikan dengan alat tangkap rawai hanyut atau rawai dasar.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'purse_seine',
                'name' => 'Pukat Cincin (Purse Seine)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap ikan pelagis dengan jaring lingkar bertali kerut.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'pole_and_line',
                'name' => 'Huhate (Pole and Line)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap cakalang/tuna dengan metode huhate dan umpan hidup.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'pot_and_trap',
                'name' => 'Perangkap / Bubu (Pot and Trap)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap kepiting, lobster, atau ikan demersal menggunakan bubu/perangkap.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
            [
                'code' => 'other_fishing',
                'name' => 'Perikanan Lainnya (Other Fishing)',
                'parent_type' => 'fishing',
                'description' => 'Kapal penangkap ikan dengan metode operasional penangkapan lainnya.',
                'source' => 'GFW',
                'source_version' => 'v3',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            GfwVesselType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
