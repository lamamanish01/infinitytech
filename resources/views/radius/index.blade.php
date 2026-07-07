@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">RADIUS Authentication Logs</h4>
        <span class="badge bg-primary">
            Total: {{ $authLogs->total() }}
        </span>
    </div>

    <div class="card">

        <div class="card-body p-0">

            {{-- Responsive wrapper – scroll on small screens only --}}
            <div class="table-responsive-sm">
                <table class="table table-hover table-bordered mb-0">

                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th>Status</th>
                            <th>Reply Message</th>
                            <th>Nas IP Address</th>
                            <th>Mac Address</th>
                            <th width="180" class="text-nowrap">Date</th>  {{-- Prevent date from wrapping --}}
                        </tr>
                    </thead>

                    <tbody>

                    @forelse($authLogs as $log)

                        <tr>
                            <td>{{ $authLogs->firstItem() + $loop->index }}</td>
                            <td>{{ $log->username }}</td>
                            <td>{{ $log->pass }}</td>
                            <td>
                                @if($log->reply == 'Access-Accept')
                                    <span class="badge bg-success">Access-Accept</span>
                                @elseif($log->reply == 'Access-Reject')
                                    <span class="badge bg-danger">Access-Reject</span>
                                @else
                                    <span class="badge bg-secondary">{{ $log->reply }}</span>
                                @endif
                            </td>
                            <td>{{ $log->reply_message }}</td>
                            <td>{{ $log->nasipaddress }}</td>
                            <td>{{ $log->mac }}</td>
                            <td class="text-nowrap">{{ optional($log->authdate)->format('Y-m-d H:i') }}</td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                No authentication logs found.
                            </td>
                        </tr>

                    @endforelse

                    </tbody>

                </table>

                {{-- Pagination – outside the scrolling container --}}
                @if($authLogs->hasPages())
                    <div class="card-footer d-flex justify-content-end border-top-0">
                        {{ $authLogs->links() }}
                    </div>
                @endif
            </div> {{-- /table-responsive-sm --}}

        </div> {{-- /card-body --}}

    </div> {{-- /card --}}

</div> {{-- /container-fluid --}}

@endsection
