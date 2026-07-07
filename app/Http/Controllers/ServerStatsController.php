<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;

class ServerStatsController extends Controller
{
    /**
     * Public method to get stats – callable from other controllers.
     */
    function __construct()
    {
        $this->middleware('permission:view serverstats')->only(['index']);
    }

    public function getStats()
    {
        return $this->gatherStats();
    }

    /**
     * Show the statistics page (web).
     */
    public function index()
    {
        $stats = $this->getStats();
        return view('admin.server_stats', compact('stats'));
    }

    /**
     * Return statistics as JSON (API) – used for live updates.
     */
    public function json()
    {
        $stats = $this->getStats();
        return response()->json($stats);
    }

    /**
     * Gather all server stats with caching (5 seconds).
     */
    private function gatherStats()
    {
        return Cache::remember('server_stats', 5, function () {
            return [
                'system' => $this->getSystemInfo(),
                'cpu'    => $this->getCpuStats(),
                'memory' => $this->getMemoryStats(),
                'disk'   => $this->getDiskStats(),
                'php'    => $this->getPhpInfo(),
                'laravel'=> $this->getLaravelInfo(),
                'uptime' => $this->getUptime(),
            ];
        });
    }

    // ---------- System ----------
    private function getSystemInfo()
    {
        return [
            'hostname' => gethostname(),
            'os'       => php_uname('s') . ' ' . php_uname('r'),
            'server'   => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        ];
    }

    // ---------- CPU (with /proc/stat fallback) ----------
    private function getCpuStats()
    {
        $load = sys_getloadavg();
        $cpuUsage = null;
        $cores = null;

        if (function_exists('exec') && strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
            // Try 'top'
            exec("top -bn1 | grep 'Cpu(s)' | awk '{print \$2}'", $topOutput);
            if (!empty($topOutput)) {
                $cpuUsage = (float) $topOutput[0];
            } else {
                // Fallback to 'mpstat'
                exec('mpstat 1 1 | awk \'/Average/ {print 100 - $NF}\'', $mpstatOutput);
                if (!empty($mpstatOutput)) {
                    $cpuUsage = (float) $mpstatOutput[0];
                }
            }
            // Get core count
            exec("nproc", $cores);
            if (!empty($cores)) {
                $cores = (int) $cores[0];
            }
        }

        // If exec failed, try /proc/stat (no exec needed)
        if ($cpuUsage === null && file_exists('/proc/stat')) {
            $cpuUsage = $this->getCpuUsageFromProcStat();
        }

        return [
            'load_average' => $load,
            'cpu_usage'    => $cpuUsage,
            'cores'        => $cores,
        ];
    }

    /**
     * Calculate CPU usage from /proc/stat (no exec required).
     */
    private function getCpuUsageFromProcStat()
    {
        $stat1 = file_get_contents('/proc/stat');
        if (!$stat1) return null;
        usleep(100000); // 0.1 sec
        $stat2 = file_get_contents('/proc/stat');
        if (!$stat2) return null;

        $parse = function($stat) {
            preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $stat, $matches);
            if (count($matches) < 9) return null;
            return [
                'user'   => (int)$matches[1],
                'nice'   => (int)$matches[2],
                'system' => (int)$matches[3],
                'idle'   => (int)$matches[4],
                'iowait' => (int)$matches[5],
                'irq'    => (int)$matches[6],
                'softirq'=> (int)$matches[7],
                'steal'  => (int)$matches[8],
            ];
        };

        $cpu1 = $parse($stat1);
        $cpu2 = $parse($stat2);
        if (!$cpu1 || !$cpu2) return null;

        $total1 = array_sum($cpu1);
        $total2 = array_sum($cpu2);
        $idle1 = $cpu1['idle'];
        $idle2 = $cpu2['idle'];
        $totalDiff = $total2 - $total1;
        $idleDiff  = $idle2 - $idle1;
        if ($totalDiff == 0) return null;

        return round((1 - ($idleDiff / $totalDiff)) * 100, 2);
    }

    // ---------- Memory (only RAM, no swap) ----------
    private function getMemoryStats()
    {
        $memInfo = $this->readMemInfo();

        return [
            'total'   => $memInfo['MemTotal'] ?? null,
            'free'    => $memInfo['MemFree'] ?? null,
            'used'    => isset($memInfo['MemTotal'], $memInfo['MemFree'])
                            ? $memInfo['MemTotal'] - $memInfo['MemFree']
                            : null,
            'php_memory_limit' => ini_get('memory_limit'),
            'php_memory_usage' => memory_get_usage(true),
        ];
    }

    private function readMemInfo()
    {
        if (!file_exists('/proc/meminfo')) {
            return [];
        }
        $data = file_get_contents('/proc/meminfo');
        $lines = explode("\n", $data);
        $info = [];
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $parts = explode(':', $line);
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (str_ends_with($value, 'kB')) {
                $value = (int) str_replace(' kB', '', $value) / 1024;
            }
            $info[$key] = $value;
        }
        return $info;
    }

    // ---------- Disk ----------
    private function getDiskStats()
    {
        $path = base_path();
        $total = disk_total_space($path);
        $free  = disk_free_space($path);
        $used  = $total - $free;

        return [
            'total'   => $total,
            'free'    => $free,
            'used'    => $used,
            'percent' => $total > 0 ? round(($used / $total) * 100, 2) : 0,
        ];
    }

    // ---------- PHP ----------
    private function getPhpInfo()
    {
        return [
            'version'            => phpversion(),
            'memory_limit'       => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'post_max_size'      => ini_get('post_max_size'),
        ];
    }

    // ---------- Laravel ----------
    private function getLaravelInfo()
    {
        return [
            'version'      => app()->version(),
            'environment'  => app()->environment(),
            'debug'        => config('app.debug') ? 'On' : 'Off',
        ];
    }

    // ---------- Uptime (improved) ----------
    private function getUptime()
    {
        if (function_exists('exec')) {
            // Try 'uptime -p' (human-friendly)
            exec('uptime -p 2>/dev/null', $output, $returnCode);
            if ($returnCode === 0 && !empty($output)) {
                return trim($output[0]);
            }
            // Fallback to /proc/uptime
            if (file_exists('/proc/uptime')) {
                $uptime = file_get_contents('/proc/uptime');
                if ($uptime) {
                    $seconds = (int) explode(' ', $uptime)[0];
                    return $this->secondsToHuman($seconds);
                }
            }
        }
        return null;
    }

    private function secondsToHuman($seconds)
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        return sprintf("%dd %02dh %02dm %02ds", $days, $hours, $minutes, $secs);
    }
}
