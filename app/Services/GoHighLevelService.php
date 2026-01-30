<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoHighLevelService
{
    protected string $apiUrl;

    protected ?string $apiKey;

    protected ?string $locationId;

    public function __construct()
    {
        $this->apiUrl = rtrim(config('services.gohighlevel.api_url', 'https://services.leadconnectorhq.com'), '/');
        $this->apiKey = config('services.gohighlevel.api_key');
        $this->locationId = config('services.gohighlevel.location_id');
    }

    /**
     * Check if GoHighLevel credentials are properly configured.
     */
    public function hasValidCredentials(): bool
    {
        return !empty($this->apiKey) && !empty($this->locationId);
    }

    /**
     * Create a contact/lead in GoHighLevel (e.g. when someone subscribes via IB page).
     *
     * @param  array{email: string, fullname?: string, number?: string, country?: string, source?: string, refercode?: string, user_id?: int}  $contactData
     */
    public function createContact(array $contactData): bool
    {
        if (!$this->hasValidCredentials()) {
            Log::warning('GoHighLevel credentials not configured. Skipping contact creation.', [
                'contact_email' => $contactData['email'] ?? 'unknown',
            ]);
            return false;
        }

        if (empty($contactData['email'])) {
            Log::warning('Cannot create GoHighLevel contact: email is missing', ['contact_data' => $contactData]);
            return false;
        }

        $payload = $this->formatContactPayload($contactData);

        Log::info('GoHighLevel API payload', [
            'contact_email' => $contactData['email'],
            'payload' => $payload,
        ]);

        try {
            $url = "{$this->apiUrl}/contacts/";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Version' => '2021-07-28',
            ])->post($url, $payload);

            if ($response->successful()) {
                Log::info('GoHighLevel contact created successfully', [
                    'contact_email' => $contactData['email'],
                ]);
                return true;
            }

            Log::error('Failed to create GoHighLevel contact', [
                'contact_email' => $contactData['email'],
                'response_status' => $response->status(),
                'response_body' => $response->body(),
            ]);
            return false;
        } catch (\Exception $e) {
            Log::error('GoHighLevel API request failed', [
                'contact_email' => $contactData['email'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Format contact data for GHL Contacts API (create contact).
     */
    protected function formatContactPayload(array $contactData): array
    {
        $name = $contactData['fullname'] ?? $contactData['first_name'] ?? '';
        $firstName = $name;
        $lastName = '';
        if (str_contains($name, ' ')) {
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0];
            $lastName = $parts[1] ?? '';
        }

        $payload = [
            'locationId' => $this->locationId,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'email' => $contactData['email'],
            'phone' => $contactData['number'] ?? $contactData['phone'] ?? '',
            'country' => $contactData['country'] ?? '',
            'source' => $contactData['source'] ?? 'IB Page',
        ];

        $customFields = [];
        if (!empty($contactData['refercode'])) {
            $customFields[] = ['key' => 'referral_code', 'value' => $contactData['refercode']];
        }
        if (isset($contactData['user_id'])) {
            $customFields[] = ['key' => 'lqh_user_id', 'value' => (string) $contactData['user_id']];
        }
        if (!empty($customFields)) {
            $payload['customFields'] = $customFields;
        }

        return $payload;
    }
}
