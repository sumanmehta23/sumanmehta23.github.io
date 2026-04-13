<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlatformEnum;
use App\Http\Controllers\Controller;
use App\Jobs\SendScheduledMaintenanceEmailJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaintenanceEmailController extends Controller
{
    /**
     * Index - Show maintenance email page
     */
    public function index()
    {
        return view('admin.maintenance-email');
    }

    /**
     * Preview email - Renders the email template
     */
    public function previewEmail()
    {
        try {
            $testUser = User::first();
            if (!$testUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found for preview'
                ], 404);
            }

            $settings = settings();

            $html = \Illuminate\Support\Facades\View::make('emails.scheduled-maintenance-notification', [
                'user' => $testUser,
                'settings' => $settings
            ])->render();

            return response($html)->header('Content-Type', 'text/html; charset=utf-8');
        } catch (\Exception $e) {
            return response('Error rendering preview: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Fetch emails from database - latest open position per email
     */
    public function fetchEmails()
    {
        try {
            $mt5Platform = PlatformEnum::MT5->value;
            $emails = DB::table(DB::raw('(
                SELECT 
                    u.email,
                    ROW_NUMBER() OVER (PARTITION BY u.email ORDER BY t.open_time DESC) as rn
                FROM trades t
                JOIN accounts a ON a.id = t.account_id
                JOIN aspnetusers u ON u.id = a.user_id
                WHERE LOWER(t.status) IN (\'open\', \'opened\')
                  AND t.open_time >= \'2026-01-01\'
                  AND a.platform = \'' . $mt5Platform . '\'
                  AND a.demo = 0
                  AND DATE(a.last_trade_sync_at) = CURDATE()
                  AND u.maintenance_email_sent = 0
            ) x'))
            ->where('rn', 1)
            ->limit(10000)
            ->pluck('email');

            if ($emails->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No open positions found'
                ]);
            }

            return response()->json([
                'success' => true,
                'count' => $emails->count(),
                'emails' => $emails
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send emails - Dispatch background job
     */
    public function sendEmails(Request $request)
    {
        try {
            $request->validate([
                'emails' => 'required|array|min:1',
                'emails.*' => 'email'
            ]);

            // Validate emails exist in database
            $valid_emails = User::select('email')
                ->whereIn('email', $request->emails)
                ->distinct()
                ->pluck('email')
                ->toArray();
            
            if (empty($valid_emails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid users found'
                ], 422);
            }
            
            \Log::info('Email job dispatched', [
                'total_emails' => count($valid_emails)
            ]);

            dispatch(new SendScheduledMaintenanceEmailJob(
                chunkSize: 500,
                batchDelay: 1,
                emailsOption: json_encode($valid_emails)
            ))->onQueue('default');

            return response()->json([
                'success' => true,
                'message' => 'Email campaign started!',
                'sent' => count($valid_emails)
            ]);
        } catch (\Exception $e) {
            \Log::error('sendEmails error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
