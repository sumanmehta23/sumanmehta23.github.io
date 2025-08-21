<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportFailed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $exportType;
    protected $errorMessage;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $exportType, string $errorMessage)
    {
        $this->exportType = $exportType;
        $this->errorMessage = $errorMessage;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Export Failed: ' . $this->exportType)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Unfortunately, your ' . $this->exportType . ' export has failed.')
            ->line('Error: ' . $this->errorMessage)
            ->line('Please try again or contact support if the issue persists.')
            ->line('Thank you for your patience.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_failed',
            'export_type' => $this->exportType,
            'error_message' => $this->errorMessage,
        ];
    }
}
