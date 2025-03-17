<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function recharges()
    {
        return $this->hasMany(Recharge::class);
    }

    public function latestRecharge()
    {
        return $this->hasOne(Recharge::class)->latest('expire_date');
    }

    public function GracePeriod()
    {
        return $this->hasOne(GracePeriod::class);
    }

    public function NewExpiryDate($planDuration)
    {
        $latestRecharge = $this->latestRecharge;

        if($latestRecharge && !$latestRecharge->isExpired()) {
            return Carbon::parse($latestRecharge->expire_date)->addMonths($planDuration);
        } else {
            return Carbon::now()->addMonths($planDuration);
        }
    }


    public function provideGraceDays($graceDays)
    {
        $gracePeriod = GracePeriod::updateOrCreate([
            'customer_id' => $this->id,
            'grace_days' => $graceDays
        ]);

        $latestRecharge = $this->latestRecharge;
        if ($latestRecharge)
        {
            $latestRecharge->extendWithGracePeriod($gracePeriod->);
        }
    }
}

