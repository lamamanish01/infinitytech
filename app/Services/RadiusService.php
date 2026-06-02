<?php

namespace App\Services;

use App\Services\MikrotikService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RadiusService
{
    public static function syncCustomer($customer)
    {
        $plan = $customer->internetPlan;

        if (!$plan) return;

        DB::table('radcheck')->updateOrInsert(
            [
                'username'  => $customer->username,
                'attribute' => 'Cleartext-Password',
            ],
            [
                'op'    => ':=',
                'value' => $customer->password,
            ]
        );

        DB::table('radreply')->updateOrInsert(
            [
                'username'  => $customer->username,
                'attribute' => 'Mikrotik-Rate-Limit',
            ],
            [
                'op'    => ':=',
                'value' => $plan->rate_limit,
            ]
        );

        DB::table('radreply')->updateOrInsert(
            [
                'username'  => $customer->username,
                'attribute' => 'Framed-Pool',
            ],
            [
                'op'    => ':=',
                'value' => 'PPPoE-Pool',
            ]
        );

        if (!empty($customer->mac_address)) {

            DB::table('radreply')->updateOrInsert(
                [
                    'username'  => $customer->username,
                    'attribute' => 'Calling-Station-Id',
                ],
                [
                    'op'    => '==',
                    'value' => strtolower($customer->mac_address),
                ]
            );
        }
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
                'acctstoptime' => now(),
                'acctterminatecause' => 'Admin-Remove'
            ]);
    }

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

            if ($customer->mikrotik) {

                app(MikrotikService::class)
                    ->disconnectPPPoE(
                        $customer->mikrotik,
                        $customer->username
                    );
            }
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
