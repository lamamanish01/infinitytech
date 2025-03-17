<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\RadAcct;
use App\Models\RadCheck;
use App\Models\RadReply;
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
        $recharge = Recharge::wh
        $expiredCustomers = RadCheck::where('attribute', 'Expiration')
            ->where('value', '<', Carbon::now()->toDateTimeString())->get();

        if ($expiredCustomers->isEmpty())
        {
            $this->info('No expired customers were found.');
        }

        foreach ($expiredCustomers as $expiredCustomer)
        {
            // RadReply::updateOrInsert([
            //     'username' => $expiredCustomer->username,
            //     'attribute' => 'Expiration',
            //     'op' => ':=',
            //     'value' => 'Expired-Pool',
            // ]);
            RadReply::where('username', $expiredCustomer->username)
                ->where('attribute', 'Framed-Pool')
                ->update(['value' => 'Expired-Pool']);

            $this->info("User {$expiredCustomer->username}'s Framed-Pool updated to expired-pool.");
        }
    }

    private function disconnectCustomer($username)
    {
        $session = RadAcct::where('username', $username)
            ->whereNull('acctstoptime')->first();

        $nasIp = env('RADIUS_NAS_IP', $session->nasipaddress);
        $port = env('RADIUS_PORT', 3799);
        $sharedSecret = env('RADIUS_SHARED_SECRET', 'radius123');

        $acctSessionId = $session->acctsessionid;

        $command = "echo \"User-Name={$username}\nAcct-Session-Id={$acctSessionId}\nNAS-IP-Address={$nasIp}\" | radclient {$nasIp}:{$port} disconnect {$sharedSecret}";

        exec($command, $output, $result);

        if ($result === 0) {
            $this->info("User {$username} disconnected successfully.");
        } else {
            $this->error("Failed to disconnect user {$username}. Error: " . implode("\n", $output));
        }
    }
}
