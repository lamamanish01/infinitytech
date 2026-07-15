@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Create Customers') }}</h1>
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
                            <form action="{{route('customers.update', $customer->id)}}" method="POST">
                                @csrf
                                @method('PATCH')
                                <div class="form-group">
                                    <label>Full Name:</label>
                                    <input value="{{$customer->name}}" class="form-control" name="name">
                                </div>
                                <div class="form-group">
                                    <label>Email:</label>
                                    <input value="{{$customer->email}}" class="form-control" name="email">
                                </div>
                                <div class="form-group">
                                    <label>Registration On:</label>
                                    <input type="date"
                                        value="{{ old('registered_at', optional($customer)->registered_at ? \Carbon\Carbon::parse($customer->registered_at)->format('Y-m-d') : '') }}"
                                        class="form-control"
                                        name="registered_at">
                                </div>
                                <div class="form-select">
                                    <label>Internet Plans:</label>
                                        <select name="internet_plan_id" class="custom-select" required>
                                            <option value="">Select Internet Plans</option>
                                            @foreach ($internet_plans as $plan)
                                                <option value="{{ $plan->id }}"
                                                    @selected(old('internet_plan_id', $customer->internet_plan_id) == $plan->id)>
                                                    {{ $plan->bandwidth_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                </div>
                                <div class="form-group">
                                    <label for="branch_id">Branch:</label>
                                    <select name="branch_id" id="branch_id" class="form-control">
                                        <option value="">Select a branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}"
                                                {{ old('branch_id', $customer->branch_id) == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Address:</label>
                                    <input value="{{$customer->address}}" class="form-control" name="address">
                                </div>
                                <div class="form-group">
                                    <label>Contact Number:</label>
                                    <input value="{{$customer->contact_number}}" class="form-control" name="contact_number">
                                </div>
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
