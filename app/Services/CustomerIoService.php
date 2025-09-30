<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerIoService
{
    protected $apiUrl;
    protected $siteId;
    protected $appKey;
    protected $trackingApiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.customerio.api_url', 'https://track.customer.io');
        $this->siteId = config('services.customerio.site_id');
        $this->appKey = config('services.customerio.app_key');
        $this->trackingApiKey = config('services.customerio.tracking_api_key');
    }

    /**
     * Check if Customer.io credentials are properly configured
     */
    protected function hasValidCredentials(): bool
    {
        return !empty($this->siteId) && !empty($this->trackingApiKey);
    }

    /**
     * Create or update a customer in Customer.io
     */
    public function createOrUpdateCustomer($customerData)
    {
        // Check if Customer.io credentials are configured
        if (!$this->hasValidCredentials()) {
            Log::warning('Customer.io credentials not configured. Skipping customer creation/update.', [
                'customer_email' => is_array($customerData) ? ($customerData['email'] ?? 'unknown') : 'unknown'
            ]);
            return false;
        }

        // Convert to array for consistent handling
        $customerData = is_array($customerData) ? $customerData : (array) $customerData;
        
        if (empty($customerData['email'])) {
            Log::warning('Cannot create/update Customer.io customer: email is missing', ['customer_data' => $customerData]);
            return false;
        }

        // Prepare the URL and payload
        $url = "{$this->apiUrl}/api/v1/customers/{$customerData['email']}";
        
        // Log the payload being sent to Customer.io for debugging
        Log::info('Customer.io API payload', [
            'customer_id' => $customerData['id'] ?? 'unknown',
            'customer_email' => $customerData['email'],
            'payload' => $customerData
        ]);
        
        try {
            $response = Http::withBasicAuth($this->siteId, $this->trackingApiKey)
                ->put($url, $customerData);
            
            if ($response->successful()) {
                Log::info('Customer.io customer created/updated successfully', [
                    'customer_id' => $customerData['id'] ?? 'unknown',
                    'customer_email' => $customerData['email'],
                ]);
                return true;
            } else {
                Log::error('Failed to create/update Customer.io customer', [
                    'customer_id' => $customerData['id'] ?? 'unknown',
                    'customer_email' => $customerData['email'],
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Customer.io API request failed', [
                'customer_id' => $customerData['id'] ?? 'unknown',
                'customer_email' => $customerData['email'],
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Track an event for a customer in Customer.io
     */
    public function trackEvent($customerEmail, $eventName, $eventData = [])
    {
        // Check if Customer.io credentials are configured
        if (!$this->hasValidCredentials()) {
            Log::warning('Customer.io credentials not configured. Skipping event tracking.', [
                'customer_email' => $customerEmail,
                'event_name' => $eventName
            ]);
            return false;
        }

        if (empty($customerEmail)) {
            Log::warning('Cannot track Customer.io event: email is missing', [
                'event_name' => $eventName,
                'event_data' => $eventData
            ]);
            return false;
        }

        // Prepare the URL and payload for event tracking
        $url = "{$this->apiUrl}/api/v1/customers/{$customerEmail}/events";
        
        $payload = [
            'name' => $eventName,
            'data' => $eventData,
            'timestamp' => now()->timestamp
        ];
        
        // Log the event payload being sent
        Log::info('Customer.io Event payload', [
            'customer_email' => $customerEmail,
            'event_name' => $eventName,
            'payload' => $payload
        ]);
        
        try {
            $response = Http::withBasicAuth($this->siteId, $this->trackingApiKey)
                ->post($url, $payload);
            
            if ($response->successful()) {
                Log::info('Customer.io event tracked successfully', [
                    'customer_email' => $customerEmail,
                    'event_name' => $eventName,
                ]);
                return true;
            } else {
                Log::error('Failed to track Customer.io event', [
                    'customer_email' => $customerEmail,
                    'event_name' => $eventName,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Customer.io Event API request failed', [
                'customer_email' => $customerEmail,
                'event_name' => $eventName,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
