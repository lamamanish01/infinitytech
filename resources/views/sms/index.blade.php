@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- HEADER with Logs link --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">SMS Queue</h4>
        <div>
            <a href="{{ route('sms.logs') }}" class="btn btn-info btn-sm me-2">
                📋 View Logs
            </a>
            @can('create sms')
                <a href="{{ route('sms.create') }}" class="btn btn-primary btn-sm">
                    Create
                </a>
            @endcan
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('info'))
        <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    {{-- Table --}}
    <div class="card card-info">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>name</th>
                        <th>API URL</th>
                        <th>API Token</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($smsGateways as $smsGateway)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $smsGateway->name }}</td>
                            <td>{{ $smsGateway->api_url }}</td>
                            <td>{{ $smsGateway->auth_token }}</td>
                            <td>{{ $smsGateway->is_active }}</td>
                            <td>
                            <div class="btn-group" role="group">
                                @can('delete sms')
                                    <form action="{{ route('sms.destroy', $smsGateway->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this SMS?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No SMS found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
