@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Activity Logs</h1>
            </div>

        </div>

    </div>

</div>

<!-- Main Content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-body table-responsive p-0">

                        <table class="table table-sm table-striped table-hover text-nowrap">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Message</th>
                                    <th>User</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($activities as $activity)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        {{-- TITLE --}}
                                        <td>
                                            <i class="{{ $activity->icon ?? 'fas fa-bell' }}"></i>
                                            {{ $activity->title }}
                                        </td>

                                        {{-- MESSAGE --}}
                                        <td>
                                            {{ $activity->message ?? '-' }}
                                        </td>

                                        {{-- USER --}}
                                        <td>
                                            {{ $activity->user->name ?? 'System' }}
                                        </td>

                                        {{-- DATE --}}
                                        <td>
                                            {{ $activity->created_at->format('Y-m-d H:i') }}
                                            <br>
                                            <small class="text-muted">
                                                {{ $activity->created_at->diffForHumans() }}
                                            </small>
                                        </td>

                                        {{-- STATUS --}}
                                        <td>
                                            @if($activity->is_read)
                                                <span class="badge badge-success">Read</span>
                                            @else
                                                <span class="badge badge-warning">Unread</span>
                                            @endif
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No Activities Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $activities->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
