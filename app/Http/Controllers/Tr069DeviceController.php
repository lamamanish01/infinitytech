<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTr069DeviceRequest;
use App\Http\Requests\UpdateTr069DeviceRequest;
use App\Models\Tr069Device;
use App\Models\Tr069Server;
use Illuminate\Http\Request;

class Tr069DeviceController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view tr069')->only(['index', 'show']);
        $this->middleware('permission:create tr069')->only(['create', 'store']);
        $this->middleware('permission:edit tr069')->only(['edit', 'update']);
        $this->middleware('permission:delete tr069')->only(['destroy']);
    }

    /**
     * Display a listing of devices.
     */
    public function index(Request $request)
    {
        $query = Tr069Device::with('server', 'customer');

        // Filter by server
        if ($request->filled('server_id')) {
            $query->where('tr069_server_id', $request->server_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by serial, mac, or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial_number', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $devices = $query->latest('last_inform')->paginate(20);
        $servers = Tr069Server::where('status', 'active')->get();

        return view('tr069.devices.index', compact('devices', 'servers'));
    }

    /**
     * Show form for creating a new device.
     */
    public function create()
    {
        $servers = Tr069Server::where('status', 'active')->get();
        return view('tr069.devices.create', compact('servers'));
    }

    /**
     * Store a newly created device.
     */
    public function store(StoreTr069DeviceRequest $request)
    {
        $validated = $request->validated();

        Tr069Device::create($validated);

        return redirect()->route('tr069.devices.index')
            ->with('success', 'Device created successfully.');
    }

    /**
     * Display the specified device.
     */
    public function show(Tr069Device $tr069Device)
    {
        $tr069Device->load('server', 'customer');
        return view('tr069.devices.show', compact('tr069Device'));
    }

    /**
     * Show form for editing a device.
     */
    public function edit(Tr069Device $tr069Device)
    {
        $servers = Tr069Server::where('status', 'active')->get();
        return view('tr069.devices.edit', compact('tr069Device', 'servers'));
    }

    /**
     * Update the specified device.
     */
    public function update(UpdateTr069DeviceRequest $request, Tr069Device $tr069Device)
    {
        $validated = $request->validated();

        // Prevent changing serial_number – it's the unique identifier
        unset($validated['serial_number']);

        $tr069Device->update($validated);

        return redirect()->route('tr069.devices.index')
            ->with('success', 'Device updated successfully.');
    }

    /**
     * Remove the specified device.
     */
    public function destroy(Tr069Device $tr069Device)
    {
        $tr069Device->delete();

        return redirect()->route('tr069.devices.index')
            ->with('success', 'Device deleted successfully.');
    }
}
