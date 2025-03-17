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
    public function store(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);
        $internetplan = InternetPlan::find($request->internetplan_id);

        $rechargeDate = Carbon::now();

        $expiryDate = $customer->NewExpiryDate($internetplan->duration);
        $grace_period = null;

        if($request->has('provide_grace')) {
            $grace_period = $customer->gracePeriod($expiryDate);
        }

        $recharge = new Recharge();
        $recharge->customer_id = $customer->id;
        $recharge->internet_plan_id = $internetplan->id;
        $recharge->recharge_date = $rechargeDate;
        $recharge->expire_date = $expiryDate;
        $recharge->grace_period = $grace_period;
        $recharge->save();


        $billing = Billing::create([
            'customer_id' => $customer->id,
            'recharge_id' => $recharge->id,
            'billing_date' => Carbon::now(),
            'internet_plan_id' => $internetplan->id,
            'amount' => $internetplan->price,
        ]);

        Invoice::create([
            'billing_id' => $billing->id,
            'invoice_date' => Carbon::now(),
            'status' => 'unpaid',
            'amount' => $internetplan->price
        ]);

        return redirect()->route('customers.index')->with('success', 'Recharge successful' . ($grace_period ? ' with grace period added!' : '!'));
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
            'grace_days' => 'required|integer|min:0'
        ]);

        $gracePeriod = $request->grace_days;

        GracePeriod::create([
            'customer_id' => $customerId,
            'grace_days' => $gracePeriod
        ]);

        return redirect()->route('customers.show', $customer->id)->with('success', 'Grace is now available for 3 days.');
    }
}
