<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTr069ServerRequest;
use App\Http\Requests\UpdateTr069ServerRequest;
use App\Models\Tr069Server;

class Tr069ServerController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view acsserver')->only(['index', 'show']);
        $this->middleware('permission:create acsserver')->only(['create', 'store']);
        $this->middleware('permission:edit acsserver')->only(['edit', 'update']);
        $this->middleware('permission:delete acsserver')->only(['destroy']);
    }

    /**
     * Display a listing of servers.
     */
    public function index()
    {
        $tr069Servers = Tr069Server::withCount('devices')
            ->latest()
            ->get();

        $totalServers = $tr069Servers->count();
        $activeServers = $tr069Servers->where('status', 'active')->count();
        $downServers = $tr069Servers->where('status', '!=', 'active')->count();

        return view('tr069server.index', compact(
            'tr069Servers',
            'totalServers',
            'activeServers',
            'downServers'
        ));
    }

    /**
     * Show form for creating a new server.
     */
    public function create()
    {
        return view('tr069server.create');
    }

    /**
     * Store a newly created server.
     */
    public function store(StoreTr069ServerRequest $request)
    {
        Tr069Server::create([
            'name'          => $request->name,
            'acs_url'       => $request->acs_url,
            'acs_username'  => $request->acs_username,
            'acs_password'  => $request->acs_password,
            'status'        => $request->status ?? 'active',
        ]);

        return redirect()
            ->route('tr069server.index')
            ->with('success', 'Server created successfully');
    }

    /**
     * Display the specified server with its devices.
     */
    public function show($id)
    {
        $tr069Server = Tr069Server::findOrFail($id);
        $devices = $tr069Server->devices()->paginate(10);

        return view('tr069server.show', compact('tr069Server', 'devices'));
    }

    /**
     * Show form for editing a server.
     */
    public function edit(Tr069Server $tr069Server)
    {
        // Fixed view path: was 'tr069.servers.edit' -> now 'tr069server.edit'
        return view('tr069server.edit', compact('tr069Server'));
    }

    /**
     * Update the specified server.
     */
    public function update(UpdateTr069ServerRequest $request, Tr069Server $tr069Server)
    {
        $tr069Server->update([
            'name'          => $request->name,
            'acs_url'       => $request->acs_url,
            'acs_username'  => $request->acs_username,
            'acs_password'  => $request->acs_password,
            'status'        => $request->status,
        ]);

        return redirect()
            ->route('tr069server.index')
            ->with('success', 'Server updated successfully');
    }

    /**
     * Remove the specified server.
     */
    public function destroy($id)  // Fixed: accept $id, not the model binding
    {
        $server = Tr069Server::findOrFail($id);
        $server->delete();

        return redirect()
            ->route('tr069server.index')
            ->with('success', 'Server deleted successfully');
    }
}
