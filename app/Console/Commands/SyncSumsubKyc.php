<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\KycLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Actions\SubscribeToKlaviyoList;

class SyncSumsubKyc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sumsub:sync-kyc 
                           {--user_id= : Sync KYC for a specific user ID}
                           {--email= : Sync KYC for a specific user email}
                           {--user_ids= : Sync KYC for multiple user IDs (comma separated)}
                           {--emails= : Sync KYC for multiple user emails (comma separated)}
                           {--all-unverified : Sync KYC for all unverified users}
                           {--force : Force sync even for already verified users}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync KYC verification status from Sumsub';

    protected $subscribeToKlaviyoList;

    public function __construct(SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        parent::__construct();
        $this->subscribeToKlaviyoList = $subscribeToKlaviyoList;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Sumsub KYC sync...');

        $users = collect();

        // Determine which users to sync
        if ($this->option('user_id')) {
            $user = User::find($this->option('user_id'));
            if (!$user) {
                $this->error('User not found.');
                return 1;
            }
            $users->push($user);
        } elseif ($this->option('email')) {
            $user = User::where('email', $this->option('email'))->first();
            if (!$user) {
                $this->error('User not found.');
                return 1;
            }
            $users->push($user);
        } elseif ($this->option('user_ids')) {
            $userIds = explode(',', $this->option('user_ids'));
            $userIds = array_map('trim', $userIds);
            $users = User::whereIn('id', $userIds)->get();
            
            if ($users->isEmpty()) {
                $this->error('No users found for the provided IDs.');
                return 1;
            }
        } elseif ($this->option('emails')) {
            $emails = explode(',', $this->option('emails'));
            $emails = array_map('trim', $emails);
            $users = User::whereIn('email', $emails)->get();
            
            if ($users->isEmpty()) {
                $this->error('No users found for the provided emails.');
                return 1;
            }
        } elseif ($this->option('all-unverified')) {
            $users = User::where('kyc_verify', '!=', 1)->get();
        } else {
            $this->error('Please specify --user_id, --email, --user_ids, --emails, or --all-unverified option.');
            return 1;
        }

        if ($users->isEmpty()) {
            $this->info('No users found to sync.');
            return 0;
        }

        $this->info("Found {$users->count()} user(s) to sync.");

        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        $successCount = 0;
        $failedCount = 0;
        $alreadyVerifiedCount = 0;

        foreach ($users as $user) {
            try {
                $result = $this->syncKycFromSumsub($user);
                
                if ($result['status'] === 'true') {
                    if (str_contains($result['message'], 'already verified')) {
                        $alreadyVerifiedCount++;
                    } else {
                        $successCount++;
                    }
                } else {
                    $failedCount++;
                    $this->newLine();
                    $this->warn("Failed to sync KYC for user {$user->email}: {$result['message']}");
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->newLine();
                $this->error("Error syncing KYC for user {$user->email}: {$e->getMessage()}");
                Log::error('KYC Sync Command Error', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("Sync completed!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Successfully synced', $successCount],
                ['Already verified', $alreadyVerifiedCount],
                ['Failed', $failedCount],
                ['Total processed', $users->count()]
            ]
        );

        return 0;
    }

    /**
     * Core method to sync KYC data from Sumsub
     */
    private function syncKycFromSumsub(User $user)
    {
        $email = $user->email;
        
        // Try to find applicant by user email
        $applicantId = $this->getApplicantIdByEmail($email);
        
        if (!$applicantId) {
            return ['status' => 'false', 'message' => 'No applicant found for this user in Sumsub.'];
        }

        // Get applicant status from Sumsub
        $statusResponse = $this->getSumsubApplicantStatus($applicantId);
        
        if (!$statusResponse) {
            return ['status' => 'false', 'message' => 'Failed to get applicant status from Sumsub.'];
        }

        // Get full applicant details
        $applicantDetails = $this->getSumsubApplicantDetails($applicantId);
        
        // Create comprehensive response with all available data
        $comprehensiveResponse = [
            'applicant_id' => $applicantId,
            'email' => $email,
            'user_id' => $user->id,
            'sync_timestamp' => now()->toISOString(),
            'sync_source' => 'artisan_command',
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

        // Always log the comprehensive response first
        KycLog::create([
            'client_id' => $email,
            'user_id' => $user->id,
            'callback_code' => 'KYC_SYNC_COMPREHENSIVE_DATA_COMMAND',
            'callback_payload' => $comprehensiveResponse,
        ]);

        // Also log individual components for debugging
        KycLog::create([
            'client_id' => $email,
            'user_id' => $user->id,
            'callback_code' => 'KYC_SYNC_STATUS_RESPONSE_COMMAND',
            'callback_payload' => $statusResponse,
        ]);
        
        if ($applicantDetails) {
            KycLog::create([
                'client_id' => $email,
                'user_id' => $user->id,
                'callback_code' => 'KYC_SYNC_APPLICANT_DETAILS_COMMAND',
                'callback_payload' => $applicantDetails,
            ]);

            Log::info('Sumsub command sync comprehensive data', [
                'applicant_id' => $applicantId,
                'user_id' => $user->id,
                'email' => $email,
                'has_status_response' => !empty($statusResponse),
                'has_applicant_details' => !empty($applicantDetails),
                'comprehensive_response_size' => strlen(json_encode($comprehensiveResponse)),
            ]);
        }

        // Check if review status is completed and approved
        if (isset($statusResponse['reviewStatus']) && $statusResponse['reviewStatus'] == 'completed') {
            if (isset($statusResponse['reviewResult']['reviewAnswer']) && $statusResponse['reviewResult']['reviewAnswer'] == 'GREEN') {
                
                // Check if user's KYC is already verified
                if ($user->kyc_verify == 1 && !$this->option('force')) {
                    return ['status' => 'true', 'message' => 'User KYC was already verified. Data synced and logged.'];
                }

                // Update user's KYC status to verified
                $user->update(['kyc_verify' => 1]);

                // Subscribe to Klaviyo list if configured
                $list_id = @config('services.klaviyo.list_ids')['KYC_COMPLETED'];
                if ($list_id) {
                    $this->subscribeToKlaviyoList->handle($user, $list_id);
                }

                return ['status' => 'true', 'message' => 'KYC status synced and verified successfully with complete data.'];
                
            } else {
                return ['status' => 'false', 'message' => 'KYC review completed but not approved (not GREEN status).'];
            }
        } else {
            return ['status' => 'false', 'message' => 'KYC review not completed yet.'];
        }
    }

    /**
     * Get applicant ID by email from Sumsub
     */
    private function getApplicantIdByEmail($email)
    {
        $timestamp = time();
        $requestMethod = 'GET';
        $secretKey = config('services.sumsub.api_secret');
        $apiUrl = '/resources/applicants?email=' . urlencode($email);
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
        ])->get('https://api.sumsub.com' . $apiUrl);

        if ($response->status() != 200) {
            Log::error('Failed to get applicant by email', [
                'email' => $email,
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;
        }

        $applicants = $response->json();
        
        // If applicants found, return the first one's ID
        if (isset($applicants['items']) && count($applicants['items']) > 0) {
            return $applicants['items'][0]['id'];
        }

        return null;
    }

    /**
     * Get applicant status from Sumsub
     */
    private function getSumsubApplicantStatus($applicantId)
    {
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
    }

    /**
     * Get full applicant details from Sumsub
     */
    private function getSumsubApplicantDetails($applicantId)
    {
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
    }
}
