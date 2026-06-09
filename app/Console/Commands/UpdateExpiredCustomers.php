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

    public function handle()
    {
        $startedAt = microtime(true);

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

            Customer::whereNotNull('expire_date')
                ->with('activeGracePeriod')
                ->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {

                    foreach ($customers as $customer) {

                        try {

                            $stats['processed']++;

                            $oldStatus = $customer->status;
                            $newStatus = $customer->calculateStatus();

                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }

                            if ($newStatus === 'expired') {
                                $stats['expired']++;
                            }

                            if ($oldStatus === $newStatus) {
                                continue;
                            }

                            DB::transaction(function () use (
                                $customer,
                                $newStatus,
                                &$stats
                            ) {
                                $customer->update([
                                    'status' => $newStatus,
                                ]);

                                $stats['updated']++;
                            });

                            if (
                                $newStatus === 'expired' &&
                                $oldStatus !== 'expired'
                            ) {
                                $this->handleExpiredCustomer(
                                    $customer,
                                    $mikrotik,
                                    $radius
                                );
                            }

                        } catch (\Throwable $e) {

                            $stats['failed']++;

                            Log::error(
                                'Customer processing failed',
                                [
                                    'customer_id' => $customer->id,
                                    'error' => $e->getMessage(),
                                ]
                            );
                        }
                    }
                });

            $duration = round(
                microtime(true) - $startedAt,
                2
            );

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => sprintf(
                    'Processed: %d | Updated: %d | Grace: %d | Expired: %d | Failed: %d | Duration: %ss',
                    $stats['processed'],
                    $stats['updated'],
                    $stats['grace'],
                    $stats['expired'],
                    $stats['failed'],
                    $duration
                ),
            ]);

            Log::info(
                'Customer expiry cron finished',
                array_merge($stats, [
                    'duration' => $duration,
                ])
            );

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::error(
                'Customer expiry cron crashed',
                [
                    'error' => $e->getMessage(),
                ]
            );
        }

        return self::SUCCESS;
    }

    private function handleExpiredCustomer(
        Customer $customer,
        MikrotikService $mikrotik,
        RadiusService $radius
    ): void {

        $username = $customer->username;

        if (!$username) {
            Log::warning(
                'Expired customer has no username',
                [
                    'customer_id' => $customer->id,
                ]
            );

            return;
        }

        try {
            $mikrotik->disconnectPPPoE($username);
        } catch (\Throwable $e) {
            Log::error(
                'MikroTik disconnect failed',
                [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        try {

            DB::table('radacct')
                ->where('username', $username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Expired',
                ]);

        } catch (\Throwable $e) {

            Log::error(
                'Failed to close RADIUS accounting',
                [
                    'customer_id' => $customer->id,
                    'error' => $e->getMessage(),
                ]
            );
        }

        try {

            $radius->removeCustomer($customer);

        } catch (\Throwable $e) {

            Log::error(
                'Radius remove failed',
                [
                    'customer_id' => $customer->id,
                    'username' => $username,
                    'error' => $e->getMessage(),
                ]
            );
        }
    }
}
