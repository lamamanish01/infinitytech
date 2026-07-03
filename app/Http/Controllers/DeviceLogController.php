<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceLogController extends Controller
{
    /**
     * Display a list of the user's devices and login history.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Get all login logs (latest first)
        $logs = $user->authentications()->latest()->get();

        // Get unique devices (grouped by fingerprint)
        $devices = $user->getDevices();

        return view('profile.devices', compact('logs', 'devices'));
    }

    /**
     * Show details for a specific login log entry.
     */
    public function show($id)
    {
        $user = Auth::user();
        $log = $user->authentications()->findOrFail($id);

        return view('profile.device-detail', compact('log'));
    }

    /**
     * Revoke (logout) all sessions for a specific device.
     * This requires the device fingerprint.
     */
    public function revoke(Request $request)
    {
        // dd($request->all());
        $user = $request->user();
        $deviceId = $request->input('device_id');
        // dd($deviceId);

        if (empty($deviceId)) {
            return back()->with('error', 'Device ID is required.');
        }

        $logs = $user->authentications()->where('device_id', $deviceId)->get();

        if ($logs->isEmpty()) {
            return back()->with('error', 'No logs found for this device.');
        }

        foreach ($logs as $log) {
            $log->update(['logout_at' => now()]);
        }

        return back()->with('success', 'Device sessions revoked successfully.');
    }

    /**
     * Delete all login logs for the authenticated user (optional).
     */
    public function clearLogs(Request $request)
    {
        $user = $request->user();
        $user->authentications()->delete();

        return back()->with('success', 'All login logs cleared.');
    }
}
