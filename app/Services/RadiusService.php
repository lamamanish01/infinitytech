<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\RadUserGroup;
use App\Services\MikrotikService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RadiusService
{
    /**
     * Create or update Radius user.
     */
    // public static function syncCustomer($customer)
    // {
    //     $plan = $customer->internetPlan;

    //     if (!$plan) {
    //         return;
    //     }

    //     // Remove Reject flag if customer is active again
    //     DB::table('radcheck')
    //         ->where('username', $customer->username)
    //         ->where('attribute', 'Auth-Type')
    //         ->where('value', 'Reject')
    //         ->delete();

    //     DB::table('radcheck')->updateOrInsert(
    //         [
    //             'username'  => $customer->username,
    //             'attribute' => 'Cleartext-Password',
    //         ],
    //         [
    //             'op'    => ':=',
    //             'value' => $customer->password,
    //         ]
    //     );

    //     DB::table('radreply')->updateOrInsert(
    //         [
    //             'username'  => $customer->username,
    //             'attribute' => 'Mikrotik-Rate-Limit',
    //         ],
    //         [
    //             'op'    => ':=',
    //             'value' => $plan->rate_limit,
    //         ]
    //     );

    //     DB::table('radreply')->updateOrInsert(
    //         [
    //             'username'  => $customer->username,
    //             'attribute' => 'Framed-Pool',
    //         ],
    //         [
    //             'op'    => ':=',
    //             'value' => 'PPPoE-Pool',
    //         ]
    //     );

    //     if (!empty($customer->mac_address)) {

    //         DB::table('radreply')->updateOrInsert(
    //             [
    //                 'username'  => $customer->username,
    //                 'attribute' => 'Calling-Station-Id',
    //             ],
    //             [
    //                 'op'    => '==',
    //                 'value' => strtolower($customer->mac_address),
    //             ]
    //         );
    //     }
    // }

    // /**
    //  * Disable customer instead of deleting Radius records.
    //  */
    // public static function removeCustomer($customer)
    // {
    //     DB::table('radcheck')->updateOrInsert(
    //         [
    //             'username'  => $customer->username,
    //             'attribute' => 'Auth-Type',
    //         ],
    //         [
    //             'op'    => ':=',
    //             'value' => 'Reject',
    //         ]
    //     );

    //     DB::table('radacct')
    //         ->where('username', $customer->username)
    //         ->whereNull('acctstoptime')
    //         ->update([
    //             'acctstoptime'       => now(),
    //             'acctterminatecause' => 'Expired',
    //         ]);
    // }

    // /**
    //  * Enable customer after renewal.
    //  */
    // public static function enableCustomer($customer)
    // {
    //     DB::table('radcheck')
    //         ->where('username', $customer->username)
    //         ->where('attribute', 'Auth-Type')
    //         ->where('value', 'Reject')
    //         ->delete();
    // }

    // /**
    //  * Disconnect active session.
    //  */
    // public static function disconnect($customer)
    // {
    //     try {

    //         $updated = DB::table('radacct')
    //             ->where('username', $customer->username)
    //             ->whereNull('acctstoptime')
    //             ->update([
    //                 'acctstoptime'       => now(),
    //                 'acctterminatecause' => 'Admin-Disconnect',
    //             ]);

    //         if ($customer->mikrotik) {
    //             app(MikrotikService::class)
    //                 ->disconnectPPPoE(
    //                     $customer->mikrotik,
    //                     $customer->username
    //                 );
    //         }

    //         if ($updated === 0) {
    //             return [
    //                 'status'  => false,
    //                 'message' => 'User is not currently online',
    //             ];
    //         }

    //         return [
    //             'status'  => true,
    //             'message' => 'User disconnected successfully',
    //         ];

    //     } catch (\Exception $e) {

    //         return [
    //             'status'  => false,
    //             'message' => $e->getMessage(),
    //         ];
    //     }
    // }


    //new

        public function syncCustomer(Customer $customer): void
    {
        $status = $customer->calculateStatus();

        Log::info('Syncing customer to RADIUS', [
            'username' => $customer->username,
            'status'   => $status,
        ]);

        DB::transaction(function () use ($customer, $status) {
            switch ($status) {
                case 'active':
                    $this->enableCustomer($customer);
                    break;
                case 'grace':
                    $this->ensureActiveForGrace($customer);
                    break;
                case 'expired':
                    $this->disableCustomer($customer);
                    break;
                case 'suspended':
                    $this->disableCustomer($customer);
                    break;
            }

            // Persist the calculated status
            if ($customer->status !== $status) {
                $customer->update(['status' => $status]);
            }
        });
    }

    /**
     * Activate: remove blocks, assign plan group.
     */
    public function enableCustomer(Customer $customer): void
    {
        // Ensure password exists
        RadCheck::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $customer->password]
        );

        // Remove Expiration and Auth-Type Reject
        RadCheck::where('username', $customer->username)
            ->whereIn('attribute', ['Expiration', 'Auth-Type'])
            ->delete();

        RadCheck::where('username', $customer->username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->delete();

        // Assign to plan group
        $planGroup = $customer->internetPlan->radius_group ?? 'default';
        RadUserGroup::updateOrCreate(
            ['username' => $customer->username],
            ['groupname' => $planGroup, 'priority' => 1]
        );

        $this->syncReplyAttributes($customer);
    }

    /**
     * Disable: block with Expiration, move to suspended, close sessions.
     */
    public function disableCustomer(Customer $customer): void
    {
        // Block with Expiration (past date)
        RadCheck::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Expiration'],
            ['op' => ':=', 'value' => $customer->expire_date->format('d M Y H:i:s')]
        );

        // Move to suspended group
        RadUserGroup::updateOrCreate(
            ['username' => $customer->username],
            ['groupname' => 'suspended', 'priority' => 10]
        );

        // Close accounting sessions
        RadAcct::where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Expired',
            ]);

        // Disconnect from MikroTik (if service exists)
        $this->disconnect($customer);
    }

    /**
     * Grace: ensure unblocked and in plan group.
     */
    public function ensureActiveForGrace(Customer $customer): void
    {
        RadCheck::where('username', $customer->username)
            ->whereIn('attribute', ['Expiration', 'Auth-Type'])
            ->delete();

        $planGroup = $customer->internetPlan->radius_group ?? 'default';
        RadUserGroup::updateOrCreate(
            ['username' => $customer->username],
            ['groupname' => $planGroup, 'priority' => 1]
        );

        $this->syncReplyAttributes($customer);
    }

    /**
     * Sync per-user reply attributes (optional if you use radgroupreply).
     */
    private function syncReplyAttributes(Customer $customer): void
    {
        $plan = $customer->internetPlan;
        if (!$plan) {
            return;
        }

        RadReply::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Mikrotik-Rate-Limit'],
            ['op' => ':=', 'value' => $plan->rate_limit]
        );

        RadReply::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Framed-Pool'],
            ['op' => ':=', 'value' => 'PPPoE-Pool']
        );

        if (!empty($customer->mac_address)) {
            RadReply::updateOrCreate(
                ['username' => $customer->username, 'attribute' => 'Calling-Station-Id'],
                ['op' => '==', 'value' => strtolower($customer->mac_address)]
            );
        }
    }

    /**
     * Disconnect from MikroTik and close radacct.
     */
    public function disconnect(Customer $customer): array
    {
        try {
            RadAcct::where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Admin-Disconnect',
                ]);

            if ($customer->mikrotik) {
                app(MikrotikService::class)
                    ->disconnectPPPoE($customer->mikrotik, $customer->username);
            }

            return ['status' => true, 'message' => 'Disconnect attempted'];
        } catch (\Exception $e) {
            Log::error('Disconnect failed', [
                'username' => $customer->username,
                'error' => $e->getMessage(),
            ]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }
}
