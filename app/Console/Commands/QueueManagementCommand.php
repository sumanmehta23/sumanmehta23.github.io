<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Queue;

/**
 * Queue Management Command for BatchSyncTradesJob monitoring and cleanup
 */
class QueueManagementCommand extends Command
{
    protected $signature = 'queue:manage 
                            {action : Action to perform: status, clear, limit-check}
                            {--queue=priority-sync-trades : Queue name to manage}
                            {--limit=100 : Job limit for limit-check action}';

    protected $description = 'Manage BatchSyncTradesJob queue - check status, clear, or verify limits';

    public function handle()
    {
        $action = $this->argument('action');
        $queueName = $this->option('queue');
        $limit = (int) $this->option('limit');

        switch ($action) {
            case 'status':
                $this->showQueueStatus($queueName);
                break;

            case 'clear':
                $this->clearQueue($queueName);
                break;

            case 'limit-check':
                $this->checkQueueLimit($queueName, $limit);
                break;

            default:
                $this->error("Unknown action: {$action}");
                $this->info("Available actions: status, clear, limit-check");
                return 1;
        }

        return 0;
    }

    protected function showQueueStatus($queueName)
    {
        $this->info("=== Queue Status for '{$queueName}' ===");

        try {
            $redisQueueName = "queues:{$queueName}";
            $pendingCount = Redis::llen($redisQueueName);
            $delayedCount = Redis::zcard($redisQueueName . ':delayed');
            $reservedCount = Redis::zcard($redisQueueName . ':reserved');
            $failedCount = Redis::llen('queues:failed');

            $total = $pendingCount + $delayedCount + $reservedCount;

            $this->table(['Queue Type', 'Count'], [
                ['Pending Jobs', number_format($pendingCount)],
                ['Delayed Jobs', number_format($delayedCount)],
                ['Reserved Jobs', number_format($reservedCount)],
                ['Failed Jobs (Global)', number_format($failedCount)],
                ['Total Active', number_format($total)],
            ]);

            if ($total > 1000) {
                $this->warn("⚠️  High job count detected: {$total} jobs");
            }

            if ($total > 10000) {
                $this->error("🚨 CRITICAL: Very high job count: {$total} jobs - Consider clearing queue");
            }
        } catch (\Exception $e) {
            $this->error("Error checking queue: " . $e->getMessage());
        }
    }

    protected function clearQueue($queueName)
    {
        $this->warn("Are you sure you want to clear all jobs from '{$queueName}' queue?");

        if (!$this->confirm('This action cannot be undone. Continue?')) {
            $this->info('Queue clear cancelled.');
            return;
        }

        try {
            $redisQueueName = "queues:{$queueName}";

            // Get current counts
            $pendingCount = Redis::llen($redisQueueName);
            $delayedCount = Redis::zcard($redisQueueName . ':delayed');
            $reservedCount = Redis::zcard($redisQueueName . ':reserved');

            // Clear all queue types
            Redis::del($redisQueueName);
            Redis::del($redisQueueName . ':delayed');
            Redis::del($redisQueueName . ':reserved');

            $totalCleared = $pendingCount + $delayedCount + $reservedCount;

            $this->info("✅ Queue '{$queueName}' cleared successfully!");
            $this->info("Removed {$totalCleared} jobs:");
            $this->info("- Pending: {$pendingCount}");
            $this->info("- Delayed: {$delayedCount}");
            $this->info("- Reserved: {$reservedCount}");
        } catch (\Exception $e) {
            $this->error("Error clearing queue: " . $e->getMessage());
        }
    }

    protected function checkQueueLimit($queueName, $limit)
    {
        try {
            $redisQueueName = "queues:{$queueName}";
            $pendingCount = Redis::llen($redisQueueName);
            $delayedCount = Redis::zcard($redisQueueName . ':delayed');
            $reservedCount = Redis::zcard($redisQueueName . ':reserved');
            $total = $pendingCount + $delayedCount + $reservedCount;

            $this->info("Queue Limit Check for '{$queueName}':");
            $this->info("Current: {$total} jobs");
            $this->info("Limit: {$limit} jobs");

            if ($total >= $limit) {
                $this->error("🚨 QUEUE LIMIT EXCEEDED: {$total}/{$limit}");
                $this->warn("No new jobs should be dispatched until queue is processed.");
                return 1;
            } else {
                $this->info("✅ Queue within limits: {$total}/{$limit}");
                $remaining = $limit - $total;
                $this->info("Can dispatch {$remaining} more jobs.");
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("Error checking queue limit: " . $e->getMessage());
            return 1;
        }
    }
}
