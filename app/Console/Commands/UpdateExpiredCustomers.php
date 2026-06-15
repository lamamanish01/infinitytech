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
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Handle ISP customer lifecycle (active, grace, expired)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $startedAt = microtime(true);

        // Check if the cron job is registered and active
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job) {
            $this->error("Cron job configuration not found for: {$this->signature}");
            Log::error('Cron job missing', ['command' => $this->signature]);
            return self::FAILURE;
        }

        if (!$job->is_active) {
            $this->error("Cron job '{$this->signature}' is inactive. Skipping execution.");
            Log::info('Customer expiry cron skipped (inactive)', ['job_id' => $job->id]);
            return self::FAILURE;
        }

        $this->info('Customer expiry cron started');
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
            // Process customers with an expire_date, eager load the active grace period
            Customer::whereNotNull('expire_date')
                ->with('activeGracePeriod')
                ->chunkById(200, function ($customers) use ($mikrotik, $radius, &$stats) {
                    foreach ($customers as $customer) {
                        try {
                            $stats['processed']++;

                            $oldStatus = $customer->status;
                            $newStatus = $customer->calculateStatus();

                            // Count status changes for reporting
                            if ($newStatus === 'grace') {
                                $stats['grace']++;
                            }
                            if ($newStatus === 'expired') {
                                $stats['expired']++;
                            }

                            // Skip if status hasn't changed
                            if ($oldStatus === $newStatus) {
                                continue;
                            }

                            // --- IMPORTANT: Handle all transitions to 'expired' ---
                            // This includes active→expired, suspended→expired, and grace→expired.
                            // When grace ends, the customer moves to 'expired' and we MUST
                            // disconnect, close accounting, and remove the RADIUS user.
                            if ($newStatus === 'expired') {
                                $this->handleExpiredCustomer($customer, $mikrotik, $radius);
                            }

                            // Update the customer status in database
                            DB::transaction(function () use ($customer, $newStatus, &$stats) {
                                $customer->update(['status' => $newStatus]);
                                $stats['updated']++;
                            });

                        } catch (\Throwable $e) {
                            $stats['failed']++;
                            Log::error('Customer processing failed', [
                                'customer_id' => $customer->id,
                                'error'       => $e->getMessage(),
                            ]);
                        }
                    }
                });

            $duration = round(microtime(true) - $startedAt, 2);
            $message = sprintf(
                'Processed: %d | Updated: %d | Grace: %d | Expired: %d | Failed: %d | Duration: %ss',
                $stats['processed'],
                $stats['updated'],
                $stats['grace'],
                $stats['expired'],
                $stats['failed'],
                $duration
            );

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => $message,
            ]);

            $this->info($message);
            Log::info('Customer expiry cron finished', array_merge($stats, ['duration' => $duration]));

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            $this->error('Cron crashed: ' . $e->getMessage());
            Log::error('Customer expiry cron crashed', ['error' => $e->getMessage()]);

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Handle all necessary actions when a customer becomes expired.
     * This includes disconnecting PPPoE, closing RADIUS accounting,
     * and removing the RADIUS user (radcheck, radusergroup, etc.).
     *
     * @param Customer $customer
     * @param MikrotikService $mikrotik
     * @param RadiusService $radius
     * @return void
     */
    private function handleExpiredCustomer(
        Customer $customer,
        MikrotikService $mikrotik,
        RadiusService $radius
    ): void {
        $username = $customer->username;

        if (empty($username)) {
            Log::warning('Expired customer has no username', ['customer_id' => $customer->id]);
            return;
        }

        // 1. Forcefully disconnect PPPoE session on MikroTik
        try {
            $mikrotik->disconnectPPPoE($username);
            Log::info('PPPoE disconnected for expired customer', [
                'username'    => $username,
                'customer_id' => $customer->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('MikroTik disconnect failed', [
                'customer_id' => $customer->id,
                'username'    => $username,
                'error'       => $e->getMessage(),
            ]);
        }

        // 2. Close any open RADIUS accounting session
        try {
            $updated = DB::table('radacct')
                ->where('username', $username)
                ->whereNull('acctstoptime')
                ->update([
                    'acctstoptime'       => now(),
                    'acctterminatecause' => 'Expired',
                ]);

            if ($updated) {
                Log::info('Closed RADIUS accounting sessions', [
                    'username' => $username,
                    'count'    => $updated,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to close RADIUS accounting', [
                'customer_id' => $customer->id,
                'error'       => $e->getMessage(),
            ]);
        }

        // 3. Remove the RADIUS user completely (this is what ensures grace→expired cleans up)
        try {
            $radius->removeCustomer($customer);
            Log::info('RADIUS user removed for expired customer', [
                'username'    => $username,
                'customer_id' => $customer->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('RADIUS removal failed', [
                'customer_id' => $customer->id,
                'username'    => $username,
                'error'       => $e->getMessage(),
            ]);
        }
    }
}
