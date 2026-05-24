<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $fillable = [
        'customer_id',
        'recharge_id',
        'billing_no',
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

    public function internetPlan()
    {
        return $this->belongsTo(InternetPlan::class, 'internet_plan_id', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public static function createBilling($customer, $recharge)
    {
        $billing = self::create([
            'customer_id'  => $customer->id,
            'recharge_id'  => $recharge->id,
            'amount'       => $recharge->price,
            'billing_date' => now(),
            'status'       => 'paid',
        ]);

        // generate billing number immediately
        $billing->generateNumber();

        return $billing;
    }

    /*
    |--------------------------------------------------------------------------
    | BILLING NUMBER GENERATOR
    |--------------------------------------------------------------------------
    */

    public function generateNumber()
    {
        $this->update([
            'billing_no' =>
                'BILL-' . now()->format('Ymd') . '-' . $this->id
        ]);
    }
}
