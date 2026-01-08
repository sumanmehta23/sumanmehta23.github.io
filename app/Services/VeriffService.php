<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VeriffService
{
    protected string $apiKey;
    protected string $apiSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.veriff.api_key', '');
        $this->apiSecret = (string) config('services.veriff.api_secret', '');
        $this->baseUrl = rtrim((string) config('services.veriff.base_url', 'https://stationapi.veriff.com/v1'), '/');
        
        // Debug: log what URL and key are being used
        Log::info('Veriff Service initialized', [
            'base_url' => $this->baseUrl,
            'api_key_set' => !empty($this->apiKey),
            'api_key_length' => strlen($this->apiKey),
        ]);
    }

    /**
     * Create a Veriff verification session for a given user.
     *
     * @param  array  $userData
     * @return array
     * @throws \Exception
     */
    public function createSession(array $userData): array
    {
        if (empty($this->apiKey) || empty($this->apiSecret)) {
            Log::warning('Veriff API credentials are not configured.');
            throw new \Exception('Veriff API credentials are not configured. Please contact administrator.');
        }

        $payload = [
            'verification' => [
                'vendorData' => $userData['email'] ?? null,
                'callback' => $userData['callback'] ?? null,
                'person' => [
                    'firstName' => $userData['first_name'] ?? null,
                    'lastName' => $userData['last_name'] ?? null,
                    'idNumber' => $userData['id_number'] ?? null,
                    'dateOfBirth' => $userData['date_of_birth'] ?? null,
                ],
            ],
        ];

        try {
            $url = $this->baseUrl . '/sessions';
            
            // Debug: log request details
            Log::info('Veriff API request', [
                'url' => $url,
                'headers' => array_keys($this->authHeaders()),
                'payload_keys' => array_keys($payload),
            ]);
            
            $response = Http::timeout(30)
                ->withHeaders($this->authHeaders())
                ->post($url, $payload);

            if (! $response->successful()) {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? $errorBody['error'] ?? 'Unknown error';
                
                Log::error('Failed to create Veriff session', [
                    'url' => $url,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'error' => $errorMessage,
                ]);

                throw new \Exception('Verification service error: ' . $errorMessage);
            }

            $sessionData = $response->json();
            
            if (empty($sessionData['verification']['url'] ?? null)) {
                Log::error('Veriff session created but URL is missing', [
                    'response' => $sessionData,
                ]);
                throw new \Exception('Invalid response from verification service.');
            }

            return $sessionData;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Veriff connection error', [
                'error' => $e->getMessage(),
                'url' => $this->baseUrl . '/sessions',
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve a specific Veriff session by ID.
     */
    public function getSession(string $sessionId): ?array
    {
        $response = Http::withHeaders($this->authHeaders())
            ->get($this->baseUrl . '/sessions/' . $sessionId);

        if (! $response->successful()) {
            Log::error('Failed to fetch Veriff session', [
                'session_id' => $sessionId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        return $response->json();
    }

    /**
     * Build authentication headers for Veriff requests.
     * Veriff uses X-AUTH-CLIENT header with API public key.
     */
    protected function authHeaders(): array
    {
        return [
            'X-AUTH-CLIENT' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}


