<?php

namespace App\Models;

use App\Models\InternetPlan;
use App\Services\BillingService;
use App\Services\InvoiceService;
use App\Services\RadiusService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function makeRecharge($customerId, $planId, $paymentMethod = null, $transactionId = null)
    {
        return DB::transaction(function () use (
            $customerId,
            $planId,
            $paymentMethod,
            $transactionId
        ) {

            // ✅ FIX: convert IDs to MODELS
            $customer = Customer::lockForUpdate()->findOrFail($customerId);
            $plan = InternetPlan::findOrFail($planId);
            $branch = Branch::lockForUpdate()->findOrFail($customer->branch_id);

            if ($transactionId) {
                $exists = self::where('transaction_id', $transactionId)->exists();
                if ($exists) {
                    throw new \Exception("Duplicate transaction detected");
                }
            }

            if ($branch->balance < $plan->price) {
                throw new \Exception("Insufficient branch balance");
            }

            GracePeriod::where('customer_id', $customer->id)->delete();

            // expiry
            $baseDate = ($customer->expire_date && $customer->expire_date > now())
            ? Carbon::parse($customer->expire_date)
            : now();

            $expireDate = match ($plan->type) {
                'month' => $baseDate->copy()->addMonths($plan->duration),
                'year'  => $baseDate->copy()->addYears($plan->duration),
                default => $baseDate->copy()->addDays($plan->duration),
            };

            $recharge = self::create([
                'customer_id' => $customer->id,
                'internet_plan_id' => $plan->id,
                'price' => $plan->price,
                'recharge_date' => now(),
                'expire_date' => $expireDate,
                'status' => 'active',
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'user_id' => auth()->id(),
            ]);

            $billing = Billing::createBilling($customer, $recharge);
            $invoice = Invoice::createInvoice($billing, $recharge);

            // debit
            $branch->decrement('balance', $plan->price);

            $customer->update([
                'internet_plan_id' => $plan->id,
                'expire_date' => $expireDate,
                'status' => 'active',
            ]);

            RadiusService::syncCustomer($customer->fresh());

            return $recharge;
        });
    }
}
