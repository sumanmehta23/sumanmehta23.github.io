<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageNotFoundAccountsCommand extends Command
{
    protected $signature = 'app:manage-not-found-accounts 
                            {--list : List all accounts marked as not_found_in_mt5}
                            {--stats : Show statistics about not found accounts}
                            {--mark= : Mark specific account as not_found_in_mt5}
                            {--unmark= : Unmark specific account (reset to pending)}
                            {--unmark-all : Reset all not_found_in_mt5 accounts to pending}
                            {--dry-run : Show what would be done without making changes}';

    protected $description = 'Manage accounts marked as not found in MT5 server';

    public function handle()
    {
        if ($this->option('list')) {
            $this->listNotFoundAccounts();
        } elseif ($this->option('stats')) {
            $this->showStats();
        } elseif ($this->option('mark')) {
            $this->markAccount($this->option('mark'));
        } elseif ($this->option('unmark')) {
            $this->unmarkAccount($this->option('unmark'));
        } elseif ($this->option('unmark-all')) {
            $this->unmarkAllAccounts();
        } else {
            $this->showStats();
            $this->info("\nUse --help to see available options.");
        }

        return 0;
    }

    private function listNotFoundAccounts(): void
    {
        $accounts = Account::where('sync_status', 'not_found_in_mt5')
            ->select('code', 'created_at', 'sync_flagged_at', 'sync_flag_reason')
            ->orderBy('sync_flagged_at', 'desc')
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('✅ No accounts are currently marked as not_found_in_mt5');
            return;
        }

        $this->info("📋 Accounts marked as not_found_in_mt5 ({$accounts->count()}):");
        $this->newLine();

        $tableData = [];
        foreach ($accounts as $account) {
            $tableData[] = [
                $account->code,
                $account->created_at->format('M j, Y H:i'),
                $account->sync_flagged_at ? $account->sync_flagged_at->format('M j, Y H:i') : 'N/A',
                $account->sync_flag_reason ?: 'N/A'
            ];
        }

        $this->table(
            ['Account Code', 'Created', 'Flagged At', 'Reason'],
            $tableData
        );
    }

    private function showStats(): void
    {
        $this->info('📊 Not Found Accounts Statistics');
        $this->line('=====================================');

        $notFoundCount = Account::where('sync_status', 'not_found_in_mt5')->count();
        $totalAccounts = Account::whereNotNull('code')->count();
        $liveAccounts = Account::whereNotNull('code')->where('demo', false)->count();
        $excludedFromBalance = Account::whereNotNull('code')
            ->where('demo', false)
            ->where('sync_status', 'not_found_in_mt5')
            ->count();

        $percentage = $totalAccounts > 0 ? round(($notFoundCount / $totalAccounts) * 100, 2) : 0;

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Accounts (with code)', $totalAccounts],
                ['Live Accounts', $liveAccounts],
                ['Not Found in MT5', $notFoundCount],
                ['Excluded from Balance Sync', $excludedFromBalance],
                ['Percentage Not Found', $percentage . '%']
            ]
        );

        if ($notFoundCount > 0) {
            $this->newLine();
            $this->warn("⚠️  {$notFoundCount} accounts are excluded from sync operations");
            $this->info("💡 Use --list to see which accounts are marked");
            $this->info("💡 Use --unmark-all to reset all accounts (with caution)");
        } else {
            $this->newLine();
            $this->info("✅ All accounts are eligible for sync operations");
        }
    }

    private function markAccount(string $accountCode): void
    {
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("❌ Account {$accountCode} not found in database");
            return;
        }

        if ($account->sync_status === 'not_found_in_mt5') {
            $this->info("ℹ️  Account {$accountCode} is already marked as not_found_in_mt5");
            return;
        }

        if ($this->option('dry-run')) {
            $this->info("🔍 DRY RUN: Would mark account {$accountCode} as not_found_in_mt5");
            return;
        }

        $account->update([
            'sync_status' => 'not_found_in_mt5',
            'sync_error' => 'Manually marked as not found in MT5',
            'sync_flagged_at' => now(),
            'sync_flag_reason' => 'Manually marked via command'
        ]);

        $this->info("✅ Account {$accountCode} marked as not_found_in_mt5");
        Log::info("Account {$accountCode} manually marked as not_found_in_mt5");
    }

    private function unmarkAccount(string $accountCode): void
    {
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("❌ Account {$accountCode} not found in database");
            return;
        }

        if ($account->sync_status !== 'not_found_in_mt5') {
            $this->info("ℹ️  Account {$accountCode} is not marked as not_found_in_mt5 (current: {$account->sync_status})");
            return;
        }

        if ($this->option('dry-run')) {
            $this->info("🔍 DRY RUN: Would reset account {$accountCode} to pending status");
            return;
        }

        $account->update([
            'sync_status' => 'pending',
            'sync_error' => null,
            'sync_flagged_at' => null,
            'sync_flag_reason' => null
        ]);

        $this->info("✅ Account {$accountCode} reset to pending status");
        Log::info("Account {$accountCode} reset from not_found_in_mt5 to pending");
    }

    private function unmarkAllAccounts(): void
    {
        $accounts = Account::where('sync_status', 'not_found_in_mt5')->get();

        if ($accounts->isEmpty()) {
            $this->info('ℹ️  No accounts are currently marked as not_found_in_mt5');
            return;
        }

        $count = $accounts->count();

        if ($this->option('dry-run')) {
            $this->info("🔍 DRY RUN: Would reset {$count} accounts from not_found_in_mt5 to pending");
            return;
        }

        $this->warn("⚠️  This will reset {$count} accounts from not_found_in_mt5 to pending status");

        if (!$this->confirm('Are you sure you want to continue?')) {
            $this->info('Operation cancelled');
            return;
        }

        foreach ($accounts as $account) {
            $account->update([
                'sync_status' => 'pending',
                'sync_error' => null,
                'sync_flagged_at' => null,
                'sync_flag_reason' => null
            ]);
        }

        $this->info("✅ Reset {$count} accounts from not_found_in_mt5 to pending");
        Log::info("Bulk reset {$count} accounts from not_found_in_mt5 to pending");
    }
}
