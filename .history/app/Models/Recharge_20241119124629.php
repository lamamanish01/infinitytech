<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\RadCheck;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = ['customer_id'. 'internetplan', 'recharge_date', 'expire_date'];

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
            $this->expire_date = Carbon::parse($this->expire_date)->addDays($graceDays);
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

        // $radCheck = RadCheck::where('username', $username)
        //     ->where('attribute', 'Expiration')
        //     ->first();

        // if ($radCheck) {
        //     $radCheck->update(['value' => $expire_date]);
        // } else {
        //     RadCheck::updateOrCreate([
        //         'username' => $username,
        //         'attribute' => 'Expiration',
        //         'op' => ':=',
        //         'value' => $expire_date,
        //     ]);
        // }

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

        // $radReply = RadReply::where('username', $username)
        //     ->where('attribute', 'Framed-Pool')
        //     ->first();

        // if ($radReply)
        // {
        //     if ($radReply->value === 'Expired-Pool')
        //     {
        //         $radReply->update(['value' => 'PPPoE-Pool']);
        //     }
        // } else {
        //     RadReply::updateOrCreate([
        //         'username' => $username,
        //         'attribute' => 'Framed-Pool',
        //         'op' => ':=',
        //         'value' => 'PPPoE-Pool',
        //     ]);
        // }

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

    public function removeExpiredSession($username, $expire_date)
    {
        $radReply = RadReply::->where('attribute', 'Mikrotik-Address-List')->delete();
    }

    public function disconnectCustomer($username)
    {
        $radiusIP = env('RADIUS_SERVER_IP ');
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
