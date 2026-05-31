<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CronJob;

class RunDynamicCron extends Command
{
    protected $signature = 'cron:run-dynamic';

    protected $description = 'Run enabled cron jobs from DB';

    public function handle()
    {
        $jobs = CronJob::where('is_active', 1)->get();

        if ($jobs->isEmpty()) {
            $this->info('No active cron jobs');
            return Command::SUCCESS;
        }

        foreach ($jobs as $job) {

            try {

                $this->info("Running: {$job->key}");

                switch ($job->key) {

                    case 'mac_bind':
                        app(\App\Console\Commands\BindMac::class)->handle();
                        break;

                    case 'expire_customers':
                        app(\App\Console\Commands\UpdateExpiredCustomers::class)->handle();
                        break;

                    case 'radius_cleanup':
                        app(\App\Console\Commands\CleanRadiusLogs::class)->handle();
                        break;

                    case 'stale_sessions':
                        app(\App\Console\Commands\CleanStaleSessions::class)->handle();
                        break;

                    default:
                        $this->warn("No handler for: {$job->key}");
                        break;
                }

            } catch (\Throwable $e) {

                $this->error("Failed: {$job->key}");

                \App\Models\CronLog::create([
                    'command' => $job->key,
                    'status' => 'failed',
                    'message' => $e->getMessage()
                ]);
            }
        }

        return Command::SUCCESS;
    }
}
