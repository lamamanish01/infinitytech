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

    {{-- SUMMARY CARDS (using pre-computed counts) --}}
    <div class="row mb-3">

        <div class="col-md-3">
            <div class="card border-primary shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $tr069Server->devices_count ?? 0 }}</h5>
                    <small>Total Devices</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-success shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $tr069Server->online_count ?? 0 }}</h5>
                    <small>Online Devices</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-danger shadow-sm">
                <div class="card-body text-center">
                    <h5>{{ $tr069Server->offline_count ?? 0 }}</h5>
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

                    <table class="table table-sm table-striped mb-0">

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
                            <td>{{ $tr069Server->devices_count ?? 0 }}</td>
                        </tr>

                        <tr>
                            <th>Online</th>
                            <td class="text-success">
                                {{ $tr069Server->online_count ?? 0 }}
                            </td>
                        </tr>

                        <tr>
                            <th>Offline</th>
                            <td class="text-danger">
                                {{ $tr069Server->offline_count ?? 0 }}
                            </td>
                        </tr>

                    </table>

                </div>

            </div>

        </div>

        {{-- DEVICE LIST (PAGINATED & SEARCHABLE) --}}
        <div class="col-md-8">

            <div class="card shadow-sm">

                {{-- CARD HEADER WITH SEARCH FORM --}}
                <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <strong>Connected Devices</strong>

                    <form method="GET"
                          action="{{ route('tr069server.show', $tr069Server->id) }}"
                          class="d-flex gap-2"
                          id="searchForm">

                        <input type="text"
                               name="search"
                               class="form-control form-control-sm"
                               style="min-width: 200px;"
                               placeholder="Search all devices..."
                               value="{{ request('search') }}"
                               id="deviceSearchInput">

                        {{-- Search button --}}
                        <button type="submit" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 0.5rem;">
                            <i class="fas fa-search"></i> Search
                        </button>

                        {{-- Clear button --}}
                        @if(request('search'))
                            <a href="{{ route('tr069server.show', $tr069Server->id) }}"
                            class="btn btn-secondary btn-sm py-0 px-2" style="font-size: 0.5rem;">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        @endif

                    </form>

                </div>

                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-sm table-hover table-striped mb-0" id="deviceTable">

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
                                            @if(request('search'))
                                                No devices found matching "<strong>{{ request('search') }}</strong>".
                                            @else
                                                No devices found.
                                            @endif
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                    {{-- PAGINATION LINKS (preserves search term) --}}
                    <div class="mt-3 px-3 pb-3">
                        {{ $devices->links() }}
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    let searchTimeout;
    const searchInput = document.getElementById('deviceSearchInput');

    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                document.getElementById('searchForm').submit();
            }, 500);
        });
    }
</script>

@endsection
