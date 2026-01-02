<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Zapier\CreateZapierAccountRequest;
use App\Services\ZapierAccountCreationService;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Zapier Webhook Controller
 * 
 * Handles incoming webhook requests from Zapier
 * - Validates incoming data
 * - Triggers account creation service
 * - Returns appropriate responses
 */
class ZapierWebhookController extends Controller
{
    protected $zapierService;

    public function __construct(ZapierAccountCreationService $zapierService)
    {
        $this->zapierService = $zapierService;
    }

    /**
     * Handle Zapier webhook for account creation
     * 
     * @param CreateZapierAccountRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAccount(CreateZapierAccountRequest $request)
    {
        try {
            // Validate secret header (recommended for public webhooks)
            $expected = env('ZAPIER_SECRET');
            if ($expected) {
                $provided = $request->header('X-Zapier-Secret');
                if (!$provided || $provided !== $expected) {
                    Log::warning('Zapier webhook unauthorized', [
                        'provided' => $provided ? 'present' : 'missing'
                    ]);

                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized',
                        'error_code' => 'UNAUTHORIZED'
                    ], 401);
                }
            }
            // Get sanitized validated data
            $data = $request->sanitize();

            Log::info("Zapier webhook received", [
                'email' => $data['email'],
                'name' => $data['name'],
                'account_type' => $data['account_type']
            ]);

            // Create account using service
            $result = $this->zapierService->createAccount($data);

            Log::info("Zapier account creation successful", [
                'user_id' => $result['user']->id,
                'account_id' => $result['account']->id,
                'email' => $result['user']->email
            ]);

            // Return success response
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => [
                    'user_id' => $result['user']->id,
                    'email' => $result['user']->email,
                    'account_code' => $result['account']->code,
                    'account_id' => $result['account']->id,
                    'bonus_amount' => $result['bonus'] ? $result['bonus']->bonus_amount : 50,
                ],
            ], 201);
        } catch (Exception $e) {
            Log::error("Zapier webhook error", [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create account: ' . $e->getMessage(),
                'error_code' => 'ACCOUNT_CREATION_FAILED',
            ], 400);
        }
    }

    /**
     * Health check endpoint for Zapier
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function healthCheck()
    {
        return response()->json([
            'status' => 'ok',
            'service' => 'Zapier Account Creation Webhook',
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
