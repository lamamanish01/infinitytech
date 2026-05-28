@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="mb-3">
        <h4>Create Mikrotik</h4>
        <small class="text-muted">Add router API connection details</small>
    </div>

    <!-- CARD -->
    <div class="card shadow-sm">

        <div class="card-body">

            <form action="{{ route('mikrotik.store') }}" method="POST">
                @csrf

                <div class="row">

                    <!-- NAME -->
                    <div class="col-md-6 mb-3">
                        <label>Mikrotik Name *</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="Office Router"
                               required>
                    </div>

                    <!-- HOST -->
                    <div class="col-md-6 mb-3">
                        <label>IP Address *</label>
                        <input type="text"
                               name="host"
                               class="form-control"
                               placeholder="192.168.1.1"
                               required>
                    </div>

                    <!-- PORT -->
                    <div class="col-md-4 mb-3">
                        <label>API Port</label>
                        <input type="number"
                               name="port"
                               class="form-control"
                               value="8728"
                               placeholder="8728">
                    </div>

                    <!-- USERNAME -->
                    <div class="col-md-4 mb-3">
                        <label>Username *</label>
                        <input type="text"
                               name="username"
                               class="form-control"
                               placeholder="admin"
                               required>
                    </div>

                    <!-- PASSWORD -->
                    <div class="col-md-4 mb-3">
                        <label>Password *</label>
                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="••••••••"
                               required>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="d-flex gap-2 mt-3">

                    <button type="submit" class="btn btn-primary">
                        Save Mikrotik
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
