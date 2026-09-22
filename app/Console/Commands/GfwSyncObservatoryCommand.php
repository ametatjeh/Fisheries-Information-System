<?php

namespace App\Console\Commands;

use App\Services\Gfw\GfwIngestionService;
use Illuminate\Console\Command;

class GfwSyncObservatoryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gfw:sync-observatory
                            {--dry-run : Execute in dry-run mode without modifying the database}
                            {--limit=5 : Maximum number of vessels to process (max: 10)}
                            {--days=7 : Number of days in the observation window (max: 7)}
                            {--query=MEULABOH : Search query for vessel identity}
                            {--aoi=zee-indonesia-aceh : Area of Interest code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform controlled GFW vessel and presence ingestion into isolated sistem_gfw database';

    /**
     * Execute the console command.
     */
    public function handle(GfwIngestionService $ingestionService): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = max(1, min((int) $this->option('limit'), 10));
        $days = max(1, min((int) $this->option('days'), 7));
        $query = (string) $this->option('query');
        $aoi = (string) $this->option('aoi');

        $this->info('====================================================');
        $this->info(' GFW VESSEL OBSERVATORY — CONTROLLED INGESTION');
        $this->info('====================================================');
        $this->line('Mode      : '.($dryRun ? '<fg=yellow;options=bold>DRY-RUN (NO WRITE)</>' : '<fg=green;options=bold>LIVE CONTROLLED SYNC</>'));
        $this->line('Target DB : <fg=cyan>sistem_gfw</>');
        $this->line("Query     : {$query}");
        $this->line("AOI       : {$aoi}");
        $this->line("Window    : {$days} days");
        $this->line("Limit     : {$limit} vessels");
        $this->newLine();

        $result = $ingestionService->ingest([
            'query' => $query,
            'limit' => $limit,
            'days' => $days,
            'aoi' => $aoi,
        ], $dryRun);

        $this->table(['Metric', 'Count / Value'], [
            ['Dry Run Mode', $result['dry_run'] ? 'YES' : 'NO'],
            ['AOI', $result['aoi']],
            ['Query Start Date', $result['query_period']['start_date']],
            ['Query End Date', $result['query_period']['end_date']],
            ['Vessels Found', $result['vessels_found']],
            ['New Vessels', $result['new_vessels']],
            ['Vessels Updated', $result['vessels_updated']],
            ['Vessels Skipped', $result['vessels_skipped']],
            ['Presence Points Found', $result['presence_found']],
            ['New Presence Inserted', $result['new_presence']],
            ['Duplicate Presence Skipped', $result['duplicate_presence']],
            ['Invalid Records', $result['invalid_records']],
            ['Sync Run ID', $result['sync_run_id'] ?? ($dryRun ? 'N/A (Dry-Run)' : 'None')],
        ]);

        if (! empty($result['errors'])) {
            $this->error('Errors encountered:');
            foreach ($result['errors'] as $error) {
                $this->line(" - {$error}");
            }

            return self::FAILURE;
        }

        $this->info($dryRun ? 'Dry-run completed successfully. Zero database writes.' : 'Controlled ingestion completed successfully.');

        return self::SUCCESS;
    }
}
