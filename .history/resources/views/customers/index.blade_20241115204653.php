@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Customers') }}</h1>
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

    <!-- Main content -->
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
                                    @forelse($customers as $customer)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>

                                            <td>
                                                @if ($customer->latestRecharge)
                                                    @if ($customer->latestRecharge->isExpired())
                                                        <div class="btn btn-sm btn-danger">
                                                            {{$customer->username}}
                                                        </div>
                                                    @elseif ($customer->latestRecharge->isWithinGracePeriod())
                                                        <div class="btn btn-sm btn-warning">
                                                            {{$customer->username}}
                                                        </div>
                                                    @elseif ($customer->latestRecharge->expire_date)
                                                        <div class="btn btn-sm btn-success">
                                                            {{$customer->username}}
                                                        </div>
                                                    @endif
                                                @else
                                                    {{$customer->username}}
                                                @endif
                                            </td>
                                            <td>{{$customer->internetplan}}</td>
                                            <td>{{$customer->name}}</td>
                                            <td>{{$customer->address}}</td>
                                            <td>{{$customer->contact_number}}</td>
                                            <td>
                                                @if ($customer->latestRecharge)
                                                    @if ($customer->latestRecharge->isExpired())
                                                        <div class="btn btn-sm btn-danger">
                                                            {{\Carbon\Carbon::parse($customer->latestRecharge->expire_date)->format('d-m-Y')}}
                                                        </div>
                                                    @elseif ($customer->latestRecharge->isWithinGracePeriod())
                                                        <div class="btn btn-sm btn-warning">
                                                            {{\Carbon\Carbon::parse($customer->latestRecharge->expire_date)->format('d-m-Y')}}
                                                        </div>
                                                    @else
                                                        {{\Carbon\Carbon::parse($customer->latestRecharge->expire_date)->format('d-m-Y')}}
                                                    @endif
                                                @else
                                                    <p>No recharge record found.</p>
                                                @endif
                                            </td>
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
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{$customers->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
