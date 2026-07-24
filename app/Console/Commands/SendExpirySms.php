<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\Customer;
use App\Models\GracePeriod;
use App\Models\SmsQueue;
use Carbon\Carbon;
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
        $customers = Customer::whereDate('expire_date', $threeDaysFromNow)->get();

        if ($customers->isEmpty()) {
            $this->info('No customers expiring in 3 days.');
            return self::SUCCESS;
        }

        $queuedCount = 0;

        foreach ($customers as $customer) {
            // ---- SKIP IF CUSTOMER HAS AN ACTIVE GRACE PERIOD ----
            $hasActiveGrace = GracePeriod::where('customer_id', $customer->id)
                ->where('grace_end', '>=', now())
                ->exists();

            if ($hasActiveGrace) {
                $this->info("Customer {$customer->username} is in grace period – SMS skipped.");
                continue;
            }

            // Avoid duplicate pending reminders
            $exists = SmsQueue::where('username', $customer->username)
                ->where('type', 'expiry_reminder')
                ->where('status', SmsQueue::STATUS_PENDING)
                ->exists();

            if ($exists) {
                continue;
            }

            $expiry = Carbon::parse($customer->expire_date);

            $message = "Dear {$customer->name}, your subscription ends on {$expiry->format('Y-m-d')}. Please renew. Call +977-9801973212. Thank you.";

            SmsQueue::create([
                'username' => $customer->username,
                'mobile'   => $customer->contact_number,
                'message'  => $message,
                'type'     => 'expiry_reminder',
                'status'   => SmsQueue::STATUS_PENDING,
                'send_at'  => now(),
            ]);

            $queuedCount++;
        }

        $this->info("Queued expiry reminders for {$queuedCount} customers (skipped those in grace period).");
        return self::SUCCESS;
    }
}
