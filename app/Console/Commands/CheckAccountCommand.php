<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use Illuminate\Console\Command;

class CheckAccountCommand extends Command
{
    protected $signature = 'app:check-account {code : Account code to check}';
    protected $description = 'Check account data status before cleanup';

    public function handle()
    {
        $accountCode = $this->argument('code');
        $account = Account::where('code', $accountCode)->first();

        if (!$account) {
            $this->error("Account {$accountCode} not found!");

            // Search for similar accounts
            $this->info('Searching for similar accounts...');
            $similarAccounts = Account::where('code', 'LIKE', "%{$accountCode}%")->limit(10)->get();

            if ($similarAccounts->count() > 0) {
                $this->table(
                    ['Code', 'ID', 'Demo', 'Balance'],
                    $similarAccounts->map(fn($acc) => [
                        $acc->code,
                        $acc->id,
                        $acc->demo ? 'YES' : 'NO',
                        $acc->balance
                    ])->toArray()
                );
            } else {
                $this->warn('No similar accounts found.');
            }
            return 1;
        }

        $this->info("=== ACCOUNT {$accountCode} CURRENT STATE ===");
        $this->line("Account ID: {$account->id}");
        $this->line("Account Code: {$account->code}");
        $this->line("Demo: " . ($account->demo ? 'YES' : 'NO'));
        $this->line("Current Balance: {$account->balance}");
        $this->line("Platform: {$account->platform}");

        // Check current data counts
        $dealsCount = Deal::where('account_id', $account->id)->count();
        $tradesCount = Trade::where('account_id', $account->id)->count();

        $this->info("\n=== DATA COUNTS ===");
        $this->line("Total Deals: {$dealsCount}");
        $this->line("Total Trades: {$tradesCount}");

        // Check sync status
        $this->info("\n=== SYNC STATUS ===");
        $this->line("Sync Status: " . ($account->sync_status ?? 'NULL'));
        $this->line("Last Synced: " . ($account->deals_last_fetch_at ?? 'Never'));
        $this->line("Deals Synced From: " . ($account->deals_synced_from ?? 'NULL'));
        $this->line("Deals Synced To: " . ($account->deals_synced_to ?? 'NULL'));
        $this->line("Has Balance Activity: " . ($account->has_balance_activity ? 'YES' : 'NO'));

        // Check recent deals if any
        if ($dealsCount > 0) {
            $latestDeal = Deal::where('account_id', $account->id)
                ->orderBy('time_done', 'desc')
                ->first();
            $oldestDeal = Deal::where('account_id', $account->id)
                ->orderBy('time_done', 'asc')
                ->first();

            $this->info("\n=== DEAL TIME RANGE ===");
            $this->line("Oldest Deal: " . ($oldestDeal ? $oldestDeal->time_done : 'None'));
            $this->line("Latest Deal: " . ($latestDeal ? $latestDeal->time_done : 'None'));
        }

        // Check recent trades if any
        if ($tradesCount > 0) {
            $latestTrade = Trade::where('account_id', $account->id)
                ->orderBy('open_time', 'desc')
                ->first();
            $oldestTrade = Trade::where('account_id', $account->id)
                ->orderBy('open_time', 'asc')
                ->first();

            $this->info("\n=== TRADE TIME RANGE ===");
            $this->line("Oldest Trade: " . ($oldestTrade ? $oldestTrade->open_time : 'None'));
            $this->line("Latest Trade: " . ($latestTrade ? $latestTrade->open_time : 'None'));
        }

        $this->info("\n=== READY FOR CLEANUP TEST ===");
        $this->comment("Account {$accountCode} analysis complete!");

        return 0;
    }
}
