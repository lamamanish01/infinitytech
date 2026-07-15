<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchTransaction;
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

        $totalBalance = BranchTransaction::where('is_void', false)
        ->selectRaw('SUM(IF(type = "credit", amount, -amount)) as balance')
        ->value('balance') ?? 0;

    // Remaining balance = same but also exclude transactions that have been reversed
    // (i.e., exclude IDs that appear in reversal_of column of another transaction)
    $reversedIds = BranchTransaction::whereNotNull('reversal_of')
        ->pluck('reversal_of')
        ->unique();

    $totalAlloted = BranchTransaction::where('is_void', false)
        ->whereNotIn('id', $reversedIds)
        ->selectRaw('SUM(IF(type = "credit", amount, -amount)) as balance')
        ->value('balance') ?? 0;

        $totalBalance = BranchTransaction::where('is_void', false)
    ->selectRaw('SUM(IF(type = "credit", amount, -amount)) as balance')
    ->value('balance') ?? 0;

        return view('dashboard.index', [
            'onlineCustomers'   => $onlineCustomers,
            'totalCustomers'    => Customer::count(),
            'expiringCustomers' => $expiringCustomers,
            'expiredCustomers'  => $expiredCustomers,
            'totalBalance'      => Branch::sum('balance'),
            'activeSessions'    => $activeSessions,
            'nasCount'          => Nas::count(),
            'stats'             => $stats,
            'totalAlloted'     => $totalAlloted
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
