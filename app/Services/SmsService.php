<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsLog;
use App\Models\SmsQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $gateway;

    public function __construct()
    {
        $this->gateway = SmsGateway::active()->first();
        if (!$this->gateway) {
            throw new \Exception('No active SMS gateway found.');
        }
    }

    /**
     * Send immediately and log result
     */
    public function sendNow(string $username, string $mobile, string $message): bool
    {
        try {
            $response = Http::post($this->gateway->api_url, [
                'auth_token' => $this->gateway->auth_token,
                'mobile'     => $mobile,
                'message'    => $message,
            ]);

            $success = $response->successful();
            $responseBody = $response->body();

            SmsLog::create([
                'username' => $username,
                'mobile'   => $mobile,
                'message'  => $message,
                'response' => $responseBody,
                'status'   => $success ? 'sent' : 'failed',
            ]);

            if (!$success) {
                Log::error('SMS send failed', ['username' => $username, 'mobile' => $mobile, 'response' => $responseBody]);
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

            Log::error('SMS send exception', ['username' => $username, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Queue a message (used by custom send)
     */
    public function queueMessage(string $username, string $mobile, string $message, string $type = 'custom'): void
    {
        SmsQueue::create([
            'username' => $username,
            'mobile'   => $mobile,
            'message'  => $message,
            'type'     => $type,
            'status'   => SmsQueue::STATUS_PENDING,
            'send_at'  => now(),
        ]);
    }

    /**
     * Process a single queue entry
     */
    public function processQueueEntry(SmsQueue $queue): void
    {
        $success = $this->sendNow($queue->username, $queue->mobile, $queue->message);

        if ($success) {
            $queue->markAsSent();
        } else {
            $queue->markAsFailed();
        }
    }
}
