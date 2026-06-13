@extends('layouts.app')

@section('title', 'TR-069 Server Details')

@section('content')
<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-0">{{ $tr069Server->name }}</h3>
            <small class="text-muted">
                ACS Server Management
            </small>
        </div>

        <a href="{{ route('tr069server.index') }}"
           class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @php
        $totalDevices = $tr069Server->devices->count();
        $onlineDevices = $tr069Server->devices->where('status', 'online')->count();
        $offlineDevices = $tr069Server->devices->where('status', 'offline')->count();
    @endphp

    {{-- SUMMARY CARDS --}}
    <div class="row mb-3">

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $totalDevices }}</h5>
                    <small>Total Devices</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $onlineDevices }}</h5>
                    <small>Online Devices</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $offlineDevices }}</h5>
                    <small>Offline Devices</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-info shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ ucfirst($tr069Server->status) }}</h5>
                    <small>ACS Status</small>
                </div>
            </div>
        </div>

    </div>

    <div class="row">

        {{-- SERVER INFO --}}
        <div class="col-md-4">

            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">
                    <strong>ACS Server Information</strong>
                </div>

                <div class="card-body">

                    <table class="table table-bordered mb-0">

                        <tr>
                            <th width="35%">Name</th>
                            <td>{{ $tr069Server->name }}</td>
                        </tr>

                        <tr>
                            <th>ACS URL</th>
                            <td>
                                <small class="text-primary">
                                    {{ $tr069Server->acs_url }}
                                </small>
                            </td>
                        </tr>

                        <tr>
                            <th>Username</th>
                            <td>{{ $tr069Server->acs_username ?? '-' }}</td>
                        </tr>

                        <tr>
                            <th>Status</th>
                            <td>
                                <span class="badge bg-{{ $tr069Server->status == 'active' ? 'success' : 'danger' }}">
                                    {{ strtoupper($tr069Server->status) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Total Devices</th>
                            <td>{{ $totalDevices }}</td>
                        </tr>

                        <tr>
                            <th>Online</th>
                            <td class="text-success">
                                {{ $onlineDevices }}
                            </td>
                        </tr>

                        <tr>
                            <th>Offline</th>
                            <td class="text-danger">
                                {{ $offlineDevices }}
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

        {{-- DEVICE LIST (PAGINATED) --}}
        <div class="col-md-8">

            <div class="card shadow-sm">

                <div class="card-header bg-white d-flex justify-content-between align-items-center">

                    <strong>Connected Devices</strong>

                    <input type="text"
                           class="form-control form-control-sm w-25"
                           id="deviceSearch"
                           placeholder="Search... (current page only)">

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover table-striped mb-0" id="deviceTable">

                            <thead class="table-light">
                                <tr>
                                    <th>Serial</th>
                                    <th>PPPoE</th>
                                    <th>Manufacturer</th>
                                    <th>Product Class</th>
                                    <th>OUI</th>
                                    <th>MAC</th>
                                    <th>IP</th>
                                    <th>Status</th>
                                    <th>Last Inform</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($devices as $device)

                                    <tr>

                                        <td>
                                            <strong>{{ $device->serial }}</strong>
                                        </td>

                                        <td>{{ $device->ppp_username ?? '-' }}</td>
                                        <td>{{ $device->manufacturer ?? '-' }}</td>
                                        <td>{{ $device->product_class ?? '-' }}</td>
                                        <td>{{ $device->oui ?? '-' }}</td>

                                        <td>
                                            <small>{{ $device->router_mac ?? $device->mac_address ?? '-' }}</small>
                                        </td>

                                        <td>{{ $device->ip_address ?? '-' }}</td>

                                        <td>
                                            <span class="badge bg-{{ $device->status == 'online' ? 'success' : 'secondary' }}">
                                                {{ strtoupper($device->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ $device->last_inform ? $device->last_inform->diffForHumans() : '-' }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            No devices found.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- PAGINATION LINKS --}}
                    <div class="mt-3 px-3 pb-3">
                        {{ $devices->links() }}
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    // Client‑side search (works only on the currently visible page)
    document.getElementById('deviceSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        document.querySelectorAll('#deviceTable tbody tr').forEach(row => {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        });
    });
</script>

@endsection
