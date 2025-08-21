<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportStarted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $exportType;
    protected $estimatedRecords;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $exportType, int $estimatedRecords = 0)
    {
        $this->exportType = $exportType;
        $this->estimatedRecords = $estimatedRecords;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database']; // Only database notification for start
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_started',
            'export_type' => $this->exportType,
            'estimated_records' => $this->estimatedRecords,
            'started_at' => now(),
        ];
    }
}
