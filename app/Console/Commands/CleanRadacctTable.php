<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use App\Models\CronJob;

class CleanRadacctTable extends Command
{
    protected $signature = 'radius:clean-radacct';
    protected $description = 'Clean old radacct session logs';

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
            // Configurable retention days (default 30)
            $retentionDays = config('radius.radacct_retention_days', 15);
            $cutoff = now()->subDays($retentionDays);

            $deleted = DB::table('radacct')
                ->where('acctstarttime', '<', $cutoff)
                ->delete();

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "radacct cleaned: {$deleted} records (kept last {$retentionDays} days)",
            ]);

            $this->info("✔ Deleted {$deleted} radacct records");
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
