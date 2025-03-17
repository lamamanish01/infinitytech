@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Customers') }}</h1>
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
                            <form action="{{route('customers.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Full Name">
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="text" class="form-control" name="email" placeholder="Enter Email">
                                </div>
                                <div class="form-group">
                                    <label>Username:</label>
                                    <input type="text" class="form-control" name="username" placeholder="Enter Username Name">
                                </div>
                                <div class="form-group">
                                    <label>Password:</label>
                                    <input type="text" class="form-control" name="password" placeholder="Enter Password Names">
                                </div>
                                <div class="form-select">
                                    <label>Internet Plans:</label>
                                        <select name="internetplan" class="custom-select">
                                            <option value="">Select Internet Plans</option>
                                            @foreach ($internetplans as $internetplan)
                                                <option value="{{$internetplan->bandwidth_name}}">{{$internetplan->bandwidth_name}}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="form-group">
                                    <label>Address:</label>
                                    <input type="text" class="form-control" name="address" placeholder="Enter Address">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number:</label>
                                    <input type="text" class="form-control" name="contact_number" placeholder="Enter Contact Number">
                                </div>
                                <div class="form-select">
                                    <label>Branch:</label>
                                    @foreach ($branches as $branch)
                                        <select name="branch" class="custom-select">
                                            <option value="">Select Branch</option>
                                            <option value="{{$branch->name}}">{{$branch->name}}</option>
                                        </select>
                                    @endforeach
                                </div>
                                <div class="form-group">
                                    <label>Registration Date:</label>
                                    <input type="date" class="form-control" name="registration_date" placeholder="0">
                                </div>

                                <div class="btn-group mt-2">
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
