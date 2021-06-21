<?php

namespace App\Console\Commands;

use App\Helpers\WhoisLib;
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

            $this->check();

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


        if ($whois || $delete || $agents) {
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

        /*if ($deleted > 0) {
            RblLog::saveLog('crontab', __('[cleanup whois]'), __('[Deleted :deleted rows from whois table (older than 1 month)]', ['deleted' => $deleted]));
        }*/

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
                ->forceDelete();
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

    /**
     * checks
     *
     */
    public function check(): void
    {
        $time_start = microtime(true);

        DB::enableQueryLog();

        $debug = !$this->checkNewActions();

        $update = !$this->checkMissingInfo();

        $oldInfo = !$this->checkOldWhois();

        if ($debug || $update || $oldInfo) {
            $time_end = microtime(true);
            $time = round($time_end - $time_start, 4);

            $logs = DB::getQueryLog();

            $this->line("SQL LOG: ".print_r($logs, true));

            $this->line(__('[:method took :time seconds]', ['time' => $time, 'method' => __METHOD__]));
        }
    }

    /**
     * if new actions found newer than last_check -> mark unchecked
     *
     * @return bool
     */
    private function checkNewActions(): bool
    {
        $toCheck = IpInfo::
            where('checked', 1)
            ->select(['ip_infos.*'])
            ->join('agents', function ($join) {
                $join->on('agents.ip_info_id', '=', 'ip_infos.id')
                    ->whereNull('agents.deleted_at');
            })
            ->whereColumn('agents.created_at', '>', 'ip_infos.last_check')
            ->get();

        // $this->line('to check='. print_r($toCheck->toArray(), true));

        foreach ($toCheck as $row) {
            $row->last_check = date('Y-m-d H:i:s');
            $row->checked = 0;

            // $this->line('to save ip='. print_r($row->toArray(), true));

            try {
                $row->saveOrFail();
            } catch (Exception $e) {
                $this->line(__("[ERROR! saving update, msg: :msg, trace:\n :trace]", ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                return false;
            }
        }

        return true;
    }


    /**
     * if new ipinfo with empty whois data, update
     *
     * @return bool
     */
    private function checkMissingInfo()
    {
        //
        $toUpdate = IpInfo::
            where('checked', 0)
            ->whereNull(['inetnum', 'netname', 'country', 'orgname', 'geoipcountry', 'last_check'])
            ->first();

        if (empty($toUpdate)) {
            // $this->line('nothing to update');
            return true;
        }

        // $this->line('to update='. print_r($toUpdate->toArray(), true));

        $whoisLib = new WhoisLib();

        $ipAddr = $toUpdate->format_ip;

        $cidrInfo = $ipAddr.'/'.$toUpdate->mask;

        $geoCountry = @geoip_country_name_by_name($ipAddr);

        //get whois
        $whoisData = $whoisLib->searchCache($cidrInfo, false);

        //update whois
        $toUpdate->update([
            'inetnum' => $whoisData['inetnum'],
            'netname' => $whoisData['netname'],
            'country' => $whoisData['country'],
            'orgname' => $whoisData['orgname'],
            'geoipcountry' => $geoCountry,
            'last_check' => now(),
            'checked' => 1
        ]);

        return true;
    }

    /**
     * update last_check field
     *
     * mark unchecked if whois data is changed
     *
     *
     * @return bool
     */
    private function checkOldWhois(): bool
    {
        $whoisLib = new WhoisLib();

        $toCheck = IpInfo::
            where('last_check', '<', DB::raw('DATE_SUB(NOW(),INTERVAL 7 MONTH)'))
            ->where('checked', 1)
            ->orderBy('created_at', 'asc')
            ->first();

        if (is_null($toCheck)) {
            //nothing to do
            return true;
        }

        // $this->line('$toCheck = '.print_r($toCheck->toArray(), true));

        $ipWhois = $toCheck->format_ip . '/' . $toCheck->mask;

        // $this->line('ip='.$ipWhois);

        $whoisData = $whoisLib->searchCache($ipWhois);

        //$this->line('whoisData='.print_r($whoisData, true));

        if ($whoisData['inetnum'] != $toCheck->inetnum ||
            $whoisData['netname'] != $toCheck->netname ||
            $whoisData['country'] != $toCheck->country ||
            $whoisData['orgname'] != $toCheck->orgname
        ) {
            //whois is changed, update last check and mark unchecked so a human checks the ip
            $toCheck->last_check = date('Y-m-d H:i:s');
            $toCheck->checked = 0;

            //$this->line('to save ip='. print_r($toCheck->toArray(), true));

            try {
                $toCheck->saveOrFail();
            } catch (Exception $e) {
                $this->line(__("[ERROR! saving update ip, msg: :msg, trace:\n :trace]", ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                return false;
            }
        } else {
            // $this->line($ipWhois.' no changes');

            //all ok, update last check
            $toCheck->last_check = date('Y-m-d H:i:s');

            try {
                $toCheck->saveOrFail();
            } catch (Exception $e) {
                $this->line(__("[ERROR! saving update ip, msg: :msg, trace:\n :trace]", ['msg' => $e->getMessage(), 'trace' => $e->getTraceAsString()]));
                return false;
            }
        }


        return true;
    }
}
