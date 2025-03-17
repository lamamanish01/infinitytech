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
                            <form action="{{route('users.store')}}" method="post">
                                @csrf
                                <div class="form-group">
                                    <label>Name:</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Name">
                                </div>

                                <div class="form-group">
                                    <label>Email:</label>
                                    <input type="text" class="form-control" name="email" placeholder="Enter Email">
                                </div>

                                <div class="form-select">
                                    <select name="branch_id" id="">
                                        @foreach ($branches as $branch)
                                            <option value="{{$br}}"></option>
                                        @endforeach
                                    </select>
                                    <label>:</label>
                                    <input type="text" class="form-control" name="email" placeholder="Enter Email">
                                </div>

                                <div class="form-group">
                                    <label>Password:</label>
                                    <input type="text" class="form-control" name="password" placeholder="Enter Password">
                                    @error('password')
                                        {{$message}}
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>Confirm Password:</label>
                                    <input type="text" class="form-control" name="password_confirmation" placeholder="Enter Confirm Password">
                                    @error('password_confirmation')
                                        {{$message}}
                                    @enderror
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
