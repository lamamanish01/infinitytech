@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="mb-0">SMS Queue</h4>

        @can('create sms')
            <a href="{{ route('sms.create') }}" class="btn btn-primary btn-sm">
                Create
            </a>
        @endcan

    </div>

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

                            <td>
                                {{ \Illuminate\Support\Str::limit($sms->message, 40) }}
                            </td>

                            <td>
                                <span class="badge bg-info">{{ $sms->type }}</span>
                            </td>

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

                                <form action="{{ route('sms.send') }}" method="POST">
                                    @csrf

                                    <input type="hidden" name="mobile" value="{{ $sms->mobile }}">
                                    <input type="hidden" name="message" value="{{ $sms->message }}">

                                    <button class="btn btn-sm btn-success">
                                        Send
                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="10" class="text-center">
                                No SMS found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
