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
                case 'suspended':
                case 'discontinued':
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
        RadCheck::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $customer->password]
        );

        RadCheck::where('username', $customer->username)
            ->whereIn('attribute', ['Expiration', 'Auth-Type'])
            ->delete();

        RadReply::where('username', $customer->username)
            ->whereIn('attribute', [
                'Framed-Pool',          // ← THIS REMOVES THE SUSPENDED POOL
                'Mikrotik-Rate-Limit',  // ← low bandwidth override
                'Reply-Message',        // ← custom message
            ])
            ->delete();

        RadCheck::where('username', $customer->username)
            ->where('attribute', 'Auth-Type')
            ->where('value', 'Reject')
            ->delete();

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

        RadAcct::where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime' => now(),
                'acctterminatecause' => 'Expired',
            ]);

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
     * Disconnect a customer – close RADIUS sessions and disconnect from MikroTik.
     */
    public function disconnect(Customer $customer): array
    {
        try {
            // 1️⃣ Close any active RADIUS accounting session
            RadAcct::where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Admin-Disconnect',
                ]);

            // 2️⃣ Disconnect from MikroTik using the username (not mikrotik object)
            if ($customer->username) {
                $mk = app(MikrotikService::class);
                $result = $mk->disconnectPPPoE($customer->username);

                // If the result indicates a failure, we still log it but the RADIUS session is closed.
                if (!empty($result['status']) && $result['status'] === false) {
                    Log::warning('MikroTik disconnect failed', [
                        'username' => $customer->username,
                        'message'  => $result['message'] ?? 'Unknown error',
                    ]);
                }
            }

            return ['status' => true, 'message' => 'Disconnect attempted'];
        } catch (\Exception $e) {
            Log::error('Disconnect failed', [
                'username' => $customer->username,
                'error'    => $e->getMessage(),
            ]);
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    public function suspendCustomer(Customer $customer): void
    {
        $customer->update(['status' => 'suspended']);

        // // Remove any Expiration block
        // RadCheck::where('username', $customer->username)
        //     ->where('attribute', 'Expiration')
        //     ->delete();

        // Ensure password exists
        RadCheck::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Cleartext-Password'],
            ['op' => ':=', 'value' => $customer->password]
        );

        RadReply::where('username', $customer->username)
            ->where('attribute', 'Framed-Pool')
            ->delete();

        // Move to suspended group (for grouping, no reject rule)
        RadUserGroup::updateOrCreate(
            ['username' => $customer->username],
            ['groupname' => 'suspended', 'priority' => 10]
        );

        // Assign suspended pool and low bandwidth
        RadReply::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Framed-Pool'],
            ['op' => ':=', 'value' => 'suspended-pool']
        );

        RadReply::updateOrCreate(
            ['username' => $customer->username, 'attribute' => 'Reply-Message'],
            ['op' => ':=', 'value' => 'Your account is suspended']
        );

        // Kick them off immediately
        RadAcct::where('username', $customer->username)
            ->whereNull('acctstoptime')
            ->update([
                'acctstoptime'       => now(),
                'acctterminatecause' => 'Admin-Disconnect',
            ]);

        $this->disconnect($customer);
    }
}
