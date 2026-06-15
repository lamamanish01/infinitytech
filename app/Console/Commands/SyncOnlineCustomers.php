<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncOnlineCustomers extends Command
{
    protected $signature = 'customers:sync-online';
    protected $description = 'Sync online customers snapshot from radacct (no DB flag)';

    /** @var int Online threshold in minutes (configurable) */
    protected int $onlineThreshold;

    /** @var string Cache key for online list */
    protected const CACHE_KEY = 'online_customers';

    public function handle(): int
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
            $this->onlineThreshold = config('radius.online_threshold_minutes', 15);
            $cutoff = now()->subMinutes($this->onlineThreshold);

            // Directly fetch unique usernames with recent activity
            $onlineUsers = DB::table('radacct')
                ->whereNull('acctstoptime')
                ->where(function ($query) use ($cutoff) {
                    $query->whereNotNull('acctupdatetime')
                          ->where('acctupdatetime', '>=', $cutoff)
                          ->orWhere(function ($sub) use ($cutoff) {
                              $sub->whereNull('acctupdatetime')
                                  ->where('acctstarttime', '>=', $cutoff);
                          });
                })
                ->distinct()
                ->pluck('username')
                ->toArray();

            $count = count($onlineUsers);

            // Store in cache (expires every 2 minutes)
            Cache::put(self::CACHE_KEY, $onlineUsers, now()->addMinutes(2));

            // Optional: log only when count changes significantly
            $previous = Cache::get(self::CACHE_KEY . '_previous_count');
            if ($previous !== $count) {
                Log::info('Online customers count changed', [
                    'previous' => $previous,
                    'current'  => $count,
                ]);
                Cache::put(self::CACHE_KEY . '_previous_count', $count, now()->addDay());
            }

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Online snapshot: {$count} users (threshold: {$this->onlineThreshold} min)",
            ]);

            $this->info("✔ Online snapshot synced: {$count} users");
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
