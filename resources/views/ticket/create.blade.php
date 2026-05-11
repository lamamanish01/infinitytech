@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Ticket') }}</h1>
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

                        <div class="card-body">
                            <form action="{{route('ticket.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Customer</label>
                                    <select name="username" class="custom-select">
                                        <option value="">Select Customer Username</option>
                                            @foreach ($customers as $customer)
                                                <option>{{$customer->username}}</option>
                                            @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Priority</label>
                                    <select name="priority" class="custom-select">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Subject</label>
                                    <input name="subject" class="form-control" placeholder="Subject">
                                </div>
                                <div class="form-group">
                                    <label>Message</label>
                                    <textarea name="message" rows="5" style="width:100%;" class="form-control" placeholder="Message"></textarea>
                                </div>
                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </div>
                            </form>

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
