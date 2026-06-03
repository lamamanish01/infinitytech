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

        if (!$job || !$job->is_active) {
            $this->info('Radacct cleanup cron disabled');

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'skipped',
                'message' => 'Cron disabled'
            ]);

            return self::SUCCESS;
        }

        try {

            $days = 30; // keep last 30 days

            $deleted = DB::table('radacct')
                ->where('acctstarttime', '<', now()->subDays($days))
                ->delete();

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "radacct cleaned: {$deleted} records (kept last {$days} days)"
            ]);

            $this->info("✔ Deleted {$deleted} radacct records");

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error("❌ Failed: " . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
