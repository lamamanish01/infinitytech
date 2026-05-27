@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    Show Ticket
                </h1>
            </div>
        </div>

    </div>
</div>

<!-- Main content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                {{-- TICKET INFO --}}
                <div class="card card-info mb-3">

                    <div class="card-header">
                        <h3 class="card-title">
                            Ticket Information
                        </h3>
                    </div>

                    <div class="card-body">

                        <h4>
                            {{ $ticket->subject }}
                        </h4>

                        <p>
                            <strong>Ticket:</strong>
                            {{ $ticket->ticket_no }}
                        </p>

                        <p>
                            <strong>Customer:</strong>
                            {{ $ticket->customer->name }}
                        </p>

                        <p>
                            <strong>Status:</strong>

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
                        </p>

                        <p>
                            <strong>Assigned To:</strong>

                            {{ $ticket->assignedUser->name ?? 'Unassigned' }}
                        </p>

                        <p>
                            <strong>Created By:</strong>

                            {{ $ticket->creator->name ?? 'System' }}
                        </p>

                    </div>

                </div>

                {{-- ASSIGN TICKET --}}
                <div class="card mb-3">

                    <div class="card-header">
                        <h3 class="card-title">
                            Assign Ticket
                        </h3>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('tickets.assign', $ticket->id) }}">

                            @csrf

                            <div class="form-group">

                                <select name="assigned_to"
                                        class="form-control">

                                    @foreach($users as $user)

                                        <option value="{{ $user->id }}"
                                            {{ $ticket->assigned_to == $user->id ? 'selected' : '' }}>

                                            {{ $user->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            <button class="btn btn-primary">
                                Assign Ticket
                            </button>

                        </form>

                    </div>

                </div>

                {{-- UPDATE STATUS --}}
                <div class="card mb-3">

                    <div class="card-header">
                        <h3 class="card-title">
                            Update Status
                        </h3>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('tickets.status', $ticket->id) }}">

                            @csrf

                            <div class="form-group">

                                <select name="status"
                                        class="form-control">

                                    <option value="open"
                                        {{ $ticket->status == 'open' ? 'selected' : '' }}>
                                        Open
                                    </option>

                                    <option value="in_progress"
                                        {{ $ticket->status == 'in_progress' ? 'selected' : '' }}>
                                        In Progress
                                    </option>

                                    <option value="resolved"
                                        {{ $ticket->status == 'resolved' ? 'selected' : '' }}>
                                        Resolved
                                    </option>

                                    <option value="closed"
                                        {{ $ticket->status == 'closed' ? 'selected' : '' }}>
                                        Closed
                                    </option>

                                </select>

                            </div>

                            <button class="btn btn-success">
                                Update Status
                            </button>

                        </form>

                    </div>

                </div>

                {{-- CONVERSATION --}}
                <div class="card mb-3">

                    <div class="card-header">
                        <h3 class="card-title">
                            Conversation
                        </h3>
                    </div>

                    <div class="card-body">

                        @forelse($ticket->replies->where('is_internal', false) as $reply)

                            <div class="border rounded p-3 mb-3">

                                <div class="d-flex justify-content-between">

                                    <strong>

                                        @if($reply->customer_id)

                                            {{ $reply->customer->name }}
                                            (Customer)

                                        @else

                                            {{ $reply->user->name }}
                                            (Staff)

                                        @endif

                                    </strong>

                                    <small class="text-muted">
                                        {{ $reply->created_at->diffForHumans() }}
                                    </small>

                                </div>

                                <div class="mt-2">
                                    {{ $reply->message }}
                                </div>

                            </div>

                        @empty

                            <p class="text-muted">
                                No replies found.
                            </p>

                        @endforelse

                    </div>

                </div>

                {{-- STAFF REPLY --}}
                <div class="card mb-3">

                    <div class="card-header">
                        <h3 class="card-title">
                            Reply
                        </h3>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('tickets.reply', $ticket->id) }}">

                            @csrf

                            <div class="form-group">

                                <textarea name="message"
                                          rows="4"
                                          class="form-control"
                                          placeholder="Write reply..."
                                          required></textarea>

                            </div>

                            <button class="btn btn-success">
                                Send Reply
                            </button>

                        </form>

                    </div>

                </div>

                {{-- INTERNAL NOTE --}}
                <div class="card">

                    <div class="card-header">
                        <h3 class="card-title">
                            Internal Note
                        </h3>
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('tickets.internal-note', $ticket->id) }}">

                            @csrf

                            <div class="form-group">

                                <textarea name="message"
                                          rows="3"
                                          class="form-control"
                                          placeholder="Internal note..."
                                          required></textarea>

                            </div>

                            <button class="btn btn-warning">
                                Add Internal Note
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
