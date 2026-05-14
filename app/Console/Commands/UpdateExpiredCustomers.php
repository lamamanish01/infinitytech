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
    protected $description = 'Check and update exipred customers & change  Framed-Pool in radreply';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customers = Customer::whereDate('expire_date', '<', now())
            ->where('status', 'active')
            ->get();

        foreach ($customers as $customer) {

            $customer->update([
                'status' => 'expired'
            ]);

            RadiusService::removeCustomer($customer);
        }

        $this->info("Expired users removed from FreeRADIUS");
    }

}
