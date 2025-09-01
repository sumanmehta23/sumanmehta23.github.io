<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class MonitorSyncJobs extends Command
{
    protected $signature = 'app:monitor-sync-jobs {--clear-failed : Clear failed jobs}';

    protected $description = 'Monitor sync jobs queue status and performance';

    public function handle()
    {
        if ($this->option('clear-failed')) {
            $this->clearFailedJobs();
            return;
        }

        $this->showQueueStatus();
        $this->showRecentJobs();
    }

    protected function showQueueStatus()
    {
        $this->info("=== Queue Status ===");

        // Get queue sizes
        $syncAllJobsCount = DB::table('jobs')->where('queue', 'sync-all-trades')->count();
        $defaultJobsCount = DB::table('jobs')->where('queue', 'default')->count();
        $failedJobsCount = DB::table('failed_jobs')->count();

        $this->line("Pending Jobs:");
        $this->line("  sync-all-trades: {$syncAllJobsCount}");
        $this->line("  default: {$defaultJobsCount}");
        $this->line("  failed: {$failedJobsCount}");

        // Show batch status
        $activeBatches = DB::table('job_batches')
            ->where('finished_at', null)
            ->where('cancelled_at', null)
            ->count();

        $completedBatches = DB::table('job_batches')
            ->whereNotNull('finished_at')
            ->whereDate('created_at', today())
            ->count();

        $this->line("");
        $this->line("Batches:");
        $this->line("  active: {$activeBatches}");
        $this->line("  completed today: {$completedBatches}");
    }

    protected function showRecentJobs()
    {
        $this->info("\n=== Recent Failed Jobs (last 10) ===");

        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get();

        if ($failedJobs->isEmpty()) {
            $this->line("No recent failed jobs");
            return;
        }

        foreach ($failedJobs as $job) {
            $payload = json_decode($job->payload, true);
            $jobName = $payload['displayName'] ?? 'Unknown';

            $this->line("- {$jobName} failed at {$job->failed_at}");

            // Show exception for SyncAllAccountsTradesJob
            if (str_contains($jobName, 'SyncAllAccountsTradesJob')) {
                $exception = $job->exception;
                $timeoutPattern = '/TimeoutExceededException|timeout/i';
                if (preg_match($timeoutPattern, $exception)) {
                    $this->line("  <comment>TIMEOUT ERROR</comment>");
                } else {
                    // Show first line of exception
                    $firstLine = strtok($exception, "\n");
                    $this->line("  " . substr($firstLine, 0, 80) . "...");
                }
            }
        }
    }

    protected function clearFailedJobs()
    {
        $count = DB::table('failed_jobs')->count();
        DB::table('failed_jobs')->truncate();
        $this->info("Cleared {$count} failed jobs");
    }
}
