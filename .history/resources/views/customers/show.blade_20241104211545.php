@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-pills">
                        <li class="nav-item"><a class="nav-link active" href="#activity" data-bs-toggle="tab">Info</a></li>
                        <li class="nav-item"><a class="nav-link" href="#timeline" data-bs-toggle="tab">Technical</a></li>
                        <li class="nav-item"><a class="nav-link" href="#billing" data-bs-toggle="tab">Billing</a></li>
                        <li class="nav-item"><a class="nav-link" href="#stat" data-bs-toggle="tab">Statistics</a></li>
                        <li class="nav-item"><a class="nav-link" href="#settings" data-bs-toggle="tab">Activity Logs</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
