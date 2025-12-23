<?php

namespace App\Http\Controllers\Api;

use App\Services\X9Service;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class X9Controller extends Controller
{
    protected $x9Service;

    public function __construct(X9Service $x9Service)
    {
        $this->x9Service = $x9Service;
    }

    /**
     * Test connection to X9 CRM API
     */
    public function testConnection(): JsonResponse
    {
        $response = $this->x9Service->testConnection();

        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 500);
    }

    /**
     * Create a new user in X9
     */
    public function createUser(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'master_password' => 'required|string|min:6',
            'investor_password' => 'required|string|min:6',
            'client_group_type_id' => 'integer',
            'client_group_id' => 'integer',
            'country_id' => 'integer',
            'phone' => 'nullable|string|max:20',
            'middle_name' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        $response = $this->x9Service->createUser($validatedData);
        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 400);
    }

    /**
     * Get user details by login ID
     */
    public function getUserDetails($loginId): JsonResponse
    {
        $response = $this->x9Service->getUserDetails($loginId);

        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 404);
    }

    /**
     * Manage user balance (deposit/withdraw)
     */
    public function manageBalance(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'login_id' => 'required|integer',
            'operation_type' => 'required|string|in:balance,credit',
            'transaction_type' => 'required|string|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:0',
            'comment' => 'nullable|string|max:255',
            'operate_without_checking' => 'boolean'
        ]);

        $response = $this->x9Service->manageBalance(
            $validatedData['login_id'],
            $validatedData['operation_type'],
            $validatedData['transaction_type'],
            $validatedData['amount'],
            $validatedData['comment'] ?? '',
            $validatedData['operate_without_checking'] ?? true
        );

        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 400);
    }

    /**
     * Get client group types
     */
    public function getClientGroupTypes(): JsonResponse
    {
        $response = $this->x9Service->getClientGroupTypes();

        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 500);
    }

    /**
     * Get client groups by type ID
     */
    public function getClientGroupsByType($typeId): JsonResponse
    {
        $response = $this->x9Service->getClientGroupsByType($typeId);

        return response()->json([
            'success' => $response['status'],
            'message' => $response['message'],
            'data' => $response['data']
        ], $response['status'] ? 200 : 500);
    }
}
