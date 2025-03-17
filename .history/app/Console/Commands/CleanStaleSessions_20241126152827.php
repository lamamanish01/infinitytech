<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CleanStaleSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:clean-stale-sessions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove stale sessions from the FreeRADIUS radacct table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $radacct = RadAcct::whereNull('acctstoptime')
            ->where('acctstarttime')
    }
}
