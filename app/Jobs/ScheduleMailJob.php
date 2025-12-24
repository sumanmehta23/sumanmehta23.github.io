<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ScheduleMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $toEmail;
    protected $subject;
    protected $apiKey;
    /**
     * Create a new job instance.
     */
    public function __construct(array $data, $toEmail, $subject)
    {
        $this->data = $data;
        $this->toEmail = $toEmail;
        $this->subject = $subject;
        $this->apiKey = config('services.brevo.api_key');
        // dd($this);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $settings = settings();
        $maildriver = config('mail.default') ?? 'smtp';
        // dd($this->subject);
        Log::alert('Email subject: ' . $this->subject);
        try {


            if (strpos($this->subject, 'Competition Registration') !== false) {
                $template = 'emails.competition_registration';
            } else if (strpos($this->subject, 'Competition Activated') !== false) {
                $template = 'emails.competition_activated';
            } else if (strpos($this->subject, 'Competition Ended') !== false) {
                $template = 'emails.competition_ended';
            } else if (
                strpos($this->subject, 'Withdrawal Details Verification') !== false ||
                strpos($this->subject, 'Thank You for Confirming Your Wallet Withdrawal') !== false ||
                strpos($this->subject, 'Thank You for Confirming Your Wallet Address') !== false
            ) {
                $template = 'emails.emailVerification';
            } else if (strpos($this->subject, 'Transaction Approved') !== false) {
                $template = 'emails.transactionApproved';
            } elseif (strpos($this->subject, 'Fund Deposit') !== false) {
                $template = 'emails.fundsAdd';
            } elseif ((strpos($this->subject, 'Live Account Details') !== false)) {
                $template = 'emails.issueLiveAccount';
            } elseif ((strpos($this->subject, 'Demo Account Details') !== false)) {
                $template = 'emails.issueDemoAccount';
            }elseif ((strpos($this->subject, 'Account Details') !== false)) {
                $template = 'emails.resendAccountDetails';
            } elseif ((strpos($this->subject, 'Competition Account Details') !== false)) {
                $template = 'emails.issueCompetitionAccount';
            } elseif (strpos($this->subject, 'Password Reset') !== false) {
                $template = 'emails.resetPassword';
            } elseif (strpos($this->subject, 'Export Started') !== false) {
                $template = 'emails.export-started';
            } elseif (strpos($this->subject, 'Export Completed') !== false) {
                $template = 'emails.export-completed';
            } elseif (strpos($this->subject, 'Export Failed') !== false) {
                $template = 'emails.export-failed';
            } else {
                $template = 'emails.defaultTemplate';
                // $template = 'emails.template';
            }
            Log::info("maildriver".$maildriver);
            Log::info("api key".$this->apiKey);
            Log::info("emailsubject".$this->subject);
            // Always use Brevo API for export emails or if configured
            if (strpos($this->subject, 'Export') !== false || $maildriver == 'brevo' || $this->apiKey) {
                $htmlContent = view($template, $this->data)->render();
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

                $response = Http::withHeaders([
                    'api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', $payload);

                if ($response->failed()) {
                    Log::error('Brevo email sending failed', [
                        'subject' => $this->subject,
                        'email' => $this->toEmail,
                        'response' => $response->body()
                    ]);
                } else {
                    Log::info('Email sent successfully via Brevo', [
                        'subject' => $this->subject,
                        'email' => $this->toEmail
                    ]);
                }
            } else {
                Mail::send($template, $this->data, function (Message $message) use ($settings) {
                    $message->from($settings['sender_email_address'], $settings['sender_name']);
                    $message->to($this->toEmail);
                    $message->subject($this->subject);
                });
            }
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }
    }
}
