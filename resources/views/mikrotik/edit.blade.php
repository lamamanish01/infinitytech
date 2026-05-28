@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="mb-3">
        <h4>Edit Mikrotik</h4>
        <small class="text-muted">Update router configuration</small>
    </div>

    <!-- CARD -->
    <div class="card shadow-sm">

        <div class="card-body">

            <form action="{{ route('mikrotik.update', $mikrotik->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row">

                    <!-- NAME -->
                    <div class="col-md-6 mb-3">
                        <label>Mikrotik Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $mikrotik->name) }}"
                               required>
                    </div>

                    <!-- HOST -->
                    <div class="col-md-6 mb-3">
                        <label>IP Address</label>
                        <input type="text"
                               name="host"
                               class="form-control"
                               value="{{ old('host', $mikrotik->host) }}"
                               required>
                    </div>

                    <!-- PORT -->
                    <div class="col-md-4 mb-3">
                        <label>Port</label>
                        <input type="number"
                               name="port"
                               class="form-control"
                               value="{{ old('port', $mikrotik->port) }}">
                    </div>

                    <!-- USERNAME -->
                    <div class="col-md-4 mb-3">
                        <label>Username</label>
                        <input type="text"
                               name="username"
                               class="form-control"
                               value="{{ old('username', $mikrotik->username) }}"
                               required>
                    </div>

                    <!-- PASSWORD -->
                    <div class="col-md-4 mb-3">
                        <label>Password</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Leave blank to keep current password">
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="d-flex gap-2 mt-3">

                    <button type="submit" class="btn btn-primary">
                        Update Mikrotik
                    </button>

                    <a href="{{ route('mikrotik.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
