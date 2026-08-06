@extends('layouts.app')

@section('content')

{{-- Load Chart.js from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="card shadow-sm mb-2">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-6 col-md-3 mb-3">
                    <h4 class="fw-bold">{{ $customer->name }}</h4>
                    {{-- Clickable status badge – green/red on load, spin on click --}}
                    <span id="status-badge"
                          class="badge status-refresh {{ $customer->is_online ? 'bg-success' : 'bg-danger' }}"
                          style="cursor: pointer;"
                          data-customer-id="{{ $customer->id }}">
                        @if($customer->is_online)
                            <i class="fas fa-sync-alt fa-spin"></i> ONLINE
                        @else
                            <i class="fas fa-sync-alt"></i> OFFLINE
                        @endif
                    </span>
                </div>
                <div class="col-6 col-md-3 text-right">
                    <div class="mb-0">{{ $customer->address ?? 'Address not available' }} <i class="fas fa-map-marker-alt text-danger me-2"></i></div>
                    <div class="mb-0"><a href="tel:{{ $customer->contact_number }}">{{ $customer->contact_number }}</a> <i class="fas fa-phone text-success me-2"></i></div>
                    <div class="mt-0"><a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a> <i class="fas fa-envelope text-primary me-2"></i></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Unpaid alert --}}
    @if($unpaidCount > 0)
        <div class="alert alert-danger py-1 px-3 small shadow-sm mb-1"
            role="alert" style="font-size: 0.85rem; border-left: 4px solid #dc3545;">
            <div class="d-flex align-items-center flex-wrap">
                <i class="fas fa-times-circle me-1 text-danger"></i>
                <span class="fw-semibold me-1">Unpaid Billing :</span>
                <span class="badge bg-danger text-white me-1">{{ $unpaidCount }}</span>
                <span class="me-1">bill{{ $unpaidCount > 1 ? 's' : '' }}</span>
                <span class="mx-1 text-muted">·</span>
                <a href="{{ route('billing.index', ['customer_id' => $customer->id, 'status' => 'unpaid']) }}"
                class="alert-link text-decoration-none fw-semibold">
                    Click to View
                </a>
            </div>
        </div>
    @endif

    {{-- Partial alert --}}
    @if($partialCount > 0)
        <div class="alert alert-warning py-1 px-3 small shadow-sm mb-1"
            role="alert" style="font-size: 0.85rem; border-left: 4px solid #ffc107;">
            <div class="d-flex align-items-center flex-wrap">
                <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                <span class="fw-semibold me-1">Partial Billing :</span>
                <span class="badge bg-warning text-dark me-1">{{ $partialCount }}</span>
                <span class="me-1">payment{{ $partialCount > 1 ? 's' : '' }}</span>
                <span class="mx-1 text-muted">·</span>
                <a href="{{ route('billing.index', ['customer_id' => $customer->id, 'status' => 'partial']) }}"
                class="alert-link text-decoration-none fw-semibold">
                    Click to View
                </a>
            </div>
        </div>
    @endif

    {{-- ================= MAIN CARD ================= --}}
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#overview" type="button"><strong>Overview</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#session" type="button"><strong>Session</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#router" type="button"><strong>Router Mgmt</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#billing" type="button"><strong>Billing</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#create-ticket" type="button"><strong>Create Ticket</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#auth-logs" type="button"><strong>Auth Logs</strong></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#activity-logs" type="button"><strong>Activity Logs</strong></button></li>
            </ul>
        </div>

        <div class="card-body">
            <div class="tab-content">

                {{-- ================= OVERVIEW ================= --}}
                <div class="tab-pane fade show active" id="overview" role="tabpanel">
                    <div id="overview-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= SESSION ================= --}}
                <div class="tab-pane fade" id="session" role="tabpanel">
                    <div id="session-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= ROUTER ================= --}}
                <div class="tab-pane fade" id="router" role="tabpanel">
                    <div id="router-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= BILLING ================= --}}
                <div class="tab-pane fade" id="billing" role="tabpanel">
                    <div id="billing-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= CREATE TICKET ================= --}}
                <div class="tab-pane fade" id="create-ticket" role="tabpanel">
                    <div id="create-ticket-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= AUTH LOGS ================= --}}
                <div class="tab-pane fade" id="auth-logs" role="tabpanel">
                    <div id="auth-logs-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

                {{-- ================= ACTIVITY LOGS ================= --}}
                <div class="tab-pane fade" id="activity-logs" role="tabpanel">
                    <div id="activity-logs-content">
                        @include('partials.loading-spinner')
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= SCRIPTS ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

    // -------------------------------------------------------------
    // CLICKABLE STATUS BADGE – NO WARNING FLASH
    // -------------------------------------------------------------
    const badge = document.getElementById('status-badge');
    if (badge) {
        badge.addEventListener('click', function(e) {
            e.preventDefault();
            const customerId = this.dataset.customerId;
            const originalHtml = this.innerHTML;
            const originalClass = this.className;

            // Show loading state
            this.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> Checking...';
            this.style.pointerEvents = 'none';

            fetch(`/customers/${customerId}/online-status`)
                .then(response => {
                    if (!response.ok) throw new Error('Network error');
                    return response.json();
                })
                .then(data => {
                    if (data.is_online) {
                        this.innerHTML = '<i class="fas fa-sync-alt fa-spin"></i> ONLINE';
                        this.className = 'badge bg-success status-refresh';
                    } else {
                        this.innerHTML = '<i class="fas fa-sync-alt"></i> OFFLINE';
                        this.className = 'badge bg-danger status-refresh';
                    }
                    this.style.pointerEvents = 'auto';
                })
                .catch(err => {
                    console.error('Failed to refresh status:', err);
                    // Revert to previous state – no warning flash
                    this.innerHTML = originalHtml;
                    this.className = originalClass;
                    this.style.pointerEvents = 'auto';
                });
        });
    }

    // -------------------------------------------------------------
    // 1. GLOBAL CHART INITIALISATION (for Session tab)
    // -------------------------------------------------------------
    window.initCharts = function() {
        // Destroy previous instances if they exist
        if (window.trafficChartInstance) {
            window.trafficChartInstance.destroy();
            window.trafficChartInstance = null;
        }
        if (window.dailyChartInstance) {
            window.dailyChartInstance.destroy();
            window.dailyChartInstance = null;
        }
        if (window.trafficPollInterval) {
            clearInterval(window.trafficPollInterval);
            window.trafficPollInterval = null;
        }

        // Ensure Chart.js is loaded
        if (typeof Chart === 'undefined') {
            console.warn('Chart.js not loaded – charts will not render');
            return;
        }

        // ----- Live Traffic Chart -----
        const trafficCanvas = document.getElementById('trafficChart');
        if (!trafficCanvas) {
            console.warn('trafficChart canvas not found');
            return;
        }
        const ctx = trafficCanvas.getContext('2d');
        window.trafficChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Upload (TX)',
                        borderColor: '#20c997',
                        backgroundColor: 'rgba(32, 201, 151, 0.1)',
                        data: [],
                        fill: true,
                        tension: 0.3,
                        borderWidth: 3,
                        pointRadius: 1,
                    },
                    {
                        label: 'Download (RX)',
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        data: [],
                        fill: true,
                        tension: 0.3,
                        borderWidth: 3,
                        pointRadius: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 300 },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' Mbps';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'category',
                        grid: { display: false },
                        ticks: { maxTicksLimit: 15 }
                    },
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Traffic (Mbps)' },
                        ticks: {
                            callback: function(value) {
                                if (value >= 1000) return (value / 1000).toFixed(1) + ' Gbps';
                                if (value >= 1) return value.toFixed(1) + ' Mbps';
                                if (value >= 0.001) return (value * 1000).toFixed(0) + ' Kbps';
                                return (value * 1000000).toFixed(0) + ' bps';
                            }
                        }
                    }
                }
            }
        });

        // ----- Traffic Polling (NO MOCK DATA) -----
        const username = '{{ $customer->username }}';
        const MAX_POINTS = 60;

        function formatSpeed(mbps) {
            if (mbps >= 1000) return (mbps / 1000).toFixed(2) + ' Gbps';
            if (mbps >= 1) return mbps.toFixed(2) + ' Mbps';
            if (mbps >= 0.001) return (mbps * 1000).toFixed(0) + ' Kbps';
            return (mbps * 1000000).toFixed(0) + ' bps';
        }

        function addData(timeLabel, rx, tx) {
            rx = (typeof rx === 'number' && !isNaN(rx)) ? rx : 0;
            tx = (typeof tx === 'number' && !isNaN(tx)) ? tx : 0;

            const chart = window.trafficChartInstance;
            chart.data.labels.push(timeLabel);
            chart.data.datasets[0].data.push(rx);  // Upload
            chart.data.datasets[1].data.push(tx);  // Download

            if (chart.data.labels.length > MAX_POINTS) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            chart.update();
        }

        function fetchTraffic() {
            const url = `/customers/${username}/ppp-traffic`;
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (!data.success) throw new Error('Invalid response');
                    let rxMbps = (data.rx_bps || 0) / 1_000_000;
                    let txMbps = (data.tx_bps || 0) / 1_000_000;
                    const now = new Date().toLocaleTimeString();

                    addData(now, rxMbps, txMbps);

                    const downloadEl = document.getElementById('traffic-download');
                    const uploadEl = document.getElementById('traffic-upload');
                    const updateTimeEl = document.getElementById('traffic-update-time');
                    if (downloadEl) {
                        downloadEl.textContent = `⬆️ ${formatSpeed(rxMbps)}`;
                        downloadEl.style.color = '#20c997';
                    }
                    if (uploadEl) {
                        uploadEl.textContent = `⬇️ ${formatSpeed(txMbps)}`;
                        uploadEl.style.color = '#0d6efd';
                    }
                    if (updateTimeEl) updateTimeEl.textContent = `Last update: ${now}`;
                })
                .catch(err => {
                    console.error('❌ Traffic fetch error:', err);
                    const now = new Date().toLocaleTimeString();
                    addData(now, 0, 0);

                    const downloadEl = document.getElementById('traffic-download');
                    const uploadEl = document.getElementById('traffic-upload');
                    const updateTimeEl = document.getElementById('traffic-update-time');
                    if (downloadEl) {
                        downloadEl.textContent = '⬆️ 0 bps';
                        downloadEl.style.color = '#20c997';
                    }
                    if (uploadEl) {
                        uploadEl.textContent = '⬇️ 0 bps';
                        uploadEl.style.color = '#0d6efd';
                    }
                    if (updateTimeEl) {
                        updateTimeEl.textContent = `⚠️ Error – ${now}`;
                        updateTimeEl.style.color = '#dc3545';
                    }
                });
        }

        // Seed with zero point
        addData(new Date().toLocaleTimeString(), 0, 0);
        // Start polling
        fetchTraffic();
        window.trafficPollInterval = setInterval(fetchTraffic, 1000);

        // ----- Daily Traffic Chart -----
        const dailyCanvas = document.getElementById('dailyTrafficChart');
        if (dailyCanvas) {
            const dailyUrl = `/customers/{{ $customer->id }}/daily-traffic`;
            fetch(dailyUrl)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (!data || !data.dates || data.dates.length === 0) {
                        dailyCanvas.parentElement.innerHTML = `
                            <p class="text-muted text-center my-4">No daily data available.</p>`;
                        return;
                    }

                    const hasData = data.upload.some(v => v > 0) || data.download.some(v => v > 0);
                    if (!hasData) {
                        dailyCanvas.parentElement.innerHTML = `
                            <p class="text-muted text-center my-4">No daily data available.</p>`;
                        return;
                    }

                    const ctx2 = dailyCanvas.getContext('2d');
                    const toGB = (bytes) => bytes / 1073741824;

                    window.dailyChartInstance = new Chart(ctx2, {
                        type: 'bar',
                        data: {
                            labels: data.dates.map(d => {
                                const parts = d.split('-');
                                return parts[2] + '/' + parts[1];
                            }),
                            datasets: [
                                {
                                    label: 'Upload (TX)',
                                    data: data.upload.map(v => toGB(v)),
                                    backgroundColor: 'rgba(32, 201, 151, 0.7)',
                                    borderColor: '#20c997',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'Download (RX)',
                                    data: data.download.map(v => toGB(v)),
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: '#0d6efd',
                                    borderWidth: 1,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    stacked: true,
                                    title: { display: true, text: 'Date' }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: { display: true, text: 'Volume (GB)' },
                                    ticks: {
                                        callback: function(value) {
                                            if (value >= 1000) return (value / 1000).toFixed(1) + ' TB';
                                            return value.toFixed(2) + ' GB';
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.parsed.y.toFixed(2) + ' GB';
                                        }
                                    }
                                }
                            }
                        }
                    });
                })
                .catch(err => {
                    console.error('❌ Daily traffic error:', err);
                    dailyCanvas.parentElement.innerHTML = `
                        <p class="text-danger text-center my-4">⚠️ Could not load daily data</p>`;
                });
        }
    };

    // -------------------------------------------------------------
    // 2. GENERIC TAB LOADER with AJAX PAGINATION
    // -------------------------------------------------------------
    const customerId = {{ $customer->id }};
    const loadedTabs = {};

    // ---- PAGINATION HANDLER ----
    function attachPaginationHandler(container, tabName) {
        const paginationLinks = container.querySelectorAll('.pagination a');
        if (!paginationLinks.length) return;

        paginationLinks.forEach(link => {
            // Remove existing listeners to avoid duplicates
            link.removeEventListener('click', paginationClickHandler);
            link.addEventListener('click', paginationClickHandler);
        });

        function paginationClickHandler(e) {
            e.preventDefault();
            const url = this.href;

            // Show spinner inside the container while loading new page
            container.innerHTML = `
                <div class="d-flex flex-column justify-content-center align-items-center py-3" style="min-height: 100px;">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                    <p class="text-muted fw-light mb-0" style="font-size: 0.9rem;">Loading...</p>
                </div>
            `;

            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    // Re‑attach pagination handler for the new links
                    attachPaginationHandler(container, tabName);
                    // If this is the session tab and charts are present, re‑init charts
                    if (tabName === 'session' && typeof window.initCharts === 'function') {
                        // Only re‑init if the charts were destroyed – but we didn't destroy them.
                        // The container only contains the table/pagination, not the whole tab.
                        // So we can skip.
                    }
                })
                .catch(err => {
                    console.error('❌ Pagination error:', err);
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <strong>Error loading page:</strong><br>
                            ${err.message}
                        </div>
                    `;
                });
        }
    }

    function loadTabContent(tabName, containerId, callback) {
        const container = document.getElementById(containerId);
        if (!container) return;

        // If already loaded, just call callback (if any) and exit
        if (loadedTabs[tabName]) {
            if (callback) callback();
            return;
        }

        // Show spinner
        container.innerHTML = `
            <div class="d-flex flex-column justify-content-center align-items-center py-3" style="min-height: 100px;">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <p class="text-muted fw-light mb-0" style="font-size: 0.9rem;">
                    Loading<span class="dot-loader">...</span>
                </p>
            </div>
            <style>
                .dot-loader {
                    display: inline-block;
                    width: 1.2em;
                    text-align: left;
                    animation: dots 1.5s steps(4, end) infinite;
                }
                @keyframes dots {
                    0%   { opacity: 0; }
                    25%  { opacity: 0.2; }
                    50%  { opacity: 0.5; }
                    75%  { opacity: 0.8; }
                    100% { opacity: 1; }
                }
            </style>
        `;

        const url = "{{ route('customer.load-tab', ['id' => ':id', 'tab' => ':tab']) }}"
            .replace(':id', customerId)
            .replace(':tab', tabName);

        fetch(url)
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                    });
                }
                return response.text();
            })
            .then(html => {
                container.innerHTML = html;
                loadedTabs[tabName] = true;
                if (callback) callback();

                // Attach pagination handler
                attachPaginationHandler(container, tabName);
            })
            .catch(err => {
                console.error('❌ Full error:', err);
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <strong>Error loading ${tabName}:</strong><br>
                        ${err.message}<br>
                        <small>Check console for full details.</small>
                    </div>
                `;
            });
    }

    // -------------------------------------------------------------
    // 3. TAB EVENT BINDING
    // -------------------------------------------------------------
    const tabConfig = {
        'overview': { container: 'overview-content', callback: null },
        'session': { container: 'session-content', callback: () => {
            if (typeof window.initCharts === 'function') {
                window.initCharts();
            }
        }},
        'router': { container: 'router-content', callback: null },
        'billing': { container: 'billing-content', callback: null },
        'create-ticket': { container: 'create-ticket-content', callback: null },
        'auth-logs': { container: 'auth-logs-content', callback: null },
        'activity-logs': { container: 'activity-logs-content', callback: null },
    };

    Object.keys(tabConfig).forEach(tabName => {
        const trigger = document.querySelector(`button[data-bs-target="#${tabName}"]`);
        if (!trigger) return;
        const config = tabConfig[tabName];
        trigger.addEventListener('shown.bs.tab', function(e) {
            loadTabContent(tabName, config.container, config.callback);
        });
    });

    // -------------------------------------------------------------
    // 4. LOAD THE ACTIVE TAB ON PAGE LOAD
    // -------------------------------------------------------------
    const activeTabPane = document.querySelector('.tab-pane.active');
    if (activeTabPane) {
        const activeId = activeTabPane.id;
        const tabName = activeId;
        const config = tabConfig[tabName];
        if (config) {
            loadTabContent(tabName, config.container, config.callback);
        }
    }

});
</script>

<style>
    /* Smooth hover transition for the status badge */
    .status-refresh {
        transition: opacity 0.2s;
    }
    .status-refresh:hover {
        opacity: 0.8;
    }
</style>

@endsection
