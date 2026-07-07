@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="card shadow-sm mb-2">
        <div class="card-body">

            <div class="row align-items-center">

                {{-- Left Column --}}
                <div class="col-6 col-md-3 mb-3">
                    <h4 class="fw-bold">
                        {{ $customer->name }}
                    </h4>

                    @if($customer->is_online)
                        <span class="badge bg-success">● ONLINE</span>
                    @else
                        <span class="badge bg-danger">● OFFLINE</span>
                    @endif
                </div>

                {{-- Right Column – centered on all screens --}}
                <div class="col-6 col-md-3 text-right">

                    <div class="mb-0">
                        {{ $customer->address ?? 'Address not available' }}
                        <i class="fas fa-map-marker-alt text-danger me-2"></i>
                    </div>

                    <div class="mb-0">
                        {{ $customer->contact_number }}
                        <i class="fas fa-phone text-success me-2"></i>
                    </div>

                    <div class="mt-0">
                        {{ $customer->email }}
                        <i class="fas fa-envelope text-primary me-2"></i>
                    </div>

                </div>

            </div> <!-- /row -->

        </div> <!-- /card-body -->
    </div> <!-- /card -->

    {{-- ================= CARD ================= --}}
    <div class="card shadow-sm">

        {{-- ================= TABS HEADER ================= --}}
        <div class="card-header bg-white">

            <ul class="nav nav-tabs card-header-tabs" role="tablist">

                <li class="nav-item" role="presentation">
                    <button class="nav-link active"
                            data-bs-toggle="tab"
                            data-bs-target="#overview"
                            type="button">
                        <strong>Overview</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#session"
                            type="button">
                        <strong>Session</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#router"
                            type="button">
                        <strong>Router Mgmt</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#billing"
                            type="button">
                        <strong>Billing</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#create-ticket"
                            type="button">
                        <strong>Create Ticket</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#auth-logs"
                            type="button">
                        <strong>Auth Logs</strong>
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#activity-logs"
                            type="button">
                        <strong>Activity Logs</strong>
                    </button>
                </li>

            </ul>

        </div>

        {{-- ================= BODY ================= --}}
        <div class="card-body">

            <div class="tab-content">

                {{-- ================= OVERVIEW ================= --}}
                <div class="tab-pane fade show active" id="overview">

                    @php
                        $grace = $customer->activeGrace();
                    @endphp

                    <div class="row">

                        <div class="col-md-6">

                            <ul class="list-group">

                                <li class="list-group-item d-flex justify-content-between">
                                    Username
                                    <strong>{{ $customer->username ?? '-' }}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Plan
                                    <strong>{{ $customer->internetPlan->bandwidth_name ?? '-' }}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Status
                                    @php
                                        $status = $customer->status;
                                    @endphp

                                    <span class="badge
                                        @if($status == 'active') bg-success
                                        @elseif($status == 'grace') bg-warning text-dark
                                        @else bg-danger
                                        @endif">
                                        {{ strtoupper($status) }}
                                    </span>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Registered On
                                    <strong>{{ $customer->registered_at->format('Y-m-d')}}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Expiry
                                    <strong>{{ optional($customer->expire_date)->format('Y-m-d') }}</strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Grace
                                    @if($grace)
                                        <span class="badge bg-warning text-dark">
                                            {{ $grace->grace_days }} Days
                                        </span>
                                    @else
                                        <span class="text-muted">No Grace</span>
                                    @endif
                                </li>

                                @if($grace)
                                    <li class="list-group-item d-flex justify-content-between">
                                        Grace Start
                                        <span>{{ $grace->grace_start->format('Y-m-d') }}</span>
                                    </li>

                                    <li class="list-group-item d-flex justify-content-between">
                                        Grace End
                                        <span>{{ $grace->grace_end->format('Y-m-d') }}</span>
                                    </li>
                                @endif

                                <li class="list-group-item d-flex justify-content-between">
                                    MAC
                                    <strong>
                                        @if($customer->mac_address)
                                            <span class="text-success">{{ $customer->mac_address }}</span>
                                        @else
                                            <span class="text-danger">Not Bound</span>
                                        @endif
                                    </strong>
                                </li>

                                <li class="list-group-item d-flex justify-content-between">
                                    Last Disconnect Reason
                                    <strong>
                                        @if($lastSession && $lastSession->acctterminatecause)
                                            <span class="badge badge-danger">
                                                {{ $lastSession->acctterminatecause }}
                                            </span>
                                        @else
                                            <span class="badge badge-success">
                                                N/A
                                            </span>
                                        @endif
                                    </strong>
                                </li>

                            </ul>

                        </div>

                    </div>

                     {{-- ================= QUICK ACTIONS ================= --}}
                    <div class="card mt-3 shadow-sm">

                        <div class="card-header bg-white">
                            <strong>Quick Actions</strong>
                        </div>

                        <div class="card-body d-flex flex-wrap gap-2">

                            @can('recharge customers')
                                <a href="{{ route('recharges.create', $customer->id) }}"
                                class="btn btn-warning btn-sm">
                                    Recharge
                                </a>
                            @endcan

                            @can('change expiry customers')
                                <a href="{{ route('customers.expiry-form', $customer->id) }}"
                                class="btn btn-danger btn-sm">
                                    Change Expiry
                                </a>
                            @endcan

                            @can('grace customers')
                                <form action="{{ route('provide-grace', $customer->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-info btn-sm">
                                        +3 Days Grace
                                    </button>
                                </form>
                            @endcan

                            @can('disconnect customers')
                                <form action="{{ route('customer.disconnect', $customer->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-dark btn-sm">
                                        Disconnect
                                    </button>
                                </form>
                            @endcan

                            @if($customer->mac_address)

                                @can('unbind mac customers')
                                    <form action="{{ route('customer.unbind-mac', $customer->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Unbind MAC
                                        </button>
                                    </form>
                                @endcan

                            @else

                                @can('bind mac customers')
                                    <form action="{{ route('customer.bind-mac', $customer->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            Bind MAC
                                        </button>
                                    </form>
                                @endcan

                            @endif

                        </div>

                    </div>

                </div>

                {{-- ================= SESSION ================= --}}
                <div class="tab-pane fade" id="session">

                    {{-- ACTIVE SESSION --}}
                    <h6 class="mb-2">Active Session</h6>

                    @if($customer->active)

                        <div class="table-responsive">

                            <table class="table table-bordered table-sm">

                                <thead class="table-light">
                                    <tr>
                                        <th>IP</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Time</th>
                                        <th>Mac Address</th>
                                        <th>NAS IP</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                        <th>Server</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>{{ $customer->active->ip_address }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($customer->active->start_time)
                                                ->timezone('Asia/Kathmandu')
                                                ->format('Y-m-d H:i:s') }}
                                        </td>
                                        <td>
                                            @if($lastSession && $lastSession->acctstoptime)
                                                {{ \Carbon\Carbon::parse($lastSession->acctstoptime)->timezone('Asia/Kathmandu')->format('Y-m-d h:i:s') }}
                                            @else
                                                <span class="badge badge-success">
                                                    Never Disconnected
                                                </span>
                                            @endif
                                        </td>
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
                        <div class="alert alert-secondary">
                            No active session
                        </div>
                    @endif

                    {{-- PREVPREVIOUS SESSION --}}
                    <h6 class="mt-4 mb-2">Previous Session</h6>

                    @if($customer->previous)

                        <div class="table-responsive">

                            <table class="table table-striped table-sm">

                                <thead class="table-light">
                                    <tr>
                                        <th>IP</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Time</th>
                                        <th>Mac Address</th>
                                        <th>NAS IP</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                        <th>Server</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($previousSessions as $session)

                                        <tr>
                                            <td>{{ $session->ip_address ?? '-' }}</td>
                                            <td>
                                                {{ \Carbon\Carbon::parse($session->start_time)->format('Y-m-d H:i:s') }}
                                            </td>
                                            <td>
                                                @if($session->acctstoptime)
                                                    {{ \Carbon\Carbon::parse($session->acctstoptime)->format('Y-m-d H:i:s') }}
                                                @else
                                                    <span class="badge badge-success">Active</span>
                                                @endif
                                            </td>
                                            <td>{{ $session->session_time_human ?? '-' }}</td>
                                            <td>{{ $session->mac_address ?? '-' }}</td>
                                            <td>{{ $session->nas_ip ?? '-' }}</td>
                                            <td>{{ $session->upload_mb ?? '-' }}</td>
                                            <td>{{ $session->download_mb ?? '-' }}</td>
                                            <td>{{ $session->ppp_server ?? '-' }}</td>
                                        </tr>

                                        @empty

                                        <tr>
                                            <td colspan="9" class="text-center">
                                                No previous sessions found
                                            </td>
                                        </tr>

                                        @endforelse

                                </tbody>
                            </table>

                            <div class="mt-3">
                                {{ $previousSessions->links() }}
                            </div>

                        </div>

                    @else
                        <div class="alert alert-light">
                            No previous session found
                        </div>
                    @endif

                </div>

                {{-- ================= ROUTER MANAGEMENT TAB ================= --}}
                <div class="tab-pane fade" id="router">

                @php
                    $router = $customer->routerDevices->first();
                    $server = $router?->server;
                @endphp

                @if($router)

                    <form method="POST" action="{{ route('tr069.device.router.update', $router->id) }}">
                        @csrf

                        <div class="row">

                            {{-- ================= LEFT SIDE ================= --}}
                            <div class="col-md-4">

                                {{-- ================= ACS INFO ================= --}}
                                <div class="card shadow-sm mb-3">

                                    <div class="card-header bg-dark text-white">
                                        <strong>📡 ACS Server Info</strong>
                                    </div>

                                    <div class="card-body">

                                        <p><strong>ACS URL:</strong><br>
                                            <span class="text-primary">
                                                {{ $server->acs_url ?? '-' }}
                                            </span>
                                        </p>

                                        <p><strong>Username:</strong> {{ $server->acs_username ?? '-' }}</p>

                                        <p>
                                            <strong>Status:</strong>
                                            <span class="badge {{ $router->status == 'online' ? 'bg-success' : 'bg-danger' }}">
                                                {{ strtoupper($router->status) }}
                                            </span>
                                        </p>

                                        <p>
                                            <strong>Last Sync:</strong><br>
                                            {{ $router->updated_at?->diffForHumans() }}
                                        </p>

                                    </div>

                                </div>

                                {{-- ================= ROUTER INFO ================= --}}
                                <div class="card shadow-sm">

                                    <div class="card-header bg-white">
                                        <strong>📦 Router Info</strong>
                                    </div>

                                    <div class="card-body">

                                        <p><strong>Serial:</strong> {{ $router->serial }}</p>
                                        <p><strong>Product Class:</strong> {{ $router->product_class ?? '-' }}</p>
                                        <p><strong>Manufacturer:</strong> {{ $router->manufacturer ?? '-' }}</p>

                                    </div>

                                </div>

                            </div>

                            {{-- ================= RIGHT SIDE ================= --}}
                            <div class="col-md-8">

                                {{-- ================= WIFI CARD ================= --}}
                                <div class="card shadow-sm border-primary mb-3">

                                    <div class="card-header bg-primary text-white">
                                        <strong>📶 WiFi Settings (2.4G + 5G)</strong>
                                    </div>

                                    <div class="card-body">

                                        <div class="row">
                                            {{-- 2.4GHz --}}
                                            <div class="{{ $router->wifi_5_ssid ? 'col-md-6' : 'col-md-12' }}">
                                                <h6 class="text-primary">2.4 GHz</h6>

                                                <div class="mb-2">
                                                    <label>SSID</label>
                                                    <input type="text"
                                                        name="ssid_24"
                                                        class="form-control"
                                                        value="{{ old('ssid_24', $router->wifi_24_ssid ?? '') }}">
                                                </div>

                                                <div class="mb-2">
                                                    <label>Password</label>
                                                    <input type="text"
                                                        name="password_24"
                                                        class="form-control"
                                                        value="{{ old('password_24', $router->wifi_24_password ?? '') }}">
                                                </div>

                                                <div class="form-check form-switch">
                                                    <input class="form-check-input"
                                                        type="checkbox"
                                                        name="hide_ssid_24"
                                                        value="1"
                                                        {{ $router->hide_ssid_24 ? 'checked' : '' }}>
                                                    <label class="form-check-label">Hide SSID</label>
                                                </div>
                                            </div>

                                            {{-- 5GHz – only if SSID exists --}}
                                            @if($router->wifi_5_ssid)
                                                <div class="col-md-6">
                                                    <h6 class="text-success">5 GHz</h6>

                                                    <div class="mb-2">
                                                        <label>SSID</label>
                                                        <input type="text"
                                                            name="ssid_5"
                                                            class="form-control"
                                                            value="{{ old('ssid_5', $router->wifi_5_ssid) }}">
                                                    </div>

                                                    <div class="mb-2">
                                                        <label>Password</label>
                                                        <input type="text"
                                                            name="password_5"
                                                            class="form-control"
                                                            value="{{ old('password_5', $router->wifi_5_password) }}">
                                                    </div>

                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input"
                                                            type="checkbox"
                                                            name="hide_ssid_5"
                                                            value="1"
                                                            {{ $router->hide_ssid_5 ? 'checked' : '' }}>
                                                        <label class="form-check-label">Hide SSID</label>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>

                                        <div class="text-end mt-3">
                                            <button type="submit"
                                                    name="action"
                                                    value="update_wifi"
                                                    class="btn btn-primary">
                                                🚀 Update WiFi
                                            </button>
                                        </div>

                                    </div>

                                </div>

                                {{-- ================= PPPoE CARD ================= --}}
                                <div class="card shadow-sm border-success">

                                    <div class="card-header bg-success text-white">
                                        <strong>🌐 PPPoE Settings</strong>
                                    </div>

                                    <div class="card-body">

                                        <div class="mb-2">
                                            <label>Username</label>
                                            <input type="text"
                                                name="pppoe_username"
                                                class="form-control"
                                                value="{{ $customer->username }}">
                                        </div>

                                        <div class="mb-2">
                                            <label>Password</label>
                                            <input type="password"
                                                name="pppoe_password"
                                                class="form-control"
                                                value="{{ $customer->password }}">
                                        </div>

                                        <div class="text-end mt-3">
                                            <button type="submit"
                                                    name="action"
                                                    value="update_pppoe"
                                                    class="btn btn-success">
                                                🚀 Update PPPoE
                                            </button>
                                        </div>

                                    </div>

                                </div>

                                <div class="card shadow-sm border-0 mt-3">

                                    <div class="card-header bg-light">
                                        <strong>⚙️ Router Actions</strong>
                                    </div>

                                    <div class="card-body d-flex gap-2 flex-wrap">

                                        <form method="POST"
                                            action="{{ route('tr069.device.reboot', $router->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-primary">
                                                🔄 Reboot
                                            </button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('tr069.device.factory-reset', $router->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-danger"
                                                    onclick="return confirm('Factory reset router?')">
                                                ⚠️ Factory Reset
                                            </button>
                                        </form>

                                        <form method="POST"
                                            action="{{ route('tr069.device.push-acs', $router->id) }}">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-dark">
                                                🚀 Push ACS
                                            </button>
                                        </form>

                                        <a href="{{ route('tr069.device.logs', $router->id) }}"
                                        class="btn btn-outline-secondary">
                                            📜 Logs
                                        </a>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </form>

                @else

                    <div class="alert alert-warning">
                        No router device linked with this customer.
                    </div>

                @endif

                </div>

                {{-- ================= BILLING ================= --}}
                <div class="tab-pane fade" id="billing">

                    <div class="table-responsive">

                        <table class="table table-striped table-sm">

                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Package</th>
                                    <th>Amount</th>
                                    <th>Expire</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($billings as $billing)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $billing->billing_no }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ optional($billing->customer->internetPlan)->bandwidth_name }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($billing->amount, 2) }}</td>
                                        <td>{{ optional($billing->recharge->expire_date ?? null)->format('Y-m-d') }}</td>
                                        <td>{{ $billing->created_at->format('Y-m-d') }}</td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No Billing Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>
                            <div class="mt-3">
                                {{ $billings->links() }}
                            </div>

                    </div>

                </div>

                {{-- ================= CREATE TICKET ================= --}}
                <div class="tab-pane fade" id="create-ticket">

                    <div class="card shadow-sm">
                        <div class="card-header">
                            <strong>Create Support Ticket</strong>
                        </div>

                        <div class="card-body">

                            <form method="POST" action="{{ route('ticket.store') }}">
                                @csrf

                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                                <div class="mb-3">
                                    <label class="form-label">Subject</label>
                                    <input
                                        type="text"
                                        name="subject"
                                        class="form-control"
                                        value="{{ old('subject') }}"
                                        required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Priority</label>
                                    <select name="priority" class="form-control">
                                        <option value="low">Low</option>
                                        <option value="medium" selected>Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Message</label>
                                    <textarea
                                        name="message"
                                        rows="6"
                                        class="form-control"
                                        required>{{ old('message') }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-ticket-alt"></i> Create Ticket
                                </button>

                            </form>

                        </div>
                    </div>

                </div>

                {{-- ================= AUTH LOGS ================= --}}
                <div class="tab-pane fade" id="auth-logs">

                    <div class="table-responsive">

                        <table class="table table-bordered table-sm">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Pass</th>
                                    <th>Reply</th>
                                    <th>Reply Message</th>
                                    <th>Nas IP Address</th>
                                    <th>Mac Address</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

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
                            <div class="mt-3">
                                {{ $authLogs->links() }}
                            </div>

                    </div>

                </div>

                {{-- ================= ACTIVITY LOGS ================= --}}
                <div class="tab-pane fade" id="activity-logs">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activityLogs as $activity)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <i class="{{ $activity->icon ?? 'fas fa-bell' }}"></i>
                                            {{ $activity->title }}
                                        </td>
                                        <td>{{ $activity->message ?? '-' }}</td>
                                        <td>{{ $activity->user->name ?? 'System' }}</td>
                                        <td>
                                            {{ $activity->created_at->format('Y-m-d H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($activity->is_read)
                                                <span class="badge bg-success">Read</span>
                                            @else
                                                <span class="badge bg-warning">Unread</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No activities found for this customer.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $activityLogs->links() }}
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
