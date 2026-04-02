<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Trade;
use Illuminate\Console\Command;

class ResetOrphanedTradeCloseData extends Command
{
    protected $signature = 'app:reset-orphaned-trade-close-data 
                            {--account-code= : Reset specific account by code}
                            {--dry-run : Show what would be reset without making changes}';

    protected $description = 'Reset close_price and close_time to null for trades that were closed as orphaned but have no actual close data';

    public function handle(): void
    {
        $dryRun = $this->option('dry-run');
        $accountCode = $this->option('account-code');

        if ($dryRun) {
            $this->warn('DRY-RUN MODE: No changes will be made');
        }

        // Find trades where both close_price AND close_time are null
        // (These were likely closed without finding actual close data)
        $query = Trade::where('status', 'closed')
            ->whereNull('close_price')
            ->whereNull('close_time');

        if ($accountCode) {
            $account = Account::where('code', $accountCode)->first();
            if (!$account) {
                $this->error("Account with code '{$accountCode}' not found");
                return;
            }
            $query->where('account_id', $account->id);
            $this->info("Filtering to account: {$accountCode}");
        }

        $tradeCount = $query->count();

        if ($tradeCount === 0) {
            $this->info('No trades found to reset');
            return;
        }

        $this->warn("Found {$tradeCount} closed trades with null close_price and close_time");

        if (!$dryRun) {
            if (!$this->confirm('Reset these trades back to open status?')) {
                $this->info('Operation cancelled');
                return;
            }

            $updated = $query->update([
                'status' => 'open',
                'close_price' => null,
                'close_time' => null,
                'updated_at' => now(),
            ]);

            $this->info("✓ Successfully reset {$updated} trades back to open status");
        } else {
            $this->line("Would reset {$tradeCount} trades:");
            $query->limit(10)->get()->each(function ($trade) {
                $this->line("  - Position ID: {$trade->position_id}, Account: {$trade->account_id}");
            });
            if ($tradeCount > 10) {
                $this->line("  ... and " . ($tradeCount - 10) . " more");
            }
        }
    }
}
