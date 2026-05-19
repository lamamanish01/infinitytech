@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Mikrotik') }}</h1>
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
                            <form action="{{route('mikrotik.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Mikrotik Name:</label>
                                    <input type="text" name="name" placeholder="Name" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>IP Address:</label>
                                    <input type="text" name="host" placeholder="IP Address" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Port:</label>
                                    <input type="text" name="port" placeholder="8728" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Username:</label>
                                    <input type="text" name="username" placeholder="API User" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Password:</label>
                                    <input type="text" name="password" placeholder="Password" class="form-control mb-2">
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
