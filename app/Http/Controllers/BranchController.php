<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Branch;
use App\Models\BranchTransaction;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:view branch')->only(['index', 'show']);
        $this->middleware('permission:create branch')->only(['create', 'store', 'addBalance', 'reverse branchTransaction']);
        $this->middleware('permission:edit branch')->only(['edit', 'update']);
        $this->middleware('permission:delete branch')->only(['destroy']);
    }

    public function index()
    {
        $branches = Branch::orderBy('name', 'desc')->paginate(10);
        return view('branch.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('branch.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'contact_number' => 'required|numeric|integer',
        ]);

        $branch = Branch::create([
            'name' => $request->name,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'remarks' => $request->remarks,
        ]);

        Activity::add(
            'Branch Created',
            $branch->name . ' branch has been created',
            'fas fa-code-branch text-success',
            route('branch.index')
        );

        return redirect()->route('branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::findOrFail($id);
        $transactions = $branch->transactions()
            ->latest()
            ->paginate(10);
        return view('branch.show', compact('branch', 'transactions'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        return view('branch.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required',
            'address' => 'required',
            'contact_number' => 'required',
        ]);

        $branch->name = $request->name;
        $branch->address = $request->address;
        $branch->contact_number = $request->contact_number;
        $branch->save();

        Activity::add(
            'Branch Updated',
            $branch->name . ' branch has been updated',
            'fas fa-edit text-primary',
            route('branch.index')
        );

        return redirect()->route('branch.index')->with('success', 'Branch updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $txn = BranchTransaction::findOrFail($id);

        $txn->reverse(); // 🔥 ALL LOGIC INSIDE MODEL

        return back()->with('success', 'Transaction reversed');
    }

    public function addBalance(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'amount' => 'required|numeric|min:1'
        ]);

        $branch = Branch::findOrFail($request->branch_id);

        // 💰 ADD BALANCE
        $branch->addBalance(
            $request->amount,
            'Balance added by admin'
        );

        // 🔔 ACTIVITY LOG
        Activity::add(
            'Branch Balance Updated',
            $branch->name . ' received balance of Rs. ' . $request->amount,
            'fas fa-coins text-success',
            route('branch.show', $branch->id)
        );

        return back()->with('success', 'Balance added successfully');
    }
}
