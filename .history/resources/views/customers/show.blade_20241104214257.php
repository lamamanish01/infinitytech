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

                        <ul class="nav nav-pills" id="pills-tab" role="tablist">
                            <li class="nav-item">
                              <a class="nav-link active" data-bs-toggle="tab" data-bs-target="#pills-home">Home</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" data-bs-toggle="tab" data-bs-target="#pills-profile">Profile</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" id="pills-contact-tab" data-bs-toggle="tab" data-bs-target="#pills-contact">Contact</a>
                            </li>
                          </ul>
                          <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-home">home</div>
                            <div class="tab-pane fade" id="pills-profile">profile</div>
                            <div class="tab-pane fade" id="pills-contact">contact</div>
                          </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
