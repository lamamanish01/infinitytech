<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTr069DeviceRequest;
use App\Http\Requests\UpdateTr069DeviceRequest;
use App\Models\Tr069Device;
use App\Models\Tr069Log;
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

        if ($request->filled('server_id')) {
            $query->where('tr069_server_id', $request->server_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('serial', 'like', "%{$search}%")
                  ->orWhere('mac_address', 'like', "%{$search}%")
                  ->orWhere('ppp_username', 'like', "%{$search}%");
            });
        }

        $devices = $query->latest('last_inform')->paginate(20);
        $servers = Tr069Server::where('status', 'active')->get();

        return view('tr069.devices.index', compact('devices', 'servers'));
    }

    public function create()
    {
        $servers = Tr069Server::where('status', 'active')->get();
        return view('tr069.devices.create', compact('servers'));
    }

    public function store(StoreTr069DeviceRequest $request)
    {
        $validated = $request->validated();
        Tr069Device::create($validated);
        return redirect()->route('tr069.devices.index')->with('success', 'Device created successfully.');
    }

    public function show(Tr069Device $tr069Device)
    {
        $tr069Device->load('server', 'customer');
        return view('tr069.devices.show', compact('tr069Device'));
    }

    public function edit(Tr069Device $tr069Device)
    {
        $servers = Tr069Server::where('status', 'active')->get();
        return view('tr069.devices.edit', compact('tr069Device', 'servers'));
    }

    public function update(UpdateTr069DeviceRequest $request, Tr069Device $tr069Device)
    {
        $validated = $request->validated();
        unset($validated['serial']); // prevent changing the unique serial
        $tr069Device->update($validated);
        return redirect()->route('tr069.devices.index')->with('success', 'Device updated successfully.');
    }

    public function destroy(Tr069Device $tr069Device)
    {
        $tr069Device->delete();
        return redirect()->route('tr069.devices.index')->with('success', 'Device deleted successfully.');
    }

    // ==================== DEVICE MANAGEMENT ACTIONS ====================

    /**
     * Handle WiFi and PPPoE updates from a single form (used in the customer view).
     */
    public function routerMgmtUpdate(Request $request, $id)
    {
        $device = Tr069Device::with('server')->findOrFail($id);
        $action = $request->action;

        try {
            if ($action === 'update_wifi') {
                $changes = [];

                // 2.4 GHz
                if ($request->filled('ssid_24')) {
                    if ($device->changeWiFiName($request->ssid_24, 1)) {
                        $changes['wifi_24_ssid'] = $request->ssid_24;
                    } else {
                        throw new \Exception('Failed to update 2.4 GHz SSID');
                    }
                }
                if ($request->filled('password_24')) {
                    if ($device->changeWiFiPassword($request->password_24, 1)) {
                        $changes['wifi_24_password'] = $request->password_24;
                    } else {
                        throw new \Exception('Failed to update 2.4 GHz password');
                    }
                }
                if ($device->hideSSID($request->boolean('hide_ssid_24'), 1)) {
                    $changes['wifi_24_hidden'] = $request->boolean('hide_ssid_24');
                } else {
                    throw new \Exception('Failed to update 2.4 GHz SSID visibility');
                }

                // 5 GHz
                if ($request->filled('ssid_5')) {
                    if ($device->changeWiFiName($request->ssid_5, 5)) {
                        $changes['wifi_5_ssid'] = $request->ssid_5;
                    } else {
                        throw new \Exception('Failed to update 5 GHz SSID');
                    }
                }
                if ($request->filled('password_5')) {
                    if ($device->changeWiFiPassword($request->password_5, 5)) {
                        $changes['wifi_5_password'] = $request->password_5;
                    } else {
                        throw new \Exception('Failed to update 5 GHz password');
                    }
                }
                if ($device->hideSSID($request->boolean('hide_ssid_5'), 5)) {
                    $changes['wifi_5_hidden'] = $request->boolean('hide_ssid_5');
                } else {
                    throw new \Exception('Failed to update 5 GHz SSID visibility');
                }

                if (!empty($changes)) {
                    $device->update($changes);
                }
                return back()->with('success', 'WiFi settings updated.');
            }

            if ($action === 'update_pppoe') {
                if ($device->changePPPInfo($request->pppoe_username, $request->pppoe_password)) {
                    $device->update([
                        'ppp_username' => $request->pppoe_username,
                        'ppp_password' => $request->pppoe_password,
                    ]);
                    return back()->with('success', 'PPPoE settings updated.');
                }
                throw new \Exception('Failed to update PPPoE credentials');
            }

            return back()->with('warning', 'Invalid action.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Send a reboot command.
     */
    public function reboot($id)
    {
        $device = Tr069Device::findOrFail($id);

        $success = $device->reboot();

        if ($success) {
            return back()->with('success', 'Reboot command sent to device.');
        } else {
            return back()->with('error', 'Failed to send reboot command. Device may be offline or ACS unreachable.');
        }
    }

    /**
     * Send a factory reset command.
     */
    public function factoryReset($id)
    {
        $device = Tr069Device::findOrFail($id);
        return $device->factoryReset()
            ? back()->with('success', 'Factory reset command sent.')
            : back()->with('error', 'Factory reset failed.');
    }

    /**
     * Force a parameter refresh (ACS will immediately request new data).
     */
    public function pushAcs($id)
    {
        $device = Tr069Device::findOrFail($id);
        return $device->refresh()
            ? back()->with('success', 'Refresh command sent.')
            : back()->with('error', 'Refresh failed.');
    }

    /**
     * Show the action logs for a device.
     */
    public function logs($id)
    {
        $device = Tr069Device::findOrFail($id);
        $logs = Tr069Log::where('tr069_device_id', $device->id)->latest()->paginate(50);
        return view('tr069.logs', compact('device', 'logs'));
    }

    /**
     * Universal action method for single operations (reboot, factory reset,
     * WiFi name/password, PPPoE, VLAN, user/admin login, file upload, etc.)
     * Uses route model binding.
     */
    public function action(Request $request, Tr069Device $device)
    {
        $action = $request->action;

        switch ($action) {
            case 'reboot':
                return $device->reboot()
                    ? back()->with('success', 'Reboot command sent.')
                    : back()->with('error', 'Reboot failed.');

            case 'factoryReset':
                return $device->factoryReset()
                    ? back()->with('success', 'Factory reset command sent.')
                    : back()->with('error', 'Factory reset failed.');

            case 'wifi_name':
                $request->validate(['wifi_name' => 'required']);
                if ($device->changeWiFiName($request->wifi_name)) {
                    $device->update(['wifi_24_ssid' => $request->wifi_name]);
                    return back()->with('success', 'WiFi name updated.');
                }
                return back()->with('error', 'Failed to update WiFi name.');

            case 'wifi_password':
                $request->validate(['wifi_password' => 'required']);
                if ($device->changeWiFiPassword($request->wifi_password)) {
                    $device->update(['wifi_24_password' => $request->wifi_password]);
                    return back()->with('success', 'WiFi password updated.');
                }
                return back()->with('error', 'Failed to update WiFi password.');

            case 'file_update':
                $request->validate(['file' => 'required']);
                return $device->uploadFiles($request->file)
                    ? back()->with('success', 'File upload task sent.')
                    : back()->with('error', 'File upload failed.');

            case 'vlan_id':
                $request->validate(['vlan_id' => 'required|numeric|between:0,4096']);
                return $device->changeVlan((int) $request->vlan_id)
                    ? back()->with('success', 'VLAN ID updated.')
                    : back()->with('error', 'Failed to update VLAN ID.');

            case 'ppp_set':
                $request->validate([
                    'ppp_username' => 'required',
                    'ppp_password' => 'required',
                ]);
                if ($device->changePPPInfo($request->ppp_username, $request->ppp_password)) {
                    $device->update([
                        'ppp_username' => $request->ppp_username,
                        'ppp_password' => $request->ppp_password,
                    ]);
                    return back()->with('success', 'PPPoE credentials updated.');
                }
                return back()->with('error', 'Failed to update PPPoE credentials.');

            case 'ppp_username':
                $request->validate(['ppp_username' => 'required']);
                if ($device->changePPPUsername($request->ppp_username)) {
                    $device->update(['ppp_username' => $request->ppp_username]);
                    return back()->with('success', 'PPPoE username updated.');
                }
                return back()->with('error', 'Failed to update PPPoE username.');

            case 'ppp_password':
                $request->validate(['ppp_password' => 'required']);
                if ($device->changePPPPassword($request->ppp_password)) {
                    $device->update(['ppp_password' => $request->ppp_password]);
                    return back()->with('success', 'PPPoE password updated.');
                }
                return back()->with('error', 'Failed to update PPPoE password.');

            case 'hide_ssid':
                $newHidden = !$device->wifi_24_hidden;
                if ($device->hideSSID($newHidden)) {
                    $device->update(['wifi_24_hidden' => $newHidden]);
                    return back()->with('success', 'SSID visibility toggled.');
                }
                return back()->with('error', 'Failed to toggle SSID visibility.');

            case 'user_login':
                $request->validate([
                    'user_username' => 'required',
                    'user_password' => 'required',
                ]);
                if ($device->changeUserLogin($request->user_username, $request->user_password)) {
                    $device->update([
                        'user_username' => $request->user_username,
                        'user_password' => $request->user_password,
                    ]);
                    return back()->with('success', 'User login credentials updated.');
                }
                return back()->with('error', 'Failed to update user login credentials.');

            case 'admin_login':
                $request->validate([
                    'admin_username' => 'required',
                    'admin_password' => 'required',
                ]);
                if ($device->changeAdminLogin($request->admin_username, $request->admin_password)) {
                    $device->update([
                        'admin_username' => $request->admin_username,
                        'admin_password' => $request->admin_password,
                    ]);
                    return back()->with('success', 'Admin login credentials updated.');
                }
                return back()->with('error', 'Failed to update admin login credentials.');

            default:
                return back()->with('error', 'Invalid action.');
        }
    }
}
