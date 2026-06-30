@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Customers</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('create customers')
                    <a class="btn btn-primary" href="{{ route('customers.create') }}">
                        Create Customer
                    </a>
                @endcan

            </div>
        </div>

    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Plan</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Address</th>
                                    <th>Expiry Date</th>
                                    <th>Status</th>
                                    <th width="160">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($customers as $customer)

                                    @php
                                        $expire = $customer->expire_date
                                            ? \Carbon\Carbon::parse($customer->expire_date)
                                            : null;

                                        $isExpired = $expire && $expire->isPast();
                                    @endphp

                                    <tr>

                                        <td>{{ $customers->firstItem() + $loop->index }}</td>
                                        <td>
                                            <a href="{{route('customers.show', $customer->id)}}">{{ $customer->username }}</a>
                                        </td>
                                        <td>{{ $customer->internetPlan->bandwidth_name ?? 'N/A' }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->contact_number }}</td>
                                        <td>{{ $customer->address }}</td>
                                        <td>{{ $customer->expire_date->timezone('Asia/Kathmandu')->format('Y-m-d') }}</td>
                                        <td>
                                            @php
                                                $status = $customer->status;
                                            @endphp

                                            <span class="badge
                                                @if($status == 'active') bg-success
                                                @elseif($status == 'grace') bg-warning text-dark
                                                @else bg-danger
                                                @endif">
                                                {{ strtoupper($status) }}
                                            </span>
                                        </td>

                                        <td>

                                            <div class="btn-group">

                                                <a href="{{ route('customers.show', $customer->id) }}"
                                                   class="btn btn-sm btn-secondary">
                                                    Show
                                                </a>

                                                @can('edit customers')
                                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                                       class="btn btn-sm btn-warning">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('delete customers')
                                                    <form action="{{ route('customers.destroy', $customer->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Delete this customer?')"
                                                          style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="btn btn-sm btn-danger">
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endcan

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            No Customers Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="mt-3">
                    {{ $customers->links() }}
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
