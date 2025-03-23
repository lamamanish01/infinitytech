@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Role Create') }}</h1>
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
                            <form action="{{route('roles.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Name">
                                </div>

                                @foreach ($permissions as $permission)
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="permission[]" value="{{$permission->name}}">
                                        <label class="form-check-label">{{$permission->name}}</label>
                                    </div>
                                @endforeach

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
