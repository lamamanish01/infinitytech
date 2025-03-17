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
        $nases = Nas::orderBy('nasname', 'asc')->paginate(10);
        return view('nas.index', compact('nases'));
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

        $ports = !empty($request->ports) ? $request->ports : 3799;

        NAS::create([
            'nasname' => $request->ipaddress,
            'shortname' => $request->name,
            'secret' => $request->secret,
            'type' => $request->type,
            'ports' => $ports,
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
    public function edit(Nas $nas)
    {
        return view('nas.edit', compact('nas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nas $nas)
    {
        $request->validate([
            'name' => 'required',
            'ipaddress' => 'required',
            'secret' => 'required',
        ]);

        $nas->name = $request->name;
        $nas->ipaddress = $request->ipaddress;
        $nas->secret = $request->secret;
        $nas->ports = $request->ports;
        $nas->descrip
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
