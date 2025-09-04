<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\EnhancedUniversalMT5Service;
use Illuminate\Support\Facades\Log;

/**
 * Demo Queue Job for Redis Coordinated MT5 Operations
 * 
 * This job demonstrates how queue jobs coordinate
 * MT5 connections through Redis with HTTP requests.
 */
class MT5RedisCoordinationDemoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $login;
    protected $operationType;
    protected $jobId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $login, string $operationType = 'balance_check', string $jobId = null)
    {
        $this->login = $login;
        $this->operationType = $operationType;
        $this->jobId = $jobId ?: 'job_' . uniqid();
    }

    /**
     * Execute the job with Redis coordination.
     */
    public function handle(EnhancedUniversalMT5Service $mt5Service): void
    {
        Log::info("🚀 Queue Job {$this->jobId} starting", [
            'operation' => $this->operationType,
            'login' => $this->login,
            'coordination_mode' => $mt5Service->isUsingRedisCoordination() ? 'redis' : 'local'
        ]);

        $beforeStats = $mt5Service->getStats();

        Log::info("📊 Queue Job {$this->jobId} - Before Stats", $beforeStats);

        try {
            $startTime = microtime(true);
            $result = null;

            // Perform different operations based on type
            switch ($this->operationType) {
                case 'balance_check':
                    $result = $mt5Service->getAccountBalance($this->login);
                    break;

                case 'account_info':
                    $result = $mt5Service->getAccount($this->login);
                    break;

                case 'trade_history':
                    $from = strtotime('-1 week');
                    $to = time();
                    $result = $mt5Service->getTradeHistory($this->login, $from, $to);
                    break;

                case 'dealer_connect':
                    $result = $mt5Service->dealerConnect();
                    break;

                default:
                    throw new \InvalidArgumentException("Unknown operation type: {$this->operationType}");
            }

            $duration = microtime(true) - $startTime;
            $afterStats = $mt5Service->getStats();

            Log::info("✅ Queue Job {$this->jobId} completed successfully", [
                'operation' => $this->operationType,
                'login' => $this->login,
                'duration_seconds' => round($duration, 3),
                'result_type' => gettype($result),
                'result_size' => is_array($result) ? count($result) : (is_string($result) ? strlen($result) : 'N/A'),
                'coordination' => [
                    'before' => $beforeStats,
                    'after' => $afterStats,
                    'connection_reused' => $beforeStats['global_connections'] === $afterStats['global_connections'],
                    'global_utilization' => $afterStats['connection_utilization'] . '%'
                ]
            ]);

            // Log coordination benefits
            if ($mt5Service->isUsingRedisCoordination()) {
                Log::info("🔄 Queue Job {$this->jobId} - Redis Coordination Active", [
                    'global_connections' => $afterStats['global_connections'],
                    'active_processes' => $afterStats['active_processes'],
                    'current_process' => $afterStats['current_process'],
                    'message' => 'This job coordinated with all HTTP requests and other queue jobs'
                ]);
            }
        } catch (\Exception $e) {
            $afterStats = $mt5Service->getStats();

            Log::error("❌ Queue Job {$this->jobId} failed", [
                'operation' => $this->operationType,
                'login' => $this->login,
                'error' => $e->getMessage(),
                'stats_after_error' => $afterStats,
                'coordination_still_active' => $mt5Service->isUsingRedisCoordination()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("💥 Queue Job {$this->jobId} failed permanently", [
            'operation' => $this->operationType,
            'login' => $this->login,
            'exception' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Static helper to dispatch multiple demo jobs
     */
    public static function dispatchDemoJobs(int $count = 5, int $login = 12345): array
    {
        $operations = ['balance_check', 'account_info', 'trade_history', 'dealer_connect'];
        $dispatched = [];

        for ($i = 1; $i <= $count; $i++) {
            $operation = $operations[($i - 1) % count($operations)];
            $jobId = "demo_job_{$i}_" . time();

            $job = new self($login, $operation, $jobId);

            // Dispatch with delay to spread out execution
            $job->delay(now()->addSeconds($i * 2));
            dispatch($job);

            $dispatched[] = [
                'job_id' => $jobId,
                'operation' => $operation,
                'login' => $login,
                'delayed_seconds' => $i * 2
            ];
        }

        Log::info("🚀 Dispatched {$count} demo jobs with Redis coordination", [
            'jobs' => $dispatched,
            'message' => 'These jobs will coordinate MT5 connections with HTTP requests through Redis'
        ]);

        return $dispatched;
    }
}
