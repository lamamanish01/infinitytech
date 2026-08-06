<?php

namespace App\Http\Controllers;

use App\Exports\CustomerExport;
use App\Helpers\Activity;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\InternetPlan;
use App\Models\RadAcct;
use App\Models\Recharge;
use App\Services\MacService;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view customers')->only([
            'index', 'show', 'online', 'expired', 'expiring'
        ]);

        $this->middleware('permission:create customers')->only([
            'create', 'store'
        ]);

        $this->middleware('permission:edit customers')->only([
            'edit', 'update', 'changeExpiry', 'provideGrace',
            'bindMac', 'unbindMac', 'disconnect'
        ]);

        $this->middleware('permission:delete customers')->only([
            'destroy'
        ]);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Customer::with('internetPlan')
            ->where('status', '!=', 'discontinued');

        if (!$user->hasRole('super-admin') && !$user->can('view all customers')) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->q) {
            $query->where(function ($sub) use ($request) {
                $sub->where('username', 'like', "%{$request->q}%")
                    ->orWhere('contact_number', 'like', "%{$request->q}%");
            });
        }

        $customers = $query->paginate(15);

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

        $exists = Customer::where('username', $request->username)->exists();

        if ($exists) {
            return back()->withErrors([
                'username' => 'This username is already in use.'
            ])->withInput();
        }

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
            $customer->username,
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
        ])->findOrFail($id);

        $previousSessions = $customer->previousSession()->paginate(15);

        $billings = $customer->billings()
            ->with('internetPlan')
            ->latest()
            ->paginate(25, ['*'], 'billing_page');

        $authLogs = $customer->authLogs()->latest('authdate')
            ->paginate(10, ['*'], 'auth_page');

        // $lanHosts = $customer->lanHosts()
        //     ->with('device')  // eager load the CPE serial/name
        //     ->paginate(20, ['*'], 'lan_hosts_page');

        $session = get_active_mac($customer->username);

        $lastSession = DB::table('radacct')
            ->where('username', $customer->username)
            ->whereNotNull('acctstoptime')
            ->orderByDesc('radacctid')
            ->first();

        $activityLogs = ActivityLog::where('username', $customer->username)
                                   ->orderBy('created_at', 'desc')
                                   ->paginate(15);
        $unpaidCount = $customer->billings()->where('status', 'unpaid')->count();
        $partialCount = $customer->billings()->where('status', 'partial')->count();

        return view('customers.show', compact(
            'customer',
            'session',
            'lastSession',
            'previousSessions',
            'billings',
            'authLogs',
            'activityLogs',
            'unpaidCount',
            'partialCount',
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(customer $customer)
    {
        $internet_plans = InternetPlan::all();
        $branches = Branch::all();
        return view('customers.edit', compact('customer', 'internet_plans', 'branches'));
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
        $customer->registered_at = $request->registered_at;
        $customer->branch_id = $request->branch_id;
        $customer->address = $request->address;
        $customer->contact_number = $request->contact_number;
        $customer->internet_plan_id = $request->internet_plan_id;
        $customer->save();

        Activity::add(
            'Customer Updated',
            $customer->name . ' details have been updated',
            'fas fa-user-edit text-primary',
            $customer->username ,
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

        $oldExpiry = $customer->expire_date;

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
                ->disableCustomer($customer);

            app(\App\Services\RadiusService::class)
                ->disconnect($customer);

            return redirect()
                ->route('customers.show', $customer->id)
                ->with('error', 'Customer is expired and has been disconnected.');
        }

        $customer->update([
            'status' => 'active'
        ]);

        $oldDate = $oldExpiry ? $oldExpiry->format('Y-m-d') : 'N/A';
        $newDate = $customer->expire_date->format('Y-m-d');
        $activityMessage = "{$customer->name} expiry changed from {$oldDate} to {$newDate}";

        Activity::add(
            'Expiry Date Updated',
            $activityMessage,
            'fas fa-calendar-alt text-info',
            $customer->username,
            route('customers.show', $customer->id)
        );

        app(RadiusService::class)->syncCustomer($customer->fresh());

        return redirect()
            ->route('customers.show', $customer->id)
            ->with('success', 'Expiry Date updated successfully.');
    }

    public function provideGrace(Request $request, $customerId)
    {
        $customer = Customer::findOrFail($customerId);

        if ($customer->expire_date && $customer->expire_date->isFuture()) {
            return back()->with('error', 'Customer is still active. Grace period is only allowed after expiration.');
        }

        $hasEverHadGrace = GracePeriod::where('customer_id', $customerId)->exists();
        if ($hasEverHadGrace) {
            return back()->with('error', 'This customer has already been granted a grace period. Only one is allowed.');
        }

        $start = now();
        $end = $start->copy()->addDays(3);

        DB::transaction(function () use ($customer, $start, $end) {
            GracePeriod::create([
                'customer_id' => $customer->id,
                'grace_days'  => 3,
                'grace_start' => $start,
                'grace_end'   => $end,
            ]);

            $customer->update([
                'status' => 'grace',
            ]);
        });

        app(RadiusService::class)->syncCustomer($customer->fresh());

        Activity::add(
            'Customer in Grace Period',
            $customer->name . ' is now in grace period until ' . $end->toDateString(),
            'fas fa-clock text-warning',
            $customer->username,
            route('customers.show', $customer->id)
        );

        return back()->with('success', 'Grace period activated successfully.');
    }

    public function disconnect($id)
    {
        $customer = Customer::findOrFail($id);

        $mk = app(\App\Services\MikrotikService::class);

        try {

            if (!$customer->username) {
                return back()->with('error', 'Username not found');
            }

            $result = $mk->disconnectPPPoE($customer->username);

            if (!empty($result['status']) && $result['status'] === true) {
                return back()->with('success', $result['message']);
            }

            return back()->with('error', $result['message']);

        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function bindMac($id)
    {
        $customer = Customer::findOrFail($id);

        $mac = MacService::getActiveMac($customer->username);

        if (!$mac) {
            return back()->with('error', 'No active session found');
        }

        MacService::bind($customer, $mac);
        app(RadiusService::class)->syncCustomer($customer->fresh());

        Activity::add(
            'MAC Address Bind',
            $customer->name . ' MAC address has been bound',
            'fas fa-lock text-success',
            $customer->username,
            route('customers.show', $customer->id)
        );

        return back()->with('success', 'MAC Bound Successfully');
    }

    public function unbindMac($id)
    {
        $customer = Customer::findOrFail($id);

        MacService::unbind($customer);

        app(RadiusService::class)->syncCustomer($customer->fresh());

        Activity::add(
            'MAC Address Unbind',
            $customer->name . ' MAC address has been removed',
            'fas fa-unlock text-danger',
            $customer->username,
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
         $customersExpired = Customer::where('status', 'expired')
                ->orderBy('expire_date', 'desc')
                ->paginate(15);

        return view('customers.expired', compact('customersExpired'));
    }

    public function expiring()
    {
        $customersExpiring = Customer::where('status', 'active')
            ->whereBetween('expire_date', [
                now(),
                now()->addDays(3)
            ])
            ->orderBy('expire_date', 'asc')
            ->paginate(15);

        return view('customers.expiring', compact('customersExpiring'));
    }

    public function searchOnline(Request $request)
    {
        $customers = Customer::whereHas('activeSession')->with(['activeSession', 'internetPlan']);

        if ($request->filled('search')) {
            $search = $request->search;
            $customers->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('username', 'LIKE', "%{$search}%")
                      ->orWhereHas('activeSession', function ($q) use ($search) {
                          $q->where('framedipaddress', 'LIKE', "%{$search}%")
                            ->orWhere('callingstationid', 'LIKE', "%{$search}%");
                      });
            });
        }

        if ($request->filled('package')) {
            $customers->where('internet_plan_id', $request->package);
        }

        $customers = $customers->paginate(15);

        $packages = InternetPlan::all();

        return view('customers.online', compact('customers', 'packages'));
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'customers_' . date('Y-m-d') . '.xlsx');
    }

    public function getPppTraffic($username)
    {

        $traffic = MikrotikService::getPPPUserTraffic($username);

        return response()->json([
            'success' => true,
            'rx_bps'  => $traffic['rx_bps'],
            'tx_bps'  => $traffic['tx_bps'],
            'sessions'=> $traffic['sessions'],
        ]);
    }

    public function getDailyTraffic(Customer $customer)
    {
        $end = now();
        $start = $end->copy()->subDays(30);

        // Direct query on the sessions table
        $dailyData = RadAcct::where('username', $customer->username)
            ->whereNotNull('acctstoptime')
            ->whereBetween('acctstoptime', [$start, $end])
            ->selectRaw('DATE(acctstoptime) as date, SUM(acctinputoctets / 1024 / 1024) as total_download, SUM(acctoutputoctets / 1024 / 1024) as total_upload')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->keyBy('date');

        // Fill missing days with zeros
        $dates = collect();
        for ($date = $start->copy(); $date <= $end; $date->addDay()) {
            $key = $date->toDateString();
            $dates->put($key, [
                'date' => $key,
                'download' => $dailyData->has($key) ? (float) $dailyData[$key]->total_download : 0,
                'upload'   => $dailyData->has($key) ? (float) $dailyData[$key]->total_upload : 0,
            ]);
        }

        return response()->json([
            'dates'    => $dates->keys(),
            'download' => $dates->pluck('download'),
            'upload'   => $dates->pluck('upload'),
        ]);
    }

    public function enable(Customer $customer)
    {
        try {
            $customer->update(['status' => 'active']);

            Activity::add(
                'Customer Enabled',
                $customer->name . ' has been activated.',
                'fas fa-check-circle text-success',
                $customer->username,
                route('customers.show', $customer->id)
            );

            app(RadiusService::class)->enableCustomer($customer);

            return back()->with('success', 'Customer enabled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to enable customer.');
        }
    }

    /**
     * Disable a customer (set status to 'suspended' and sync with RADIUS).
     */
    public function disable(Customer $customer)
    {
        try {
            $customer->update(['status' => 'suspended']);

            Activity::add(
                'Customer Disabled',
                $customer->name . ' has been suspended.',
                'fas fa-ban text-danger',
                $customer->username,
                route('customers.show', $customer->id)
            );

            app(RadiusService::class)->suspendCustomer($customer);

            return back()->with('success', 'Customer disabled successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to disable customer.');
        }
    }

    public function continue(Customer $customer)
    {
        try {
            $customer->update([
                'status' => 'active',
            ]);

            Activity::add(
                'Customer Reactivated',
                "{$customer->name} has been reactivated (status: active).",
                'fas fa-play text-success',
                $customer->username,
                route('customers.show', $customer->id)
            );

            app(RadiusService::class)->enableCustomer($customer);

            return back()->with('success', 'Customer reactivated successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to reactivate customer.');
        }
    }

    public function discontinue(Customer $customer)
    {
        try {
            $customer->update(['status' => 'discontinued']);

            Activity::add(
                'Customer Discontinued',
                "{$customer->name} has been permanently discontinued.",
                'fas fa-ban text-danger',
                $customer->username,
                route('customers.show', $customer->id)
            );

            app(RadiusService::class)->discontinueCustomer($customer);

            return back()->with('success', 'Customer discontinued permanently.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to discontinue customer.');
        }
    }

    public function discontinued(Request $request)
    {
        $user = auth()->user();

        $query = Customer::with('internetPlan')
            ->where('status', 'discontinued');

        if (!$user->hasRole('super-admin') && !$user->can('view all customers')) {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($sub) use ($search) {
                $sub->where('username', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $customers = $query->orderBy('updated_at', 'desc')->paginate(15);

        return view('customers.discontinued', compact('customers'));
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        if (strlen($query) < 2) {
            return response()->json(['data' => []]);
        }

        try {
            // Get the table name and columns
            $table = (new Customer)->getTable();
            $columns = Schema::getColumnListing($table);

            // Define the columns we want to search (name and username)
            $searchable = ['name', 'username'];
            $available = array_intersect($searchable, $columns);

            if (empty($available)) {
                throw new \Exception('No searchable columns (name, username) found in the table.');
            }

            // Build the search query
            $customers = Customer::where(function ($q) use ($query, $available) {
                foreach ($available as $col) {
                    $q->orWhere($col, 'LIKE', "%{$query}%");
                }
            })->limit(10)->get();

            // Map results
            $mapped = $customers->map(function ($customer) {
                return [
                    'id'       => $customer->id,
                    'name'     => $customer->name ?? 'Unknown',
                    'username' => $customer->username ?? '',
                    'url'      => route('customers.show', $customer->id) ?? '#',
                ];
            });

            return response()->json(['data' => $mapped]);

        } catch (\Exception $e) {
            // Log the error (optional)
            \Log::error('Customer search error: ' . $e->getMessage());
            // Return a JSON error response
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function loadTab($id, $tab)
    {
        try {
            // Eager load the relationships needed across all tabs
            $customer = Customer::with(['internetPlan', 'activeSession'])->findOrFail($id);
            $data = ['customer' => $customer];

            switch ($tab) {
                case 'overview':
                    $data['grace'] = $customer->activeGrace();
                    // Get the last session (for termination cause)
                    $data['lastSession'] = $customer->previousSession()
                        ->orderBy('acctstoptime', 'desc')
                        ->first();
                    break;

                case 'session':
                    // Previous sessions – ordered by acctstarttime (correct column name)
                    $data['previousSessions'] = $customer->previousSession()
                        ->orderBy('acctstarttime', 'desc')
                        ->paginate(10);
                    // Last session (for termination cause)
                    $data['lastSession'] = $customer->previousSession()
                        ->orderBy('acctstoptime', 'desc')
                        ->first();
                    break;

                case 'router':
                    $data['router'] = $customer->routerDevices->first();
                    $data['server'] = $data['router']?->server;
                    break;

                case 'billing':
                    $data['billings'] = $customer->billings()
                        ->with(['recharge', 'customer.internetPlan'])
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
                    break;

                case 'create-ticket':
                    // No extra data needed for this tab
                    break;

                case 'auth-logs':
                    $data['authLogs'] = $customer->authLogs()
                        ->orderBy('authdate', 'desc')
                        ->paginate(10);
                    break;

                case 'activity-logs':
                    $data['activityLogs'] = $customer->activities()
                        ->with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(10);
                    break;

                default:
                    abort(404, 'Tab not found');
            }

            // Build the view name from the tab (e.g., partials.customer_session_tab)
            $viewName = "partials.customer_{$tab}_tab";

            // Check if the view exists to avoid a fatal error
            if (!view()->exists($viewName)) {
                throw new \Exception("View [{$viewName}] not found.");
            }

            return view($viewName, $data);
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error("AJAX tab load error (customer {$id}, tab {$tab}): " . $e->getMessage());
            // Return a plain text error so the JavaScript can display it
            return response("Error: " . $e->getMessage(), 500);
        }
    }

    public function dailyTraffic($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            $username = $customer->username;

            // Last 30 days (including today)
            $dates = collect();
            for ($i = 29; $i >= 0; $i--) {
                $dates->push(Carbon::now()->subDays($i)->toDateString());
            }

            // Aggregate daily totals from radacct
            $traffic = RadAcct::where('username', $username)
                ->whereBetween('acctstarttime', [Carbon::now()->subDays(30), Carbon::now()])
                ->select(
                    DB::raw('DATE(acctstarttime) as date'),
                    DB::raw('SUM(acctinputoctets) as total_upload'),   // bytes from user (TX)
                    DB::raw('SUM(acctoutputoctets) as total_download') // bytes to user (RX)
                )
                ->groupBy(DB::raw('DATE(acctstarttime)'))
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date');

            // Build response (data in bytes)
            $response = [
                'dates'    => $dates->toArray(),
                'upload'   => [],
                'download' => []
            ];

            foreach ($dates as $date) {
                if ($traffic->has($date)) {
                    $row = $traffic->get($date);
                    $response['upload'][]   = (int) $row->total_upload;
                    $response['download'][] = (int) $row->total_download;
                } else {
                    $response['upload'][]   = 0;
                    $response['download'][] = 0;
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error("Daily traffic error for customer $id: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function pppTraffic($username)
    {
        try {
            $traffic = MikrotikService::getPPPUserTraffic($username);

            return response()->json([
                'success' => true,
                'rx_bps'  => $traffic['rx_bps'] ?? 0,
                'tx_bps'  => $traffic['tx_bps'] ?? 0,
            ]);
        } catch (\Exception $e) {
            \Log::error("PPP traffic error for $username: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function onlineStatus($id)
    {
        try {
            $customer = Customer::findOrFail($id);
            // Check if there's an active RADIUS session
            $isOnline = $customer->activeSession()->exists();
            // Update the database (optional, but keeps consistency)
            $customer->is_online = $isOnline;
            $customer->save();

            return response()->json(['is_online' => $isOnline]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
