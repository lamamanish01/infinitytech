<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronJob;
use App\Models\CronLog;
use App\Services\MikrotikService;
use App\Services\RadiusService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';
    protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            Log::info('Customer expiry cron skipped (disabled)');
            return self::SUCCESS;
        }

        Log::info('Customer expiry cron started');

        $mikrotik = app(MikrotikService::class);
        $radius   = app(RadiusService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'grace'     => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        try {
            Customer::with(['activeGracePeriod' => function ($query) {
                $query->where('grace_start', '<=', now())
                      ->where('grace_end', '>=', now())
                      ->latest('grace_end');
            }])->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {

                foreach ($customers as $customer) {
                    try {
                        $stats['processed']++;

                        if (!$customer->expire_date) {
                            continue;
                        }

                        $now = now();
                        $expireDate = $customer->expire_date->copy()->endOfDay();

                        $activeGrace = $customer->activeGracePeriod->first();
                        $cutoffDate = $activeGrace ? Carbon::parse($activeGrace->grace_end) : $expireDate;

                        if ($now->greaterThan($cutoffDate)) {
                            $newStatus = 'expired';
                        } elseif ($now->greaterThan($expireDate)) {
                            $newStatus = 'grace';
                        } else {
                            $newStatus = 'active';
                        }

                        $oldStatus = $customer->status;

                        // No change
                        if ($oldStatus === $newStatus) {
                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }
                            continue;
                        }

                        // --- Status change (transaction) ---
                        DB::transaction(function () use ($customer, $newStatus, $oldStatus, $mikrotik, $radius, &$stats) {
                            $customer->update(['status' => $newStatus]);
                            $stats['updated']++;

                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }

                            if ($newStatus === 'expired') {
                                $stats['expired']++;
                                $this->handleExpiredCustomer($customer, $mikrotik, $radius);
                            }
                        });

                    } catch (\Throwable $e) {
                        $stats['failed']++;
                        Log::error('Customer processing failed', [
                            'customer_id' => $customer->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            });

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Processed: {$stats['processed']} | Updated: {$stats['updated']} | Grace: {$stats['grace']} | Expired: {$stats['expired']} | Failed: {$stats['failed']}"
            ]);

            Log::info('Customer expiry cron finished', $stats);

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);
            Log::error('Customer expiry cron crashed', ['error' => $e->getMessage()]);
        }

        return self::SUCCESS;
    }

    /**
     * Handle cleanup for a customer that just became expired.
     */
    private function handleExpiredCustomer($customer, $mikrotik, $radius)
    {
        // Ensure we have a username
        $username = $customer->username;
        if (empty($username)) {
            Log::warning('Expired customer has no username – cannot cleanup RADIUS', [
                'customer_id' => $customer->id,
                'status'      => $customer->status
            ]);
            return;
        }

        Log::info('Starting expired cleanup', [
            'customer_id' => $customer->id,
            'username'    => $username
        ]);

        // 1. Disconnect from MikroTik (failure does not stop the rest)
        try {
            $mikrotik->disconnectPPPoE($username);
            Log::info('Disconnected expired user', ['username' => $username]);
        } catch (\Throwable $e) {
            Log::error('MikroTik disconnect failed', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // 2. Close RADIUS accounting session
        try {
            $updated = DB::table('radacct')
                ->where('username', $username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Expired'
                ]);
            Log::info('Closed RADIUS accounting sessions', ['username' => $username, 'updated' => $updated]);
        } catch (\Throwable $e) {
            Log::error('Failed to close RADIUS accounting', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // 3. Remove from RADIUS (radcheck, radusergroup, etc.)
        try {
            Log::info('Calling RadiusService::removeCustomer()', ['username' => $username]);
            $radius->removeCustomer($customer);
            Log::info('Successfully removed customer from RADIUS', ['customer_id' => $customer->id]);
        } catch (\Throwable $e) {
            Log::error('Radius remove failed', [
                'customer_id' => $customer->id,
                'username'    => $username,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
