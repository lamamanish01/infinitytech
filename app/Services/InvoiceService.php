<?php

namespace App\Services;

use App\Models\Invoice;
use App\Services\NumberService;

class InvoiceService
{
    /**
     * Create ONLY invoice record
     */
    public static function create($billing, $recharge)
    {
        return Invoice::create([
            'billing_id'    => $billing->id,
            'invoice_no'    => NumberService::invoiceNo($billing->id),
            'invoice_date'  => now(),
            'amount'        => $billing->amount,
            'status'        => 'paid',
            'payment_method'=> $recharge->payment_method ?? 'cash',
            'transaction_id'=> $recharge->transaction_id ?? null,
        ]);
    }
}
