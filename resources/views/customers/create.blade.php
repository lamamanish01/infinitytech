@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Create Customer</h4>
            <small class="text-muted">Add new internet customer</small>
        </div>

        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Back
        </a>
    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form action="{{ route('customers.store') }}" method="POST">
                @csrf

                <div class="row">

                    {{-- FULL NAME --}}
                    <div class="col-md-6 mb-3">
                        <label>Full Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter full name">
                    </div>

                    {{-- EMAIL --}}
                    <div class="col-md-6 mb-3">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email">
                    </div>

                    {{-- USERNAME --}}
                    <div class="col-md-6 mb-3">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" placeholder="Enter username">
                    </div>

                    {{-- PASSWORD --}}
                    <div class="col-md-6 mb-3">
                        <label>Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Enter password">
                    </div>

                    {{-- INTERNET PLAN --}}
                    <div class="col-md-6 mb-3">
                        <label>Internet Plan</label>
                        <select name="internet_plan_id" class="form-control">
                            <option value="">Select plan</option>
                            @foreach($internet_plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->bandwidth_name }} - {{ $plan->price }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- BRANCH (FIXED - NO LOOP) --}}
                    <div class="col-md-6 mb-3">
                        <label>Branch</label>
                        <select name="branch_id" class="form-control">
                            <option value="">Select branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- ADDRESS --}}
                    <div class="col-md-6 mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Enter address">
                    </div>

                    {{-- CONTACT --}}
                    <div class="col-md-6 mb-3">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="Enter contact number">
                    </div>

                </div>

                {{-- BUTTONS --}}
                <div class="d-flex justify-content-end gap-2 mt-3">

                    <a href="{{ route('customers.index') }}" class="btn btn-sm btn-light">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-sm btn-primary">
                        Save
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
