<?php

namespace App\Services;

use App\Models\Mt5User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Exception;

/**
 * MT5 REST API Service
 * 
 * Provides high-level interface for MT5 operations using REST API
 * with connection pooling for improved performance and reliability.
 */
class MT5RestAPIService
{
    private $settings;
    private $connectionPool;

    public function __construct()
    {
        $this->settings = settings();
        $this->connectionPool = MT5RestAPIConnectionPool::getInstance();
    }

    /**
     * Get multiple user balances in a single batch request
     * 
     * @param array $logins Array of MT5 login IDs
     * @return array Array of user balance data indexed by login
     */
    public function getBatchBalances(array $logins): array
    {
        if (empty($logins)) {
            return [];
        }

        $apiRequest = $this->connectionPool->getConnection();
        if (!$apiRequest) {
            Log::error('MT5RestAPI: Failed to get connection from pool');
            return [];
        }

        try {
            // Prepare the batch request data
            $requestData = [
                'logins' => array_values($logins), // Ensure numeric array
                'fields' => [
                    'Login',
                    'Balance',
                    'Credit',
                    'Margin',
                    'MarginFree',
                    'MarginLevel',
                    'Equity'
                ]
            ];

            Log::info('MT5RestAPI: Sending batch balance request', [
                'login_count' => count($logins),
                'logins' => $logins
            ]);

            // Make the batch API request
            $result = $apiRequest->Post('/api/user/get_batch', json_encode($requestData));

            if ($result === false) {
                Log::error('MT5RestAPI: Batch balance request failed');
                $this->connectionPool->reportConnectionError($apiRequest);
                return [];
            }

            // Process and return the results
            return $this->processBatchBalanceResponse($result, $logins);
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Exception in getBatchBalances', [
                'error' => $e->getMessage(),
                'logins' => $logins
            ]);
            $this->connectionPool->reportConnectionError($apiRequest);
            return [];
        }
    }

    /**
     * Process the response from batch balance API
     */
    private function processBatchBalanceResponse($response, array $requestedLogins): array
    {
        if (!is_array($response)) {
            $response = json_decode($response, true);
        }

        if (!isset($response['data']) || !is_array($response['data'])) {
            Log::warning('MT5RestAPI: Invalid batch response format', ['response' => $response]);
            return [];
        }

        $balances = [];
        $foundLogins = [];

        foreach ($response['data'] as $userData) {
            if (isset($userData['Login'])) {
                $login = (string)$userData['Login'];
                $foundLogins[] = $login;

                $balances[$login] = [
                    'login' => $login,
                    'balance' => floatval($userData['Balance'] ?? 0),
                    'credit' => floatval($userData['Credit'] ?? 0),
                    'margin' => floatval($userData['Margin'] ?? 0),
                    'margin_free' => floatval($userData['MarginFree'] ?? 0),
                    'margin_level' => floatval($userData['MarginLevel'] ?? 0),
                    'equity' => floatval($userData['Equity'] ?? 0),
                ];
            }
        }

        // Log missing logins for debugging
        $missingLogins = array_diff(array_map('strval', $requestedLogins), $foundLogins);
        if (!empty($missingLogins)) {
            Log::info('MT5RestAPI: Some logins not found in batch response', [
                'missing_logins' => $missingLogins,
                'found_count' => count($foundLogins),
                'requested_count' => count($requestedLogins)
            ]);
        }

        Log::info('MT5RestAPI: Batch balance processing completed', [
            'processed_count' => count($balances),
            'requested_count' => count($requestedLogins)
        ]);

        return $balances;
    }

    /**
     * Update balances for multiple MT5 users efficiently
     * 
     * @param Collection|array $mt5Users Collection of Mt5User models or array of logins
     * @return array Summary of update results
     */
    public function updateMultipleUserBalances($mt5Users): array
    {
        $startTime = microtime(true);

        // Convert to logins array if needed
        if ($mt5Users instanceof Collection) {
            $logins = $mt5Users->pluck('login')->toArray();
            $usersByLogin = $mt5Users->keyBy('login');
        } elseif (is_array($mt5Users) && !empty($mt5Users) && is_object($mt5Users[0])) {
            // Array of Mt5User objects
            $logins = array_map(fn($user) => $user->login, $mt5Users);
            $usersByLogin = collect($mt5Users)->keyBy('login');
        } else {
            // Array of login IDs
            $logins = $mt5Users;
            $usersByLogin = Mt5User::whereIn('login', $logins)->get()->keyBy('login');
        }

        if (empty($logins)) {
            return ['updated' => 0, 'errors' => 0, 'time' => 0];
        }

        Log::info('MT5RestAPI: Starting batch balance update', [
            'user_count' => count($logins)
        ]);

        // Get balances in batch
        $balances = $this->getBatchBalances($logins);

        $updated = 0;
        $errors = 0;

        // Update each user's balance
        foreach ($balances as $login => $balanceData) {
            if (!isset($usersByLogin[$login])) {
                Log::warning('MT5RestAPI: User not found for login', ['login' => $login]);
                $errors++;
                continue;
            }

            try {
                $user = $usersByLogin[$login];
                $user->balance = $balanceData['balance'];
                $user->credit = $balanceData['credit'];
                $user->margin = $balanceData['margin'];
                $user->margin_free = $balanceData['margin_free'];
                $user->margin_level = $balanceData['margin_level'];
                $user->equity = $balanceData['equity'];
                $user->save();

                $updated++;
            } catch (Exception $e) {
                Log::error('MT5RestAPI: Failed to update user balance', [
                    'login' => $login,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }

        $executionTime = microtime(true) - $startTime;

        $summary = [
            'updated' => $updated,
            'errors' => $errors,
            'time' => round($executionTime, 3),
            'total_requested' => count($logins),
            'balances_received' => count($balances)
        ];

        Log::info('MT5RestAPI: Batch balance update completed', $summary);

        return $summary;
    }

    /**
     * Get single user balance (falls back to batch for consistency)
     */
    public function getUserBalance(string $login): ?array
    {
        $balances = $this->getBatchBalances([$login]);
        return $balances[$login] ?? null;
    }

    /**
     * Get connection pool statistics
     */
    public function getConnectionStats(): array
    {
        return $this->connectionPool->getStats();
    }

    /**
     * Force cleanup connections (for maintenance)
     */
    public function cleanupConnections(): void
    {
        $this->connectionPool->forceCleanup();
    }

    /**
     * Health check for the service
     */
    public function healthCheck(): bool
    {
        try {
            $apiRequest = $this->connectionPool->getConnection();
            if (!$apiRequest) {
                return false;
            }

            // Try a simple API call
            $result = $apiRequest->Get('/api/ping');
            return $result !== false;
        } catch (Exception $e) {
            Log::error('MT5RestAPI: Health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
