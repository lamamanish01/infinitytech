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
}
