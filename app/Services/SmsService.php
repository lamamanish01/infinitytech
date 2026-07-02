<?php

namespace App\Services;

use App\Models\Gateway;
use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    public function sendNow(string $username, string $mobile, string $message): bool
    {
        try {
            $response = Http::timeout(10)->post($this->gateway->api_url, [
                'auth_token' => $this->gateway->auth_token,
                'mobile'     => $mobile,
                'message'    => $message,
            ]);

            $httpSuccess = $response->successful();
            $rawBody = $response->body();
            $decoded = json_decode($rawBody, true);
            $jsonError = json_last_error();

            // Determine gateway-level success (adjust to your provider)
            $gatewaySuccess = false;
            if ($httpSuccess && $jsonError === JSON_ERROR_NONE && is_array($decoded)) {
                $gatewaySuccess = ($decoded['status'] ?? '') === 'success' ||
                                  ($decoded['success'] ?? false) === true ||
                                  ($decoded['code'] ?? 0) === 200;
            }

            $success = $httpSuccess && $gatewaySuccess;

            SmsLog::create([
                'username' => $username,
                'mobile'   => $mobile,
                'message'  => $message,
                'response' => $rawBody,
                'parsed'   => $decoded,
                'status'   => $success ? 'sent' : 'failed',
            ]);

            if (!$success) {
                Log::error('SMS send failed', [
                    'username'   => $username,
                    'mobile'     => $mobile,
                    'http_code'  => $response->status(),
                    'raw_body'   => $rawBody,
                    'parsed'     => $decoded,
                ]);
            }

            return $success;

        } catch (\Throwable $e) {
            SmsLog::create([
                'username' => $username,
                'mobile'   => $mobile,
                'message'  => $message,
                'response' => $e->getMessage(),
                'parsed'   => null,
                'status'   => 'failed',
            ]);

            Log::error('SMS send exception', [
                'username' => $username,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return false;
        }
    }
}
