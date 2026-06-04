@extends('layouts.app')

@section('content')

<!-- HEADER -->
<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">SMS Gateway Create</h1>
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

                        <form action="{{ route('sms.store') }}" method="POST">
                            @csrf

                            <!-- NAME -->
                            <div class="form-group">
                                <label>Gateway Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="Aakash SMS"
                                       required>
                            </div>

                            <!-- API URL -->
                            <div class="form-group">
                                <label>API URL</label>
                                <input type="text"
                                       name="api_url"
                                       class="form-control"
                                       value="https://sms.aakashsms.com/sms/v3/send"
                                       required>
                            </div>

                            <!-- AUTH TOKEN -->
                            <div class="form-group">
                                <label>Auth Token</label>
                                <input type="text"
                                       name="auth_token"
                                       class="form-control"
                                       required>
                            </div>

                            <!-- STATUS -->
                            <div class="form-group">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>

                            <!-- SUBMIT -->
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary">
                                    Save SMS Gateway
                                </button>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>

    </div>
</div>

@endsection
