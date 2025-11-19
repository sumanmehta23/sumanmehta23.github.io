<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MailService; // adjust if your service is in different namespace
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBroadcastEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $subject;
    public $content;
    public $settings;

    /**
     * Create a new job instance.
     */
    public function __construct($subject, $content, $settings)
    {
        $this->subject   = $subject;
        $this->content   = $content;
        $this->settings  = $settings;
    }

    /**
     * Execute the job.
     */
    public function handle(MailService $mailService)
    {
        $users = User::select('fullname', 'email')->get();

        foreach ($users as $user) {

            // personalize email
            $personalContent = str_replace('{{name}}', $user->fullname, $this->content);

            $emailSubject = $this->settings['admin_title'] . ' ' . $this->subject;

            $templateVars = [
                'name'      => $user->fullname,
                'email'     => $this->settings['email_from_address'],
                'content'   => $personalContent,
                "title_right" => "",
                "subtitle_right" => ""
            ];

            // send email using your mail service
            $mailService->sendEmail($user->email, $emailSubject, '', '', $templateVars);
        }
    }
}
