<?php

namespace App\Services;

use App\Models\Billing;
use App\Services\NumberService;

class BillingService
{
    /**
     * Create ONLY billing record
     */
    public static function create($customer, $recharge)
    {
        return Billing::create([
            'customer_id' => $customer->id,
            'recharge_id' => $recharge->id,
            'billing_no'  => NumberService::billingNo($customer->id),
            'billing_date'=> now(),
            'amount'      => $recharge->price,
            'status'      => 'paid',
        ]);
    }
}
