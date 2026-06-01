<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Dashboard;
use App\Models\InternetPlan;
use App\Models\Nas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $onlineCustomers = Customer::whereHas('activeSession', function ($q) {
            $q->whereNull('acctstoptime')
            ->where('acctupdatetime', '>=', now()->subMinutes(15));
        })->distinct()->count();

        $expiringCustomers = Customer::where('status', 'active')
            ->whereBetween('expire_date', [
                now(),
                now()->addDays(3)
            ])->count();

        return view('dashboard.index', [
            'onlineCustomers'   => $onlineCustomers,
            'totalCustomers'    => Customer::count(),

            // 🔥 REPLACED THIS LINE
            'expiringCustomers' => $expiringCustomers,

            'expiredCustomers'  => Customer::where('status', 'expired')->count(),

            'branchBalance'     => Branch::sum('balance'),

            'activeSessions'    => DB::table('radacct')
                ->whereNull('acctstoptime')
                ->whereNotNull('username')
                ->distinct()
                ->count('username'),

            'nasCount' => Nas::count(),
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
