<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\Nas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view dashboard')->only(['index']);
    }

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | ONLINE CUSTOMERS (REAL SESSION CHECK)
        |--------------------------------------------------------------------------
        */
        $onlineCustomers = Customer::whereHas('activeSession', function ($q) {
            $q->whereNull('acctstoptime')
              ->where('acctupdatetime', '>=', now()->subMinutes(15));
        })->distinct()->count();

        /*
        |--------------------------------------------------------------------------
        | EXPIRING SOON (NEXT 3 DAYS)
        |--------------------------------------------------------------------------
        */
        $expiringCustomers = Customer::where('status', 'active')
            ->whereNotNull('expire_date')
            ->whereBetween('expire_date', [
                now(),
                now()->addDays(3)
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | EXPIRED CUSTOMERS (FIXED)
        |--------------------------------------------------------------------------
        | Handles case + whitespace issues
        |--------------------------------------------------------------------------
        */
        $expiredCustomers = Customer::whereRaw(
            'LOWER(TRIM(status)) = ?',
            ['expired']
        )->count();

        /*
        |--------------------------------------------------------------------------
        | ACTIVE SESSIONS (RADIUS)
        |--------------------------------------------------------------------------
        */
        $activeSessions = DB::table('radacct')
            ->whereNull('acctstoptime')
            ->whereNotNull('username')
            ->distinct()
            ->count('username');

        $stats = (new ServerStatsController)->getStats();

        $user = auth()->user();
        $branch = Branch::find($user->branch_id);

        $branchBalance = $branch->balance ?? 0;
        $totalBalance = Branch::sum('balance');

        return view('dashboard.index', [
            'onlineCustomers'   => $onlineCustomers,
            'totalCustomers'    => Customer::count(),
            'expiringCustomers' => $expiringCustomers,
            'expiredCustomers'  => $expiredCustomers,
            'totalBalance'      => $totalBalance,
            'activeSessions'    => $activeSessions,
            'nasCount'          => Nas::count(),
            'stats'             => $stats,
            'branchBalance'     => $branchBalance
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Dashboard $dashboard)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Dashboard $dashboard)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dashboard $dashboard)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dashboard $dashboard)
    {
        //
    }
}
