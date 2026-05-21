<?php

namespace App\Services;

use App\Models\Customer;

class MacService
{
    public static function check(Customer $customer, string $mac): bool
    {
        $mac = strtoupper($mac);

        // FIRST LOGIN → bind MAC automatically
        if (!$customer->mac_address) {
            $customer->update([
                'mac_address' => $mac
            ]);

            return true;
        }

        // CHECK MATCH
        return $customer->mac_address === $mac;
    }
}
