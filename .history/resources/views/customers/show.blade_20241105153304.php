@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row mb-3">
                            <div class="col">
                              <h3>{{$customer->name}}</h3>
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
                        <ul class="nav nav-pills mt-3 mb-3">
                            <li class="nav-item">
                              <a class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-home" type="button">Home</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button">Profile</a>
                            </li>
                            <li class="nav-item">
                              <a class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button">Contact</a>
                            </li>
                          </ul>
                          <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-home">
                                <div class="row">
                                  <div class="col-md-4">
                                    <div class="card">
                                      <div class="body">
                                        <ul class="list-group mb-3">
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Status :
                                                <span class="badge badge-danger badge-pill">Expired</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Session :
                                                                            <span class="badge badge-info">New</span>
                                                                    </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Grace :
                                                <span class="badge badge-success " title="Grace for this user">No</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Registered :
                                                <span class="badge badge-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="2080-08-05 , मङ्गलबार">2023-11-21</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Register by :
                                                <span class="badge badge-primary">Administrator</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Updated :
                                                <span class="badge badge-primary" data-toggle="tooltip" data-placement="top" title="" data-original-title="2080-08-09 , शनिवार">2023-11-25</span>
                                            </li>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                Update by :
                                                <span class="badge badge-primary">Administrator</span>
                                            </li>
                                        </ul>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            </div>


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
