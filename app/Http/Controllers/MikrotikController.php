<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use Illuminate\Http\Request;

class MikrotikController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view mikrotik')->only(['index', 'show']);
        $this->middleware('permission:create mikrotik')->only(['create', 'store']);
        $this->middleware('permission:edit mikrotik')->only(['edit', 'update']);
        $this->middleware('permission:delete mikrotik')->only(['destroy']);
    }

    public function index()
    {
        $mikrotiks = Mikrotik::orderBy('name', 'desc')->paginate(10);
        return view('mikrotik.index', compact('mikrotiks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('mikrotik.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Mikrotik $mikrotik)
    {
        $request->validate([
            'name' => 'required',
            'host' => 'required',
            'port' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);

        Mikrotik::create([
            'name' => $request->name,
            'host' => $request->host,
            'port' => $request->port,
            'username' => $request->username,
            'password' => $request->password,
        ]);

        return redirect()->route('mikrotik.index')->with('success', 'Mikrotik created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Mikrotik $mikrotik)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mikrotik $mikrotik)
    {
        return view('mikrotik.edit', compact('mikrotik'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mikrotik $mikrotik)
    {
        $mikrotik->update([
            $mikrotik->name = $request->name,
            $mikrotik->host = $request->host,
            $mikrotik->port = $request->port,
            $mikrotik->username = $request->username,
            $mikrotik->password = $request->password,
        ]);

        return redirect()->route('mikrotik.index')->with('success', 'Mikrotik edited successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mikrotik $mikrotik)
    {
        //
    }
}
