<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class X9Service
{
    protected $baseUrl;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.x9.base_url', env('X9_BASE_URL'));
        $this->accessToken = config('services.x9.access_token', env('X9_ACCESS_TOKEN'));
    }

    /**
     * Test connection to X9 CRM API
     */
    public function testConnection()
    {
        try {
            $response = Http::withHeaders([
                'x-access-token' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/crm/connection');

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
     * Create a new user account in X9
     */
    public function createUser($userData)
    {
        try {
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
                'country_id' => $userData['country_id'] ?? 5
            ];

            $response = Http::withHeaders([
                'x-access-token' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/crm/create_user', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => true,
                    'message' => 'User created successfully',
                    'data' => $data
                ];
            }

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
                'x-access-token' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get($this->baseUrl . '/api/crm/user/' . $loginId);

            if ($response->successful()) {
                return [
                    'status' => true,
                    'message' => 'User details retrieved successfully',
                    'data' => $response->json()
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
     * Deposit/Withdraw balance
     */
    public function manageBalance($loginId, $operationType, $transactionType, $amount, $comment = '', $operateWithoutChecking = true)
    {
        try {
            $payload = [
                'login_id' => $loginId,
                'operation_type' => $operationType, // balance, credit etc
                'transaction_type' => $transactionType, // deposit, withdrawal etc
                'amount' => $amount,
                'comment' => $comment,
                'operate_without_checking' => $operateWithoutChecking
            ];

            $response = Http::withHeaders([
                'x-access-token' => $this->accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->baseUrl . '/api/crm/user/balance', $payload);

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
                'x-access-token' => $this->accessToken,
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
                'x-access-token' => $this->accessToken,
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
                $groups = $response['data'];

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
}
