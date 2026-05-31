<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CronJob;

class RunDynamicCron extends Command
{
    protected $signature = 'cron:run-dynamic';

    protected $description = 'Run all enabled cron jobs dynamically';

    public function handle()
    {
        $jobs = CronJob::where('is_active', 1)->get();

        foreach ($jobs as $job) {

            try {

                switch ($job->key) {

                    case 'update_expire_customers':
                        app(\App\Console\Commands\UpdateExpireCustomers::class)->handle();
                        break;

                    case 'mac_bind':
                        app(\App\Console\Commands\BindMac::class)->handle();
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

                $this->info("Executed: {$job->key}");

            } catch (\Throwable $e) {
                $this->error("Failed: {$job->key} - " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
