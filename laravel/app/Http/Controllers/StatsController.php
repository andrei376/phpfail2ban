<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\IpInfo;
use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Inertia\Response;

class StatsController extends Controller
{
    public function index(): Response
    {
        /*$flash = [
            'msg_success' => 'test success',
            'msg_error' => 'test error',
            'msg_warning' => 'test warning',
            'msg_info' => 'test info'
        ];*/

        return Inertia::render('Stats/Index', [
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            // 'flash' => $flash
        ]);
    }

    public function stats(): JsonResponse
    {
        if (function_exists('sys_getloadavg')) {
            $loadAvg = sys_getloadavg();
        } else {
            $loadAvg[0] = 'sys_getloadavg() missing';
            $loadAvg[1] = '';
            $loadAvg[2] = '';
        }

        $data['loadAvg'] = $loadAvg;
        $data['date'] = date('H:i:s');

        return response()->json($data);
    }

    /**
     * save info received from agent
     *
     * @param $action = ban/unban
     * @param $jail = name of jail
     * @param $ip
     * @return JsonResponse
     */
    public function save($action, $jail, $ip): JsonResponse
    {
        if (!auth()->user()->tokenCan('create')) {
            abort(403, __('[Unauthorized]'));
        }

        $agent = auth()->user()->currentAccessToken()->name ?? 'agent_missing';

        if (stripos($ip, ':') !== false) {
            //ipv6
            $mask = 128;
        } else {
            //ipv4
            $mask = 32;
        }

        if (stripos($ip, '/') !== false) {
            //it's cidr notation
            list($ip, $mask) = explode('/', $ip);
        }

        // dump($ip);
        // dump($mask);

        //validate ip
        $validator = Validator::make(['ip' => $ip, 'action' => $action, 'jail' => $jail], [
            'ip' => [
                'required',
                function ($attribute, $value, $fail) {
                    $result = filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

                    if (!$result) {
                        $fail(__('[:ip is invalid.]', ['ip' => $value]));
                        return false;
                    }

                    return true;
                }
            ]
        ]);

        // dump($validator);

        if ($validator->fails()) {
            //
            $response = $validator->errors();
            return response()->json(['message' => $response], 422);
        }

        //save ip info only == an ip can be present in many agents, ensure it's unique
        $range = IpInfo::getRange($ip, $mask);

        $saveIp = [
            'ipnum' => inet_pton($ip),
            'mask' => $mask,
            'start' => inet_pton($range['start']),
            'end' => inet_pton($range['end']),
        ];

        try {
            // DB::enableQueryLog();

            $ipInfo = IpInfo::updateOrCreate(
                [
                    'ipnum' => $saveIp['ipnum'],
                    'mask' => $saveIp['mask']
                ],
                $saveIp
            );

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage()."\n".
                ', trace: '.$e->getTraceAsString()."\n\n"
            );

            return response()->json(['message' => __('[error saving ip]')], 400);
        }

        // dump($ipInfo->toArray());

        $ipId = $ipInfo->id;

        //delete prev agent info, so this new save is the latest
        try {
            // DB::enableQueryLog();

            Agent::
                where('ip_info_id', $ipId)
                ->where('agent', $agent)
                ->where('jail', $jail)
                ->delete();

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage()."\n".
                ', trace: '.$e->getTraceAsString()."\n\n"
            );

            return response()->json(['message' => __('[error deleting old agent info]')], 400);
        }

        //save agent info == link ip to this info:  action taken, date, agent
        try {
            // DB::enableQueryLog();

            $saveAgent = [
                'id' => null,
                'ip_info_id' => $ipId,
                'agent' => $agent,
                'action' => $action,
                'jail' => $jail
            ];

            Agent::create($saveAgent);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage()."\n".
                ', trace: '.$e->getTraceAsString()."\n\n"
            );

            return response()->json(['message' => __('[error saving agent info]')], 400);
        }

        //keep this for debug
        Log::debug(
            __METHOD__.
            " agent=$agent, action=$action, jail=$jail, ip=$ip, mask=$mask"
        );

        return response()->json(['message' => __('[Information saved.]')], 201);
    }
}
