@extends('layouts.app')

@section('content')
    <div class="container-fluid">


        <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{$customer->name}}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create customer')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('customers.create') }}"></i> Create </a>
                </div>
            @endcan
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->





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
                        <ul class="nav nav-pills mb-3">
                            <li class="nav-item">
                              <a class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-home" type="button">Info</a>
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
                                <div class="card">
                                    <div class="row">
                                        <div class="col-md-5 mb-3">
                                            <ul class="list-group mb-3">
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Username :
                                                    <span class="badge badge-success badge-pill">{{$customer->username}}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Registered :
                                                    <span class="badge badge-primary badge-pill">
                                                        {{\Carbon\Carbon::parse($customer->registered)->format('d M Y')}}
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Interner Plan :
                                                    <span class="badge badge-primary badge-pill">{{$customer->internetplan->name}}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Grace :
                                                    <span class="badge badge-danger badge-pill">{{$customer->grace ?? 0}}</span>
                                                </li>
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    Registered By :
                                                    <span class="badge badge-danger badge-pill">{{$customer->grace ?? 0}}</span>
                                                </li>
                                            </ul>
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
