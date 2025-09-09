<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\Services\TradeCacheService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CleanAndResyncTestCommand extends Command
{
    protected $signature = 'app:clean-resync-test 
                            {account : Account code to test cleanup on}
                            {--backup : Create backup before cleanup}
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--resync : Automatically resync after cleanup}';

    protected $description = 'Test cleanup and resync process on a single account';

    public function handle()
    {
        $accountCode = $this->argument('account');
        $dryRun = $this->option('dry-run');
        $createBackup = $this->option('backup');
        $autoResync = $this->option('resync');

        $this->info("🧹 Starting cleanup test for account: {$accountCode}");

        if ($dryRun) {
            $this->warn("DRY RUN MODE: No data will actually be deleted");
        }

        // Step 1: Validate account exists
        $account = Account::where('code', $accountCode)->first();
        if (!$account) {
            $this->error("Account {$accountCode} not found!");
            return 1;
        }

        // Step 2: Show current state
        $this->showCurrentState($account);

        // Step 3: Confirm before proceeding
        if (!$dryRun && !$this->confirm("Do you want to proceed with cleanup for account {$accountCode}?")) {
            $this->info("Cleanup cancelled.");
            return 0;
        }

        // Step 4: Create backup if requested
        if ($createBackup && !$dryRun) {
            $this->createBackup($account);
        }

        // Step 5: Perform cleanup
        $this->performCleanup($account, $dryRun);

        // Step 6: Auto resync if requested
        if ($autoResync && !$dryRun) {
            $this->performResync($account);
        }

        $this->info("✅ Cleanup test completed for account {$accountCode}");
        return 0;
    }

    private function showCurrentState(Account $account): void
    {
        $dealsCount = Deal::where('account_id', $account->id)->count();
        $tradesCount = Trade::where('account_id', $account->id)->count();

        $this->info("\n=== CURRENT STATE ===");
        $this->line("Account: {$account->code} (ID: {$account->id})");
        $this->line("Total Deals: {$dealsCount}");
        $this->line("Total Trades: {$tradesCount}");
        $this->line("Sync Status: " . ($account->sync_status ?? 'NULL'));
        $this->line("Last Synced: " . ($account->deals_last_fetch_at ?? 'Never'));
    }

    private function createBackup(Account $account): void
    {
        $this->info("\n📦 Creating backup...");

        $timestamp = now()->format('Y_m_d_H_i_s');
        $backupFile = storage_path("app/backups/account_{$account->code}_{$timestamp}.sql");

        // Create backup directory if it doesn't exist
        $backupDir = dirname($backupFile);
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $deals = Deal::where('account_id', $account->id)->get();
        $trades = Trade::where('account_id', $account->id)->get();

        $backupData = [
            'account_id' => $account->id,
            'account_code' => $account->code,
            'backup_timestamp' => now()->toISOString(),
            'deals_count' => $deals->count(),
            'trades_count' => $trades->count(),
            'deals' => $deals->toArray(),
            'trades' => $trades->toArray(),
            'account_sync_state' => [
                'sync_status' => $account->sync_status,
                'deals_last_fetch_at' => $account->deals_last_fetch_at,
                'deals_synced_from' => $account->deals_synced_from,
                'deals_synced_to' => $account->deals_synced_to,
                'has_balance_activity' => $account->has_balance_activity,
                'last_balance_changed_at' => $account->last_balance_changed_at,
            ]
        ];

        file_put_contents($backupFile, json_encode($backupData, JSON_PRETTY_PRINT));
        $this->info("✅ Backup created: {$backupFile}");
    }

    private function performCleanup(Account $account, bool $dryRun): void
    {
        $this->info("\n🗑️  Performing cleanup...");

        $dealsCount = Deal::where('account_id', $account->id)->count();
        $tradesCount = Trade::where('account_id', $account->id)->count();

        if ($dryRun) {
            $this->warn("DRY RUN: Would delete {$dealsCount} deals and {$tradesCount} trades");
            $this->warn("DRY RUN: Would reset account sync status");
            $this->warn("DRY RUN: Would invalidate caches");
            return;
        }

        DB::beginTransaction();
        try {
            // Delete trades first (they depend on deals conceptually)
            $deletedTrades = Trade::where('account_id', $account->id)->delete();
            $this->line("Deleted {$deletedTrades} trades");

            // Delete deals
            $deletedDeals = Deal::where('account_id', $account->id)->delete();
            $this->line("Deleted {$deletedDeals} deals");

            // Reset account sync status
            $account->update([
                'sync_status' => 'needs_sync',
                'sync_error' => null,
                'has_balance_activity' => true,
                'last_balance_changed_at' => now(),
                'last_sync_attempt_at' => null,
                'deals_synced_from' => null,
                'deals_synced_to' => null,
                'deals_last_fetch_at' => null,
                'deals_sync_complete' => false
            ]);
            $this->line("Reset account sync status");

            // Invalidate caches
            $cacheService = app(TradeCacheService::class);
            $cacheService->invalidateAccountDeals($account);
            $this->line("Invalidated caches");

            DB::commit();
            $this->info("✅ Cleanup completed successfully");
        } catch (\Exception $e) {
            DB::rollback();
            $this->error("❌ Cleanup failed: " . $e->getMessage());
            Log::error("Cleanup failed for account {$account->code}: " . $e->getMessage());
            throw $e;
        }
    }

    private function performResync(Account $account): void
    {
        $this->info("\n🔄 Starting resync...");

        // Use the optimized sync command
        $this->call('app:optimized-sync-trades', [
            '--test-account' => $account->code
        ]);

        // Show results after resync
        $this->info("\n=== POST-RESYNC STATE ===");
        $this->showCurrentState($account);
    }
}
