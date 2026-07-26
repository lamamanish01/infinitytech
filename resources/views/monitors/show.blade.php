@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">🔍 Monitor Details</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('monitors.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <a href="{{ route('monitors.edit', $monitor) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="text-muted border-bottom pb-2">Basic Information</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">ID</dt>
                                    <dd class="col-sm-8">{{ $monitor->id }}</dd>

                                    <dt class="col-sm-4">Name</dt>
                                    <dd class="col-sm-8">{{ $monitor->name }}</dd>

                                    <dt class="col-sm-4">Host</dt>
                                    <dd class="col-sm-8"><code>{{ $monitor->host }}</code></dd>

                                    <dt class="col-sm-4">Check Type</dt>
                                    <dd class="col-sm-8">
                                        @if($monitor->check_type === 'snmp')
                                            <span class="badge badge-info">SNMP</span>
                                        @else
                                            <span class="badge badge-secondary">PING</span>
                                        @endif
                                    </dd>

                                    <!-- SNMP fields - always displayed -->
                                    <dt class="col-sm-4">SNMP Community</dt>
                                    <dd class="col-sm-8">{{ $monitor->snmp_community ?? '—' }}</dd>

                                    <dt class="col-sm-4">SNMP Version</dt>
                                    <dd class="col-sm-8">{{ $monitor->snmp_version ?? '—' }}</dd>

                                    @if($monitor->check_type === 'snmp')
                                        <dt class="col-sm-4">Port</dt>
                                        <dd class="col-sm-8">{{ $monitor->snmp_port }}</dd>

                                        <dt class="col-sm-4">Timeout</dt>
                                        <dd class="col-sm-8">{{ $monitor->snmp_timeout }} seconds</dd>

                                        <dt class="col-sm-4">OID</dt>
                                        <dd class="col-sm-8"><code>{{ $monitor->snmp_oid }}</code></dd>
                                    @endif
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <h5 class="text-muted border-bottom pb-2">Status & Metrics</h5>
                                <dl class="row">
                                    <dt class="col-sm-4">Status</dt>
                                    <dd class="col-sm-8">
                                        <span class="badge {{ $monitor->status_badge_class }} fs-6">
                                            {{ $monitor->status_label }}
                                        </span>
                                    </dd>

                                    <dt class="col-sm-4">Uptime</dt>
                                    <dd class="col-sm-8">{{ $monitor->formatted_uptime }}</dd>

                                    <dt class="col-sm-4">Response Time</dt>
                                    <dd class="col-sm-8">{{ $monitor->response_time ? $monitor->response_time.' ms' : '—' }}</dd>

                                    <dt class="col-sm-4">Successful / Total</dt>
                                    <dd class="col-sm-8">{{ $monitor->success_count }} / {{ $monitor->total_count }}</dd>

                                    <dt class="col-sm-4">Last Checked</dt>
                                    <dd class="col-sm-8">
                                        {{ $monitor->last_checked_at?->format('Y-m-d H:i:s') ?? 'Never' }}
                                        @if($monitor->last_checked_at)
                                            <small class="text-muted">({{ $monitor->last_checked_at->diffForHumans() }})</small>
                                        @endif
                                    </dd>

                                    <dt class="col-sm-4">Created</dt>
                                    <dd class="col-sm-8">{{ $monitor->created_at->format('Y-m-d H:i:s') }}</dd>

                                    <dt class="col-sm-4">Updated</dt>
                                    <dd class="col-sm-8">{{ $monitor->updated_at->format('Y-m-d H:i:s') }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete button -->
                <div class="mt-3">
                    <form action="{{ route('monitors.destroy', $monitor) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Delete this monitor permanently?')">
                            <i class="fas fa-trash"></i> Delete Monitor
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
