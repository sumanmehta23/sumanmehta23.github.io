<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Jobs\BatchSyncTradesJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PerformanceTestBatchSync extends Command
{
    protected $signature = 'app:test-batch-performance 
                            {--accounts=3 : Number of accounts to test}
                            {--codes= : Specific account codes to test (comma-separated)}
                            {--days=7 : Number of days to sync back}';

    protected $description = 'Test BatchSyncTradesJob performance with detailed debugging';

    public function handle()
    {
        $accountCount = (int) $this->option('accounts');
        $specificCodes = $this->option('codes');
        $days = (int) $this->option('days');

        $this->info("🧪 BatchSyncTradesJob Performance Test");
        $this->line("======================================");

        if ($specificCodes) {
            $codes = array_map('trim', explode(',', $specificCodes));
            $accounts = Account::whereIn('code', $codes)->get();
            $this->info("Testing specific accounts: " . implode(', ', $codes));
        } else {
            $accounts = Account::where('demo', false)
                ->whereNotNull('code')
                ->inRandomOrder()
                ->limit($accountCount)
                ->get();
            $this->info("Testing {$accountCount} random accounts");
        }

        if ($accounts->isEmpty()) {
            $this->error("No accounts found for testing");
            return 1;
        }

        $this->info("Accounts to test: " . $accounts->pluck('code')->join(', '));
        $this->line("Sync period: Last {$days} days");
        $this->newLine();

        // Set up detailed logging
        $this->info("📊 Starting performance test...");
        $this->info("Monitor logs for detailed PERF[ACCOUNT] entries");

        // Create fromTimes array for consistent testing
        $fromTimes = array_fill(0, $accounts->count(), now()->subDays($days));

        // Execute the job
        $startTime = microtime(true);
        $job = new BatchSyncTradesJob($accounts->toArray(), $fromTimes);

        try {
            $job->handle(app(\App\Services\UniversalMT5Service::class));
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            $this->newLine();
            $this->info("✅ Performance test completed in {$totalTime}ms");
            $this->info("📈 Average per account: " . round($totalTime / $accounts->count(), 2) . "ms");

            $this->newLine();
            $this->info("📋 Analysis:");
            $this->line("1. Check application logs for PERF[ACCOUNT] entries");
            $this->line("2. Look for 'PERF_BREAKDOWN' JSON summary");
            $this->line("3. Identify bottlenecks (API/DB/Processing)");

            if ($totalTime / $accounts->count() > 1000) {
                $this->warn("⚠️  Average > 1000ms per account indicates performance issues");
                $this->line("Common causes:");
                $this->line("- Large historical data (check HistoryGetPage times)");
                $this->line("- Database query performance (check existing trades query)");
                $this->line("- MT5 server response times (check API call times)");
            } else {
                $this->info("✅ Performance looks good (< 1000ms per account)");
            }
        } catch (\Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->error("❌ Performance test failed after {$totalTime}ms");
            $this->error("Error: " . $e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info("🔍 Next Steps:");
        $this->line("1. Review performance logs to identify slowest accounts");
        $this->line("2. Check if specific accounts consistently perform poorly");
        $this->line("3. Analyze the breakdown of API vs DB vs Processing time");
        $this->line("4. Use the findings to optimize the specific bottleneck");

        return 0;
    }
}
