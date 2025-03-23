@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Bandwidths') }}</h1>
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
                            <form action="{{route('bandwidth.store')}}" method="POST">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Bandwidth Name:</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="name" placeholder="1Mbps">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Upload Rate:</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control"  placeholder="1M" name="upload_rate">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Download Rate:</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control"  placeholder="1M" name="download_rate">
                                        </div>
                                    </div>
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
