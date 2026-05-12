<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\Recharge;
use App\Models\RadPostAuth;
use App\Models\InternetPlan;
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
        return $this->hasOne(Recharge::class)->latestOfMany('expire_date');
    }

    public function rechargeDate()
    {
        return $this->hasOne(Recharge::class)->latestOfMany('recharge_date');
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

    public function radAccts()
    {
        return $this->hasMany(RadAcct::class, 'username', 'username');
    }

    public function radChecks()
    {
        return $this->hasMany(RadCheck::class, 'username', 'username');
    }

    public function radreplies()
    {
        return $this->hasMany(RadReply::class, 'username', 'username');
    }

    public function authLogs()
    {
        return $this->hasMany(RadPostAuth::class, 'username', 'username');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }

    public function syncWithRad($username, $password, $rate_limit)
    {
        RadCheck::updateOrCreate([
            'username' => $username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $password
        ]);

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $rate_limit,
        ]);

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Framed-Pool',
            'op' => ':=',
            'value' => 'PPPoE-Pool',
        ]);
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

    public function activeSession()
    {
        return $this->hasOne(RadAcct::class, 'username', 'username')
            ->whereNull('acctstoptime')
            ->latestOfMany('acctstarttime');
    }

    public function previousSession()
    {
        return $this->hasOne(RadAcct::class, 'username', 'username')
            ->whereNotNull('acctstoptime')
            ->latestOfMany('acctstoptime');
    }

    public function getIsOnlineAttribute()
    {
        $session = $this->activeSession;

        if (!$session) {
            return false;
        }

        if (!$session->acctupdatetime) {
            return false;
        }

        return $session->acctupdatetime >= now()->subMinutes(5);
    }

    public function getActiveAttribute()
    {
        return $this->activeSession;
    }

    public function getPreviousAttribute()
    {
        return $this->previousSession;
    }

    public function recentAuthLogs($limit = 25)
    {
        return $this->authLogs()
            ->select('username', 'reply', 'authdate')
            ->orderBy('authdate', 'desc')
            ->take($limit)
            ->get();
    }

    public function getCustomerBilling()
    {
        return $this->billings()
        ->join('customers', 'billings.customer_id', '=', 'customers.id')
        ->select(
            'billings.customer_id',
            'customers.username',
            'billings.recharge_id',
            'billings.billing_date',
            'billings.internet_plan',
            'billings.amount'
        )
        ->get();
    }

}

