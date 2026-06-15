@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="mb-3">Cron Job Manager</h3>

    <!-- ADD FORM -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="POST" action="/cron-jobs/store">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <input type="text" name="key" class="form-control" placeholder="Command (e.g. customers:update-expired)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="name" class="form-control" placeholder="Display Name" required>
                    </div>
                    <div class="col-md-3">
                        <select name="frequency" class="form-control">
                            <option value="minute">Every Minute</option>
                            <option value="five_minutely">Every 5 Minutes</option>
                            <option value="hourly">Hourly</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-primary btn-block">Add Cron Job</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- CRON JOBS TABLE -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Command</th>
                        <th>Status</th>
                        <th>Frequency</th>
                        <th>Last Run</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cronJobs as $job)
                        <tr>
                            <td>{{ $job->name }}</td>
                            <td><code>{{ $job->key }}</code></td>
                            <td>
                                @if($job->is_active)
                                    <span class="badge badge-success">Enabled</span>
                                @else
                                    <span class="badge badge-danger">Disabled</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="/cron-jobs/{{ $job->id }}/frequency" class="form-inline">
                                    @csrf
                                    <select name="frequency" class="form-control form-control-sm mr-1">
                                        <option value="minute" {{ $job->frequency == 'minute' ? 'selected' : '' }}>Minute</option>
                                        <option value="five_minutely" {{ $job->frequency == 'five_minutely' ? 'selected' : '' }}>5 Minutes</option>
                                        <option value="hourly" {{ $job->frequency == 'hourly' ? 'selected' : '' }}>Hourly</option>
                                        <option value="daily" {{ $job->frequency == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ $job->frequency == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ $job->frequency == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Save</button>
                                </form>
                            </td>
                            <td>
                                {{ $job->last_run_at ? \Carbon\Carbon::parse($job->last_run_at)->diffForHumans() : 'Never' }}
                            </td>
                            <td>
                                <div class="d-flex">
                                    <form method="POST" action="/cron-jobs/{{ $job->id }}/toggle" class="mr-1">
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
                                        <button class="btn btn-sm btn-dark" onclick="return confirm('Delete this cron job?')">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No cron jobs found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
