<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\InternetPlan;
use Illuminate\Database\Eloquent\Model;

class Recharge extends Model
{
    protected $fillable = [
        'customer_id',
        'internet_plan_id',
        'price',
        'recharge_date',
        'expire_date',
        'status',
        'payment_method',
        'transaction_id',
        'user_id',
    ];

    protected $casts = [
        'recharge_date' => 'datetime',
        'expire_date' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function internetPlan()
    {
        return $this->belongsTo(InternetPlan::class);
    }

    public static function makeRecharge($customerId, $internetplanId, $paymentMethod = null, $transactionId = null)
    {
        $customer = Customer::findOrFail($customerId);
        $internetPlan = InternetPlan::findOrFail($internetplanId);

        if ($customer->expire_date && Carbon::parse($customer->expire_date)->isFuture())
        {
            $expireDate = Carbon::parse($customer->expire_date);

        } else {
            $expireDate = now();
        }

        switch ($internetPlan->type) {
            case 'day':
                $expireDate->addDays(
                    (int) $internetPlan->duration
                );
            break;

            case 'month':
                $expireDate->addMonths(
                    (int) $internetPlan->duration
                );
            break;

            case 'year':
                $expireDate->addYears(
                    (int) $internetPlan->duration
                );
            break;

            default:
                $expireDate->addDays(
                    (int) $internetPlan->duration
                );
            break;
        }

        $recharge = self::create([
            'customer_id' => $customer->id,
            'internet_plan_id' => $internetPlan->id,
            'price' => $internetPlan->price,
            'recharge_date' => now(),
            'expire_date' => $expireDate,
            'status' => 'active',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'user_id' => auth()->id(),
        ]);

        $customer->update([
            'internet_plan_id' => $internetPlan->id,
            'expire_date' => $expireDate,
            'status' => 'active',
        ]);

        if ($customer->gracePeriod) {
            $customer->gracePeriod->update([
                'grace_start_date' => 0
            ]);
        }

        return true;
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
}
