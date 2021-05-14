<?php

namespace App\Helpers;

use App\Models\IpInfo;
use App\Models\Whois;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhoisLib
{
    public function arraySize($a)
    {
        $size = 0;
        if (is_array($a)) {
            foreach ($a as $v) {
                $size += is_array($v) ? $this->arraySize($v) : strlen($v);
            }
        } elseif (is_string($a)) {
            $size += strlen($a);
        }

        return $size;
    }

    public function searchCache($ip, $force = false): array
    {
        $cacheKey = $ip;

        //check cache
        if ($force == false && $value = Cache::get($cacheKey)) {
            //dump($value);
            $value['output'] = json_decode($value['output']);

            //dump($value['output']);

            $value['output']->source = 'FROM CACHE';

            return $value;
        } elseif ($force == false && $dbInfo = Whois::isInDb($ip)) {
            $dbInfo = $dbInfo->toArray();

            $dbInfo['output'] = json_decode($dbInfo['output']);

            $dbInfo['output']->source = 'FROM DB';

            $saveData = $dbInfo;
            $saveData['output'] = json_encode($saveData['output']);

            $this->saveCache($cacheKey, $saveData);

            return $dbInfo;
        } else {
            //get fresh data
            $data = $this->search($ip);
        }

        //save to db
        try {
            //
            $db['id'] = '';
            $db['date'] = $data['date'];

            $db['ipnum'] = $data['ipnum'];
            $db['mask'] = $data['mask'];

            $db['start'] = $data['start'];
            $db['end'] = $data['end'];


            $db['inetnum'] = $data['inetnum'];
            $db['range'] = $data['range'];
            $db['netname'] = $data['netname'];
            $db['country'] = $data['country'];
            $db['orgname'] = $data['orgname'];
            $db['output'] = $data['output'];

            // DB::enableQueryLog();

            Whois::updateOrCreate(
                [
                    'ipnum' => $db['ipnum'],
                    'mask' => $db['mask']
                ],
                $db
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
                ' error saving whois in db: '.$e->getMessage().
                ", line=".$e->getLine().
                "\n data=".
                print_r($db, true).
                "\n"
            );
        }

        $data['ipnum'] = inet_ntop($data['ipnum']);


        $data['start'] = inet_ntop($data['start']);
        $data['end'] = inet_ntop($data['end']);

        $this->saveCache($cacheKey, $data);

        if (isset($data['output'])) {
            $data['output'] = json_decode($data['output']);
        }
        return $data;
    }

    public function saveCache($cacheKey, $data): bool
    {
        //save to cache

        $size = $this->arraySize($data);

        if ($size < 1000 * 1000) {
            Cache::put($cacheKey, $data, now()->addDay());
        } else {
            Log::debug(
                __METHOD__.
                " error saving to cache (size = $size)\n"
            );

            return false;
        }

        return true;
    }

    public function search($ip): array
    {
        $data = $this->searchArin($ip);

        return $data;
    }

    public function searchArin($ip): array
    {
        //dump('WHOIS SEARCH '.$ip);

        if (stripos($ip, '/') !== false) {
            list($ips, $mask) = explode('/', $ip);
        } else {
            $ips = $ip;
            $mask = 32;

            if (stripos($ip, ':') !== false) {
                // dump('ipv6');
                $mask = 128;
            }
        }

        $test['ip'] = $ips;
        $test['cidr'] = $mask;

        $getRange = IpInfo::getRange($test['ip'], $test['cidr']);

        $emptyResult = [

            'date' => now(),

            'ipnum' => inet_pton($test['ip']),
            'mask' => $test['cidr'],

            'start' => inet_pton($getRange['start']),
            'end' => inet_pton($getRange['end']),

            'inetnum' => '',
            'range' => '',
            'netname' => '',
            'country' => '',
            'orgname' => '',
            'output' => json_encode(['response' => 'empty', 'had_error' => true])
        ];

        $url = 'https://rdap.arin.net/registry/ip/' . $ip;

        $http = Http::withOptions([
            'allow_redirects' => true,
            'verify' => false
        ]);

        $response = $http->get($url);

        if (!$response->successful()) {
            Log::error(__("[ERROR! querying :ip, url: :url, http status: :status, response:\n :resp \nbody: \n :body]", [
                'ip' => $ip,
                'url' => $response->effectiveUri(),
                'status' => $response->status(),
                'resp' => print_r($response->headers(), true),
                'body' => print_r($response->body(), true)
            ]));
            return $emptyResult;
        }

        //Log::debug('status='. $response->status());

        $data = $response->json();

        if ($data['port43'] == 'whois.afrinic.net') {
            //search again directly in afrinic
            $url = 'https://rdap.afrinic.net/rdap/ip/' . $ip;

            $response = $http->get($url);

            $data = $response->json();
        }

        //dump($data);
        //dump('whois data='. print_r($response, true));

        $start = $data['startAddress'] ?? 'start';
        $end = $data['endAddress'] ?? 'end';

        $data['cidr0_cidrs'] = $data['cidr0_cidrs'] ?? [];

        $cidr = [];

        if (isset($data['ipVersion'])) {
            $ipVersion = $data['ipVersion'];

            foreach ($data['cidr0_cidrs'] as $prefix) {
                $cidr[] = $prefix[$ipVersion . 'prefix'] . '/' . $prefix['length'];
            }
        }

        $cidrStr = implode(', ', $cidr);


            //$data['handle'] ?? 'handle';


        $netname = $data['name'] ?? ($data['handle'] ?? 'name');

        $orgname = 'not found';
        $country = $data['country'] ?? '';

        if (isset($data['remarks'][0]['description'][0])) {
            $orgname = $data['remarks'][0]['description'][0];
        }

        foreach ($data['entities'] as $entity) {
            if (isset($entity['vcardArray'])) {
                foreach ($entity['vcardArray'][1] as $row) {
                    if ($row[0] == 'fn') {
                        $orgname = $row[3];
                    }

                    if ($row[0] == 'adr' && !isset($data['country'])) {
                        if (!isset($row[1]['label'])) {
                            $country = array_pop($row[3]);
                        }
                    }
                }
                break;
            }
        }

        $country = empty($country) ? 'US' : strtoupper($country);


        $inetnum = $start.' - '.$end;
        $range = $start.' - '.$end;
        if (stripos($ip, ':') !== false && isset($data['handle'])) {
            $range = $data['handle'];
        }

        if (!empty($cidrStr)) {
            $inetnum = $start.' - '.$end." ($cidrStr)";
        }

        $result = [

            'date' => now(),

            'ipnum' => inet_pton($test['ip']),
            'mask' => $test['cidr'],

            'start' => inet_pton($getRange['start']),
            'end' => inet_pton($getRange['end']),

            'inetnum' => substr($inetnum, 0, 190),
            'range' => $range,
            'netname' => $netname,
            'country' => $country,
            'orgname' => $orgname,
            'output' => json_encode($data)
        ];

        //dump($data);

        //dd();
        return $result;
    }
}
