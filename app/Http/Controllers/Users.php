<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycLog;
use App\Models\Account;
use App\Models\KycUpdate;
use Illuminate\Support\Str;
use App\Models\ClientWallet;
use Illuminate\Http\Request;
use App\Services\MailService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Actions\SubscribeToKlaviyoList;
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
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Create Sanctum token
        $token = $user->createToken('api_call')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token
        ], 201);
    }



    public function profile()
    {
        $user_id = auth()->user()->id;
        $bank_accounts = ClientWallet::where('user_id', $user_id)->get();
        $user = User::where('id', $user_id)->first();

        // $verf_docs = KycUpdate::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        return view('profile', compact('bank_accounts', 'user'));
    }
    public function changePassword(Request $request)
    {
        $rules = [
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'confirmed',
                'min:8', // At least 8 characters
                'regex:/[a-z]/', // At least one lowercase letter
                'regex:/[A-Z]/', // At least one uppercase letter
                'regex:/\d/', // At least one number
                'regex:/[\W_]/', // At least one special character
            ],
        ];

        $messages = [
            'new_password.min' => 'The password must be at least 8 characters long.',
            'new_password.regex' => [
                'The password must contain at least one lowercase letter.',
                'The password must contain at least one uppercase letter.',
                'The password must contain at least one number.',
                'The password must contain at least one special character.',
            ],
            'new_password.confirmed' => 'Passwords do not match.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $filteredErrors = [];

            // Check which specific regex rule failed and return only unmet requirements
            if ($errors->has('new_password')) {
                $password = $request->new_password;

                if (!preg_match('/[a-z]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one lowercase letter.';
                }
                if (!preg_match('/[A-Z]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one uppercase letter.';
                }
                if (!preg_match('/\d/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one number.';
                }
                if (!preg_match('/[\W_]/', $password)) {
                    $filteredErrors[] = 'The password must contain at least one special character.';
                }
                if (strlen($password) < 8) {
                    $filteredErrors[] = 'The password must be at least 8 characters long.';
                }
                if ($errors->has('new_password.confirmed')) {
                    $filteredErrors[] = 'Passwords do not match.';
                }
            }

            return response()->json([
                'errors' => $filteredErrors
            ], 422);
        }

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
        $secretKey = 'dpROMBlvbrtOvPvrjwQGxkRRawRgkHW8'; // Replace with your actual secret key
        $secretKey = config('services.sumsub.api_secret');
        $timestamp = time(); // Current timestamp in seconds

        // Example values (replace with actual values as needed)
        // $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
        $appToken = config('services.sumsub.api_token');
        $apiUrl = '/resources/accessTokens?userId=' . urlencode($user->email) . '&levelName=basic-kyc-level'; // URI of the request
        $requestMethod = 'POST'; // HTTP method
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
    public function sumsub_verify(Request $request, SubscribeToKlaviyoList $subscribeToKlaviyoList)
    {
        if (auth()->check() && $request->has(['sumsub', 'type', 'payload'])) {
            $email = auth()->user()->email;
            $type = $request->input('type');
            $payload = $request->input('payload');

            // $type='idCheck.onApplicantStatusChanged';
            // $payload=['reviewStatus'=>'completed','reviewResult'=>["reviewAnswer"=>"GREEN"]];
            if ($type == 'idCheck.onApplicantStatusChanged') {
                $timestamp = time();
                $requestMethod = "GET";
                $secretKey = config('services.sumsub.api_secret');
                $apiUrl = '/resources/applicants/' . $payload['applicantId'] . '/status'; // URI of the request
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
