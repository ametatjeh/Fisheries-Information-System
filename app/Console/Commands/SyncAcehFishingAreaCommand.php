<?php

namespace App\Console\Commands;

use App\Models\FaoFishingArea;
use App\Models\Wppnri;
use Illuminate\Console\Command;

class SyncAcehFishingAreaCommand extends Command
{
    protected $signature = 'fishing-area:sync-aceh';

    protected $description = 'Sinkronisasi master data WPPNRI dan FAO Fishing Area untuk wilayah Aceh';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai sinkronisasi master data wilayah perikanan Aceh...');

        // 1. Sinkronisasi FAO Fishing Area
        $faoArea = FaoFishingArea::updateOrCreate(
            ['code' => '57'],
            [
                'name_en' => 'Indian Ocean, Eastern',
                'is_active' => true,
            ]
        );
        $this->info('FAO Fishing Area 57 berhasil disinkronisasi.');

        // 2. Sinkronisasi WPPNRI
        $wppnri571 = Wppnri::updateOrCreate(
            ['code' => '571'],
            [
                'fao_fishing_area_id' => $faoArea->id,
                'name' => 'WPPNRI 571',
                'description' => 'Perairan Selat Malaka dan Laut Andaman',
                'is_active' => true,
            ]
        );

        $wppnri572 = Wppnri::updateOrCreate(
            ['code' => '572'],
            [
                'fao_fishing_area_id' => $faoArea->id,
                'name' => 'WPPNRI 572',
                'description' => 'Perairan Samudera Hindia sebelah Barat Sumatera',
                'is_active' => true,
            ]
        );
        $this->info('WPPNRI 571 & 572 berhasil disinkronisasi.');

        $this->info('Sinkronisasi selesai.');
    }
}
