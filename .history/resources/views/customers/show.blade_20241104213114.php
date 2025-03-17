@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col">
                              <h2>{{$customer->name}}</h2>
                            </div>
                            <div class="col text-center mt-2">
                                <i class="fas fa-phone"></i>
                                {{$customer->contact_number}}
                            </div>
                            <div class="col text-center mt-2">
                                <i class="fas fa-map-marker-alt"></i>
                                {{$customer->address}}
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-2">
                        <ul class="nav nav-pills">
                            <li class="nav-item"><a class="nav-link active"  id="pills-tabContent"href="#activity" data-bs-toggle="tab">Info</a></li>
                            <li class="nav-item"><a class="nav-link" href="#timeline" data-bs-toggle="tab">Technical</a></li>
                            <li class="nav-item"><a class="nav-link" href="#billing" data-bs-toggle="tab">Billing</a></li>
                            <li class="nav-item"><a class="nav-link" href="#statistics" data-bs-toggle="tab">Statistics</a></li>
                            <li class="nav-item"><a class="nav-link" href="#logs" data-bs-toggle="tab">Activity Logs</a></li>
                        </ul>
                        <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">...</div>
                            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">...</div>
                            <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
