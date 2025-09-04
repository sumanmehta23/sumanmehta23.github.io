<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AnalyzeBatchPerformance extends Command
{
    protected $signature = 'app:analyze-batch-performance 
                            {--file= : Specific log file to analyze}
                            {--lines=1000 : Number of recent log lines to analyze}
                            {--accounts= : Filter by specific account codes}';

    protected $description = 'Analyze BatchSyncTradesJob performance logs to identify bottlenecks';

    public function handle()
    {
        $logFile = $this->option('file') ?: storage_path('logs/laravel.log');
        $lines = (int) $this->option('lines');
        $accountFilter = $this->option('accounts');

        $this->info("📊 BatchSyncTradesJob Performance Analysis");
        $this->line("========================================");

        if (!File::exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return 1;
        }

        $this->info("Analyzing: {$logFile}");
        $this->info("Lines: {$lines}");
        if ($accountFilter) {
            $this->info("Account filter: {$accountFilter}");
        }
        $this->newLine();

        // Read recent log lines
        $logContent = $this->getRecentLogLines($logFile, $lines);

        // Extract performance data
        $performanceData = $this->extractPerformanceData($logContent, $accountFilter);

        if (empty($performanceData)) {
            $this->warn("No performance data found in recent logs");
            $this->line("Make sure you've run BatchSyncTradesJob recently");
            return 1;
        }

        // Analyze the data
        $this->analyzePerformance($performanceData);

        return 0;
    }

    private function getRecentLogLines(string $logFile, int $lines): string
    {
        $command = "tail -{$lines} " . escapeshellarg($logFile);
        return shell_exec($command) ?: '';
    }

    private function extractPerformanceData(string $logContent, ?string $accountFilter): array
    {
        $lines = explode("\n", $logContent);
        $data = [];

        foreach ($lines as $line) {
            // Extract individual account performance
            if (preg_match('/PERF\[([^\]]+)\]: (\d+)ms total \| API: (\d+)ms \((\d+) calls\) \| DB: (\d+)ms \| Processing: (\d+)ms \| Orders: (\d+), Deals: (\d+), Trades: (\d+)/', $line, $matches)) {
                $account = $matches[1];

                if ($accountFilter && strpos($account, $accountFilter) === false) {
                    continue;
                }

                $data[] = [
                    'account' => $account,
                    'total_ms' => (int) $matches[2],
                    'api_ms' => (int) $matches[3],
                    'api_calls' => (int) $matches[4],
                    'db_ms' => (int) $matches[5],
                    'processing_ms' => (int) $matches[6],
                    'orders' => (int) $matches[7],
                    'deals' => (int) $matches[8],
                    'trades' => (int) $matches[9],
                ];
            }
        }

        return $data;
    }

    private function analyzePerformance(array $data): void
    {
        $this->info("📈 Performance Analysis Results");
        $this->line("Found " . count($data) . " account sync records");
        $this->newLine();

        // Calculate statistics
        $totalTimes = array_column($data, 'total_ms');
        $apiTimes = array_column($data, 'api_ms');
        $dbTimes = array_column($data, 'db_ms');
        $processingTimes = array_column($data, 'processing_ms');

        // Overall statistics
        $this->table(
            ['Metric', 'Min', 'Max', 'Average', 'Median'],
            [
                ['Total Time (ms)', min($totalTimes), max($totalTimes), round(array_sum($totalTimes) / count($totalTimes), 2), $this->median($totalTimes)],
                ['API Time (ms)', min($apiTimes), max($apiTimes), round(array_sum($apiTimes) / count($apiTimes), 2), $this->median($apiTimes)],
                ['DB Time (ms)', min($dbTimes), max($dbTimes), round(array_sum($dbTimes) / count($dbTimes), 2), $this->median($dbTimes)],
                ['Processing (ms)', min($processingTimes), max($processingTimes), round(array_sum($processingTimes) / count($processingTimes), 2), $this->median($processingTimes)],
            ]
        );

        $this->newLine();

        // Identify bottlenecks
        $avgTotal = array_sum($totalTimes) / count($totalTimes);
        $avgApi = array_sum($apiTimes) / count($apiTimes);
        $avgDb = array_sum($dbTimes) / count($dbTimes);
        $avgProcessing = array_sum($processingTimes) / count($processingTimes);

        $this->info("🔍 Bottleneck Analysis:");

        $apiPercentage = round(($avgApi / $avgTotal) * 100, 1);
        $dbPercentage = round(($avgDb / $avgTotal) * 100, 1);
        $processingPercentage = round(($avgProcessing / $avgTotal) * 100, 1);

        $this->line("API calls: {$apiPercentage}% of total time ({$avgApi}ms avg)");
        $this->line("Database: {$dbPercentage}% of total time ({$avgDb}ms avg)");
        $this->line("Processing: {$processingPercentage}% of total time ({$avgProcessing}ms avg)");

        $this->newLine();

        // Recommendations
        if ($apiPercentage > 60) {
            $this->warn("🚨 PRIMARY BOTTLENECK: MT5 API calls ({$apiPercentage}%)");
            $this->line("Recommendations:");
            $this->line("- Check MT5 server performance");
            $this->line("- Consider connection pooling optimization");
            $this->line("- Implement data pagination for large accounts");
            $this->line("- Add retry logic with exponential backoff");
        } elseif ($dbPercentage > 40) {
            $this->warn("🚨 PRIMARY BOTTLENECK: Database operations ({$dbPercentage}%)");
            $this->line("Recommendations:");
            $this->line("- Check database indexes on trades table");
            $this->line("- Optimize upsert batch sizes");
            $this->line("- Consider read replicas for heavy queries");
            $this->line("- Monitor database connection pool");
        } elseif ($processingPercentage > 30) {
            $this->warn("🚨 PRIMARY BOTTLENECK: Data processing ({$processingPercentage}%)");
            $this->line("Recommendations:");
            $this->line("- Optimize data transformation logic");
            $this->line("- Reduce Laravel collection overhead");
            $this->line("- Profile memory usage during processing");
            $this->line("- Consider background processing for complex calculations");
        } else {
            $this->info("✅ Performance distribution looks balanced");
        }

        $this->newLine();

        // Show slowest accounts
        usort($data, fn($a, $b) => $b['total_ms'] <=> $a['total_ms']);
        $slowestAccounts = array_slice($data, 0, 5);

        $this->info("🐌 Slowest Accounts:");
        $tableData = [];
        foreach ($slowestAccounts as $account) {
            $tableData[] = [
                $account['account'],
                $account['total_ms'] . 'ms',
                $account['api_ms'] . 'ms',
                $account['db_ms'] . 'ms',
                $account['orders'],
                $account['deals'],
                $account['trades']
            ];
        }

        $this->table(
            ['Account', 'Total', 'API', 'DB', 'Orders', 'Deals', 'Trades'],
            $tableData
        );

        // Performance targets
        $this->newLine();
        $this->info("🎯 Performance Targets:");
        $slowAccounts = array_filter($data, fn($d) => $d['total_ms'] > 1000);
        $this->line("Accounts > 1000ms: " . count($slowAccounts) . " of " . count($data));

        if (count($slowAccounts) > 0) {
            $this->warn("Target: Get all accounts under 1000ms for optimal performance");
        } else {
            $this->info("✅ All accounts under 1000ms - excellent performance!");
        }
    }

    private function median(array $values): float
    {
        sort($values);
        $count = count($values);
        $middle = floor(($count - 1) / 2);

        if ($count % 2) {
            return $values[$middle];
        } else {
            return ($values[$middle] + $values[$middle + 1]) / 2;
        }
    }
}
