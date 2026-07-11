@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- HEADER with Logs link --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">SMS Queue</h4>
        <div>
            @can('view sms queues')
                <a href="{{ route('sms.queues') }}" class="btn btn-warning btn-sm me-2">
                    📋 View Queues
                </a>
            @endcan

            @can('view sms logs')
                <a href="{{ route('sms.logs') }}" class="btn btn-info btn-sm me-2">
                    📋 View Logs
                </a>
            @endcan

            @can('create sms gateway')
                <a href="{{ route('sms.create') }}" class="btn btn-primary btn-sm">
                    Create
                </a>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="card card-info">
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped table-hover text-nowrap">
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
                                <div class="btn-group btn-group-sm">
                                    @can('edit sms gateway')
                                            <a href="{{ route('sms.edit', $smsGateway->id) }}"
                                               class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                    @endcan

                                    @can('delete sms gateway')
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
