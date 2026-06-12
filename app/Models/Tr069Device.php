<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Tr069Device extends Model
{
    protected $table = 'tr069_devices';

    protected $fillable = [
        'tr069_server_id', 'customer_id', '_id', 'encoded_id', 'serial',
        'oui', 'product_class', 'manufacturer', 'model',
        'ppp_username', 'ppp_password',
        'onu_mac', 'router_mac', 'mac_address', 'ip_address',
        'wifi_24_ssid', 'wifi_24_password', 'wifi_24_hidden',
        'wifi_5_ssid', 'wifi_5_password', 'wifi_5_hidden',
        'user_username', 'user_password', 'admin_username', 'admin_password',
        'last_inform', 'status',
    ];

    protected $casts = [
        'wifi_24_hidden' => 'boolean',
        'wifi_5_hidden'  => 'boolean',
        'last_inform'    => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Tr069Server::class, 'tr069_server_id');
    }

    public function logs()
    {
        return $this->hasMany(Tr069Log::class, 'tr069_device_id');
    }

    // ------------------------------------------------------------------
    // Task sender with automatic logging
    // ------------------------------------------------------------------
    private function sendTask(string $action, array $payload): bool
    {
        if (!$this->encoded_id) {
            \Log::error("Device {$this->serial} has no encoded_id (ACS _id)");
            return false;
        }

        $url = $this->server->baseUrl() . "/devices/{$this->encoded_id}/tasks?connection_request";

        $log = Tr069Log::create([
            'tr069_device_id' => $this->id,
            'action'          => $action,
            'status'          => 'pending',
            'request_payload' => $payload,
        ]);

        try {
            $response = Http::timeout(30)->post($url, $payload);
            $success = $response->successful();
            $log->update([
                'status'           => $success ? 'success' : 'failed',
                'response_payload' => $response->json(),
                'message'          => $success ? null : $response->body(),
            ]);
            return $success;
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'message' => $e->getMessage()]);
            return false;
        }
    }

    // ------------------------------------------------------------------
    // Device actions
    // ------------------------------------------------------------------
    public function setParameterValues(array $params): bool
    {
        return $this->sendTask('setParameterValues', [
            'name' => 'setParameterValues',
            'parameterValues' => $params,
        ]);
    }

    public function reboot(): bool
    {
        return $this->sendTask('reboot', ['name' => 'reboot']);
    }

    public function factoryReset(): bool
    {
        return $this->sendTask('factoryReset', ['name' => 'factoryReset']);
    }

    public function refresh(): bool
    {
        return $this->sendTask('refresh', ['name' => 'refresh']);
    }

    // ----- WiFi -----
    public function changeWiFiName(string $ssid, int $band = 1): bool
    {
        $path = $band == 1
            ? "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSID"
            : "InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSID";
        return $this->setParameterValues([[$path, $ssid, 'xsd:string']]);
    }

    public function changeWiFiPassword(string $password, int $band = 1): bool
    {
        $path = $band == 1
            ? "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.KeyPassphrase"
            : "InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.KeyPassphrase";
        return $this->setParameterValues([[$path, $password, 'xsd:string']]);
    }

    public function hideSSID(bool $hide, int $band = 1): bool
    {
        $path = $band == 1
            ? "InternetGatewayDevice.LANDevice.1.WLANConfiguration.1.SSIDAdvertisementEnabled"
            : "InternetGatewayDevice.LANDevice.1.WLANConfiguration.5.SSIDAdvertisementEnabled";
        $value = $hide ? false : true;
        return $this->setParameterValues([[$path, $value, 'xsd:boolean']]);
    }

    // ----- PPPoE -----
    public function changePPPInfo(string $username, string $password): bool
    {
        return $this->setParameterValues([
            ["InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username", $username, 'xsd:string'],
            ["InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password", $password, 'xsd:string'],
        ]);
    }

    public function changePPPUsername(string $username): bool
    {
        $path = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Username";
        return $this->setParameterValues([[$path, $username, 'xsd:string']]);
    }

    public function changePPPPassword(string $password): bool
    {
        $path = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.2.WANPPPConnection.1.Password";
        return $this->setParameterValues([[$path, $password, 'xsd:string']]);
    }

    // ----- VLAN -----
    public function changeVlan(int $vlanId): bool
    {
        $path = "InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1.X_HW_VLAN";
        return $this->setParameterValues([[$path, (string)$vlanId, 'xsd:string']]);
    }

    // ----- User / Admin login -----
    public function changeUserLogin(string $username, string $password): bool
    {
        return $this->setParameterValues([
            ["InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.1.UserName", $username, 'xsd:string'],
            ["InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.1.Password", $password, 'xsd:string'],
        ]);
    }

    public function changeAdminLogin(string $username, string $password): bool
    {
        return $this->setParameterValues([
            ["InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.UserName", $username, 'xsd:string'],
            ["InternetGatewayDevice.UserInterface.X_HW_WebUserInfo.2.Password", $password, 'xsd:string'],
        ]);
    }

    // ----- File upload (download task) -----
    public function uploadFiles(string $fileUrl): bool
    {
        return $this->sendTask('download', ['name' => 'download', 'file' => $fileUrl]);
    }
}
