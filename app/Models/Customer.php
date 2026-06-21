<?php

namespace App\Models;

use App\Models\GracePeriod;
use App\Models\InternetPlan;
use App\Models\RadCheck;
use App\Models\RadPostAuth;
use App\Models\RadReply;
use App\Models\Recharge;
use App\Models\Tr069Device;
use App\Models\Tr069Server;
use App\Services\MacService;
use Carbon\Carbon;
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

    protected $appends = ['is_online'];


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

    public function getActiveGracePeriod()
    {
        return $this->gracePeriods()
            ->where('grace_start', '<=', now())
            ->where('grace_end', '>=', now())
            ->orderByDesc('grace_end')
            ->first();
    }

    public function calculateStatus(): string
    {
        $now = now();

        if (is_null($this->expire_date)) {
            return 'active';
        }

        if ($this->expire_date->isFuture()) {
            return 'active';
        }

        if ($this->gracePeriods()->where('grace_end', '>=', $now)->exists()) {
            return 'grace';
        }

        $graceDays = (int) $this->grace_days;
        if ($graceDays > 0) {
            $graceEnd = $this->expire_date->copy()->addDays($graceDays);
            if ($graceEnd->isFuture()) {
                return 'grace';
            }
        }

        return 'expired';
    }

    public function getCalculatedStatusAttribute(): string
    {
        return $this->calculateStatus();
    }

    public function isActive(): bool
    {
        return $this->calculateStatus() === 'active';
    }

    public function isGrace(): bool
    {
        return $this->calculateStatus() === 'grace';
    }

    public function isExpired(): bool
    {
        return $this->calculateStatus() === 'expired';
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
            ->latest('radacctid');
    }

    public function previousSession()
    {
        return $this->hasMany(RadAcct::class, 'username', 'username')
            ->whereNotNull('acctstoptime')
            ->orderByDesc('acctstoptime');
    }

    public function getIsOnlineAttribute(): bool
    {
        $session = $this->activeSession;

        if (!$session) {
            return false;
        }

        $lastActivity = $session->acctupdatetime
            ?? $session->acctstarttime;

        if (!$lastActivity) {
            return false;
        }

        return Carbon::parse($lastActivity)
            ->greaterThan(now()->subMinutes(15));
    }

    public function getStatusAttribute($value)
    {
        if (!$this->expire_date) {
            return 'unknown';
        }

        $expire = Carbon::parse($this->expire_date);

        if ($expire->lt(now())) {

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

    public function recentAuthLogs($limit = 10)
    {
        return $this->authLogs()
            ->select('id', 'username', 'reply', 'authdate')
            ->orderByDesc('authdate')
            ->limit($limit)
            ->get();
    }

    public function scopeOnline($query)
    {
        $cutoff = now()->subMinutes(15);

        return $query->whereHas('activeSession', function ($q) use ($cutoff) {

            $q->where(function ($sub) use ($cutoff) {

                $sub->where('acctupdatetime', '>=', $cutoff)

                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('acctupdatetime')
                           ->where('acctstarttime', '>=', $cutoff);
                    });

            });

        });
    }

    public function device()
    {
        return $this->hasOne(Tr069Device::class);
    }

    public function server()
    {
        return $this->belongsTo(Tr069Server::class);
    }

    public function routerDevices()
    {
        return $this->hasMany(Tr069Device::class, 'ppp_username', 'username');
    }
}

