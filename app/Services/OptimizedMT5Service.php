<?php

namespace App\Services;

use App\MT5\MTWebAPI;
use App\MT5\MTRetCode;
use Illuminate\Support\Facades\Log;

/**
 * Optimized MT5 Service with Connection Pooling
 * 
 * Benefits:
 * - Reuses connections from pool
 * - Handles connection failures gracefully  
 * - Provides fallback to direct connection
 * - Monitors connection health
 */
class OptimizedMT5Service
{
    protected $api;
    protected $connectionPool;
    protected $usePool;

    public function __construct(bool $usePool = true)
    {
        $this->usePool = $usePool;
        if ($this->usePool) {
            $this->connectionPool = MT5ConnectionPool::getInstance();
        }
    }

    /**
     * Get MT5 API with pooled connection
     */
    public function getApi(): ?MTWebAPI
    {
        if ($this->api) {
            return $this->api;
        }

        if ($this->usePool) {
            $this->api = $this->connectionPool->getConnection();
        } else {
            $this->api = $this->createDirectConnection();
        }

        return $this->api;
    }

    /**
     * Connect using pool or direct connection
     */
    public function connect(): bool
    {
        $api = $this->getApi();
        return $api !== null;
    }

    /**
     * Create direct connection (fallback)
     */
    private function createDirectConnection(): ?MTWebAPI
    {
        try {
            $api = new MTWebAPI();
            $settings = settings();

            $api->SetLoggerWriteDebug(config('constants.IS_WRITE_DEBUG_LOG'));

            $result = $api->Connect(
                $settings['mt5_server_ip'],
                $settings['mt5_server_port'],
                30,
                $settings['mt5_server_web_login'],
                $settings['mt5_server_web_password']
            );

            if ($result === MTRetCode::MT_RET_OK) {
                Log::info("OptimizedMT5: Direct connection established");
                return $api;
            } else {
                Log::error("OptimizedMT5: Direct connection failed: " . MTRetCode::GetError($result));
                return null;
            }
        } catch (\Exception $e) {
            Log::error("OptimizedMT5: Direct connection exception: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Report connection error to pool
     */
    public function reportError(): void
    {
        if ($this->usePool && $this->api) {
            $this->connectionPool->reportConnectionError($this->api);
        }
    }

    /**
     * Get connection pool statistics
     */
    public function getPoolStats(): array
    {
        if ($this->usePool) {
            return $this->connectionPool->getStats();
        }
        return ['pool_enabled' => false];
    }

    /**
     * Dealer connect (keep original functionality)
     */
    public function dealerConnect(): int
    {
        $api = $this->getApi();
        if (!$api) {
            return MTRetCode::MT_RET_ERR_NETWORK;
        }

        $settings = settings();
        return $api->DealerConnect(
            $settings['mt5_server_ip'],
            $settings['mt5_server_port'],
            30,
            $settings['mt5_server_web_login'],
            $settings['mt5_server_web_password']
        );
    }

    /**
     * Execute MT5 operation with error handling
     */
    public function executeWithRetry(callable $operation, int $maxRetries = 3): mixed
    {
        $attempt = 0;
        $lastError = null;

        while ($attempt < $maxRetries) {
            try {
                $api = $this->getApi();
                if (!$api) {
                    throw new \Exception("No MT5 connection available");
                }

                $result = $operation($api);

                // Check if result indicates connection error
                if (is_int($result) && in_array($result, [
                    MTRetCode::MT_RET_ERR_NETWORK,
                    MTRetCode::MT_RET_ERR_CONNECTION,
                    MTRetCode::MT_RET_ERR_TIMEOUT
                ])) {
                    throw new \Exception("MT5 connection error: " . $result);
                }

                return $result;
            } catch (\Exception $e) {
                $lastError = $e;
                $attempt++;

                Log::warning("OptimizedMT5: Operation failed (attempt {$attempt}): " . $e->getMessage());

                // Report error to pool and reset connection
                $this->reportError();
                $this->api = null;

                if ($attempt < $maxRetries) {
                    sleep(1 * $attempt); // Exponential backoff
                }
            }
        }

        throw new \Exception("MT5 operation failed after {$maxRetries} attempts: " . $lastError->getMessage());
    }
}
