@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <h3>Cron Job Manager</h3>

    <!-- ================= ADD FORM ================= -->
    <div class="card mb-3">

        <div class="card-body">

            <form method="POST" action="/cron-jobs/store">
                @csrf

                <div class="row">

                    <div class="col-md-3">
                        <input type="text" name="key" class="form-control" placeholder="Key (e.g. expire_customers)">
                    </div>

                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Name">
                    </div>

                    <div class="col-md-3">
                        <select name="frequency" class="form-control">
                            <option value="minute">Minute</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block">Add Cron Job</button>
                    </div>

                </div>

            </form>

        </div>

    </div>

    <!-- ================= TABLE ================= -->
    <div class="card">

        <div class="card-body table-responsive">

            <table class="table table-bordered">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Key</th>
                        <th>Status</th>
                        <th>Frequency</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($cronJobs as $job)

                    <tr>

                        <td>{{ $job->name }}</td>
                        <td>{{ $job->key }}</td>

                        <td>
                            @if($job->is_active)
                                <span class="badge badge-success">Enabled</span>
                            @else
                                <span class="badge badge-danger">Disabled</span>
                            @endif
                        </td>

                        <td>
                            <form method="POST" action="/cron-jobs/{{ $job->id }}/frequency">
                                @csrf

                                <select name="frequency" class="form-control form-control-sm">
                                    <option value="minute" {{ $job->frequency=='minute'?'selected':'' }}>Minute</option>
                                    <option value="hourly" {{ $job->frequency=='hourly'?'selected':'' }}>Hourly</option>
                                    <option value="daily" {{ $job->frequency=='daily'?'selected':'' }}>Daily</option>
                                </select>
                        </td>

                        <td>

                            <button class="btn btn-sm btn-primary">Save</button>
                            </form>

                            <form method="POST" action="/cron-jobs/{{ $job->id }}/toggle" style="display:inline-block;">
                                @csrf

                                @if($job->is_active)
                                    <button class="btn btn-sm btn-danger">Disable</button>
                                @else
                                    <button class="btn btn-sm btn-success">Enable</button>
                                @endif

                            </form>

                            <form method="POST" action="/cron-jobs/{{ $job->id }}">
                                @csrf
                                @method('DELETE')

                                <button class="btn btn-sm btn-dark">Delete</button>
                            </form>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection
