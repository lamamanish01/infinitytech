<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internet_plan_id', 'recharge_date', 'expiry_date', 'status'];

    public function isExpired()
    {
        return Carbon::now() < $this->expiry_date);
    }
}
