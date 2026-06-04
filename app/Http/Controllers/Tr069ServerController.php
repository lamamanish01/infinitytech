<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTr069ServerRequest;
use App\Http\Requests\UpdateTr069ServerRequest;
use App\Models\Tr069Server;

class Tr069ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function __construct()
    {
        $this->middleware('permission:view acsserver')->only(['index', 'show']);
        $this->middleware('permission:create acsserver')->only(['create', 'store']);
        $this->middleware('permission:edit acsserver')->only(['edit', 'update']);
        $this->middleware('permission:delete acsserver')->only(['destroy']);
    }

    public function index()
    {
        $tr069servers = Tr069Server::get();
        return view('tr069server.index', compact('tr069servers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTr069ServerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Tr069Server $tr069Server)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tr069Server $tr069Server)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTr069ServerRequest $request, Tr069Server $tr069Server)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tr069Server $tr069Server)
    {
        //
    }
}
