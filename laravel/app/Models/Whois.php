<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin IdeHelperWhois
 */
class Whois extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',

        'ipnum',
        'mask',

        'start',
        'end',

        'range',

        'inetnum',
        'netname',
        'country',
        'orgname',

        'output'
    ];

    protected $casts = [
        'date' => 'datetime'
    ];

    public static function isInDb($cidr)
    {
        list($ip, $prefix) = explode('/', $cidr);

        //

        // dump(__METHOD__.' '.__LINE__.' ');
        // dump("testing $ip/$prefix");

        $range = IpInfo::getRange($ip, $prefix);

        $end = inet_pton($range['end']);

        if ($ret = self::where([['ipnum', inet_pton($ip)], ['mask', $prefix]])->first()) {
            return $ret;
        } elseif($ret = self::where('start', '<=', inet_pton($ip))->where('end', '>=', $end)->first()) {
            // dump('in range');

            //dump($ret);
            return $ret;
        } else {
            return false;
        }
    }

    public function getIpnumAttribute($value)
    {
        return inet_ntop($value);
    }

    public function getStartAttribute($value)
    {
        return inet_ntop($value);
    }

    public function getEndAttribute($value)
    {
        return inet_ntop($value);
    }
}
