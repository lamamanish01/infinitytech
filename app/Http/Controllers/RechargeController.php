<?php

namespace App\Http\Controllers;

use App\Models\Billing;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\InternetPlan;
use App\Models\Invoice;
use App\Models\Recharge;
use Carbon\Carbon;
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
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internet_plan_id' => 'required|exists:internet_plans,id',
        ]);

        Recharge::makeRecharge(
            $request->customer_id,
            $request->internet_plan_id,
            $request->payment_method,
            $request->transaction_id
        );

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
    public function edit(Recharge $recharge, $customerId)
    {
        $customer = Customer::findOrFail($customerId);
        return view('recharges.edit', compact('recharge', 'customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Recharge $recharge)
    {
        $request->validate([
            'internet_plan' => 'required',
            'expire_date' => 'required',
        ]);

        $recharge->customer_id = $request->customer_id;
        $recharge->internet_plan = $request->internet_plan;
        $recharge->recharge_date = $request->recharge_date;
        $recharge->expire_date = $request->expire_date;
        $recharge->save();

        return redirect()->route('customers.index')->with('success', 'Expiry Date changed successfully.');
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

        GracePeriod::updateOrCreate(
            ['customer_id' => $customerId],
            [
                'grace_days' => $request->grace_days,
                'grace_start_date' => Carbon::now()
            ]
        );

        return back()->with('success', 'Grace updated successfully');
    }
}
