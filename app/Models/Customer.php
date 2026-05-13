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
    protected $fillable = [
        'name',
        'email',
        'address',
        'contact_number',
        'username',
        'password',
        'internet_plan_id',
        'branch_id',
        'user_id',
        'expire_date',
        'registered_at',
        'status',
        'remarks',
    ];

    protected $casts = [
        'expire_date' => 'datetime',
        'registered_at' => 'datetime',
    ];

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
        return $this->belongsTo(InternetPlan::class, 'id');
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

    public function gracePeriods()
    {
        return $this->hasMany(GracePeriod::class);
    }

    public function activeGrace()
    {
        return $this->gracePeriods()->latest()->first();
    }

     public function isActive()
    {
        return $this->expire_date && now()->lte($this->expire_date);
    }

    public function graceEndDate()
    {
        $grace = $this->activeGrace();

        if (!$grace || !$grace->grace_start) {
            return null;
        }

        return Carbon::parse($grace->grace_start)
            ->addDays($grace->grace_days);
    }

    public function isInGrace()
    {
        $grace = $this->activeGrace();

        if (!$grace || !$this->expire_date) {
            return false;
        }

        return now()->gt($this->expire_date)
            && now()->lte($this->graceEndDate());
    }

    public function isExpired()
    {
        if (!$this->expire_date)
            return true;

        $grace = $this->activeGrace();

        if ($grace) {
            return now()->gt($this->graceEndDate());
        }

        return now()->gt($this->expire_date);
    }

    public function status()
    {
        if ($this->status === 'suspended')
            return 'suspended';
        if ($this->isActive())
            return 'active';
        if ($this->isInGrace())
            return 'grace';

        return 'expired';
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
}

