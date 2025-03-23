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
        $plan_types = InternetPlanType::pluck('type_name', 'id');

        return view('internetplan.create', compact('plan_types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'duration' => 'required',
            'type' => 'required',
            'rate_limit' => 'required',
        ]);

        InternetPlan::create([
            'name' => $request->name,
            'bandwidth_name' => 'FTTX'. '-' . $request->name. '-' . $request->duration. '' . $request->type,
            'price' => $request->price,
            'duration' => $request->duration,
            'type' => $request->type,
            'rate_limit' => $request->rate_limit,
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
        $plan_types = InternetPlanType::pluck('type_name', 'id');

        return view('internetplan.edit', compact('internetplan', 'plan_types'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Internetplan $internetplan)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'duration' => 'required',
            'type' => 'required',
        ]);

        $internetplan->name = $request->name;
        $internetplan->bandwidth_name = 'FTTX'. '-' . $request->name. '-' . $request->duration. '' . $request->type;
        $internetplan->price = $request->price;
        $internetplan->duration = $request->duration;
        $internetplan->type = $request->type;
        $internetplan->save();

        return redirect()->route('internetplan.index')->with('success', 'Internet Plan created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InternetPlan $internetplan)
    {
        $internetplan->delete();

        return redirect()->route('internetplan.index')->with('success', 'Internet Plan deleted successfully.');
    }
}
