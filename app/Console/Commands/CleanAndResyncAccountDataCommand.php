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
                            {--all-accounts : Process ALL accounts (use with caution)}
                            {--confirm : Actually perform the cleanup (without this flag, only shows what would be deleted)}
                            {--force : Force resync even if account was recently synced}
                            {--deals-only : Only clean deals, keep trades}
                            {--trades-only : Only clean trades, keep deals}
                            {--keep-cache : Don\'t clear cache (for testing)}
                            {--resync-after : Automatically resync after cleanup}
                            {--resync-only : Skip cleanup, only resync data}
                            {--dry-run : Show what would be processed without making changes}
                            {--from-days=30 : Days to resync from (default: 30)}
                            {--skip-recent-hours=6 : Skip accounts synced within this many hours (default: 6)}
                            {--batch-size=1 : Number of accounts to process at once}';

    protected $description = 'Clean deals and trades data for accounts and optionally resync fresh data';

    public function handle()
    {
        $this->info('🧹 Account Data Cleanup and Resync Tool');
        $this->info('=====================================');

        $accountCode = $this->option('account');
        $allAccounts = $this->option('all-accounts');
        $confirmCleanup = $this->option('confirm');
        $force = $this->option('force');
        $dealsOnly = $this->option('deals-only');
        $tradesOnly = $this->option('trades-only');
        $keepCache = $this->option('keep-cache');
        $resyncAfter = $this->option('resync-after');
        $resyncOnly = $this->option('resync-only');
        $dryRun = $this->option('dry-run');
        $fromDays = $this->option('from-days');
        $skipRecentHours = $this->option('skip-recent-hours');

        // Get account(s) to process
        if ($accountCode) {
            $accounts = Account::where('code', $accountCode)->get();
            if ($accounts->isEmpty()) {
                $this->error("Account with code {$accountCode} not found.");
                return 1;
            }
        } elseif ($allAccounts) {
            $accounts = Account::all();
            $this->warn("⚠️  Processing ALL {$accounts->count()} accounts!");

            if (!$confirmCleanup && !$resyncOnly && !$dryRun) {
                $this->error('When using --all-accounts, you must use --confirm for cleanup, --resync-only for resync, or --dry-run to preview');
                return 1;
            }

            if (!$resyncOnly && !$dryRun && !$this->confirm("Are you ABSOLUTELY sure you want to clean data for ALL {$accounts->count()} accounts?")) {
                $this->info("Operation cancelled.");
                return 1;
            }
        } else {
            $this->error('Please specify either --account=XXXXX or --all-accounts');
            $this->info('Examples:');
            $this->info('  Single account: php artisan app:clean-and-resync-account-data --account=394402 --confirm --resync-after');
            $this->info('  All accounts (dry run): php artisan app:clean-and-resync-account-data --all-accounts --resync-only --dry-run');
            $this->info('  All accounts (resync only): php artisan app:clean-and-resync-account-data --all-accounts --resync-only --confirm');
            $this->info('  All accounts (clean & resync): php artisan app:clean-and-resync-account-data --all-accounts --confirm --resync-after');
            return 1;
        }

        // Filter accounts to avoid duplicate processing (for resync operations)
        if (($resyncOnly || $resyncAfter) && !$force && $allAccounts) {
            $recentlysynced = now()->subHours($skipRecentHours);
            $originalCount = $accounts->count();

            $accounts = $accounts->filter(function ($account) use ($recentlysynced, $fromDays) {
                // Skip if recently synced (within skip hours) and sync covers our target period
                if ($account->deals_last_fetch_at && $account->deals_last_fetch_at >= $recentlysynced) {
                    $syncedFrom = $account->deals_synced_from;
                    $targetFrom = now()->subDays($fromDays);

                    // Skip if the previous sync covers our target period
                    if ($syncedFrom && $syncedFrom <= $targetFrom) {
                        return false;
                    }
                }
                return true;
            });

            $filteredCount = $accounts->count();
            $skippedCount = $originalCount - $filteredCount;

            if ($skippedCount > 0) {
                $this->info("📋 Filtered out {$skippedCount} recently synced accounts (use --force to override)");
                $this->info("📊 Processing {$filteredCount} accounts that need sync");
            }

            if ($filteredCount == 0) {
                $this->info("✅ All accounts are already up to date!");
                return 0;
            }
        }

        foreach ($accounts as $index => $account) {
            if ($allAccounts) {
                $this->info("\n📊 Progress: " . ($index + 1) . "/{$accounts->count()} accounts");
            }

            $accountOptions = [
                'confirm' => $confirmCleanup,
                'force' => $force,
                'deals_only' => $dealsOnly,
                'trades_only' => $tradesOnly,
                'keep_cache' => $keepCache,
                'resync_after' => $resyncAfter,
                'resync_only' => $resyncOnly,
                'dry_run' => $dryRun,
                'from_days' => $fromDays,
                'skip_recent_hours' => $skipRecentHours,
                'skip_individual_confirm' => $allAccounts // Skip individual confirmations for bulk operations
            ];

            $this->processAccount($account, $accountOptions);
        }

        // Show summary for bulk operations
        if ($allAccounts) {
            $this->info("\n🎉 Bulk operation completed!");
            $this->info("📊 Processed {$accounts->count()} accounts total");

            if ($dryRun) {
                $this->info("🔍 Mode: Dry run (no changes made)");
            } elseif ($resyncOnly) {
                $this->info("🔄 Mode: Resync only");
            } else {
                $this->info("🧹 Mode: " . ($resyncAfter ? "Clean and resync" : "Clean only"));
            }
        }

        return 0;
    }

    protected function processAccount(Account $account, array $options)
    {
        $this->info("\n🔍 Processing Account: {$account->code} (ID: {$account->id})");
        $this->info("======================================");

        // Show current data status
        $this->showCurrentDataStatus($account);

        // Check if account should be skipped (for individual processing)
        if (($options['resync_only'] || $options['resync_after']) && !$options['force'] && !isset($options['skip_individual_confirm'])) {
            if ($this->shouldSkipAccount($account, $options)) {
                $this->info("⏭️  Skipping account {$account->code} - recently synced (use --force to override)");
                return;
            }
        }

        // If dry-run mode, show what would be done and return
        if ($options['dry_run']) {
            $this->info("\n🔍 DRY RUN MODE - Showing what would be processed:");
            if ($options['resync_only']) {
                $this->info("🔄 Would resync account {$account->code} from {$options['from_days']} days ago");
            } else {
                $this->showWhatWouldBeDeleted($account, $options);
                if ($options['resync_after']) {
                    $this->info("🔄 Would then resync account {$account->code} from {$options['from_days']} days ago");
                }
            }
            return;
        }

        // If resync-only mode, skip cleanup and go straight to resync
        if ($options['resync_only']) {
            $this->resyncAccount($account, $options['from_days']);
            return;
        }

        if (!$options['confirm']) {
            $this->warn("\n⚠️  DRY RUN MODE - Add --confirm to actually perform cleanup");
            $this->showWhatWouldBeDeleted($account, $options);
            return;
        }

        // Confirm with user (only for single account operations)
        if (!isset($options['skip_individual_confirm']) && !$this->confirm("Are you sure you want to clean data for account {$account->code}?")) {
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

    protected function shouldSkipAccount(Account $account, array $options)
    {
        // Don't skip if force is enabled
        if ($options['force']) {
            return false;
        }

        // Check if account was recently synced
        $recentlysynced = now()->subHours($options['skip_recent_hours']);
        if (!$account->deals_last_fetch_at || $account->deals_last_fetch_at < $recentlysynced) {
            return false; // Not recently synced, don't skip
        }

        // Check if the previous sync covers our target period
        $syncedFrom = $account->deals_synced_from;
        $targetFrom = now()->subDays($options['from_days']);

        if (!$syncedFrom || $syncedFrom > $targetFrom) {
            return false; // Previous sync doesn't cover our target period, don't skip
        }

        return true; // Account was recently synced and covers our target period
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
