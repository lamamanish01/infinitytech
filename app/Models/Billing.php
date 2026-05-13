<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = [
        'customer_id',
        'recharge_id',
        'billing_date',
        'amount',
        'status',
        'invoice_number',
    ];

    protected $casts = [
        'billing_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function recharge()
    {
        return $this->belongsTo(Recharge::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }
}
