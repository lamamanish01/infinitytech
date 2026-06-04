<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
use App\Models\SmsQueue;
use App\Models\CronLog;
use App\Models\CronJob;
use Carbon\Carbon;

class SendExpirySms extends Command
{
    protected $signature = 'sms:expiry-reminder';

    protected $description = 'Send SMS to customers whose internet will expire in 3 days';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->info('Expiry SMS cron is disabled');
            return self::SUCCESS;
        }

        try {

            $targetDate = Carbon::now()->addDays(3)->toDateString();

            $customers = Customer::whereDate('expire_date', $targetDate)->get();

            $count = 0;

            foreach ($customers as $customer) {

                // ❌ prevent duplicate SMS in same day
                $exists = SmsQueue::where('mobile', $customer->contact_number)
                    ->where('type', 'expiry_reminder')
                    ->whereDate('created_at', today())
                    ->exists();

                if ($exists) {
                    continue;
                }

                SmsQueue::create([
                    'username' => $customer->username,
                    'mobile'   => $customer->contact_number,
                    'message'  => "Dear {$customer->name}, your internet will expire in 3 days. Please renew to avoid service interruption.",
                    'type'     => 'expiry_reminder',
                    'status'   => 'pending',
                    'retry_count' => 0,
                    'send_at'  => now(),
                ]);

                $count++;
            }

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Expiry SMS queued for {$count} customers",
            ]);

            $this->info("✔ {$count} SMS queued");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ Failed: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
