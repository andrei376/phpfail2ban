<?php

namespace App\Http\Controllers;

use App\Helpers\WhoisLib;
use App\Http\Resources\LogsResource;
use App\Models\Agent;
use App\Models\IpInfo;
use App\Models\RblLog;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Schema;
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

    public function browse(Request $request)
    {
        if ($request->isMethod('post')) {
            $model = new IpInfo();

            $searchField = 'id';
            $searchValue = '';
            foreach ($request->input('search') as $field => $value) {
                //
                if (!empty($value)) {
                    switch ($field) {
                        case 'ip':
                            $searchField = DB::raw('INET6_NTOA(`ipnum`)');
                            break;

                        case 'created_at':
                            $searchField = DB::raw('DATE_FORMAT(`created_at`, "%d %M %Y, %H:%i:%s")');
                            break;

                        case 'last_check':
                            $searchField = DB::raw('DATE_FORMAT(`last_check`, "%d %M %Y, %H:%i:%s")');
                            break;

                        default:
                            $searchField = $field;
                            break;
                    }
                    $searchValue = $value;
                }
            }

            //dump($searchField);
            // dump($searchValue);

            // dump($request->all());

            // DB::enableQueryLog();

            $data = $model
                ->orderBy($request->column, $request->order)
                ->orderBy('created_at', 'desc')
                ->where($searchField, 'like', '%'.$searchValue.'%')
                ->paginate($request->perPage);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            foreach ($data as $id => $row) {
                $data[$id]['ipnum'] = inet_ntop($row->ipnum);
                $data[$id]['start'] = inet_ntop($row->start);
                $data[$id]['end'] = inet_ntop($row->end);
            }

            return LogsResource::collection($data);
        }

        return Inertia::render('Top/Browse', [
            //
        ]);
    }

    public function show(Request $request, int $id)
    {
        $whoisLib = new WhoisLib();

        //find db row
        $ipInfo = IpInfo::find($id);

        if (empty($ipInfo)) {
            return Redirect::route('stats.index')->with('msg.error', __('Nothing to show.'));
        }

        $ipAddr = $ipInfo->format_ip;

        $hostnameInfo = IpInfo::hostnameRange($ipAddr, $ipInfo->mask);

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

        if ($request->isMethod('post') && $request->forceWhois == 1) {
            $whoisData = $whoisLib->searchCache($cidrInfo, true);
        } else {
            $whoisData = $whoisLib->searchCache($cidrInfo, false);
        }
        $whoisData['date'] = date('d F Y, H:i:s',strtotime($whoisData['date']));



        if ($request->isMethod('post') && $request->updateWhois == 1) {
            // DB::enableQueryLog();

            $ipInfo->update([
                'inetnum' => $whoisData['inetnum'],
                'netname' => $whoisData['netname'],
                'country' => $whoisData['country'],
                'orgname' => $whoisData['orgname'],
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            $ipInfo->ipnum = inet_ntop($ipInfo->ipnum);
            $ipInfo->start = inet_ntop($ipInfo->start);
            $ipInfo->end = inet_ntop($ipInfo->end);

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->updateLastCheck == 1) {
            // DB::enableQueryLog();

            $ipInfo->update([
                'last_check' => now(),
            ]);

            /*Log::debug(
                __METHOD__.
                " query: \n".
                print_r(DB::getQueryLog(), true).
                "\n"
            );*/

            $ipInfo->ipnum = inet_ntop($ipInfo->ipnum);
            $ipInfo->start = inet_ntop($ipInfo->start);
            $ipInfo->end = inet_ntop($ipInfo->end);

            return response()->json($ipInfo);
        }

        if ($request->isMethod('post') && $request->forceWhois == 1) {
            return response()->json($whoisData);
        }

        // dump($whoisData);

        $geoCountry = geoip_country_name_by_name($ipAddr);

        $ipInfo->ipnum = inet_ntop($ipInfo->ipnum);
        $ipInfo->start = inet_ntop($ipInfo->start);
        $ipInfo->end = inet_ntop($ipInfo->end);

        $rangeInfo = $ipInfo->start.' - '.$ipInfo->end;

        $ipv6 = false;
        if (stripos($ipInfo->ipnum, ':') !== false) {
            $ipv6 = true;
        }

        // dump($ipInfo->toArray());
        // dump($hostnameInfo);
        // dump($cidrInfo);
        // dump($geoCountry);
        // dump($whoisData);
        // dump($rangeInfo);

        // DB::enableQueryLog();
        $multiple = IpInfo::isMultiple($ipAddr, $ipInfo->mask, $id);
        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        // dump($multiple);


        //dynamic search, mask-8 and mask-16
        // $other24 = $c4->searchOthers($list, $ipInfo);
        $classDiff = 8;

        if ($ipv6) {
            $classDiff = 16;
        }

        $searchMask1 = $ipInfo->mask;
        if ($ipInfo->mask > $classDiff) {
            $searchMask1 = $ipInfo->mask - $classDiff;
        }
        $other24 = IpInfo::searchOthers($id, $ipAddr, $searchMask1);

        // $other16 = $c4->searchOthers($list, $ipInfo, 16);
        //$searchMask2 = $ipInfo->mask;
        if ($ipInfo->mask > ($classDiff*2)) {
            $searchMask2 = $ipInfo->mask - ($classDiff*2);
        } else {
            $searchMask2 = $searchMask1;
        }
        $other16 = IpInfo::searchOthers($id, $ipAddr, $searchMask2);

        return Inertia::render('Top/Show', [
            'ipv6' => $ipv6,
            'ipInfo' => $ipInfo,
            'hostnameInfo' => $hostnameInfo,
            'cidrInfo' => $cidrInfo,
            'geoCountry' => $geoCountry,
            'whoisData' => $whoisData,
            'rangeInfo' => $rangeInfo,
            'multiple' => $multiple,
            'other24' => $other24,
            'other16' => $other16,
            'searchMask1' => $searchMask1,
            'searchMask2' => $searchMask2
        ]);
    }

    public function toggle(Request $request, int $id, string $field, string $reason = null)
    {
        //find db row
        $model = new IpInfo();

        $ipInfo = $model->find($id);

        // dump($ipInfo->toArray());

        if (empty($ipInfo)) {
            return Redirect::back()->with('msg.error', __('[Nothing to show.]'));
        }

        //check column
        if (!Schema::hasColumn($model->getTable(), $field)) {
            return Redirect::back()->with('msg.error', __('[Invalid field.]'));
        }

        $ipAddr = $ipInfo->format_ip;

        $cidrInfo = $ipAddr.'/'.$ipInfo->mask;

        if ($request->isMethod('post')) {
            //dump($ipInfo);
            // dump($ipInfo->$field);
            // dump($request->input());

            try {
                // DB::enableQueryLog();

                $url = route('top.show', [
                    'id' => $id
                ]);

                $message = '<a href="'.$url.'">'.$cidrInfo.'</a>';
                $message .='<br>';

                $message .= __('[toggle :field from :from to :to]', [
                    'field' => $field,
                    'from' => $ipInfo->$field ? 'true' : 'false',
                    'to' => !$ipInfo->$field ? 'true' : 'false'
                ]);

                $message .='<br><br>';

                $message .= __('[Reason]').': ';
                $message .= '<br>';
                $message .= nl2br($request->input('reason'));


                if (!empty($request->input('reason'))) {
                    RblLog::saveLog($request->user()->name, __('['.$field.']'), $message);
                }

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error saving: '.$e->getMessage().
                    ", line=".$e->getLine().
                    "\n"
                );

                return response()->json(['error' => __('Error saving to log.')], 400);
            }

            $ipInfo->$field = !$ipInfo->$field;

            try {
                // DB::enableQueryLog();
                $ipInfo->save();

                /*Log::debug(
                    __METHOD__.
                    " query: \n".
                    print_r(DB::getQueryLog(), true).
                    "\n"
                );*/
            } catch (Exception $e) {
                Log::error(
                    __METHOD__.
                    ' error updating ip in db: '.$e->getMessage().
                    ", line=".$e->getLine().
                    ", field=$field, ".
                    "\n data=".
                    print_r($ipInfo, true).
                    "\n"
                );

                return response()->json(['error' => __('Error updating field :field.', ['field' => $field])], 400);
            }

            return Redirect::route('top.show', [
                'id' => $id,
            ])->with('msg.success', __('Information saved.'));
        }

        return Inertia::render('Top/Toggle', [
            'id' => $id,
            'field' => $field,
            'cidrInfo' => $cidrInfo,
            'reason' => $reason
        ]);
    }

    public function ipLog(Request $request): AnonymousResourceCollection
    {
        DB::enableQueryLog();

        $searchIp = $request->searchIp;

        /*if (stripos($request->searchIp, ':') !== false) {
            $searchIp = $request->searchIp;
            $g = explode(':', $searchIp);

            // dump($g);

            $search = '';

            foreach ($g as $item) {
                if (trim($item) != '') {
                    $search .= $item.':';
                }
            }
            $searchIp = $search;
        } else {
            $searchIp = long2ip($request->searchIp);
        }*/


        $data = RblLog::
        where('type', '!=', __('[readlog]'))
            ->where('message', 'like', '%'.$searchIp.'%')
            ->orderBy('date', 'desc')
            ->paginate($request->perPage);

        /*Log::debug(
            __METHOD__.
            " query: \n".
            print_r(DB::getQueryLog(), true).
            "\n"
        );*/

        return LogsResource::collection($data);
    }

    public function history(Request $request, int $id): AnonymousResourceCollection
    {
        // dump($id);

        $table = [];

        try {
            // DB::enableQueryLog();

            $data = Agent::
                select(['created_at', 'agent', 'action', 'jail'])
                ->where('ip_info_id', $id)
                ->withTrashed()
                ->orderBy($request->column ?? 'created_at', $request->order ?? 'desc')
                ->paginate(intval($request->perPage));

            /*Log::debug(
                __METHOD__ .
                " query: \n" .
                print_r(DB::getQueryLog(), true) .
                "\n"
            );*/

            $table = LogsResource::collection($data);

            /*$table->additional = [
                'rangeInfo' => $rangeInfo,
            ];*/
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                ' error: '.$e->getMessage().
                "\n".$e->getTraceAsString().
                "\n\n"
            );
        }


        // dump($table);

        return $table;
    }
}