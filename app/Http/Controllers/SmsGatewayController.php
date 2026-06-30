<?php

namespace App\Http\Controllers;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $gateways = SmsGateway::all();
        $queues = SmsQueue::all();
        return view('sms.index', compact('gateways', 'queues'));
    }

    public function create()
    {
        return view('sms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string',
            'api_url'    => 'required|url',
            'auth_token' => 'required|string',
            'is_active'  => 'boolean',
        ]);

        SmsGateway::create($validated);
        return redirect()->route('sms.index')->with('success', 'Gateway created.');
    }

    public function edit(SmsGateway $smsGateway)
    {
        return view('sms.edit', compact('smsGateway'));
    }

    public function update(Request $request, SmsGateway $smsGateway)
    {
        $validated = $request->validate([
            'name'       => 'required|string',
            'api_url'    => 'required|url',
            'auth_token' => 'required|string',
            'is_active'  => 'boolean',
        ]);

        $smsGateway->update($validated);
        return redirect()->route('sms.index')->with('success', 'Gateway updated.');
    }

    public function destroy(SmsGateway $smsGateway)
    {
        $smsGateway->delete();
        return redirect()->route('sms.index')->with('success', 'Gateway deleted.');
    }

    public function logs()
    {
        $logs = SmsLog::orderBy('created_at', 'desc')->paginate(50);
        return view('sms.logs', compact('logs'));
    }

    public function send(Request $request, SmsService $smsService)
    {
        $request->validate([
            'username' => 'required|string',
            'mobile'   => 'required|string',
            'message'  => 'required|string|max:500',
        ]);

        $success = $smsService->sendNow(
            $request->username,
            $request->mobile,
            $request->message
        );

        if ($success) {
            return back()->with('success', 'SMS sent successfully.');
        } else {
            return back()->with('error', 'SMS sending failed. Check logs.');
        }
    }
}
