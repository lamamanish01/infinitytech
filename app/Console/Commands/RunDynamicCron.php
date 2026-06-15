<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CronJob;
use App\Models\CronLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class RunDynamicCron extends Command
{
    protected $signature = 'cron:run-dynamic';
    protected $description = 'Run dynamic cron jobs from database';

    // Frequency intervals in minutes
    protected const FREQUENCIES = [
        'minute'        => 1,
        'five_minutely' => 5,
        'ten_minutely'  => 10,
        'fifteen_minutely' => 15,
        'thirty_minutely' => 30,
        'hourly'        => 60,
        'daily'         => 1440,
        'weekly'        => 10080,
        'monthly'       => 43200,
    ];

    public function handle(): int
    {
        $jobs = CronJob::where('is_active', true)->get();

        if ($jobs->isEmpty()) {
            $this->info('No active cron jobs');
            return self::SUCCESS;
        }

        foreach ($jobs as $job) {
            // Skip if not time to run
            if (!$this->shouldRun($job)) {
                continue;
            }

            // Prevent overlapping executions
            $lockKey = "cron_job_{$job->id}_running";
            $lock = Cache::lock($lockKey, $job->timeout ?? 300);

            if (!$lock->get()) {
                $this->warn("Job {$job->key} is already running, skipped");
                continue;
            }

            try {
                $this->info("Running: {$job->key}");
                $start = microtime(true);

                // Execute artisan command
                $exitCode = Artisan::call($job->key);

                $duration = round(microtime(true) - $start, 2);

                // Update last run timestamp
                $job->update(['last_run_at' => now()]);

                $status = $exitCode === 0 ? 'success' : 'failed';
                $message = $status === 'success'
                    ? "Executed successfully in {$duration}s"
                    : "Command returned exit code {$exitCode}";

                CronLog::create([
                    'command' => $job->key,
                    'status'  => $status,
                    'message' => $message,
                ]);

                if ($status === 'failed') {
                    $this->error("Job {$job->key} failed (exit {$exitCode})");
                } else {
                    $this->info("Job {$job->key} completed in {$duration}s");
                }
            } catch (\Throwable $e) {
                CronLog::create([
                    'command' => $job->key,
                    'status'  => 'failed',
                    'message' => $e->getMessage(),
                ]);
                $this->error("Job {$job->key} crashed: " . $e->getMessage());
            } finally {
                $lock->release();
            }
        }

        return self::SUCCESS;
    }

    /**
     * Determine if the cron job should run now.
     */
    private function shouldRun($job): bool
    {
        // First run
        if (!$job->last_run_at) {
            return true;
        }

        $lastRun = Carbon::parse($job->last_run_at);
        $now = now();

        // Support custom frequency via stored interval (minutes) or predefined name
        $interval = $this->getIntervalMinutes($job->frequency);

        if (!$interval) {
            $this->warn("Unknown frequency '{$job->frequency}' for job {$job->key}");
            return false;
        }

        // Check if enough minutes have passed
        return $lastRun->copy()->addMinutes($interval)->lte($now);
    }

    /**
     * Get interval in minutes from frequency string.
     */
    private function getIntervalMinutes(string $frequency): ?int
    {
        // If numeric (e.g., "5" meaning 5 minutes)
        if (is_numeric($frequency)) {
            return (int) $frequency;
        }

        // Otherwise lookup from predefined map
        return self::FREQUENCIES[$frequency] ?? null;
    }
}
