@extends('layouts.app')

@section('title', 'TR-069 Server Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Server Information</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr><th>Name</th><td>{{ $tr069Server->name }}</td></tr>
                        <tr><th>ACS URL</th><td>{{ $tr069Server->acs_url }}</td></tr>
                        <tr><th>Username</th><td>{{ $tr069Server->acs_username ?? '-' }}</td></tr>
                        <tr><th>Status</th><td><span class="badge {{ $tr069Server->status == 'active' ? 'bg-success' : 'bg-danger' }}">{{ ucfirst($tr069Server->status) }}</span></td></tr>
                        <tr><th>Total Devices</th><td>{{ $tr069Server->devices->count() }}</td></tr>
                    </table>
                    {{--  <a href="{{ route('tr069server.edit', $tr069Server) }}" class="btn btn-warning">Edit Server</a>  --}}
                    <a href="{{ route('tr069server.index') }}" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Devices on this Server</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Serial Number</th>
                                <th>Model</th>
                                <th>MAC Address</th>
                                <th>Status</th>
                                <th>Last Inform</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($tr069Server->devices as $device)
                            <tr>
                                <td>{{ $device->serial_number }}</td>
                                <td>{{ $device->model ?? '-' }}</td>
                                <td>{{ $device->mac_address ?? '-' }}</td>
                                <td><span class="badge {{ $device->status == 'online' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($device->status) }}</span></td>
                                <td>{{ $device->last_inform ? $device->last_inform->diffForHumans() : '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center">No devices found for this server.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
