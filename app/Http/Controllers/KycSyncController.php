<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Actions\SubscribeToKlaviyoList;
use Illuminate\Support\Facades\Validator;

class KycSyncController extends Controller
{
    protected $subscribeToKlaviyoList;

    public function __construct(SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $this->subscribeToKlaviyoList = $subscribeToKlaviyoList;
    }

    /**
     * Display the KYC sync page
     */
    public function index()
    {
        return view('admin.kyc_sync');
    }

    /**
     * Debug endpoint to test applicant ID retrieval
     */
    public function debugApplicant(Request $request)
    {
        if (!$request->email) {
            return response()->json([
                'error' => 'Email parameter is required'
            ], 400);
        }

        $email = $request->email;
        $applicantId = $this->getApplicantIdByEmail($email);

        return response()->json([
            'email' => $email,
            'applicant_id' => $applicantId,
            'found' => !empty($applicantId)
        ]);
    }

    /**
     * Sync KYC data from Sumsub for a specific user
     */
    public function syncUser(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_id' => 'nullable|exists:aspnetusers,id',
                'user_email' => 'nullable|email|exists:aspnetusers,email',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Ensure at least one identifier is provided
            if (!$request->user_id && !$request->user_email) {
                return response()->json([
                    'status' => false,
                    'message' => 'Either user ID or user email is required.'
                ], 400);
            }

            // Find user by ID or email
            $user = $this->findUser($request->user_id, $request->user_email);
        
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found.'
                ], 404);
            }

            // Sync KYC data
            $result = $this->performKycSync($user);

            return response()->json([
                'status' => $result['success'],
                'message' => $result['message'],
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'kyc_status' => $user->fresh()->kyc_verify
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('KYC Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while syncing KYC data. Please try again.'
            ], 500);
        }
    }

    /**
     * Bulk sync KYC data from Sumsub for multiple users
     */
    public function bulkSync(Request $request)
    {
        try {
            // Validate input
            $validator = Validator::make($request->all(), [
                'user_ids' => 'nullable|array',
                'user_ids.*' => 'integer|exists:aspnetusers,id',
                'user_emails' => 'nullable|array',
                'user_emails.*' => 'email|exists:aspnetusers,email',
                'sync_all_unverified' => 'nullable|boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get users to sync
            $users = $this->getUsersForBulkSync($request);

            if ($users->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No users found to sync.'
                ], 400);
            }

            // Perform bulk sync
            $results = $this->performBulkSync($users);

            return response()->json([
                'status' => true,
                'message' => 'Bulk sync completed successfully.',
                'data' => [
                    'total_processed' => count($results),
                    'successful' => collect($results)->where('success', true)->count(),
                    'failed' => collect($results)->where('success', false)->count(),
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Bulk KYC Sync Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred during bulk sync. Please try again.'
            ], 500);
        }
    }

    /**
     * Find user by ID or email
     */
    private function findUser($userId = null, $userEmail = null)
    {       
        if ($userId) {
            return User::find($userId);
        }
        
        if ($userEmail) {
            return User::where('email', $userEmail)->first();
        }
        
        return null;
    }

    /**
     * Get users for bulk sync based on request parameters
     */
    private function getUsersForBulkSync(Request $request)
    {
        $users = collect();

        if ($request->sync_all_unverified) {
            return User::where('kyc_verify', '!=', 1)->get();
        }

        // Get users by IDs
        if ($request->user_ids) {
            $usersByIds = User::whereIn('id', $request->user_ids)->get();
            $users = $users->merge($usersByIds);
        }
        
        // Get users by emails
        if ($request->user_emails) {
            $usersByEmails = User::whereIn('email', $request->user_emails)->get();
            $users = $users->merge($usersByEmails);
        }
        
        // Remove duplicates
        return $users->unique('id');
    }

    /**
     * Perform bulk sync for multiple users
     */
    private function performBulkSync($users)
    {
        $results = [];

        foreach ($users as $user) {
            try {
                $result = $this->performKycSync($user);
                $results[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'success' => $result['success'],
                    'message' => $result['message']
                ];
            } catch (\Exception $e) {
                Log::error('Individual KYC Sync Error in Bulk', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                
                $results[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'success' => false,
                    'message' => 'Failed to sync: ' . $e->getMessage()
                ];
            }
        }

        return $results;
    }

    /**
     * Core method to sync KYC data from Sumsub
     */
    private function performKycSync(User $user)
    {
        $email = $user->email;
        
        // Try to find applicant by user email
        $applicantId = $this->getApplicantIdByEmail($email);
    
        // Add debug logging for applicant ID retrieval
        Log::info('Applicant ID retrieval result', [
            'email' => $email,
            'user_id' => $user->id,
            'applicant_id' => $applicantId,
            'found_applicant' => !empty($applicantId)
        ]);
        
        if (!$applicantId) {
            return [
                'success' => false,
                'message' => 'No applicant found for this user in Sumsub.'
            ];
        }

        // Get applicant status from Sumsub
        $statusResponse = $this->getSumsubApplicantStatus($applicantId);
        
        if (!$statusResponse) {
            return [
                'success' => false,
                'message' => 'Failed to get applicant status from Sumsub.'
            ];
        }

        // Get full applicant details
        $applicantDetails = $this->getSumsubApplicantDetails($applicantId);
        
        // Create comprehensive response with all available data
        $comprehensiveResponse = [
            'applicant_id' => $applicantId,
            'email' => $email,
            'user_id' => $user->id,
            'sync_timestamp' => now()->toISOString(),
            'status_response' => $statusResponse,
            'applicant_details' => $applicantDetails,
            'api_responses' => [
                'status_api_response' => $statusResponse,
                'details_api_response' => $applicantDetails
            ]
        ];

        // If we have applicant details, add structured data
        if ($applicantDetails && is_array($applicantDetails)) {
            $comprehensiveResponse['structured_data'] = [
                'id' => $applicantDetails['id'] ?? null,
                'externalUserId' => $applicantDetails['externalUserId'] ?? null,
                'info' => $applicantDetails['info'] ?? null,
                'email' => $applicantDetails['email'] ?? null,
                'phone' => $applicantDetails['phone'] ?? null,
                'fixedInfo' => $applicantDetails['fixedInfo'] ?? null,
                'review' => $applicantDetails['review'] ?? null,
                'questionnaires' => $applicantDetails['questionnaires'] ?? null,
                'requiredIdDocs' => $applicantDetails['requiredIdDocs'] ?? null,
                'applicantPlatform' => $applicantDetails['applicantPlatform'] ?? null,
                'clientId' => $applicantDetails['clientId'] ?? null,
                'type' => $applicantDetails['type'] ?? null,
                'sandboxMode' => $applicantDetails['sandboxMode'] ?? null,
                'lang' => $applicantDetails['lang'] ?? null,
                'metadata' => $applicantDetails['metadata'] ?? null
            ];
        }

        // Log only the comprehensive response to avoid duplicate entries
        $this->logKycResponse($user, 'KYC_SYNC_SUCCESS', $comprehensiveResponse);

        Log::info('Sumsub sync comprehensive data', [
            'applicant_id' => $applicantId,
            'user_id' => $user->id,
            'email' => $email,
            'has_status_response' => !empty($statusResponse),
            'has_applicant_details' => !empty($applicantDetails),
            'status_keys' => is_array($statusResponse) ? array_keys($statusResponse) : 'not_array',
            'details_keys' => is_array($applicantDetails) ? array_keys($applicantDetails) : 'not_array',
            'comprehensive_response_size' => strlen(json_encode($comprehensiveResponse)),
        ]);

        // Check if review status is completed and approved
        if (!$this->isKycApproved($statusResponse)) {
            // Still log what we have even if not approved
            $partialResponse = [
                'applicant_id' => $applicantId,
                'email' => $email,
                'user_id' => $user->id,
                'sync_timestamp' => now()->toISOString(),
                'status_response' => $statusResponse,
                'applicant_details' => $applicantDetails,
                'approval_status' => 'not_approved',
                'reason' => $this->getKycStatusMessage($statusResponse)
            ];
            $this->logKycResponse($user, 'KYC_SYNC_NOT_APPROVED', $partialResponse);
            
            return [
                'success' => false,
                'message' => $this->getKycStatusMessage($statusResponse)
            ];
        }

        // Check if user's KYC is already verified
        // if ($user->kyc_verify == 1) {
        //     return [
        //         'success' => true,
        //         'message' => 'User KYC was already verified. Complete data synced and logged.'
        //     ];
        // }

        // Update user's KYC status to verified
        // $user->update(['kyc_verify' => 1]);

        // Subscribe to Klaviyo list if configured
        $this->subscribeToKlaviyo($user);

        return [
            'success' => true,
            'message' => 'KYC status synced and verified successfully with complete data.'
        ];
    }

    /**
     * Check if KYC is approved based on Sumsub response
     */
    private function isKycApproved($statusResponse)
    {
        return isset($statusResponse['reviewStatus']) 
            && $statusResponse['reviewStatus'] == 'completed'
            && isset($statusResponse['reviewResult']['reviewAnswer']) 
            && $statusResponse['reviewResult']['reviewAnswer'] == 'GREEN';
    }

    /**
     * Get KYC status message based on Sumsub response
     */
    private function getKycStatusMessage($statusResponse)
    {
        if (!isset($statusResponse['reviewStatus'])) {
            return 'KYC review status is not available.';
        }

        if ($statusResponse['reviewStatus'] != 'completed') {
            return 'KYC review is not completed yet.';
        }

        if (!isset($statusResponse['reviewResult']['reviewAnswer']) 
            || $statusResponse['reviewResult']['reviewAnswer'] != 'GREEN') {
            return 'KYC review completed but not approved.';
        }

        return 'KYC status is pending.';
    }

    /**
     * Log KYC response to database
     */
    private function logKycResponse(User $user, $callbackCode, $payload)
    {
        try {
            // Ensure payload is properly serialized
            $serializedPayload = is_array($payload) || is_object($payload) ? $payload : ['raw_data' => $payload];
            
            $logEntry = KycLog::create([
                'client_id' => $user->email,
                'user_id' => $user->id,
                'callback_code' => $callbackCode,
                'callback_payload' => $serializedPayload,
            ]);

            Log::info('KYC response logged successfully', [
                'log_id' => $logEntry->id,
                'user_id' => $user->id,
                'callback_code' => $callbackCode,
                'payload_size' => strlen(json_encode($serializedPayload)),
                'payload_type' => gettype($payload),
                'has_applicant_id' => isset($serializedPayload['applicant_id']),
                'payload_keys' => is_array($serializedPayload) ? array_keys($serializedPayload) : 'not_array'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log KYC response', [
                'user_id' => $user->id,
                'callback_code' => $callbackCode,
                'error' => $e->getMessage(),
                'payload_type' => gettype($payload),
                'payload_preview' => is_string($payload) ? substr($payload, 0, 100) : 'not_string'
            ]);
        }
    }

    /**
     * Subscribe user to Klaviyo list
     */
    private function subscribeToKlaviyo(User $user)
    {
        try {
            $klaviyoConfig = config('services.klaviyo');
            $list_id = $klaviyoConfig['list_ids']['KYC_COMPLETED'] ?? null;
            
            if ($list_id) {
                $this->subscribeToKlaviyoList->handle($user, $list_id);
            } else {
                Log::info('Klaviyo KYC_COMPLETED list ID not configured, skipping subscription', [
                    'user_id' => $user->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to subscribe to Klaviyo', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get applicant ID by email from Sumsub
     */
    private function getApplicantIdByEmail($email)
    {
        try {
            // Try to get applicant by external user ID (email)
            $applicantId = $this->getApplicantByExternalUserId($email);
                
            if ($applicantId) {
                return $applicantId;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Exception getting applicant by email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get applicant by external user ID using Sumsub API
     */
    private function getApplicantByExternalUserId($externalUserId)
    {
        try {
            $timestamp = time();
            $requestMethod = 'GET';
            $secretKey = config('services.sumsub.api_secret');
            $apiUrl = '/resources/applicants/-;externalUserId=' . urlencode($externalUserId);
            $requestBody = '';

            $valueToSign = $timestamp . $requestMethod . $apiUrl;
            if (!empty($requestBody)) {
                $valueToSign .= $requestBody;
            }

            $signature = hash_hmac('sha256', $valueToSign, $secretKey, true);
            $signatureHex = bin2hex($signature);

            $response = Http::withHeaders([
                'X-App-Token' => config('services.sumsub.api_token'),
                'X-App-Access-Sig' => $signatureHex,
                'X-App-Access-Ts' => $timestamp,
                'Accept' => 'application/json',
            ])->get('https://api.sumsub.com' . $apiUrl);
 
            if ($response->status() == 200) {
                $applicant = $response->json();
                
                // Use either id or inspectionId based on what's available
                $applicantId = $applicant['list']['items'][0]['id'] ?? $applicant['list']['items'][0]['inspectionId'] ?? null;

                if ($applicantId) {
                    Log::info('Found applicant', [
                        'external_user_id' => $externalUserId,
                        'applicant_id' => $applicantId,
                        'inspection_id' => $applicant['inspectionId'] ?? null,
                        'key' => $applicant['key'] ?? null
                    ]);
                    return $applicantId;
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Exception getting applicant by external user ID', [
                'external_user_id' => $externalUserId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get applicant status from Sumsub using official API
     * Endpoint: GET /resources/applicants/{applicantId}/status
     */
    /**
     * Get applicant status from Sumsub
     */
    private function getSumsubApplicantStatus($applicantId)
    {
        try {
            $timestamp = time();
            $requestMethod = 'GET';
            $secretKey = config('services.sumsub.api_secret');
            $apiUrl = '/resources/applicants/' . $applicantId . '/status';
            $requestBody = '';

            $valueToSign = $timestamp . $requestMethod . $apiUrl;
            if (!empty($requestBody)) {
                $valueToSign .= $requestBody;
            }

            $signature = hash_hmac('sha256', $valueToSign, $secretKey, true);
            $signatureHex = bin2hex($signature);

            $response = Http::withHeaders([
                'X-App-Token' => config('services.sumsub.api_token'),
                'X-App-Access-Sig' => $signatureHex,
                'X-App-Access-Ts' => $timestamp,
                'Accept' => 'application/json',
            ])->get('https://api.sumsub.com' . $apiUrl);

            if ($response->status() != 200) {
                Log::error('Failed to get applicant status', [
                    'applicant_id' => $applicantId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Exception getting applicant status', [
                'applicant_id' => $applicantId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get full applicant details from Sumsub
     */
    private function getSumsubApplicantDetails($applicantId)
    {
        try {
            $timestamp = time();
            $requestMethod = 'GET';
            $secretKey = config('services.sumsub.api_secret');
            $apiUrl = '/resources/applicants/' . $applicantId . '/one';
            $requestBody = '';

            $valueToSign = $timestamp . $requestMethod . $apiUrl;
            if (!empty($requestBody)) {
                $valueToSign .= $requestBody;
            }

            $signature = hash_hmac('sha256', $valueToSign, $secretKey, true);
            $signatureHex = bin2hex($signature);

            $response = Http::withHeaders([
                'X-App-Token' => config('services.sumsub.api_token'),
                'X-App-Access-Sig' => $signatureHex,
                'X-App-Access-Ts' => $timestamp,
                'Accept' => 'application/json',
            ])->get('https://api.sumsub.com' . $apiUrl);

            if ($response->status() != 200) {
                Log::error('Failed to get applicant details', [
                    'applicant_id' => $applicantId,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Exception getting applicant details', [
                'applicant_id' => $applicantId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
