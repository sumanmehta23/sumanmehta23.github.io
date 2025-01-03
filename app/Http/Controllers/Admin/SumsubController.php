<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SumsubController extends Controller
{

    public function sumsub_data(Request $request)
    {
        $email = $request->email;
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

        // return view('client_details', compact('token'));
        if (isset($auth->token)) {
            return response()->json(['token' => $auth->token, 'email' => $email]);
        }

        return response()->json(['error' => 'Failed to fetch token'], 500);

    }

}
