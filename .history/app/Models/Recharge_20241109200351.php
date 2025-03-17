<?php

namespace App\Models;

use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internet_plan_id', 'recharge_date', 'expiry_date', 'status'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'username');
    }

    public function latestRecharge()
    {
        return $this->hasOne(Recharge::class)->lastest('recharge_date');
    }

    public function isExpired()
    {
        return Carbon::now()->gt($this->expiry_date);
    }
}
