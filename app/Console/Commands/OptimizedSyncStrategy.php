<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\Trade;
use Carbon\Carbon;

class OptimizedSyncStrategy extends Command
{
    protected $signature = 'app:sync-strategy-analyze {--implement : Actually implement the strategy}';

    protected $description = 'Analyze and implement optimized sync strategy to reduce MT5 requests';

    public function handle()
    {
        $this->info("=== MT5 Sync Optimization Analysis ===\n");

        $this->analyzeCurrentLoad();
        $this->suggestOptimizations();

        if ($this->option('implement')) {
            $this->implementOptimizations();
        } else {
            $this->info("\nRun with --implement to apply optimizations");
        }
    }

    protected function analyzeCurrentLoad()
    {
        $this->info("📊 Current Sync Load Analysis:");

        // Get accounts that would be synced
        $totalSyncableAccounts = Account::whereNotNull('code')
            ->whereNull('deleted_at')
            ->where('account_request_status', 1)
            ->where('demo', false)
            ->where(function ($q) {
                $q->whereNull('competition_start_date')
                    ->orWhereNull('competition_end_date')
                    ->orWhereNull('competition_status')
                    ->orWhere('competition_status', '!=', 'active');
            })
            ->count();

        // Accounts with recent activity (last 7 days)
        $activeAccounts = DB::table('trades')
            ->join('accounts', 'trades.code', '=', 'accounts.code')
            ->where('accounts.demo', false)
            ->where('trades.created_at', '>=', now()->subDays(7))
            ->distinct('accounts.code')
            ->count('accounts.code');

        // Accounts with very recent activity (last 24 hours)
        $veryActiveAccounts = DB::table('trades')
            ->join('accounts', 'trades.code', '=', 'accounts.code')
            ->where('accounts.demo', false)
            ->where('trades.created_at', '>=', now()->subDay())
            ->distinct('accounts.code')
            ->count('accounts.code');

        // Average positions per account
        $avgPositions = DB::table('trades')
            ->selectRaw('AVG(position_count) as avg_positions')
            ->from(DB::raw('(SELECT code, COUNT(DISTINCT position_id) as position_count FROM trades GROUP BY code) as subquery'))
            ->value('avg_positions') ?? 5;

        $this->line("Total syncable accounts: {$totalSyncableAccounts}");
        $this->line("Accounts with trades (7 days): {$activeAccounts}");
        $this->line("Accounts with trades (24 hours): {$veryActiveAccounts}");
        $this->line("Average positions per account: " . round($avgPositions, 1));

        $currentRequests = $totalSyncableAccounts * (3 + (2 * $avgPositions));
        $this->line("Current MT5 requests per full sync: " . number_format($currentRequests));

        $inactiveAccounts = $totalSyncableAccounts - $activeAccounts;
        $this->line("Inactive accounts (wasted checks): {$inactiveAccounts} (" . round(($inactiveAccounts / $totalSyncableAccounts) * 100, 1) . "%)");
    }

    protected function suggestOptimizations()
    {
        $this->info("\n🚀 Optimization Strategies:");

        $this->line("\n1. TIERED SYNC FREQUENCY:");
        $this->line("   • Very Active (24h trades): Every 15 minutes");
        $this->line("   • Active (7d trades): Every 2 hours");
        $this->line("   • Inactive (>7d): Every 24 hours");
        $this->line("   • Dormant (>30d): Every 7 days");

        $this->line("\n2. LAST ACTIVITY TRACKING:");
        $this->line("   • Add 'last_trade_at' to accounts table");
        $this->line("   • Skip accounts with no recent activity");
        $this->line("   • Reduce sync frequency based on inactivity");

        $this->line("\n3. INCREMENTAL SYNC:");
        $this->line("   • Only sync trades newer than last_synced_at");
        $this->line("   • Use MT5 time filters instead of full history");
        $this->line("   • Reduce HistoryGetPage range");

        $this->line("\n4. BATCH OPTIMIZATION:");
        $this->line("   • Group accounts by activity level");
        $this->line("   • Process high-activity accounts first");
        $this->line("   • Skip dormant accounts during peak hours");

        $this->line("\n5. SMART SCHEDULING:");
        $this->line("   • Distribute inactive account checks throughout day");
        $this->line("   • Peak hours: Only active accounts");
        $this->line("   • Off-peak: Include dormant accounts");
    }

