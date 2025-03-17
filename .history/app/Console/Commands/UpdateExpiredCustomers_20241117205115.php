<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateExpiredCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:update-expired-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and update exipred customers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        //
    }
}
