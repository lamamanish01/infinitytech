<?php

namespace App\Services;

use App\Services\MikrotikService;
use Illuminate\Support\Facades\DB;

class RadiusService
{
    /**
     * Create or update Radius user.
     */
    public static function syncCustomer($customer)
    {
        $plan = $customer->internetPlan;

        if (!$plan) {
            return;
        }

        // Remove Reject flag if customer is active again
        DB::table('radcheck')
            ->where('username', $customer->username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->delete();

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

    /**
     * Disable customer instead of deleting Radius records.
     */
    public static function removeCustomer($customer)
    {
        DB::table('radcheck')->updateOrInsert(
            [
                'username'  => $customer->username,
                'attribute' => 'Auth-Type',
            ],
            [
                'op'    => ':=',
                'value' => 'Reject',
            ]
        );

        DB::table('radacct')
            ->where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime'       => now(),
                'acctterminatecause' => 'Expired',
            ]);
    }

    /**
     * Enable customer after renewal.
     */
    public static function enableCustomer($customer)
    {
        DB::table('radcheck')
            ->where('username', $customer->username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->delete();
    }

    /**
     * Disconnect active session.
     */
    public static function disconnect($customer)
    {
        try {

            $updated = DB::table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Admin-Disconnect',
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
                    'status'  => false,
                    'message' => 'User is not currently online',
                ];
            }

            return [
                'status'  => true,
                'message' => 'User disconnected successfully',
            ];

        } catch (\Exception $e) {

            return [
                'status'  => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
