@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER with back button -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">SMS Logs</h4>
        <a href="{{ route('sms.queues') }}" class="btn btn-secondary btn-sm">
            ← Back to Queue
        </a>
    </div>

    <!-- TABLE CARD -->
    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-sm table-striped table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Response</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $key => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $key }}</td>
                            <td>{{ $log->username }}</td>
                            <td>{{ $log->mobile }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($log->message, 15) }}</td>
                            <td>
                                @if($log->status == 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @else
                                    <span class="badge bg-danger">Failed</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ \Illuminate\Support\Str::limit($log->response, 60) }}
                                </small>
                            </td>
                            <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No SMS logs found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PAGINATION -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $logs->links() }}
    </div>

</div>

@endsection
