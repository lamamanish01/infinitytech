@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h3>{{$customer->name}}</h3>
                        @if($customer->is_online)
                            <span class="badge bg-success">ONLINE</span>
                        @else
                            <span class="badge bg-danger">OFFLINE</span>
                        @endif

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
                                    <span class="badge badge-primary badge-pill">{{$customer->internetPlan->bandwidth_name}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Status :
                                    <span class="badge {{ $customer->status == 'expired' ? 'badge-danger' : 'badge-primary' }} badge-pill">{{$customer->status}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    @php
                                        $grace = $customer->activeGrace();
                                    @endphp
                                    Grace:
                                    <span class="badge badge-primary badge-pill">{{ $grace ? $grace->grace_days.' days' : 'Not given' }}</span>
                                </li>
                                    @if($grace)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Grace Start:
                                        <span class="badge badge-warning badge-pill">{{$grace->grace_start->format('Y-m-d')}}</span>
                                    </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Grace End:
                                        <span class="badge badge-warning badge-pill">{{$grace->grace_end->format('Y-m-d')}}</span>
                                    </li>
                                    @endif

                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Expired :
                                    <span class="badge badge-danger badge-pill">{{$customer->expire_date->format('Y-m-d')}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    Registered By :
                                    <span class="badge badge-success badge-pill">{{$customer->user->name}}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-12">
                            <a class="btn btn-warning" href="{{ route('recharges.create', $customer->id) }}"></i> Recharge Customer </a>
                            <a class="btn btn-danger" href="{{ route('customers.expiry-form', $customer->id) }}"></i> Change Expiry Date </a>

                            <form action="{{ route('customer.disconnect', $customer->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-warning btn-sm">Disconnect</button>
                            </form>

                            <form action="{{ route('customer.forceDisconnect', $customer->id) }}" method="POST"
                                onsubmit="return confirm('Force disconnect?')">
                                @csrf
                                <button class="btn btn-danger btn-sm">Force Disconnect</button>
                            </form>
                        </div>
                        <form action="{{route('provide-grace', $customer->id)}}" method="POST">
                            @csrf
                            <div class="input-group input-group-sm">
                                <span class="input-group-append">
                                    <button type="submit" class="btn btn-info btn-flat">Provide 3 Days Grace</button>
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
                            @if($customer->active)
                                <tr>
                                    <td>#</td>
                                    <td>{{ $customer->active->username}}</td>
                                    <td>{{ $customer->active->ip_address}}</td>
                                    <td>{{ $customer->active->start_time}}</td>
                                    <td>{{ $customer->active->session_time_human}}</td>
                                    <td>{{ $customer->active->upload_mb}}</td>
                                    <td>{{ $customer->active->download_mb}}</td>
                                    <td>{{ $customer->active->mac_address}}</td>
                                    <td>{{ $customer->active->nas_ip}}</td>
                                    <td>{{ $customer->active->ppp_server}}</td>
                                </tr>
                            @else

                                <div class="alert alert-secondary">
                                    No active session (or inactive for more than 5 minutes)
                                </div>

                            @endif

                            {{-- PREVIOUS SESSION --}}
                            @if($customer->previous)
                                <tr>
                                    <td>#</td>
                                    <td>{{ $customer->previous->username}}</td>
                                    <td>{{ $customer->previous->ip_address}}</td>
                                    <td>{{ $customer->previous->start_time}}</td>
                                    <td>{{ $customer->previous->session_time_human}}</td>
                                    <td>{{ $customer->previous->upload_mb}}</td>
                                    <td>{{ $customer->previous->download_mb}}</td>
                                    <td>{{ $customer->previous->mac_address}}</td>
                                    <td>{{ $customer->previous->nas_ip}}</td>
                                    <td>{{ $customer->previous->ppp_server}}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>




                <div class="tab-pane fade" id="billing" role="tabpanel" aria-labelledby="billing-tab">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Billing No</th>
                                <th>Username</th>
                                <th>Recharge</th>
                                <th>Internet Plan</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($billings as $billing)
                                <tr>
                                    <td>{{$loop->iteration}}</td>
                                    <td>{{$billing->billing_no}}</td>
                                    <td>{{$billing->username}}</td>
                                    <td>{{$billing->expire_date->format('Y-m-d')}}</td>
                                    <td>{{$billing->internetPlan->bandwidth_name}}</td>
                                    <td>{{$billing->amount}}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>




                {{--  <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
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
                </div>  --}}

              </div>
        </div>
    </div>
@endsection

@push('scripts')

@endpush
