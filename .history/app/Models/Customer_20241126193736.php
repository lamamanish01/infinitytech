<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\Recharge;
use App\Models\InternetPlan;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = ['name', 'address', 'email', 'username', 'password', 'contact_number', 'internetplan', 'branch', 'registered', 'status', 'user_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'name');
    }

    public function bandwidth()
    {
        return $this->belongsTo(Bandwidth::class, 'name');
    }

    public function internetPlan()
    {
        return $this->belongsTo(InternetPlan::class, 'internetplan', 'bandwidth_name', 'price');
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

    public function gracePeriod()
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

    public function radAcct()

    public function radChecks()
    {
        return $this->hasMany(RadCheck::class, 'username', 'username');
    }

    public function radreplies()
    {
        return $this->hasMany(RadReply::class, 'username', 'username');
    }

    public function provideGraceDays($graceDays)
    {
        $latestRecharge = $this->latestRecharge->first();

        if ($latestRecharge && $latestRecharge->isExpired()) {
            $gracePeriod = GracePeriod::updateOrCreate([
                'customer_id' => $this->id,
                'grace_days' => $graceDays
            ]);

            $latestRecharge->extendWithGracePeriod($gracePeriod->grace_days);
        } else {
            return false;
        }
        return true;
    }

    public function disableExpiredUser()
    {

    }
}

