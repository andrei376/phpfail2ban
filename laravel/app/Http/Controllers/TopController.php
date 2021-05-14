<?php

namespace App\Http\Controllers;

use App\Models\IpInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class TopController extends Controller
{
    public function check(): RedirectResponse
    {
        //find first checked=0 and redirect to show
        $ip = IpInfo::
        where('checked', 0)
            ->orderBy('mask', 'asc')
            ->orderBy('last_check', 'asc')
            ->orderBy('created_at', 'asc')
            ->first();

        //dump($ip);

        if (empty($ip)) {
            return Redirect::back()->with('msg.error', __('Nothing to show.'));
        }

        $id = $ip->id;

        //redirect to show
        return redirect()->route('top.show', [
            'id' => $id
        ]);
    }

    public function index(): Response
    {
        return Inertia::render('Top/Index', [
            //
        ]);
    }

    public function browse(): Response
    {
        return Inertia::render('Top/Browse', [
            //
        ]);
    }

    public function show(): Response
    {
        return Inertia::render('Top/Show', [
            //
        ]);
    }
}
