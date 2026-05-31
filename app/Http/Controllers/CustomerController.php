<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\InternetPlan;
use App\Models\Recharge;
use App\Services\MacService;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::with(['internetPlan']);

        if ($request->filled('q')) {

            $q = $request->q;

            $query->where(function ($sub) use ($q) {
                $sub->where('username', 'like', "%{$q}%")
                    ->orWhere('name', 'like', "%{$q}%")
                    ->orWhere('contact_number', 'like', "%{$q}%")
                    ->orWhere('mac_address', 'like', "%{$q}%")
                    ->orWhere('status', 'like', "%{$q}%");
            });
        }

        $customers = $query->paginate(10);

        return view('customers.index', compact('customers'));


        // $customers = Customer::orderBy('name', 'ASC')->paginate(10);
        // return view('customers.index', compact('customers'));
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

        Activity::add(
            'Customer Created',
            $customer->name . ' has been created successfully',
            'fas fa-user-plus text-success',
            route('customers.show', $customer->id)
        );

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $customer = Customer::with([
            'activeSession',
            'internetPlan',
            'billings.internetPlan',
            'authLogs' => function ($q) {
                $q->orderByDesc('authdate')->limit(25);
            },
        ])->findOrFail($id);

        $session = get_active_mac($customer->username);

        return view('customers.show', compact('customer', 'session'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customer $customer)
    {
        $internet_plans = InternetPlan::all();
        return view('customers.edit', compact('customer', 'internet_plans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, customer $customer)
    {
        $request->validate([
            'internet_plan_id' => 'required',
        ]);

        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->address = $request->address;
        $customer->contact_number = $request->contact_number;
        $customer->internet_plan_id = $request->internet_plan_id;
        $customer->save();

        Activity::add(
            'Customer Updated',
            $customer->name . ' details have been updated',
            'fas fa-user-edit text-primary',
            route('customers.show', $customer->id)
        );

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

        $newExpiry = Carbon::parse($request->expire_date)->endOfDay();
        $now = now();

        $customer->update([
            'expire_date' => $newExpiry,
        ]);

        $latestRecharge = Recharge::where('customer_id', $customer->id)
            ->latest()
            ->first();

        if ($latestRecharge) {
            $latestRecharge->update([
                'expire_date' => $newExpiry
            ]);
        }

        if ($newExpiry->lessThan($now)) {

            $customer->update([
                'status' => 'expired'
            ]);

            app(\App\Services\RadiusService::class)
                ->removeCustomer($customer);

            app(\App\Services\RadiusService::class)
                ->disconnect($customer);

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('error', 'Customer is expired and has been disconnected.');
        }

        $customer->update([
            'status' => 'active'
        ]);

        Activity::add(
            'Expiry Date Updated',
            $customer->name . ' expiry changed to ' . $customer->expire_date,
            'fas fa-calendar-alt text-info',
            route('customers.show', $customer->id)
        );

        RadiusService::syncCustomer($customer->fresh());

        return redirect()
            ->route('customers.show', $customer->id)
            ->with('success', 'Expiry Date updated successfully.');
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

        Activity::add(
            'Customer in Grace Period',
            $customer->name . ' is now in grace period',
            'fas fa-clock text-warning',
            route('customers.show', $customer->id)
        );

        RadiusService::syncCustomer(
            $customer->fresh()
        );

        return back()->with('success', 'Grace updated successfully');
    }

    public function disconnect($id)
    {
        $customer = Customer::findOrFail($id);

        $result = MikrotikService::disconnectPPPoE(
            $customer->username
        );

        if ($result['status']) {

            return back()->with(
                'success',
                $result['message']
            );
        }

        return back()->with(
            'error',
            $result['message']
        );
    }

    public function bindMac($id)
    {
        $customer = Customer::findOrFail($id);

        $mac = MacService::getActiveMac($customer->username);

        if (!$mac) {
            return back()->with('error', 'No active session found');
        }

        MacService::bind($customer, $mac);
        RadiusService::syncCustomer($customer);

        Activity::add(
            'MAC Address Bind',
            $customer->name . ' MAC address has been bound',
            'fas fa-lock text-success',
            route('customers.show', $customer->id)
        );

        return back()->with('success', 'MAC Bound Successfully');
    }

    public function unbindMac($id)
    {
        $customer = Customer::findOrFail($id);

        MacService::unbind($customer);
        RadiusService::syncCustomer($customer);

        Activity::add(
            'MAC Address Unbind',
            $customer->name . ' MAC address has been removed',
            'fas fa-unlock text-danger',
            route('customers.show', $customer->id)
        );

        return back()->with('success', 'MAC Unbound Successfully');
    }

    public function online()
    {
        $customers = Customer::with([
                'internetPlan',
                'activeSession'
            ])
            ->online()
            ->latest()
            ->paginate(20);

        return view('customers.online', compact('customers'));
    }

    public function expired()
    {
        $customers = Customer::where('status', 'expired')
            ->latest()
            ->paginate(25);

        return view('customers.expired', compact('customers'));
    }
}
