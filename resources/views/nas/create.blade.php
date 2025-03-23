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
                            <form action="{{route('nas.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>NAS Name:</label>
                                    <input type="text" class="form-control" name="name" placeholder="Enter Name">
                                </div>
                                <div class="form-group">
                                    <label>IP Address:</label>
                                    <input type="text" class="form-control" name="ipaddress" placeholder="0.0.0.0/0">
                                </div>
                                <div class="form-group">
                                    <label>Secret:</label>
                                    <input type="text" class="form-control" name="secret" placeholder="radius123">
                                </div>
                                <div class="form-group">
                                    <label>Ports:</label>
                                    <input type="text" class="form-control" name="ports" placeholder="3799">
                                </div>
                                <div class="form-select">
                                    <label>Type:</label>
                                    <select name="type" class="custom-select">
                                        <option value="other">Other</option>
                                        <option value="mikrotik">Mikrotik</option>
                                        <option value="juniper">Juniper</option>
                                    </select>
                                </div>
                                <div class="form-group mt-2">
                                    <label>Description:</label>
                                    <textarea type="text" class="form-control" name="description" placeholder="..."></textarea>
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
