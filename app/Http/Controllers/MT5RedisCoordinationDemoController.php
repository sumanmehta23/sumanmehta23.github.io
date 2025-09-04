<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EnhancedUniversalMT5Service;
use Illuminate\Http\JsonResponse;

/**
 * Demo Controller for Redis Coordinated MT5 Operations
 * 
 * This controller demonstrates how different HTTP requests
 * now coordinate MT5 connections through Redis.
 */
class MT5RedisCoordinationDemoController extends Controller
{
    protected $mt5Service;

    public function __construct(EnhancedUniversalMT5Service $mt5Service)
    {
        $this->mt5Service = $mt5Service;
    }

    /**
     * Show Redis coordination status
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'coordination_enabled' => $this->mt5Service->isUsingRedisCoordination(),
            'stats' => $this->mt5Service->getStats(),
            'health' => $this->mt5Service->getHealth(),
            'message' => 'Cross-HTTP request coordination is ' .
                ($this->mt5Service->isUsingRedisCoordination() ? 'ACTIVE' : 'INACTIVE')
        ]);
    }

    /**
     * Simulate account balance check (coordinates through Redis)
     */
    public function checkBalance(Request $request): JsonResponse
    {
        $login = $request->get('login', 12345);

        $beforeStats = $this->mt5Service->getStats();

        try {
            // This operation will coordinate with all other HTTP requests/queue jobs
            $balance = $this->mt5Service->getAccountBalance($login);

            $afterStats = $this->mt5Service->getStats();

            return response()->json([
                'success' => true,
                'login' => $login,
                'balance' => $balance,
                'coordination' => [
                    'before' => $beforeStats,
                    'after' => $afterStats,
                    'global_utilization' => $afterStats['connection_utilization'] . '%'
                ],
                'message' => 'Balance check coordinated through Redis'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->mt5Service->getStats(),
                'message' => 'Operation failed but coordination still active'
            ], 500);
        }
    }

    /**
     * Simulate trade history fetch (coordinates through Redis)
     */
    public function tradeHistory(Request $request): JsonResponse
    {
        $login = $request->get('login', 12345);
        $from = $request->get('from', strtotime('-1 month'));
        $to = $request->get('to', time());

        $beforeStats = $this->mt5Service->getStats();

        try {
            // This operation will coordinate with all other HTTP requests/queue jobs
            $trades = $this->mt5Service->getTradeHistory($login, $from, $to);

            $afterStats = $this->mt5Service->getStats();

            return response()->json([
                'success' => true,
                'login' => $login,
                'period' => ['from' => $from, 'to' => $to],
                'trades_count' => is_array($trades) ? count($trades) : 0,
                'coordination' => [
                    'before' => $beforeStats,
                    'after' => $afterStats,
                    'reused_connection' => $beforeStats['global_connections'] === $afterStats['global_connections']
                ],
                'message' => 'Trade history fetched with Redis coordination'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->mt5Service->getStats(),
                'message' => 'Operation failed but coordination still active'
            ], 500);
        }
    }

    /**
     * Force cleanup stale processes (admin function)
     */
    public function cleanupStaleProcesses(): JsonResponse
    {
        if (!$this->mt5Service->isUsingRedisCoordination()) {
            return response()->json([
                'success' => false,
                'message' => 'Redis coordination not enabled'
            ], 400);
        }

        $cleaned = $this->mt5Service->forceCleanupStaleProcesses();

        return response()->json([
            'success' => true,
            'processes_cleaned' => $cleaned,
            'current_stats' => $this->mt5Service->getStats(),
            'message' => "Cleaned $cleaned stale processes"
        ]);
    }

    /**
     * Switch coordination mode (for testing)
     */
    public function switchMode(Request $request): JsonResponse
    {
        $useRedis = $request->boolean('redis', true);

        $beforeMode = $this->mt5Service->isUsingRedisCoordination() ? 'redis' : 'local';
        $this->mt5Service->switchCoordinationMode($useRedis);
        $afterMode = $this->mt5Service->isUsingRedisCoordination() ? 'redis' : 'local';

        return response()->json([
            'success' => true,
            'switched' => $beforeMode !== $afterMode,
            'before_mode' => $beforeMode,
            'after_mode' => $afterMode,
            'stats' => $this->mt5Service->getStats(),
            'message' => "Coordination mode: $beforeMode → $afterMode"
        ]);
    }

    /**
     * Stress test - multiple concurrent operations
     */
    public function stressTest(Request $request): JsonResponse
    {
        $operations = $request->get('operations', 5);
        $login = $request->get('login', 12345);

        $results = [];
        $startStats = $this->mt5Service->getStats();

        for ($i = 1; $i <= $operations; $i++) {
            $operationStart = microtime(true);

            try {
                // Simulate different operations
                switch ($i % 3) {
                    case 0:
                        $result = $this->mt5Service->getAccountBalance($login);
                        $operation = 'balance_check';
                        break;
                    case 1:
                        $result = $this->mt5Service->getAccount($login);
                        $operation = 'account_info';
                        break;
                    case 2:
                        $result = $this->mt5Service->dealerConnect();
                        $operation = 'dealer_connect';
                        break;
                }

                $duration = microtime(true) - $operationStart;
                $currentStats = $this->mt5Service->getStats();

                $results[] = [
                    'operation' => $i,
                    'type' => $operation,
                    'success' => true,
                    'duration_ms' => round($duration * 1000, 2),
                    'global_connections' => $currentStats['global_connections'],
                    'utilization' => $currentStats['connection_utilization'] . '%'
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'operation' => $i,
                    'type' => $operation ?? 'unknown',
                    'success' => false,
                    'error' => $e->getMessage(),
                    'duration_ms' => round((microtime(true) - $operationStart) * 1000, 2)
                ];
            }

            // Small delay between operations
            usleep(100000); // 0.1 second
        }

        $endStats = $this->mt5Service->getStats();

        return response()->json([
            'success' => true,
            'operations_performed' => $operations,
            'coordination_mode' => $this->mt5Service->isUsingRedisCoordination() ? 'redis' : 'local',
            'stats' => [
                'start' => $startStats,
                'end' => $endStats
            ],
            'results' => $results,
            'message' => "Stress test completed with Redis coordination"
        ]);
    }
}
