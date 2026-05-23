<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Console\Command;

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
    protected $description = 'Disconnect expired users and sync status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $radius = app(\App\Services\RadiusService::class);
        $mk = app(\App\Services\MikroTikService::class);

        Customer::with(['mikrotik', 'internetPlan'])
            ->chunkById(200, function ($customers) use ($radius, $mk) {

                foreach ($customers as $c) {
                    $status = $c->calculateStatus();
                    $oldStatus = $c->status;

                    if ($status === 'expired') {

                        if ($c->mikrotik) {
                            $mk->disconnectPPPoE($c->mikrotik, $c->username);
                        }

                        $radius->removeCustomer($c);
                    }

                    if ($oldStatus !== $status) {

                        $c->update([
                            'status' => $status
                        ]);

                        $this->info("Customer {$c->id}: {$oldStatus} → {$status}");

                        $radius->syncCustomer($c);
                    }
                }
            });

        return Command::SUCCESS;
    }
}
