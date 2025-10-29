<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class RagapayService
{
    protected string $baseUrl;
    protected string $merchantKey;
    protected string $merchantPassword;
    protected string $channelId;

    public function __construct()
    {
        $this->baseUrl = config('services.raga_pay.api_url', 'https://checkout.ragapay.com');
        $this->merchantKey = config('services.raga_pay.merchant_key');
        $this->merchantPassword = config('services.raga_pay.password');
    }

    /**
     * Create a new Ragapay checkout session
     *
     * @param array $order
     * @param array $customer
     * @param array $urls (optional) Custom URLs for success, cancel, and error
     * @return array
     */
    public function createCheckoutSession(array $order, array $customer, array $urls = []): array
    {
        Log::info('RagapayService::createCheckoutSession called', [
            'order' => $order,
            'customer' => $customer,
            'urls' => $urls,
        ]);
        try {
            $hash = $this->generateHash(
                $order['number'],
                $order['amount'],
                $order['currency'],
                $order['description']
            );

            $payload = [
                "merchant_key" => $this->merchantKey,
                "operation" => "purchase",
                "methods" => ["card", "paypal", "googlepay"],
                "session_expiry" => 60,
                "success_url" => $urls['success_url'] ?? route('ragapay.success'),
                "cancel_url" => $urls['cancel_url'] ?? route('ragapay.cancel'),
                "error_url" => $urls['error_url'] ?? route('ragapay.error'),
                "url_target" => "_parent",
                "req_token" => false,
                "recurring_init" => false,
                "vat_calc" => false,
                "hash" => $hash,
                "order" => $order,
                "customer" => $customer,
            ];

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post("{$this->baseUrl}/api/v1/session", $payload);
            if ($response->failed()) {
                Log::error('Ragapay createCheckoutSession failed', [
                    'response' => $response->body(),
                ]);
                throw new Exception('Ragapay checkout session failed.');
            }

            return $response->json();
        } catch (Exception $e) {
            Log::error('RagapayService::createCheckoutSession Exception', [
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate the Ragapay hash
     * Format: SHA1(MD5(UPPERCASE(order_number + order_amount + order_currency + order_description + password)))
     *
     * Important: The concatenated string must be converted to UPPERCASE before hashing
     */
    protected function generateHash(string $orderNumber, string $amount, string $currency, string $description): string
    {
        // Concatenate all parameters
        $concatenated = $orderNumber . $amount . $currency . $description . $this->merchantPassword;

        // Convert to uppercase (this is critical!)
        $uppercase = strtoupper($concatenated);

        // Apply MD5, then SHA1
        $md5 = md5($uppercase);
        $sha1 = sha1($md5);

        // Return as hexadecimal string
        return $sha1;
    }
}
