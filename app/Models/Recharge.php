<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id', 'internet_plan', 'recharge_date', 'expire_date'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function internetPlan()
    {
        return $this->belongsTO(InternetPlan::class);
    }

    public function isExpired()
    {
        return Carbon::parse($this->expire_date)->isPast();
    }

    public function isWithinGracePeriod()
    {
        return $this->grace_period && Carbon::now()->between($this->expire_date, $this->grace_period);
    }

    public function extendWithGracePeriod($graceDays)
    {
        if ($this->isExpired())
        {
            $this->expire_date = Carbon::parse($this->expire_date)->addDays((int)$graceDays);
            $this->save();
        }
    }

    public function syncWithRad($username, $password, $expire_date, $rate_limit)
    {
        RadCheck::updateOrCreate([
            'username' => $username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $password
        ]);

        RadCheck::updateOrCreate([
            'username' => $username,
            'attribute' => 'Expiration',
            'op' => ':=',
            'value' => $expire_date,
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
}
