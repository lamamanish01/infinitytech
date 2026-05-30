<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Customer;
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
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'internet_plan_id' => 'required|exists:internet_plans,id',
            'payment_method' => 'nullable',
            'transaction_id' => 'nullable|unique:recharges,transaction_id',
        ]);

        try {
            $recharge = Recharge::makeRecharge(
                $request->customer_id,
                $request->internet_plan_id,
                $request->payment_method,
                $request->transaction_id
            );

            $customer = Customer::findOrFail($recharge->customer_id);

            Activity::add(
                'Recharge Added',
                $customer->name . ' recharged Rs. ' . $recharge->price,
                'fas fa-money-bill text-success',
                route('customers.show', $customer->id)
            );

        return redirect()->route('customers.show', $customer->id)->with('success', 'Recharge successful');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
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
        // $request->validate([
        //     'expire_date' => 'required|date',
        // ]);

        // $recharge->expire_date = $request->expire_date;

        // $customer = Customer::findOrFail($recharge->customer_id);
        // $customer->update([
        //     'expire_date' => Carbon::parse($request->expire_date),
        // ]);

        // return redirect()->route('customers.show', $customer->id)->with('success', 'Expiry Date changed successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
