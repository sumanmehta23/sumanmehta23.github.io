<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\PositionSyncJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncPositionsCommand extends Command
{
    protected $signature = 'app:sync-positions 
                            {--account= : Specific account code to sync}
                            {--demo : Sync demo accounts only}
                            {--live : Sync live accounts only}
                            {--batch-size=10 : Number of accounts per batch}
                            {--open-only : Sync only open positions}
                            {--closed-since= : Sync closed positions since date (Y-m-d)}
                            {--from-days=7 : Days to sync closed positions from (if closed-since not specified)}';

    protected $description = 'Sync MT5 positions directly to trades using Position APIs (most efficient method)';

    public function handle()
    {
        $this->info('🎯 Starting Position-Based Trade Sync...');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found to sync.');
            return 1;
        }

        $batchSize = $this->option('batch-size');
        $openOnly = $this->option('open-only');
        $closedSince = $this->getClosedSinceDate();

        $this->info("Found {$accounts->count()} accounts to sync.");
        $this->info("Sync type: " . ($openOnly ? 'Open positions only' : 'Open and closed positions'));
        $this->info("Batch size: {$batchSize}");

        if (!$openOnly && $closedSince) {
            $this->info("Closed positions since: " . $closedSince->format('Y-m-d H:i:s'));
        }

        $accountBatches = $accounts->chunk($batchSize);
        $jobs = [];

        foreach ($accountBatches as $batch) {
            $job = new PositionSyncJob($batch->all(), $openOnly, $closedSince);
            $jobs[] = $job;
        }

        // Dispatch jobs
        if (count($jobs) === 1) {
            // Single job - dispatch directly
            dispatch($jobs[0]);
            $this->info('Position sync job dispatched.');
        } else {
            // Multiple jobs - create batch
            $batch = Bus::batch($jobs)
                ->then(function () {
                    Log::info('All position sync jobs completed successfully.');
                })
                ->catch(function (\Throwable $e) {
                    Log::error('Position sync batch failed: ' . $e->getMessage());
                })
                ->name('Position Sync Batch')
                ->dispatch();

            $this->info("Position sync batch created with ID: {$batch->id}");
            $this->info("Dispatched " . count($jobs) . " jobs for {$accounts->count()} accounts.");
        }

        $this->info('✅ Position sync process initiated!');

        // Show next steps
        $this->line('');
        $this->info('💡 Next steps:');
        $this->line('  • Monitor progress: php artisan app:sync-status-dashboard');
        $this->line('  • Check queue status: php artisan queue:work');
        $this->line('  • View logs for detailed progress');

        return 0;
    }

    protected function getClosedSinceDate(): ?Carbon
    {
        if ($this->option('closed-since')) {
            try {
                return Carbon::createFromFormat('Y-m-d', $this->option('closed-since'))->startOfDay();
            } catch (\Exception $e) {
                $this->error("Invalid date format for --closed-since. Use Y-m-d format.");
                return null;
            }
        }

        if (!$this->option('open-only')) {
            $fromDays = $this->option('from-days');
            return now()->subDays($fromDays);
        }

        return null;
    }

    protected function getAccounts()
    {
        $query = Account::query();

        // Filter by specific account
        if ($this->option('account')) {
            $query->where('code', $this->option('account'));
        }

        // Filter by account type
        if ($this->option('demo')) {
            $query->where('demo', true);
        } elseif ($this->option('live')) {
            $query->where('demo', false);
        }

        // Only get accounts that are not currently syncing
        $query->whereNotIn('sync_status', ['syncing', 'pending']);

        return $query->orderBy('code')->get();
    }
}
