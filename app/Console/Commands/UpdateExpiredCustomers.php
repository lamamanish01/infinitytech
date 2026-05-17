<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\RadiusService;
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
    protected $description = 'Disconnect expired users';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $customers = Customer::all();

        foreach ($customers as $customer) {

            $newStatus = $customer->calculateStatus();

            if ($customer->status !== $newStatus) {

                $customer->status = $newStatus;
                $customer->save();

                $this->info("Customer {$customer->id} changed to {$newStatus}");

                // if ($newStatus === 'expired') {
                //     RadiusService::disconnect($customer);
                // }
            }
        }

        $this->info('Customer expiry check completed.');
    }
}
