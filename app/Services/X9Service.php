<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class X9Service
{
    protected $baseUrl;
    protected $accessToken;
    protected $v2BaseUrl;
    protected $v2AccessToken;

    public function __construct()
    {
        // V1 CRM API (legacy)
        $this->baseUrl = config('services.x9.base_url', env('X9_BASE_URL'));
        $this->accessToken = config('services.x9.access_token', env('X9_ACCESS_TOKEN'));

        // V2 API (new)
        $this->v2BaseUrl = config('services.x9.v2_base_url', env('X9_V2_BASE_URL'));
        $this->v2AccessToken = config('services.x9.v2_access_token', env('X9_V2_ACCESS_TOKEN'));
    }

    /**
     * Test connection to X9 CRM API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/system/status');

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Connection successful',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Connection failed: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Connection Test Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Create a new user account in X9 using V2 API
     */
    public function createUser($userData)
    {
        try {
            // V2 API payload structure
            $payload = [
                'preferred_login' => $userData['preferred_login'] ?? 'default',
                'client_id' => $userData['client_id'] ?? null,
                'client_group_type_id' => $userData['client_group_type_id'] ?? 1, // 1 for Demo, 2 for Real
                'client_group_id' => $userData['client_group_id'] ?? 1,
                'first_name' => $userData['first_name'],
                'middle_name' => $userData['middle_name'] ?? null,
                'last_name' => $userData['last_name'],
                'company' => $userData['company'] ?? null,
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'master_password' => $userData['master_password'],
                'investor_password' => $userData['investor_password'],
                'country_id' => $userData['country_id'] ?? 5,
                'account_number' => $userData['preferred_login'] ?? 'default',
            ];

            // Use V2 endpoint and X-API-Key header
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/v1/accounts/create', $payload);

            if ($response->successful()) {
                $data = $response->json()['data'];
                return [
                    'status' => true,
                    'message' => 'User created successfully',
                    'data' => $data
                ];
            }

            Log::error('X9 V2 Create User Failed: ' . $response->status() . ' - ' . $response->body());
            return [
                'status' => false,
                'message' => 'Failed to create user: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Create User Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to create user: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get user details
     */
    public function getUserDetails($loginId)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/accounts/' . $loginId);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'User details retrieved successfully',
                    'data' => $response->json()['data']
                ];
            }
            return [
                'status' => false,
                'message' => 'Failed to get user details: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get User Details Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get user details: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Deposit/Withdraw balance using V2 API
     */
    public function manageBalance($loginId, $operationType, $transactionType, $amount, $comment = '', $operateWithoutChecking = true)
    {
        try {
            // V2 API uses capitalized operation types: Deposit, Withdrawal, Credit, Bonus
            // Map based on transaction type if operation type is generic 'balance'
            if (strtolower($operationType) === 'balance') {
                $v2OperationType = ucfirst(strtolower($transactionType)); // deposit -> Deposit, withdrawal -> Withdrawal
                $v2TransactionType = ucfirst(strtolower($transactionType)); // deposit -> Deposit
            } else {
                // Map common operation types to V2 format
                $operationTypeMap = [
                    'deposit' => 'Deposit',
                    'withdrawal' => 'Withdrawal',
                    'credit' => 'Credit',
                    'bonus' => 'Bonus'
                ];
                $v2OperationType = $operationTypeMap[strtolower($operationType)] ?? ucfirst($operationType);
                $v2TransactionType = $operationTypeMap[strtolower($transactionType)] ?? ucfirst($transactionType);
            }

            $payload = [
                'login_id' => $loginId,
                'operation_type' => $transactionType, // deposit, withdrawal etc
                'amount' => $amount,
                'comment' => $comment,
                'transaction_type' => $operationType, // Credit or Debit
                'operate_without_checking' => $operateWithoutChecking
            ];

            // V2 endpoint: /api/v1/accounts/{account_number}/balance
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . "/api/v1/accounts/{$loginId}/balance", $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Balance operation successful',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Balance operation failed: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Balance Operation Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Balance operation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get client group types
     */
    public function getClientGroupTypes()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/crm/client_group_types');

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Client group types retrieved successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get client group types: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get Client Group Types Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get client group types: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get client groups by type
     */
    public function getClientGroupsByType($typeId)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/crm/client_groups_by_type/' . $typeId);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Client groups retrieved successfully',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get client groups: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get Client Groups Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get client groups: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get client group name by ID
     */
    public function getClientGroupName($groupId, $typeId = 1)
    {
        try {
            $response = $this->getClientGroupsByType($typeId);
            if ($response['status'] && isset($response['data'])) {
                $groups = $response['data']['client_groups_by_type'] ?? [];

                foreach ($groups as $group) {
                    if (isset($group['id']) && $group['id'] == $groupId) {
                        return $group['name'] ?? 'Unknown Group';
                    }
                }
            }

            return 'Group ID: ' . $groupId; // Fallback to show the ID
        } catch (Exception $e) {
            Log::error('X9 Get Client Group Name Failed: ' . $e->getMessage());
            return 'Group ID: ' . $groupId;
        }
    }

    /**
     * Generate a random 8-digit login ID for X9
     */
    protected function generateRandomLoginId()
    {
        return rand(10000000, 99999999);
    }

    /**
     * Generate a random password
     */
    protected function generatePassword($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Update user group in X9
     */
    public function updateUserGroup($loginId, $groupId)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($this->baseUrl . '/api/crm/account_group', [
                'login_id' => $loginId,
                'group' => $groupId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('X9 Update User Group Success: ' . json_encode($data));

                return [
                    'status' => true,
                    'message' => 'User group updated successfully',
                    'data' => $data
                ];
            } else {
                Log::error('X9 Update User Group Failed: ' . $response->body());
                return [
                    'status' => false,
                    'message' => 'Failed to update user group: ' . $response->body(),
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            Log::error('X9 Update User Group Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to update user group: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Update user leverage in X9
     */
    public function updateUserLeverage($loginId, $leverage)
    {
        try {
            // Map leverage values to leverage_profile_id
            // Based on actual X9 system configuration
            $leverageProfileMapping = [
                100 => 1,    // 1:100 - Standard leverage for regular trading
                500 => 2,    // 1:500 - High leverage for experienced traders
            ];

            // Convert string leverage (e.g., "1:100" or "100") to integer
            $leverageValue = is_string($leverage) ? intval(str_replace('1:', '', $leverage)) : intval($leverage);

            // Get the leverage_profile_id
            if (!isset($leverageProfileMapping[$leverageValue])) {
                return [
                    'status' => false,
                    'message' => "Unsupported leverage value: {$leverageValue}. Available options: 100 (1:100), 500 (1:500)",
                    'data' => null
                ];
            }

            $leverageProfileId = $leverageProfileMapping[$leverageValue];

            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($this->baseUrl . '/api/crm/account_leverage', [
                'login_id' => $loginId,
                'leverage_profile_id' => $leverageProfileId
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('X9 Update User Leverage Success: ' . json_encode($data));

                return [
                    'status' => true,
                    'message' => 'User leverage updated successfully',
                    'data' => $data
                ];
            } else {
                Log::error('X9 Update User Leverage Failed: ' . $response->body());
                return [
                    'status' => false,
                    'message' => 'Failed to update user leverage: ' . $response->body(),
                    'data' => null
                ];
            }
        } catch (Exception $e) {
            Log::error('X9 Update User Leverage Exception: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to update user leverage: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Handle bonus operations (Bonus In/Out) using V2 API
     */
    public function manageBonus($loginId, $bonusType, $amount, $comment = '', $operateWithoutChecking = true)
    {
        try {
            // Determine the correct operation type based on bonus type
            $operationType = 'Bonus';
            $transactionType = $bonusType === 'in' ? 'Bonus In' : 'Bonus Out';

            $payload = [
                'operation_type' => $operationType,
                'transaction_type' => $transactionType,
                'amount' => abs($amount), // Always send positive amount
                'comment' => $comment
            ];

            // V2 endpoint: /api/v1/accounts/{account_number}/balance
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->v2BaseUrl . '/api/v1/accounts/' . $loginId . '/balance', $payload);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Bonus operation successful',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Bonus operation failed: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Bonus Operation Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Bonus operation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Reset user password in X9 using V2 API
     *
     * Note: V2 API currently only supports master password updates via PATCH endpoint.
     * Investor password updates may need to use V1 CRM API or a different V2 endpoint.
     */
    public function resetUserPassword($loginId, $passwordType, $newPassword)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->patch($this->baseUrl . "/api/v1/accounts/{$loginId}/password", [
                // 'login_id' => intval($loginId),
                // 'password_type' => $passwordType, // 'master', 'investor', or 'api'
                'master_password' => $newPassword
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'message' => $data['message'] ?? 'Password updated successfully',
                    'data' => $data
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to reset password: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Password Reset Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Password reset failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function accountSetting($account, $field, $type)
    {

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($this->baseUrl . '/api/crm/account_settings', [
                'login_id' => intval($account->code),
                'field_to_update' => $field, // 'master', 'investor', or 'api'
                'field_setting' => $type
            ]);
            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Account setting successful updated',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to update account setting: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Account setting updation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Account setting updation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    public function enableAccount($accountCode)
    {

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($this->baseUrl . "/api/v1/accounts/{$accountCode}/trading", [
                'trading' => true
            ]);
            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Account setting successful updated',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to update account setting: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Account setting updation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Account setting updation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
    public function disableAccount($accountCode)
    {

        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->put($this->baseUrl . "/api/v1/accounts/{$accountCode}/trading", [
                'trading' => false
            ]);
            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'Account setting successful updated',
                    'data' => $response->json()
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to update account setting: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Account setting updation failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Account setting updation failed: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get accounts with open positions
     * New API endpoint: /api/v1/accounts/withpositions
     */
    public function getAccountsWithPositions()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/accounts/withpositions');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'message' => 'Accounts with positions retrieved successfully',
                    'data' => $data['accounts'] ?? [],
                    'total_accounts' => $data['total_accounts'] ?? 0
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get accounts with positions: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get Accounts With Positions Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get accounts with positions: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get all open positions across all accounts
     * New API endpoint: /api/v1/positions/all
     */
    public function getAllPositions()
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/positions/all');

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return [
                    'status' => true,
                    'message' => 'All positions retrieved successfully',
                    'data' => $data,
                    'total_accounts' => $data['total_accounts'] ?? 0,
                    'accounts_with_positions' => $data['accounts_with_positions'] ?? 0
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get all positions: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get All Positions Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get all positions: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get closed trades for a specific client group
     * New API endpoint: /api/v1/closed-trades/group/{client_group_id}
     * 
     * @param int $clientGroupId The client group ID
     * @param string|null $dateFrom Start date in YYYY-MM-DD format (default: today)
     * @param string|null $dateTo End date in YYYY-MM-DD format (default: today)
     * @param int $limit Maximum trades to return (1-1000, default: 100)
     * @param int $offset Offset for pagination (default: 0)
     */
    public function getClosedTradesByGroup($clientGroupId, $dateFrom = null, $dateTo = null, $limit = 100, $offset = 0)
    {
        try {
            $queryParams = [];

            if ($dateFrom) {
                $queryParams['date_from'] = $dateFrom;
            }

            if ($dateTo) {
                $queryParams['date_to'] = $dateTo;
            }

            $queryParams['limit'] = min(max($limit, 1), 1000); // Ensure limit is between 1-1000
            $queryParams['offset'] = max($offset, 0); // Ensure offset is not negative

            $url = $this->baseUrl . '/api/v1/closed-trades/group/' . $clientGroupId;
            if (!empty($queryParams)) {
                $url .= '?' . http_build_query($queryParams);
            }

            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'message' => 'Closed trades retrieved successfully',
                    'data' => $data,
                    'client_group_id' => $data['client_group_id'] ?? null,
                    'client_group_name' => $data['client_group_name'] ?? null,
                    'accounts_count' => $data['accounts_count'] ?? 0,
                    'summary' => $data['summary'] ?? null,
                    'trades' => $data['trades'] ?? []
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get closed trades: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get Closed Trades By Group Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get closed trades: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Get account balance and equity details
     * Uses existing endpoint: /api/v1/accounts/{account_number}
     */
    public function getAccountBalance($accountNumber)
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/v1/accounts/' . $accountNumber);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return [
                    'status' => true,
                    'message' => 'Account balance retrieved successfully',
                    'data' => $data,
                    'balance' => $data['balance'] ?? null
                ];
            }

            return [
                'status' => false,
                'message' => 'Failed to get account balance: ' . $response->body(),
                'data' => null
            ];
        } catch (Exception $e) {
            Log::error('X9 Get Account Balance Failed: ' . $e->getMessage());
            return [
                'status' => false,
                'message' => 'Failed to get account balance: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }
}
