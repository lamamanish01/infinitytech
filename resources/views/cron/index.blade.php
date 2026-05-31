@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Cron Logs</h1>
            </div>

            <!-- 🗑️ DELETE ALL ONLY -->
            <div class="col-sm-6 text-right">

                <form action="{{ route('cron.clearAll') }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure you want to delete ALL cron logs?')"
                      style="display:inline-block;">

                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger">
                        Delete All Logs
                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<!-- Main content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-header">
                        <h3 class="card-title">System Cron Activity</h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Job Name</th>
                                    <th>Message</th>
                                    <th>Records</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($cronLogs as $log)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $log->job_name }}</td>

                                        <td>{{ $log->message }}</td>

                                        <td>
                                            <span class="badge badge-info">
                                                {{ $log->records_deleted }}
                                            </span>
                                        </td>

                                        <td>

                                            @if($log->status == 'success')
                                                <span class="badge badge-success">Success</span>
                                            @else
                                                <span class="badge badge-danger">Failed</span>
                                            @endif

                                        </td>

                                        <td>
                                            {{ $log->created_at->format('Y-m-d H:i') }}
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No Cron Logs Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $cronLogs->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
