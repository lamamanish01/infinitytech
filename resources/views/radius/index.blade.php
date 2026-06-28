@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">All Radius Auth</h4>
        </div>

        {{--  @can('create nas')
            <a href="{{ route('nas.create') }}"
               class="btn btn-primary btn-sm">
                + Add NAS
            </a>
        @endcan  --}}

    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-sm">

                    <thead>
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Pass</th>
                            <th>Reply</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($authLogs as $log)

                            {{-- Conditional row background based on reply --}}
                            @php
                                $rowClass = '';
                                if ($log->reply == 'Access-Accept') {
                                    $rowClass = 'table-success'; // light green
                                } elseif ($log->reply == 'Access-Reject') {
                                    $rowClass = 'table-danger';  // light red
                                }
                            @endphp

                            <tr class="{{ $rowClass }}">
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $log->username }}</td>
                                <td>{{ $log->pass }}</td>
                                <td>{{ $log->reply }}</td>
                                <td>{{ optional($log->authdate)->toDateTimeString() }}</td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $authLogs->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
