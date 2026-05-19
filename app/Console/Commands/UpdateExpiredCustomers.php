<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\MikroTikService;
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
        $radius = app(RadiusService::class);
        $mk = app(MikroTikService::class);

        Customer::with('mikrotik','internetPlan')
            ->chunkById(200, function ($customers) use ($radius,$mk) {

                foreach ($customers as $c) {

                    $status = $c->calculateStatus();

                    if ($c->status === $status) continue;

                    $c->update(['status'=>$status]);

                    /*
                    |--------------------------------------------------------------------------
                    | 1. FREE RADIUS (AUTH ONLY)
                    |--------------------------------------------------------------------------
                    */
                    $radius->syncCustomer($c);

                    if ($status === 'expired') {
                        $mk->disconnectPPPoE($c->mikrotik, $c->username);
                    }
                }
            });

        return self::SUCCESS;
    }
}
