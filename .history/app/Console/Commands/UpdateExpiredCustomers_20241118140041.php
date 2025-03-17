<?php

namespace App\Console\Commands;

use App\Models\RadCheck;
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

        DB::table('radcheck')->where('Expiration', '<', now)->
        // $expiredCustomers = RadCheck::where('Expiration', '<', now())->get();

        if ($expiredCustomers->isEmpty())
        {
            $this->info('No expired customers were found.');
        }

        foreach ($expiredCustomers as $$expiredCustomer)
        {
            RadReply::updateOrInsert([
                'username' => $expiredCustomer->username,
                'attribute' => 'Framed-Pool',
                'op' => ':=',
                'value' => 'Expired-Pool'
            ]);

            $this->disconnectUser($expiredCustomer->username);

            $this->info("User {$expiredCustomer->username}'s Framed-Pool updated to expired-pool.");
        }
    }

    private function disconnectCustomer($username)
    {
        $session = RadAcct::where('username', $username)->whereNull('acctstoptime')->first();

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
