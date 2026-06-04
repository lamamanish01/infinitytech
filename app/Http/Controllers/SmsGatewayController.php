<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmsGatewayRequest;
use App\Http\Requests\UpdateSmsGatewayRequest;
use App\Models\Customer;
use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmsGatewayController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view sms')->only(['index']);
        $this->middleware('permission:create sms')->only(['create', 'store']);
        $this->middleware('permission:edit sms')->only(['edit', 'update']);
        $this->middleware('permission:delete sms')->only(['destroy']);
        $this->middleware('permission:send sms')->only(['send']);
    }

    /**
     * LIST
     */
    public function index()
    {
        $queues = SmsQueue::latest()->get();
        $logs = SmsLog::latest()->get();

        return view('sms.index', compact('queues', 'logs'));
    }

    /**
     * CREATE VIEW
     */
    public function create()
    {
        return view('sms.create');
    }

    /**
     * STORE GATEWAY
     */
    public function store(StoreSmsGatewayRequest $request)
    {
        SmsGateway::create([
            'name' => $request->name,
            'api_url' => $request->api_url,
            'auth_token' => $request->auth_token,
            'is_active' => $request->is_active ?? 1,
        ]);

        return redirect()->route('sms.index')
            ->with('success', 'SMS Gateway created successfully.');
    }

    /**
     * SEND SMS (MANUAL)
     */
    public function send(Request $request)
    {
        $gateway = SmsGateway::where('is_active', 1)->first();

        if (!$gateway) {
            return back()->with('error', 'No active SMS gateway found');
        }

        try {

            $response = Http::asForm()->post($gateway->api_url, [
                'auth_token' => $gateway->auth_token,
                'to' => $request->mobile,
                'text' => $request->message,
            ]);

            SmsLog::create([
                'username' => 'manual',
                'mobile' => $request->mobile,
                'message' => $request->message,
                'response' => $response->body(),
                'status' => 'sent',
            ]);

            return back()->with('success', 'SMS sent successfully');

        } catch (\Exception $e) {

            SmsLog::create([
                'username' => 'manual',
                'mobile' => $request->mobile,
                'message' => $request->message,
                'response' => $e->getMessage(),
                'status' => 'failed',
            ]);

            return back()->with('error', 'SMS failed to send');
        }
    }

    /**
     * ADD TO QUEUE
     */
    public function queue(Request $request)
    {
        SmsQueue::create([
            'username' => $request->username,
            'mobile' => $request->mobile,
            'message' => $request->message,
            'type' => $request->type ?? 'general',
            'status' => 'pending',
            'retry_count' => 0,
            'send_at' => $request->send_at ?? now(),
        ]);

        return back()->with('success', 'SMS added to queue');
    }

    /**
     * PLACEHOLDERS (NOT IMPLEMENTED YET)
     */
    public function show(SmsGateway $smsGateway) {}
    public function edit(SmsGateway $smsGateway) {}
    public function update(UpdateSmsGatewayRequest $request, SmsGateway $smsGateway) {}
    public function destroy(SmsGateway $smsGateway) {}

    public function custom()
    {
        $customers = Customer::select('id', 'username', 'contact_number')->get();

        return view('sms.custom', compact('customers'));
    }

    public function sendCustom(Request $request)
    {
        $customer = Customer::findOrFail($request->customer_id);

        $gateway = SmsGateway::where('is_active', 1)->first();

        if (!$gateway) {
            return back()->with('error', 'No active SMS gateway found');
        }

        $message = $request->message;

        Http::asForm()->post($gateway->api_url, [
            'auth_token' => $gateway->auth_token,
            'to' => $customer->contact_number,
            'text' => $message,
        ]);

        SmsLog::create([
            'username' => $customer->username,   // ✅ HERE
            'mobile' => $customer->contact_number,
            'message' => $message,
            'status' => 'sent',
        ]);

        return back()->with('success', 'SMS sent successfully');
    }

    public function logs()
    {
        $logs = SmsLog::latest()->paginate(10);

        return view('sms.logs', compact('logs'));
    }
}
