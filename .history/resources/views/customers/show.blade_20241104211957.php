@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
    <div class="col order-last">
      First in DOM, ordered last
    </div>
    <div class="col">
      Second in DOM, unordered
    </div>
    <div class="col order-first">
      Third in DOM, ordered first
    </div>
  </div>
                    </div>
                    <div class="card-body p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active" href="#activity" data-bs-toggle="tab">Info</a></li>
                            <li class="nav-item"><a class="nav-link" href="#timeline" data-bs-toggle="tab">Technical</a></li>
                            <li class="nav-item"><a class="nav-link" href="#billing" data-bs-toggle="tab">Billing</a></li>
                            <li class="nav-item"><a class="nav-link" href="#statistics" data-bs-toggle="tab">Statistics</a></li>
                            <li class="nav-item"><a class="nav-link" href="#logs" data-bs-toggle="tab">Activity Logs</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
