<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class bindMac extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customer:bind-mac';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto bind missing MAC from active sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $customers = Customer::whereNull('mac_address')->get();

        foreach ($customers as $customer) {

            $session = DB::table('radacct')
                ->where('username', $customer->username)
                ->whereNull('acctstoptime')
                ->first();

            if (!$session || !$session->callingstationid) {
                continue;
            }

            $customer->update([
                'mac_address' => strtoupper($session->callingstationid)
            ]);

            $this->info("MAC bound: {$customer->username}");
        }

        return 0;
    }
}
