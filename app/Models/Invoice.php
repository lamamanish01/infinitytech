<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'billing_id',
        'invoice_no',
        'invoice_date',
        'amount',
        'status',
        'payment_method',
        'transaction_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public static function createInvoice($billing, $recharge)
    {
        $invoice = self::create([
            'billing_id'     => $billing->id,
            'invoice_no'     => 'TEMP-' . uniqid(), // safe temporary unique
            'invoice_date'   => now(),
            'amount'         => $billing->amount,
            'status'         => 'paid',
            'payment_method' => $recharge->payment_method,
            'transaction_id' => $recharge->transaction_id,
        ]);

        // generate final invoice number
        $invoice->generateNumber();

        return $invoice;
    }

    /*
    |--------------------------------------------------------------------------
    | INVOICE NUMBER GENERATOR
    |--------------------------------------------------------------------------
    */

    public function generateNumber()
    {
        $this->update([
            'invoice_no' =>
                'INV-' . now()->format('Ymd') . '-' . $this->id
        ]);
    }
}
