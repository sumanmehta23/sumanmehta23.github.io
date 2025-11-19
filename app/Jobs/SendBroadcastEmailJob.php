<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\EmailBroadcasting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBroadcastEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subject, $content, $settings;

    public function __construct($subject, $content, $settings)
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->settings = $settings;
    }

    public function handle()
    {
        User::chunk(50, function ($users) {
            foreach ($users as $user) {
                $emailSubject = $this->settings['admin_title'] . ' ' . $this->subject;

                $templateVars = [
                    'name' => $user->fullname,
                    'email' => $this->settings['email_from_address'],
                    'content' => $this->content,
                    "title_right" => "",
                    "subtitle_right" => ""
                ];

                $user->notify(new EmailBroadcasting(
                    $this->settings,
                    $emailSubject,
                    $templateVars
                ));
            }
        });
    }
}
