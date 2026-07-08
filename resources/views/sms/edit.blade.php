@extends('layouts.app')

@section('content')

<!-- HEADER -->
<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Edit SMS Gateway</h1>
            </div>
        </div>

    </div>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-body">

                        <form action="{{ route('sms.update', $smsGateway->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- NAME -->
                            <div class="form-group mb-3">
                                <label>Gateway Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name', $smsGateway->name) }}"
                                       required>
                            </div>

                            <!-- API URL -->
                            <div class="form-group mb-3">
                                <label>API URL</label>
                                <input type="text"
                                       name="api_url"
                                       class="form-control"
                                       value="{{ old('api_url', $smsGateway->api_url) }}"
                                       required>
                            </div>

                            <!-- AUTH TOKEN -->
                            <div class="form-group mb-3">
                                <label>Auth Token</label>
                                <input type="text"
                                       name="auth_token"
                                       class="form-control"
                                       value="{{ old('auth_token', $smsGateway->auth_token) }}"
                                       required>
                            </div>

                            <!-- STATUS -->
                            <div class="form-group mb-3">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', $smsGateway->is_active) == 1 ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="0" {{ old('is_active', $smsGateway->is_active) == 0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                            </div>

                            <!-- BUTTONS -->
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Gateway
                                </button>

                                <a href="{{ route('sms.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection
