<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internet_plan_id', 'recharge_date', 'expire_date'];

    public function isExpired()
    {
        return Carbon::now()->gt($this->expire_date);
    }

    public function isWithinGracePeriod()
    {
        return $this->grace_period && Carbon::now()->between($this->expire_date, $this->grace_period);
    }

    public function extendWithGracePeriod($graceDays)
    {
        if ($this->isExpired())
        {
            $this->expire_date = Carbon::parse($this->expire_date)->addDays($graceDays);
            dd($)
            $this->save();
        }
    }
}
