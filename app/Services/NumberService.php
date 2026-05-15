<?php

namespace App\Services;

class NumberService
{
    public static function billingNo($customerId)
    {
        return 'BILL-' . date('Ymd') . '-' . str_pad($customerId, 4, '0', STR_PAD_LEFT);
    }

    public static function invoiceNo($billingId)
    {
        return 'INV-' . date('Ymd') . '-' . str_pad($billingId, 4, '0', STR_PAD_LEFT);
    }

    public static function rechargeNo($customerId)
    {
        return 'RCH-' . date('Ymd') . '-' . rand(1000, 9999);
    }
}
