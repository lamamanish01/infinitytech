@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">📡 Monitors</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('monitors.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Monitor
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card">
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Host</th>
                            <th>Type</th>
                            <th>Community</th>       <!-- always shown -->
                            <th>SNMP Version</th>    <!-- always shown -->
                            <th>Status</th>
                            <th>Uptime</th>
                            <th>Response</th>
                            <th>Last Check</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monitors as $monitor)
                            <tr>
                                <td>{{ $monitor->id }}</td>
                                <td>{{ $monitor->name }}</td>
                                <td><code>{{ $monitor->host }}</code></td>
                                <td>
                                    @if($monitor->check_type === 'snmp')
                                        <span class="badge badge-info">SNMP</span>
                                    @else
                                        <span class="badge badge-secondary">PING</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $monitor->snmp_community ?? '—' }}
                                </td>
                                <td>
                                    {{ $monitor->snmp_version ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge {{ $monitor->status_badge_class }}">
                                        {{ $monitor->status_label }}
                                    </span>
                                </td>
                                <td>{{ $monitor->formatted_uptime }}</td>
                                <td>{{ $monitor->response_time ? $monitor->response_time.' ms' : '—' }}</td>
                                <td>{{ $monitor->last_checked_at?->diffForHumans() ?? 'Never' }}</td>
                                <td>
                                    <a href="{{ route('monitors.show', $monitor) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('monitors.edit', $monitor) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('monitors.destroy', $monitor) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Delete this monitor?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center">No monitors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
