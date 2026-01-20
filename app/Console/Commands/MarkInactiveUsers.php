<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MarkInactiveUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mark-inactive-users 
                            {--days= : Number of days of inactivity to mark as inactive}
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark users as inactive based on login_history records';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of days from option or config file
        $days = $this->option('days') ?: config('constants.INACTIVE_USER_DAYS', 30);
        $dryRun = $this->option('dry-run');

        if (!is_numeric($days) || $days < 1) {
            $this->error('Days must be a positive number');
            return 1;
        }

        $this->info("Checking for users inactive for {$days} days...");
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }

        $cutoffDate = Carbon::now()->subDays($days);

        // Find users who have login history but haven't logged in for X days
        $usersWithLoginHistory = DB::table('login_history')
            ->select('user_id', DB::raw('MAX(created_date_js) as last_login'))
            ->where('action', 'login')
            ->where('status', 1) // Only successful logins
            ->groupBy('user_id')
            ->havingRaw('MAX(created_date_js) < ?', [$cutoffDate])
            ->pluck('user_id')
            ->toArray();

        // Find users who have never logged in (no login history) and registered before cutoff date
        $usersNeverLoggedIn = DB::table('aspnetusers')
            ->whereNotIn('id', function ($query) {
                $query->select('user_id')
                    ->from('login_history')
                    ->where('action', 'login')
                    ->where('status', 1);
            })
            ->where('reg_date', '<', $cutoffDate)
            ->where('is_inactive', false)
            ->pluck('id')
            ->toArray();

        // Combine both sets of user IDs
        $inactiveUserIds = array_unique(array_merge($usersWithLoginHistory, $usersNeverLoggedIn));

        if (empty($inactiveUserIds)) {
            $this->info('No inactive users found.');
            return 0;
        }

        $count = count($inactiveUserIds);
        $this->info("Found {$count} inactive user(s)");

        if ($dryRun) {
            $this->table(
                ['User ID', 'Email', 'Last Login', 'Status'],
                User::whereIn('id', $inactiveUserIds)
                    ->select('id', 'email', 'reg_date')
                    ->get()
                    ->map(function ($user) use ($cutoffDate) {
                        $lastLogin = DB::table('login_history')
                            ->where('user_id', $user->id)
                            ->where('action', 'login')
                            ->where('status', 1)
                            ->max('created_date_js');

                        return [
                            'id' => $user->id,
                            'email' => $user->email,
                            'last_login' => $lastLogin ? Carbon::parse($lastLogin)->format('Y-m-d H:i:s') : 'Never',
                            'status' => 'Would be marked inactive'
                        ];
                    })
                    ->toArray()
            );
            $this->info("DRY RUN: Would mark {$count} user(s) as inactive");
            return 0;
        }

        // Mark users as inactive
        $updated = User::whereIn('id', $inactiveUserIds)
            ->where('is_inactive', false)
            ->update(['is_inactive' => true]);

        $this->info("Successfully marked {$updated} user(s) as inactive.");

        // Show summary
        $this->table(
            ['Metric', 'Count'],
            [
                ['Users with old login history', count($usersWithLoginHistory)],
                ['Users who never logged in', count($usersNeverLoggedIn)],
                ['Total marked inactive', $updated],
            ]
        );

        return 0;
    }
}
