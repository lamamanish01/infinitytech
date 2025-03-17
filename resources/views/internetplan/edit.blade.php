@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Edit Internet Plans') }}</h1>
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
                            <form action="{{route('internetplan.update', $internetplan->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Plan Name :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="name" placeholder="Enter Plan Name" value="{{$internetplan->name}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Price :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="price" placeholder="Enter Plan Price" value="{{$internetplan->price}}">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-2 col-form-label">Plan Validity :</label>
                                    <div class="col">
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control" name="duration" placeholder="Enter Plan Validity" value="{{$internetplan->duration}}">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="col-sm-4">
                                            <select name="type" class="custom-select">
                                                @if (!empty($internetplan->type))
                                                    <option value="{{ $internetplan->type }}" selected>
                                                        {{ $internetplan->type }}
                                                    </option>
                                                @endif

                                                @foreach($plan_types as $plan_type)
                                                    @if($plan_type !== $internetplan->type)
                                                        <option value="{{ $plan_type }}">{{ $plan_type }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="btn-group">
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
