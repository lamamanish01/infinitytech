@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ isset($monitor) ? 'Edit' : 'Create' }} Monitor</h1>
            </div>
            <div class="col-sm-6 text-right">
                <a href="{{ route('monitors.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ isset($monitor) ? 'Edit' : 'Create' }} Monitor</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ isset($monitor) ? route('monitors.update', $monitor) : route('monitors.store') }}"
                              method="POST">
                            @csrf
                            @if(isset($monitor)) @method('PUT') @endif

                            <!-- NAME -->
                            <div class="form-group">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $monitor->name ?? '') }}"
                                       placeholder="e.g. Main Server" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- HOST -->
                            <div class="form-group">
                                <label for="host">Host (IP or Domain) <span class="text-danger">*</span></label>
                                <input type="text" id="host" name="host"
                                       class="form-control @error('host') is-invalid @enderror"
                                       value="{{ old('host', $monitor->host ?? '') }}"
                                       placeholder="8.8.8.8 or example.com" required>
                                @error('host') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- CHECK TYPE -->
                            <div class="form-group">
                                <label for="check_type">Check Method</label>
                                <select id="check_type" name="check_type"
                                        class="form-control @error('check_type') is-invalid @enderror">
                                    @foreach(\App\Models\Monitor::getCheckTypes() as $type)
                                        <option value="{{ $type }}"
                                            {{ old('check_type', $monitor->check_type ?? 'ping') == $type ? 'selected' : '' }}>
                                            {{ strtoupper($type) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('check_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- =============== SNMP FIELDS (always shown) =============== -->
                            <div class="card card-secondary mt-3">
                                <div class="card-header">
                                    <h5 class="card-title">SNMP Settings</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label for="snmp_community">SNMP Community</label>
                                        <input type="text" id="snmp_community" name="snmp_community"
                                               class="form-control @error('snmp_community') is-invalid @enderror"
                                               value="{{ old('snmp_community', $monitor->snmp_community ?? 'public') }}"
                                               placeholder="public">
                                        @error('snmp_community') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">Default is 'public'.</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="snmp_version">SNMP Version</label>
                                        <select id="snmp_version" name="snmp_version"
                                                class="form-control @error('snmp_version') is-invalid @enderror">
                                            <option value="v1" {{ old('snmp_version', $monitor->snmp_version ?? 'v2c') == 'v1' ? 'selected' : '' }}>v1</option>
                                            <option value="v2c" {{ old('snmp_version', $monitor->snmp_version ?? 'v2c') == 'v2c' ? 'selected' : '' }}>v2c</option>
                                            <option value="v3" {{ old('snmp_version', $monitor->snmp_version ?? 'v2c') == 'v3' ? 'selected' : '' }}>v3</option>
                                        </select>
                                        @error('snmp_version') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="snmp_port">Port</label>
                                        <input type="number" id="snmp_port" name="snmp_port"
                                               class="form-control @error('snmp_port') is-invalid @enderror"
                                               value="{{ old('snmp_port', $monitor->snmp_port ?? 161) }}"
                                               min="1" max="65535">
                                        @error('snmp_port') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="snmp_timeout">Timeout (seconds)</label>
                                        <input type="number" id="snmp_timeout" name="snmp_timeout"
                                               class="form-control @error('snmp_timeout') is-invalid @enderror"
                                               value="{{ old('snmp_timeout', $monitor->snmp_timeout ?? 1) }}"
                                               min="1" max="10">
                                        @error('snmp_timeout') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="snmp_oid">OID to retrieve</label>
                                        <input type="text" id="snmp_oid" name="snmp_oid"
                                               class="form-control @error('snmp_oid') is-invalid @enderror"
                                               value="{{ old('snmp_oid', $monitor->snmp_oid ?? '.1.3.6.1.2.1.1.1.0') }}"
                                               placeholder=".1.3.6.1.2.1.1.1.0">
                                        @error('snmp_oid') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        <small class="form-text text-muted">e.g., sysDescr (.1.3.6.1.2.1.1.1.0).</small>
                                    </div>
                                </div>
                            </div>

                            <!-- STATUS (manual override) -->
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select id="status" name="status"
                                        class="form-control @error('status') is-invalid @enderror">
                                    @foreach(\App\Models\Monitor::getStatuses() as $status)
                                        <option value="{{ $status }}"
                                            {{ old('status', $monitor->status ?? 'unknown') == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- UPTIME -->
                            <div class="form-group">
                                <label for="uptime">Uptime (%)</label>
                                <input type="number" id="uptime" name="uptime"
                                       class="form-control @error('uptime') is-invalid @enderror"
                                       step="0.01" min="0" max="100"
                                       value="{{ old('uptime', $monitor->uptime ?? '') }}"
                                       placeholder="99.99">
                                @error('uptime') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- LAST CHECKED AT -->
                            <div class="form-group">
                                <label for="last_checked_at">Last Checked At</label>
                                <input type="datetime-local" id="last_checked_at" name="last_checked_at"
                                       class="form-control @error('last_checked_at') is-invalid @enderror"
                                       value="{{ old('last_checked_at', isset($monitor) && $monitor->last_checked_at ? $monitor->last_checked_at->format('Y-m-d\TH:i') : '') }}">
                                @error('last_checked_at') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <!-- SUBMIT -->
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> {{ isset($monitor) ? 'Update' : 'Create' }}
                                </button>
                                <a href="{{ route('monitors.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
