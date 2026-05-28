<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class MacService
{
    /*
    |--------------------------------------------------------------------------
    | NORMALIZE MAC
    |--------------------------------------------------------------------------
    */
    public static function normalize($mac)
    {
        $mac = strtolower(str_replace(['-', ':', '.'], '', $mac));
        return implode(':', str_split($mac, 2));
    }

    /*
    |--------------------------------------------------------------------------
    | GET ACTIVE MAC FROM RADIUS
    |--------------------------------------------------------------------------
    */
    public static function getActiveMac($username)
    {
        $session = DB::table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->first();

        return $session->callingstationid ?? null
            ? self::normalize($session->callingstationid)
            : null;
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK MAC
    |--------------------------------------------------------------------------
    */
    public static function check(Customer $customer, $mac): bool
    {
        $mac = self::normalize($mac);

        if (!$mac) return false;

        // FIRST LOGIN → auto bind
        if (!$customer->mac_address) {
            $customer->update([
                'mac_address' => $mac
            ]);

            return true;
        }

        return self::normalize($customer->mac_address) === $mac;
    }

    /*
    |--------------------------------------------------------------------------
    | FORCE BIND MAC
    |--------------------------------------------------------------------------
    */
    public static function bind(Customer $customer, $mac): bool
    {
        $mac = self::normalize($mac);

        if (!$mac) return false;

        return $customer->update([
            'mac_address' => $mac
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UNBIND MAC
    |--------------------------------------------------------------------------
    */
    public static function unbind(Customer $customer): bool
    {
        return $customer->update([
            'mac_address' => null
        ]);
    }
}
