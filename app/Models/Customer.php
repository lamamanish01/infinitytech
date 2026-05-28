<?php

namespace App\Models;

use App\Models\InternetPlan;
use App\Models\RadCheck;
use App\Models\RadPostAuth;
use App\Models\RadReply;
use App\Models\Recharge;
use App\Services\MacService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'email',
        'address',
        'contact_number',
        'username',
        'password',
        'mac_address',
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
        return $this->belongsTo(InternetPlan::class, 'internet_plan_id');
    }

    public function isMacValid($mac): bool
    {
        return MacService::check($this, $mac);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recharges()
    {
        return $this->hasMany(Recharge::class);
    }

    public function mikrotik()
    {
        return $this->belongsTo(Mikrotik::class);
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

    public function calculateStatus()
    {
        if (!$this->expire_date) {
            return 'active';
        }
        $now = now();
        $expire = $this->expire_date->copy()->endOfDay();
        $graceEnd = $expire->copy()->addDays(3);

        if ($now->greaterThan($graceEnd)) {
            return 'expired';
        }

        if ($now->greaterThan($expire)) {
            return 'grace';
        }

        return 'active';
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
        return $this->hasMany(Billing::class)->latest();
    }

    public function activeSession()
    {
        return $this->hasOne(RadAcct::class, 'username', 'username')
            ->whereNull('acctstoptime')
            ->latestOfMany('radacctid');
    }

    public function previousSession()
    {
        return $this->hasOne(RadAcct::class, 'username', 'username')
            ->whereNotNull('acctstoptime')
            ->orderByDesc('acctstoptime');
    }

    public function getIsOnlineAttribute()
    {
        $session = DB::table('radacct')
            ->where('username', $this->username)
            ->whereNull('acctstoptime')
            ->latest('acctstarttime')
            ->first();

        if (!$session) {
            return false;
        }

        $lastUpdate = $session->acctupdatetime ?? $session->acctstarttime;

        return Carbon::parse($lastUpdate)->gt(now()->subMinutes(10));
    }

    public function getStatusAttribute($value)
    {
        if (!$this->expire_date) {
            return 'unknown';
        }

        $expire = Carbon::parse($this->expire_date);

        if ($expire->lt(now())) {

            // check grace
            $grace = $this->activeGrace();

            if ($grace && Carbon::parse($grace->grace_end)->gte(now())) {
                return 'grace';
            }

            return 'expired';
        }

        return 'active';
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
            ->select('id', 'username', 'reply', 'authdate')
            ->orderByDesc('authdate')
            ->limit($limit)
            ->get();
    }

    public function scopeOnline($query)
    {
        return $query->whereHas('activeSession', function ($q) {
            $q->where('acctupdatetime', '>=', now()->subMinutes(5));
        });
    }
}

