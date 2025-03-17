<?php

namespace App\Models;

use App\Models\Recharge;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number', 'internetplan_id', 'branch_id', 'registered', 'status', 'user_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'name');
    }

    public function internetplan()
    {
        return $this->belongsTo(InternetPlan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function latestRecharge()
    {
        return $this->hasOne(Recharge::class)->latest('recharge_date');
    }

    public function hasGracePeriod()
    {
        return !is_null($this->grace_period);
    }

    public function NewExpiryDate()
    {
        $latestRecharge = $this->latestRecharge;

        if($latestRecharge && !$latestRecharge->isExpired()) {
            return Carbon::parse($latestRecharge->expire_date)->addMonths($planDuration);
        } else {
            return Carbon::now()->addMonths($planDuration);
        }
    }

    public function gracePeriod()
    {
        if($latestRecharge && $latestRecharge->hasGracePeriod()) {
            return $expiryDate->copy()->addDays(3);
        }
    }
}

