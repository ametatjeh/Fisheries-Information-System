<?php

namespace App\Console\Commands;

use App\Services\Rzwp3k\Rzwp3kSourceHealthCheck;
use Illuminate\Console\Command;

class AuditRzwp3kSourceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rzwp3k:source-audit {--ping : Execute live HTTP check for sources with configured endpoints}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit registered spatial data sources and health status for RZWP3K Aceh';

    /**
     * Execute the console command.
     */
    public function handle(Rzwp3kSourceHealthCheck $healthCheck): int
    {
        $this->info('================================================================');
        $this->info('              RZWP3K ACEH SPATIAL SOURCE AUDIT                  ');
        $this->info('================================================================');
        $this->line('Legal Basis : Qanun Aceh Nomor 1 Tahun 2020');
        $this->line('Target CRS  : '.config('rzwp3k.target_crs', 'EPSG:4326').' (WGS84 [longitude, latitude])');
        $this->line('Policy      : Zero Fake Polygon / Strict Provenance Isolation');
        $this->newLine();

        $sources = config('rzwp3k.sources', []);

        if (empty($sources)) {
            $this->warn('No sources configured in config/rzwp3k.php');

            return Command::SUCCESS;
        }

        $tableRows = [];

        foreach ($sources as $key => $config) {
            $url = $config['dataset_url'] ?? $config['service_url'] ?? null;
            $isVerified = ! empty($config['verified']);
            $statusLabel = $isVerified ? 'VERIFIED' : 'UNVERIFIED';

            if ($this->option('ping') && ! empty($url)) {
                $check = $healthCheck->checkSource($key, $config);
                $statusLabel = $check['status_label'];
            } elseif (empty($url)) {
                $statusLabel = 'NO_ENDPOINT';
            }

            $tableRows[] = [
                $key,
                $config['name'] ?? '-',
                $config['authority'] ?? '-',
                $config['source_type'] ?? 'unknown',
                $config['crs'] ?? 'Unknown',
                $isVerified ? 'YES' : 'NO',
                $statusLabel,
                $url ?: '(none)',
            ];
        }

        $this->table(
            ['Source Key', 'Name', 'Authority', 'Type', 'CRS', 'Verified', 'Status', 'URL / Endpoint'],
            $tableRows
        );

        $this->newLine();
        $this->comment('Safety Note: This audit command is strictly read-only and does not modify database records.');
        $this->comment('If official machine-readable vector files are unattached, zone geometries safely remain NULL.');

        return Command::SUCCESS;
    }
}
