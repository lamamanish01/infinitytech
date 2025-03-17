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
        $customer = Customer::findOrFail($request->customer_id);
        $internetplan = InternetPlan::find($request->internetplan_id);

        $rechargeDate = Carbon::now();

        $latestRecharge = $customer->latestRecharge;
        $internetplans = $internetplan->duration;

        if($latestRecharge && !$latestRecharge->isExpired()) {
            $expiryDate = Carbon::parse($latestRecharge->exipryDate)->addMonths($internetplans);
        } else {
            $expiryDate = $rechargeDate->copy()->addMonths($internetplans);
        }
        $grace_period = null;

        if($request->has('provide_grace') && (!$latestRecharge || $latestRecharge->hasGracePeriod())) {
            $grace_period = $expiryDate->copy()->addDays(3);
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
