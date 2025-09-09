<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\Services\TradeCacheService;
use App\Jobs\DealSyncJob;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanAndResyncAccountDataCommand extends Command
{
    protected $signature = 'app:clean-and-resync-account-data 
                            {--account= : Specific account code to clean and resync}
                            {--confirm : Actually perform the cleanup (without this flag, only shows what would be deleted)}
                            {--deals-only : Only clean deals, keep trades}
                            {--trades-only : Only clean trades, keep deals}
                            {--keep-cache : Don\'t clear cache (for testing)}
                            {--resync-after : Automatically resync after cleanup}
                            {--from-days=30 : Days to resync from (default: 30)}
                            {--batch-size=1 : Number of accounts to process at once}';

    protected $description = 'Clean deals and trades data for accounts and optionally resync fresh data';

    public function handle()
    {
        $this->info('🧹 Account Data Cleanup and Resync Tool');
        $this->info('=====================================');

        $accountCode = $this->option('account');
        $confirmCleanup = $this->option('confirm');
        $dealsOnly = $this->option('deals-only');
        $tradesOnly = $this->option('trades-only');
        $keepCache = $this->option('keep-cache');
        $resyncAfter = $this->option('resync-after');
        $fromDays = $this->option('from-days');

        // Get account(s) to process
        if ($accountCode) {
            $accounts = Account::where('code', $accountCode)->get();
            if ($accounts->isEmpty()) {
                $this->error("Account with code {$accountCode} not found.");
                return 1;
            }
        } else {
            $this->error('Please specify an account code with --account=XXXXX');
            $this->info('Example: php artisan app:clean-and-resync-account-data --account=394402 --confirm --resync-after');
            return 1;
        }

        foreach ($accounts as $account) {
            $this->processAccount($account, [
                'confirm' => $confirmCleanup,
                'deals_only' => $dealsOnly,
                'trades_only' => $tradesOnly,
                'keep_cache' => $keepCache,
                'resync_after' => $resyncAfter,
                'from_days' => $fromDays
            ]);
        }

        return 0;
    }

    protected function processAccount(Account $account, array $options)
    {
        $this->info("\n🔍 Processing Account: {$account->code} (ID: {$account->id})");
        $this->info("======================================");

        // Show current data status
        $this->showCurrentDataStatus($account);

        if (!$options['confirm']) {
            $this->warn("\n⚠️  DRY RUN MODE - Add --confirm to actually perform cleanup");
            $this->showWhatWouldBeDeleted($account, $options);
            return;
        }

        // Confirm with user
        if (!$this->confirm("Are you sure you want to clean data for account {$account->code}?")) {
            $this->info("Cleanup cancelled for account {$account->code}");
            return;
        }

        $this->info("\n🧹 Starting cleanup for account {$account->code}...");

        try {
            DB::beginTransaction();

            // Clean deals
            if (!$options['trades_only']) {
                $this->cleanDealsData($account);
            }

            // Clean trades
            if (!$options['deals_only']) {
                $this->cleanTradesData($account);
            }

            // Reset account sync status
            $this->resetAccountSyncStatus($account);

            // Clear cache
            if (!$options['keep_cache']) {
                $this->clearAccountCache($account);
            }

            DB::commit();

            $this->info("✅ Cleanup completed successfully for account {$account->code}");

            // Show new status
            $this->showCurrentDataStatus($account);

            // Resync if requested
            if ($options['resync_after']) {
                $this->resyncAccount($account, $options['from_days']);
            }
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Cleanup failed for account {$account->code}: " . $e->getMessage());
            Log::error("Account cleanup failed", [
                'account' => $account->code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    protected function showCurrentDataStatus(Account $account)
    {
        $dealsCount = Deal::where('account_id', $account->id)->count();
        $tradesCount = Trade::where('account_id', $account->id)->count();

        $this->info("📊 Current Data Status:");
        $this->info("   Deals: {$dealsCount}");
        $this->info("   Trades: {$tradesCount}");
        $this->info("   Sync Status: {$account->sync_status}");
        $this->info("   Last Synced: " . ($account->deals_last_fetch_at ?? 'Never'));
        $this->info("   Has Balance Activity: " . ($account->has_balance_activity ? 'Yes' : 'No'));
    }

    protected function showWhatWouldBeDeleted(Account $account, array $options)
    {
        $this->info("\n📋 What would be deleted:");

        if (!$options['trades_only']) {
            $dealsCount = Deal::where('account_id', $account->id)->count();
            $this->info("   🗂️  {$dealsCount} deals");
        }

        if (!$options['deals_only']) {
            $tradesCount = Trade::where('account_id', $account->id)->count();
            $this->info("   📈 {$tradesCount} trades");
        }

        if (!$options['keep_cache']) {
            $this->info("   🗄️  All cached data");
        }

        $this->info("   ⚙️  Account sync status reset");
    }

    protected function cleanDealsData(Account $account)
    {
        $dealsCount = Deal::where('account_id', $account->id)->count();

        if ($dealsCount > 0) {
            $this->info("🗂️  Deleting {$dealsCount} deals...");
            $deleted = Deal::where('account_id', $account->id)->delete();
            $this->info("   ✅ Deleted {$deleted} deals");
        } else {
            $this->info("🗂️  No deals to delete");
        }
    }

    protected function cleanTradesData(Account $account)
    {
        $tradesCount = Trade::where('account_id', $account->id)->count();

        if ($tradesCount > 0) {
            $this->info("📈 Deleting {$tradesCount} trades...");
            $deleted = Trade::where('account_id', $account->id)->delete();
            $this->info("   ✅ Deleted {$deleted} trades");
        } else {
            $this->info("📈 No trades to delete");
        }
    }

    protected function resetAccountSyncStatus(Account $account)
    {
        $this->info("⚙️  Resetting account sync status...");

        $account->update([
            'sync_status' => 'needs_sync',
            'sync_error' => null,
            'has_balance_activity' => true,
            'last_balance_changed_at' => now(),
            'last_sync_attempt_at' => null,
            'deals_synced_from' => null,
            'deals_synced_to' => null,
            'deals_last_fetch_at' => null,
            'deals_sync_complete' => false,
            'sync_flagged_at' => null,
            'sync_flag_reason' => null,
            'sync_stuck_count' => 0
        ]);

        $this->info("   ✅ Account sync status reset");
    }

    protected function clearAccountCache(Account $account)
    {
        $this->info("🗄️  Clearing cache...");

        $cacheService = app(TradeCacheService::class);
        $cacheService->invalidateAccountDeals($account);

        $this->info("   ✅ Cache cleared");
    }

    protected function resyncAccount(Account $account, int $fromDays)
    {
        $this->info("\n🔄 Starting resync for account {$account->code}...");
        $this->info("📅 Syncing from {$fromDays} days ago");

        try {
            $fromTime = now()->subDays($fromDays);

            // Step 1: Sync deals first
            $this->info("🗂️  Step 1: Syncing deals...");
            $dealSyncJob = new DealSyncJob([$account], [$fromTime], false);
            $dealSyncJob->handle(
                app(\App\Services\UniversalMT5Service::class),
                app(TradeCacheService::class)
            );
            $this->info("   ✅ Deals sync completed");

            // Step 2: Sync trades
            $this->info("📈 Step 2: Syncing trades...");
            $batchSyncJob = new BatchSyncTradesJob([$account], [$fromTime]);
            $batchSyncJob->handle(
                app(\App\Services\UniversalMT5Service::class),
                app(TradeCacheService::class)
            );
            $this->info("   ✅ Trades sync completed");

            // Show final status
            $this->info("\n🎉 Resync completed successfully!");
            $this->showCurrentDataStatus($account);
        } catch (\Exception $e) {
            $this->error("❌ Resync failed: " . $e->getMessage());
            Log::error("Account resync failed", [
                'account' => $account->code,
                'error' => $e->getMessage()
            ]);
        }
    }
}
