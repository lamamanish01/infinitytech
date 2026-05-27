@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Create User</h1>
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

                        <form action="{{ route('users.store') }}"
                              method="POST">

                            @csrf

                            {{-- NAME --}}
                            <div class="form-group">

                                <label>Name</label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       placeholder="Enter name"
                                       value="{{ old('name') }}"
                                       required>

                                @error('name')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- EMAIL --}}
                            <div class="form-group">

                                <label>Email</label>

                                <input type="email"
                                       name="email"
                                       class="form-control"
                                       placeholder="Enter email"
                                       value="{{ old('email') }}"
                                       required>

                                @error('email')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- BRANCH --}}
                            <div class="form-group">

                                <label>Branch</label>

                                <select name="branch_id"
                                        class="form-control">

                                    <option value="">
                                        None
                                    </option>

                                    @foreach ($branches as $branch)

                                        <option value="{{ $branch->id }}"
                                            {{ old('branch_id') == $branch->id ? 'selected' : '' }}>

                                            {{ $branch->name }}

                                        </option>

                                    @endforeach

                                </select>

                            </div>

                            {{-- PASSWORD --}}
                            <div class="form-group">

                                <label>Password</label>

                                <input type="password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Enter password"
                                       required>

                                @error('password')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- CONFIRM PASSWORD --}}
                            <div class="form-group">

                                <label>Confirm Password</label>

                                <input type="password"
                                       name="password_confirmation"
                                       class="form-control"
                                       placeholder="Confirm password"
                                       required>

                            </div>

                            {{-- BUTTON --}}
                            <button type="submit"
                                    class="btn btn-primary">

                                Create User

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection
