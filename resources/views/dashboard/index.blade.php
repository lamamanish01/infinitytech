@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-3">
        <h4 class="mb-0">Dashboard</h4>
        <small class="text-muted">System overview</small>
    </div>

    {{-- ROW 1 --}}
    <div class="row">

        {{-- ONLINE --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary text-white">
                <div class="inner">
                    <h3>{{ $onlineCustomers ?? 0 }}</h3>
                    <p>Online Customers</p>
                </div>
                <div class="icon">
                    <i class="fas fa-sync-alt fa-spin"></i>
                </div>
                <a href="{{ url('/customers/online') }}" class="small-box-footer text-white">
                    More info <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- TOTAL --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success text-white">
                <div class="inner">
                    <h3>{{ $totalCustomers ?? 0 }}</h3>
                    <p>Total Customers</p>
                </div>
                <div class="icon">
                    {{-- Static rotation 90° --}}
                    <i class="fas fa fa-users"></i>
                </div>
                <a href="{{ route('customers.index') }}" class="small-box-footer text-white">
                    More info <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- EXPIRING SOON --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $expiringCustomers ?? 0 }}</h3>
                    <p>Expiring Soon</p>
                </div>
                <div class="icon">
                    {{-- Step rotation (pulse) --}}
                    <i class="fas fa fa-clock"></i>
                </div>
                <a href="{{ url('/customers/expiring') }}" class="small-box-footer">
                    More info <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- EXPIRED --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $expiredCustomers ?? 0 }}</h3>
                    <p>Expired Customers</p>
                </div>
                <div class="icon">
                    {{-- Rotate 180° (flipped) --}}
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
                <a href="{{ url('/customers/expired') }}" class="small-box-footer">
                    More info <i class="fa fa-arrow-right"></i>
                </a>
            </div>
        </div>

    </div>

    {{-- ROW 2 --}}
    <div class="row mt-3">

        {{-- BRANCH BALANCE --}}
        @foreach ($branches as $branch)
            <div class="col-lg-4 col-md-6 col-12">
                <div class="small-box bg-{{ $loop->iteration % 2 == 0 ? 'success' : 'info' }} text-white">
                    <div class="inner">
                        <h3>Rs {{ number_format($branch->balance ?? 0, 2) }}</h3>
                        <p>{{ $branch->name }} Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
            </div>
        @endforeach

        {{-- ACTIVE SESSIONS --}}
        <div class="col-lg-4 col-12">
            <div class="small-box bg-primary text-white">
                <div class="inner">
                    <h3>{{ $activeSessions ?? 0 }}</h3>
                    <p>Active Sessions</p>
                </div>
                <div class="icon">
                    {{-- Wi‑Fi icon with continuous spin (like a signal) --}}
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>

        {{-- NAS DEVICES --}}
        <div class="col-lg-4 col-12">
            <div class="small-box bg-secondary text-white">
                <div class="inner">
                    <h3>{{ $nasCount ?? 0 }}</h3>
                    <p>NAS Devices</p>
                </div>
                <div class="icon">
                    {{-- Server icon – pulse rotation for a "working" feel --}}
                    <i class="fa fa-server"></i>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================== --}}
    {{--  SERVER STATISTICS – PROGRESS GROUP STYLE  --}}
    {{-- ========================================== --}}
    <div class="row mt-4">
        <div class="col-12">
            <h4>Server Statistics</h4>
            <span class="text-muted small">
                Last updated: <span id="stats-updated">{{ now()->format('Y-m-d H:i:s') }}</span>
                <span id="stats-refreshing" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
            </span>
        </div>
    </div>

    <div class="row">
        <!-- System Info Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-server"></i> System
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Hostname</span>
                            <strong id="sys-hostname">{{ $stats['system']['hostname'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>OS</span>
                            <strong id="sys-os">{{ $stats['system']['os'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Web Server</span>
                            <strong id="sys-server">{{ $stats['system']['server'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Uptime</span>
                            <strong id="sys-uptime">{{ $stats['uptime'] ?? 'N/A' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- CPU & Load Card -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-microchip"></i> CPU
                </div>
                <div class="card-body">
                    @php
                        $load = $stats['cpu']['load_average'];
                        $cpuUsage = $stats['cpu']['cpu_usage'];
                        $cores = $stats['cpu']['cores'] ?? 'N/A';
                    @endphp
                    <!-- Load average -->
                    <div class="mb-3">
                        <span class="text-muted">Load Average</span><br>
                        <span class="badge bg-info" id="load-1">{{ number_format($load[0], 2) }}</span>
                        <span class="badge bg-warning" id="load-5">{{ number_format($load[1], 2) }}</span>
                        <span class="badge bg-danger" id="load-15">{{ number_format($load[2], 2) }}</span>
                        <small class="text-muted">(1/5/15 min)</small>
                    </div>
                    <!-- CPU Usage with progress bar -->
                    <div class="progress-group">
                        CPU Usage
                        <span class="float-end">
                            <b id="cpu-usage-text">
                                {{ $cpuUsage !== null ? number_format($cpuUsage, 2) : 'No data' }}
                            </b>%
                        </span>
                        <div class="progress progress-sm">
                            <div class="progress-bar text-bg-primary" id="cpu-usage-bar"
                                 style="width: {{ $cpuUsage !== null ? $cpuUsage : 0 }}%;
                                        @if($cpuUsage === null) opacity:0.5; background-color:#6c757d; @endif">
                            </div>
                        </div>
                        @if($cpuUsage === null)
                            <small class="text-muted">(CPU usage not available – check exec or /proc/stat)</small>
                        @endif
                    </div>
                    <div class="mt-2">
                        <span class="text-muted">Cores:</span> <strong id="cpu-cores">{{ $cores }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memory Card (only RAM) -->
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <div class="card-header bg-warning text-dark">
                    <i class="fas fa-memory"></i> Memory
                </div>
                <div class="card-body">
                    @php
                        $totalMem = $stats['memory']['total'];
                        $usedMem  = $stats['memory']['used'];
                        $freeMem  = $stats['memory']['free'];
                        $memPercent = $totalMem > 0 ? round(($usedMem / $totalMem) * 100, 2) : 0;
                    @endphp
                    <!-- RAM usage -->
                    <div class="progress-group">
                        RAM Usage
                        <span class="float-end">
                            <b id="ram-used-text">{{ $totalMem !== null ? number_format($usedMem, 0) : '?' }}</b> MB /
                            <span id="ram-total-text">{{ $totalMem !== null ? number_format($totalMem, 0) : '?' }}</span> MB
                        </span>
                        <div class="progress progress-sm">
                            <div class="progress-bar text-bg-warning" id="ram-usage-bar"
                                 style="width: {{ $totalMem ? $memPercent : 0 }}%">
                            </div>
                        </div>
                    </div>
                    <!-- Free memory line -->
                    <div class="mt-2">
                        <span class="text-muted">Free:</span> <span id="ram-free-text">{{ $totalMem !== null ? number_format($freeMem, 0) : '?' }}</span> MB
                        <span class="float-end"><span class="text-muted">Used:</span> <span id="ram-percent-text">{{ $totalMem ? $memPercent : 0 }}</span>%</span>
                    </div>
                    <!-- PHP memory -->
                    <div class="mt-3">
                        <span class="text-muted">PHP Memory:</span>
                        <strong id="php-mem-usage">{{ formatBytes($stats['memory']['php_memory_usage']) }}</strong>
                        (Limit: <span id="php-mem-limit">{{ $stats['memory']['php_memory_limit'] }}</span>)
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Disk Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-hdd"></i> Disk
                </div>
                <div class="card-body">
                    @php
                        $diskTotal = $stats['disk']['total'];
                        $diskUsed  = $stats['disk']['used'];
                        $diskFree  = $stats['disk']['free'];
                        $diskPercent = $stats['disk']['percent'];
                    @endphp
                    <div class="progress-group">
                        Disk Usage
                        <span class="float-end">
                            <b id="disk-used-text">{{ formatBytes($diskUsed) }}</b> /
                            <span id="disk-total-text">{{ formatBytes($diskTotal) }}</span>
                        </span>
                        <div class="progress progress-sm">
                            <div class="progress-bar text-bg-danger" id="disk-usage-bar"
                                 style="width: {{ $diskPercent }}%">
                            </div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <span class="text-muted">Free:</span> <span id="disk-free-text">{{ formatBytes($diskFree) }}</span>
                        <span class="float-end"><span class="text-muted">Used:</span> <span id="disk-percent-text">{{ $diskPercent }}</span>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- PHP & Laravel Card -->
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-info text-white">
                    <i class="fas fa-code"></i> PHP & Laravel
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>PHP Version</span>
                            <strong id="php-version">{{ $stats['php']['version'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Laravel Version</span>
                            <strong id="laravel-version">{{ $stats['laravel']['version'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Environment</span>
                            <strong id="app-env">{{ $stats['laravel']['environment'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Debug Mode</span>
                            <strong id="app-debug">{{ $stats['laravel']['debug'] }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Max Execution Time</span>
                            <strong id="php-max-exec">{{ $stats['php']['max_execution_time'] }}s</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Upload Max Size</span>
                            <strong id="php-upload-max">{{ $stats['php']['upload_max_filesize'] }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    (function() {
        // Helper to format bytes (matches PHP helper)
        function formatBytes(bytes, precision = 2) {
            if (bytes === 0) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB', 'TB'];
            const pow = Math.floor(Math.log(bytes) / Math.log(1024));
            const value = bytes / Math.pow(1024, pow);
            return value.toFixed(precision) + ' ' + units[pow];
        }

        // Update DOM with fresh stats
        function updateStats(data) {
            // System
            document.getElementById('sys-hostname').textContent = data.system.hostname;
            document.getElementById('sys-os').textContent = data.system.os;
            document.getElementById('sys-server').textContent = data.system.server;
            document.getElementById('sys-uptime').textContent = data.uptime || 'N/A';

            // CPU
            const load = data.cpu.load_average;
            document.getElementById('load-1').textContent = load[0].toFixed(2);
            document.getElementById('load-5').textContent = load[1].toFixed(2);
            document.getElementById('load-15').textContent = load[2].toFixed(2);

            const cpuUsage = data.cpu.cpu_usage;
            const cpuUsageText = cpuUsage !== null ? cpuUsage.toFixed(2) : 'No data';
            document.getElementById('cpu-usage-text').textContent = cpuUsageText;
            document.getElementById('cpu-usage-bar').style.width = (cpuUsage !== null ? cpuUsage : 0) + '%';
            if (cpuUsage === null) {
                document.getElementById('cpu-usage-bar').style.opacity = '0.5';
                document.getElementById('cpu-usage-bar').style.backgroundColor = '#6c757d';
            } else {
                document.getElementById('cpu-usage-bar').style.opacity = '1';
                document.getElementById('cpu-usage-bar').style.backgroundColor = '';
            }
            document.getElementById('cpu-cores').textContent = data.cpu.cores || 'N/A';

            // Memory (RAM only)
            const mem = data.memory;
            if (mem.total !== null) {
                document.getElementById('ram-total-text').textContent = mem.total.toFixed(0);
                document.getElementById('ram-used-text').textContent = mem.used.toFixed(0);
                document.getElementById('ram-free-text').textContent = (mem.total - mem.used).toFixed(0);
                const ramPercent = mem.total > 0 ? (mem.used / mem.total) * 100 : 0;
                document.getElementById('ram-usage-bar').style.width = ramPercent + '%';
                document.getElementById('ram-percent-text').textContent = ramPercent.toFixed(2);
            }
            document.getElementById('php-mem-usage').textContent = formatBytes(mem.php_memory_usage);
            document.getElementById('php-mem-limit').textContent = mem.php_memory_limit;

            // Disk
            const disk = data.disk;
            document.getElementById('disk-total-text').textContent = formatBytes(disk.total);
            document.getElementById('disk-used-text').textContent = formatBytes(disk.used);
            document.getElementById('disk-free-text').textContent = formatBytes(disk.free);
            document.getElementById('disk-percent-text').textContent = disk.percent;
            document.getElementById('disk-usage-bar').style.width = disk.percent + '%';

            // PHP & Laravel
            document.getElementById('php-version').textContent = data.php.version;
            document.getElementById('laravel-version').textContent = data.laravel.version;
            document.getElementById('app-env').textContent = data.laravel.environment;
            document.getElementById('app-debug').textContent = data.laravel.debug;
            document.getElementById('php-max-exec').textContent = data.php.max_execution_time + 's';
            document.getElementById('php-upload-max').textContent = data.php.upload_max_filesize;

            // Update timestamp
            const now = new Date();
            const ts = now.toISOString().replace('T', ' ').slice(0, 19);
            document.getElementById('stats-updated').textContent = ts;
        }

        // Fetch fresh stats from API
        function fetchStats() {
            const refreshing = document.getElementById('stats-refreshing');
            refreshing.classList.remove('d-none');

            fetch('{{ route("stats.json") }}')
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    updateStats(data);
                })
                .catch(error => {
                    console.error('Error fetching stats:', error);
                })
                .finally(() => {
                    refreshing.classList.add('d-none');
                });
        }

        // Poll every 5 seconds
        let interval = setInterval(fetchStats, 5000);

        // Refresh button
        document.getElementById('refresh-stats-btn').addEventListener('click', function() {
            fetchStats();
        });

        // Stop polling when tab is hidden to save resources
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                clearInterval(interval);
            } else {
                interval = setInterval(fetchStats, 5000);
                fetchStats(); // immediate update when tab becomes visible
            }
        });
    })();
</script>
@endpush
