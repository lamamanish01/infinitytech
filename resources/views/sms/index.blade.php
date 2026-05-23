@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of SMS Gateway') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create roles')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('sms.create') }}"></i> Create </a>
                </div>
            @endcan
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card card-info">
                        {{--  <div class="card-header">
                            <h3 class="card-title">Color &amp; Time Picker</h3>
                        </div>  --}}

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
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $sms->username }}</td>
                                    <td>{{ $sms->mobile }}</td>
                                    <td>{{ Str::limit($sms->message, 40) }}</td>
                                    <td>
                                        <span class="badge bg-info">
                                            {{ $sms->type }}
                                        </span>
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
                                            <input type="hidden" name="id" value="{{ $sms->id }}">

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
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
