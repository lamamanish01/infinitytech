@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Branch Edit') }}</h1>
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
                            <form action="{{route('branch.update', $branch->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" class="form-control" name="name" value="{{$branch->name}}">
                                </div>
                                <div class="form-group">
                                    <label>Address:</label>
                                    <input type="text" class="form-control" name="name" value="{{$branch->name}}">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number:</label>
                                    <input type="text" class="form-control" name="contact_number" value="{{$branch->contact_number}}">
                                </div>
                                <div class="btn-group">
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
