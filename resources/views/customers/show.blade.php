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
                    @if($customer->is_online)
                        <span class="badge bg-success"><i class="fas fa-sync-alt fa-spin"></i> ONLINE</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-sync-alt"></i> OFFLINE</span>
                    @endif
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
                <div class="tab-pane fade show active" id="overview">
                    @php $grace = $customer->activeGrace(); @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between"><strong>Username</strong><strong><span class="badge badge-success">{{ $customer->username ?? '-' }}</span></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Internet Plan</strong><strong><span class="badge badge-primary">{{ $customer->internetPlan->bandwidth_name ?? '-' }}</span></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Status</strong><strong><span class="badge @if($customer->status == 'active') badge-success @elseif($customer->status == 'grace') badge-warning text-dark @else badge-danger @endif">{{ strtoupper($customer->status) }}</span></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Registered Date</strong><strong><span class="badge badge-primary">{{ $customer->registered_at->format('Y-m-d') }}</span></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Expire Date</strong><strong><span class="badge badge-danger">{{ optional($customer->expire_date)->format('Y-m-d') }}</span></strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Grace</strong><strong>@if($grace)<span class="badge bg-warning text-dark">{{ $grace->grace_days }} Days</span>@else<span class="badge bg-info text-muted">No Grace</span>@endif</strong></li>
                                @if($grace)
                                    <li class="list-group-item d-flex justify-content-between"><strong>Grace Start</strong><strong><span class="badge badge-primary">{{ $grace->grace_start->format('Y-m-d') }}</span></strong></li>
                                    <li class="list-group-item d-flex justify-content-between"><strong>Grace End</strong><strong><span class="badge badge-danger">{{ $grace->grace_end->format('Y-m-d') }}</span></strong></li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between"><strong>MAC Address</strong><strong>@if($customer->mac_address)<span class="badge badge-primary">{{ $customer->mac_address }}</span>@else<span class="badge badge-danger">Not Bound</span>@endif</strong></li>
                                <li class="list-group-item d-flex justify-content-between"><strong>Termination Cause</strong><strong>@if($lastSession && $lastSession->acctterminatecause)<span class="badge badge-danger">{{ $lastSession->acctterminatecause }}</span>@else<span class="badge badge-success">N/A</span>@endif</strong></li>
                            </ul>
                        </div>
                    </div>

                    {{-- QUICK ACTIONS --}}
                    <div class="card mt-3 shadow-sm">
                        <div class="card-header bg-white"><strong>Quick Actions</strong></div>
                        <div class="card-body d-flex flex-wrap gap-2">
                            @can('recharge customers')<a href="{{ route('recharges.create', $customer->id) }}" class="btn btn-warning btn-sm">Recharge</a>@endcan
                            @can('change expiry customers')<a href="{{ route('customers.expiry-form', $customer->id) }}" class="btn btn-danger btn-sm">Change Expiry</a>@endcan
                            @can('grace customers')<form action="{{ route('provide-grace', $customer->id) }}" method="POST">@csrf<button class="btn btn-info btn-sm">+3 Days Grace</button></form>@endcan
                            @can('disconnect customers')<form action="{{ route('customer.disconnect', $customer->id) }}" method="POST">@csrf<button class="btn btn-dark btn-sm">Disconnect</button></form>@endcan
                            @if($customer->mac_address)
                                @can('unbind mac customers')<form action="{{ route('customer.unbind-mac', $customer->id) }}" method="POST">@csrf<button type="submit" class="btn btn-danger btn-sm">Unbind MAC</button></form>@endcan
                            @else
                                @can('bind mac customers')<form action="{{ route('customer.bind-mac', $customer->id) }}" method="POST">@csrf<button type="submit" class="btn btn-primary btn-sm">Bind MAC</button></form>@endcan
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ================= SESSION ================= --}}
                <div class="tab-pane fade" id="session">
                    <h6 class="mb-2">Active Session</h6>
                    @if($customer->active)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover text-nowrap">
                                <thead class="table-light"><tr><th>IP</th><th>Start Time</th><th>End Time</th><th>Time</th><th>Mac Address</th><th>NAS IP</th><th>Upload</th><th>Download</th><th>Server</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            @php $ip = optional($customer->active)->ip_address; @endphp
                                            @if($ip)
                                                <a href="http://{{ $ip }}" target="_blank" rel="noopener noreferrer">{{ $ip }}</a>
                                            @else
                                                <span>N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($customer->active->start_time)->format('Y-m-d H:i:s A') }}</td>
                                        <td>@if($lastSession && $lastSession->acctstoptime){{ \Carbon\Carbon::parse($lastSession->acctstoptime)->format('Y-m-d h:i:s A') }}@else<span class="badge badge-success">Never Disconnected</span>@endif</td>
                                        <td>{{ $customer->active->session_time_human }}</td>
                                        <td>{{ $customer->active->mac_address }}</td>
                                        <td>{{ $customer->active->nas_ip }}</td>
                                        <td>{{ $customer->active->upload_mb }}</td>
                                        <td>{{ $customer->active->download_mb }}</td>
                                        <td>{{ $customer->active->ppp_server }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-secondary">No active session</div>
                    @endif

                    {{-- ================= LIVE PPPoE TRAFFIC CHART ================= --}}
                    <div class="card mt-3 shadow-sm">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <strong>📊 Live PPP User Traffic</strong>
                            <div class="text-muted small text-end">
                                <span id="traffic-download" class="me-3" style="color: #0d6efd;">0 bps</span>/
                                <span id="traffic-upload" class="me-3" style="color: #20c997;">0 bps</span>
                                <span id="traffic-update-time">Updating...</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 280px; min-height: 280px; width: 100%;">
                                <canvas id="trafficChart"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- ================= DAILY TRAFFIC VOLUME (STACKED BAR) ================= --}}
                    <div class="card mt-3 shadow-sm">
                        <div class="card-header bg-white">
                            <strong>📊 Daily Traffic Volume (Last 30 Days)</strong>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height: 300px; min-height: 300px; width: 100%;">
                                <canvas id="dailyTrafficChart"></canvas>
                            </div>
                        </div>
                    </div>

                    {{-- PREVIOUS SESSIONS --}}
                    <h6 class="mt-4 mb-2">Previous Sessions</h6>
                    @if($customer->previous)
                        <div class="table-responsive">
                            <table class="table table-sm table-striped table-hover text-nowrap">
                                <thead class="table-light"><tr><th>IP</th><th>Start Time</th><th>End Time</th><th>Time</th><th>Mac Address</th><th>NAS IP</th><th>Upload</th><th>Download</th><th>Server</th></tr></thead>
                                <tbody>
                                    @forelse ($previousSessions as $session)
                                        <tr>
                                            <td>{{ $session->ip_address ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($session->start_time)->format('Y-m-d H:i:s A') }}</td>
                                            <td>@if($session->acctstoptime){{ \Carbon\Carbon::parse($session->acctstoptime)->format('Y-m-d H:i:s A') }}@else<span class="badge badge-success">Active</span>@endif</td>
                                            <td>{{ $session->session_time_human ?? '-' }}</td>
                                            <td>{{ $session->mac_address ?? '-' }}</td>
                                            <td>{{ $session->nas_ip ?? '-' }}</td>
                                            <td>{{ $session->upload_mb ?? '-' }}</td>
                                            <td>{{ $session->download_mb ?? '-' }}</td>
                                            <td>{{ $session->ppp_server ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="9" class="text-center">No previous sessions found</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <div class="mt-3">{{ $previousSessions->links() }}</div>
                        </div>
                    @else
                        <div class="alert alert-light">No previous session found</div>
                    @endif
                </div>

                {{-- ================= ROUTER MANAGEMENT ================= --}}
                <div class="tab-pane fade" id="router">
                    @php $router = $customer->routerDevices->first(); $server = $router?->server; @endphp
                    @if($router)
                        <form method="POST" action="{{ route('tr069.device.router.update', $router->id) }}">@csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header bg-dark text-white"><strong>📡 ACS Server Info</strong></div>
                                        <div class="card-body">
                                            <p><strong>ACS URL:</strong><br><span class="text-primary">{{ $server->acs_url ?? '-' }}</span></p>
                                            <p><strong>Username:</strong> {{ $server->acs_username ?? '-' }}</p>
                                            <p><strong>Status:</strong> <span class="badge {{ $router->status == 'online' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($router->status) }}</span></p>
                                            <p><strong>Last Sync:</strong><br>{{ $router->updated_at?->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-white"><strong>📦 Router Info</strong></div>
                                        <div class="card-body">
                                            <p><strong>Serial:</strong> {{ $router->serial }}</p>
                                            <p><strong>Product Class:</strong> {{ $router->product_class ?? '-' }}</p>
                                            <p><strong>Manufacturer:</strong> {{ $router->manufacturer ?? '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card shadow-sm border-primary mb-3">
                                        <div class="card-header bg-primary text-white"><strong>📶 WiFi Settings (2.4G + 5G)</strong></div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="{{ $router->wifi_5_ssid ? 'col-md-6' : 'col-md-12' }}">
                                                    <h6 class="text-primary">2.4 GHz</h6>
                                                    <div class="mb-2"><label>SSID</label><input type="text" name="ssid_24" class="form-control" value="{{ old('ssid_24', $router->wifi_24_ssid ?? '') }}"></div>
                                                    <div class="mb-2"><label>Password</label><input type="text" name="password_24" class="form-control" value="{{ old('password_24', $router->wifi_24_password ?? '') }}"></div>
                                                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="hide_ssid_24" value="1" {{ $router->hide_ssid_24 ? 'checked' : '' }}><label class="form-check-label">Hide SSID</label></div>
                                                </div>
                                                @if($router->wifi_5_ssid)
                                                    <div class="col-md-6">
                                                        <h6 class="text-success">5 GHz</h6>
                                                        <div class="mb-2"><label>SSID</label><input type="text" name="ssid_5" class="form-control" value="{{ old('ssid_5', $router->wifi_5_ssid) }}"></div>
                                                        <div class="mb-2"><label>Password</label><input type="text" name="password_5" class="form-control" value="{{ old('password_5', $router->wifi_5_password) }}"></div>
                                                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="hide_ssid_5" value="1" {{ $router->hide_ssid_5 ? 'checked' : '' }}><label class="form-check-label">Hide SSID</label></div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-end mt-3"><button type="submit" name="action" value="update_wifi" class="btn btn-sm btn-primary">🚀 Update WiFi</button></div>
                                        </div>
                                    </div>
                                    <div class="card shadow-sm border-success">
                                        <div class="card-header bg-success text-white"><strong>🌐 PPPoE Settings</strong></div>
                                        <div class="card-body">
                                            <div class="mb-2"><label>Username</label><input type="text" name="pppoe_username" class="form-control" value="{{ $customer->username }}"></div>
                                            <div class="mb-2"><label>Password</label><input type="password" name="pppoe_password" class="form-control" value="{{ $customer->password }}"></div>
                                            <div class="text-end mt-3"><button type="submit" name="action" value="update_pppoe" class="btn btn-sm btn-success">🚀 Update PPPoE</button></div>
                                        </div>
                                    </div>
                                    <div class="card shadow-sm border-0 mt-3">
                                        <div class="card-header bg-light"><strong>⚙️ Router Actions</strong></div>
                                        <div class="card-body d-flex gap-2 flex-wrap">
                                            <form method="POST" action="{{ route('tr069.device.reboot', $router->id) }}">@csrf<button type="submit" class="btn btn-sm btn-outline-primary">🔄 Reboot</button></form>
                                            <form method="POST" action="{{ route('tr069.device.factory-reset', $router->id) }}">@csrf<button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Factory reset router?')">⚠️ Factory Reset</button></form>
                                            <form method="POST" action="{{ route('tr069.device.push-acs', $router->id) }}">@csrf<button type="submit" class="btn btn-sm btn-outline-dark">🚀 Push ACS</button></form>
                                            <a href="{{ route('tr069.device.logs', $router->id) }}" class="btn btn-sm btn-outline-secondary">📜 Logs</a>
                                            <form method="POST" action="{{ route('tr069.device.destroy', $router->id) }}" onsubmit="return confirm('Are you sure you want to delete this router? This action cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">🗑️ Delete</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="alert alert-warning">No router device linked with this customer.</div>
                    @endif
                </div>

                {{-- ================= BILLING ================= --}}
                <div class="tab-pane fade" id="billing">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover text-nowrap">
                            <thead class="table-light"><tr><th>#</th><th>Invoice</th><th>Package</th><th>Amount</th><th>Expire</th><th>Date</th></tr></thead>
                            <tbody>
                                @forelse($billings as $billing)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $billing->billing_no }}</td>
                                        <td><span class="badge bg-primary">{{ optional($billing->customer->internetPlan)->bandwidth_name }}</span></td>
                                        <td>{{ number_format($billing->amount, 2) }}</td>
                                        <td>{{ optional($billing->recharge->expire_date ?? null)->format('Y-m-d') }}</td>
                                        <td>{{ $billing->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No Billing Found</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <div class="mt-3">{{ $billings->links() }}</div>
                    </div>
                </div>

                {{-- ================= CREATE TICKET ================= --}}
                <div class="tab-pane fade" id="create-ticket">
                    <div class="card shadow-sm">
                        <div class="card-header"><strong>Create Support Ticket</strong></div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('ticket.store') }}">@csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                                <div class="mb-3"><label class="form-label">Subject</label><input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required></div>
                                <div class="mb-3"><label class="form-label">Priority</label><select name="priority" class="form-control"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                                <div class="mb-3"><label class="form-label">Message</label><textarea name="message" rows="6" class="form-control" required>{{ old('message') }}</textarea></div>
                                <button type="submit" class="btn btn-success"><i class="fas fa-ticket-alt"></i> Create Ticket</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ================= AUTH LOGS ================= --}}
                <div class="tab-pane fade" id="auth-logs">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover text-nowrap">
                            <thead><tr><th>#</th><th>User</th><th>Pass</th><th>Reply</th><th>Reply Message</th><th>Nas IP Address</th><th>Mac Address</th><th>Date</th></tr></thead>
                            <tbody>
                                @foreach($authLogs as $log)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->username }}</td>
                                        <td>{{ $log->pass }}</td>
                                        <td>{{ $log->reply }}</td>
                                        <td>{{ $log->reply_message }}</td>
                                        <td>{{ $log->nasipaddress }}</td>
                                        <td>{{ $log->mac }}</td>
                                        <td>{{ optional($log->authdate)->toDateTimeString() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">{{ $authLogs->links() }}</div>
                    </div>
                </div>

                {{-- ================= ACTIVITY LOGS ================= --}}
                <div class="tab-pane fade" id="activity-logs">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover text-nowrap">
                            <thead><tr><th>#</th><th>Title</th><th>Message</th><th>User</th><th>Date</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse($activityLogs as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><i class="{{ $activity->icon ?? 'fas fa-bell' }}"></i> {{ $activity->title }}</td>
                                        <td>{{ $activity->message ?? '-' }}</td>
                                        <td>{{ $activity->user->name ?? 'System' }}</td>
                                        <td>{{ $activity->created_at->format('Y-m-d H:i') }}<br><small class="text-muted">{{ $activity->created_at->diffForHumans() }}</small></td>
                                        <td>@if($activity->is_read)<span class="badge bg-success">Read</span>@else<span class="badge bg-warning">Unread</span>@endif</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No activities found for this customer.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $activityLogs->links() }}</div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- ================= CHART SCRIPTS ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {

        // -------------------------------------------------------------
        // Helper: Format speed with adaptive units
        // -------------------------------------------------------------
        function formatSpeed(mbps) {
            if (mbps >= 1000) {
                return (mbps / 1000).toFixed(2) + ' Gbps';
            } else if (mbps >= 1) {
                return mbps.toFixed(2) + ' Mbps';
            } else if (mbps >= 0.001) {
                return (mbps * 1000).toFixed(0) + ' Kbps';
            } else {
                return (mbps * 1000000).toFixed(0) + ' bps';
            }
        }

        // -------------------------------------------------------------
        // 1. LIVE TRAFFIC CHART
        // -------------------------------------------------------------
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            document.getElementById('traffic-update-time').textContent = '⚠️ Chart library missing';
            return;
        }

        const canvas = document.getElementById('trafficChart');
        if (!canvas) {
            console.error('Canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Upload (TX)',
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        data: [],
                        fill: true,
                        tension: 0.3,
                        borderWidth: 3,
                        pointRadius: 1,
                    },
                    {
                        label: 'Download (RX)',
                        borderColor: '#20c997',
                        backgroundColor: 'rgba(32, 201, 151, 0.1)',
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

        const username = '{{ $customer->username }}';
        const MAX_POINTS = 60;
        let failCount = 0;

        function addData(timeLabel, rx, tx, source = 'API') {
            rx = (typeof rx === 'number' && !isNaN(rx)) ? rx : 0;
            tx = (typeof tx === 'number' && !isNaN(tx)) ? tx : 0;

            chart.data.labels.push(timeLabel);
            chart.data.datasets[0].data.push(rx);
            chart.data.datasets[1].data.push(tx);

            if (chart.data.labels.length > MAX_POINTS) {
                chart.data.labels.shift();
                chart.data.datasets[0].data.shift();
                chart.data.datasets[1].data.shift();
            }
            chart.update();
            console.log(`📊 ${source}: ${timeLabel} RX=${rx.toFixed(3)} TX=${tx.toFixed(3)}`);
        }

        function fetchTraffic() {
            const url = `/customer/${username}/ppp-traffic`;
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

                    addData(now, rxMbps, txMbps, 'API');

                    // Update speed indicators – fixed colours
                    const downloadEl = document.getElementById('traffic-download');
                    const uploadEl = document.getElementById('traffic-upload');
                    const updateTimeEl = document.getElementById('traffic-update-time');

                    if (downloadEl) {
                        downloadEl.textContent = `⬇️ ${formatSpeed(rxMbps)}`;
                        downloadEl.style.color = '#0d6efd';   // fixed blue
                    }
                    if (uploadEl) {
                        uploadEl.textContent = `⬆️ ${formatSpeed(txMbps)}`;
                        uploadEl.style.color = '#20c997';    // fixed green
                    }
                    if (updateTimeEl) updateTimeEl.textContent = `Last update: ${now}`;

                    failCount = 0;
                })
                .catch(err => {
                    console.error('❌ Fetch error:', err);
                    failCount++;
                    if (failCount > 3) {
                        const now = new Date().toLocaleTimeString();
                        const rx = Math.random() * 10;
                        const tx = Math.random() * 8;
                        addData(now, rx, tx, 'Mock (fallback)');
                        const downloadEl = document.getElementById('traffic-download');
                        const uploadEl = document.getElementById('traffic-upload');
                        const updateTimeEl = document.getElementById('traffic-update-time');
                        if (downloadEl) {
                            downloadEl.textContent = `${formatSpeed(rx)} (mock)`;
                            downloadEl.style.color = '#0d6efd';
                        }
                        if (uploadEl) {
                            uploadEl.textContent = `${formatSpeed(tx)} (mock)`;
                            uploadEl.style.color = '#20c997';
                        }
                        if (updateTimeEl) updateTimeEl.textContent = `⚠️ API unreachable – using mock`;
                    } else {
                        const now = new Date().toLocaleTimeString();
                        addData(now, 0, 0, 'Fallback');
                        const downloadEl = document.getElementById('traffic-download');
                        const uploadEl = document.getElementById('traffic-upload');
                        const updateTimeEl = document.getElementById('traffic-update-time');
                        if (downloadEl) {
                            downloadEl.textContent = `0 bps`;
                            downloadEl.style.color = '#0d6efd';
                        }
                        if (uploadEl) {
                            uploadEl.textContent = `0 bps`;
                            uploadEl.style.color = '#20c997';
                        }
                        if (updateTimeEl) updateTimeEl.textContent = `⚠️ Error – using 0`;
                    }
                });
        }

        addData(new Date().toLocaleTimeString(), 0, 0, 'Seed');
        setInterval(fetchTraffic, 1000);
        fetchTraffic();

        const sessionTab = document.querySelector('button[data-bs-target="#session"]');
        if (sessionTab) {
            sessionTab.addEventListener('shown.bs.tab', function() {
                chart.resize();
                chart.update();
            });
        }
        setTimeout(() => { chart.resize(); chart.update(); }, 300);


        // -------------------------------------------------------------
        // 2. DAILY TRAFFIC CHART (Stacked Bar)
        // -------------------------------------------------------------
        const dailyCanvas = document.getElementById('dailyTrafficChart');
        if (dailyCanvas) {
            const url = `/customer/{{ $customer->id }}/daily-traffic`;
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    if (!data.dates || data.dates.length === 0) {
                        dailyCanvas.parentElement.innerHTML = `
                            <p class="text-muted text-center my-4">No daily data available.</p>`;
                        return;
                    }
                    const ctx = dailyCanvas.getContext('2d');
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.dates.map(d => {
                                const parts = d.split('-');
                                return parts[2] + '/' + parts[1];
                            }),
                            datasets: [
                                {
                                    label: 'Upload (TX)',
                                    data: data.download.map(v => v / 1024),
                                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                                    borderColor: '#0d6efd',
                                    borderWidth: 1,
                                },
                                {
                                    label: 'Download (RX)',
                                    data: data.upload.map(v => v / 1024),
                                    backgroundColor: 'rgba(32, 201, 151, 0.7)',
                                    borderColor: '#20c997',
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
                                    title: { display: true, text: 'Volume (GB)' }
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
    });
</script>

@endsection
