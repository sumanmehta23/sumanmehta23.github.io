<?php

namespace App\Console\Commands;

use App\Models\Trade;
use App\Models\Deal;
use App\Models\Account;
use App\Jobs\DealSyncJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Fix Trade Profits Command
 * 
 * This command recalculates and fixes profit values for existing trades by:
 * 1. Finding trades with zero or incorrect profit
 * 2. Recalculating profit from associated deals
 * 3. Updating trades with correct profit values
 * 4. Providing detailed reporting of fixes applied
 */
class FixTradesProfitCommand extends Command
{
    protected $signature = 'trades:fix-profit 
                            {--account= : Fix trades for specific account code}
                            {--position= : Fix specific position ID}
                            {--status= : Fix trades with specific status (open/closed)}
                            {--zero-only : Only fix trades with exactly zero profit}
                            {--dry-run : Show what would be fixed without making changes}
                            {--batch-size=100 : Number of trades to process per batch}
                            {--limit= : Maximum number of trades to process}
                            {--sync-deals : Automatically sync deals for accounts with insufficient deal data}
                            {--check-deal-coverage : Check and report deal coverage for each account}';

    protected $description = 'Recalculate and fix profit values for existing trades using deal data';

    public function handle()
    {
        $accountCode = $this->option('account');
        $positionId = $this->option('position');
        $status = $this->option('status');
        $zeroOnly = $this->option('zero-only');
        $dryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $syncDeals = $this->option('sync-deals');
        $checkDealCoverage = $this->option('check-deal-coverage');

        $this->info("Starting trade profit fix process...");

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No changes will be made");
        }

        // If checking deal coverage, analyze accounts first
        if ($checkDealCoverage) {
            $this->checkDealCoverage($accountCode);
            if (!$this->confirm('Continue with profit fixing?')) {
                return 0;
            }
        }

        // Build query to find trades that need fixing
        $query = Trade::query();

