<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SmsService
{
    protected SmsGateway $gateway;

    public function __construct(?SmsGateway $smsgateway = null)
    {
        // If an injected gateway is provided but has no ID, treat it as invalid
        if ($smsgateway && $smsgateway->id) {
            $this->gateway = $smsgateway;
        } else {
            // Otherwise fetch from DB: first active, then any gateway
            $this->gateway = SmsGateway::where('is_active', true)->first();
            if (!$this->gateway) {
                $this->gateway = SmsGateway::first();
            }
        }

        // Now validate
        if (!$this->gateway) {
            throw new RuntimeException('No gateway record found in sms_gateways table.');
        }

        if (!$this->gateway->id) {
            throw new RuntimeException(
                'Gateway object still has no ID – ensure your database has a record and the query is correct.'
            );
        }

        if (empty($this->gateway->api_url)) {
            throw new RuntimeException(
                'Gateway ID ' . $this->gateway->id . ' has empty api_url. Update it via Tinker.'
            );
        }

        if (empty($this->gateway->auth_token)) {
            throw new RuntimeException(
                'Gateway ID ' . $this->gateway->id . ' has empty auth_token. Update it.'
            );
        }
    }

    public function sendNow(string $username, string $mobile, string $message): bool
    {
        try {
            $response = Http::timeout(10)->post($this->gateway->api_url, [
                'auth_token' => $this->gateway->auth_token,
                'to'         => $mobile,   // adjust if your API uses 'mobile' or 'phone'
                'text'    => $message,
            ]);

            $httpSuccess = $response->successful();
            $rawBody = $response->body();
            $decoded = json_decode($rawBody, true);
            $jsonError = json_last_error();

            $gatewaySuccess = false;
            if ($httpSuccess && $jsonError === JSON_ERROR_NONE && is_array($decoded)) {
                $gatewaySuccess = isset($decoded['error']) && $decoded['error'] === false;
            }

            $success = $httpSuccess && $gatewaySuccess;

            SmsLog::create([
                'username' => $username,
                'mobile'   => $mobile,
                'message'  => $message,
                'response' => $rawBody,
                'status'   => $success ? 'sent' : 'failed',
            ]);

            if (!$success) {
                Log::error('SMS send failed', [
                    'username'   => $username,
                    'mobile'     => $mobile,
                    'http_code'  => $response->status(),
                    'response'   => $rawBody,
                ]);
            }

            return $success;

        } catch (\Throwable $e) {
            SmsLog::create([
                'username' => $username,
                'mobile'   => $mobile,
                'message'  => $message,
                'response' => $e->getMessage(),
                'status'   => 'failed',
            ]);

            Log::error('SMS send exception', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);

            return false;
        }
    }
}
