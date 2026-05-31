<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CronJob;
use App\Models\CronLog;
use Illuminate\Support\Facades\Artisan;

class RunDynamicCron extends Command
{
    protected $signature = 'cron:run-dynamic';
    protected $description = 'Run dynamic cron jobs from database';

    public function handle()
    {
        $jobs = CronJob::where('is_active', 1)->get();

        if ($jobs->isEmpty()) {
            $this->info('No active cron jobs');
            return self::SUCCESS;
        }

        foreach ($jobs as $job) {

            if (!$this->shouldRun($job->frequency)) {
                continue;
            }

            try {

                $this->info("Running: {$job->key}");

                Artisan::call($job->key);

                CronLog::create([
                    'command' => $job->key,
                    'status'  => 'success',
                    'message' => 'Executed successfully',
                ]);

            } catch (\Throwable $e) {

                CronLog::create([
                    'command' => $job->key,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | FREQUENCY LOGIC
    |--------------------------------------------------------------------------
    */
    private function shouldRun(string $frequency): bool
    {
        $now = now();

        return match ($frequency) {

            'minute' => true,

            'hour' => $now->minute === 0,

            'daily' => $now->hour === 0 && $now->minute === 0,

            default => false,
        };
    }
}
