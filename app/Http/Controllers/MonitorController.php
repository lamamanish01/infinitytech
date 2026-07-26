<?php

namespace App\Http\Controllers;

use App\Models\Monitor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MonitorController extends Controller
{
    public function index()
    {
        $monitors = Monitor::latest()->get();
        return view('monitors.index', compact('monitors'));
    }

    public function create()
    {
        return view('monitors.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'host'            => 'required|string|max:255|unique:monitors,host',
            'check_type'      => ['required', Rule::in(Monitor::getCheckTypes())],
            'snmp_community'  => 'nullable|string|max:255',
            'snmp_version'    => ['nullable', Rule::in(['v1', 'v2c', 'v3'])],
            'snmp_port'       => 'nullable|integer|min:1|max:65535',
            'snmp_timeout'    => 'nullable|integer|min:1|max:10',
            'snmp_oid'        => 'nullable|string|max:255',
            'status'          => ['required', Rule::in(Monitor::getStatuses())],
            'uptime'          => 'nullable|numeric|min:0|max:100',
            'response_time'   => 'nullable|integer|min:0',
            'last_checked_at' => 'nullable|date',
        ]);

        Monitor::create($validated);
        return redirect()->route('monitors.index')->with('success', 'Monitor created.');
    }

    public function show(Monitor $monitor)
    {
        return view('monitors.show', compact('monitor'));
    }

    public function edit(Monitor $monitor)
    {
        return view('monitors.create', compact('monitor'));
    }

    public function update(Request $request, Monitor $monitor)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'host'            => ['required', 'string', 'max:255', Rule::unique('monitors')->ignore($monitor->id)],
            'check_type'      => ['required', Rule::in(Monitor::getCheckTypes())],
            'snmp_community'  => 'nullable|string|max:255',
            'snmp_version'    => ['nullable', Rule::in(['v1', 'v2c', 'v3'])],
            'snmp_port'       => 'nullable|integer|min:1|max:65535',
            'snmp_timeout'    => 'nullable|integer|min:1|max:10',
            'snmp_oid'        => 'nullable|string|max:255',
            'status'          => ['required', Rule::in(Monitor::getStatuses())],
            'uptime'          => 'nullable|numeric|min:0|max:100',
            'response_time'   => 'nullable|integer|min:0',
            'last_checked_at' => 'nullable|date',
        ]);

        $monitor->update($validated);
        return redirect()->route('monitors.index')->with('success', 'Monitor updated.');
    }

    public function destroy(Monitor $monitor)
    {
        $monitor->delete();
        return redirect()->route('monitors.index')->with('success', 'Monitor deleted.');
    }
}
