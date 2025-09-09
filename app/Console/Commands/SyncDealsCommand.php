<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\DealSyncJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncDealsCommand extends Command
{
    protected $signature = 'app:sync-deals 
                            {--account= : Specific account code to sync}
                            {--demo : Sync demo accounts only}
                            {--live : Sync live accounts only}
                            {--full-sync : Perform full sync instead of incremental}
                            {--batch-size=10 : Number of accounts per batch}
                            {--from-days=7 : Days to sync from (for full sync)}';

    protected $description = 'Sync MT5 deals to local database for intelligent position reconstruction';

    public function handle()
    {
        $this->info('Starting Deal Sync Process...');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found to sync.');
            return 1;
        }

        $batchSize = $this->option('batch-size');
        $fullSync = $this->option('full-sync');
        $fromDays = $this->option('from-days');

        $this->info("Found {$accounts->count()} accounts to sync.");
        $this->info("Batch size: {$batchSize}");
        $this->info("Sync type: " . ($fullSync ? 'Full' : 'Incremental'));

        // Show prioritization info
        $this->info("Account prioritization (recently created accounts first):");
        foreach ($accounts->take(5) as $index => $account) {
            $createdDate = Carbon::parse($account->created_at)->format('M j, Y H:i');
            $this->line("  " . ($index + 1) . ". Account {$account->code}: Created {$createdDate}");
        }
        if ($accounts->count() > 5) {
            $this->line("  ... and " . ($accounts->count() - 5) . " more accounts");
        }

        $accountBatches = $accounts->chunk($batchSize);
        $jobs = [];

        foreach ($accountBatches as $batch) {
            $fromTimes = [];

            if ($fullSync) {
                // For full sync, use the same fromTime for all accounts in batch
                $fromTime = now()->subDays($fromDays);
                $fromTimes = array_fill(0, $batch->count(), $fromTime);
            }
            // For incremental sync, fromTimes will be empty and job will determine them

            $job = new DealSyncJob($batch->all(), $fromTimes, $fullSync);
            $jobs[] = $job;
        }

        // Dispatch jobs
        if (count($jobs) === 1) {
            // Single job - dispatch directly to deal-sync queue
            dispatch($jobs[0])->onQueue('deal-sync');
            $this->info('Deal sync job dispatched to deal-sync queue.');
        } else {
            // Multiple jobs - create batch and dispatch to deal-sync queue
            $batch = Bus::batch($jobs)
                ->then(function () {
                    Log::info('All deal sync jobs completed successfully.');
                })
                ->catch(function (\Throwable $e) {
                    Log::error('Deal sync batch failed: ' . $e->getMessage());
                })
                ->name('Deal Sync Batch')
                ->onQueue('deal-sync') // Ensure batch jobs go to deal-sync queue
                ->dispatch();

            $this->info("Deal sync batch created with ID: {$batch->id}");
            $this->info("Dispatched " . count($jobs) . " jobs for {$accounts->count()} accounts to deal-sync queue.");
        }

        return 0;
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

        // PRIORITY: Order by account creation date first (recently created accounts synced first)
        // Recently created accounts are more likely to need initial deal syncing
        $query->select('accounts.*')
            ->orderByDesc('accounts.created_at') // Most recently created accounts first
            ->orderBy('accounts.code'); // Secondary sort by account code for consistency

        return $query->get();
    }
}
