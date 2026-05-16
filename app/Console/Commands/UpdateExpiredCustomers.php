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
    $customers = Customer::whereNotNull('expire_date')
        ->whereIn('status', ['active', 'grace'])
        ->get();

    $now = now();

    foreach ($customers as $customer) {

        if ($now->gt($customer->expire_date)) {

            $customer->update([
                'status' => 'expired'
            ]);

            RadiusService::disconnect($customer);
        }
    }

        $this->info("Expired users processed successfully");
    }
}
