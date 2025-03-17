@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create NAS') }}</h1>
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
                            <form action="{{route('menus.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>NAS Name:</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Title">
                                </div>
                                <div class="form-group">
                                    <label>IP Address:</label>
                                    <input type="text" class="form-control" name="ip" placeholder="Enter Title">
                                </div>
                                <div class="form-group">
                                    <label>URL:</label>
                                    <input type="text" class="form-control" name="url" placeholder="Enter URL">
                                </div>
                                <div class="form-group">
                                    <label>Icon:</label>
                                    <input type="text" class="form-control" name="icon" placeholder="Enter Icon">
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
