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
        return $this->belongsTo(InternetPlan::class, 'internetplan', 'name');
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

    public function syncWithRad($username, $password, $customerId)
    {
        $customer = self::find($customerId);

        // RadCheck::updateOrCreate([
        //     'username' => $username,
        //     'attribute' => 'Cleartext-Password',
        //     'op' => ':=',
        //     'value' => $password
        // ]);

        $exipry = $customer->recharge()->first();
        dd($exipry);

        RadCheck::updateOrInsert([
            'username' => $username,
            'attribute' => 'Expiration',
            'op' => ':=',
            'value' => $expire_date,
        ]);


        $internetPlan = $customer->internetPlan()->first();

        if (!$internetPlan || !$internetPlan->rate_limit) {
            return false;
        }

        $rate_limit = $internetPlan->rate_limit;

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

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Session-Timeout',
            'op' => ':=',
            'value' => 3600,
        ]);

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Idle-Timeout',
            'op' => ':=',
            'value' => 600,
        ]);
    }

    public function disconnectCustomer($username)
    {
        $radiusIP = env('
        ');
        $radiusSecret = env('RADIUS_SHARED_SECRET');
        $radiusPorts = env('RADIUS_SERVER_PORT');

        $command = "echo 'User-Name=$username' | radclient -x $radiusServerIp:$port disconnect $sharedSecret";

        exec($command, $output, $status);

        if ($status === 0) {
            return "User session disconnected successfully.\n" . implode("\n", $output);
        } else {
            return "Failed to disconnect user session.\n" . implode("\n", $output);
        }
    }
}

