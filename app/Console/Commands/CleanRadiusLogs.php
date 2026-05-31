<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\CronLog;
use App\Models\CronJob;

class CleanRadiusLogs extends Command
{
    protected $signature = 'radius:clean-logs';

    protected $description = 'Clean old radpostauth logs';

    public function handle()
    {

        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->info('Radius cleanup cron disabled');
            return self::SUCCESS;
        }

        try {

            $deleted = DB::table('radpostauth')
                ->whereNotNull('authdate')
                ->where('authdate', '<', now()->subDays(15))
                ->delete();

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "radpostauth cleaned: {$deleted} records"
            ]);

            $this->info("✔ Deleted {$deleted} records");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            $this->error("❌ Failed: " . $e->getMessage());

            return self::FAILURE;
        }
    }
}
