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

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';

    protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    public function handle(): int
    {
        $startedAt = microtime(true);

        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job configuration not found");
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->info("Cron is inactive");
            return self::SUCCESS;
        }

        $this->info('Customer expiry cron started');

        $mikrotik = app(MikrotikService::class);
        $radius   = app(RadiusService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'active'    => 0,
            'grace'     => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        try {

            Customer::whereNotNull('expire_date')
                ->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {

                    foreach ($customers as $customer) {

                        try {

                            $stats['processed']++;

                            $oldStatus = $customer->status;
                            $newStatus = $customer->calculateStatus();

                            if ($oldStatus === $newStatus) {
                                continue;
                            }

                            // COUNT ONLY REAL TRANSITIONS
                            match ($newStatus) {
                                'active'  => $stats['active']++,
                                'grace'   => $stats['grace']++,
                                'expired' => $stats['expired']++,
                            };

                            // UPDATE FIRST (IMPORTANT)
                            DB::transaction(function () use ($customer, $newStatus, &$stats) {
                                $customer->update([
                                    'status' => $newStatus,
                                ]);

                                $stats['updated']++;
                            });

                            $customer->refresh();

                            /*
                            |--------------------------------------------------------------------------
                            | ACTIVE
                            |--------------------------------------------------------------------------
                            */
                            if ($newStatus === 'active') {
                                $radius->enableCustomer($customer);

                                Log::info('Customer activated', [
                                    'id' => $customer->id
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | GRACE
                            |--------------------------------------------------------------------------
                            */
                            if ($newStatus === 'grace') {
                                Log::info('Customer in grace period', [
                                    'id' => $customer->id
                                ]);
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | EXPIRED
                            |--------------------------------------------------------------------------
                            */
                            if ($newStatus === 'expired') {
                                $this->handleExpiredCustomer($customer, $mikrotik, $radius);
                            }

                        } catch (\Throwable $e) {

                            $stats['failed']++;

                            Log::error('Customer update failed', [
                                'id'    => $customer->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                });

            $duration = round(microtime(true) - $startedAt, 2);

            $message = "Processed: {$stats['processed']} | Updated: {$stats['updated']} | Active: {$stats['active']} | Grace: {$stats['grace']} | Expired: {$stats['expired']} | Failed: {$stats['failed']} | Duration: {$duration}s";

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => $message,
            ]);

            $this->info($message);

            Log::info('Cron finished', $stats);

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::error('Cron crashed', [
                'error' => $e->getMessage()
            ]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Handle expired customer
     */
    private function handleExpiredCustomer($customer, $mikrotik, $radius): void
    {
        $username = $customer->username;

        if (!$username) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | 1. Disconnect MikroTik
        |--------------------------------------------------------------------------
        */
        try {
            $mikrotik->disconnectPPPoE($username);
        } catch (\Throwable $e) {
            Log::error('MikroTik disconnect failed', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Close RADIUS session
        |--------------------------------------------------------------------------
        */
        try {
            DB::table('radacct')
                ->where('username', $username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Expired',
                ]);
        } catch (\Throwable $e) {
            Log::error('radacct update failed', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Disable RADIUS login (Auth-Type := Reject)
        |--------------------------------------------------------------------------
        */
        try {
            $radius->removeCustomer($customer);
        } catch (\Throwable $e) {
            Log::error('Radius disable failed', [
                'username' => $username,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}
