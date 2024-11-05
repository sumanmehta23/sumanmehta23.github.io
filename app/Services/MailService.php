<?php

namespace App\Services;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $settings=settings();
        $this->client = new Client([
            'base_uri' => 'https://api.brevo.com/v3/',
            'timeout' => 10.0,
        ]);
        $this->apiKey = config('services.brevo.api_key', $settings['api_key']);
    }

    public function sendEmail($toEmail, $subject, $headers,$templateFile,$data)
    {
        $settings=settings();
        $htmlContent = view('emails.template', $data)->render();
        $payload = [
            'sender' => [
                'name' => $settings['sender_name'],
                'email' => $settings['sender_email_address'],
            ],
            'to' => [
                [
                    'email' => $toEmail,
                ],
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ];
        try {
            $response = $this->client->post('smtp/email', [
                'headers' => [
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            Log::error('Brevo API Error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ];
        }
    }
}
