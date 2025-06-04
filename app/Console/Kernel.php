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
        $schedule->command('app:sync-account-trades')->everyTwoHours();
        $schedule->command('app:update-price-snapshots')->hourly();
        // $schedule->command('app:activate-competition-accounts')->monthlyOn(1, '00:00');
        $schedule->command('app:sync-trades')->everyFiveMinutes();
        $schedule->command('app:sync-accounts')->everyFiveMinutes();
        $schedule->command('sync:daily-reports')->dailyAt('12:00');
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
