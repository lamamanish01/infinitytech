<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RadiusService
{
    public static function syncCustomer($customer)
    {
        DB::table('radcheck')
            ->where('username', $customer->username)
            ->delete();

        DB::table('radreply')
            ->where('username', $customer->username)
            ->delete();

        if (!$customer->expire_date || now()->gt($customer->expire_date)) {
            return;
        }

        $plan = $customer->internetPlan;

        if (!$plan) {
            return;
        }

        DB::table('radcheck')->insert([
            'username' => $customer->username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $customer->password,
        ]);

        DB::table('radreply')->insert([
            'username' => $customer->username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $plan->rate_limit,
        ]);
    }

    public static function removeCustomer($customer)
    {
        DB::table('radcheck')
            ->where('username', $customer->username)
            ->delete();

        DB::table('radreply')
            ->where('username', $customer->username)
            ->delete();
        DB::table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime' => now()
            ]);
    }
}
