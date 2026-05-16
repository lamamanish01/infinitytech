<?php

namespace App\Services;

use App\Models\Billing;

class BillingService
{
    /**
     * Create ONLY billing record
     */
    public static function create($customer, $recharge)
    {
        $billing = Billing::create([
            'customer_id' => $customer->id,
            'recharge_id' => $recharge->id,
            'billing_date'=> now(),
            'amount'      => $recharge->price,
            'status'      => 'paid',
        ]);

        $billing->update([
            'billing_no' => 'BILL-' . now()->format('Ymd') . '-' . $billing->id
        ]);

        return $billing;
    }
}
