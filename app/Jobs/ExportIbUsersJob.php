<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\EmployeeList;
use App\Models\Ib1;
use App\Exports\IbUsersExport;
use App\Notifications\ExportCompleted;
use App\Notifications\ExportFailed;
use App\Notifications\ExportStarted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Exception;

class ExportIbUsersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $filters;
    protected $fileName;
    protected $exportEmail;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     */
    public $timeout = 1800; // 30 minutes

    /**
     * Create a new job instance.
     */
    public function __construct($user, array $filters = [], ?string $fileName = null, ?string $exportEmail = null)
    {
        $this->user = $user;
        $this->filters = $filters;
        $this->fileName = $fileName ?? 'IB_List_' . date('Y-m-d_H-i-s') . '.xlsx';
        $this->exportEmail = $exportEmail ?? $this->getUserEmail();
    }

    /**
     * Get user email from either User or EmployeeList model
     */
    protected function getUserEmail(): string
    {
        if ($this->user instanceof User) {
            return $this->user->email ?? 'admin@example.com';
        } elseif ($this->user instanceof EmployeeList) {
            return $this->user->email ?? 'admin@example.com';
        }
        
        return 'admin@example.com';
    }

    /**
     * Get user name from either User or EmployeeList model
     */
    protected function getUserName(): string
    {
        if ($this->user instanceof User) {
            return $this->user->name ?? $this->user->fullname ?? 'Admin User';
        } elseif ($this->user instanceof EmployeeList) {
            return $this->user->name ?? $this->user->full_name ?? 'Admin User';
        }
        
        return 'Admin User';
    }

    /**
     * Get user ID from either User or EmployeeList model
     */
    protected function getUserId(): string
    {
        return $this->user->id ?? 'unknown';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Get estimated record count
            $estimatedCount = $this->getEstimatedRecordCount();

            // Send start notification via email directly
            $this->sendExportStartedEmail($estimatedCount);

            // Create the export instance with filters
            $export = new IbUsersExport($this->filters);

            // Store the file in exports directory
            $filePath = 'exports/' . $this->fileName;
            
            // Generate the export
            Excel::store($export, $filePath, 'local');

            // Verify file was created
            if (!Storage::disk('local')->exists($filePath)) {
                throw new Exception('Export file was not created successfully');
            }

            // Get actual record count from the export
            $actualCount = $this->getActualRecordCount();

            // Generate download URL (valid for 24 hours)
            $downloadUrl = route('admin.admin.download.export', [
                'file' => $this->fileName,
                'token' => $this->generateDownloadToken()
            ]);

            // Send completion notification via email
            $this->sendExportCompletedEmail($actualCount, $downloadUrl);

        } catch (Exception $e) {
            // Send failure notification via email
            $this->sendExportFailedEmail($e->getMessage());

            // Re-throw the exception to mark the job as failed
            throw $e;
        }
    }

    /**
     * Get estimated record count for the export
     */
    protected function getEstimatedRecordCount(): int
    {
        $query = Ib1::query()->where('status', 1);

        // Apply filters if provided
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->count();
    }

    /**
     * Get actual record count from the database
     */
    protected function getActualRecordCount(): int
    {
        return $this->getEstimatedRecordCount();
    }

    /**
     * Generate a secure download token
     */
    protected function generateDownloadToken(): string
    {
        return encrypt([
            'user_id' => $this->getUserId(),
            'file_name' => $this->fileName,
            'expires_at' => now()->addDay()
        ]);
    }

    /**
     * Get estimated file size based on record count
     */
    protected function getFileSizeEstimate(int $recordCount): string
    {
        // Estimate ~2KB per record for Excel file
        $sizeInKB = $recordCount * 2;
        
        if ($sizeInKB < 1024) {
            return $sizeInKB . ' KB';
        } elseif ($sizeInKB < 1024 * 1024) {
            return round($sizeInKB / 1024, 1) . ' MB';
        } else {
            return round($sizeInKB / (1024 * 1024), 1) . ' GB';
        }
    }

        /**
     * Send export started email notification
     */
    protected function sendExportStartedEmail(int $estimatedCount): void
    {
        try {
            Mail::send('emails.export-started', [
                'userName' => $this->getUserName(),
                'name' => $this->getUserName(),
                'content' => 'Your IB Users export has been initiated and is currently being processed. You will receive another email with the download link once the export is completed.',
                'exportType' => 'IB Users',
                'estimatedRecords' => $estimatedCount,
                'startedAt' => now()->format('Y-m-d H:i:s'),
                'startDate' => now()->format('Y-m-d H:i:s'),
                'processingTime' => $this->getEstimatedProcessingTime($estimatedCount),
                'filters' => $this->getFilterDescription(),
                'adminPanelUrl' => url('/admin/iblist_active'),
                'estimatedCompletion' => now()->addMinutes($this->getEstimatedProcessingTime($estimatedCount))->format('Y-m-d H:i:s')
            ], function ($message) use ($estimatedCount) {
                $message->to($this->exportEmail, $this->getUserName())
                       ->subject('Export Started: IB Users - Processing ' . number_format($estimatedCount) . ' Records');
            });
        } catch (Exception $e) {
            Log::error('Failed to send export started email: ' . $e->getMessage());
            // Silently fail if email can't be sent
        }
    }

    /**
     * Send export completed email notification
     */
    protected function sendExportCompletedEmail(int $recordCount, string $downloadUrl): void
    {
        try {
            Mail::send('emails.export-completed', [
                'userName' => $this->getUserName(),
                'name' => $this->getUserName(),
                'content' => 'Great news! Your IB Users export has been completed successfully. Your file is ready for download and will be available for the next 24 hours.',
                'exportType' => 'IB Users',
                'fileName' => $this->fileName,
                'recordCount' => $recordCount,
                'downloadUrl' => $downloadUrl,
                'expiresAt' => now()->addDay()->format('M d, Y \a\t H:i'),
                'exportDate' => now()->format('M d, Y \a\t H:i'),
                'fileSizeEstimate' => $this->getFileSizeEstimate($recordCount),
                'totalRecords' => $recordCount,
                'completedAt' => now()->format('Y-m-d H:i:s'),
                'downloadToken' => $this->generateDownloadToken(),
                'supportEmail' => 'support@lqhmarkets.com',
                'adminPanelUrl' => url('/admin/iblist_active')
            ], function ($message) use ($recordCount) {
                $message->to($this->exportEmail, $this->getUserName())
                       ->subject('✅ Export Ready: IB Users (' . number_format($recordCount) . ' records)');
            });
        } catch (Exception $e) {
            Log::error('Failed to send export completed email: ' . $e->getMessage());
            // Silently fail if email can't be sent
        }
    }

    /**
     * Send export failed email notification
     */
    protected function sendExportFailedEmail(string $errorMessage): void
    {
        try {
            Mail::send('emails.export-failed', [
                'userName' => $this->getUserName(),
                'name' => $this->getUserName(),
                'content' => 'Unfortunately, your IB Users export could not be completed due to an error. Please try again or contact support if the issue persists.',
                'exportType' => 'IB Users',
                'errorMessage' => $errorMessage,
                'failedAt' => now()->format('M d, Y \a\t H:i'),
                'attemptDate' => now()->format('Y-m-d H:i:s'),
                'supportEmail' => 'support@lqhmarkets.com',
                'adminPanelUrl' => url('/admin/iblist_active'),
                'retryUrl' => url('/admin/iblist_active'),
                'troubleshootingTips' => [
                    'Try exporting a smaller date range',
                    'Check your internet connection',
                    'Wait a few minutes before retrying',
                    'Contact support if the issue persists'
                ],
                'errorCode' => 'EXP_' . time(),
                'requestedRecords' => $this->getEstimatedRecordCount()
            ], function ($message) {
                $message->to($this->exportEmail, $this->getUserName())
                       ->subject('❌ Export Failed: IB Users - Please Try Again');
            });
        } catch (Exception $e) {
            Log::error('Failed to send export failed email: ' . $e->getMessage());
            // Silently fail if email can't be sent
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Exception $exception): void
    {
        // Send final failure notification
        $this->sendExportFailedEmail(
            'Export failed after ' . $this->tries . ' attempts: ' . $exception->getMessage()
        );
    }

    /**
     * Get estimated processing time in minutes based on record count
     */
    protected function getEstimatedProcessingTime(int $recordCount): int
    {
        // Estimate 1 minute per 1000 records, minimum 2 minutes
        return max(2, ceil($recordCount / 1000));
    }

    /**
     * Get description of applied filters
     */
    protected function getFilterDescription(): string
    {
        $descriptions = [];
        
        if (!empty($this->filters['status'])) {
            $descriptions[] = 'Status: ' . $this->filters['status'];
        }
        
        if (!empty($this->filters['date_from'])) {
            $descriptions[] = 'From: ' . $this->filters['date_from'];
        }
        
        if (!empty($this->filters['date_to'])) {
            $descriptions[] = 'To: ' . $this->filters['date_to'];
        }
        
        return empty($descriptions) ? 'No filters applied' : implode(', ', $descriptions);
    }
}
