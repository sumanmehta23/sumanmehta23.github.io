<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduledMaintenanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $settings;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
        $this->onQueue('default');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->view('emails.scheduled-maintenance-notification', [
                'user' => $notifiable,
                'settings' => $this->settings,
            ])
            ->subject('Action Required: All Positions Must Be Closed by Friday 4 April');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
