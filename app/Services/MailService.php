<?php

namespace App\Services;

use GuzzleHttp\Client;
use App\Jobs\ScheduleMailJob;
use Illuminate\Support\Facades\Log;

class MailService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.brevo.com/v3/',
            'timeout' => 10.0,
        ]);

        // Prefer environment variables in testing, fallback to settings() for production
        if (app()->environment('testing')) {
            $this->apiKey = config('services.brevo.api_key', env('BREVO_API_KEY', 'test_brevo_api_key'));
        } else {
            $settings = settings();
            $this->apiKey = config('services.brevo.api_key', $settings['api_key']);
        }
    }

    public function sendEmail($toEmail, $subject, $headers, $templateFile, $data)
    {
        try {
            ScheduleMailJob::dispatch($data, $toEmail, $subject); // Remove ->onQueue('emails')
            // $response = $this->client->post('smtp/email', [
            //     'headers' => [
            //         'api-key' => $this->apiKey,
            //         'Content-Type' => 'application/json',
            //     ],
            //     'json' => $payload,
            // ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Brevo API Error: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ];
        }
    }
}
