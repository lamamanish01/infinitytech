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

            // ❌ duplicate check
            if ($transactionId) {
                $exists = self::where('transaction_id', $transactionId)->exists();
                if ($exists) {
                    throw new \Exception("Duplicate transaction detected");
                }
            }

            // ❌ balance check
            if ($branch->balance < $plan->price) {
                throw new \Exception("Insufficient branch balance");
            }

             /*
            |--------------------------------------------------------------------------
            | RESET TEMP STATES (IMPORTANT FIX)
            |--------------------------------------------------------------------------
            */
            GracePeriod::where('customer_id', $customer->id)->delete();

            // expiry
            $expireDate = $customer->expire_date
                ? Carbon::parse($customer->expire_date)->copy()
                : now();

            match ($plan->type) {
                'month' => $expireDate->addMonths($plan->duration),
                'year'  => $expireDate->addYears($plan->duration),
                default => $expireDate->addDays($plan->duration),
            };

            // recharge
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

            // billing
            $billing = Billing::createBilling($customer, $recharge);

            // invoice
            $invoice = Invoice::createInvoice($billing, $recharge);

            // debit
            $branch->decrement('balance', $plan->price);

            // update customer
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
