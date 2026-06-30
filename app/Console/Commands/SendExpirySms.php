<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\SmsQueue;
use App\Models\CronJob;
use Illuminate\Console\Command;

class SendExpirySms extends Command
{
    protected $signature = 'sms:send-expiry-sms';
    protected $description = 'Queue expiry reminder SMS for customers expiring in 3 days';

    public function handle(): int
    {
        $cron = CronJob::where('key', $this->signature)->first();
        if (!$cron || !$cron->is_active) {
            $this->warn("Cron job '{$this->signature}' is inactive.");
            return self::SUCCESS;
        }

        $threeDaysFromNow = now()->addDays(3)->toDateString();
        $customers = Customer::whereDate('expiry_date', $threeDaysFromNow)->get();

        if ($customers->isEmpty()) {
            $this->info('No customers expiring in 3 days.');
            return self::SUCCESS;
        }

        foreach ($customers as $customer) {
            $exists = SmsQueue::where('username', $customer->username)
                ->where('type', 'expiry_reminder')
                ->where('status', SmsQueue::STATUS_PENDING)
                ->exists();

            if ($exists) {
                continue;
            }

            $message = "Dear {$customer->name}, your subscription expires on {$customer->expiry_date}. Please renew to avoid service interruption.";

            SmsQueue::create([
                'username' => $customer->username,
                'mobile'   => $customer->mobile,
                'message'  => $message,
                'type'     => 'expiry_reminder',
                'status'   => SmsQueue::STATUS_PENDING,
                'send_at'  => now(),
            ]);
        }

        $this->info("Queued expiry reminders for {$customers->count()} customers.");
        return self::SUCCESS;
    }
}
