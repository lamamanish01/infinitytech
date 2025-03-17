<?php

namespace App\Models;

use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internet_plan_id', 'recharge_date', 'expiry']

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'username');
    }
}
