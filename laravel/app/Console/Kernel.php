<?php

namespace App\Console;

use App\Console\Commands\CronRun;
use Exception;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        try {
            $schedule->command(CronRun::class)
                ->withoutOverlapping()
                ->everyMinute()
                ->description('Fail2ban CronRun')
                ->sendOutputTo(storage_path('logs/cron.log'))
                ->emailWrittenOutputTo(env('APP_ADMIN_EMAIL', null))
                ->runInBackground();
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                "\n, trace: ".$e->getTraceAsString().
                "\n"
            );
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
