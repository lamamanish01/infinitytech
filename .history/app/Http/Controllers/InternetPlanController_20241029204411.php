<?php

namespace App\Http\Controllers;

use App\Models\Bandwidth;
use Illuminate\Http\Request;
use App\Models\InternetPlanType;

class InternetPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bandwidth = Bandwidth::pluck('name')->implode('');
        $plan_type = InternetPlanType::pluck('type_name', 'id')->implode('');
        return view('internetplan.create', compact('bandwidth', 'plan_type'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'bandwidth_name' => 'required',
            'price' => 'required',
            'duration' => 'required',
            'type' => 'required',
        ]);

        InternetPlan::create([
            'name' => $request->name,
            'bandwidth' => $request->bandwidth,
            ''
        ])
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
