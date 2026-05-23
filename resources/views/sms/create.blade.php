@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('SMS Gateway Create') }}</h1>
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
                            <form action="{{route('sms.store')}}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label>Gateway Name</label>
                                    <input type="text" name="name" class="form-control" value="Aakash SMS">
                                </div><div class="form-group">
                                    <label>API URL</label>
                                    <input type="text" name="api_url" class="form-control"
                                        value="https://sms.aakashsms.com/sms/v3/send">
                                </div>
                                {{-- Auth Token --}}
                                <div class="form-group">
                                    <label>Auth Token</label>
                                    <input type="text" name="auth_token" class="form-control" required>
                                </div>
                                {{-- Status --}}
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="is_active" class="form-control">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>

                                <div class="btn-group mt-2">
                                    <button type="submit" class="btn btn-primary">Save Sms Gateway</button>
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
