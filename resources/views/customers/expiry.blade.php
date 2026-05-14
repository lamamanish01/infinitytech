@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Change Customer Expiry Date') }}</h1>
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
                        <div class="card-body">
                            <form action="{{route('customers.change-expiry', $customer->id)}}" method="POST">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{$customer->id}}">
                                <div class="form-group">
                                    <label>Username:</label>
                                    <input type="text" class="form-control" name="username" value="{{$customer->username}}" readonly>
                                </div>
                                <div class="form-group">
                                    <label>Expiry Date:</label>
                                    <input type="date" class="form-control" name="expire_date" value="{{$customer->expire_date->format('Y-m-d')}}">
                                </div>

                                <div class="btn-group">
                                    <button type="submit" class="btn btn-primary">Recharge</button>
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
