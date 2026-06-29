<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CronJob;
use App\Models\CronLog;

class SyncOnlineCustomers extends Command
{
    protected $signature = 'customers:sync-online';
    protected $description = 'Sync online customers snapshot from radacct';

    private const CACHE_KEY = 'online_customers';

    private int $onlineThreshold = 15; // minutes
    private string $timezone = 'Asia/Kathmandu';

    public function handle(): int
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job not found: {$this->signature}");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->warn("Cron job inactive: {$this->signature}");
            return self::FAILURE;
        }

        try {
            $cutoff = $this->getCutoffTime();

            $onlineUsers = $this->getOnlineUsers($cutoff);

            $count = count($onlineUsers);

            $this->storeCache($onlineUsers);

            $this->logIfChanged($count);

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Online snapshot: {$count} users (TZ: {$this->timezone})",
            ]);

            $this->info("✔ Online snapshot synced: {$count} users");

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error("❌ {$e->getMessage()}");

            return self::FAILURE;
        }
    }

    /**
     * Cutoff time using Asia/Kathmandu → converted to UTC for DB
     */
    private function getCutoffTime()
    {
        return now($this->timezone)
            ->subMinutes($this->onlineThreshold)
            ->utc();
    }

    /**
     * Fetch online users
     */
    private function getOnlineUsers($cutoff): array
    {
        return DB::table('radacct')
            ->whereNull('acctstoptime')
            ->where(function ($query) use ($cutoff) {

                $query->where(function ($q) use ($cutoff) {
                    $q->whereNotNull('acctupdatetime')
                      ->where('acctupdatetime', '>=', $cutoff);
                })
                ->orWhere(function ($q) use ($cutoff) {
                    $q->whereNull('acctupdatetime')
                      ->where('acctstarttime', '>=', $cutoff);
                });

            })
            ->distinct()
            ->pluck('username')
            ->toArray();
    }

    /**
     * Cache online snapshot
     */
    private function storeCache(array $users): void
    {
        Cache::put(self::CACHE_KEY, $users, now()->addMinutes(2));
    }

    /**
     * Log changes only
     */
    private function logIfChanged(int $count): void
    {
        $previous = Cache::get(self::CACHE_KEY . '_previous_count');

        if ($previous !== $count) {
            Log::info('Online customers changed', [
                'previous' => $previous,
                'current'  => $count,
                'timezone' => $this->timezone,
            ]);

            Cache::put(self::CACHE_KEY . '_previous_count', $count, now()->addDay());
        }
    }
}
