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
     * Create or update (upsert) a contact/lead in GoHighLevel.
     * Uses upsert endpoint to handle duplicate contacts automatically.
     * If contact exists (matched by phone/email), it will be updated; otherwise, a new contact is created.
     *
     * @param  array{email: string, fullname?: string, number?: string, country?: string, source?: string, refercode?: string, user_id?: int, tags?: array<string>}  $contactData
     */
    public function createContact(array $contactData): bool
    {
        if (!$this->hasValidCredentials()) {
            Log::warning('GoHighLevel credentials not configured. Skipping contact upsert.', [
                'contact_email' => $contactData['email'] ?? 'unknown',
            ]);
            return false;
        }

        if (empty($contactData['email'])) {
            Log::warning('Cannot upsert GoHighLevel contact: email is missing', ['contact_data' => $contactData]);
            return false;
        }

        $payload = $this->formatContactPayload($contactData);

        Log::info('GoHighLevel API payload (upsert)', [
            'contact_email' => $contactData['email'],
            'contact_phone' => $contactData['number'] ?? $contactData['phone'] ?? 'N/A',
            'payload' => $payload,
        ]);

        try {
            // Use upsert endpoint to handle duplicate contacts automatically
            $url = "{$this->apiUrl}/contacts/upsert";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Version' => '2021-07-28',
            ])->post($url, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                $contactId = $responseData['contact']['id'] ?? null;
                $wasCreated = isset($responseData['contact']['id']);
                
                Log::info('GoHighLevel contact upserted successfully', [
                    'contact_email' => $contactData['email'],
                    'contact_phone' => $contactData['number'] ?? $contactData['phone'] ?? 'N/A',
                    'contact_id' => $contactId,
                    'action' => $wasCreated ? 'created' : 'updated',
                ]);
                return true;
            }

            Log::error('Failed to upsert GoHighLevel contact', [
                'contact_email' => $contactData['email'],
                'contact_phone' => $contactData['number'] ?? $contactData['phone'] ?? 'N/A',
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
     * Format contact data for GHL Contacts API (upsert contact).
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

        // Add tags if provided
        if (!empty($contactData['tags']) && is_array($contactData['tags'])) {
            $payload['tags'] = $contactData['tags'];
        }

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
