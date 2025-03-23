<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Recharge;
use App\Models\InternetPlan;
use Illuminate\Http\Request;

class RechargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        return view('recharges.create', compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Recharge $recharge)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $duration = $customer->internetPlan->duration;
        $rate_limit = $customer->internetPlan->rate_limit;;
        $price = $customer->internetPlan->price;

        $expiryDate = $customer->NewExpiryDate($duration);

        if ($customer->gracePeriod && $customer->gracePeriod->grace_days > 0)
        {
            $expiryDate->subDays($customer->gracePeriod->grace_days);

            $customer->gracePeriod->update(['grace_days' => 0]);
        }

        $recharge->customer_id = $customer->id;
        $recharge->internet_plan = $request->internetplan;
        $recharge->recharge_date = Carbon::now();
        $recharge->expire_date = $expiryDate;
        $recharge->save();

        $username = $customer->username;
        $password = $customer->password;
        $rate_limit = $rate_limit;
        $expiry_date = $expiryDate;

        $recharge->syncWithRad($username, $password, $expiry_date, $rate_limit);

        $billing = Billing::create([
            'customer_id' => $customer->id,
            'recharge_id' => $recharge->id,
            'billing_date' => Carbon::now(),
            'internet_plan' => $customer->internetplan,
            'amount' => $price,
        ]);

        Invoice::create([
            'billing_id' => $billing->id,
            'invoice_date' => Carbon::now(),
            'status' => 'unpaid',
            'amount' => $price
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer recharge successful');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }


    public function provideGrace(Request $request, $customerId)
    {
        $request->validate([
            'grace_days' => 'required'
        ]);

        $customer = Customer::findOrFail($customerId);

        $gracePermit = $customer->provideGraceDays($request->grace_days);

        if ($gracePermit)
        {
            return redirect()->route('customers.show', $customer->id)->with('success', "$request->grace_days days grace provided successfully");
        } else {
            return redirect()->route('customers.show', $customer->id)->with('error', 'Cannot provide grace days, subscription is not expired.');
        }
    }
}
