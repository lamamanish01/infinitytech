<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /* ---------------------------------
     | SYNC CUSTOMER
     * ---------------------------------*/
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
            'username'  => $customer->username,
            'attribute' => 'Cleartext-Password',
            'op'        => ':=',
            'value'     => $customer->password,
        ]);

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op'        => ':=',
            'value'     => $plan->rate_limit,
        ]);

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Session-Timeout',
            'op'        => ':=',
            'value'     => $plan->session_time ?? 7200,
        ]);

        DB::table('radreply')->insert([
            'username'  => $customer->username,
            'attribute' => 'Idle-Timeout',
            'op'        => ':=',
            'value'     => $plan->idle_time ?? 300,
        ]);
    }

    /* ---------------------------------
     | REMOVE CUSTOMER
     * ---------------------------------*/
    public static function removeCustomer($customer)
    {
        self::forceDisconnect($customer);

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
    public static function disconnect($customer)
    {
        try {

            $updated = DB::table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Admin-Disconnect'
                ]);

            if ($updated === 0) {
                return [
                    'status' => false,
                    'message' => 'User is not currently online'
                ];
            }

            return [
                'status' => true,
                'message' => 'User disconnected successfully'
            ];

        } catch (\Exception $e) {

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* ---------------------------------
     | FORCE DISCONNECT (REAL CoA)
     * ---------------------------------*/
    public static function forceDisconnect($customer)
    {
        try {

            $sessions = DB::table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->get();

            if ($sessions->isEmpty()) {
                return [
                    'status' => false,
                    'message' => 'No active session found'
                ];
            }

            foreach ($sessions as $session) {

                self::sendCoA(
                    $session->nasipaddress,
                    $customer->username,
                    $session->acctsessionid
                );

                DB::table('radacct')
                    ->where('radacctid', $session->radacctid)
                    ->update([
                        'acctstoptime' => now(),
                        'acctterminatecause' => 'Force-Disconnect'
                    ]);
            }

            return [
                'status' => true,
                'message' => 'User force disconnected successfully'
            ];

        } catch (\Exception $e) {

            Log::error('ForceDisconnect Error', [
                'user' => $customer->username ?? null,
                'error' => $e->getMessage()
            ]);

            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /* ---------------------------------
     | CoA SENDER (FIXED)
     * ---------------------------------*/
    private static function sendCoA($nasIp, $username, $sessionId)
    {
        $nas = DB::table('nas')
            ->where('nasname', $nasIp)
            ->first();

        if (!$nas)
            return false;

        $packet = "User-Name = \"{$username}\"\nAcct-Session-Id = \"{$sessionId}\"";

        $cmd = "echo " . escapeshellarg($packet) .
            " | radclient -x {$nasIp}:{$nas->ports} disconnect {$nas->secret}";

        exec($cmd, $output, $status);

        Log::info("CoA Sent", [
            'nas' => $nasIp,
            'user' => $username,
            'status' => $status,
            'output' => $output
        ]);

        return $status === 0;
    }
}
