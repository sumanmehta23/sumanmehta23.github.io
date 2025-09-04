<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

/**
 * Queue Management Command for Production
 * 
 * This command helps manage large queue backlogs, especially for BatchSyncTradesJob
 * Provides tools to:
 * 1. Monitor queue sizes across all queues
 * 2. Clear specific job types safely
 * 3. Pause/resume queue processing
 * 4. Estimate completion times
 */
class ManageQueueCommand extends Command
{
    protected $signature = 'app:manage-queue 
                            {--status : Show detailed queue status}
                            {--clear-batch-sync : Clear all BatchSyncTradesJob from queue}
                            {--clear-queue= : Clear specific queue name}
                            {--pause-horizon : Pause Horizon workers}
                            {--resume-horizon : Resume Horizon workers}
                            {--estimate-completion : Estimate queue completion time}
                            {--monitor : Monitor queue in real-time}';

    protected $description = 'Manage production queue backlogs and monitor queue health';

    public function handle()
    {
        $showStatus = $this->option('status');
        $clearBatchSync = $this->option('clear-batch-sync');
        $clearQueue = $this->option('clear-queue');
        $pauseHorizon = $this->option('pause-horizon');
        $resumeHorizon = $this->option('resume-horizon');
        $estimateCompletion = $this->option('estimate-completion');
        $monitor = $this->option('monitor');

        if ($showStatus) {
            $this->showQueueStatus();
        } elseif ($clearBatchSync) {
            $this->clearBatchSyncJobs();
        } elseif ($clearQueue) {
            $this->clearSpecificQueue($clearQueue);
        } elseif ($pauseHorizon) {
            $this->pauseHorizon();
        } elseif ($resumeHorizon) {
            $this->resumeHorizon();
        } elseif ($estimateCompletion) {
            $this->estimateCompletion();
        } elseif ($monitor) {
            $this->monitorQueue();
        } else {
            $this->showQueueStatus();
        }
    }

