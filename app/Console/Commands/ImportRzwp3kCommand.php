<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\Rzwp3kZoneController;
use App\Models\Regency;
use App\Models\Rzwp3kZone;
use App\Services\Rzwp3k\Rzwp3kGeoJsonValidator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportRzwp3kCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rzwp3k:import
                            {--file= : Path berkas GeoJSON spasial RZWP3K Aceh}
                            {--dry-run : Jalankan simulasi validasi tanpa mengubah basis data}
                            {--update : Perbarui data zona jika kode sudah ada}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import dan validasi data spasial GeoJSON zonasi RZWP3K Aceh (Qanun 1/2020) secara aman';

    public function __construct(protected Rzwp3kGeoJsonValidator $validator)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $filePath = $this->option('file');
        $isDryRun = (bool) $this->option('dry-run');
        $isUpdate = (bool) $this->option('update');

        $this->info('===============================================================');
        $this->info(' IMPORTER DATA SPASIAL RZWP3K ACEH (QANUN NO. 1 TAHUN 2020)');
        $this->info('===============================================================');

        if ($isDryRun) {
            $this->warn(' [MODE DRY-RUN AKTIF]: Basis data tidak akan diubah.');
        }

        if (! $filePath) {
            $this->error(' Parameter --file wajib diisi. Contoh: php artisan rzwp3k:import --file=/path/to/rzwp3k_aceh.geojson');

            return Command::FAILURE;
        }

        if (! file_exists($filePath) || ! is_readable($filePath)) {
            $this->error(" Berkas GeoJSON tidak ditemukan atau tidak dapat dibaca: {$filePath}");

            return Command::FAILURE;
        }

        $this->line(" Membaca berkas: {$filePath} (".number_format(filesize($filePath) / 1024, 2).' KB)');
        $content = file_get_contents($filePath);

        $this->line(' Memvalidasi struktur GeoJSON RFC 7946 WGS84...');
        $validationResult = $this->validator->validate($content);

        $this->table(
            ['Metrik Validasi', 'Jumlah'],
            [
                ['Total Feature Terdeteksi', $validationResult['feature_count']],
                ['Feature Valid (Polygon/MultiPolygon)', $validationResult['valid_count']],
                ['Feature Tidak Valid', $validationResult['invalid_count']],
            ]
        );

        if ($validationResult['invalid_count'] > 0) {
            $this->warn(' Ditemukan kesalahan geometri pada berkas GeoJSON:');
            foreach (array_slice($validationResult['errors'], 0, 10) as $err) {
                $this->line("   - <fg=red>{$err}</>");
            }
            if (count($validationResult['errors']) > 10) {
                $this->line('   - ... dan '.(count($validationResult['errors']) - 10).' error lainnya.');
            }
        }

        if ($validationResult['valid_count'] === 0) {
            $this->error(' Tidak ada feature geometri valid yang dapat diproses.');

            return Command::FAILURE;
        }

        // Evaluasi features untuk impor
        $regencies = Regency::pluck('id', 'name')->all();
        $processedCodes = [];
        $duplicateCodes = 0;
        $toInsert = 0;
        $toUpdate = 0;
        $skipped = 0;

        $preparedRecords = [];

        foreach ($validationResult['valid_features'] as $feature) {
            $props = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            $code = $props['code'] ?? ($props['KODE_ZONA'] ?? ($props['kode'] ?? null));
            $name = $props['name'] ?? ($props['NAMA_ZONA'] ?? ($props['nama'] ?? 'Zona RZWP3K Aceh'));
            $zoneType = $props['zone_type'] ?? ($props['KAWASAN'] ?? ($props['zona'] ?? 'KPU'));
            $subzoneType = $props['subzone_type'] ?? ($props['SUB_ZONA'] ?? ($props['subzona'] ?? 'KPU-PT'));
            $description = $props['description'] ?? ($props['DESKRIPSI'] ?? ($props['keterangan'] ?? null));
            $areaHa = isset($props['area_ha']) ? (float) $props['area_ha'] : (isset($props['LUAS_HA']) ? (float) $props['LUAS_HA'] : null);
            $regencyName = $props['regency'] ?? ($props['KABUPATEN'] ?? ($props['kab_kota'] ?? null));
            $regencyId = $regencyName ? ($regencies[$regencyName] ?? null) : null;

            if (! $code) {
                $skipped++;

                continue;
            }

            if (isset($processedCodes[$code])) {
                $duplicateCodes++;

                continue;
            }
            $processedCodes[$code] = true;

            $existing = Rzwp3kZone::where('code', $code)->first();
            if ($existing) {
                if ($isUpdate) {
                    $toUpdate++;
                } else {
                    $skipped++;
                }
            } else {
                $toInsert++;
            }

            $preparedRecords[] = [
                'code' => $code,
                'parent_code' => $props['parent_code'] ?? substr($code, 0, strrpos($code, '-') ?: strlen($code)),
                'name' => $name,
                'zone_type' => in_array($zoneType, ['KPU', 'KK', 'KSNT', 'AL']) ? $zoneType : 'KPU',
                'subzone_type' => $subzoneType,
                'description' => $description,
                'regency_id' => $regencyId,
                'area_ha' => $areaHa,
                'source' => $props['source'] ?? 'DKP Aceh / Bappeda Aceh',
                'source_document' => $props['source_document'] ?? 'Qanun Aceh No. 1 Tahun 2020',
                'legal_basis' => $props['legal_basis'] ?? 'Qanun Aceh Nomor 1 Tahun 2020 tentang RZWP3K Aceh 2020-2040',
                'valid_from' => $props['valid_from'] ?? '2020-01-13',
                'valid_until' => $props['valid_until'] ?? '2040-01-13',
                'status' => $props['status'] ?? 'legal_active',
                'metadata' => $props['metadata'] ?? $props,
                'geometry' => $geometry,
            ];
        }

        $this->table(
            ['Rencana Aksi Basis Data', 'Jumlah'],
            [
                ['Zona Baru (Akan Di-insert)', $toInsert],
                ['Zona Existing (Akan Di-update)', $toUpdate],
                ['Kode Duplikat dalam Berkas (Dilewati)', $duplicateCodes],
                ['Zona Dilewati (Tanpa Kode/Skip)', $skipped],
            ]
        );

        if ($isDryRun) {
            $this->info(' [SUKSES SIMULASI]: Validasi dry-run selesai. Tidak ada mutasi basis data.');

            return Command::SUCCESS;
        }

        // Eksekusi Impor Sebenarnya dalam Transaksi Basis Data
        $this->line(' Menyimpan data ke tabel rzwp3k_zones dalam database transaction...');

        try {
            DB::transaction(function () use ($preparedRecords, $isUpdate) {
                foreach ($preparedRecords as $record) {
                    if ($isUpdate) {
                        Rzwp3kZone::updateOrCreate(
                            ['code' => $record['code']],
                            $record
                        );
                    } else {
                        Rzwp3kZone::firstOrCreate(
                            ['code' => $record['code']],
                            $record
                        );
                    }
                }
            });

            // Invalidate API GeoJSON cache
            Rzwp3kZoneController::clearCache();

            $this->info(' [SELESAI]: Impor data spasial RZWP3K Aceh berhasil disimpan secara aman.');

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error(' [GAGAL]: Terjadi kesalahan saat transaksi basis data: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
