<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Trade;
use App\Models\Account;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\ScheduledMaintenanceNotification;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class SendScheduledMaintenanceEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $chunkSize;
    protected $batchDelay;
    protected $emailsOption;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     * For 3000 emails at 500 per chunk = 6 chunks × 1 sec = 6 seconds + email time
     */
    public $timeout = 300;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [10, 30, 60];
    }

    /**
     * Create a new job instance.
     */
    public function __construct(int $chunkSize = 100, int $batchDelay = 1, ?string $emailsOption = null)
    {
        $this->chunkSize = $chunkSize;
        $this->batchDelay = $batchDelay;
        $this->emailsOption = $emailsOption;
        $this->onQueue('default');
    }

    /**
     * Get the middleware the job should pass through.
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping('send-scheduled-maintenance-emails'),
        ];
    }

    /**
     * Execute the job - send notifications to provided emails.
     */
    public function handle(): void
    {
        try {
            $settings = settings();

            Log::info('SendScheduledMaintenanceEmailJob: Starting email campaign');

            // Parse emails from JSON
            $decoded = json_decode($this->emailsOption, true);
            if (is_array($decoded)) {
                $emailList = $decoded;
            } else {
                $emailList = array_map('trim', explode(',', $this->emailsOption));
            }
            
            $emails = collect($emailList)->map(function ($email) {
                return (object) ['email' => $email];
            });

            if ($emails->isEmpty()) {
                Log::info('SendScheduledMaintenanceEmailJob: No emails to process');
                return;
            }

            $totalEmails = $emails->count();
            Log::info("SendScheduledMaintenanceEmailJob: Processing {$totalEmails} emails");

            $sent = 0;
            $failed = 0;
            $batch = 0;
            
            // Pre-fetch all users by email
            $emailList = $emails->pluck('email')->toArray();
            $users = User::select('id', 'email', 'fullname')->whereIn('email', $emailList)->get()->keyBy('email');

            // Process in chunks
            $emails->chunk($this->chunkSize)->each(function ($chunk) use ($settings, $users, &$sent, &$failed, &$batch) {
                $batch++;
                $batchStart = $sent;
                
                foreach ($chunk as $email) {
                    try {
                        $user = $users->get($email->email);

                        if (!$user) {
                            $failed++;
                            Log::warning('SendScheduledMaintenanceEmailJob: User not found', [
                                'email' => $email->email
                            ]);
                            continue;
                        }

                        // Send notification
                        $user->notify(new ScheduledMaintenanceNotification($settings));
                        $sent++;
                        
                        // Log successful send
                        Log::info('SendScheduledMaintenanceEmailJob: Email sent successfully', [
                            'email' => $email->email,
                            'user_id' => $user->id,
                            'user_name' => $user->fullname
                        ]);
                    } catch (\Exception $e) {
                        Log::error('SendScheduledMaintenanceEmailJob: Failed to send email', [
                            'email' => $email->email ?? 'unknown',
                            'user_id' => $user->id ?? 'unknown',
                            'error' => $e->getMessage()
                        ]);
                        $failed++;
                    }
                }

                // Progress log
                $batchSent = $sent - $batchStart;
                Log::info("SendScheduledMaintenanceEmailJob: Batch {$batch} done ({$batchSent} emails, total: {$sent} sent)");
                
                // Delay between batches
                if ($this->batchDelay > 0) {
                    sleep($this->batchDelay);
                }
            });

            // Completion log
            Log::info('SendScheduledMaintenanceEmailJob: Campaign completed', [
                'total_emails' => $totalEmails,
                'sent' => $sent,
                'failed' => $failed,
                'success_rate' => round(($sent / $totalEmails) * 100, 2) . '%'
            ]);
        } catch (\Exception $e) {
            Log::error('SendScheduledMaintenanceEmailJob: Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
