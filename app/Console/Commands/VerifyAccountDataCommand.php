<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyAccountDataCommand extends Command
{
    protected $signature = 'app:verify-account-data 
                            {--account= : Specific account code to verify}
                            {--detailed : Show detailed breakdown}';

    protected $description = 'Verify account data integrity and show comprehensive statistics';

    public function handle()
    {
        $accountCode = $this->option('account');
        $detailed = $this->option('detailed');

        if ($accountCode) {
            $account = Account::where('code', $accountCode)->first();
            if (!$account) {
                $this->error("Account {$accountCode} not found");
                return 1;
            }
            $this->verifyAccount($account, $detailed);
        } else {
            $this->error('Please specify an account code with --account=XXXXX');
            return 1;
        }

        return 0;
    }

    protected function verifyAccount(Account $account, bool $detailed = false)
    {
        $this->info("📊 Account Data Verification: {$account->code}");
        $this->info("==========================================");

        // Basic counts
        $dealsCount = Deal::where('account_id', $account->id)->count();
        $tradesCount = Trade::where('account_id', $account->id)->count();

        $this->info("Basic Statistics:");
        $this->info("  Total Deals: {$dealsCount}");
        $this->info("  Total Trades: {$tradesCount}");

        if ($detailed && $dealsCount > 0) {
            $this->showDetailedDealsAnalysis($account);
        }

        if ($detailed && $tradesCount > 0) {
            $this->showDetailedTradesAnalysis($account);
        }

        $this->showAccountStatus($account);
        $this->showDataIntegrityChecks($account);
    }

    protected function showDetailedDealsAnalysis(Account $account)
    {
        $this->info("\n🗂️  Detailed Deals Analysis:");

        // Date range
        $dateRange = Deal::where('account_id', $account->id)
            ->selectRaw('MIN(time_done) as earliest, MAX(time_done) as latest')
            ->first();

        if ($dateRange) {
            $this->info("  Date Range: {$dateRange->earliest} to {$dateRange->latest}");
        }

        // Deal types
        $dealTypes = Deal::where('account_id', $account->id)
            ->selectRaw('type, COUNT(*) as count, SUM(volume) as total_volume')
            ->groupBy('type')
            ->get();

        $this->info("  Deal Types:");
        foreach ($dealTypes as $type) {
            $typeName = $type->type == 0 ? 'BUY' : 'SELL';
            $this->info("    {$typeName}: {$type->count} deals, {$type->total_volume} volume");
        }

        // Symbols
        $symbols = Deal::where('account_id', $account->id)
            ->selectRaw('symbol, COUNT(*) as count')
            ->groupBy('symbol')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $this->info("  Top 5 Symbols:");
        foreach ($symbols as $symbol) {
            $this->info("    {$symbol->symbol}: {$symbol->count} deals");
        }

        // Check for potential duplicates
        $duplicates = DB::select("
            SELECT position_id, COUNT(*) as count 
            FROM deals 
            WHERE account_id = ? 
            GROUP BY position_id 
            HAVING COUNT(*) > 10 
            ORDER BY count DESC 
            LIMIT 5
        ", [$account->id]);

        if (!empty($duplicates)) {
            $this->warn("  Potential Issues (positions with >10 deals):");
            foreach ($duplicates as $dup) {
                $this->warn("    Position {$dup->position_id}: {$dup->count} deals");
            }
        }
    }

    protected function showDetailedTradesAnalysis(Account $account)
    {
        $this->info("\n📈 Detailed Trades Analysis:");

        // Trade states
        $tradeStates = Trade::where('account_id', $account->id)
            ->selectRaw('state, COUNT(*) as count')
            ->groupBy('state')
            ->get();

        $this->info("  Trade States:");
        foreach ($tradeStates as $state) {
            $this->info("    {$state->state}: {$state->count} trades");
        }

        // Date range
        $dateRange = Trade::where('account_id', $account->id)
            ->selectRaw('MIN(time_open) as earliest, MAX(time_open) as latest')
            ->first();

        if ($dateRange) {
            $this->info("  Date Range: {$dateRange->earliest} to {$dateRange->latest}");
        }

        // Volume analysis
        $volumeStats = Trade::where('account_id', $account->id)
            ->selectRaw('MIN(volume) as min_vol, MAX(volume) as max_vol, AVG(volume) as avg_vol, SUM(volume) as total_vol')
            ->first();

        if ($volumeStats) {
            $this->info("  Volume Stats:");
            $this->info("    Min: {$volumeStats->min_vol}, Max: {$volumeStats->max_vol}");
            $this->info("    Average: " . round($volumeStats->avg_vol, 2) . ", Total: {$volumeStats->total_vol}");
        }
    }

    protected function showAccountStatus(Account $account)
    {
        $this->info("\n⚙️  Account Status:");
        $this->info("  Code: {$account->code}");
        $this->info("  Sync Status: {$account->sync_status}");
        $this->info("  Last Balance Sync: " . ($account->last_balance_sync_at ?? 'Never'));
        $this->info("  Last Deals Fetch: " . ($account->deals_last_fetch_at ?? 'Never'));
        $this->info("  Has Balance Activity: " . ($account->has_balance_activity ? 'Yes' : 'No'));
        $this->info("  Sync Error: " . ($account->sync_error ?? 'None'));

        if ($account->sync_flagged_at) {
            $this->warn("  🚩 Flagged: {$account->sync_flag_reason} at {$account->sync_flagged_at}");
        }
    }

    protected function showDataIntegrityChecks(Account $account)
    {
        $this->info("\n🔍 Data Integrity Checks:");

        // Check for trades without corresponding deals
        $tradesWithoutDeals = DB::select("
            SELECT COUNT(*) as count
            FROM trades t
            LEFT JOIN deals d ON d.position_id = t.position_id AND d.account_id = t.account_id
            WHERE t.account_id = ? AND d.position_id IS NULL
        ", [$account->id])[0]->count ?? 0;

        if ($tradesWithoutDeals > 0) {
            $this->warn("  ⚠️  {$tradesWithoutDeals} trades without corresponding deals");
        } else {
            $this->info("  ✅ All trades have corresponding deals");
        }

        // Check for deals without trades (expected for some cases)
        $dealsWithoutTrades = DB::select("
            SELECT COUNT(DISTINCT position_id) as count
            FROM deals d
            LEFT JOIN trades t ON t.position_id = d.position_id AND t.account_id = d.account_id
            WHERE d.account_id = ? AND t.position_id IS NULL
        ", [$account->id])[0]->count ?? 0;

        $this->info("  📊 {$dealsWithoutTrades} unique positions in deals without trades (this may be normal)");

        // Check for invalid position IDs
        $invalidPositions = Deal::where('account_id', $account->id)
            ->where(function ($query) {
                $query->whereNull('position_id')
                    ->orWhere('position_id', '')
                    ->orWhere('position_id', '0');
            })
            ->count();

        if ($invalidPositions > 0) {
            $this->error("  ❌ {$invalidPositions} deals with invalid position IDs");
        } else {
            $this->info("  ✅ All deals have valid position IDs");
        }
    }
}
