<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTr069ServerRequest;
use App\Http\Requests\UpdateTr069ServerRequest;
use App\Models\Tr069Server;
use Illuminate\Http\Request;

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
    public function show(Request $request, $id)
    {
        $tr069Server = Tr069Server::withCount([
            'devices', // total count
            'devices as online_count' => function ($query) {
                $query->where('status', 'online');
            },
            'devices as offline_count' => function ($query) {
                $query->where('status', 'offline');
            },
        ])->findOrFail($id);

        // 2. Get the search term from the request
        $search = $request->input('search');

        // 3. Build the device query with search filter (across ALL pages)
        $devices = $tr069Server->devices()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('serial', 'like', "%{$search}%")
                      ->orWhere('ppp_username', 'like', "%{$search}%")
                      ->orWhere('manufacturer', 'like', "%{$search}%")
                      ->orWhere('product_class', 'like', "%{$search}%")
                      ->orWhere('oui', 'like', "%{$search}%")
                      ->orWhere('mac_address', 'like', "%{$search}%")
                      ->orWhere('router_mac', 'like', "%{$search}%")
                      ->orWhere('ip_address', 'like', "%{$search}%");
                });
            })
            ->paginate(15) // 15 per page
            ->appends(['search' => $search]); // Preserve search term in pagination links

        // 4. Pass both variables to the view
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
