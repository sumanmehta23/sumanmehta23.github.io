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
        // $schedule->command('app:alter-group-codes --group_code=a_book')->hourlyAt(0);
        // $schedule->command('app:alter-group-codes --group_code=b_book')->hourlyAt(30);
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
