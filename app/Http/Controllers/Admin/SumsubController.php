<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

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
        // Store sensitive credentials in the .env file for security
        $appToken = env('SUMSUB_APP_TOKEN');
        $secretKey = env('SUMSUB_SECRET_KEY');

        // Validate the request
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->email;

        $requestMethod = 'GET'; // Ensure the correct method is used
        $requestUrl = "/resources/applicants/-;externalUserId=" . urlencode($email)."/one"; // Correct endpoint
        $timestamp = time();

        // Generate the correct signature
        $stringToSign = $timestamp . $requestMethod . $requestUrl;
        $signature = hash_hmac('sha256', $stringToSign, $secretKey, true);
        $signatureHex = bin2hex($signature);

        // Set API URL
        $apiUrl = "https://api.sumsub.com" . $requestUrl;

        // Headers with proper authentication
        $headers = [
            "X-App-Token" => $appToken,
            "X-App-Access-Ts" => $timestamp,
            "X-App-Access-Sig" => $signatureHex,
            "Accept" => "application/json",
            "Content-Type" => "application/json"
        ];
        $curl = curl_init();

        curl_setopt_array($curl, [
          CURLOPT_URL => $apiUrl,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);
        // dump($response);
        dd($response);
        $err = curl_error($curl);

        curl_close($curl);

        // Make API request using Laravel's HTTP client
        // $response = Http::withHeaders($headers)->get($apiUrl);

        // Get the HTTP status code
        $statusCode = $response->status();
        $responseBody = $response->json();

        // Debugging Output (Useful for testing)
        \Log::info("Sumsub API Response:", [
            "url" => $apiUrl,
            "status" => $statusCode,
            "response" => $responseBody
        ]);

        // Handle errors properly
        if ($statusCode === 405) {
            return response()->json(["error" => "Method Not Allowed. Check if GET is the correct method."], 405);
        } elseif ($statusCode >= 400) {
            return response()->json([
                "error" => "API request failed",
                "status" => $statusCode,
                "response" => $responseBody
            ], $statusCode);
        }

        // Check if an applicant exists
        if (isset($responseBody['id'])) {
            return response()->json([
                "applicantId" => $responseBody['id']
            ], 200);
        } else {
            return response()->json([
                "error" => "Applicant not found or invalid response",
                "response" => $responseBody
            ], 404);
        }
    }

}
