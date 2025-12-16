<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \App\Console\Commands\SyncZohoBills::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Sync Zoho bills every hour
        $schedule->command('zoho:sync-bills')->hourly();
        
        // Or every 30 minutes if you want faster sync:
        // $schedule->command('zoho:sync-bills')->everyThirtyMinutes();
        
        // Or every 15 minutes:
        // $schedule->command('zoho:sync-bills')->everyFifteenMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}