<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class MacService
{
    public static function normalize($mac)
    {
        if (!$mac) return null;

        $mac = strtolower(str_replace(['-', ':', '.'], '', trim($mac)));

        if (strlen($mac) !== 12) return null;

        return implode(':', str_split($mac, 2));
    }

    public static function getActiveMac($username)
    {
        $session = DB::table('radacct')
            ->where('username', $username)
            ->whereNull('acctstoptime')
            ->latest('radacctid')
            ->first();

        if (!$session || empty($session->callingstationid)) {
            return null;
        }

        return self::normalize($session->callingstationid);
    }

    public static function check(Customer $customer, $mac): bool
    {
        $mac = self::normalize($mac);

        if (!$mac) return false;

        if (!$customer->mac_address) {
            return true;
        }

        return self::normalize($customer->mac_address) === $mac;
    }

    public static function bind(Customer $customer, $mac): bool
    {
        $mac = self::normalize($mac);

        if (!$mac) return false;

        return $customer->update([
            'mac_address' => $mac
        ]);
    }

    public static function unbind(Customer $customer): bool
    {
        return $customer->update([
            'mac_address' => null
        ]);
    }
}
