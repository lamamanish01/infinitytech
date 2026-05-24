<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RadiusService
{
    /* ---------------------------------
     | SYNC CUSTOMER
     * ---------------------------------*/
    public static function syncCustomer($customer)
    {
        $plan = $customer->internetPlan;

        if (!$plan) return;

        // 🔥 Clean previous config
        DB::table('radcheck')->where('username', $customer->username)->delete();
        DB::table('radreply')->where('username', $customer->username)->delete();

        /*
        |--------------------------------------------------------------------------
        | BASIC AUTH
        |--------------------------------------------------------------------------
        */

        DB::table('radcheck')->insert([
            'username'  => $customer->username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $customer->password,
        ]);

        DB::table('radcheck')->insert([
            'username'  => $customer->username,
            'attribute' => 'Simultaneous-Use',
            'op'        => ':=',
            'value'     => 1,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REQUIRED FOR PPPoE (VERY IMPORTANT)
        |--------------------------------------------------------------------------
        */

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Service-Type',
            'op'        => ':=',
            'value'     => 'Framed-User',
        ]);

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Framed-Protocol',
            'op'        => ':=',
            'value'     => 'PPP',
        ]);

        /*
        |--------------------------------------------------------------------------
        | IP ASSIGNMENT (THIS FIXES YOUR ISSUE)
        |--------------------------------------------------------------------------
        */

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Framed-Pool',
            'op'        => ':=',
            'value'     => 'PPPoE-Pool',
        ]);

        /*
        |--------------------------------------------------------------------------
        | EXPIRATION
        |--------------------------------------------------------------------------
        */

        if ($customer->expire_date) {
            DB::table('radcheck')->insert([
                'username'  => $customer->username,
                'attribute' => 'Expiration',
                'op'        => ':=',
                'value'     => \Carbon\Carbon::parse($customer->expire_date)
                    ->format('d M Y H:i:s'),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | RATE LIMIT (ACTIVE + GRACE)
        |--------------------------------------------------------------------------
        */

        if (in_array($customer->status, ['active', 'grace'])) {
            DB::table('radreply')->insert([
                'username'  => $customer->username,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op'        => ':=',
                'value'     => $plan->rate_limit,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | MAC BINDING (SAFE UPSERT)
        |--------------------------------------------------------------------------
        */

        if ($customer->mac_address) {
            DB::table('radcheck')->updateOrInsert(
                [
                    'username'  => $customer->username,
                    'attribute' => 'Calling-Station-Id',
                ],
                [
                    'op'    => ':=',
                    'value' => strtoupper($customer->mac_address),
                ]
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BLOCKED USERS
        |--------------------------------------------------------------------------
        */

        if (in_array($customer->status, ['expired', 'suspended', 'discontinued'])) {
            DB::table('radreply')->insert([
                'username'  => $customer->username,
                'attribute' => 'Session-Timeout',
                'op'        => ':=',
                'value'     => 60,
            ]);
        }
    }

    /* ---------------------------------
     | REMOVE CUSTOMER
     * ---------------------------------*/
    public static function removeCustomer($customer)
    {
        // self::forceDisconnect($customer);

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
                'acctstoptime' => now(),
                'acctterminatecause' => 'Admin-Remove'
            ]);
    }

    /* ---------------------------------
     | SIMPLE DISCONNECT (DB ONLY)
     * ---------------------------------*/
    // public static function disconnect($customer)
    // {
    //     try {

    //         $updated = DB::table('radacct')
    //             ->where('username', $customer->username)
    //             ->whereNull('acctstoptime')
    //             ->update([
    //                 'acctstoptime' => now(),
    //                 'acctterminatecause' => 'Admin-Disconnect'
    //             ]);

    //         if ($updated === 0) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'User is not currently online'
    //             ];
    //         }

    //         return [
    //             'status' => true,
    //             'message' => 'User disconnected successfully'
    //         ];

    //     } catch (\Exception $e) {

    //         return [
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    // /* ---------------------------------
    //  | FORCE DISCONNECT (REAL CoA)
    //  * ---------------------------------*/
    // public static function forceDisconnect($customer)
    // {
    //     try {

    //         $sessions = DB::table('radacct')
    //             ->where('username', $customer->username)
    //             ->whereNull('acctstoptime')
    //             ->get();

    //         if ($sessions->isEmpty()) {
    //             return [
    //                 'status' => false,
    //                 'message' => 'No active session found'
    //             ];
    //         }

    //         foreach ($sessions as $session) {

    //             self::sendCoA(
    //                 $session->nasipaddress,
    //                 $customer->username,
    //                 $session->acctsessionid
    //             );

    //             DB::table('radacct')
    //                 ->where('radacctid', $session->radacctid)
    //                 ->update([
    //                     'acctstoptime' => now(),
    //                     'acctterminatecause' => 'Force-Disconnect'
    //                 ]);
    //         }

    //         return [
    //             'status' => true,
    //             'message' => 'User force disconnected successfully'
    //         ];

    //     } catch (\Exception $e) {

    //         Log::error('ForceDisconnect Error', [
    //             'user' => $customer->username ?? null,
    //             'error' => $e->getMessage()
    //         ]);

    //         return [
    //             'status' => false,
    //             'message' => $e->getMessage()
    //         ];
    //     }
    // }

    // /* ---------------------------------
    //  | CoA SENDER (FIXED)
    //  * ---------------------------------*/
    // private static function sendCoA($nasIp, $username, $sessionId)
    // {
    //     $nas = DB::table('nas')
    //         ->where('nasname', $nasIp)
    //         ->first();

    //     if (!$nas)
    //         return false;

    //     $packet = "User-Name = \"{$username}\"\nAcct-Session-Id = \"{$sessionId}\"";

    //     $cmd = "echo " . escapeshellarg($packet) .
    //         " | radclient -x {$nasIp}:{$nas->ports} disconnect {$nas->secret}";

    //     exec($cmd, $output, $status);

    //     Log::info("CoA Sent", [
    //         'nas' => $nasIp,
    //         'user' => $username,
    //         'status' => $status,
    //         'output' => $output
    //     ]);

    //     return $status === 0;
    // }
}
