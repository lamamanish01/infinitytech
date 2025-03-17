@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('User Create') }}</h1>
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
                            <form action="{{route('users.update', $user->id)}}" method="post">
                                @csrf
                                @method('patch')
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" class="form-control" name="name" value="{{$user->name}}">
                                </div>

                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="text" class="form-control" name="email" value="{{$user->email}}">
                                </div>

                                @foreach ($roles as $role)
                                    <div class="form-check">
                                        <input {{$hasRoles->contains($role->name) ? 'checked' : ''}} class="form-check-input" type="checkbox" name="role[]" value="{{$role->name}}">
                                        <label class="form-check-label">{{$role->name}}</label>
                                    </div>
                                @endforeach

                                @can('change password')
                                    <div class="form-group">
                                        <label>Password:</label>
                                        <input type="text" class="form-control" name="password" placeholder="Enter Password" required>
                                        @error('password')
                                            {{$message}}
                                        @enderror
                                    </div>
                                @endcan

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
