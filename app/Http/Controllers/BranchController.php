<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\BranchTransaction;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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

        Branch::create([
            'name' => $request->name,
            'address' => $request->address,
            'contact_number' => $request->contact_number,
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('branch.index')->with('success', 'Branch created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch = Branch::findOrFail($id);
        return view('branch.show', compact('branch'));
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

        $branch->addBalance(
            $request->amount,
            'Balance added by admin'
        );

        return back()->with('success', 'Balance added successfully');
    }
}
