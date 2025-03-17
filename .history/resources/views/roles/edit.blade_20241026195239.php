@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Role Edit') }}</h1>
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
                            <form action="{{route('roles.update', $role->id)}}" method="POST">
                                @csrf
                                @method('patch')
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" class="form-control" name="name" value="{{$role->name}}">
                                </div>

                                @foreach ($permissions as $permission)
                                    <div class="form-check">
                                        <input {{$hasPermissions->contains($permission->name) ? 'checked' : ''}} class="form-check-input" type="checkbox" name="permission[]" value="{{$permission->name}}">
                                        <label class="form-check-label">{{$permission->name}}</label>
                                    </div>
                                @endforeach

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
