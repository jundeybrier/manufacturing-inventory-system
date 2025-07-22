<?php
namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('sync:activated-users')->everyFiveMinutes();
    }

    protected function commands(): void
    {
        // auto-load routes/console.php commands
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
