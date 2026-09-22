<?php

namespace App\Console\Commands;

use App\Imports\AsfisSpeciesImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

class ImportAsfisCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asfis:import {--file= : Path to ASFIS ZIP or CSV file} {--dry-run : Perform a dry run without modifying the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import or update FAO ASFIS 2026.1 master species data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info('Starting ASFIS DRY RUN...');
        } else {
            $this->info('Importing ASFIS...');
        }

        $filePath = $this->option('file');

        if (! $filePath) {
            $defaultZipPath = storage_path('app/imports/ASFIS_sp_2026.1.zip');
            if (File::exists($defaultZipPath)) {
                $filePath = $defaultZipPath;
            } else {
                $this->error('File ASFIS tidak ditemukan.');
                $this->line('Silakan taruh file ASFIS 2026.1 di: '.storage_path('app/imports/ASFIS_sp_2026.1.zip'));
                $this->line('Atau gunakan flag --file=/path/to/ASFIS.zip');

                return 1;
            }
        }

        if (! File::exists($filePath)) {
            $this->error("File tidak ditemukan di path: {$filePath}");

            return 1;
        }

        $csvPath = $filePath;

        $isZipExtracted = false;
        $extractTo = storage_path('app/imports/extracted_asfis');

        // Handle ZIP extraction
        if (str_ends_with(strtolower($filePath), '.zip')) {
            $this->info('Extracting ZIP file...');
            $zip = new ZipArchive;
            if ($zip->open($filePath) === true) {
                if (! File::isDirectory($extractTo)) {
                    File::makeDirectory($extractTo, 0755, true);
                }

                $csvFilename = null;
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entryName = $zip->getNameIndex($i);
                    // Anti-Zip Slip protection: reject path traversal and absolute paths
                    if (str_contains($entryName, '..') || str_starts_with($entryName, '/') || str_starts_with($entryName, '\\')) {
                        continue;
                    }
                    // Prioritizing CSV
                    if (str_ends_with(strtolower($entryName), '.csv')) {
                        $csvFilename = $entryName;
                        break;
                    } elseif (str_ends_with(strtolower($entryName), '.txt')) {
                        $csvFilename = $entryName;
                    }
                }

                if ($csvFilename) {
                    $zip->extractTo($extractTo, $csvFilename);
                    $zip->close();
                    $csvPath = $extractTo.DIRECTORY_SEPARATOR.basename($csvFilename);
                    $isZipExtracted = true;
                    $this->info("Extracted: {$csvFilename}");
                } else {
                    $zip->close();
                    $this->error('Tidak menemukan file .csv atau .txt di dalam arsip ZIP.');

                    return 1;
                }
            } else {
                $this->error('Gagal membuka file ZIP.');

                return 1;
            }
        }

        try {
            $this->info("Memulai parsing file: {$csvPath}");

            $importer = new AsfisSpeciesImporter;
            $importer->setOutput($this->output);
            $importer->setDryRun($isDryRun);
            $result = $importer->import($csvPath);

            $this->newLine();
            if ($isDryRun) {
                $this->info('ASFIS DRY RUN COMPLETED.');
                $this->line('--- DRY RUN STATISTICS ---');
                $this->line('Total Records Read: '.$importer->totalRecords);
                $this->line('Valid FAO Codes: '.$importer->validFaoCodes);
                $this->line('Invalid FAO Codes: '.count($importer->invalidFaoCodes));
                $this->line('Duplicate FAO Codes: '.$importer->duplicateFaoCodesCount);
                $this->line('Missing Scientific Names: '.$importer->missingScientificCount);
                $this->line('Missing Taxonomic Codes: '.$importer->missingTaxonomicCount);
                $this->line('FishStat YES: '.$importer->fishStatYesCount);
                $this->line('FishStat NO: '.$importer->fishStatNoCount);

                $this->newLine();
                $this->info('SAMPLE MAPPED RECORDS (First 10):');
                foreach ($importer->sampleMappedRecords as $index => $record) {
                    $this->line('Record #'.($index + 1));
                    foreach ($record as $key => $val) {
                        // Stringify booleans
                        $displayVal = $val;
                        if (is_bool($val)) {
                            $displayVal = $val ? 'true' : 'false';
                        }
                        if (is_null($val)) {
                            $displayVal = 'NULL';
                        }
                        $this->line("  {$key}: {$displayVal}");
                    }
                    $this->line('---------------------------------');
                }
            } else {
                $this->info('ASFIS import completed.');
                $this->newLine();
                $this->line("Total: {$result['total']}");
                $this->info("Inserted: {$result['inserted']}");
                $this->info("Updated: {$result['updated']}");
                $this->warn("Skipped: {$result['skipped']}");
                if ($result['failed'] > 0) {
                    $this->error("Failed: {$result['failed']}");
                } else {
                    $this->line("Failed: {$result['failed']}");
                }
            }

            return 0;
        } finally {
            if ($isZipExtracted && File::isDirectory($extractTo)) {
                $this->info("Membersihkan temporary files di: {$extractTo}...");
                File::deleteDirectory($extractTo);
                $this->info('Cleanup selesai.');
            }
        }
    }
}
