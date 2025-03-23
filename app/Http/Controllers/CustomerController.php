<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Branch;
use App\Models\RadAcct;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\InternetPlan;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $customers = Customer::orderBy('name', 'ASC')->paginate(10);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::orderBy('name', 'ASC')->get();
        $internetplans = InternetPlan::orderBy('bandwidth_name', 'ASC')->get();
        return view('customers.create', compact('branches', 'internetplans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'email|unique:customers,email',
            'address' => 'required',
            'contact_number' => 'required|numeric|min:12',
            'username' => 'required|unique:customers,username',
            'password' => 'required',
            'internetplan' => 'required',
            'branch' => 'required',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'username' => $request->username,
            'password' => $request->password,
            'internetplan' => $request->internetplan,
            'branch' => $request->branch,
            'expired' => Carbon::now(),
            'registered' => Carbon::now(),
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        $activeSessions = $customer->showActiveSessionDetails();
        $authLogs = $customer->recentAuthLogs();
        $billings = $customer->getCustomerBilling();

        foreach ($activeSessions as $session) {
            $seconds = $session->session_time;

            $days = floor($seconds / 86400);
            $hours = floor(($seconds % 86400) / 3600);
            $minutes = floor(($seconds % 3600) / 60);

            $session->formatted_time = "{$days} days {$hours} h {$minutes} m";
        }

        return view('customers.show', compact('customer', 'activeSessions', 'authLogs', 'billings'));
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
