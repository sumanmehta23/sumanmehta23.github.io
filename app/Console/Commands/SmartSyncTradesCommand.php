<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Deal;
use App\Models\Trade;
use App\Jobs\PositionSyncJob;
use App\Jobs\DealSyncJob;
use App\Jobs\EnhancedBatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SmartSyncTradesCommand extends Command
{
    protected $signature = 'app:smart-sync-trades 
                            {--account= : Specific account code to sync}
                            {--demo : Sync demo accounts only}
                            {--live : Sync live accounts only}
                            {--batch-size=5 : Number of accounts per batch}
                            {--strategy= : Force sync strategy: position, deal, order, auto}
                            {--sync-open-positions : Sync open positions using Position API}
                            {--sync-closed-since= : Sync closed positions since date (Y-m-d)}
                            {--from-days=7 : Days to sync from}
                            {--dry-run : Show what would be synced without executing}';

    protected $description = 'Smart trade sync that automatically chooses the best strategy: Position API, Deal-based, or Order-based';

    public function handle()
    {
        $this->info('🧠 Starting Smart Trade Sync...');
        $this->info('Analyzing accounts and choosing optimal sync strategies...');

        $accounts = $this->getAccounts();

        if ($accounts->isEmpty()) {
            $this->error('No accounts found to sync.');
            return 1;
        }

        $strategy = $this->option('strategy') ?: 'auto';
        $batchSize = $this->option('batch-size');
        $dryRun = $this->option('dry-run');

        $this->info("Found {$accounts->count()} accounts to sync.");
        $this->info("Strategy: " . ucfirst($strategy));
        $this->info("Batch size: {$batchSize}");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No actual syncing will be performed');
        }

        // Analyze accounts and group by optimal strategy
        $strategyGroups = $this->analyzeAndGroupAccounts($accounts, $strategy);

        $this->displayStrategyAnalysis($strategyGroups);

        if ($dryRun) {
            $this->info('✅ Dry run completed. Use without --dry-run to execute.');
            return 0;
        }

        // Execute sync strategies
        $this->executeSyncStrategies($strategyGroups, $batchSize);

        $this->info('✅ Smart sync process completed!');
        return 0;
    }

    protected function analyzeAndGroupAccounts($accounts, string $forcedStrategy): array
    {
        $groups = [
            'position' => collect(),
            'deal' => collect(),
            'order' => collect()
        ];

        foreach ($accounts as $account) {
            $recommendedStrategy = $this->analyzeAccountStrategy($account, $forcedStrategy);
            $groups[$recommendedStrategy]->push($account);
        }

        return $groups;
    }

    protected function analyzeAccountStrategy(Account $account, string $forcedStrategy): string
    {
        if ($forcedStrategy !== 'auto') {
            return $forcedStrategy;
        }

        // Strategy selection logic based on data availability and quality

        // 1. Position API is always preferred for open positions if available
        // We'll check this by seeing if we can get position data efficiently

        // 2. Check deal data freshness for closed positions
        $recentDealsCount = Deal::where('account_id', $account->id)
            ->where('time_done', '>=', now()->subHours(6))
            ->count();

        $totalDealsCount = Deal::where('account_id', $account->id)->count();

        // 3. Check existing trade data completeness
        $existingTradesCount = Trade::where('account_id', $account->id)->count();

        // Strategy decision logic:

        // If we have very recent deal activity and good coverage, use deal-based
        if ($recentDealsCount > 10 && $totalDealsCount > 100) {
            return 'deal';
        }

        // If we have some deal data but not very recent, use position API
        if ($totalDealsCount > 50) {
            return 'position';
        }

        // If minimal data exists, use order-based (most comprehensive but slowest)
        return 'order';
    }

    protected function displayStrategyAnalysis(array $strategyGroups): void
    {
        $this->info('📊 Strategy Analysis Results:');
        $this->line('');

        $tableData = [];
        $totalAccounts = 0;

        foreach ($strategyGroups as $strategy => $accounts) {
            $count = $accounts->count();
            $totalAccounts += $count;

            if ($count > 0) {
                $efficiency = $this->getStrategyEfficiency($strategy);
                $description = $this->getStrategyDescription($strategy);

                $tableData[] = [
                    ucfirst($strategy) . ' API',
                    $count,
                    $efficiency,
                    $description
                ];

                // Show sample accounts for each strategy
                $sampleAccounts = $accounts->take(3)->pluck('code')->join(', ');
                if ($accounts->count() > 3) {
                    $sampleAccounts .= ', ...';
                }
                $this->line("  📋 {$strategy} accounts: {$sampleAccounts}");
            }
        }

        $this->line('');
        $this->table(
            ['Strategy', 'Accounts', 'Efficiency', 'Description'],
            $tableData
        );

        $this->line('');
        $this->info("Total accounts to sync: {$totalAccounts}");
    }

    protected function getStrategyEfficiency(string $strategy): string
    {
        return match ($strategy) {
            'position' => '⚡ Fastest',
            'deal' => '🏃 Fast',
            'order' => '🐌 Comprehensive',
            default => '❓ Unknown'
        };
    }

    protected function getStrategyDescription(string $strategy): string
    {
        return match ($strategy) {
            'position' => 'Direct position API calls - most efficient',
            'deal' => 'Deal-based reconstruction - good accuracy',
            'order' => 'Order+Deal combination - complete but slower',
            default => 'Unknown strategy'
        };
    }

    protected function executeSyncStrategies(array $strategyGroups, int $batchSize): void
    {
        $allJobs = [];

        // 1. Execute Position-based sync
        if ($strategyGroups['position']->isNotEmpty()) {
            $this->info('🎯 Executing Position-based sync...');
            $jobs = $this->createPositionSyncJobs($strategyGroups['position'], $batchSize);
            $allJobs = array_merge($allJobs, $jobs);
        }

        // 2. Execute Deal-based sync  
        if ($strategyGroups['deal']->isNotEmpty()) {
            $this->info('📊 Executing Deal-based sync...');
            $jobs = $this->createDealBasedSyncJobs($strategyGroups['deal'], $batchSize);
            $allJobs = array_merge($allJobs, $jobs);
        }

        // 3. Execute Order-based sync
        if ($strategyGroups['order']->isNotEmpty()) {
            $this->info('📋 Executing Order-based sync...');
            $jobs = $this->createOrderBasedSyncJobs($strategyGroups['order'], $batchSize);
            $allJobs = array_merge($allJobs, $jobs);
        }

        // Dispatch all jobs as a single batch
        if (!empty($allJobs)) {
            $batch = Bus::batch($allJobs)
                ->then(function () {
                    Log::info('All smart sync jobs completed successfully.');
                })
                ->catch(function (\Throwable $e) {
                    Log::error('Smart sync batch failed: ' . $e->getMessage());
                })
                ->name('Smart Trade Sync Batch')
                ->dispatch();

            $this->info("Smart sync batch created with ID: {$batch->id}");
            $this->info("Dispatched " . count($allJobs) . " jobs total.");
        }
    }

    protected function createPositionSyncJobs($accounts, int $batchSize): array
    {
        $jobs = [];
        $accountBatches = $accounts->chunk($batchSize);

        $syncOpenPositions = $this->option('sync-open-positions') ?? true;
        $closedSince = $this->getClosedSinceDate();

        foreach ($accountBatches as $batch) {
            $job = new PositionSyncJob($batch->all(), !$closedSince, $closedSince);
            $jobs[] = $job;
        }

        return $jobs;
    }

    protected function createDealBasedSyncJobs($accounts, int $batchSize): array
    {
        $jobs = [];
        $accountBatches = $accounts->chunk($batchSize);

        foreach ($accountBatches as $batch) {
            // First sync deals, then reconstruct trades
            $dealJob = new DealSyncJob($batch->all(), [], false); // Incremental deal sync
            $tradeJob = new EnhancedBatchSyncTradesJob($batch->all(), [], null, null, true); // Deal-based trade sync

            $jobs[] = $dealJob;
            $jobs[] = $tradeJob;
        }

        return $jobs;
    }

    protected function createOrderBasedSyncJobs($accounts, int $batchSize): array
    {
        $jobs = [];
        $accountBatches = $accounts->chunk($batchSize);

        foreach ($accountBatches as $batch) {
            $fromDays = $this->option('from-days');
            $fromTime = now()->subDays($fromDays);
            $fromTimes = array_fill(0, $batch->count(), $fromTime);

            $job = new EnhancedBatchSyncTradesJob($batch->all(), $fromTimes, null, null, false); // Order-based sync
            $jobs[] = $job;
        }

        return $jobs;
    }

    protected function getClosedSinceDate(): ?Carbon
    {
        if ($this->option('sync-closed-since')) {
            try {
                return Carbon::createFromFormat('Y-m-d', $this->option('sync-closed-since'))->startOfDay();
            } catch (\Exception $e) {
                $this->error("Invalid date format for --sync-closed-since. Use Y-m-d format.");
                return null;
            }
        }

        $fromDays = $this->option('from-days');
        return now()->subDays($fromDays);
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

        // Only get accounts that are not currently syncing
        $query->whereNotIn('sync_status', ['syncing', 'pending']);

        return $query->orderBy('code')->get();
    }
}