        if ($accountCode) {
            $account = Account::where('code', $accountCode)->first();
            if (!$account) {
                $this->error("Account {$accountCode} not found");
                return 1;
            }
            $query->where('account_id', $account->id);

            // Check deal coverage for this specific account
            if ($syncDeals) {
                $this->checkAndSyncAccountDeals($account, $dryRun);
            }
        } else {
            // For multiple accounts, check deal coverage per account during processing
            if ($syncDeals) {
                $this->info("Will check and sync deals for each account as needed...");
            }
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($zeroOnly) {
            $query->where('profit', 0);
        }

        if ($limit) {
            $query->limit($limit);
        }

        $totalTrades = $query->count();
        $this->info("Found {$totalTrades} trades to process");

        if ($totalTrades == 0) {
            $this->info("No trades found matching criteria");
            return 0;
        }

        $fixedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;
        $totalProfitChange = 0;
        $accountsNeedingDealSync = [];

        $progressBar = $this->output->createProgressBar($totalTrades);
        $progressBar->start();

        // Process trades in batches
        $query->chunk($batchSize, function ($trades) use (
            &$fixedCount,
            &$skippedCount,
            &$errorCount,
            &$totalProfitChange,
            &$accountsNeedingDealSync,
            $dryRun,
            $progressBar,
            $syncDeals
        ) {
            foreach ($trades as $trade) {
                try {
                    // Check deal coverage for this account if not already done
                    if ($syncDeals && !in_array($trade->account_id, $accountsNeedingDealSync)) {
                        $account = Account::find($trade->account_id);
                        if ($account && $this->needsDealSync($account)) {
                            $accountsNeedingDealSync[] = $trade->account_id;
                            $this->checkAndSyncAccountDeals($account, $dryRun);
                        }
                    }

                    $result = $this->fixTradeProfit($trade, $dryRun);

                    if ($result['fixed']) {
                        $fixedCount++;
                        $totalProfitChange += $result['profit_change'];

                        if (!$dryRun) {
                            Log::info("Fixed profit for trade {$trade->position_id}: {$result['old_profit']} → {$result['new_profit']}");
                        }
                    } else {
                        $skippedCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("Error processing trade {$trade->position_id}: " . $e->getMessage());
                    Log::error("Trade profit fix error for {$trade->position_id}: " . $e->getMessage());
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine();

        // Report results
        $this->info("=== TRADE PROFIT FIX RESULTS ===");
        $this->info("Trades processed: {$totalTrades}");
        $this->info("Trades fixed: {$fixedCount}");
        $this->info("Trades skipped: {$skippedCount}");
        $this->info("Errors: {$errorCount}");
        $this->info("Total profit change: " . number_format($totalProfitChange, 2));

        if (!empty($accountsNeedingDealSync)) {
            $this->info("Accounts that needed deal sync: " . count($accountsNeedingDealSync));
        }

        if ($dryRun) {
            $this->warn("DRY RUN COMPLETED - No actual changes were made");
            $this->info("Run without --dry-run to apply the fixes");
        } else {
            $this->info("Trade profit fix completed successfully!");
        }

        return 0;
    }

    /**
     * Fix profit for a single trade by recalculating from deals
     */
    protected function fixTradeProfit(Trade $trade, bool $dryRun = false): array
    {
        $oldProfit = $trade->profit;

        // Get all deals for this position
        $deals = Deal::where('account_id', $trade->account_id)
            ->where('position_id', $trade->position_id)
            ->get();

        if ($deals->isEmpty()) {
            return [
                'fixed' => false,
                'reason' => 'No deals found',
                'old_profit' => $oldProfit,
                'new_profit' => $oldProfit,
                'profit_change' => 0
            ];
        }

        // Calculate new profit from deals
        $newProfit = $deals->sum('profit');

        // Round to 2 decimal places
        $newProfit = round($newProfit, 2);

        // Check if profit actually needs fixing
        if (abs($oldProfit - $newProfit) < 0.01) {
            return [
                'fixed' => false,
                'reason' => 'Profit already correct',
                'old_profit' => $oldProfit,
                'new_profit' => $newProfit,
                'profit_change' => 0
            ];
        }

        // Update the trade if not in dry run mode
        if (!$dryRun) {
            $trade->update([
                'profit' => $newProfit,
                'updated_at' => now()
            ]);
        }

        return [
            'fixed' => true,
            'reason' => 'Profit recalculated from deals',
            'old_profit' => $oldProfit,
            'new_profit' => $newProfit,
            'profit_change' => $newProfit - $oldProfit,
            'deal_count' => $deals->count()
        ];
    }

    /**
     * Check if an account needs deal sync (has more trades than deals)
     */
    protected function needsDealSync(Account $account): bool
    {
        $tradeCount = Trade::where('account_id', $account->id)->count();
        $dealCount = Deal::where('account_id', $account->id)->count();

        return $tradeCount > $dealCount;
    }

    /**
     * Check and sync deals for an account if needed
     */
    protected function checkAndSyncAccountDeals(Account $account, bool $dryRun = false): void
    {
        $tradeCount = Trade::where('account_id', $account->id)->count();
        $dealCount = Deal::where('account_id', $account->id)->count();

        $this->info("Account {$account->code}: {$tradeCount} trades, {$dealCount} deals");

        if ($tradeCount > $dealCount) {
            $missing = $tradeCount - $dealCount;
            $this->warn("Account {$account->code} has {$missing} more trades than deals - syncing deals...");

            if (!$dryRun) {
                // Calculate appropriate date range for deal sync
                $oldestTrade = Trade::where('account_id', $account->id)
                    ->orderBy('open_time', 'asc')
                    ->first();

                $fromTime = $oldestTrade
                    ? Carbon::parse($oldestTrade->open_time)->subDays(1) // Start 1 day before oldest trade
                    : now()->subDays(30); // Default to 30 days if no trades found

                // Dispatch deal sync job for this account with proper date range
                DealSyncJob::dispatch([$account], [$fromTime])->onQueue('deal-sync');
                $this->info("Deal sync job dispatched for account {$account->code} (from: {$fromTime->format('Y-m-d H:i:s')})");

                // Wait a moment for the job to potentially complete
                sleep(2);
            } else {
                // Calculate date range for dry run message
                $oldestTrade = Trade::where('account_id', $account->id)
                    ->orderBy('open_time', 'asc')
                    ->first();

                $fromTime = $oldestTrade
                    ? Carbon::parse($oldestTrade->open_time)->subDays(1)
                    : now()->subDays(30);

                $this->info("DRY RUN: Would dispatch deal sync job for account {$account->code} (from: {$fromTime->format('Y-m-d H:i:s')})");
            }
        } else {
            $this->info("Account {$account->code} has sufficient deal data");
        }
    }

    /**
     * Check deal coverage across accounts
     */
    protected function checkDealCoverage(?string $accountCode = null): void
    {
        $this->info("=== DEAL COVERAGE ANALYSIS ===");

        $query = Account::query();
        if ($accountCode) {
            $query->where('code', $accountCode);
        }

        $accounts = $query->whereHas('trades')->with('trades', 'deals')->get();

        foreach ($accounts as $account) {
            $tradeCount = $account->trades->count();
            $dealCount = $account->deals->count();
            $coverage = $dealCount > 0 ? ($dealCount / max($tradeCount, 1)) * 100 : 0;

            $status = $tradeCount > $dealCount ? "⚠️  NEEDS SYNC" : "✅ OK";

            $this->line(sprintf(
                "Account %s: %d trades, %d deals (%.1f%% coverage) %s",
                $account->code,
                $tradeCount,
                $dealCount,
                $coverage,
                $status
            ));
        }
    }
}
