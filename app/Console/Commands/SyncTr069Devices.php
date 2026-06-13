<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use App\Models\Tr069Device;
use App\Models\Tr069Server;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncTr069Devices extends Command
{
    protected $signature = 'tr069:sync-devices';

    protected $description = 'Sync TR069 Devices From ACS';

    protected int $onlineThreshold = 10;

    public function handle()
    {
        $job = CronJob::where('key', $this->signature)->first();

        if ($job && !$job->is_active) {
            $this->warn('TR069 Sync Disabled');
            return self::SUCCESS;
        }

        $this->onlineThreshold = config(
            'tr069.online_threshold',
            env('TR069_ONLINE_THRESHOLD', 10)
        );

        $total = 0;
        $success = 0;
        $failed = 0;

        try {

            $servers = Tr069Server::where('status', 'active')->get();

            if ($servers->isEmpty()) {
                throw new \Exception('No active ACS server found');
            }

            foreach ($servers as $server) {

                $response = $this->fetchDevices($server);

                if (!$response->successful()) {
                    throw new \Exception(
                        "ACS returned status {$response->status()}"
                    );
                }

                $devices = $response->json();

                if (!is_array($devices)) {
                    throw new \Exception('Invalid ACS response');
                }

                foreach ($devices as $device) {

                    try {

                        $this->syncDevice(
                            $device,
                            $server
                        );

                        $success++;
                        $total++;

                    } catch (\Throwable $e) {

                        $failed++;

                        Log::error('TR069 Device Sync Failed', [
                            'server_id' => $server->id,
                            'device' => $device['_id'] ?? null,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            CronLog::create([
                'command' => $this->signature,
                'status' => 'success',
                'message' => "Total={$total}, Success={$success}, Failed={$failed}",
            ]);

            $this->info(
                "TR069 Sync Complete. Success={$success}"
            );

            return self::SUCCESS;

        } catch (\Throwable $e) {

            CronLog::create([
                'command' => $this->signature,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::error('TR069 Sync Error', [
                'error' => $e->getMessage(),
            ]);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function fetchDevices(Tr069Server $server)
    {
        $url = rtrim($server->acs_url, '/') . '/devices';

        $http = Http::timeout(120);

        if (
            !empty($server->acs_username) &&
            !empty($server->acs_password)
        ) {
            $http = $http->withBasicAuth(
                $server->acs_username,
                $server->acs_password
            );
        }

        try {

            return $http->get($url);

        } catch (ConnectionException $e) {

            throw new \Exception(
                "Unable to connect ACS: {$server->acs_url}"
            );
        }
    }

    protected function syncDevice(
        array $device,
        Tr069Server $server
    ): void {

        $fullId = $device['_id'] ?? null;

        $serial =
            data_get(
                $device,
                '_deviceId._SerialNumber'
            );

        if (!$serial) {
            return;
        }

        $lastInform =
            $device['_lastInform']
            ?? null;

        $lastInformDate = $lastInform
            ? Carbon::parse($lastInform)
            : null;

        $oui =
            data_get(
                $device,
                '_deviceId._OUI'
            );

        $productClass =
            data_get(
                $device,
                '_deviceId._ProductClass'
            );

        $manufacturer =
            data_get(
                $device,
                '_deviceId._Manufacturer'
            );

        $model =
            data_get(
                $device,
                'InternetGatewayDevice.DeviceInfo.ModelName._value'
            );

        /*
        |--------------------------------------------------------------------------
        | PPP Username
        |--------------------------------------------------------------------------
        */

        $ppp1 =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.Username._value'
            );

        $ppp2 =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username._value'
            );

        $ppp3 =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.2.Username._value'
            );

        $pppUsername = $ppp1 ? $ppp2 : $ppp3;

        /*
        |--------------------------------------------------------------------------
        | ONU MAC
        |--------------------------------------------------------------------------
        */

        $onuMac =
            data_get(
                $device,
                'InternetGatewayDevice.LANDevice.1.LANEthernetInterfaceConfig.1.MACAddress._value'
            );

        /*
        |--------------------------------------------------------------------------
        | Router MAC
        |--------------------------------------------------------------------------
        */

        $routerMac1 =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.MACAddress._value'
            );

        $routerMac2 =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.MACAddress._value'
            );

        $routerMac = $routerMac1 ?: $routerMac2;

        /*
        |--------------------------------------------------------------------------
        | WIFI
        |--------------------------------------------------------------------------
        */

        $wifi24 =
            data_get(
                $device,
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID._value'
            );

        $wifi5 =
            data_get(
                $device,
                'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID._value'
            );

        /*
        |--------------------------------------------------------------------------
        | IP
        |--------------------------------------------------------------------------
        */

        $ip =
            data_get(
                $device,
                'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ExternalIPAddress._value'
            );

        Tr069Device::updateOrCreate(

            [
                'serial' => $serial,
            ],

            [
                'tr069_server_id' => $server->id,

                '_id' => $fullId,
                'encoded_id' => urlencode($fullId),

                'serial' => $serial,

                'oui' => $oui,
                'product_class' => $productClass,

                'manufacturer' => $manufacturer,
                'model' => $model,

                'ppp_username' => $pppUsername,

                'onu_mac' => $onuMac,

                'router_mac' => $routerMac,
                'mac_address' => $routerMac,

                'ip_address' => $ip,

                'wifi_24_ssid' => $wifi24,
                'wifi_5_ssid' => $wifi5,

                'last_inform' => $lastInformDate,

                'status' => $this->getStatus(
                    $lastInformDate
                ),
            ]
        );
    }

    protected function getStatus(
        ?Carbon $lastInform
    ): string {

        if (!$lastInform) {
            return 'offline';
        }

        return $lastInform->diffInMinutes(now())
            <= $this->onlineThreshold
            ? 'online'
            : 'offline';
    }
}