    protected function showQueueStatus()
    {
        $this->info("=== Production Queue Status ===");

        try {
            // Common Laravel queue names to check
            $queues = [
                'default',
                'optimized-sync-trades',
                'sync-trades',
                'high',
                'low',
                'batch'
            ];

            $totalJobs = 0;
            $queueData = [];

            foreach ($queues as $queueName) {
                $queueKey = "queues:{$queueName}";
                $pendingCount = Redis::llen($queueKey);
                $delayedCount = Redis::zcard($queueKey . ':delayed');
                $reservedCount = Redis::zcard($queueKey . ':reserved');
                $failedCount = Redis::llen($queueKey . ':failed');

                $total = $pendingCount + $delayedCount + $reservedCount;
                $totalJobs += $total;

                if ($total > 0 || $failedCount > 0) {
                    $queueData[] = [
                        $queueName,
                        $pendingCount,
                        $delayedCount,
                        $reservedCount,
                        $failedCount,
                        $total
                    ];
                }
            }

            if (empty($queueData)) {
                $this->info("✅ All queues are empty!");
            } else {
                $this->table([
                    'Queue Name',
                    'Pending',
                    'Delayed',
                    'Reserved',
                    'Failed',
                    'Total'
                ], $queueData);

                $this->info("📊 Total jobs across all queues: {$totalJobs}");
            }

            // Check Horizon status
            $this->info("\n=== Horizon Status ===");
            try {
                $horizonStatus = Artisan::call('horizon:status');
                $this->info("Horizon is running");
            } catch (\Exception $e) {
                $this->warn("Horizon status check failed: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error("Failed to get queue status: " . $e->getMessage());
        }
    }

    protected function clearBatchSyncJobs()
    {
        $this->warn("⚠️  DANGER: This will clear ALL BatchSyncTradesJob from the queue!");
        $this->warn("This action cannot be undone.");

        if (!$this->confirm('Are you sure you want to proceed?')) {
            $this->info("Operation cancelled.");
            return;
        }

        try {
            $queueName = 'optimized-sync-trades';
            $queueKey = "queues:{$queueName}";

            // Get current count before clearing
            $beforeCount = Redis::llen($queueKey);

            // Clear the queue
            $cleared = Redis::del($queueKey);

            // Also clear delayed and reserved jobs
            Redis::del($queueKey . ':delayed');
            Redis::del($queueKey . ':reserved');

            $this->info("✅ Cleared {$beforeCount} jobs from {$queueName} queue");
            Log::warning("Production queue cleared: {$beforeCount} BatchSyncTradesJob jobs removed from {$queueName}");
        } catch (\Exception $e) {
            $this->error("Failed to clear queue: " . $e->getMessage());
        }
    }

    protected function clearSpecificQueue(string $queueName)
    {
        $this->warn("⚠️  DANGER: This will clear ALL jobs from queue: {$queueName}");

        if (!$this->confirm('Are you sure you want to proceed?')) {
            $this->info("Operation cancelled.");
            return;
        }

        try {
            $queueKey = "queues:{$queueName}";
            $beforeCount = Redis::llen($queueKey);

            Redis::del($queueKey);
            Redis::del($queueKey . ':delayed');
            Redis::del($queueKey . ':reserved');

            $this->info("✅ Cleared {$beforeCount} jobs from {$queueName} queue");
            Log::warning("Production queue cleared: {$beforeCount} jobs removed from {$queueName}");
        } catch (\Exception $e) {
            $this->error("Failed to clear queue: " . $e->getMessage());
        }
    }

    protected function pauseHorizon()
    {
        try {
            Artisan::call('horizon:pause');
            $this->info("⏸️  Horizon workers paused");
            Log::info("Horizon workers paused via queue management command");
        } catch (\Exception $e) {
            $this->error("Failed to pause Horizon: " . $e->getMessage());
        }
    }

    protected function resumeHorizon()
    {
        try {
            Artisan::call('horizon:continue');
            $this->info("▶️  Horizon workers resumed");
            Log::info("Horizon workers resumed via queue management command");
        } catch (\Exception $e) {
            $this->error("Failed to resume Horizon: " . $e->getMessage());
        }
    }

    protected function estimateCompletion()
    {
        try {
            $queueName = 'optimized-sync-trades';
            $queueKey = "queues:{$queueName}";
            $pendingJobs = Redis::llen($queueKey);

            if ($pendingJobs == 0) {
                $this->info("✅ No jobs in queue to process");
                return;
            }

            // Estimates based on typical BatchSyncTradesJob performance
            $avgJobTime = 3; // seconds per job (conservative estimate)
            $workersCount = 8; // typical Horizon worker count

            $totalTimeSeconds = ($pendingJobs * $avgJobTime) / $workersCount;
            $hours = floor($totalTimeSeconds / 3600);
            $minutes = floor(($totalTimeSeconds % 3600) / 60);

            $this->info("📊 Queue Completion Estimate:");
            $this->info("Jobs in queue: {$pendingJobs}");
            $this->info("Estimated workers: {$workersCount}");
            $this->info("Avg time per job: {$avgJobTime}s");
            $this->info("⏱️  Estimated completion: {$hours}h {$minutes}m");

            if ($hours > 2) {
                $this->warn("⚠️  Queue will take over 2 hours to complete. Consider:");
                $this->warn("   1. Clearing the queue if jobs are redundant");
                $this->warn("   2. Increasing Horizon worker count");
                $this->warn("   3. Using priority-sync with queue limits");
            }
        } catch (\Exception $e) {
            $this->error("Failed to estimate completion: " . $e->getMessage());
        }
    }

    protected function monitorQueue()
    {
        $this->info("🔍 Monitoring queue in real-time (Press Ctrl+C to stop)");

        $previousCount = null;

        while (true) {
            try {
                $queueKey = "queues:optimized-sync-trades";
                $currentCount = Redis::llen($queueKey);
                $timestamp = now()->format('H:i:s');

                if ($previousCount !== null) {
                    $change = $currentCount - $previousCount;
                    $changeStr = $change > 0 ? "+{$change}" : (string)$change;
                    $arrow = $change > 0 ? "📈" : ($change < 0 ? "📉" : "➡️");

                    $this->info("[{$timestamp}] Queue: {$currentCount} jobs ({$changeStr}) {$arrow}");
                } else {
                    $this->info("[{$timestamp}] Queue: {$currentCount} jobs");
                }

                $previousCount = $currentCount;
                sleep(5); // Update every 5 seconds

            } catch (\Exception $e) {
                $this->error("Monitoring error: " . $e->getMessage());
                sleep(10);
            }
        }
    }
}
