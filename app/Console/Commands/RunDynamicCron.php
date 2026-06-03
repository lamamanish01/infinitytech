<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CronJob;
use App\Models\CronLog;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

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

            if (!$this->shouldRun($job)) {
                continue;
            }

            try {

                $this->info("Running: {$job->key}");

                // run artisan command dynamically
                Artisan::call($job->key);

                // update last run time
                $job->update([
                    'last_run_at' => now()
                ]);

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
    | BEST RELIABLE FREQUENCY ENGINE
    |--------------------------------------------------------------------------
    */
    private function shouldRun($job): bool
    {
        if (!$job->last_run_at) {
            return true;
        }

        $last = Carbon::parse($job->last_run_at);

        return match ($job->frequency) {

            'minute'  => $last->copy()->addMinute()->lte(now()),

            'hourly'  => $last->copy()->addHour()->lte(now()),

            'daily'   => $last->copy()->addDay()->lte(now()),

            'weekly'  => $last->copy()->addWeek()->lte(now()),

            'monthly' => $last->copy()->addMonth()->lte(now()),

            default => false,
        };
    }
}
