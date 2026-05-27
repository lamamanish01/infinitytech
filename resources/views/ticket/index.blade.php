@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">
                    List of Tickets
                </h1>
            </div>

            <div class="col-sm-6 text-right">

                <a href="{{ route('ticket.create') }}"
                   class="btn btn-primary">

                    Create Ticket

                </a>

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
                        <h3 class="card-title">
                            Tickets
                        </h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">

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
                                            {{ $ticket->customer->username ?? '-' }}
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

                                                <span class="badge badge-danger">
                                                    High
                                                </span>

                                            @elseif($ticket->priority == 'medium')

                                                <span class="badge badge-warning">
                                                    Medium
                                                </span>

                                            @else

                                                <span class="badge badge-info">
                                                    Low
                                                </span>

                                            @endif

                                        </td>

                                        {{-- STATUS --}}
                                        <td>

                                            @if($ticket->status == 'open')

                                                <span class="badge badge-danger">
                                                    Open
                                                </span>

                                            @elseif($ticket->status == 'in_progress')

                                                <span class="badge badge-warning">
                                                    In Progress
                                                </span>

                                            @elseif($ticket->status == 'resolved')

                                                <span class="badge badge-info">
                                                    Resolved
                                                </span>

                                            @else

                                                <span class="badge badge-success">
                                                    Closed
                                                </span>

                                            @endif

                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>

                                            <div class="btn-group">

                                                <a href="{{ route('ticket.show', $ticket->id) }}"
                                                   class="btn btn-sm btn-primary">

                                                    Show

                                                </a>

                                                <a href="{{ route('ticket.edit', $ticket->id) }}"
                                                   class="btn btn-sm btn-secondary">

                                                    Edit

                                                </a>

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

                <div class="mt-3">
                    {{ $tickets->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
