@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Edit Mikrotik') }}</h1>
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
                            <form action="{{route('mikrotik.update', $mikrotik->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label>Mikrotik Name:</label>
                                    <input type="text" name="name" value="{{$mikrotik->name}}" placeholder="Name" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>IP Address:</label>
                                    <input type="text" name="host" value="{{$mikrotik->host}}" placeholder="IP Address" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Port:</label>
                                    <input type="text" name="port" value="{{$mikrotik->port}}" placeholder="8728" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Username:</label>
                                    <input type="text" name="username" value="{{$mikrotik->username}}" placeholder="API User" class="form-control mb-2">
                                </div>
                                <div class="form-group">
                                    <label>Password:</label>
                                    <input type="text" name="password" value="{{$mikrotik->password}}" placeholder="Password" class="form-control mb-2">
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
