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
                        <th>Username</th>
                        <th>Mobile</th>
                        <th>Message</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Retry</th>
                        <th>Send At</th>
                        <th>Created</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($queues as $key => $sms)
                        <tr>
                            <td>{{ $queues->firstItem() + $key }}</td>
                            <td>{{ $sms->username }}</td>
                            <td>{{ $sms->mobile }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($sms->message, 40) }}</td>
                            <td><span class="badge bg-info">{{ $sms->type }}</span></td>
                            <td>
                                @if($sms->status == 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($sms->status == 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @else
                                    <span class="badge bg-danger">{{ $sms->status }}</span>
                                @endif
                            </td>
                            <td>{{ $sms->retry_count }}</td>
                            <td>{{ $sms->send_at ?? 'N/A' }}</td>
                            <td>{{ $sms->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    @if($sms->status != 'sent')
                                        <form action="{{ route('sms.send') }}" method="POST" class="me-1">
                                            @csrf
                                            <input type="hidden" name="sms_id" value="{{ $sms->id }}">
                                            <input type="hidden" name="username" value="{{ $sms->username }}">
                                            <input type="hidden" name="mobile" value="{{ $sms->mobile }}">
                                            <input type="hidden" name="message" value="{{ $sms->message }}">

                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-paper-plane"></i> Send
                                            </button>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-sm btn-secondary" disabled>
                                            Sent
                                        </button>
                                    @endif

                                    @can('delete sms queue')
                                        <form action="{{ route('sms.queues.delete', $sms->id) }}"
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

    {{-- Bulk Send All Unsent --}}
    <form action="{{ route('sms.send') }}" method="POST" class="mt-3">
        @csrf
        <input type="hidden" name="bulk" value="1">
        <button class="btn btn-primary">Send All Unsent</button>
    </form>

    {{-- Pagination --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $queues->links() }}
    </div>

</div>
@endsection
