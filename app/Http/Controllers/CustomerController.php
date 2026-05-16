<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\InternetPlan;
use App\Models\Recharge;
use App\Services\RadiusService;
use Carbon\Carbon;
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
        $internet_plans = InternetPlan::all();
        return view('customers.create', compact('branches', 'internet_plans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string',
            'username' => 'required|unique:customers,username',
            'password' => 'required',
            'contact_number' => 'required|numeric|min:12',
            'internet_plan_id' => 'required',
            'branch_id' => 'required',
        ]);

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'username' => $request->username,
            'password' => $request->password,
            'internet_plan_id' => $request->internet_plan_id,
            'branch_id' => $request->branch_id,
            'expire_date' => Carbon::now(),
            'registered_at' => Carbon::now(),
            'user_id' => auth()->id(),
        ]);

        RadiusService::syncCustomer(
            $customer->fresh()
        );

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customer = Customer::with(['activeSession'])->findOrFail($id);
        $authLogs = $customer->recentAuthLogs();
        $billings = $customer->all();

        return view('customers.show', compact('customer', 'authLogs', 'billings'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customer $customer)
    {
        $internetplans = InternetPlan::orderBy('bandwidth_name', 'ASC')->get();
        return view('customers.edit', compact('customer', 'internetplans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, customer $customer)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'email|unique:customers,email',
            'address' => 'required',
            'contact_number' => 'required|numeric|min:12',
            'username' => 'required|unique:customers,username',
            'password' => 'required',
            'internet_plan_id' => 'required',
            'branch_id' => 'required',
        ]);

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->contact_number = $request->contact_number;
        $customer->username = $request->username;
        $customer->password = $request->password;
        $customer->internet_plan_id = $request->internet_plan_id;

        return redirect()->route('customers.index')->with('success', 'Customer edited successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function expiryForm(Customer $customer)
    {
        return view('customers.expiry', compact('customer'));
    }

    public function changeExpiry(Request $request, Customer $customer)
    {
        $request->validate([
            'expire_date' => 'required|date'
        ]);

        $newExpiry = Carbon::parse(
            $request->expire_date
        )->toDateString();

        $customer->update([
            'expire_date' => $newExpiry,
            'status' => 'active'
        ]);

        $latestRecharge = Recharge::where(
            'customer_id',
            $customer->id
        )->latest()->first();

        if ($latestRecharge) {

            $latestRecharge->update([
                'expire_date' => $newExpiry
            ]);
        }

        RadiusService::syncCustomer(
            $customer->fresh()
        );

        return redirect()->route('customers.index')->with('success', 'Expiry Date changed successfully.');
    }

    public function provideGrace(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);

        $existing = GracePeriod::where('customer_id', $customerId)
            ->where('grace_end', '>=', now())
            ->first();

        if ($existing) {
            return back()->with('error', 'Grace already active for this customer');
        }

        $start = now();
        $end = $start->copy()->addDays(3);

        GracePeriod::updateOrCreate(
            [
                'customer_id' => $customerId,
                'grace_days' => 3,
                'grace_start' => $start,
                'grace_end' => $end
            ]
        );

        $customer->update([
            'status' => 'grace'
        ]);

        RadiusService::syncCustomer(
            $customer->fresh()
        );

        return back()->with('success', 'Grace updated successfully');
    }

    public function disconnect($id)
    {
        $customer = Customer::findOrFail($id);

        $result = RadiusService::disconnect($customer);

        if ($result['status']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

    public function forceDisconnect($id)
    {
        $customer = Customer::findOrFail($id);

        $result = RadiusService::forceDisconnect($customer);

        if ($result['status']) {
            return back()->with('success', $result['message']);
        }

        return back()->with('error', $result['message']);
    }

}
