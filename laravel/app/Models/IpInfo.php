<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
