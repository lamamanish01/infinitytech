<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CronJobSeeder extends Seeder
{
    /**
     * List of cron job signatures and their friendly names.
     */
    protected array $jobs = [
        ['key' => 'customers:bind-mac',            'name' => 'Bind MAC Address',            'frequency' => 'daily'],
        ['key' => 'radius:clean-radacct',          'name' => 'Clean RADIUS Accounting',     'frequency' => 'daily'],
        ['key' => 'radius:clean-logs',             'name' => 'Clean RADIUS Logs',           'frequency' => 'daily'],
        ['key' => 'customers:discontinue',         'name' => 'Discontinue Inactive Users',  'frequency' => 'daily'],
        ['key' => 'customers:clean-stale-sessions','name' => 'Clean Stale RADIUS Sessions', 'frequency' => 'daily'],
        ['key' => 'monitor:ping',                  'name' => 'Monitor Ping',                'frequency' => 'everyMinute'],
        ['key' => 'sms:send-expiry-sms',           'name' => 'Send Expiry SMS Alerts',      'frequency' => 'daily'],
        ['key' => 'customers:sync-online',         'name' => 'Sync Online Status',          'frequency' => 'everyFiveMinutes'],
        ['key' => 'tr069:sync-devices',            'name' => 'Sync TR-069 Devices',         'frequency' => 'hourly'],
        ['key' => 'customers:update-expired',      'name' => 'Update Expired Customers',    'frequency' => 'daily'],
    ];

    public function run(): void
    {
        foreach ($this->jobs as $job) {
            DB::table('cron_jobs')->updateOrInsert(
                ['key' => $job['key']],
                [
                    'name'      => $job['name'],
                    'is_active' => true,
                    'frequency' => $job['frequency'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
