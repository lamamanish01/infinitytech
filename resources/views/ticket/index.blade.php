@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

            <div>
                <h4 class="mb-0">List of Tickets</h4>
            </div>

            <div>
                @can('create tickets')
                    <a href="{{ route('ticket.create') }}"
                       class="btn btn-sm btn-primary">
                        Create Ticket
                    </a>
                @endcan
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
                        <h3 class="card-title">Tickets</h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-sm table-striped table-hover text-center text-nowrap">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Ticket No</th>
                                    <th>Customer</th>
                                    <th>Subject</th>
                                    <th>Assigned To</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th width="180">Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($tickets as $ticket)

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $ticket->ticket_no }}
                                        </td>

                                        <td>
                                            {{ optional($ticket->customer)->username ?? '-' }}
                                        </td>

                                        <td>
                                            {{ $ticket->subject }}
                                        </td>

                                        {{-- ASSIGNED USER --}}
                                        <td>

                                            @if($ticket->assignedUser)
                                                <span class="badge badge-primary">
                                                    {{ $ticket->assignedUser->name }}
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    Unassigned
                                                </span>
                                            @endif

                                        </td>

                                        {{-- PRIORITY --}}
                                        <td>

                                            @if($ticket->priority == 'high')
                                                <span class="badge badge-danger">High</span>

                                            @elseif($ticket->priority == 'medium')
                                                <span class="badge badge-warning">Medium</span>

                                            @else
                                                <span class="badge badge-info">Low</span>
                                            @endif

                                        </td>

                                        {{-- STATUS --}}
                                        <td>

                                            @if($ticket->status == 'open')
                                                <span class="badge badge-danger">Open</span>

                                            @elseif($ticket->status == 'in_progress')
                                                <span class="badge badge-warning">In Progress</span>

                                            @elseif($ticket->status == 'resolved')
                                                <span class="badge badge-info">Resolved</span>

                                            @else
                                                <span class="badge badge-success">Closed</span>
                                            @endif

                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>

                                            <div class="btn-group">

                                                @can('view tickets')
                                                    <a href="{{ route('ticket.show', $ticket->id) }}"
                                                       class="btn btn-sm btn-primary">
                                                        Show
                                                    </a>
                                                @endcan

                                                @can('edit tickets')
                                                    <a href="{{ route('ticket.edit', $ticket->id) }}"
                                                       class="btn btn-sm btn-secondary">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('delete tickets')
                                                    <form action="{{ route('ticket.destroy', $ticket->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Delete this ticket?')">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger">
                                                            Delete
                                                        </button>

                                                    </form>
                                                @endcan

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center">
                                            No Tickets Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
