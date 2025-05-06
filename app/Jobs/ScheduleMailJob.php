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

class ScheduleMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;
    protected $toEmail;
    protected $subject;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, $toEmail, $subject)
    {
        $this->data = $data;
        $this->toEmail = $toEmail;
        $this->subject = $subject;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $settings = settings();

        try {
            Mail::send('emails.template', $this->data, function (Message $message) use ($settings) {
                $message->from($settings['sender_email_address'], $settings['sender_name']);
                $message->to($this->toEmail);
                $message->subject($this->subject);
            });

            Log::info('Email sent successfully to ' . $this->toEmail);
        } catch (\Exception $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
        }
    }
}
