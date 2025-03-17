<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\RadCheck;
use App\Models\RadReply;
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

    public function bandwidth()
    {
        return $this->belongsTo(Bandwidth::class);
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

    public function syncWithRad($username, $password)
    {
        RadCheck::create([
            'username' => $username,
            'attribute' => 'Cleartext-Password',
            'op' => ':=',
            'value' => $password
        ]);

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $speed,
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

    public function getBandwidth($customerId)
    {
        $customer_id = Customer::find($this);
        dd(customer_id);
        $internetplan = $this->internetplan()->with('bandwidth')->first();

        dd($internetplan);
        if (!$internetplan || !$internetplan->bandwidth)
        {
            return false;
        }

        $update_rate = $internetplan->bandwidth->upload_rate;
        $download_rate = $internetplan->bandwidth->download_rate;
        $speed = $update_rate. '/' .$download_rate;

        $username = $customer->username;

        RadReply::updateOrCreate([
            'username' => $username,
            'attribute' => 'Mikrotik-Rate-Limit',
            'op' => ':=',
            'value' => $speed,
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
        $radiusIP = env('RADIUS_SERVER_IP');
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

