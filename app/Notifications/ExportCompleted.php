<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $exportType;
    protected $fileName;
    protected $downloadUrl;
    protected $recordCount;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $exportType, string $fileName, string $downloadUrl, int $recordCount)
    {
        $this->exportType = $exportType;
        $this->fileName = $fileName;
        $this->downloadUrl = $downloadUrl;
        $this->recordCount = $recordCount;
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
            ->subject('Export Completed: ' . $this->exportType)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your ' . $this->exportType . ' export has been completed successfully.')
            ->line('Total records exported: ' . number_format($this->recordCount))
            ->line('File name: ' . $this->fileName)
            ->action('Download Export', $this->downloadUrl)
            ->line('This download link will be available for 24 hours.')
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'export_completed',
            'export_type' => $this->exportType,
            'file_name' => $this->fileName,
            'download_url' => $this->downloadUrl,
            'record_count' => $this->recordCount,
            'expires_at' => now()->addDay(),
        ];
    }
}
