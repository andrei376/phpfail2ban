<?php

namespace App\Models;

use App\Helpers\WhoisLib;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * @mixin IdeHelperIpInfo
 */
class IpInfo extends Model
{
    use HasFactory;

    protected $fillable = [

        'ipnum',
        'mask',

        'start',
        'end',

        'inetnum',
        'netname',
        'country',
        'orgname',
        'geoipcountry',

        'last_check',

        'checked'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'last_check' => 'datetime',
        'checked' => 'boolean',
    ];

    protected $appends = [
        'created_at_format',
        'created_at_ago',
        'created_at_dt',
        'last_check_format',
        'last_check_ago',
        'last_check_dt',
        'format_ip',
        'format_cidr',
        'range',
        'total_ip_format',
        'total_db_ip_format',
        'actions_count_format',
        'row_count_format'
    ];

    public function actions(): HasMany
    {
        return $this->hasMany(Agent::class)->withTrashed();
    }

    public static function getRange($ip, $mask): array
    {
        if (stripos($ip, ':') !== false) {
            //ipv6
            return self::getRange6($ip, $mask);
        } else {
            return self::getRange4($ip, $mask);
        }
    }

    public static function getRange4($ip, $mask): array
    {
        $ip = ip2long($ip);
        $nm = 0xffffffff << (32 - $mask);
        $nw = ($ip & $nm);
        $bc = $nw | (~$nm);

        return array('start' => long2ip($nw), 'end' => long2ip($bc));
    }

    public static function getRange6($ip, $mask): array
    {
        // Parse the address into a binary string
        $addr_given_bin = inet_pton($ip);

        // Convert the binary string to a string with hexadecimal characters
        $addr_given_hex = bin2hex($addr_given_bin);

        // Calculate the number of 'flexible' bits
        $flexbits = 128 - $mask;

        // Build the hexadecimal strings of the first and last addresses
        $addr_hex_first = $addr_given_hex;
        $addr_hex_last = $addr_given_hex;

        // We start at the end of the string (which is always 32 characters long)
        $pos = 31;
        while ($flexbits > 0) {
            // Get the characters at this position
            $orig_first = substr($addr_hex_first, $pos, 1);
            $orig_last = substr($addr_hex_last, $pos, 1);

            // Convert them to an integer
            $origval_first = hexdec($orig_first);
            $origval_last = hexdec($orig_last);

            // First address: calculate the subnet mask. min() prevents the comparison from being negative
            $mask = 0xf << (min(4, $flexbits));

            // AND the original against its mask
            $new_val_first = $origval_first & $mask;

            // Last address: OR it with (2^flexbits)-1, with flexbits limited to 4 at a time
            $new_val_last = $origval_last | (pow(2, min(4, $flexbits)) - 1);

            // Convert them back to hexadecimal characters
            $new_first = dechex($new_val_first);
            $new_last = dechex($new_val_last);

            // And put those character back in their strings
            $addr_hex_first = substr_replace($addr_hex_first, $new_first, $pos, 1);
            $addr_hex_last = substr_replace($addr_hex_last, $new_last, $pos, 1);

            // We processed one nibble, move to previous position
            $flexbits -= 4;
            $pos -= 1;
        }

        // Convert the hexadecimal strings to a binary string
        $addr_bin_first = hex2bin($addr_hex_first);
        $addr_bin_last = hex2bin($addr_hex_last);

        // And create an IPv6 address from the binary string
        $addr_str_first = inet_ntop($addr_bin_first);
        $addr_str_last = inet_ntop($addr_bin_last);

        $addr_str_first = self::expandIPv6($addr_str_first);
        $addr_str_last = self::expandIPv6($addr_str_last);

        return array('start' => $addr_str_first, 'end' => $addr_str_last);
    }

    public static function expandIPv6($ip): string
    {
        $hex = bin2hex(inet_pton($ip));
        return implode(':', str_split($hex, 4));
    }

