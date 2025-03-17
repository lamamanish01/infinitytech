<?php

namespace App\Http\Controllers;

use App\Models\Bandwidth;
use App\Models\InternetPlan;
use Illuminate\Http\Request;
use App\Models\InternetPlanType;

class InternetPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $internetplans = InternetPlan::orderBy('name', 'desc')->paginate(10);
        return view('internetplan.index', compact('internetplans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $bandwidths = Bandwidth::pluck('name', 'id');
        $plan_types = InternetPlanType::pluck('type_name', 'id');

        return view('internetplan.create', compact('bandwidths', 'plan_types'));
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
            'bandwidth_name' => $request->bandwidth_name,
            'price' => $request->price,
            'duration' => $request->duration,
            'type' => $request->type,
        ]);

        return redirect()->route('internetplan.index')->with('success', 'Internet Plan created successfully.');
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
    public function edit(Internetplan $internetplan)
    {
        $bandwidth = Bandwidth::get();
        $hasBandwidths = $intenetplan->bandwidth->pluck('name');
        return view('internetplan.edit', compact('internetplan', 'hasbandwidths'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internetplan $internetplan)
    {
        $request->validate([
            'name' => 'required',
            'bandwidth_name' => 'required',
            'price' => 'required',
            'duration' => 'required',
            'type' => 'required',
        ]);

        $internetplan->name = $request->name;
        $internetplan->bandwidth_name = $request->bandwidth_name;
        $internetplan->price = $request->price;
        $internetplan->duration = $request->duration;
        $internetplan->type = $request->type;
        $internetplan->save();

        return redirect()->route('internetplan.index')->with('success', 'Internet Plan created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
