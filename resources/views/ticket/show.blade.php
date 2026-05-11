@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Show ticket') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
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
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">{{$ticket->subject}}</h3><br><br>
                                        @if($ticket->status != 'closed')
                                        <form action="{{route('ticket.reply', $ticket->id)}}" method="POST">
                                        @csrf
                                            <div class="form-group">
                                                <textarea
                                                    name="message"
                                                    rows="5"
                                                    style="width:100%;"
                                                    placeholder="Write reply..."
                                                ></textarea>
                                            </div>
                                            <div class="btn-group">
                                                <button type="submit" class="btn btn-primary">Send Reply</button>
                                            </div>
                                        </form>
                                        @else
                                            This ticket is closed.
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4>Ticket Replies</h4>
                                        @forelse($ticket->replies as $reply)
                                            <div style="border:1px solid #ddd; padding:10px; margin-bottom:10px;">
                                                <b>
                                                    Reply by : {{ $reply->user->name}}
                                                </b>

                                                <br><br>
                                                {{ $reply->message }}

                                            </div>

                                        @empty

                                            <p>No replies yet.</p>

                                        @endforelse
                                    </div>
                                </div>
                            </div>
                                <div class="card">
                                    <div class="card-body">
                                        <h4>Customer Details</h4>
                                        <div class="col-md-12 mb-3">
                                            <ul class="list-group mt-6 mb-3">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Username :
                                                    <span class="badge badge-success badge-pill">{{$ticket->customer->username}}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Status :
                                                    <span class="badge badge-success badge-pill">{{$ticket->status}}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Created by :
                                                    <span class="badge badge-success badge-pill">{{$ticket->user->name}}</span>
                                                </li>
                                            </ul>
                                        </div>
                                        <form action="{{route('ticket.close', $ticket->id)}}" method="POST">
                                            @csrf
                                                <div class="btn-group">
                                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Close this ticket?')">Close Ticket</button>
                                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
