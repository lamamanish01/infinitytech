@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Create Branch</h1>
            </div>

        </div>

    </div>

</div>

<!-- Main Content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-body">

                        <form action="{{ route('branch.store') }}"
                              method="POST">

                            @csrf

                            {{-- NAME --}}
                            <div class="form-group">

                                <label>Name</label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       placeholder="Enter branch name"
                                       value="{{ old('name') }}"
                                       required>

                                @error('name')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- ADDRESS --}}
                            <div class="form-group">

                                <label>Address</label>

                                <input type="text"
                                       name="address"
                                       class="form-control"
                                       placeholder="Enter address"
                                       value="{{ old('address') }}"
                                       required>

                                @error('address')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- CONTACT --}}
                            <div class="form-group">

                                <label>Contact Number</label>

                                <input type="text"
                                       name="contact_number"
                                       class="form-control"
                                       placeholder="Enter contact number"
                                       value="{{ old('contact_number') }}"
                                       required>

                                @error('contact_number')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- BALANCE (SECURITY NOTE) --}}
                            <div class="form-group">

                                <label>Opening Balance</label>

                                <input type="number"
                                       name="balance"
                                       class="form-control"
                                       placeholder="Enter initial balance"
                                       value="{{ old('balance', 0) }}"
                                       step="0.01">

                                <small class="text-muted">
                                    Optional: Used only for initial setup
                                </small>

                            </div>

                            {{-- REMARKS --}}
                            <div class="form-group">

                                <label>Remarks</label>

                                <input type="text"
                                       name="remarks"
                                       class="form-control"
                                       placeholder="Optional remarks"
                                       value="{{ old('remarks') }}">

                            </div>

                            {{-- BUTTON --}}
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
