<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use App\Models\Tr069Device;
use App\Models\Tr069Server;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncTr069Devices extends Command
{
    protected $signature = 'tr069:sync-devices';
    protected $description = 'Sync TR-069 devices from ACS server';

    protected $onlineThreshold = 10;

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if (!$job || !$job->is_active) {
            $this->info('TR-069 sync cron disabled');
            return Command::SUCCESS;
        }

        $this->onlineThreshold = config(
            'tr069.online_threshold',
            env('TR069_ONLINE_THRESHOLD', 10)
        );

        $total = 0;
        $success = 0;
        $failed = 0;
        $skipped = 0;

        try {
            $server = Tr069Server::where('status', 'active')->first();

            if (!$server || !$server->acs_url) {
                throw new \Exception("No active TR-069 server found");
            }

            $baseUrl = rtrim($server->acs_url, '/');
            $page = 1;

            while (true) {

                $response = $this->fetchDevicePage($baseUrl, $page);

                if (!$response->successful()) {
                    throw new \Exception(
                        "Failed to fetch page {$page}: " . $response->status()
                    );
                }

                $data = $response->json();

                $devices = $data['devices']
                    ?? $data['data']
                    ?? $data['items']
                    ?? (is_array($data) ? $data : []);

                if (!is_array($devices)) {
                    throw new \Exception("Invalid ACS response format on page {$page}");
                }

                $count = count($devices);
                $total += $count;

                foreach ($devices as $device) {
                    try {
                        $result = $this->processDevice($device, $server);

                        if ($result === 'success') {
                            $success++;
                        } elseif ($result === 'skipped') {
                            $skipped++;
                        }

                    } catch (\Throwable $e) {
                        $failed++;

                        $serial = $device['serialNumber']
                            ?? $device['_id']
                            ?? $device['id']
                            ?? 'unknown';

                        Log::error('TR-069 device sync error', [
                            'serial' => $serial,
                            'error'  => $e->getMessage(),
                        ]);
                    }
                }

                // FIXED PAGINATION LOGIC
                if ($count < 1000) {
                    break;
                }

                $page++;
            }

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Total: {$total} | Success: {$success} | Failed: {$failed} | Skipped: {$skipped}",
            ]);

            $this->info("✔ TR-069 Sync Completed");
            $this->info("Total: {$total}, Success: {$success}, Failed: {$failed}, Skipped: {$skipped}");

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::error('TR-069 Sync failed', [
                'error' => $e->getMessage()
            ]);

            $this->error("❌ " . $e->getMessage());

            return Command::FAILURE;
        }
    }

    protected function fetchDevicePage(string $baseUrl, int $page)
    {
        $url = $baseUrl . '/devices';

        $http = Http::timeout(30);

        $username = config('tr069.acs_username', env('TR069_ACS_USERNAME'));
        $password = config('tr069.acs_password', env('TR069_ACS_PASSWORD'));

        if ($username && $password) {
            $http = $http->withBasicAuth($username, $password);
        }

        $token = config('tr069.acs_token', env('TR069_ACS_TOKEN'));
        if ($token) {
            $http = $http->withToken($token);
        }

        return $http->get($url, [
            'page'  => $page,
            'limit' => 1000
        ]);
    }

    protected function processDevice(array $device, Tr069Server $server): string
    {
        $serial = $device['serialNumber']
            ?? $device['_id']
            ?? $device['id']
            ?? null;

        if (!$serial) {
            Log::warning('TR-069 device skipped: no serial');
            return 'skipped';
        }

        $username = $this->extractScalar($device, [
            'username',
            'user',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username'
        ]);

        $mac = $this->extractScalar($device, [
            'mac',
            'mac_address',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.MACAddress'
        ]);

        $ip = $this->extractScalar($device, [
            'ip',
            'ip_address',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.IPAddress'
        ]);

        $manufacturer = $this->extractScalar($device, [
            'manufacturer',
            'InternetGatewayDevice.DeviceInfo.Manufacturer'
        ]);

        $model = $this->extractScalar($device, [
            'model',
            'InternetGatewayDevice.DeviceInfo.ModelName'
        ]);

        $productClass = $this->extractScalar($device, [
            'productClass',
            'product_class',
            'DeviceID.ProductClass'
        ]);

        $oui = $this->extractScalar($device, ['oui', 'OUI']);

        $lastInformRaw = $this->extractScalar($device, [
            '_lastInform',
            'lastInform',
            'InternetGatewayDevice.DeviceInfo.LastInform'
        ]);

        $lastInform = $lastInformRaw ? Carbon::parse($lastInformRaw) : null;

        $status = $this->getStatus($lastInform);

        Tr069Device::updateOrCreate(
            ['serial_number' => $serial],
            [
                'tr069_server_id' => $server->id,
                'customer_id'     => null,
                'username'        => $username,
                'oui'             => $oui,
                'product_class'   => $productClass,
                'manufacturer'    => $manufacturer,
                'model'           => $model,
                'mac_address'     => $mac,
                'ip_address'      => $ip,
                'last_inform'     => $lastInform,
                'status'          => $status,
            ]
        );

        return 'success';
    }

    private function extractScalar(array $device, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($device, $key);

            if (is_array($value)) {
                $value = $value['_value'] ?? $value['value'] ?? null;
            }

            if (!empty($value)) {
                return (string) $value;
            }
        }

        return null;
    }

    protected function getStatus(?Carbon $lastInform): string
    {
        if (!$lastInform) {
            return 'offline';
        }

        return $lastInform->diffInMinutes(now()) < $this->onlineThreshold
            ? 'online'
            : 'offline';
    }
}
