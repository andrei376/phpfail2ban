<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Models\IpInfo;
use App\Models\RblLog;
use App\Models\Whois;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CronRun extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CronRun';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'DB maintenance';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function uptime(): ?string
    {
        if (!function_exists('posix_times')) {
            return null;
        }

        if (!$times = posix_times() ) {
            return null;
        } else {
            $now = $times['ticks']/1000;
            $days = intval($now / (60*60*24*100));
            $remainder = $now % (60*60*24*100);
            $hours = intval($remainder / (60*60*100));
            $remainder = $remainder % (60*60*100);
            $minutes = intval($remainder / (60*100));

            $writeDays = "days";
            $writeHours = "hours";
            $writeMins = "minutes";

            if ($days == 1) {
                $writeDays = "day";
            }
            if ($hours == 1) {
                $writeHours = "hour";
            }
            if ($minutes == 1) {
                $writeMins = "minute";
            }

            return ("$days $writeDays, $hours $writeHours, $minutes $writeMins");
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        set_time_limit(1200);
        $time_start = microtime(true);

        /*
        $this->error('test');
        $this->newLine(3);
        $this->line('Display this on the screen');
        */

        $seconds = 301;

        if (function_exists('posix_times')) {
            $times = posix_times();

            $seconds = $times['ticks']/1000/100;
        }

        if ($seconds <= 300) {
            $this->error(__('[Server started recently, wait 5 minutes. Uptime: :uptime]', ['uptime' => $this->uptime()]));
            return -1;
        }

        try {
            //$this->line(date('H:i:s').' re-enable functions in handle()');

            $this->cleanup();

            // $this->check();

            // $this->export();
            //
        } catch (Exception $e) {
            $this->line(
                __(
                    "[ERROR! message: :msg, trace: \n :trace]",
                    [
                        'msg' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]
                )
            );

            return -1;
        }

        $time_end = microtime(true);
        $time = round($time_end - $time_start, 4);

        $file = storage_path('logs/cron.log');

        $str = \file($file);

        if (!empty($str)) {
            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }

        return 0;
    }

    /**
     * DB maintenance/cleanup
     *
     * @throws Exception
     */
    public function cleanup(): void
    {
        $time_start = microtime(true);

        DB::enableQueryLog();

        $whois = !$this->cleanupWhois();

        //not yet
        $delete = false;
        // $delete = !$this->cleanupDelete();

        $agents = !$this->cleanupAgents();

        $syslog = false;
        // $syslog = !$this->cleanupSyslog();


        if ($whois || $delete || $agents || $syslog) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    /**
     * delete rows from whois table after 1 month
     *
     * (meaning cache whois information for 1 month)
     */
    private function cleanupWhois(): bool
    {
        try {
            Whois::where('mask', '<=', 8)->delete();

            $deleted = Whois::
            where('date', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 1 MONTH)'))
                ->delete();

            //$this->line('$deleted='.print_r($deleted, true));
        } catch (Exception $e) {
            $this->line(__('[ERROR! purging whois table: :msg]', ['msg' => $e->getMessage()]));

            return false;
        }

        if ($deleted > 0) {
            RblLog::saveLog('crontab', __('[cleanup whois]'), __('[Deleted :deleted rows from whois table (older than 1 month)]', ['deleted' => $deleted]));
        }

        return true;
    }

    /**
     * delete rows from agents table which no longer exist in ipinfo
     *
     * (IPs deleted)
     *
     * @return bool
     * @throws Exception
     */
    private function cleanupAgents(): bool
    {
        $model = new IpInfo();

        try {
            $deleted = Agent::
                whereRaw('`ip_info_id` NOT IN (SELECT `id` from '.$model->getTable().' WHERE `id`=`ip_info_id`)')
                ->delete();
        } catch (Exception $e) {
            $this->line(__('[ERROR! cleaning agents table: :msg]', ['msg' => $e->getMessage()]));
            return false;
        }

        //$this->line('$deleted='.print_r($deleted, true));

        if ($deleted > 0) {
            RblLog::saveLog('crontab', __('[cleanup agents]'), __('[Deleted :deleted rows from agents. IDs not found in ipinfo]', ['deleted' => $deleted]));
        }

        return true;
    }
}
