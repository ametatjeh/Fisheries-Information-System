<?php

namespace App\Console\Commands;

use App\Models\FaoIsscaapGroup;
use App\Models\ReferenceImport;
use Illuminate\Console\Command;

class SyncIsscaapCommand extends Command
{
    protected $signature = 'isscaap:sync {--dry-run : Lakukan simulasi tanpa menyimpan ke database}';

    protected $description = 'Sinkronisasi master data FAO ISSCAAP Groups';

    private $isscaapGroups = [
        ['code' => '11', 'name' => 'Carps, barbels and other cyprinids'],
        ['code' => '12', 'name' => 'Tilapias and other cichlids'],
        ['code' => '13', 'name' => 'Miscellaneous freshwater fishes'],
        ['code' => '21', 'name' => 'Sturgeons, paddlefishes'],
        ['code' => '22', 'name' => 'River eels'],
        ['code' => '23', 'name' => 'Salmons, trouts, smelts'],
        ['code' => '24', 'name' => 'Shads'],
        ['code' => '25', 'name' => 'Miscellaneous diadromous fishes'],
        ['code' => '31', 'name' => 'Flounders, halibuts, soles'],
        ['code' => '32', 'name' => 'Cods, hakes, haddocks'],
        ['code' => '33', 'name' => 'Miscellaneous coastal fishes'],
        ['code' => '34', 'name' => 'Miscellaneous demersal fishes'],
        ['code' => '35', 'name' => 'Herrings, sardines, anchovies'],
        ['code' => '36', 'name' => 'Tunas, bonitos, billfishes'],
        ['code' => '37', 'name' => 'Miscellaneous pelagic fishes'],
        ['code' => '38', 'name' => 'Sharks, rays, chimaeras'],
        ['code' => '39', 'name' => 'Marine fishes not identified'],
        ['code' => '41', 'name' => 'Freshwater crustaceans'],
        ['code' => '42', 'name' => 'Crabs, sea-spiders'],
        ['code' => '43', 'name' => 'Lobsters, spiny-rock lobsters'],
        ['code' => '44', 'name' => 'Squat-lobsters'],
        ['code' => '45', 'name' => 'Shrimps, prawns'],
        ['code' => '46', 'name' => 'Krill, planktonic crustaceans'],
        ['code' => '47', 'name' => 'Miscellaneous marine crustaceans'],
        ['code' => '51', 'name' => 'Freshwater molluscs'],
        ['code' => '52', 'name' => 'Abalones, winkles, conchs'],
        ['code' => '53', 'name' => 'Oysters'],
        ['code' => '54', 'name' => 'Mussels'],
        ['code' => '55', 'name' => 'Scallops, pectens'],
        ['code' => '56', 'name' => 'Clams, cockles, arkshells'],
        ['code' => '57', 'name' => 'Squids, cuttlefishes, octopuses'],
        ['code' => '58', 'name' => 'Miscellaneous marine molluscs'],
        ['code' => '61', 'name' => 'Sea-squirts and other tunicates'],
        ['code' => '62', 'name' => 'Horseshoe crabs and other arachnoids'],
        ['code' => '63', 'name' => 'Sea-urchins and other echinoderms'],
        ['code' => '64', 'name' => 'Miscellaneous aquatic invertebrates'],
        ['code' => '71', 'name' => 'Frogs and other amphibians'],
        ['code' => '72', 'name' => 'Turtles'],
        ['code' => '73', 'name' => 'Crocodiles and alligators'],
        ['code' => '74', 'name' => 'Sea-squirts and other tunicates'],
        ['code' => '75', 'name' => 'Seals, walruses, etc.'],
        ['code' => '76', 'name' => 'Miscellaneous aquatic mammals'],
        ['code' => '77', 'name' => 'Miscellaneous aquatic animals'],
        ['code' => '81', 'name' => 'Pearls, mother-of-pearl, shells'],
        ['code' => '82', 'name' => 'Corals, sponges'],
        ['code' => '83', 'name' => 'Miscellaneous aquatic animal products'],
        ['code' => '91', 'name' => 'Brown seaweeds'],
        ['code' => '92', 'name' => 'Red seaweeds'],
        ['code' => '93', 'name' => 'Green seaweeds'],
        ['code' => '94', 'name' => 'Miscellaneous aquatic plants'],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('Memulai sinkronisasi FAO ISSCAAP Groups...');
        if ($dryRun) {
            $this->warn('== DRY RUN MODE AKTIF == Tidak ada data yang akan disimpan ke database.');
        }

        $inserted = 0;
        $updated = 0;

        if (! $dryRun) {
            $importBatch = ReferenceImport::create([
                'reference_type' => 'FAO_ISSCAAP',
                'source' => 'FAO/CWP Standard List',
                'source_version' => 'Current',
                'imported_at' => now(),
                'total_records' => count($this->isscaapGroups),
                'status' => 'success',
            ]);
        }

        foreach ($this->isscaapGroups as $group) {
            if ($dryRun) {
                // Hanya simulasi
                $existing = FaoIsscaapGroup::where('isscaap_code', $group['code'])->first();
                if ($existing) {
                    $updated++;
                } else {
                    $inserted++;
                }

                continue;
            }

            $model = FaoIsscaapGroup::updateOrCreate(
                ['isscaap_code' => $group['code']],
                [
                    'name_en' => $group['name'],
                    'fao_version' => 'Current',
                    'import_batch_id' => $importBatch->id,
                    'is_active' => true,
                ]
            );

            if ($model->wasRecentlyCreated) {
                $inserted++;
            } else {
                $updated++;
            }
        }

        if (! $dryRun) {
            $importBatch->update([
                'total_inserted' => $inserted,
                'total_updated' => $updated,
            ]);
        }

        $this->info('Sinkronisasi selesai.');
        $this->table(['Total Target', 'Inserted', 'Updated'], [[count($this->isscaapGroups), $inserted, $updated]]);
    }
}
