<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view billing')->only(['index', 'show']);
        $this->middleware('permission:create billing')->only(['create', 'store']);
        $this->middleware('permission:edit billing')->only(['edit', 'update']);
        $this->middleware('permission:delete billing')->only(['destroy']);
    }

    public function index(Request $request)
    {
        $query = Billing::query()
            ->when($request->search, function ($q, $search) {
                return $q->where('billing_no', 'LIKE', "%{$search}%")
                        ->orWhereHas('customer', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%{$search}%");
                        });
            })
            ->when($request->status, function ($q, $status) {
                return $q->where('status', $status);
            });

        // --- Main table results (ordered by latest billing_date) ---
        $billings = (clone $query)->orderBy('billing_date', 'desc')->paginate(10);

        // --- Summary stats (no order needed) ---
        $summary = (clone $query)
            ->reorder() // remove any ORDER BY
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('AVG(amount) as avg_amount')
            )
            ->first();

        // --- Breakdown by status (no order needed) ---
        $statusCounts = (clone $query)
            ->reorder() // remove ORDER BY
            ->select('status', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as amount'))
            ->groupBy('status')
            ->pluck('amount', 'status')
            ->toArray();

        // --- Monthly totals (order by month) ---
        $monthlyData = (clone $query)
            ->reorder() // remove ORDER BY
            ->select(
                DB::raw("DATE_FORMAT(billing_date, '%Y-%m') as month"),
                DB::raw('SUM(amount) as total')
            )
            ->whereNotNull('billing_date')
            ->groupBy('month')
            ->orderBy('month', 'asc')
            ->pluck('total', 'month')
            ->toArray();

        $monthLabels = [];
        $monthValues = [];
        foreach ($monthlyData as $month => $total) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $month);
            $monthLabels[] = $date->format('M Y');
            $monthValues[] = $total;
        }

        return view('billing.index', compact(
            'billings',
            'summary',
            'statusCounts',
            'monthLabels',
            'monthValues'
        ));
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
    public function update(Request $request, Billing $billing)
    {
        $request->validate([
            'status' => 'required|in:unpaid,paid,partial',
        ]);

        // Capture old status for logging
        $oldStatus = $billing->status;
        $newStatus = $request->status;

        // Update the status
        $billing->status = $newStatus;
        $billing->save();


        Activity::add(
            'Billing Status Updated',
            'Billing #' . $billing->billing_no . ' status changed from ' . ucfirst($oldStatus) . ' to ' . ucfirst($newStatus),
            'fas fa-file-invoice text-warning',
            $billing->billing_no, // or $billing->customer->name ?? 'System'
            route('billing.show', $billing->id)
        );

        return redirect()->route('billing.index')
                        ->with('success', 'Status updated to ' . ucfirst($request->status));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
