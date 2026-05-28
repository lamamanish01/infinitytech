@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">{{ $customer->name }}</h4>
            <small class="text-muted d-block">
                {{ $customer->username }}
            </small>

            {{-- STATUS UNDER NAME --}}
            <div class="mt-1">
                @if($customer->is_online)
                    <span class="badge bg-success px-3 py-2">● ONLINE</span>
                @else
                    <span class="badge bg-danger px-3 py-2">● OFFLINE</span>
                @endif
            </div>
        </div>

    </div>

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
                        Overview
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#session"
                            type="button">
                        Session
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#billing"
                            type="button">
                        Billing
                    </button>
                </li>

                <li class="nav-item" role="presentation">
                    <button class="nav-link"
                            data-bs-toggle="tab"
                            data-bs-target="#logs"
                            type="button">
                        Logs
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
                                        <span>{{ $grace->grace_start }}</span>
                                    </li>

                                    <li class="list-group-item d-flex justify-content-between">
                                        Grace End
                                        <span>{{ $grace->grace_end }}</span>
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

                            </ul>

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
                                        <th>Start</th>
                                        <th>Time</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>{{ $customer->active->ip_address }}</td>
                                        <td>{{ $customer->active->start_time }}</td>
                                        <td>{{ $customer->active->session_time_human }}</td>
                                        <td>{{ $customer->active->upload_mb }}</td>
                                        <td>{{ $customer->active->download_mb }}</td>
                                    </tr>
                                </tbody>

                            </table>

                        </div>

                    @else
                        <div class="alert alert-secondary">
                            No active session
                        </div>
                    @endif

                    {{-- PREVIOUS SESSION --}}
                    <h6 class="mt-4 mb-2">Previous Session</h6>

                    @if($customer->previous)

                        <div class="table-responsive">

                            <table class="table table-striped table-sm">

                                <thead class="table-light">
                                    <tr>
                                        <th>IP</th>
                                        <th>Start</th>
                                        <th>Time</th>
                                        <th>Upload</th>
                                        <th>Download</th>
                                        <th>Server</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <tr>
                                        <td>{{ $customer->previous->ip_address }}</td>
                                        <td>{{ $customer->previous->start_time }}</td>
                                        <td>{{ $customer->previous->session_time_human }}</td>
                                        <td>{{ $customer->previous->upload_mb }}</td>
                                        <td>{{ $customer->previous->download_mb }}</td>
                                        <td>{{ $customer->previous->ppp_server }}</td>
                                    </tr>
                                </tbody>

                            </table>

                        </div>

                    @else
                        <div class="alert alert-light">
                            No previous session found
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

                                @forelse($customer->billings as $billing)

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

                    </div>

                </div>

                {{-- ================= LOGS ================= --}}
                <div class="tab-pane fade" id="logs">

                    <div class="table-responsive">

                        <table class="table table-bordered table-sm">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Pass</th>
                                    <th>Reply</th>
                                    <th>Date</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($customer->authLogs as $log)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $log->username }}</td>
                                        <td>{{ $log->pass }}</td>
                                        <td>{{ $log->reply_message }}</td>
                                        <td>{{ optional($log->authdate)->toDateTimeString() }}</td>
                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- ================= QUICK ACTIONS ================= --}}
    <div class="card mt-3 shadow-sm">

        <div class="card-header bg-white">
            <strong>Quick Actions</strong>
        </div>

        <div class="card-body d-flex flex-wrap gap-2">

            <a href="{{ route('recharges.create', $customer->id) }}" class="btn btn-warning btn-sm">
                Recharge
            </a>

            <a href="{{ route('customers.expiry-form', $customer->id) }}" class="btn btn-danger btn-sm">
                Change Expiry
            </a>

            <form action="{{ route('provide-grace', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-info btn-sm">+3 Days Grace</button>
            </form>

            <form action="{{ route('customer.disconnect', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-dark btn-sm">Disconnect</button>
            </form>

            <form action="{{ route('customer.bind-mac', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-primary btn-sm" @if($customer->mac_address) disabled @endif>
                    Bind MAC
                </button>
            </form>

            <form action="{{ route('customer.unbind-mac', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-danger btn-sm" @if(!$customer->mac_address) disabled @endif>
                    Unbind MAC
                </button>
            </form>

        </div>

    </div>

</div>

@endsection
