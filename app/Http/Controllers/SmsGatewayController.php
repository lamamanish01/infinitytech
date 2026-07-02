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
        $queues = SmsQueue::latest()->paginate(15);
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
        // Bulk send: all records that are not yet 'sent'
        if ($request->has('bulk')) {
            $unsent = SmsLog::where('status', '!=', 'sent')->get();

            if ($unsent->isEmpty()) {
                return back()->with('info', 'No unsent messages to send.');
            }

            $sentCount = 0;
            foreach ($unsent as $log) {
                if ($smsService->sendNow($log->username, $log->mobile, $log->message)) {
                    $sentCount++;
                }
            }

            return back()->with('success', "Processed {$sentCount} out of {$unsent->count()} unsent messages.");
        }

        // Single send
        $validated = $request->validate([
            'username' => 'required|string',
            'mobile'   => 'required|string',
            'message'  => 'required|string|max:500',
        ]);

        $success = $smsService->sendNow(
            $validated['username'],
            $validated['mobile'],
            $validated['message']
        );

        return back()->with(
            $success ? 'success' : 'error',
            $success ? 'SMS sent successfully.' : 'SMS sending failed. Check logs.'
        );
    }
}
