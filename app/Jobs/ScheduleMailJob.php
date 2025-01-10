<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;

class ScheduleMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;
    protected $data;
    protected $apiKey;
    protected $toEmail;
    protected $subject;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data,$toEmail,$subject)
    {
        
        $this->data = $data;
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->apiKey = config('services.brevo.api_key');
    }

    /**
     * Execute the job.
     */
    public function handle(Client $client): void
    {
        $settings=settings();
        $htmlContent = view('emails.template', $this->data)->render();
        $payload = [
            'sender' => [
                'name' => $settings['sender_name'],
                'email' => $settings['sender_email_address'],
            ],
            'to' => [
                [
                    'email' => $this->toEmail,
                ],
            ],
            'subject' => $this->subject,
            'htmlContent' => $htmlContent,
        ];
        $client->post('https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);
    }
}