    public static function checkCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
            where('checked', 0)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }

    public static function netnameCount(): int
    {
        $data = 0;
        try {
//            DB::enableQueryLog();
            $data = self::
            where('netname', null)
                ->count();
//            dump(DB::getQueryLog());
        } catch (Exception $e) {
            Log::error(
                __METHOD__.
                " error: ".$e->getMessage().
                "\n"
            );
        }

//        dump($data);

        return $data ?? 0;
    }

    public static function hostnameRange($ip, $mask): string
    {
        //$cidr = $ip.'/'.$mask;

        $result = '';

        if ($range = self::getRange($ip, $mask)) {
            $hostname = [
                'low' => '',
                'high' => ''
            ];

            if ($host = self::getHostByIp($range['start'])) {
                $hostname['start'] = $host;
            }

            if ($host = self::getHostByIp($range['end'])) {
                $hostname['end'] = $host;
            }

            $result = ': '.$range['start'].'->'.$range['end'].' ('.$hostname['start'].'->'.$hostname['end'].')';
        }

        return $result;
    }

    public static function getHostByIp($ip)
    {
        $cacheKey = $ip.'gethostname';

        //check cache
        if ($value = Cache::get($cacheKey)) {
            return $value;
        } else {
            $whois = gethostbyaddr($ip);

            $whoisLib = new WhoisLib();

            $size = $whoisLib->arraySize($whois);

            if ($size < 1000 * 1000) {
                Cache::put($cacheKey, $whois, now()->addDay());
            } else {
                Log::debug(
                    __METHOD__.
                    " error saving to cache (size = $size)\n"
                );
            }
            return $whois;
        }
    }

    public function getFormatIpAttribute()
    {
        return inet_ntop($this->getRawOriginal('ipnum'));
    }

    public function getFormatCidrAttribute(): string
    {
        return inet_ntop($this->getRawOriginal('ipnum')).'/'.$this->mask;
    }

    public function getCreatedAtFormatAttribute(): ?string
    {
        if (is_null($this->created_at)) {
            return null;
        }
        return $this->created_at->format('d F Y, H:i:s');
    }

    public function getCreatedAtAgoAttribute(): ?string
    {
        if (is_null($this->created_at)) {
            return null;
        }
        return $this->created_at->diffForHumans();
    }

    public function getCreatedAtDtAttribute(): string
    {
        return $this->getCreatedAtFormatAttribute().'<br>'.$this->getCreatedAtAgoAttribute();
    }

    public function getLastCheckFormatAttribute(): string
    {
        if (is_null($this->last_check)) {
            return 'never';
        }

        return $this->last_check->format('d F Y, H:i:s');
    }

    public function getLastCheckAgoAttribute(): ?string
    {
        if (is_null($this->last_check)) {
            return null;
        }

        return $this->last_check->diffForHumans();
    }

    public function getLastCheckDtAttribute(): string
    {
        return $this->getLastCheckFormatAttribute().'<br>'.$this->getLastCheckAgoAttribute();
    }

    public function getRangeAttribute(): string
    {
        return inet_ntop($this->getRawOriginal('start')).' -> '.inet_ntop($this->getRawOriginal('end'));
    }

    public function getTotalDbIpFormatAttribute(): ?string
    {
        if (is_null($this->total_ip)) {
            return null;
        }

        return number_format ($this->total_ip ,  0 ,  "," ,  "." );
    }
    public function getTotalIpFormatAttribute(): ?string
    {
        if (is_null($this->format_ip)) {
            return null;
        }

        $mask = 32;

        if (stripos($this->format_ip, ':') !== false) {
            $mask = 128;
        }

        $total = pow(2, ($mask - $this->mask));

        return number_format ($total ,  0 ,  "," ,  "." );
    }

    public function getActionsCountFormatAttribute(): ?string
    {
        if (is_null($this->actions_count)) {
            return null;
        }

        return number_format ($this->actions_count,  0 ,  "," ,  "." );
    }

    public function getRowCountFormatAttribute(): ?string
    {
        if (is_null($this->row_count)) {
            return null;
        }

        return number_format ($this->row_count ,  0 ,  "," ,  "." );
    }

    public static function isMultiple($ip, $prefix, $searchId): array
    {
        $findRange = self::getRange($ip, $prefix);

        $bc = inet_pton($findRange['end']);

        //dump("search $ip/$prefix");


        $data = self::
            where([
                ['start', '<=', inet_pton($ip)],
                ['end', '>=', $bc]
            ])
            ->orWhere([
                ['start', '>=', inet_pton($ip)],
                ['end', '<=', $bc]
            ])
            ->orderBy('start', 'asc')
            ->orderBy('created_at', 'asc')
            ->get()->toArray();

        // dump($data);

        $resultData = [];

        foreach ($data as $row) {
            if ($searchId !=  $row['id']) {
                $range = self::getRange(inet_ntop($row['ipnum']), $row['mask']);

                $rangeStr = $range['start'].' -> '.$range['end'];

                $resultData[] = [
                    'ip' => '<a href="'.
                        URL::route('top.show', ['id' => $row['id']]).
                        '">'.
                        inet_ntop($row['ipnum']).'/'.$row['mask'].'</a>'.'<br>'.
                        $rangeStr,
                    'inetnum' => $row['inetnum'],
                    'netname' => $row['netname'],
                    'orgname' => $row['orgname'],
                    'country' => $row['country'],
                    'checked' => $row['checked']
                ];
            }
        }

        return $resultData;
    }

    public static function searchOthers($searchId, $searchIp, $cidr = null): array
    {
        // dump($searchIp);
        // dump($cidr);

        $searchMask = $cidr;

        $findRange = self::getRange($searchIp, $searchMask);

        // dump($findRange);

        $start = inet_pton($findRange['start']);
        $end = inet_pton($findRange['end']);


        $rows = self::
            where([
                ['ipnum', '>=', $start],
                ['ipnum', '<=', $end]
            ])
            ->orderBy('ipnum', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        if (empty($rows)) {
            return [];
        }

        $resultData = [];

        foreach ($rows as $row) {
            $range = self::getRange(inet_ntop($row['ipnum']), $row['mask']);

            $rangeStr = $range['start'].' -> '.$range['end'];

            $resultData[] = [
                'ip' => ($row['id'] == $searchId ? '<span class="badge bg-success mr-1" style="min-width: 2rem;">'.__('self').'</span>' : '').
                    '<a href="'.
                    URL::route('top.show', ['id' => $row['id']]).
                    '">'.
                    inet_ntop($row['ipnum']).'/'.$row['mask'].'</a>'.'<br>'.
                    $rangeStr,
                'inetnum' => $row['inetnum'],
                'netname' => $row['netname'],
                'orgname' => $row['orgname'],
                'country' => $row['country'],
                'checked' => $row['checked']
            ];
        }

        return $resultData;
    }
}
