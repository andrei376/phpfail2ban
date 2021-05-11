<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
