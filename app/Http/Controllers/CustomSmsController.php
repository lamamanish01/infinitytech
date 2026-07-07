<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\SmsService;
use Illuminate\Http\Request;

class CustomSmsController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:view sms')->only(['index', 'show']);
        $this->middleware('permission:create sms')->only(['create', 'store']);
        $this->middleware('permission:edit sms')->only(['edit', 'update']);
        $this->middleware('permission:delete sms')->only(['destroy']);
    }

    public function create()
    {
        return view('sms.custom');
    }

    public function store(Request $request, SmsService $smsService)
    {
        $request->validate([
            'recipient_type' => 'required|in:single,expiring,active',
            'username'       => 'required_if:recipient_type,single|exists:customers,username',
            'days'           => 'required_if:recipient_type,expiring|integer|min:1',
            'message'        => 'required|string|max:500',
        ]);

        $message = $request->input('message');
        $queued = 0;

        $customers = collect();

        switch ($request->recipient_type) {
            case 'single':
                $customers = Customer::where('username', $request->username)->get();
                break;

            case 'expiring':
                $days = (int) $request->days;
                $targetDate = now()->addDays($days)->toDateString();
                $customers = Customer::whereDate('expiry_date', $targetDate)->get();
                break;

            case 'active':
                $customers = Customer::whereDate('expiry_date', '>=', now()->toDateString())->get();
                break;
        }

        foreach ($customers as $customer) {
            if (empty($customer->mobile)) {
                continue;
            }

            $smsService->queueMessage(
                $customer->username,
                $customer->mobile,
                $message,
                'custom'
            );
            $queued++;
        }

        return redirect()->route('sms.custom.create')
            ->with('success', "Custom SMS queued for {$queued} customer(s).");
    }
}
