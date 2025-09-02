<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MT5ConnectionPool;
use App\Services\OptimizedMT5Service;

class MonitorMT5ConnectionPool extends Command
{
    protected $signature = 'app:monitor-mt5-pool 
                            {--json : Output as JSON}
                            {--watch : Watch mode - refresh every 5 seconds}';

    protected $description = 'Monitor MT5 connection pool status and health';

    public function handle()
    {
        $isJson = $this->option('json');
        $isWatch = $this->option('watch');

        if ($isWatch) {
            $this->watchMode();
        } else {
            $this->showStatus($isJson);
        }
    }

    private function showStatus(bool $isJson = false): void
    {
        $mt5Service = new OptimizedMT5Service();
        $stats = $mt5Service->getPoolStats();

        if ($isJson) {
            $this->line(json_encode($stats, JSON_PRETTY_PRINT));
            return;
        }

        $this->info('MT5 Connection Pool Status');
        $this->line(str_repeat('=', 50));

        if (!isset($stats['pool_enabled']) || $stats['pool_enabled'] !== false) {
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Total Connections', $stats['total_connections'] ?? 0],
                    ['Healthy Connections', $stats['healthy_connections'] ?? 0],
                    ['Unhealthy Connections', $stats['unhealthy_connections'] ?? 0],
                    ['Max Connections', $stats['max_connections'] ?? 0],
                    ['Pool Utilization', $stats['pool_utilization'] ?? '0%'],
                ]
            );

            // Health indicators
            $healthyRatio = ($stats['total_connections'] > 0)
                ? ($stats['healthy_connections'] / $stats['total_connections']) * 100
                : 0;

            if ($healthyRatio >= 80) {
                $this->info('✅ Pool health: EXCELLENT');
            } elseif ($healthyRatio >= 60) {
                $this->warn('⚠️  Pool health: FAIR');
            } else {
                $this->error('❌ Pool health: POOR');
            }

            // Recommendations
            $utilization = (float) str_replace('%', '', $stats['pool_utilization'] ?? '0');
            if ($utilization > 90) {
                $this->warn('💡 Recommendation: Consider increasing max_connections in config/mt5.php');
            } elseif ($utilization < 30 && $stats['total_connections'] > 0) {
                $this->info('💡 Recommendation: Pool is underutilized, consider reducing max_connections');
            }
        } else {
            $this->error('❌ Connection pooling is disabled');
        }
    }

    private function watchMode(): void
    {
        $this->info('MT5 Connection Pool Monitor - Watch Mode');
        $this->info('Press Ctrl+C to exit');
        $this->line('');

        $iterations = 0;
        $maxIterations = 720; // Run for 1 hour max (720 * 5 seconds)

        while ($iterations < $maxIterations) {
            // Clear screen (works on most terminals)
            if (function_exists('system')) {
                system('clear');
            }

            $this->info('MT5 Connection Pool Monitor - ' . now()->format('Y-m-d H:i:s'));
            $this->line('Press Ctrl+C to exit');
            $this->line('');

            $this->showStatus(false);

            sleep(5);
            $iterations++;
        }

        $this->info('Monitor session ended after 1 hour');
    }
}
