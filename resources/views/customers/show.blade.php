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
                                    <span class="badge badge-primary badge-pill">{{$customer->internetplan}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Grace :
                                    <span class="badge badge-danger badge-pill">{{$customer->grace ?? 0}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Expired :
                                    <span class="badge badge-primary badge-pill">{{$customer->latestRecharge->expire_date ?? 'Expire Date Not Found.'}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Registered By :
                                    <span class="badge badge-success badge-pill">{{$customer->user->name}}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <a class="btn btn-primary" href="{{ route('recharges.create', $customer->id) }}"></i> Recharge Customer </a>
                        </div>
                        <form action="{{route('provide-grace', $customer->id)}}" method="POST">
                            @csrf
                            <label for="">Number of days</label>
                            <div class="input-group input-group-sm">
                                <input type="number" min="0" class="form-control" name="grace_days">
                                <span class="input-group-append">
                                <button type="submit" class="btn btn-info btn-flat">Provide Grace</button>
                                </span>
                            </div>
                        </form>
                    </div>
                </div>



                <div class="tab-pane fade" id="session" role="tabpanel" aria-labelledby="session-tab">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>IP Address</th>
                                <th>Start Time</th>
                                <th>Session Time</th>
                                <th>Upload</th>
                                <th>Download</th>
                                <th>MAC</th>
                                <th>NAS IP</th>
                                <th>Server</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($activeSessions as $activeSession)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$activeSession->username}}</td>
                                    <td>{{$activeSession->ip_address}}</td>
                                    <td>{{$activeSession->start_time}}</td>
                                    <td>{{$activeSession->formatted_time}}</td>
                                    <td>{{$activeSession->upload_mb. ' '. 'MB'}}</td>
                                    <td>{{$activeSession->download_mb. ' '. 'MB'}}</td>
                                    <td>{{$activeSession->mac_address}}</td>
                                    <td>{{$activeSession->nas_ip}}</td>
                                    <td>{{$activeSession->ppp_server}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>




                <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Customer</th>
                                <th>Recharage</th>
                                <th>Internet Plan</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billings as $billing)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$billing->username}}</td>
                                    <td>{{$customer->expire_date}}</td>
                                    <td>{{$customer->internetplan}}</td>
                                    <td>{{$billing->amount}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>




                <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Reply</th>
                                <th>Reply Message</th>
                                <th>Auth Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($authLogs as $authLog)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$authLog->username}}</td>
                                    <td>{{$authLog->reply}}</td>
                                    <td>{{$authLog->reply_message}}</td>
                                    <td>{{$authLog->authdate}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

              </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
