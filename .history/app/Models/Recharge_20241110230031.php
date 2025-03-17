<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internet_plan_id', 'recharge_date', 'expire_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function isExpired()
    {
        return Carbon::parse($this->expire_date)->isPast();
    }


    public function extendWithGracePeriod($graceDays)
    {
        if ($this->isExpired())
        {
            $this->expire_date = Carbon::parse($this->expire_date)->addDays($graceDays);
            $this->save();
        }
    }
}
