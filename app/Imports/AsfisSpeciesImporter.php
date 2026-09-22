<?php

namespace App\Imports;

use App\Models\Species;
use Illuminate\Support\Facades\DB;

class AsfisSpeciesImporter
{
    protected $output;

    protected $isDryRun = false;

    // Dry Run Metrics
    public $totalRecords = 0;

    public $validFaoCodes = 0;

    public $invalidFaoCodes = [];

    public $duplicateFaoCodesCount = 0;

    public $missingScientificCount = 0;

    public $missingTaxonomicCount = 0;

    public $fishStatYesCount = 0;

    public $fishStatNoCount = 0;

    public $sampleMappedRecords = [];

    protected $seenFaoCodes = [];

    public function setOutput($output)
    {
        $this->output = $output;
    }

    public function setDryRun($isDryRun)
    {
        $this->isDryRun = $isDryRun;
    }

    public function import($csvPath)
    {
        $result = [
            'total' => 0,
            'inserted' => 0,
            'updated' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        if (($handle = fopen($csvPath, 'r')) !== false) {

            // Read header
            $header = fgetcsv($handle, 4096, ',');
            if (! $header) {
                return $result;
            }

            // Clean header (remove BOM, trim, convert to lowercase)
            $header = array_map(function ($col) {
                $col = preg_replace('/[\x{FEFF}\x{EF}\x{BB}\x{BF}]/u', '', $col);
                // Also remove literal ? if present at start (sometimes BOM gets read as literal ? in simple contexts)
                $col = ltrim($col, '?');

                return trim(strtolower($col));
            }, $header);

            $batchSize = 500;
            $batchData = [];

            while (($row = fgetcsv($handle, 4096, ',')) !== false) {
                $result['total']++;
                $this->totalRecords++;

                // Map row to header
                $data = [];
                foreach ($header as $index => $colName) {
                    $data[$colName] = trim($row[$index] ?? '');
                }

                // Field Mapping based on ASFIS actual headers
                $faoCode = $data['alpha3_code'] ?? null;
                $taxonomicCode = $data['taxonomic_code'] ?? null;
                $isscaapCode = $data['isscaap_group'] ?? null;
                $scientificName = $data['scientific_name'] ?? null;
                $englishName = $data['english_name'] ?? null;
                $frenchName = $data['french_name'] ?? null;
                $spanishName = $data['spanish_name'] ?? null;
                $author = $data['author'] ?? null;
                $family = $data['family'] ?? null;
                $higherTaxa = $data['order or higher taxa'] ?? null;
                $fishStatRaw = strtoupper($data['fishstat_data'] ?? 'NO');

                // Validasi FAO Code (Alpha3_Code)
                $isValidFao = ! empty($faoCode) && strlen($faoCode) === 3 && ctype_upper($faoCode);

                if (empty($faoCode) || ! $isValidFao) {
                    $this->invalidFaoCodes[] = $faoCode ?: 'NULL/EMPTY';
                    $result['skipped']++;

                    continue;
                }

                // Check Duplicates in current file memory
                if (isset($this->seenFaoCodes[$faoCode])) {
                    $this->duplicateFaoCodesCount++;
                    // ASFIS unique key policy: we can just continue/skip or update. Typically skip for perfect duplication.
                    $result['skipped']++;

                    continue;
                }
                $this->seenFaoCodes[$faoCode] = true;
                $this->validFaoCodes++;

                // Track stats
                if (empty($scientificName)) {
                    $this->missingScientificCount++;
                }
                if (empty($taxonomicCode)) {
                    $this->missingTaxonomicCount++;
                }

                $isStatisticalItem = ($fishStatRaw === 'YES');
                if ($isStatisticalItem) {
                    $this->fishStatYesCount++;
                } else {
                    $this->fishStatNoCount++;
                }

                $mappedData = [
                    'fao_code' => $faoCode,
                    'taxonomic_code' => empty($taxonomicCode) ? null : $taxonomicCode,
                    'isscaap_code' => empty($isscaapCode) ? null : $isscaapCode,
                    'scientific_name' => empty($scientificName) ? null : $scientificName,
                    'english_name' => empty($englishName) ? null : $englishName,
                    'french_name' => empty($frenchName) ? null : $frenchName,
                    'spanish_name' => empty($spanishName) ? null : $spanishName,
                    'author' => empty($author) ? null : $author,
                    'family' => empty($family) ? null : $family,
                    'higher_taxa' => empty($higherTaxa) ? null : $higherTaxa,
                    'is_statistical_item' => $isStatisticalItem,
                    'taxon_level' => null, // Explicitly null as per rule 7
                    'functional_group' => null, // Explicitly null as per rule 8
                    'fao_version' => '2026.1',
                    'fao_source' => 'FAO ASFIS 2026.1',
                ];

                if ($this->isDryRun && count($this->sampleMappedRecords) < 10) {
                    $this->sampleMappedRecords[] = $mappedData;
                }

                $batchData[] = $mappedData;

                if (count($batchData) >= $batchSize) {
                    $this->processBatch($batchData, $result);
                    if ($this->output && ! $this->isDryRun) {
                        $this->output->writeln('Processed: '.$result['total']);
                    }
                    $batchData = []; // reset
                }
            }

            // Process remaining
            if (count($batchData) > 0) {
                $this->processBatch($batchData, $result);
                if ($this->output && ! $this->isDryRun) {
                    $this->output->writeln('Processed: '.$result['total']);
                }
            }

            fclose($handle);
        }

        return $result;
    }

    protected function processBatch($batchData, &$result)
    {
        if ($this->isDryRun) {
            // Dalam mode dry-run, cukup asumsikan seolah-olah sukses, tapi jangan DB transaction.
            $result['inserted'] += count($batchData);

            return;
        }

        DB::beginTransaction();
        try {
            foreach ($batchData as $data) {
                $existing = Species::where('fao_code', $data['fao_code'])->first();
                if ($existing) {
                    // Update only FAO fields (Local fields explicitly NOT updated)
                    $existing->forceFill($data)->save();
                    $result['updated']++;
                } else {
                    // Create new
                    $newSpecies = new Species;
                    $newSpecies->forceFill(array_merge($data, [
                        'is_active' => true,
                    ]))->save();
                    $result['inserted']++;
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $result['failed'] += count($batchData);
            if ($this->output) {
                $this->output->writeln('<error>Batch failed: '.$e->getMessage().'</error>');
            }
        }
    }
}
