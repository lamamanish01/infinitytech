@extends('layouts.app')

@section('title', 'TR-069 Servers')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">TR-069 Servers</h3>
                    <div class="card-tools">
                        @can('create acsserver')
                        <a href="{{ route('tr069server.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Server
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-server"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Servers</span>
                                    <span class="info-box-number">{{ $totalServers }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Active Servers</span>
                                    <span class="info-box-number">{{ $activeServers }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Down Servers</span>
                                    <span class="info-box-number">{{ $downServers }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>ACS URL</th>
                                    <th>Status</th>
                                    <th>Devices</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tr069Servers as $server)
                                <tr>
                                    <td>{{ $server->id }}</td>
                                    <td>{{ $server->name }}</td>
                                    <td>{{ $server->acs_url }}</td>
                                    <td>
                                        <span class="badge {{ $server->status == 'active' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($server->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $server->devices_count }}</td>
                                    <td>
                                        <a href="{{ route('tr069server.show', $server) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('edit acsserver')
                                        <a href="{{ route('tr069server.edit', $server) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                        @can('delete acsserver')
                                        <form action="{{ route('tr069server.destroy', $server->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this server?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No TR-069 servers found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