    protected function implementOptimizations()
    {
        $this->info("\n⚙️  Implementing Optimizations...");

        // Add last_trade_at column if it doesn't exist
        if (!$this->columnExists('accounts', 'last_trade_at')) {
            $this->info("Adding last_trade_at column to accounts table...");
            DB::statement('ALTER TABLE accounts ADD COLUMN last_trade_at TIMESTAMP NULL');
            DB::statement('CREATE INDEX idx_accounts_last_trade_at ON accounts(last_trade_at)');
        } else {
            $this->info("last_trade_at column already exists");
        }

        // Add sync_tier column for tiered syncing
        if (!$this->columnExists('accounts', 'sync_tier')) {
            $this->info("Adding sync_tier column to accounts table...");
            DB::statement("ALTER TABLE accounts ADD COLUMN sync_tier ENUM('very_active', 'active', 'inactive', 'dormant') DEFAULT 'inactive'");
            DB::statement('CREATE INDEX idx_accounts_sync_tier ON accounts(sync_tier)');
        } else {
            $this->info("sync_tier column already exists");
        }

        // Update last_trade_at for existing accounts (simplified approach)
        $this->info("Updating last_trade_at for existing accounts...");

        try {
            // Use raw SQL with DB::select for better compatibility
            $result = DB::select("
                SELECT a.id, MAX(COALESCE(t.closed, t.opened)) as last_trade
                FROM accounts a 
                INNER JOIN trades t ON a.code = t.code 
                WHERE a.code IS NOT NULL 
                GROUP BY a.id
            ");

            foreach ($result as $row) {
                DB::table('accounts')
                    ->where('id', $row->id)
                    ->update(['last_trade_at' => $row->last_trade]);
            }

            $this->info("Updated " . count($result) . " accounts with trade activity");
        } catch (\Exception $e) {
            $this->warn("Could not update last_trade_at: " . $e->getMessage());
            $this->warn("Skipping this step and continuing with tier updates...");
        }

        // Update sync tiers
        $this->info("Updating sync tiers based on activity...");
        $this->updateSyncTiers();

        $this->info("✅ Optimizations implemented!");
    }

    protected function updateSyncTiers()
    {
        // Very active: trades in last 24 hours
        DB::table('accounts')
            ->where('last_trade_at', '>=', now()->subDay())
            ->update(['sync_tier' => 'very_active']);

        // Active: trades in last 7 days
        DB::table('accounts')
            ->where('last_trade_at', '>=', now()->subDays(7))
            ->where('last_trade_at', '<', now()->subDay())
            ->update(['sync_tier' => 'active']);

        // Inactive: trades in last 30 days
        DB::table('accounts')
            ->where('last_trade_at', '>=', now()->subDays(30))
            ->where('last_trade_at', '<', now()->subDays(7))
            ->update(['sync_tier' => 'inactive']);

        // Dormant: no trades in 30+ days or never
        DB::table('accounts')
            ->where(function ($q) {
                $q->where('last_trade_at', '<', now()->subDays(30))
                    ->orWhereNull('last_trade_at');
            })
            ->update(['sync_tier' => 'dormant']);

        $tierCounts = DB::table('accounts')
            ->selectRaw('sync_tier, COUNT(*) as count')
            ->whereNotNull('code')
            ->where('demo', false)
            ->groupBy('sync_tier')
            ->get();

        $this->line("Sync tier distribution:");
        foreach ($tierCounts as $tier) {
            $this->line("  {$tier->sync_tier}: {$tier->count} accounts");
        }
    }

    protected function columnExists($table, $column)
    {
        try {
            $result = DB::select(
                "SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = ? AND table_schema = ?",
                [$table, $column, config('database.connections.mysql.database')]
            );
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
}
