<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\EnhancedBatchSyncTradesJob;
use App\Jobs\DealSyncJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class IntelligentSyncTradesCommand extends Command
{
    protected $signature = 'app:intelligent-sync-trades 
                            {--account= : Specific account code to sync}
                            {--demo : Sync demo accounts only}
                            {--live : Sync live accounts only}
                            {--batch-size=5 : Number of accounts per batch}
                            {--from-days=7 : Days to sync from}
                            {--sync-deals-first : Sync deals first, then trades}
                            {--deal-sync-only : Only sync deals, no trade reconstruction}
                            {--max-trades= : Maximum trades limit per account}
                            {--min-trades= : Minimum trades limit per account}';

    protected $description = 'Intelligent trade sync using deal-based reconstruction and incremental syncing';

    public function handle()
    {
        $this->info('🚀 Starting Intelligent Trade Sync Process...');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found to sync.');
            return 1;
        }

        $batchSize = $this->option('batch-size');
        $fromDays = $this->option('from-days');
        $syncDealsFirst = $this->option('sync-deals-first');
        $dealSyncOnly = $this->option('deal-sync-only');
        $maxTrades = $this->option('max-trades');
        $minTrades = $this->option('min-trades');

        $this->info("Found {$accounts->count()} accounts to sync.");
        $this->info("Strategy: " . ($dealSyncOnly ? 'Deals Only' : ($syncDealsFirst ? 'Deals First, Then Trades' : 'Intelligent Trade Sync')));
        $this->info("Batch size: {$batchSize}");

        if ($dealSyncOnly || $syncDealsFirst) {
            $this->syncDeals($accounts, $batchSize, $fromDays);

            if ($dealSyncOnly) {
                $this->info('✅ Deal sync completed. Exiting as requested.');
                return 0;
            }

            $this->info('⏱️  Waiting 30 seconds for deal sync to complete before trade sync...');
            sleep(30);
        }

        $this->syncTrades($accounts, $batchSize, $fromDays, $maxTrades, $minTrades);

        $this->info('✅ Intelligent sync process completed!');
        return 0;
    }

    protected function syncDeals($accounts, $batchSize, $fromDays)
    {
        $this->info('📊 Starting Deal Sync Phase...');

        $accountBatches = $accounts->chunk($batchSize);
        $dealJobs = [];

        foreach ($accountBatches as $batch) {
            $fromTime = now()->subDays($fromDays);
            $fromTimes = array_fill(0, $batch->count(), $fromTime);

            $job = new DealSyncJob($batch->all(), $fromTimes, false); // Incremental sync
            $dealJobs[] = $job;
        }

        if (count($dealJobs) === 1) {
            dispatch($dealJobs[0]);
            $this->info('Deal sync job dispatched.');
        } else {
            $batch = Bus::batch($dealJobs)
                ->then(function () {
                    Log::info('All deal sync jobs completed successfully.');
                })
                ->catch(function (\Throwable $e) {
                    Log::error('Deal sync batch failed: ' . $e->getMessage());
                })
                ->name('Intelligent Deal Sync Batch')
                ->dispatch();

            $this->info("Deal sync batch created with ID: {$batch->id}");
        }
    }

    protected function syncTrades($accounts, $batchSize, $fromDays, $maxTrades, $minTrades)
    {
        $this->info('🔄 Starting Enhanced Trade Sync Phase...');

        $accountBatches = $accounts->chunk($batchSize);
        $tradeJobs = [];

        foreach ($accountBatches as $batch) {
            $fromTime = now()->subDays($fromDays);
            $fromTimes = array_fill(0, $batch->count(), $fromTime);

            $job = new EnhancedBatchSyncTradesJob(
                $batch->all(),
                $fromTimes,
                $maxTrades,
                $minTrades,
                true // Use deal-based sync
            );
            $tradeJobs[] = $job;
        }

        if (count($tradeJobs) === 1) {
            dispatch($tradeJobs[0]);
            $this->info('Enhanced trade sync job dispatched.');
        } else {
            $batch = Bus::batch($tradeJobs)
                ->then(function () {
                    Log::info('All enhanced trade sync jobs completed successfully.');
                })
                ->catch(function (\Throwable $e) {
                    Log::error('Enhanced trade sync batch failed: ' . $e->getMessage());
                })
                ->name('Intelligent Trade Sync Batch')
                ->dispatch();

            $this->info("Enhanced trade sync batch created with ID: {$batch->id}");
        }
    }

    protected function getAccounts()
    {
        $query = Account::query();

        if ($this->option('account')) {
            $query->where('code', $this->option('account'));
        }

        if ($this->option('demo')) {
            $query->where('demo', true);
        } elseif ($this->option('live')) {
            $query->where('demo', false);
        }

        // Only get accounts that are not currently syncing and not flagged
        $query->whereNotIn('sync_status', ['syncing', 'pending', 'not_found_in_mt5', 'flagged']);

        return $query->orderBy('code')->get();
    }
}
