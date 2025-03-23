<?php

namespace App\Http\Controllers;

use App\Models\Bandwidth;
use Illuminate\Http\Request;

class BandwidthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bandwidths = Bandwidth::orderBy('name', 'desc')->paginate(10);
        return view('bandwidth.index', compact('bandwidths'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bandwidth.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'upload_rate' => 'required',
            'download_rate' => 'required',
        ]);

        Bandwidth::create([
            'name' => $request->name,
            'upload_rate' => $request->upload_rate,
            'download_rate' => $request->download_rate
        ]);

        return redirect()->route('bandwidth.index')->with('success', 'Bandwidth created successfully.');
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
    public function edit(Bandwidth $bandwidth)
    {
        return view('bandwidth.edit', compact('bandwidth'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bandwidth $bandwidth)
    {
        $request->validate([
            'name' => 'required',
            'upload_rate' => 'required',
            'download_rate' => 'required',
        ]);

        $bandwidth->name = $request->name;
        $bandwidth->upload_rate = $request->upload_rate;
        $bandwidth->download_rate = $request->download_rate;
        $bandwidth->save();

        return redirect()->route('bandwidth.index')->with('success', 'Bandwidth updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
