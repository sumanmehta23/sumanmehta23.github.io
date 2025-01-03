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
        $secretKey = 'dpROMBlvbrtOvPvrjwQGxkRRawRgkHW8';// Use environment variables for security
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

        // If we have the token, make another request to fetch the applicant details
        if ($token) {
            // Fetch applicant details using the token
            $applicantData = $this->getApplicantDetails($token);

            if (isset($applicantData->correlationId)) {
                dump($applicantData->correlationId);
                $applicantId = $this->getApplicantIdFromCorrelation($applicantData->correlationId);
                dump($applicantData);
                dd($applicantId);
                return response()->json(['token' => $auth->token, 'email' => $email, 'applicantData' => $applicantData, 'applicantId' => $applicantId]);
            }
            return response()->json(['error' => 'No correlationId found in applicant data'], 500);
        }

        return response()->json(['error' => 'Failed to fetch token'], 500);
    }

    // Helper function to fetch applicant details using the token
    private function getApplicantDetails($token)
    {
        $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
        $timestamp = time();
        $apiUrl = "/resources/applicants/{$token}"; // Replace with actual endpoint
        $requestMethod = 'GET';

        // Create the valueToSign string
        $valueToSign = $timestamp . $requestMethod . $apiUrl;

        // Compute HMAC SHA256 signature
        $signature = hash_hmac('sha256', $valueToSign, $appToken, true);
        $signatureHex = bin2hex($signature);

        // Initialize cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sumsub.com' . $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestMethod,
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
            return ['error' => curl_error($curl)];
        }

        curl_close($curl);

        return json_decode($response);
    }

    // Fetch applicant ID using correlation ID
    private function getApplicantIdFromCorrelation($correlationId)
    {
        dump($correlationId);
        $secretKey = 'dpROMBlvbrtOvPvrjwQGxkRRawRgkHW8';
        $timestamp = time(); // Current timestamp in seconds

        $appToken = 'prd:o43fXhlRsswSFc3l6s2tnY4u.3fdpqHGAxhVLGObNhJaigfBXjSqSaCAH';
        $apiUrl = "/resources/applicants/correlationId/{$correlationId}";  // Endpoint for fetching applicant details by correlationId
        $requestMethod = 'GET';  // HTTP method

        // Create the valueToSign string
        $valueToSign = $timestamp . $requestMethod . $apiUrl;

        // Compute HMAC SHA256 signature
        $signature = hash_hmac('sha256', $valueToSign, $secretKey, true); // Binary format

        // Convert binary signature to hexadecimal
        $signatureHex = bin2hex($signature);

        // Initialize cURL
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.sumsub.com' . $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestMethod,
            CURLOPT_HTTPHEADER => [
                'X-App-Token: ' . $appToken,
                'X-App-Access-Ts: ' . $timestamp,
                'X-App-Access-Sig: ' . $signatureHex,
            ],
        ]);

        // Execute cURL request and fetch response
        $response = curl_exec($curl);
        dd($response);
        // Check for cURL errors
        if (curl_errno($curl)) {
            return response()->json(['error' => curl_error($curl)], 500);
        }

        // Parse the response
        $applicantData = json_decode($response);

        // Close cURL session
        curl_close($curl);

        // Check if applicant data is available and return applicantId
        if (isset($applicantData->applicantId)) {
            return $applicantData->applicantId;
        }

        return response()->json(['error' => 'Failed to fetch applicant ID'], 500);
    }
}
