<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Recharge;
use Illuminate\Support\Facades\DB;
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

    public function syncWithRadCheck($username, $password)
    {
        DB::table('radcheck')->insert(
            [
                'username' => $username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $password
            ]
        );
    }

    public function disconnectCustomer()
    {
        $radiusIP = env('RADIUS_SERVER_IP');
        $radiusSecret = env('RADIUS_SHARED_SECRET');
        
    }
}

