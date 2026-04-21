<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycLog;
use App\Models\Account;
use App\Models\KycUpdate;
use Illuminate\Support\Str;
use App\Models\ClientWallet;
use App\Models\EmployeeList;
use Illuminate\Http\Request;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Actions\SubscribeToKlaviyoList;
use App\Services\VeriffService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Users extends Controller
{
    protected $mailService;

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }


    public function api_call(Request $request)
    {
        // dd($request->all());
        $user = EmployeeList::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        // Create Sanctum token
        $token = $user->createToken('api_call')->plainTextToken;

        return response()->json([
            //            'user' => $user,
            'token' => $token
        ], 201);
    }



    public function profile()
    {
        $user_id = auth()->user()->id;
        $bank_accounts = ClientWallet::where('user_id', $user_id)->orderBy('id', 'desc')->paginate(5);
        $user = User::where('id', $user_id)->withLatestKycLog()->first();

        // $verf_docs = KycUpdate::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        return view('profile', compact('bank_accounts', 'user'));
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                new \App\Rules\ValidPassword(),
                'confirmed',
            ],
        ], [
            'new_password.required' => 'The new password field is required.',
            'new_password.confirmed' => 'Passwords do not match.',
        ]);

        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 422);
        }
        // dd($request->new_password);
        // Update password
        $user->update(['password' => Hash::make($request->new_password)]);
        activity()
            ->causedBy(auth()->user()->id)
            ->withProperties([
                'ip' => request()->ip(),
                'user_email' => auth()->user()->email,
                'username' => auth()->user()->username,
                'user_id' => auth()->user()->id,
                'new_passowrd' => $request->new_password,
                'remark' => 'Update Client Password'
            ])
            ->event('update')
            ->log('Update Client Password');

        Auth::logoutOtherDevices($request->new_password);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['success' => 'Password Successfully Changed']);
    }
    public function changeProfileImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // If validation fails, return a detailed error response
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $email = auth()->user()->email;
        $user = DB::table('aspnetusers')->where('email', $email)->first();

        if ($user && $request->hasFile('profile_picture')) {
            // Check if there's an existing profile image and delete it
            if ($user->profile_image_url) {
                // Delete the old image from storage
                $oldImagePath = 'profile_images/' . $user->profile_image_url;
                if (Storage::disk('public')->exists($oldImagePath)) {
                    Storage::disk('public')->delete($oldImagePath);
                }
            }
            // Store the uploaded file in the 'profile_images' directory within 'public' storage
            $filePath = $request->file('profile_picture')->store('profile_images', 'public');
            // Extract only the filename from the stored path
            $filename = basename($filePath);
            // Update the database with only the filename
            DB::table('aspnetusers')->where('email', $email)->update(['profile_image_url' => $filename]);
            return response()->json(['success' => 'Profile Image Successfully Changed']);
        }

        return response()->json(['error' => 'Unable to update profile image'], 400);
    }

    public function sumsub()
    {
        $user = auth()->user();
        $secretKey = config('services.sumsub.api_secret');
        $timestamp = time(); // Current timestamp in seconds

        // Example values (replace with actual values as needed)
        // $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
        $appToken = config('services.sumsub.api_token');
        $apiUrl = '/resources/accessTokens/sdk'; // URI of the request
        $requestMethod = 'POST'; // HTTP method
        $requestBody = json_encode(
            [
                'userId' => $user->email,
                'levelName' => 'basic-kyc-level',
            ]
        ); // Add your request body if needed, empty for this example

        // Create the valueToSign string
        $valueToSign = $timestamp . $requestMethod . $apiUrl;

        if (!empty($requestBody)) {
            $valueToSign .= $requestBody;
        }

        // Compute HMAC SHA256 signature
        $signature = hash_hmac('sha256', $valueToSign, $secretKey, true); // Binary format

        // Convert binary signature to hexadecimal
        $signatureHex = bin2hex($signature);
        // Initialize cURL
        $curl = curl_init();
        // Set cURL options
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sumsub.com' . $apiUrl, // Full URL including hostname
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestMethod,
            CURLOPT_POSTFIELDS => $requestBody,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-App-Token: ' . $appToken,
                'X-App-Access-Ts: ' . $timestamp,
                'X-App-Access-Sig: ' . $signatureHex,
            ],
        ]);
        // Execute cURL request and fetch response
        $response = curl_exec($curl);

        // Check for cURL errors
        if (curl_errno($curl)) {
            return response()->json(['error' => curl_error($curl)], 500);
        }

        // Parse the response
        $auth = json_decode($response);
        // Close cURL session
        curl_close($curl);
        $token = $auth->token ?? null;
        return view('sumsub', compact('token'));
    }

    /**
     * Start Veriff KYC verification for the authenticated user.
     * Redirects directly to Veriff verification page.
     */
    public function veriff(VeriffService $veriffService)
    {
        try {
            $user = auth()->user();

            if (! $user) {
                return redirect()->route('login');
            }

            $session = $veriffService->createSession([
                'email' => $user->email,
                'first_name' => $user->fullname ?? $user->first_name ?? null,
                'last_name' => $user->last_name ?? null,
            ]);

            $sessionUrl = $session['verification']['url'];

            // Redirect directly to Veriff
            return redirect()->away($sessionUrl);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Veriff connection error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return redirect()->route('user-profile')
                ->with('error', 'Unable to connect to verification service. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Veriff verification error', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            $userFriendlyMessage = 'An error occurred while starting verification. Please try again later.';

            if (str_contains($e->getMessage(), 'credentials are not configured')) {
                $userFriendlyMessage = 'Verification service is not properly configured. Please contact support.';
            }

            return redirect()->route('user-profile')
                ->with('error', $userFriendlyMessage);
        }
    }

    /**
     * Handle Veriff frontend events (similar to sumsub_verify).
     * This logs events from the verification iframe.
     * Actual KYC status is updated via Veriff webhooks.
     */
    public function veriff_event(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['status' => 'false', 'message' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $type = $request->input('type', 'unknown');
        $payload = $request->input('payload', []);

        Log::info('Veriff frontend event', [
            'user_id' => $user->id,
            'email' => $user->email,
            'type' => $type,
            'payload' => $payload,
        ]);

        // Log the event
        KycLog::create([
            'client_id' => $user->email,
            'user_id' => $user->id,
            'callback_code' => 'VERIFF_FRONTEND_' . strtoupper($type),
            'callback_payload' => $payload,
        ]);

        // Check if user is already verified
        if ($user->kyc_verify) {
            return response()->json([
                'status' => 'verified',
                'message' => 'Your KYC is already verified'
            ]);
        }

        // If event indicates completion, check user status
        // (Actual verification happens via webhook, this is just for UI feedback)
        if (in_array(strtoupper($type), ['FINISHED', 'SUBMITTED'])) {
            return response()->json([
                'status' => 'submitted',
                'message' => 'Verification submitted. We will review your documents shortly.'
            ]);
        }

        return response()->json([
            'status' => 'received',
            'message' => 'Event received'
        ]);
    }

    public function sumsub_verify(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        if (auth()->check() && $request->has(['sumsub', 'type', 'payload'])) {
            $email = auth()->user()->email;
            $type = $request->input('type');
            $payload = $request->input('payload');

            // $type='idCheck.onApplicantStatusChanged';
            // $payload=['reviewStatus'=>'completed','reviewResult'=>["reviewAnswer"=>"GREEN"]];
            if ($type == 'idCheck.onApplicantStatusChanged') {
                $applicantId = $payload['applicantId'];
                $timestamp = time();
                $requestMethod = "GET";
                $secretKey = config('services.sumsub.api_secret');
                $apiUrl = '/resources/applicants/' . $applicantId . '/status'; // URI of the request
                $requestBody = ''; // Add your request body if needed, empty for this example

                // Create the valueToSign string
                $valueToSign = $timestamp . $requestMethod . $apiUrl;

                if (!empty($requestBody)) {
                    $valueToSign .= $requestBody;
                }

                // Compute HMAC SHA256 signature
                $signature = hash_hmac('sha256', $valueToSign, $secretKey, true); // Binary format

                // Convert binary signature to hexadecimal
                $signatureHex = bin2hex($signature);
                $response = Http::withHeaders([
                    'X-App-Token' => config('services.sumsub.api_token'),
                    'X-App-Access-Sig' => $signatureHex,
                    'X-App-Access-Ts' => $timestamp,
                ])->get('https://api.sumsub.com' . $apiUrl);
                if ($response->status() != 200) {
                    return response()->json(['status' => 'false', 'message' => 'Something went wrong. Please try again or Create a Support Ticket']);
                }
                $payload = $response->json();

                // Store callback log in the database
                KycLog::create([
                    'client_id' => $email,
                    'user_id' => auth()->user()->id,
                    'callback_code' => json_encode($type),
                    'callback_payload' => $payload,
                ]);
                // Check if review status is completed
                if (isset($payload['reviewStatus']) && $payload['reviewStatus'] == 'completed') {

                    // $response = $client->request('GET', 'https://api.sumsub.com/resources/applicants/67a1c0ad52ff86587fa5f1c0/status', [
                    //     'headers' => [
                    //       'X-App-Token' => 'sbx:qVcQDPeFQuB7xcGhX0MYvt80.pVSvzRBOm2Y4Qw4mI4G42vfDBDFJw1Ek',
                    //     ],
                    //   ]);
                    // Check review result
                    if (isset($payload['reviewResult']['reviewAnswer']) && $payload['reviewResult']['reviewAnswer'] == 'GREEN') {

                        $apiUrl = '/resources/applicants/' . $applicantId . '/one'; // URI of the request
                        $requestBody = ''; // Add your request body if needed, empty for this example

                        // Create the valueToSign string
                        $valueToSign = $timestamp . $requestMethod . $apiUrl;

                        if (!empty($requestBody)) {
                            $valueToSign .= $requestBody;
                        }

                        // Compute HMAC SHA256 signature
                        $signature = hash_hmac('sha256', $valueToSign, $secretKey, true); // Binary format

                        // Convert binary signature to hexadecimal
                        $signatureHex = bin2hex($signature);
                        $response = Http::withHeaders([
                            'X-App-Token' => config('services.sumsub.api_token'),
                            'X-App-Access-Sig' => $signatureHex,
                            'X-App-Access-Ts' => $timestamp,
                        ])->get('https://api.sumsub.com' . $apiUrl);
                        if ($response->status() != 200) {
                            return response()->json(['status' => 'false', 'message' => 'Something went wrong. Please try again or Create a Support Ticket']);
                        }
                        $payload = $response->json();
                        Log::info('Sumsub user payload', ['payload' => $payload]);
                        // Find the user in the database
                        $user = User::where('email', $email)->first();

                        // Check if the user's KYC is already verified
                        if ($user && $user->kyc_verify == 1) {
                            return response()->json(['status' => 'true', 'message' => 'Your KYC Already Verified']);
                        }

                        // Update user's KYC status to verified
                        User::where('email', $email)
                            ->update(['kyc_verify' => 1]);
                        $list_id = @config('services.klaviyo.list_ids')['KYC_COMPLETED'];
                        if ($list_id) {
                            $subscribeToKlaviyoList->handle($user, $list_id);
                        }

                        return response()->json(['status' => 'true', 'message' => 'KYC Verified']);
                    } else {
                        return response()->json(['status' => 'false', 'message' => 'Something went wrong. Please try again or Create a Support Ticket']);
                    }
                } else {
                    return response()->json(['status' => 'false', 'message' => 'Status in progress..']);
                }
            } else {
                return response()->json(['status' => 'false', 'message' => 'Status in progress...']);
            }
        }

        // Return a default response if session or parameters are missing
        return response()->json(['status' => 'false', 'message' => 'Invalid request.']);
    }

    /**
     * Sync KYC data from Sumsub for a specific user
     */
    public function syncUserKyc(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $request->validate([
            'user_id' => 'nullable|integer|exists:users,id',
            'user_email' => 'nullable|email|exists:users,email',
        ]);

        // Ensure at least one identifier is provided
        if (!$request->user_id && !$request->user_email) {
            return response()->json([
                'status' => 'false',
                'message' => 'Either user_id or user_email is required.'
            ]);
        }

        // Find user by ID or email
        $user = null;
        if ($request->user_id) {
            $user = User::find($request->user_id);
        } elseif ($request->user_email) {
            $user = User::where('email', $request->user_email)->first();
        }

        if (!$user) {
            return response()->json(['status' => 'false', 'message' => 'User not found.']);
        }

        try {
            $result = $this->syncKycFromSumsub($user, $subscribeToKlaviyoList);
            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('KYC Sync Error for User ID ' . $user->id, [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'false',
                'message' => 'Failed to sync KYC data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Bulk sync KYC data from Sumsub for multiple users
     */
    public function bulkSyncKyc(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        $request->validate([
            'user_ids' => 'array',
            'user_ids.*' => 'integer|exists:users,id',
            'user_emails' => 'array',
            'user_emails.*' => 'email|exists:users,email',
            'sync_all_unverified' => 'boolean',
        ]);

        $results = [];
        $users = collect();

        if ($request->sync_all_unverified) {
            // Get all users who don't have kyc_verify = 1 but might be verified on Sumsub
            $users = User::where('kyc_verify', '!=', 1)->get();
        } elseif ($request->user_ids || $request->user_emails) {
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

            // Remove duplicates (in case same user was specified by both ID and email)
            $users = $users->unique('id');
        } else {
            return response()->json(['status' => 'false', 'message' => 'Either user_ids, user_emails, or sync_all_unverified option is required.']);
        }

        if ($users->isEmpty()) {
            return response()->json(['status' => 'false', 'message' => 'No users found to sync.']);
        }

        foreach ($users as $user) {
            try {
                $result = $this->syncKycFromSumsub($user, $subscribeToKlaviyoList);
                $results[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'result' => $result
                ];
            } catch (\Exception $e) {
                Log::error('Bulk KYC Sync Error for User ID ' . $user->id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                $results[] = [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'result' => [
                        'status' => 'false',
                        'message' => 'Failed to sync: ' . $e->getMessage()
                    ]
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Bulk sync completed.',
            'results' => $results,
            'total_processed' => count($results)
        ]);
    }

    /**
     * Core method to sync KYC data from Sumsub
     */
    private function syncKycFromSumsub(User $user, SubscribeToKlaviyoList $subscribeToKlaviyoList)
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

        // Log the status response
        KycLog::create([
            'client_id' => $email,
            'user_id' => $user->id,
            'callback_code' => 'KYC_SYNC_STATUS_CHECK',
            'callback_payload' => $statusResponse,
        ]);

        // Check if review status is completed and approved
        if (isset($statusResponse['reviewStatus']) && $statusResponse['reviewStatus'] == 'completed') {
            if (isset($statusResponse['reviewResult']['reviewAnswer']) && $statusResponse['reviewResult']['reviewAnswer'] == 'GREEN') {

                // Get full applicant details
                $applicantDetails = $this->getSumsubApplicantDetails($applicantId);

                if ($applicantDetails) {
                    // Log the applicant details
                    KycLog::create([
                        'client_id' => $email,
                        'user_id' => $user->id,
                        'callback_code' => 'KYC_SYNC_APPLICANT_DETAILS',
                        'callback_payload' => $applicantDetails,
                    ]);

                    Log::info('Sumsub sync user payload', ['payload' => $applicantDetails, 'user_id' => $user->id]);
                }

                // Check if user's KYC is already verified
                if ($user->kyc_verify == 1) {
                    return ['status' => 'true', 'message' => 'User KYC was already verified. Data synced and logged.'];
                }

                // Update user's KYC status to verified
                $user->update(['kyc_verify' => 1]);

                // Subscribe to Klaviyo list if configured
                $list_id = @config('services.klaviyo.list_ids')['KYC_COMPLETED'];
                if ($list_id) {
                    $subscribeToKlaviyoList->handle($user, $list_id);
                }

                return ['status' => 'true', 'message' => 'KYC status synced and verified successfully.'];
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

    public function logVerification(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'applicantId' => 'required|string',
            'applicantEmail' => 'required|email',
            'userId' => 'required|integer|exists:users,id',
        ]);

        // Create a new KYC log entry in the database
        KycLog::create([
            'client_id' => $request->applicantEmail,
            'user_id' => $request->userId,
            'callback_code' => 'Applicant ID',
            'callback_payload' => $request->applicantId,
        ]);

        // Return a response indicating success
        return response()->json(['status' => 'success', 'message' => 'KYC log saved']);
    }


    public function changeEmail(Request $request)
    {
        // DB::beginTransaction();

        $validatedData = Validator::make($request->all(), [
            'email' => 'required|unique:aspnetusers,email'
        ]);

        if ($validatedData->fails()) {
            return redirect()->back()->with('error', 'The email you entered is already in use and exists in our system.');
        }

        try {
            // Validate and update the email

            $email = auth()->user()->email;
            // $newEmail = $validatedData->validated()['email'];
            $user = User::where('email', $email)->first();

            if ($user) {

                $user->email = $validatedData->validated()['email'];
                $user->email_confirmed = 0;
                $user->status = 0;
                $user->save();

                activity()
                    ->causedBy(auth()->guard('admin')->user())
                    ->withProperties([
                        'ip' => request()->ip(),
                        'old_email' => auth()->guard('admin')->user()->email,
                        'userRole' => auth()->guard('admin')->user()->userRole,
                        'username' => auth()->guard('admin')->user()->username,
                        'user_id' => auth()->guard('admin')->user()->id,
                        'new_email' => $user->email,
                        'remark' => 'Update Client Email'
                    ])
                    ->event('update')
                    ->log('Update Client Email');

                session()->forget('user');
                session()->put('user', User::find(auth()->id()));

                // DB::commit();
                $settings = settings();
                $from = $settings['email_from_address'];
                $toEmail = $user->email; //which email use to send message
                $uid = uniqid();
                $emailSubject = $settings['admin_title'] . ' - Activate Your New Email Address';
                $htmlContent = "";
                $code = $user->emailToken;
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From:' . $settings['admin_title'] . '<' . $from . '>' . "\r\n";
                $content =
                    // '<div>Welcome to ' . htmlspecialchars($settings['admin_title'], ENT_QUOTES, 'UTF-8') . '!</div>' .
                    '<div>Thank you for updating your contact information with LQH Markets. Please </div>' .
                    '<div>activate your new email address to ensure seamless communication.</div>';

                $templateVars = [
                    'name' => $user->fullname,
                    'server_name' => $settings['mt5_company_name'],
                    'site_link' => $settings['copyright_site_name_text'] . "/email_verify?id={$user->id}&code=$code",
                    'email' => $settings['email_from_address'],
                    "content" => $content,
                    "title_right" => "Activate",
                    "subtitle_right" => "Your Account"
                ];
                $this->mailService->sendEmail($toEmail, $emailSubject, $headers, '', $templateVars);

                return redirect()->back()->with('success', 'Email Successfully Changed');
            }
        } catch (\Exception $e) {
            DB::rollback(); // Rollback on failure
            return redirect()->back()->with('error', 'Failed to change email.');
        }
    }
}
