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
        $mk     = app(\App\Services\MikroTikService::class);

        $stats = [
            'processed' => 0,
            'updated'   => 0,
            'expired'   => 0,
            'failed'    => 0,
        ];

        Customer::with(['mikrotik'])
            ->chunkById(200, function ($customers) use ($radius, $mk, &$stats) {

                foreach ($customers as $customer) {

                    try {
                        $stats['processed']++;

                        // 🔥 always fresh data
                        $customer->refresh();

                        // 🎯 single truth
                        $newStatus = $customer->calculateStatus();
                        $oldStatus = $customer->status;

                        // skip if no change
                        if ($newStatus === $oldStatus) {
                            continue;
                        }

                        // update status
                        $customer->update([
                            'status' => $newStatus
                        ]);

                        $stats['updated']++;

                        $radius->syncCustomer($customer);

                        // ❌ expired handling
                        if ($newStatus === 'expired') {

                            $session = \DB::table('radacct')
                                ->where('username', $customer->username)
                                ->whereNull('acctstoptime')
                                ->latest()
                                ->first();

                            if ($session && $customer->mikrotik) {
                                $mk->disconnectPPPoE(
                                    $customer->mikrotik,
                                    $session->username
                                );
                            }

                            $radius->removeCustomer($customer);

                            $stats['expired']++;
                        }

                    } catch (\Throwable $e) {

                        $stats['failed']++;

                        \Log::error('Customer cron error', [
                            'customer_id' => $customer->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

        CronLog::create([
            'command' => $this->signature,
            'status'  => 'success',
            'message' =>
                "Processed: {$stats['processed']} | " .
                "Updated: {$stats['updated']} | " .
                "Expired: {$stats['expired']} | " .
                "Failed: {$stats['failed']}"
        ]);

        return Command::SUCCESS;
    }
}
