<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SetInitialBalanceStatus extends Command
{
    protected $signature = 'app:set-initial-balance-status 
                            {--dry-run : Show what would be updated without making changes}
                            {--batch-size=1000 : Number of accounts to process per batch}
                            {--account-codes= : Comma-separated list of specific account codes to process}';

    protected $description = 'Set initial balance status for existing accounts based on historical activity';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $batchSize = (int) $this->option('batch-size');
        $specificCodes = $this->option('account-codes');

        $this->info('🔄 Setting Initial Balance Status for Existing Accounts');
        $this->line('=========================================================');

        if ($isDryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $this->newLine();

        // Build account query
        $query = Account::whereNotNull('code');

        if ($specificCodes) {
            $codes = array_map('trim', explode(',', $specificCodes));
            $query->whereIn('code', $codes);
            $this->info('Processing specific accounts: ' . implode(', ', $codes));
        }

        $totalAccounts = $query->count();
        $this->info("Total accounts to process: {$totalAccounts}");
        $this->newLine();

        $processed = 0;
        $updated = 0;
        $errors = 0;

        // Process in batches
        $query->chunk($batchSize, function ($accounts) use ($isDryRun, &$processed, &$updated, &$errors) {
            foreach ($accounts as $account) {
                try {
                    $result = $this->processAccount($account, $isDryRun);
                    $processed++;

                    if ($result['updated']) {
                        $updated++;
                        $this->line("✓ {$account->code}: {$result['reason']}");
                    } else {
                        $this->line("- {$account->code}: No activity found");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("✗ {$account->code}: {$e->getMessage()}");
                    Log::error("Initial balance status error for account {$account->code}: " . $e->getMessage());
                }

                // Progress indicator
                if ($processed % 100 === 0) {
                    $this->info("Progress: {$processed} accounts processed, {$updated} updated, {$errors} errors");
                }
            }
        });

        $this->newLine();
        $this->info("📊 Summary:");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Accounts Processed', $processed],
                ['Accounts Updated', $updated],
                ['Errors', $errors],
                ['Success Rate', $processed > 0 ? round(($processed - $errors) / $processed * 100, 2) . '%' : '0%']
            ]
        );

        if ($isDryRun) {
            $this->warn('This was a dry run. Use without --dry-run to apply changes.');
        } else {
            $this->info('✅ Initial balance status setting completed!');
        }

        return $errors > 0 ? 1 : 0;
    }

    private function processAccount(Account $account, bool $isDryRun): array
    {
        $lastBalanceChange = null;
        $hasActivity = false;
        $reason = '';

        // Check for balance activity in multiple sources
        $activitySources = [
            'trade_deposits' => $this->getLatestTradeDeposit($account->id),
            'trade_withdrawals' => $this->getLatestTradeWithdrawal($account->id),
            'bonus_transactions' => $this->getLatestBonusTransaction($account->id),
            'trades' => $this->getLatestTrade($account->id)
        ];

        // Find the most recent activity
        foreach ($activitySources as $source => $timestamp) {
            if ($timestamp && (!$lastBalanceChange || $timestamp > $lastBalanceChange)) {
                $lastBalanceChange = $timestamp;
                $hasActivity = true;
                $reason = "Last activity from {$source}: {$timestamp->format('Y-m-d H:i:s')}";
            }
        }

        // If no activity found, check account creation date
        if (!$hasActivity) {
            $lastBalanceChange = $account->created_at;
            $reason = "No activity found, using creation date: {$lastBalanceChange->format('Y-m-d H:i:s')}";
        }

        // Update account if not dry run
        if (!$isDryRun) {
            $account->update([
                'last_balance_changed_at' => $lastBalanceChange,
                'has_balance_activity' => $hasActivity
            ]);
        }

        return [
            'updated' => true,
            'has_activity' => $hasActivity,
            'last_change' => $lastBalanceChange,
            'reason' => $reason
        ];
    }

    private function getLatestTradeDeposit(int $accountId): ?\Carbon\Carbon
    {
        $result = DB::table('trade_deposits')
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->first(['created_at']);

        return $result ? \Carbon\Carbon::parse($result->created_at) : null;
    }

    private function getLatestTradeWithdrawal(int $accountId): ?\Carbon\Carbon
    {
        $result = DB::table('trade_withdrawals')
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->first(['created_at']);

        return $result ? \Carbon\Carbon::parse($result->created_at) : null;
    }

    private function getLatestBonusTransaction(int $accountId): ?\Carbon\Carbon
    {
        $result = DB::table('bonus_transactions')
            ->where('account_id', $accountId)
            ->orderBy('created_at', 'desc')
            ->first(['created_at']);

        return $result ? \Carbon\Carbon::parse($result->created_at) : null;
    }

    private function getLatestTrade(int $accountId): ?\Carbon\Carbon
    {
        $result = DB::table('trades')
            ->where('account_id', $accountId)
            ->whereIn('status', ['open', 'closed'])
            ->orderBy('updated_at', 'desc')
            ->first(['updated_at']);

        return $result ? \Carbon\Carbon::parse($result->updated_at) : null;
    }
}
