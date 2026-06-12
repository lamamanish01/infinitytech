<?php

namespace App\Console\Commands;

use App\Models\CronJob;
use App\Models\CronLog;
use App\Models\Customer;
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

    public function handle(): int
    {
        $job = CronJob::where('key', $this->signature)->first();
        if (!$job || !$job->is_active) {
            $this->info('TR069 Sync Disabled');
            return self::SUCCESS;
        }

        $this->onlineThreshold = (int) config('tr069.online_threshold', env('TR069_ONLINE_THRESHOLD', 10));

        $total = 0;
        $success = 0;
        $failed = 0;

        try {
            $server = Tr069Server::where('status', 'active')->first();
            if (!$server) {
                throw new \Exception('No Active ACS Server Found');
            }

            $paginationStyle = config('tr069.pagination_style', env('TR069_PAGINATION_STYLE', 'page'));
            $page = 1;
            $limit = 1000;
            $firstDeviceLogged = false;

            while (true) {
                $response = $this->fetchDevicePage($server, $page, $limit, $paginationStyle);
                if (!$response->successful()) {
                    throw new \Exception('ACS Returned ' . $response->status());
                }

                $data = $response->json();
                $devices = $this->extractDevicesList($data);
                if (!is_array($devices)) {
                    throw new \Exception('Invalid ACS Response: cannot find devices array');
                }

                $count = count($devices);
                $total += $count;
                $this->info("Page {$page}: {$count} devices");

                foreach ($devices as $device) {
                    if (!$firstDeviceLogged && !empty($device)) {
                        $this->logFirstDevice($device);
                        $firstDeviceLogged = true;
                    }

                    try {
                        $this->syncDevice($device, $server);
                        $success++;
                    } catch (\Throwable $e) {
                        $failed++;
                        Log::error('Device sync failed', ['error' => $e->getMessage()]);
                    }
                }

                if ($count < $limit) break;
                $page++;
            }

            CronLog::create([
                'command' => $this->signature,
                'status'  => 'success',
                'message' => "Total={$total}, Success={$success}, Failed={$failed}",
            ]);

            $this->info("Synced {$success} devices");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            CronLog::create([
                'command' => $this->signature,
                'status'  => 'failed',
                'message' => $e->getMessage(),
            ]);
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    protected function fetchDevicePage(Tr069Server $server, int $page, int $limit, string $style)
    {
        $url = rtrim($server->acs_url, '/') . '/devices';
        $params = ($style === 'offset')
            ? ['offset' => ($page - 1) * $limit, 'limit' => $limit]
            : ['page' => $page, 'limit' => $limit];

        $http = Http::timeout(30);
        if ($server->acs_username && $server->acs_password) {
            $http = $http->withBasicAuth($server->acs_username, $server->acs_password);
        }

        try {
            return $http->get($url, $params);
        } catch (ConnectionException $e) {
            throw new \Exception('Cannot connect to ACS Server');
        }
    }

    protected function extractDevicesList(array $data): ?array
    {
        $possibleKeys = ['devices', 'data', 'items', 'results', 'list', 'rows', 'deviceList', 'DeviceList'];
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }
        if (array_keys($data) === range(0, count($data) - 1)) {
            return $data;
        }
        if (isset($data['data']) && is_array($data['data'])) {
            return $this->extractDevicesList($data['data']);
        }
        return null;
    }

    protected function logFirstDevice(array $device): void
    {
        Log::debug('===== FIRST DEVICE STRUCTURE =====');
        Log::debug('Top-level keys: ' . implode(', ', array_keys($device)));

        $allKeys = $this->getAllKeys($device);
        Log::debug('All nested keys (first 100): ' . implode(', ', array_slice($allKeys, 0, 100)));
        Log::debug('Full device JSON: ' . json_encode($device, JSON_PRETTY_PRINT));

        $this->info("First device logged to storage/logs/laravel.log");
        $this->info("Check the log for all keys and full JSON.");
    }

    protected function getAllKeys(array $data, string $prefix = ''): array
    {
        $keys = [];
        foreach ($data as $k => $v) {
            $full = $prefix ? "{$prefix}.{$k}" : $k;
            $keys[] = $full;
            if (is_array($v)) {
                $keys = array_merge($keys, $this->getAllKeys($v, $full));
            }
        }
        return $keys;
    }

    protected function syncDevice(array $device, Tr069Server $server): void
    {
        // --- Serial number (required) ---
        $serial = $this->extractValue($device, [
            'DeviceID.SerialNumber',
            'serial', 'serialNumber', '_id', 'id'
        ]);
        if (!$serial) return;

        // --- The ACS internal _id is crucial for the /devices/{_id}/tasks endpoint ---
        $internalId = $device['_id'] ?? null;
        if (!$internalId) {
            Log::warning("Device {$serial} has no '_id' field; tasks will fail.");
        }

        // --- Manufacturer ---
        $manufacturer = $this->extractValue($device, [
            'DeviceID.Manufacturer',
            'manufacturer', 'Manufacturer', 'vendor', 'Vendor',
            'DeviceInfo.Manufacturer', 'InternetGatewayDevice.DeviceInfo.Manufacturer',
            'parameters.DeviceID.Manufacturer', 'parameters.DeviceInfo.Manufacturer'
        ]);

        // --- Model ---
        $model = $this->extractValue($device, [
            'DeviceID.ModelName',
            'model', 'Model', 'ModelName',
            'DeviceInfo.ModelName', 'InternetGatewayDevice.DeviceInfo.ModelName',
            'parameters.DeviceID.ModelName', 'parameters.DeviceInfo.ModelName'
        ]);

        // --- Product Class ---
        $productClass = $this->extractValue($device, [
            'DeviceID.ProductClass',
            'productClass', 'ProductClass', 'product_class',
            'InternetGatewayDevice.DeviceInfo.ProductClass',
            'parameters.DeviceID.ProductClass'
        ]);

        // --- OUI ---
        $oui = $this->extractValue($device, [
            'DeviceID.OUI',
            'oui', 'OUI', 'ouiNumber',
            'parameters.DeviceID.OUI'
        ]);

        // --- MAC Address ---
        $mac = $this->extractValue($device, [
            'mac', 'macAddress', 'mac_address', 'MACAddress',
            'LANMACAddress', 'InternetGatewayDevice.LANDevice.1.LANMACAddress',
            'parameters.LANMACAddress', 'DeviceID.MACAddress'
        ]);

        // --- IP Address ---
        $ipAddress = $this->extractValue($device, [
            'ip', 'ip_address', 'IPAddress', 'WANIPAddress',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.ExternalIPAddress'
        ]);

        // --- PPPoE credentials ---
        $pppoeUsername = $this->extractValue($device, [
            'pppoe_username', 'username', 'Username', 'ppp_username',
            'WANPPPConnection.Username',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username'
        ]);
        $pppoePassword = $this->extractValue($device, [
            'pppoe_password', 'password', 'Password', 'ppp_password',
            'WANPPPConnection.Password',
            'InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password'
        ]);

        // --- WiFi 2.4 GHz ---
        $wifi24Ssid = $this->extractValue($device, [
            'wifi24_ssid', 'SSID',
            'WLANConfiguration.1.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID'
        ]);
        $wifi24Password = $this->extractValue($device, [
            'wifi24_password', 'KeyPassphrase',
            'WLANConfiguration.1.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase'
        ]);
        $wifi24Hidden = $this->extractBoolean($device, [
            'wifi24_hidden', 'SSIDAdvertisementEnabled',
            'WLANConfiguration.1.SSIDAdvertisementEnabled',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSIDAdvertisementEnabled'
        ]);

        // --- WiFi 5 GHz ---
        $wifi5Ssid = $this->extractValue($device, [
            'wifi5_ssid', 'WLANConfiguration.5.SSID',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID'
        ]);
        $wifi5Password = $this->extractValue($device, [
            'wifi5_password', 'WLANConfiguration.5.KeyPassphrase',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.KeyPassphrase'
        ]);
        $wifi5Hidden = $this->extractBoolean($device, [
            'wifi5_hidden', 'WLANConfiguration.5.SSIDAdvertisementEnabled',
            'InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSIDAdvertisementEnabled'
        ]);

        // --- Last inform ---
        $lastInformRaw = $this->extractValue($device, [
            '_lastInform', 'lastInform', 'LastInform',
            'InternetGatewayDevice.DeviceInfo.LastInform'
        ]);
        $lastInformDate = $lastInformRaw ? Carbon::parse($lastInformRaw) : null;

        // --- Customer linking ---
        $customer = $pppoeUsername ? Customer::where('username', $pppoeUsername)->first() : null;

        // --- Update or create ---
        // CRITICAL FIX: Use the internal _id as encoded_id (the correct identifier for tasks)
        Tr069Device::updateOrCreate(
            ['serial' => $serial],
            [
                'tr069_server_id'   => $server->id,
                'customer_id'       => $customer?->id,
                '_id'               => $internalId,
                'encoded_id'        => $internalId,      // <<-- store the ACS _id here
                'serial'            => $serial,
                'manufacturer'      => $manufacturer,
                'model'             => $model,
                'product_class'     => $productClass,
                'oui'               => $oui,
                'ppp_username'      => $pppoeUsername,
                'ppp_password'      => $pppoePassword,
                'onu_mac'           => $mac,
                'mac_address'       => $mac,
                'ip_address'        => $ipAddress,
                'wifi_24_ssid'      => $wifi24Ssid,
                'wifi_24_password'  => $wifi24Password,
                'wifi_24_hidden'    => $wifi24Hidden,
                'wifi_5_ssid'       => $wifi5Ssid,
                'wifi_5_password'   => $wifi5Password,
                'wifi_5_hidden'     => $wifi5Hidden,
                'last_inform'       => $lastInformDate,
                'status'            => $this->getStatus($lastInformDate),
            ]
        );
    }

    protected function extractValue(array $device, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $this->deepFind($device, $key);
            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }
        return null;
    }

    private function deepFind(array $data, string $needle)
    {
        if (array_key_exists($needle, $data)) {
            return $this->unwrap($data[$needle]);
        }
        if (str_contains($needle, '.')) {
            $value = data_get($data, $needle);
            if ($value !== null) {
                return $this->unwrap($value);
            }
        }
        foreach ($data as $value) {
            if (is_array($value)) {
                $result = $this->deepFind($value, $needle);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    private function unwrap($value)
    {
        if (is_array($value)) {
            if (isset($value['_value'])) return $value['_value'];
            if (isset($value['value']))  return $value['value'];
            if (count($value) === 1 && is_numeric(array_key_first($value))) {
                return reset($value);
            }
            return null;
        }
        return $value;
    }

    private function extractBoolean(array $device, array $keys): bool
    {
        foreach ($keys as $key) {
            $value = $this->deepFind($device, $key);
            if ($value !== null) {
                if (is_bool($value)) return $value;
                if (is_numeric($value)) return (bool) $value;
                if (is_string($value)) return in_array(strtolower($value), ['true', '1', 'yes']);
            }
        }
        return false;
    }

    protected function getStatus(?Carbon $lastInform): string
    {
        if (!$lastInform) return 'offline';
        return $lastInform->diffInMinutes(now()) <= $this->onlineThreshold ? 'online' : 'offline';
    }
}
