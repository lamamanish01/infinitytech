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
                            <form action="{{route('customers.update', $customer->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <input value="{{$customer->name}}" class="form-control" name="name">
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input value="{{$customer->email}}" class="form-control" name="email">
                                </div>
                                <div class="form-select">
                                    <label>Internet Plans:</label>
                                        <select name="internet_plan_id" class="custom-select">
                                            <option>Select Internet Plans</option>
                                            @foreach ($internet_plans as $internet_plan)
                                                <option value="{{$internet_plan->id}}">{{$internet_plan->bandwidth_name}}</option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="form-group">
                                    <label>Address:</label>
                                    <input value="{{$customer->address}}" class="form-control" name="address">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number:</label>
                                    <input value="{{$customer->contact_number}}" class="form-control" name="contact_number">
                                </div>
                                <div class="btn-group mt-2">
                                    <button type="submit" class="btn btn-primary">Update</button>
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
