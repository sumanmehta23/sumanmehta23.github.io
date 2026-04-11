<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Trade;
use App\Services\MT5RestAPIService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class IdentifyAndResetProblematicTradeSync extends Command
{
    protected $signature = 'app:identify-problematic-trade-sync 
                            {--fix : Actually reset status to partial (dry-run by default)}
                            {--limit=100 : Maximum accounts to check (default: 100)}';

    protected $description = 'Find accounts with success status but 0 open trades in MT5 REST API yet have open trades in DB. Reset to partial for re-verification.';

    protected $restApiService;

    public function handle(): void
    {
        $this->info('Identifying problematic trade sync accounts...');

        try {
            $this->restApiService = app(MT5RestAPIService::class);
            $fix = $this->option('fix');
            $limit = (int) $this->option('limit');

            if (!$fix) {
                $this->warn('DRY RUN MODE: Use --fix flag to actually reset accounts');
            }

            // Find accounts with success status
            $accounts = Account::where('demo', false)
                ->where('account_request_status', 1)
                ->where('trade_sync_status', 'success')
                ->whereNull('deleted_at')
                ->limit($limit)
                ->get();

            $this->info("Found {$accounts->count()} accounts with success status to check.");

            $problematic = 0;
            $resetCount = 0;

            foreach ($accounts as $account) {
                // Check if account has open trades in DB
                $openTradeCount = Trade::where('account_id', $account->id)
                    ->where('status', 'open')
                    ->count();

                if ($openTradeCount === 0) {
                    // No open trades in DB, skip
                    continue;
                }

                // Get open positions from MT5 REST API
                $mt5OpenPositions = $this->restApiService->getOpenPositions($account->code);

                if (count($mt5OpenPositions) === 0) {
                    // Found problematic account: has open trades in DB but 0 in MT5
                    $problematic++;

                    $this->line("");
                    $this->warn("PROBLEMATIC: Account {$account->code}");
                    $this->line("  DB open trades: {$openTradeCount}");
                    $this->line("  MT5 open positions: 0");

                    // List the open trade positions
                    $openPositions = Trade::where('account_id', $account->id)
                        ->where('status', 'open')
                        ->pluck('position_id')
                        ->toArray();

                    $this->line("  Open position IDs in DB: " . implode(', ', $openPositions));

                    if ($fix) {
                        // Reset to partial for re-verification
                        $account->update([
                            'trade_sync_status' => 'partial',
                            'last_trade_sync_position' => 0,
                        ]);
                        $resetCount++;
                        $this->info("  ✓ Status reset to 'partial' for re-sync");

                        Log::info("Trade sync status reset for problematic account", [
                            'account_id' => $account->id,
                            'account_code' => $account->code,
                            'db_open_trades' => $openTradeCount,
                            'mt5_open_positions' => 0,
                        ]);
                    } else {
                        $this->line("  [DRY RUN] Would reset status to 'partial'");
                    }
                }
            }

            // Print summary
            $this->line("\n" . str_repeat('=', 50));
            $this->info("Scan Summary:");
            $this->line("Accounts checked: {$accounts->count()}");
            $this->line("Problematic accounts found: {$problematic}");
            if ($fix) {
                $this->line("Accounts reset to partial: {$resetCount}");
            } else {
                $this->line("Accounts that would be reset: {$problematic}");
                $this->line("\nRun with --fix flag to actually reset these accounts:");
                $this->line("php artisan app:identify-problematic-trade-sync --fix");
            }
            $this->line(str_repeat('=', 50) . "\n");

            if ($fix && $resetCount > 0) {
                $this->info("✓ {$resetCount} account(s) reset to 'partial' status");
                $this->info("Run 'php artisan app:sync-live-accounts-trades' to re-verify and close ghost trades");
            }
        } catch (\Exception $e) {
            $this->error("Failed to execute command: {$e->getMessage()}");
            Log::error("IdentifyAndResetProblematicTradeSync command failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
