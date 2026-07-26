<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use App\Models\Monitor;
use App\Helpers\Activity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class MonitorPing extends Command
{
    protected $signature = 'monitor:ping';
    protected $description = 'Check all monitors (ping/SNMP) and log status changes';

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->error("Cron job inactive or not found.");
            return self::FAILURE;
        }

        try {
            $monitors = Monitor::all();
            $results = [];

            foreach ($monitors as $monitor) {
                $oldStatus = $monitor->status;

                // Run check
                $result = $monitor->check_type === Monitor::CHECK_PING
                    ? $this->checkPing($monitor)
                    : $this->checkSnmp($monitor);

                // Update counters and status
                $monitor->increment('total_count');
                if ($result['success']) $monitor->increment('success_count');

                $newStatus = $result['success'] ? Monitor::STATUS_UP : Monitor::STATUS_DOWN;
                $uptime = $monitor->total_count > 0
                    ? ($monitor->success_count / $monitor->total_count) * 100
                    : 0;

                $monitor->update([
                    'status'          => $newStatus,
                    'response_time'   => $result['response_time'] ?? null,
                    'uptime'          => round($uptime, 2),
                    'last_checked_at' => now(),
                ]);

                // Log status change via Activity
                if ($oldStatus !== $newStatus) {
                    $this->logStatusChange($monitor, $oldStatus, $newStatus);
                }

                $results[] = "{$monitor->name} ({$monitor->host}) " .
                             ($result['success'] ? '✅ UP' : '❌ DOWN');
                $this->info(end($results));
            }

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => 'Checks done: ' . implode('; ', $results),
            ]);

            return self::SUCCESS;

        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);
            $this->error("❌ Failed: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function checkPing(Monitor $monitor): array
    {
        $timeout = config('monitor.ping_timeout', 2);
        $count   = config('monitor.ping_count', 1);

        $process = Process::run("ping -c {$count} -W {$timeout} {$monitor->host}");
        $success = $process->successful();
        $output  = $process->output();

        $responseTime = null;
        if ($success && preg_match('/time=([0-9.]+)\s*ms/', $output, $matches)) {
            $responseTime = (float) $matches[1];
        }

        return ['success' => $success, 'response_time' => $responseTime];
    }

    private function checkSnmp(Monitor $monitor): array
    {
        try {
            $version = match ($monitor->snmp_version) {
                'v1'   => SNMP_VERSION_1,
                'v2c'  => SNMP_VERSION_2c,
                'v3'   => SNMP_VERSION_3,
                default => SNMP_VERSION_2c,
            };

            $session = new \SNMP($version, $monitor->host, $monitor->snmp_community);
            $session->setSecurity('noAuthNoPriv');
            $session->setTimeout($monitor->snmp_timeout * 1000000);
            $session->setPort($monitor->snmp_port);

            $start = microtime(true);
            $value = $session->get($monitor->snmp_oid);
            $duration = (microtime(true) - $start) * 1000;

            return [
                'success'       => $value !== false,
                'response_time' => $value !== false ? round($duration, 2) : null,
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'response_time' => null];
        }
    }

    private function logStatusChange(Monitor $monitor, string $oldStatus, string $newStatus)
    {
        $icon = $newStatus === Monitor::STATUS_UP
            ? 'fas fa-check-circle text-success'
            : 'fas fa-exclamation-triangle text-danger';

        $message = "Monitor '{$monitor->name}' changed from " . strtoupper($oldStatus) .
                   " → " . strtoupper($newStatus);

        Activity::add(
            'Monitor Status Change',
            $message,
            $icon,
            $monitor->host,
            route('monitors.show', $monitor->id)
        );

        $this->info("📝 Activity logged: {$message}");
    }
}
