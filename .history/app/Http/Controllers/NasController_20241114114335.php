<?php

namespace App\Http\Controllers;

use App\Models\NAS;
use Illuminate\Http\Request;

class NasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('nas.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('nas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'ipaddress' => 'required',
            'secret' => 'required',
        ]);
        // $ports = $request->ports ?? 3799;
        $ports = !empty($request->ports) ? $request->ports : 3799;

        NAS::create([
            'nasname' => $request->ipaddress,
            'shortname' => $request->nasname,
            'secret' => $request->secret,
            'type' => $request->type,
            'ports' => $request->ports,
            'description' => $request->description
        ]);

        return redirect()->route('nas.create')->with('success', 'NAS created successfully.');
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
