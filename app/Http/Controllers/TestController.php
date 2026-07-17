<?php

namespace App\Http\Controllers;

use App\Models\Mikrotik;
use RouterOS\Client;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    /**
     * Test /interface/monitor-traffic for a specific interface on a router.
     * GET /test/monitor/{routerId}/{interface?}
     */
    public function monitor($routerId, $interface = null)
    {
        $router = Mikrotik::find($routerId);
        if (!$router) {
            return response()->json(['error' => 'Router not found'], 404);
        }

        // If no interface given, try to get the first active PPP interface
        if (!$interface) {
            try {
                $client = new Client([
                    'host' => $router->host,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => $router->port ?? 8728,
                ]);
                $sessions = $client->query(
                    (new Query('/ppp/active/print'))
                )->read();
                if (!empty($sessions)) {
                    $interface = $sessions[0]['interface'] ?? null;
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        if (!$interface) {
            return response()->json(['error' => 'No interface provided or found'], 400);
        }

        try {
            $client = new Client([
                'host' => $router->host,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->port ?? 8728,
            ]);

            $query = (new Query('/interface/monitor-traffic'))
                ->equal('interface', $interface)
                ->equal('once', 'yes');

            $result = $client->query($query)->read();

            return response()->json([
                'success' => true,
                'router' => $router->host,
                'interface' => $interface,
                'result' => $result,
                'raw' => $result[0] ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 500);
        }
    }

    /**
     * Get monitor-traffic for ALL active PPP sessions of a username (or all).
     * GET /test/monitor-all/{username?}
     */
    public function monitorAllPpp($username = null)
    {
        $routers = Mikrotik::where('is_active', 1)->get();
        $results = [];

        foreach ($routers as $router) {
            try {
                $client = new Client([
                    'host' => $router->host,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => $router->port ?? 8728,
                ]);

                // Build query for active sessions
                $query = (new Query('/ppp/active/print'));
                if ($username) {
                    $query->where('name', $username);
                }
                $sessions = $client->query($query)->read();

                if (empty($sessions)) {
                    $results[$router->host] = ['sessions' => [], 'message' => 'No active sessions'];
                    continue;
                }

                $routerResults = [];
                foreach ($sessions as $session) {
                    $interface = $session['interface'] ?? null;
                    $sessionId = $session['.id'] ?? null;
                    if (!$interface) {
                        continue;
                    }

                    // Run monitor-traffic
                    $monitorQuery = (new Query('/interface/monitor-traffic'))
                        ->equal('interface', $interface)
                        ->equal('once', 'yes');

                    try {
                        $monitorResult = $client->query($monitorQuery)->read();
                        $rateRx = $monitorResult[0]['rx-bits-per-second'] ?? 0;
                        $rateTx = $monitorResult[0]['tx-bits-per-second'] ?? 0;
                    } catch (\Exception $e) {
                        $rateRx = 'error: ' . $e->getMessage();
                        $rateTx = 'error: ' . $e->getMessage();
                    }

                    $routerResults[] = [
                        'session_id' => $sessionId,
                        'interface'  => $interface,
                        'address'    => $session['address'] ?? null,
                        'uptime'     => $session['uptime'] ?? null,
                        'rx_bps'     => $rateRx,
                        'tx_bps'     => $rateTx,
                    ];
                }

                $results[$router->host] = [
                    'sessions' => $routerResults,
                    'count'    => count($routerResults),
                ];
            } catch (\Exception $e) {
                $results[$router->host] = ['error' => $e->getMessage()];
            }
        }

        return response()->json($results);
    }

    /**
     * List all active PPP sessions for a username (or all).
     * GET /test/ppp/{username?}
     */
    public function pppActive($username = null)
    {
        $routers = Mikrotik::where('is_active', 1)->get();
        $results = [];

        foreach ($routers as $router) {
            try {
                $client = new Client([
                    'host' => $router->host,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => $router->port ?? 8728,
                ]);

                $query = (new Query('/ppp/active/print'));
                if ($username) {
                    $query->where('name', $username);
                }

                $sessions = $client->query($query)->read();

                $results[$router->host] = [
                    'sessions' => $sessions,
                    'count' => count($sessions),
                ];
            } catch (\Exception $e) {
                $results[$router->host] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * Test the full getPPPUserTraffic method from MikrotikService.
     * GET /test/traffic/{username}
     */
    public function traffic($username)
    {
        $traffic = \App\Services\MikrotikService::getPPPUserTraffic($username);
        return response()->json($traffic);
    }

    /**
     * Debug: show raw API response for /interface/monitor-traffic with detailed logging.
     * GET /test/debug-monitor/{routerId}/{interface}
     */
    public function debugMonitor($routerId, $interface)
    {
        $router = Mikrotik::find($routerId);
        if (!$router) {
            return response()->json(['error' => 'Router not found'], 404);
        }

        $logData = [];

        try {
            $client = new Client([
                'host' => $router->host,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => $router->port ?? 8728,
            ]);

            $logData['client_created'] = true;

            $query = (new Query('/interface/monitor-traffic'))
                ->equal('interface', $interface)
                ->equal('once', 'yes');

            $logData['query_string'] = $query->getQueryString();

            $result = $client->query($query)->read();

            $logData['result_raw'] = $result;
            $logData['result_count'] = count($result);
            $logData['result_first'] = $result[0] ?? null;

            // Also try to get the interface list to verify existence
            $interfaces = $client->query(
                (new Query('/interface/print'))
                    ->where('name', $interface)
            )->read();

            $logData['interface_exists'] = !empty($interfaces);
            $logData['interface_list'] = $interfaces;

            Log::info('Debug monitor result', $logData);

            return response()->json([
                'success' => true,
                'log' => $logData,
            ]);
        } catch (\Exception $e) {
            $logData['error'] = $e->getMessage();
            $logData['trace'] = $e->getTraceAsString();
            Log::error('Debug monitor error', $logData);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'log' => $logData,
            ], 500);
        }
    }
}
