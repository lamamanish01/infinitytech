@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3>{{$customer->name}}</h3>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <div class="card p-3">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <a class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Home</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact" type="button" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
                </li>
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <ul class="list-group mt-3 mb-3">
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
                                    Ex :
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



                <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">...</div>
                <div class="tab-pane fade" id="contact" role="tabpanel" aria-labelledby="contact-tab">...</div>

              </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
