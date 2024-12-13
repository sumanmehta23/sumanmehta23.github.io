<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\KycUpdate;
use App\Models\ClientWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class Users extends Controller
{
    public function profile()
    {
        $user_id = auth()->user()->id;
        $bank_accounts = ClientWallet::where('user_id', $user_id)->get();
        $user = User::where('id',$user_id)->first();

        // $verf_docs = KycUpdate::where('user_id', $user_id)->orderBy('id', 'desc')->get();
        return view('profile', compact('bank_accounts', 'user'));

    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed',
        ]);
        $email = auth()->user()->email;
        $user = DB::table('aspnetusers')->where('email', $email)->first();

        if ($user &&(Hash::check($request->current_password, $user->password))) {
            User::where('email', $email)->update(['password' =>  Hash::make($request->new_password)]);
            return response()->json(['success' => 'Password Successfully Changed']);
        } else {
            return response()->json(['message' => 'Current Password is not matched'], 422);
        }
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
        $secretKey = 'dpROMBlvbrtOvPvrjwQGxkRRawRgkHW8'; // Replace with your actual secret key
        $timestamp = time(); // Current timestamp in seconds

        // Example values (replace with actual values as needed)
        $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
        $apiUrl = '/resources/accessTokens?userId=' . urlencode(session('clogin')) . '&levelName=basic-kyc-level'; // URI of the request
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
    public function sumsub_verify(Request $request)
    {
        if (Session::has('clogin') && $request->has(['sumsub', 'type', 'payload'])) {
            $email = Session::get('clogin');
            $type = $request->input('type');
            $payload = $request->input('payload');
            // $type='idCheck.onApplicantStatusChanged';
            // $payload=['reviewStatus'=>'completed','reviewResult'=>["reviewAnswer"=>"GREEN"]];
            if ($type == 'idCheck.onApplicantStatusChanged') {
                // Store callback log in the database
                DB::table('kyc_logs')->insert([
                    'client_id' => $email,
                    'callback_code' => json_encode($type),
                    'callback_payload' => json_encode($payload),
                ]);
                // Check if review status is completed
                if (isset($payload['reviewStatus']) && $payload['reviewStatus'] == 'completed') {
                    // Check review result
                    if (isset($payload['reviewResult']['reviewAnswer']) && $payload['reviewResult']['reviewAnswer'] == 'GREEN') {
                        // Find the user in the database
                        $user = DB::table('aspnetusers')->where('email', $email)->first();

                        // Check if the user's KYC is already verified
                        if ($user && $user->kyc_verify == 1) {
                            return response()->json(['status' => 'true', 'message' => 'Your KYC Already Verified']);
                        }

                        // Update user's KYC status to verified
                        DB::table('aspnetusers')
                            ->where('email', $email)
                            ->update(['kyc_verify' => 1]);

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

}
