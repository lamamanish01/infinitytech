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
    // protected $signature = 'customers:update-expired';

    // protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    // public function handle(): int
    // {
    //     $startedAt = microtime(true);

    //     $job = CronJob::where('key', $this->signature)->first();
    //     if (! $job || ! $job->is_active) {
    //         $this->info('Cron inactive or not found');
    //         return self::SUCCESS;
    //     }

    //     $this->info('Customer expiry cron started');

    //     $mikrotik = app(MikrotikService::class);
    //     $radius   = app(RadiusService::class);

    //     $stats = [
    //         'processed' => 0,
    //         'updated'   => 0,
    //         'active'    => 0,
    //         'grace'     => 0,
    //         'expired'   => 0,
    //         'failed'    => 0,
    //     ];

    //     try {
    //         Customer::whereNotNull('expire_date')
    //             ->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {
    //                 foreach ($customers as $customer) {
    //                     $stats['processed']++;

    //                     $lockKey = "customer_update_{$customer->id}";
    //                     if (! cache()->add($lockKey, true, 10)) {
    //                         Log::debug("Skipped customer [{$customer->id}] – already locked");
    //                         continue;
    //                     }

    //                     try {
    //                         // ✅ Use the raw stored status to avoid any accessor interference
    //                         $oldStatus = $customer->getRawOriginal('status');
    //                         $newStatus = $customer->calculateStatus();

    //                         Log::debug('Customer status calculation', [
    //                             'id'          => $customer->id,
    //                             'username'    => $customer->username,
    //                             'expire_date' => $customer->expire_date?->toDateTimeString(),
    //                             'stored_status' => $oldStatus,
    //                             'new_status'  => $newStatus,
    //                         ]);

    //                         // No change needed?
    //                         if ($oldStatus === $newStatus) {
    //                             cache()->forget($lockKey);
    //                             continue;
    //                         }

    //                         // Update the stored status in the database
    //                         DB::transaction(function () use ($customer, $newStatus) {
    //                             $customer->update(['status' => $newStatus]);
    //                         });

    //                         // Refresh to get the updated model
    //                         $customer->refresh();

    //                         // Apply external actions based on the new status
    //                         $this->applyExternalActions($customer, $newStatus, $mikrotik, $radius);

    //                         $stats['updated']++;
    //                         match ($newStatus) {
    //                             'active'  => $stats['active']++,
    //                             'grace'   => $stats['grace']++,
    //                             'expired' => $stats['expired']++,
    //                         };

    //                         Log::info('Customer status changed', [
    //                             'id'     => $customer->id,
    //                             'from'   => $oldStatus,
    //                             'to'     => $newStatus,
    //                         ]);

    //                     } catch (\Throwable $e) {
    //                         $stats['failed']++;
    //                         Log::error('Customer update failed', [
    //                             'id'    => $customer->id,
    //                             'error' => $e->getMessage(),
    //                             'trace' => $e->getTraceAsString(),
    //                         ]);
    //                     } finally {
    //                         cache()->forget($lockKey);
    //                     }
    //                 }
    //             });

    //         $duration = round(microtime(true) - $startedAt, 2);
    //         $message = "Processed: {$stats['processed']} | Updated: {$stats['updated']} | Active: {$stats['active']} | Grace: {$stats['grace']} | Expired: {$stats['expired']} | Failed: {$stats['failed']} | Duration: {$duration}s";

    //         CronLog::create([
    //             'command' => $this->signature,
    //             'status'  => 'success',
    //             'message' => $message,
    //         ]);

    //         Log::info('Cron finished', $stats);
    //         $this->info($message);

    //     } catch (\Throwable $e) {
    //         CronLog::create([
    //             'command' => $this->signature,
    //             'status'  => 'failed',
    //             'message' => $e->getMessage(),
    //         ]);

    //         Log::error('Cron crashed', ['error' => $e->getMessage()]);
    //         return self::FAILURE;
    //     }

    //     return self::SUCCESS;
    // }

    // private function applyExternalActions(
    //     Customer $customer,
    //     string $newStatus,
    //     MikrotikService $mikrotik,
    //     RadiusService $radius
    // ): void {
    //     switch ($newStatus) {
    //         case 'active':
    //             $radius->enableCustomer($customer);
    //             break;
    //         case 'grace':
    //             // No external action needed
    //             break;
    //         case 'expired':
    //             $this->handleExpiredCustomer($customer, $mikrotik, $radius);
    //             break;
    //         default:
    //             throw new \InvalidArgumentException("Unknown status: {$newStatus}");
    //     }
    // }

    // private function handleExpiredCustomer(Customer $customer, MikrotikService $mikrotik, RadiusService $radius): void
    // {
    //     $username = $customer->username;
    //     if (empty($username)) {
    //         return;
    //     }

    //     try {
    //         $mikrotik->disconnectPPPoE($username);
    //     } catch (\Throwable $e) {
    //         Log::error('Mikrotik disconnect failed', [
    //             'username' => $username,
    //             'error'    => $e->getMessage(),
    //         ]);
    //     }

    //     try {
    //         DB::table('radacct')
    //             ->where('username', $username)
    //             ->whereNull('acctstoptime')
    //             ->update([
    //                 'acctstoptime'       => now(),
    //                 'acctterminatecause' => 'Expired',
    //             ]);
    //     } catch (\Throwable $e) {
    //         Log::error('radacct close failed', [
    //             'username' => $username,
    //             'error'    => $e->getMessage(),
    //         ]);
    //     }

    //     try {
    //         $radius->removeCustomer($customer);
    //     } catch (\Throwable $e) {
    //         Log::error('Radius remove failed', [
    //             'username' => $username,
    //             'error'    => $e->getMessage(),
    //         ]);
    //     }
    // }

    protected $signature = 'customers:update-expired';
    protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    public function handle(): int
    {
        $startedAt = microtime(true);

        $job = CronJob::where('key', $this->signature)->first();
        if (! $job || ! $job->is_active) {
            $this->info('Cron inactive or not found');
            return self::SUCCESS;
        }

        $this->info('Customer expiry cron started');

        $radius = app(RadiusService::class);

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
                ->chunkById(200, function ($customers) use ($radius, &$stats) {
                    foreach ($customers as $customer) {
                        $stats['processed']++;

                        $lockKey = "customer_update_{$customer->id}";
                        if (! cache()->add($lockKey, true, 60)) {
                            Log::debug("Skipped customer [{$customer->id}] – already locked");
                            continue;
                        }

                        try {
                            $oldStatus = $customer->getRawOriginal('status');
                            $newStatus = $customer->calculateStatus();

                            Log::debug('Customer status calculation', [
                                'id'          => $customer->id,
                                'username'    => $customer->username,
                                'expire_date' => $customer->expire_date?->toDateTimeString(),
                                'grace_days'  => $customer->grace_days,
                                'stored_status' => $oldStatus,
                                'new_status'  => $newStatus,
                            ]);

                            if ($oldStatus === $newStatus) {
                                cache()->forget($lockKey);
                                continue;
                            }

                            // Update DB status
                            DB::transaction(function () use ($customer, $newStatus) {
                                $customer->update(['status' => $newStatus]);
                            });

                            $customer->refresh();

                            // Apply external actions based on the new status
                            $this->applyExternalActions($customer, $newStatus, $radius);

                            $stats['updated']++;
                            match ($newStatus) {
                                'active'  => $stats['active']++,
                                'grace'   => $stats['grace']++,
                                'expired' => $stats['expired']++,
                            };

                            Log::info('Customer status changed', [
                                'id'     => $customer->id,
                                'from'   => $oldStatus,
                                'to'     => $newStatus,
                            ]);

                        } catch (\Throwable $e) {
                            $stats['failed']++;
                            Log::error('Customer update failed', [
                                'id'    => $customer->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);
                        } finally {
                            cache()->forget($lockKey);
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

            Log::info('Cron finished', $stats);
            $this->info($message);

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::error('Cron crashed', ['error' => $e->getMessage()]);
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function applyExternalActions(
        Customer $customer,
        string $newStatus,
        RadiusService $radius
    ): void {
        switch ($newStatus) {
            case 'active':
                $radius->enableCustomer($customer);
                break;
            case 'grace':
                $radius->ensureActiveForGrace($customer);
                break;
            case 'expired':
                $radius->disableCustomer($customer);
                break;
            default:
                throw new \InvalidArgumentException("Unknown status: {$newStatus}");
        }
    }
}
