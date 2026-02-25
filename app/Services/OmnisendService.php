<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmnisendService
{
    protected $apiUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.omnisend.api_url', 'https://api.omnisend.com/v5');
        $this->apiKey = config('services.omnisend.api_key');
    }

    /**
     * Check if Omnisend credentials are properly configured
     */
    protected function hasValidCredentials(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Create or update a contact in Omnisend
     */
    public function createOrUpdateContact($contactData)
    {
        // Immediate debug logging
        \Illuminate\Support\Facades\File::append(
            storage_path('logs/omnisend-debug.log'),
            '[' . now()->toDateTimeString() . '] createOrUpdateContact called' . PHP_EOL
        );

        // Check if Omnisend credentials are configured
        if (!$this->hasValidCredentials()) {
            \Illuminate\Support\Facades\File::append(
                storage_path('logs/omnisend-debug.log'),
                '[' . now()->toDateTimeString() . '] Omnisend credentials NOT configured!' . PHP_EOL
            );
            Log::warning('Omnisend credentials not configured. Skipping contact creation/update.', [
                'apikey' => $this->apiKey,
                'config_api_key' => config('services.omnisend.api_key'),
                'contact_email' => is_array($contactData) ? ($contactData['email'] ?? 'unknown') : 'unknown'
            ]);
            return false;
        }

        // Convert to array for consistent handling
        $contactData = is_array($contactData) ? $contactData : (array) $contactData;

        if (empty($contactData['email'])) {
            Log::warning('Cannot create/update Omnisend contact: email is missing', ['contact_data' => $contactData]);
            return false;
        }

        // Prepare the Omnisend API payload
        $tags = $contactData['tags'] ?? [];
        $payload = $this->formatContactPayload($contactData, $tags);

        // Log the payload being sent to Omnisend for debugging
        Log::info('Omnisend API payload', [
            'contact_id' => $contactData['id'] ?? 'unknown',
            'contact_email' => $contactData['email'],
            'payload' => $payload
        ]);

        \Illuminate\Support\Facades\File::append(
            storage_path('logs/omnisend-debug.log'),
            '[' . now()->toDateTimeString() . '] Sending to Omnisend API: ' . $contactData['email'] . PHP_EOL
        );

        try {
            $url = "{$this->apiUrl}/contacts";

            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('Omnisend contact created/updated successfully', [
                    'contact_id' => $contactData['id'] ?? 'unknown',
                    'contact_email' => $contactData['email'],
                ]);
                return true;
            } else {
                Log::error('Failed to create/update Omnisend contact', [
                    'contact_id' => $contactData['id'] ?? 'unknown',
                    'contact_email' => $contactData['email'],
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Omnisend API request failed', [
                'contact_id' => $contactData['id'] ?? 'unknown',
                'contact_email' => $contactData['email'],
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Track a custom event for a contact in Omnisend
     */
    public function trackEvent($email, $eventName, $eventData = [])
    {
        // Immediate debug logging
        \Illuminate\Support\Facades\File::append(
            storage_path('logs/omnisend-debug.log'),
            '[' . now()->toDateTimeString() . '] trackEvent called: ' . $eventName . ' for ' . $email . PHP_EOL
        );

        // Check if Omnisend credentials are configured
        if (!$this->hasValidCredentials()) {
            Log::warning('Omnisend credentials not configured. Skipping event tracking.', [
                'email' => $email,
                'event_name' => $eventName
            ]);
            return false;
        }

        if (empty($email)) {
            Log::warning('Cannot track Omnisend event: email is missing', [
                'event_name' => $eventName,
                'event_data' => $eventData
            ]);
            return false;
        }

        // Prepare the event payload for Omnisend
        $payload = [
            'email' => $email,
            'eventName' => $eventName,
            'fields' => $eventData,
            'createdAt' => now()->toIso8601String()
        ];

        // Log the event payload being sent
        Log::info('Omnisend Event payload', [
            'email' => $email,
            'event_name' => $eventName,
            'payload' => $payload
        ]);

        try {
            $url = "{$this->apiUrl}/events";

            $response = Http::withHeaders([
                'X-API-KEY' => $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('Omnisend event tracked successfully', [
                    'email' => $email,
                    'event_name' => $eventName,
                ]);
                return true;
            } else {
                Log::error('Failed to track Omnisend event', [
                    'email' => $email,
                    'event_name' => $eventName,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Omnisend Event API request failed', [
                'email' => $email,
                'event_name' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format contact data for Omnisend API
     */
    protected function formatContactPayload($contactData, $tags = []): array
    {
        $payload = [];

        // Set basic identifiers
        if (!empty($contactData['email'])) {
            $payload['identifiers'] = [
                [
                    'type' => 'email',
                    'id' => $contactData['email'],
                    'channels' => [
                        'email' => [
                            'status' => 'subscribed',
                            'statusDate' => now()->toIso8601String()
                        ]
                    ]
                ]
            ];
        }

        // Add phone if available
        if (!empty($contactData['phone'])) {
            $payload['identifiers'][] = [
                'type' => 'phone',
                'id' => $contactData['phone'],
                'channels' => [
                    'sms' => [
                        'status' => 'nonSubscribed'
                    ]
                ]
            ];
        }

        // Set first name and last name
        if (!empty($contactData['first_name'])) {
            $payload['firstName'] = $contactData['first_name'];
        }

        if (!empty($contactData['last_name'])) {
            $payload['lastName'] = $contactData['last_name'];
        }

        // Set custom properties
        $customProperties = [];

        if (isset($contactData['id'])) {
            $customProperties['user_id'] = (string) $contactData['id'];
        }

        if (isset($contactData['deposit_amount'])) {
            $customProperties['deposit_amount'] = (float) $contactData['deposit_amount'];
        }

        if (!empty($contactData['created_at'])) {
            $customProperties['created_at'] = is_numeric($contactData['created_at'])
                ? date('Y-m-d H:i:s', $contactData['created_at'])
                : $contactData['created_at'];
        }

        if (!empty($contactData['registered_at'])) {
            $customProperties['registered_at'] = is_numeric($contactData['registered_at'])
                ? date('Y-m-d H:i:s', $contactData['registered_at'])
                : $contactData['registered_at'];
        }

        if (isset($contactData['kyc_verified'])) {
            $customProperties['kyc_verified'] = (bool) $contactData['kyc_verified'];
        }

        if (!empty($contactData['kyc_verified_at'])) {
            $customProperties['kyc_verified_at'] = is_numeric($contactData['kyc_verified_at'])
                ? date('Y-m-d H:i:s', $contactData['kyc_verified_at'])
                : $contactData['kyc_verified_at'];
        }

        if (!empty($contactData['kyc_status'])) {
            $customProperties['kyc_status'] = $contactData['kyc_status'];
        }

        if (!empty($contactData['last_deposit_at'])) {
            $customProperties['last_deposit_at'] = is_numeric($contactData['last_deposit_at'])
                ? date('Y-m-d H:i:s', $contactData['last_deposit_at'])
                : $contactData['last_deposit_at'];
        }

        if (!empty($contactData['last_open_trade_at'])) {
            $customProperties['last_open_trade_at'] = is_numeric($contactData['last_open_trade_at'])
                ? date('Y-m-d H:i:s', $contactData['last_open_trade_at'])
                : $contactData['last_open_trade_at'];
        }

        if (!empty($customProperties)) {
            $payload['customProperties'] = $customProperties;
        }

        // Add tags if provided
        if (!empty($tags) && is_array($tags)) {
            $payload['tags'] = array_values($tags);
        }

        return $payload;
    }

    /**
     * Send one "Trades Opened" event with all trades collected for the user (batched).
     */
    public function trackBatchTradesOpened(string $email, $userId, array $trades): bool
    {
        $eventData = [
            'user_id' => (string) $userId,
            'trades_count' => count($trades),
            'trades' => $trades,
        ];
        return $this->trackEvent($email, 'Trades Opened', $eventData);
    }

    /**
     * Determine deposit tag based on amount
     */
    public function getDepositTag($depositAmount): string
    {
        if ($depositAmount >= 5000) {
            return 'Deposit $5000+';
        } elseif ($depositAmount >= 2000) {
            return 'Deposit $2000-$5000';
        } elseif ($depositAmount >= 200) {
            return 'Deposit $200-$2000';
        } elseif ($depositAmount >= 10) {
            return 'Deposit $10-$200';
        }

        return '';
    }
}
