<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\Services\DealAnalysisService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncStatusDashboardCommand extends Command
{
    protected $signature = 'app:sync-status-dashboard 
                            {--account= : Specific account code to analyze}
                            {--demo : Show demo accounts only}
                            {--live : Show live accounts only}
                            {--detailed : Show detailed analysis}';

    protected $description = 'Display intelligent sync system status and recommendations';

    protected $dealAnalysisService;

    public function __construct(DealAnalysisService $dealAnalysisService)
    {
        parent::__construct();
        $this->dealAnalysisService = $dealAnalysisService;
    }

    public function handle()
    {
        $this->info('📊 Intelligent Sync System Dashboard');
        $this->line('=========================================');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found.');
            return 1;
        }

        $this->showOverallStats($accounts);
        $this->line('');

        if ($this->option('detailed')) {
            $this->showDetailedAnalysis($accounts);
        } else {
            $this->showSummaryTable($accounts);
        }

        return 0;
    }

    protected function showOverallStats($accounts)
    {
        $totalAccounts = $accounts->count();
        $demoAccounts = $accounts->where('demo', true)->count();
        $liveAccounts = $accounts->where('demo', false)->count();

        // Deal statistics
        $totalDeals = Deal::whereIn('account_id', $accounts->pluck('id'))->count();
        $dealsLast24h = Deal::whereIn('account_id', $accounts->pluck('id'))
            ->where('time_done', '>=', now()->subDay())
            ->count();

        // Trade statistics
        $totalTrades = Trade::whereIn('account_id', $accounts->pluck('id'))->count();
        $openTrades = Trade::whereIn('account_id', $accounts->pluck('id'))
            ->where('status', 'open')
            ->count();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Accounts', $totalAccounts],
                ['Demo Accounts', $demoAccounts],
                ['Live Accounts', $liveAccounts],
                ['Total Deals', number_format($totalDeals)],
                ['Deals (24h)', number_format($dealsLast24h)],
                ['Total Trades', number_format($totalTrades)],
                ['Open Trades', number_format($openTrades)],
            ]
        );
    }

    protected function showSummaryTable($accounts)
    {
        $this->info('📋 Account Sync Analysis Summary');
        $this->line('');

        $tableData = [];
        $strategyCounts = ['deal_based' => 0, 'hybrid' => 0, 'order_based' => 0];

        foreach ($accounts as $account) {
            $analysis = $this->dealAnalysisService->analyzeSyncStrategy($account);
            $strategyCounts[$analysis['strategy']]++;

            $lastDealAge = $analysis['last_deal_time']
                ? $analysis['last_deal_time']->diffForHumans()
                : 'Never';

            $tableData[] = [
                $account->code,
                $account->demo ? 'Demo' : 'Live',
                ucfirst($analysis['strategy']),
                $analysis['deal_count_24h'],
                $analysis['incomplete_positions'],
                $lastDealAge,
                ucfirst(str_replace('_', ' ', $analysis['recommendation']))
            ];
        }

        $this->table(
            ['Account', 'Type', 'Strategy', 'Deals 24h', 'Incomplete', 'Last Deal', 'Recommendation'],
            $tableData
        );

        $this->line('');
        $this->info('📈 Strategy Distribution:');
        foreach ($strategyCounts as $strategy => $count) {
            $percentage = round(($count / $accounts->count()) * 100, 1);
            $this->line("  " . ucfirst(str_replace('_', ' ', $strategy)) . ": {$count} accounts ({$percentage}%)");
        }
    }

    protected function showDetailedAnalysis($accounts)
    {
        foreach ($accounts as $account) {
            $this->showAccountDetails($account);
            $this->line('');
        }
    }

    protected function showAccountDetails(Account $account)
    {
        $this->info("🔍 Detailed Analysis: Account {$account->code}");
        $this->line(str_repeat('-', 50));

        $analysis = $this->dealAnalysisService->analyzeSyncStrategy($account);
        $positionAnalysis = $this->dealAnalysisService->analyzePositionCompleteness($account);
        $recommendedInterval = $this->dealAnalysisService->getRecommendedSyncInterval($account);
        $needsVerification = $this->dealAnalysisService->getPositionsNeedingVerification($account);

        // Basic info
        $this->table(
            ['Property', 'Value'],
            [
                ['Account Type', $account->demo ? 'Demo' : 'Live'],
                ['Sync Status', $account->sync_status ?? 'Unknown'],
                ['Last Sync', $account->last_balance_sync_at ? Carbon::parse($account->last_balance_sync_at)->diffForHumans() : 'Never'],
                ['Last Deal Sync', $account->last_deal_sync_at ? Carbon::parse($account->last_deal_sync_at)->diffForHumans() : 'Never'],
            ]
        );

        // Strategy analysis
        $this->line('');
        $this->info('🎯 Recommended Strategy:');
        $this->line("  Strategy: " . ucfirst(str_replace('_', ' ', $analysis['strategy'])));
        $this->line("  Reason: {$analysis['reason']}");
        $this->line("  Recommendation: " . ucfirst(str_replace('_', ' ', $analysis['recommendation'])));
        $this->line("  Sync Interval: Every {$recommendedInterval} minutes");

        // Deal statistics
        $this->line('');
        $this->info('📊 Deal Statistics:');
        $this->table(
            ['Period', 'Count'],
            [
                ['Last 24 hours', $analysis['deal_count_24h']],
                ['Last 7 days', $analysis['deal_count_7d']],
                ['Last deal time', $analysis['last_deal_time'] ? $analysis['last_deal_time']->format('Y-m-d H:i:s') : 'Never'],
            ]
        );

        // Position analysis
        $this->line('');
        $this->info('🎲 Position Analysis:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Deal Positions', $positionAnalysis['deal_positions']],
                ['Trade Positions', $positionAnalysis['trade_positions']],
                ['Missing in Trades', $positionAnalysis['missing']],
                ['Orphaned in Trades', $positionAnalysis['orphaned']],
                ['Incomplete Positions', $positionAnalysis['incomplete']],
            ]
        );

        // Verification needed
        if ($needsVerification->isNotEmpty()) {
            $this->line('');
            $this->warn('⚠️  Positions Needing Verification:');
            foreach ($needsVerification->take(5) as $issue) {
                $this->line("  Position {$issue['position_id']}: {$issue['reason']}");
            }
            if ($needsVerification->count() > 5) {
                $this->line("  ... and " . ($needsVerification->count() - 5) . " more");
            }
        }
    }

    protected function getAccounts()
    {
        $query = Account::query();

        if ($this->option('account')) {
            $query->where('code', $this->option('account'));
        }

        if ($this->option('demo')) {
            $query->where('demo', true);
        } elseif ($this->option('live')) {
            $query->where('demo', false);
        }

        return $query->orderBy('code')->get();
    }
}
