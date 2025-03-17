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
                  <a class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info" type="button" role="tab" aria-controls="info" aria-selected="true">Customer Info</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="session-tab" data-bs-toggle="tab" data-bs-target="#session" type="button" role="tab" aria-controls="session" aria-selected="false">Session</a>
                </li>
                <li class="nav-item" role="presentation">
                  <a class="nav-link" id="billing-tab" data-bs-toggle="tab" data-bs-target="#billing" type="button" role="tab" aria-controls="billing" aria-selected="false">Billing</a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab" aria-controls="logs" aria-selected="false">Activity Logs</a>
                </li>
              </ul>
              <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="home-tab">

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
                                    Expired :
                                    <span class="badge badge-danger badge-pill">{{$customer->exipred ?? 0}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Registered By :
                                    <span class="badge badge-danger badge-pill">{{$customer->grace ?? 0}}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>



                <div class="tab-pane fade" id="session" role="tabpanel" aria-labelledby="session-tab">...</div>



                <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                    <div class="content">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="card card-info">
                                        {{--  <div class="card-header">
                                            <h3 class="card-title">Color &amp; Time Picker</h3>
                                        </div>  --}}

                                        <div class="card-body table-responsive p-0">
                                            <table class="table table-hover text-nowrap">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Username</th>
                                                        <th>Internet Plan</th>
                                                        <th>Full Name</th>
                                                        <th>Address</th>
                                                        <th>Contact</th>
                                                        <th>Expire</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{--  @forelse($customers as $customer)
                                                        <tr>
                                                            <td>{{$loop->iteration}}</td>
                                                            <td>{{$customer->username}}</td>
                                                            <td>{{$customer->internetplan->name}}</td>
                                                            <td>{{$customer->name}}</td>
                                                            <td>{{$customer->address}}</td>
                                                            <td>{{$customer->contact_number}}</td>
                                                            <td>{{''}}</td>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <a href="{{route('customers.show', $customer->id)}}" class="btn btn-sm btn-secondary">Show</a>
                                                                    @can('edit customers')
                                                                        <a href="{{route('customers.edit', $customer->id)}}" class="btn btn-sm btn-warning">Edit</a>
                                                                    @endcan
                                                                    @can('delete customers')
                                                                        <form action="{{route('customers.destroy', $customer->id)}}" method="post">
                                                                            @method('Delete')
                                                                            @csrf
                                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this Customer ?')">Delete</button>
                                                                        </form>
                                                                    @endcan
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4">No Data Found</td>
                                                        </tr>
                                                    @endforelse  --}}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    {{--  {{$customers->links()}}  --}}
                                </div>
                            </div>
                        </div>
                            <!-- /.row -->
                        </div><!-- /.container-fluid -->
                    </div>
                </div>



                <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">...</div>

              </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
