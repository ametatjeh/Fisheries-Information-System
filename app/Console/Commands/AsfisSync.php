<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsfisSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asfis:sync
                            {file? : Path to ASFIS CSV file (default: storage/app/imports/asfis/ASFIS_sp_2026.1.csv)}
                            {--asfis-version=2026.1 : ASFIS version identifier}
                            {--dry-run : Preview import without writing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import or sync FAO ASFIS species reference data from official CSV into fao_asfis_species table';

    /**
     * ASFIS CSV column mapping to database columns.
     *
     * @var array<string, string>
     */
    private const COLUMN_MAP = [
        'ISSCAAP_Group' => 'isscaap_code',
        'Taxonomic_Code' => 'taxonomic_code',
        'Alpha3_Code' => 'fao_code',
        'Scientific_Name' => 'scientific_name',
        'English_name' => 'english_name',
        'French_name' => 'french_name',
        'Spanish_name' => 'spanish_name',
        'Author' => 'author',
        'Family' => 'family',
        'Order or higher taxa' => 'higher_taxa',
        'FishStat_Data' => 'is_statistical_item',
    ];

    public function handle(): int
    {
        $filePath = $this->argument('file')
            ?? storage_path('app/imports/asfis/ASFIS_sp_2026.1.csv');
        $version = $this->option('asfis-version');
        $dryRun = $this->option('dry-run');

        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return self::FAILURE;
        }

        $this->info("FAO ASFIS Sync — Version: {$version}");
        $this->info("Source: {$filePath}");

        if ($dryRun) {
            $this->warn('DRY RUN MODE — no database changes will be made.');
        }

        $rows = $this->parseCsv($filePath);
        $totalRows = count($rows);

        if ($totalRows === 0) {
            $this->error('No data rows found in CSV.');

            return self::FAILURE;
        }

        $this->info("Parsed {$totalRows} records from CSV.");

        $inserted = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        if (! $dryRun) {
            $importBatch = DB::table('reference_imports')->insertGetId([
                'reference_type' => 'ASFIS',
                'source' => 'FAO',
                'source_version' => $version,
                'imported_at' => now(),
                'total_records' => $totalRows,
                'status' => 'RUNNING',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("Created import batch #{$importBatch}");
        } else {
            $importBatch = null;
        }

        $bar = $this->output->createProgressBar($totalRows);
        $bar->start();

        foreach ($rows as $row) {
            try {
                $mapped = $this->mapRow($row, $version, $importBatch);

                if ($mapped === null) {
                    $skipped++;
                    $bar->advance();

                    continue;
                }

                if (! $dryRun) {
                    $existing = DB::table('fao_asfis_species')
                        ->where('fao_code', $mapped['fao_code'])
                        ->first();

                    if ($existing) {
                        DB::table('fao_asfis_species')
                            ->where('id', $existing->id)
                            ->update(array_merge($mapped, ['updated_at' => now()]));
                        $updated++;
                    } else {
                        DB::table('fao_asfis_species')->insert(
                            array_merge($mapped, [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ])
                        );
                        $inserted++;
                    }
                } else {
                    $inserted++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::warning('ASFIS sync error on row', [
                    'fao_code' => $row['Alpha3_Code'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if (! $dryRun && $importBatch) {
            DB::table('reference_imports')
                ->where('id', $importBatch)
                ->update([
                    'total_inserted' => $inserted,
                    'total_updated' => $updated,
                    'total_deactivated' => 0,
                    'status' => $errors > 0 ? 'PARTIAL' : 'SUCCESS',
                    'notes' => $errors > 0 ? "Completed with {$errors} errors, {$skipped} skipped." : null,
                    'updated_at' => now(),
                ]);
        }

        $this->info('Import complete.');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total CSV rows', $totalRows],
                ['Inserted', $inserted],
                ['Updated', $updated],
                ['Skipped (no fao_code)', $skipped],
                ['Errors', $errors],
            ]
        );

        if ($dryRun) {
            $this->warn('DRY RUN — nothing was written to the database.');
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Parse the ASFIS CSV file into an array of associative rows.
     *
     * @return array<int, array<string, string>>
     */
    private function parseCsv(string $filePath): array
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return [];
        }

        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);

            return [];
        }

        $headers = array_map(function (string $header): string {
            $header = trim($header);
            // Strip UTF-8 BOM if present on the first header
            $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

            return $header;
        }, $headers);
        $expectedCount = count($headers);
        $rows = [];

        while (($values = fgetcsv($handle)) !== false) {
            if (count($values) !== $expectedCount) {
                continue;
            }

            $rows[] = array_combine($headers, $values);
        }

        fclose($handle);

        return $rows;
    }

    /**
     * Map a CSV row to database column values.
     *
     * @param  array<string, string>  $row
     * @return array<string, mixed>|null
     */
    private function mapRow(array $row, string $version, ?int $importBatchId): ?array
    {
        $faoCode = trim($row['Alpha3_Code'] ?? '');

        if ($faoCode === '' || strlen($faoCode) !== 3) {
            return null;
        }

        $isscaapRaw = trim($row['ISSCAAP_Group'] ?? '');
        $isscaapCode = $isscaapRaw !== '' ? str_pad($isscaapRaw, 2, '0', STR_PAD_LEFT) : null;

        $fishStatData = strtoupper(trim($row['FishStat_Data'] ?? ''));
        $isStatisticalItem = $fishStatData === 'YES';

        return [
            'fao_code' => $faoCode,
            'taxonomic_code' => $this->nullIfEmpty($row['Taxonomic_Code'] ?? ''),
            'isscaap_code' => $isscaapCode,
            'scientific_name' => $this->nullIfEmpty($row['Scientific_Name'] ?? ''),
            'author' => $this->nullIfEmpty($row['Author'] ?? ''),
            'english_name' => $this->nullIfEmpty($row['English_name'] ?? ''),
            'french_name' => $this->nullIfEmpty($row['French_name'] ?? ''),
            'spanish_name' => $this->nullIfEmpty($row['Spanish_name'] ?? ''),
            'family' => $this->nullIfEmpty($row['Family'] ?? ''),
            'higher_taxa' => $this->nullIfEmpty($row['Order or higher taxa'] ?? ''),
            'taxon_level' => null,
            'is_statistical_item' => $isStatisticalItem,
            'fao_version' => $version,
            'fao_source' => 'FAO ASFIS',
            'import_batch_id' => $importBatchId,
            'is_active' => true,
        ];
    }

    private function nullIfEmpty(string $value): ?string
    {
        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
