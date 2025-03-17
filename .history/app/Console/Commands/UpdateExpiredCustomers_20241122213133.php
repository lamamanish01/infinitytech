<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Nas;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
use App\Models\Recharge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
        $expiredCustomers = RadCheck::where('attribute', 'Expiration')
            ->where('value', '<', Carbon::now()->toDateTimeString())->get();

        if ($expiredCustomers->isEmpty())
        {
            $this->info('No expired customers were found.');
        }

        foreach ($expiredCustomers as $expiredCustomer)
        {
            // RadReply::where('username', $expiredCustomer->username)
            //     ->where('attribute', 'Framed-Pool')
            //     ->update(['value' => 'Expired-Pool']);

            // RadReply::updateOrCreate([
            //     'username' => $expiredCustomer->username,
            //     'attribute' => 'Mikrotik-Address-List',
            //     'op' => ':=',
            //     'value' => 'Exipred',
            // ]);

            $username = $expiredCustomer->username;

            $this->disconnectCustomer($username);

            $this->info("Customer {$expiredCustomer->username}'s is expired.");
        }
    }
}
