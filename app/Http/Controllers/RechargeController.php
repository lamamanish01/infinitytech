<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\Recharge;
use App\Services\RadiusService;
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
            'expire_date' => 'required|date',
        ]);

        $recharge->expire_date = $request->expire_date;

        $customer = Customer::findOrFail($recharge->customer_id);
        $customer->update([
            'expire_date' => Carbon::parse($request->expire_date),
        ]);

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

        $customer = Customer::findOrFail($customerId);

        $base = $customer->expire_date
            ? Carbon::parse($customer->expire_date)
            : now();

        $newExpire = $base->addDays((int) $request->grace_days);

        GracePeriod::updateOrCreate(
            ['customer_id' => $customerId],
            [
                'grace_days' => $request->grace_days,
                'grace_start' => Carbon::now(),
                'grace_end' => $newExpire
            ]
        );

        $customer->update([
            'expire_date' => $newExpire,
            'status' => 'active'
        ]);

        RadiusService::syncCustomer(
            $customer->fresh()
        );

        return back()->with('success', 'Grace updated successfully');
    }
}
