<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EnhancedUniversalMT5Service;
use Illuminate\Support\Facades\Redis;

class MonitorMT5RedisCoordination extends Command
{
    protected $signature = 'app:monitor-mt5-redis 
                            {--json : Output as JSON}
                            {--watch : Watch mode - refresh every 5 seconds}
                            {--cleanup : Clean up stale processes}';

    protected $description = 'Monitor MT5 Redis-coordinated connection status across processes';

    public function handle()
    {
        $isJson = $this->option('json');
        $isWatch = $this->option('watch');
        $cleanup = $this->option('cleanup');

        if ($cleanup) {
            $this->performCleanup();
            return;
        }

        if ($isWatch) {
            $this->watchMode();
        } else {
            $this->showStatus($isJson);
        }
    }

    private function showStatus(bool $isJson = false): void
    {
        $mt5Service = app(EnhancedUniversalMT5Service::class);

        if (!$mt5Service->isUsingRedisCoordination()) {
            $this->error('Redis coordination is not enabled. Current mode: local');
            return;
        }

        $stats = $mt5Service->getStats();
        $health = $mt5Service->getHealth();

        if ($isJson) {
            $output = [
                'stats' => $stats,
                'health' => $health,
                'timestamp' => now()->toISOString()
            ];
            $this->line(json_encode($output, JSON_PRETTY_PRINT));
            return;
        }

        $this->info('MT5 Redis-Coordinated Connection Status');
        $this->line(str_repeat('=', 60));

        // Global statistics
        $this->line('🌐 Global Statistics:');
        $this->line("   Global Connections: {$stats['global_connections']}/{$stats['max_global_connections']}");
        $this->line("   Active Processes: {$stats['active_processes']}");
        $this->line("   Utilization: " . round($stats['connection_utilization'], 1) . "%");

        // Local statistics
        $this->newLine();
        $this->line('📍 Local Process (' . $stats['current_process'] . '):');
        $this->line("   Local Connections: {$stats['local_connections']}/{$stats['max_local_connections']}");

        // Health status
        $this->newLine();
        $healthIcon = $health['service_healthy'] ? '✅' : '❌';
        $this->line("{$healthIcon} Health Status:");
        $this->line("   Service Healthy: " . ($health['service_healthy'] ? 'YES' : 'NO'));
        $this->line("   Coordination Mode: {$health['coordination_mode']}");

        if (isset($health['error'])) {
            $this->line("   Error: {$health['error']}");
        }

        // Redis details
        $this->newLine();
        $this->line('🔗 Redis Coordination Details:');
        $this->showRedisDetails();
    }

    private function showRedisDetails(): void
    {
        try {
            $redis = Redis::connection();

            // Global count
            $globalCount = $redis->get('mt5_connections:global_count') ?: 0;
            $this->line("   Global Count in Redis: {$globalCount}");

            // Process registry
            $processes = $redis->hgetall('mt5_connections:processes');
            $this->line("   Registered Processes: " . count($processes));

            if (count($processes) > 0) {
                $this->line('   Process Details:');
                foreach ($processes as $serverId => $processData) {
                    $data = json_decode($processData, true);
                    $startedAt = isset($data['started_at']) ? date('H:i:s', $data['started_at']) : 'unknown';
                    $this->line("     {$serverId} (started: {$startedAt})");

                    // Show connections for this process
                    $processConnections = $redis->hgetall("mt5_connections:processes:{$serverId}");
                    $this->line("       Connections: " . count($processConnections));
                }
            }

            // Lock status
            $lockValue = $redis->get('mt5_connections:lock');
            if ($lockValue) {
                $this->line("   🔒 Lock held by: {$lockValue}");
            } else {
                $this->line("   🔓 No active locks");
            }
        } catch (\Exception $e) {
            $this->error("   Redis connection failed: " . $e->getMessage());
        }
    }

    private function watchMode(): void
    {
        $this->info('👀 Watching MT5 Redis coordination (press Ctrl+C to exit)...');
        $this->newLine();

        while (true) {
            // Clear screen
            system('clear');

            $this->line('Last updated: ' . now()->format('Y-m-d H:i:s'));
            $this->newLine();

            $this->showStatus();

            sleep(5);
        }
    }

    private function performCleanup(): void
    {
        $this->info('🧹 Cleaning up stale MT5 processes...');

        $mt5Service = app(EnhancedUniversalMT5Service::class);

        if (!$mt5Service->isUsingRedisCoordination()) {
            $this->error('Redis coordination is not enabled. Cannot perform cleanup.');
            return;
        }

        $cleaned = $mt5Service->forceCleanupStaleProcesses();

        if ($cleaned > 0) {
            $this->info("✅ Cleaned up {$cleaned} stale processes");
        } else {
            $this->info("✅ No stale processes found");
        }

        // Show updated status
        $this->newLine();
        $this->showStatus();
    }
}
