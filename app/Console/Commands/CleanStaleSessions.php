<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronJob;
use App\Models\CronLog;
use Carbon\Carbon;

class CleanStaleSessions extends Command
{
    protected $signature = 'customers:clean-stale-sessions';
    protected $description = 'Safely close stale FreeRADIUS sessions (ISP safe mode)';

    public function handle(): int
    {
        // Check if cron job is configured and active
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
            // Configurable cutoff (default 15 minutes)
            $cutoffMinutes = config('radius.stale_session_minutes', 15);
            $cutoff = Carbon::now()->subMinutes($cutoffMinutes);

            // Close stale RADIUS sessions
            $updated = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($q) use ($cutoff) {
                    $q->whereNotNull('acctupdatetime')
                      ->where('acctupdatetime', '<', $cutoff)
                      ->orWhere(function ($q2) use ($cutoff) {
                          $q2->whereNull('acctupdatetime')
                             ->where('acctstarttime', '<', $cutoff);
                      });
                })
                ->update([
                    'acctstoptime' => now(),
                    'acctterminatecause' => 'Stale-Session'
                ]);

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Stale sessions closed: {$updated} (cutoff: {$cutoffMinutes} min)",
            ]);

            $this->info("✔ Stale sessions closed: {$updated}");
            return self::SUCCESS;

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
