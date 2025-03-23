@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Internet Plans') }}</h1>
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
                            <form action="{{route('internetplan.store')}}" method="POST">
                                @csrf
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Plan Name :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="name" placeholder="Enter Plan Name">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Price :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="price" placeholder="Enter Plan Price">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Plan Validity :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="duration" placeholder="Enter Plan Validity">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="col-sm-4">
                                            <select name="type" class="custom-select">
                                                @foreach ($plan_types as $plan_type)
                                                    <option value="{{$plan_type}}">{{$plan_type}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Rate Limit :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="rate_limit" placeholder="1M/1M">
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
