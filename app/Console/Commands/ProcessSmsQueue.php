<?php

namespace App\Console\Commands;

use App\Models\SmsQueue;
use App\Models\CronJob;
use App\Services\SmsService;
use Illuminate\Console\Command;

class ProcessSmsQueue extends Command
{
    protected $signature = 'sms:process-queue';
    protected $description = 'Send queued SMS messages';

    public function handle(SmsService $smsService): int
    {
        $cron = CronJob::where('key', $this->signature)->first();

        if (!$cron || !$cron->is_active) {
            $this->warn("Cron job '{$this->signature}' is inactive or not found.");
            return self::SUCCESS;
        }

        $pending = SmsQueue::where('status', SmsQueue::STATUS_PENDING)
            ->where('send_at', '<=', now())
            ->orderBy('created_at')
            ->limit(50)
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending SMS in queue.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($pending as $queue) {
            $smsService->processQueueEntry($queue);
            $sent++;
        }

        $this->info("Processed {$sent} SMS messages.");
        return self::SUCCESS;
    }
}
