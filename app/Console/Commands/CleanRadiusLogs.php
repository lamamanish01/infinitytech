<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use App\Models\CronJob;

class CleanRadiusLogs extends Command
{
    protected $signature = 'radius:clean-logs';
    protected $description = 'Clean all radpostauth logs';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job configuration not found for: {$this->signature}");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->error("Cron job '{$this->signature}' is inactive. Skipping execution.");
            return self::FAILURE;
        }

        try {
            $deleted = DB::table('radpostauth')->delete();

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "radpostauth cleaned: all {$deleted} records deleted",
            ]);

            $this->info("✔ Deleted {$deleted} records (all)");
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
