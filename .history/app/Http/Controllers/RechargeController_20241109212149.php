<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
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
        //$internetplans = InternetPlan::get();
        return view('recharges.create', compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // $validatedData = $request->validate([
        //     'customer_id' => 'required|exists:customers,id',
        //     'plan_id' => 'required|exists:plans,id',
        // ]);

        $customer = Customer::findOrFail('customer_id');
        $internetplan = Plan::findOrFail('internetplan_id');
        $rechargeDate = Carbon::now();

        // Retrieve the latest recharge for this customer, if any
        $latestRecharge = $customer->latestRecharge;

        // Calculate the new expiry date
        if ($latestRecharge && !$latestRecharge->isExpired()) {
            // If the latest recharge is still active, extend from the existing expiry date
            $expiryDate = Carbon::parse($latestRecharge->expiry_date)->addMonths($internetplan->duration);
        } else {
            // If there is no active recharge or the latest one has expired, start from today
            $expiryDate = $rechargeDate->copy()->addMonths($internetplan->duration);
        }

        // Optionally add a grace period if requested and not previously added
        $grace_period = null;
        if ($request->has('provide_grace') && (!$latestRecharge || !$latestRecharge->hasGracePeriod())) {
            $grace_period = $expiryDate->copy()->addDays(3); // 3-day grace period
        }

        // Create the new recharge record
        $recharge = new Recharge();
        $recharge->customer_id = $customer->id;
        $recharge->internet_plan_id = $internetplan->id;
        $recharge->
        // CustomerRecharge::create([
        //     'customer_id' => $customer->id,
        //     'internet_plan_id' => $internetplan->id,
        //     'recharge_date' => $rechargeDate,
        //     'expiry_date' => $expiryDate,
        //     'grace_period' => $grace_period,
        // ]);

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
}
