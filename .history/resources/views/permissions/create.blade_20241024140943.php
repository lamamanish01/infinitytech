@extends('layouts.app')

@section('content')
     <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Permissions') }}</h1>
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


<div class="card-header">
    <h3 class="card-title">Color &amp; Time Picker</h3>
    </div>
    <div class="card-body">

    <div class="form-group">
    <label>Color picker:</label>
    <input type="text" class="form-control my-colorpicker1 colorpicker-element" data-colorpicker-id="1" data-original-title="" title="">
    </div>


    <div class="form-group">
    <label>Color picker with addon:</label>
    <div class="input-group my-colorpicker2 colorpicker-element" data-colorpicker-id="2">
    <input type="text" class="form-control" data-original-title="" title="">
    <div class="input-group-append">
    <span class="input-group-text"><i class="fas fa-square"></i></span>
    </div>
    </div>

    </div>


    <div class="bootstrap-timepicker">
    <div class="form-group">
    <label>Time picker:</label>
    <div class="input-group date" id="timepicker" data-target-input="nearest">
    <input type="text" class="form-control datetimepicker-input" data-target="#timepicker">
    <div class="input-group-append" data-target="#timepicker" data-toggle="datetimepicker">
    <div class="input-group-text"><i class="far fa-clock"></i></div>
    </div>
    </div>

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
