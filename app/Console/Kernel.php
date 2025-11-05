<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * You can add custom command classes here.
     */
    protected $commands = [
        \App\Console\Commands\SendBirthdayWishes::class,
    ];

    protected $routeMiddleware = [
        // ...
        'is_admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
    ];

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run birthday wishes job daily at 8:00 AM
        $schedule->command('send:birthday-wishes --sms')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/birthday-wishes.log'));

        // run daily at 8:00 AM server time (adjust to your preference)
        $schedule->command('greetings:anniversary')->dailyAt('07:00');
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
