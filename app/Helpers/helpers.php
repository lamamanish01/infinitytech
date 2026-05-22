<?php

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| MAC NORMALIZER
|--------------------------------------------------------------------------
*/
if (!function_exists('normalize_mac')) {

    function normalize_mac($mac)
    {
        return strtoupper(str_replace([':', '-', '.'], '', $mac));
    }
}

/*
|--------------------------------------------------------------------------
| GET ACTIVE SESSION MAC FROM RADIUS
|--------------------------------------------------------------------------
*/
if (!function_exists('get_active_mac')) {

    function get_active_mac($username)
    {
        $session = DB::table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->latest()
            ->first();

        if (!$session || !$session->callingstationid) {
            return null;
        }

        return normalize_mac($session->callingstationid);
    }
}

/*
|--------------------------------------------------------------------------
| CHECK MAC (AUTO BIND FIRST LOGIN)
|--------------------------------------------------------------------------
*/
if (!function_exists('check_mac')) {

    function check_mac(Customer $customer, string $mac): bool
    {
        $mac = normalize_mac($mac);

        // FIRST LOGIN → auto bind MAC
        if (!$customer->mac_address) {

            $customer->update([
                'mac_address' => $mac
            ]);

            return true;
        }

        return $customer->mac_address === $mac;
    }
}

/*
|--------------------------------------------------------------------------
| IS MAC MATCH
|--------------------------------------------------------------------------
*/
if (!function_exists('is_mac_match')) {

    function is_mac_match($dbMac, $clientMac): bool
    {
        if (!$dbMac || !$clientMac) {
            return false;
        }

        return normalize_mac($dbMac) === normalize_mac($clientMac);
    }
}

/*
|--------------------------------------------------------------------------
| FORCE BIND MAC
|--------------------------------------------------------------------------
*/
if (!function_exists('force_bind_mac')) {

    function force_bind_mac(Customer $customer, $mac)
    {
        $customer->update([
            'mac_address' => normalize_mac($mac)
        ]);

        return true;
    }
}

/*
|--------------------------------------------------------------------------
| UNBIND MAC
|--------------------------------------------------------------------------
*/
if (!function_exists('unbind_mac')) {

    function unbind_mac(Customer $customer)
    {
        $customer->update([
            'mac_address' => null
        ]);

        return true;
    }
}
