<?php

namespace App\Http\Controllers;

use App\Helpers\Activity;
use App\Models\Customer;
use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use App\Services\SmsService;
use Illuminate\Http\Request;

class SmsGatewayController extends Controller
{
    public function index()
    {
        $queues = SmsQueue::latest()->paginate(15);
        return view('sms.index', compact('queues'));
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
        // === BULK SEND ===
        if ($request->has('bulk')) {
            // Send all that are not yet 'sent' (pending or failed)
            $unsent = SmsQueue::where('status', '!=', SmsQueue::STATUS_SENT)->get();

            if ($unsent->isEmpty()) {
                return back()->with('info', 'No unsent messages to send.');
            }

            $sentCount = 0;
            foreach ($unsent as $sms) {
                $success = $smsService->sendNow($sms->username, $sms->mobile, $sms->message);
                if ($success) {
                    $sms->markAsSent();
                    $sentCount++;

                    $customer = Customer::where('username', $sms->username)->first();
                    $name = $customer ? $customer->name : $sms->username;
                    Activity::add(
                        'SMS Sent',
                        $name . ' – SMS sent to ' . $sms->mobile,
                        'fas fa-envelope text-success',
                        $customer ? route('customers.show', $customer->id) : '#'
                    );
                } else {
                    $sms->markAsFailed(); // increments retry_count, marks as failed after 3
                }
            }

            return back()->with('success', "Processed {$sentCount} out of {$unsent->count()} unsent messages.");
        }

        // === SINGLE SEND ===
        $validated = $request->validate([
            'sms_id'   => 'required|exists:sms_queues,id',
            'username' => 'required|string',
            'mobile'   => 'required|string',
            'message'  => 'required|string|max:500',
        ]);

        $sms = SmsQueue::findOrFail($validated['sms_id']);

        $success = $smsService->sendNow(
            $validated['username'],
            $validated['mobile'],
            $validated['message']
        );

        if ($success) {
            $sms->markAsSent();

            $customer = Customer::where('username', $validated['username'])->first();
            $name = $customer ? $customer->name : $validated['username'];
            Activity::add(
                'SMS Sent',
                $name . ' – SMS sent to ' . $validated['mobile'],
                'fas fa-envelope text-success',
                $customer ? route('customers.show', $customer->id) : '#'
            );

            $message = 'SMS sent successfully.';
        } else {
            $sms->markAsFailed();
            $message = 'SMS sending failed. Check logs.';
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
