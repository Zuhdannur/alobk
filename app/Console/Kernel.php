<?php

namespace App\Console;

use App\Cron;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('command:updateschedule')->everyMinute()->when(function() {
            return Cron::shouldIRun('command:updateschedule', 1);
            //returns true every hour
        });
        $schedule->command('command:startschedule')->everyMinute()->when(function() {
            return Cron::shouldIRun('command:startschedule', 1);
            //returns true every hour
        });
        $schedule->command('command:daringexpired')->everyMinute()->when(function() {
            return Cron::shouldIRun('command:daringexpired', 1);
            //returns true every hour
        });
    }

    protected $commands = [
        Commands\ExpiredScheduler::class,
        Commands\StartScheduler::class,
        Commands\DaringExpiredScheduler::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

}
