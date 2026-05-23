<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CronLog;
use Illuminate\Console\Command;

class UpdateExpiredCustomers extends Command
{
    protected $signature = 'customers:update-expired';
    protected $description = 'Update customer status + disconnect expired users';

    public function handle()
    {
        $radius = app(\App\Services\RadiusService::class);
        $mk = app(\App\Services\MikroTikService::class);

        $processed = 0;
        $updated = 0;
        $expired = 0;
        $failed = 0;

        try {

            Customer::with(['mikrotik', 'internetPlan'])
                ->chunkById(200, function ($customers) use (
                    $radius,
                    $mk,
                    &$processed,
                    &$updated,
                    &$expired,
                    &$failed
                ) {

                    foreach ($customers as $c) {

                        try {

                            $status = $c->calculateStatus();
                            $oldStatus = $c->status;

                            $processed++;

                            /*
                            |--------------------------------------------------------------------------
                            | EXPIRED HANDLING (SESSION BASED)
                            |--------------------------------------------------------------------------
                            */
                            if ($status === 'expired' && $oldStatus !== 'expired') {

                                // Get ACTIVE session from radius
                                $session = \DB::table('radacct')
                                    ->where('username', $c->username)
                                    ->whereNull('acctstoptime')
                                    ->latest()
                                    ->first();

                                // Disconnect only if session exists
                                if ($c->mikrotik && $session) {
                                    $mk->disconnectPPPoE(
                                        $c->mikrotik,
                                        $session->username
                                    );
                                }

                                $radius->removeCustomer($c);

                                $expired++;
                            }

                            /*
                            |--------------------------------------------------------------------------
                            | STATUS UPDATE
                            |--------------------------------------------------------------------------
                            */
                            if ($oldStatus !== $status) {

                                $c->update([
                                    'status' => $status
                                ]);

                                $updated++;

                                $this->info("Customer {$c->id}: {$oldStatus} → {$status}");

                                $radius->syncCustomer($c);
                            }

                        } catch (\Exception $e) {

                            $failed++;

                            \Log::error('Cron customer error', [
                                'customer_id' => $c->id,
                                'message' => $e->getMessage()
                            ]);
                        }
                    }
                });

            /*
            |--------------------------------------------------------------------------
            | CRON SUCCESS LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Processed: $processed | Updated: $updated | Expired: $expired | Failed: $failed"
            ]);

            return Command::SUCCESS;

        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | CRON FAILED LOG
            |--------------------------------------------------------------------------
            */
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }
}
