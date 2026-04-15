<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('telescope:prune')->daily();
        $schedule->command('queue:prune-batches --hours=48')->daily();

        $schedule->command('app:activate-competition-accounts')->everyTenSeconds();
        // $schedule->command('app:sync-trades')->everyFiveMinutes();

        //sync closed competition trades
        $schedule->command('app:sync-closed-trades')->everyTenMinutes();

        // $schedule->command('app:breach-account')->monthlyOn(1, '00:00');

        $schedule->command('app:breach-account')->everyMinute();

        // CONSOLIDATED SYNC: Use priority-sync instead of multiple commands
        // $schedule->command('app:priority-sync --daemon --max-pending-jobs=100')->everyFiveMinutes()->withoutOverlapping();

        // DISABLED: Replaced by priority-sync
        // $schedule->command('app:sync-accounts')->everyFiveMinutes();
        // $schedule->command('app:sync-account-trades')->hourly();


        $schedule->command('app:sync-daily-reports')->daily();
        $schedule->command('app:sync-mt5-account-status')->daily();
        // Sync all non-competition accounts trades - uncomment to enable
        // $schedule->command('app:sync-all-accounts-trades --batch-size=20 --delay=60')->everyThirtyMinutes();

        // $schedule->command('app:update-price-snapshots')->hourly();

        // Export cleanup - runs daily at 2 AM to clean up exports older than 7 days
        $schedule->command('export:cleanup --days=7')->daily()->at('02:00');

        // Mark inactive users based on login_history - runs daily at 3 AM
        $schedule->command('app:mark-inactive-users')->daily()->at('03:00');

        // Sync FXStreet feed through RSS2JSON and keep DB cache fresh.
        $schedule->command('app:sync-forex-news')->everyThirtyMinutes()->withoutOverlapping();

        $schedule->command('app:alter-group-codes --group_code=a_book');
        $schedule->command('app:alter-group-codes --group_code=b_book');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
