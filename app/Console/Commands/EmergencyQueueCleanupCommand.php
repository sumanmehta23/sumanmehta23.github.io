<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;

/**
 * Emergency Queue Cleanup Command for Production
 */
class EmergencyQueueCleanupCommand extends Command
{
    protected $signature = 'queue:emergency-cleanup 
                            {--dry-run : Show what would be done without actually clearing}
                            {--force : Skip confirmation prompts}';

    protected $description = 'Emergency cleanup for overloaded queue in production';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->warn("🚨 EMERGENCY QUEUE CLEANUP");
        $this->info("Checking all queue types...");

        $queues = [
            'priority-sync-trades',
            'optimized-sync-trades',
            'default',
            'high',
            'low'
        ];

        $totalJobs = 0;
        $queueStats = [];

        foreach ($queues as $queue) {
            $redisQueueName = "queues:{$queue}";
            $pending = Redis::llen($redisQueueName);
            $delayed = Redis::zcard($redisQueueName . ':delayed');
            $reserved = Redis::zcard($redisQueueName . ':reserved');
            $total = $pending + $delayed + $reserved;

            $queueStats[$queue] = [
                'pending' => $pending,
                'delayed' => $delayed,
                'reserved' => $reserved,
                'total' => $total
            ];

            $totalJobs += $total;
        }

        $this->table(
            ['Queue', 'Pending', 'Delayed', 'Reserved', 'Total'],
            collect($queueStats)->map(function ($stats, $queue) {
                return [
                    $queue,
                    number_format($stats['pending']),
                    number_format($stats['delayed']),
                    number_format($stats['reserved']),
                    number_format($stats['total'])
                ];
            })->toArray()
        );

        $this->info("Grand Total: " . number_format($totalJobs) . " jobs");

        if ($totalJobs < 1000) {
            $this->info("✅ Queue levels are normal. No cleanup needed.");
            return;
        }

        if ($dryRun) {
            $this->warn("DRY RUN: Would clear queues with high job counts");
            foreach ($queueStats as $queue => $stats) {
                if ($stats['total'] > 100) {
                    $this->warn("Would clear {$queue}: {$stats['total']} jobs");
                }
            }
            return;
        }

        if (!$force && !$this->confirm("Clear queues with > 100 jobs? This will cancel pending sync jobs.")) {
            $this->info("Cleanup cancelled.");
            return;
        }

        $cleared = 0;
        foreach ($queueStats as $queue => $stats) {
            if ($stats['total'] > 100) {
                $this->warn("Clearing {$queue} ({$stats['total']} jobs)...");

                $redisQueueName = "queues:{$queue}";
                Redis::del($redisQueueName);
                Redis::del($redisQueueName . ':delayed');
                Redis::del($redisQueueName . ':reserved');

                $cleared += $stats['total'];
            }
        }

        $this->info("✅ Cleared " . number_format($cleared) . " jobs");
        $this->warn("⚠️  Recommended next steps:");
        $this->info("1. Restart queue workers: php artisan queue:restart");
        $this->info("2. Check horizon status: php artisan horizon:status");
        $this->info("3. Monitor queue: php artisan app:priority-sync --status");
    }
}
