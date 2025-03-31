<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class NasController extends Controller
{
    protected $clientsConfPath = '/etc/freeradius/3.0/clients.conf';

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

        return redirect()->route('nas.index')->with('success', 'NAS created successfully.');
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
    public function edit(Nas $na)
    {
        return view('nas.edit', compact('na'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Nas $na)
    {
        $request->validate([
            'name' => 'required',
            'ipaddress' => 'required',
            'secret' => 'required',
        ]);

        $na->shortname = $request->name;
        $na->nasname = $request->ipaddress;
        $na->secret = $request->secret;
        $na->type = $request->type;
        $na->ports = $request->ports;
        $na->description = $request->description;
        $na->save();

        return redirect()->route('nas.index')->with('success', 'NAS updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
