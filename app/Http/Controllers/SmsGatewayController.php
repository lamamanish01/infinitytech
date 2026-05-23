<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSmsGatewayRequest;
use App\Http\Requests\UpdateSmsGatewayRequest;
use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SmsGatewayController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $queues = SmsQueue::latest()->get();
        $logs = SmsLog::latest()->get();
        return view('sms.index', compact('queues', 'logs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('sms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSmsGatewayRequest $request, SmsGateway $smsGateway)
    {
        SmsGateway::create([
            'name' => $request->name,
            'api_url' => $request->api_url,
            'auth_token' => $request->auth_token,
            'is_active' => $request->is_active,
        ]);

        return redirect()->route('sms.index')->with('success', 'SMS Gateway created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(SmsGateway $smsGateway)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SmsGateway $smsGateway)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSmsGatewayRequest $request, SmsGateway $smsGateway)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SmsGateway $smsGateway)
    {
        //
    }

    public function send(Request $request)
    {
        $gateway = SmsGateway::where('is_active',1)->first();

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
            'status' => 'sent'
        ]);

        return back();
    }

    public function queue(Request $request)
    {
        SmsQueue::create([
            'username' => $request->username,
            'mobile' => $request->mobile,
            'message' => $request->message,
            'type' => $request->type,
            'status' => 'pending',
            'send_at' => now()
        ]);

        return back();
    }
}
