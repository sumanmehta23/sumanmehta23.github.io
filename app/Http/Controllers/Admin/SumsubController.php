<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SumsubController extends Controller
{
    // public function sumsub_data(Request $request)
    // {
    //     $email = $request->email;
    //     $secretKey = 'dpROMBlvbrtOvPvrjwQGxkRRawRgkHW8';// Use environment variables for security
    //     $timestamp = time(); // Current timestamp in seconds

    //     // Example values (replace with actual values as needed)
    //     $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
    //     $apiUrl = '/resources/accessTokens?userId=' . urlencode(session('clogin')) . '&levelName=basic-kyc-level'; // URI of the request
    //     $requestMethod = 'POST'; // HTTP method
    //     $requestBody = ''; // Add your request body if needed, empty for this example

    //     // Create the valueToSign string
    //     $valueToSign = $timestamp . $requestMethod . $apiUrl;

    //     if (!empty($requestBody)) {
    //         $valueToSign .= $requestBody;
    //     }

    //     // Compute HMAC SHA256 signature
    //     $signature = hash_hmac('sha256', $valueToSign, $secretKey, true); // Binary format

    //     // Convert binary signature to hexadecimal
    //     $signatureHex = bin2hex($signature);
    //     // Initialize cURL
    //     $curl = curl_init();
    //     // Set cURL options
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => 'https://api.sumsub.com' . $apiUrl, // Full URL including hostname
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => '',
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => $requestMethod,
    //         CURLOPT_POSTFIELDS => $requestBody,
    //         CURLOPT_HTTPHEADER => [
    //             'X-App-Token: ' . $appToken,
    //             'X-App-Access-Ts: ' . $timestamp,
    //             'X-App-Access-Sig: ' . $signatureHex,
    //         ],
    //     ]);
    //     // Execute cURL request and fetch response
    //     $response = curl_exec($curl);

    //     // Check for cURL errors
    //     if (curl_errno($curl)) {
    //         return response()->json(['error' => curl_error($curl)], 500);
    //     }

    //     // Parse the response
    //     $auth = json_decode($response);
    //     dd($auth);
    //     // Close cURL session
    //     curl_close($curl);
    //     $token = $auth->token ?? null;
    //     if ($token) {
    //         // Pass token to frontend for SDK initialization
    //         return response()->json(['token' => $token, 'email' => $email]);
    //     }

    //     return response()->json(['error' => 'No token received'], 500);
    // }

    public function sumsub_data(Request $request)
    {
        // Sumsub API access token (replace with your actual token)
        $appToken = 'prd:iyi7rUjLQYFWdpw3LFuemd31.R0zHUOl2ZoXyr8y1ZpNKEGUNhx4e18NR';

        $secretKey = 'Bb7iwwWRFGNvetESRV21JGzv2AQanTZP';// Use environment variables for security

        $email = $request->email;

        $httpMethod = "POST";
        $requestUrl = "/resources/applicants?-;externalUserId=" . urlencode($email)."/one"; // Add externalUserId as a query parameter
        $payload = ""; // Empty payload for this request
        $timestamp = time();

        // Generate signature
        $stringToSign = $httpMethod . ' ' . $requestUrl . "\n" . $timestamp . "\n" . $payload;
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $secretKey, true));

        // Headers
        $headers = [
            "X-App-Token: $appToken",
            "X-App-Access-Ts: $timestamp",
            "X-App-Access-Sig: $signature",
            "Content-Type: application/json" // Default Content-Type, no payload here
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sumsub.com' . $requestUrl, // Full URL including query parameters
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $httpMethod,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        dd($response);
        curl_close($curl);

        if ($err) {
            return response()->json(['error' => "cURL Error: $err"], 500);
        }

        $decodedResponse = json_decode($response, true);

        // Debugging Logs
        \Log::info('Sumsub Request URL: ' . $requestUrl);
        \Log::info('Sumsub Request StringToSign: ' . $stringToSign);
        \Log::info('Sumsub Response: ' . $response);

        if (isset($decodedResponse['errorCode'])) {
            return response()->json($decodedResponse, 401); // Handle API error response
        }

        return response()->json($decodedResponse, 200);
    }



}
