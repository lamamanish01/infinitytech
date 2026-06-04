@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Customers</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('customers.create')
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
                                    <th>Expire</th>
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

                                        <td>{{ $customer->username }}</td>

                                        <td>{{ $customer->internetPlan->bandwidth_name ?? 'N/A' }}</td>

                                        <td>{{ $customer->name }}</td>

                                        <td>{{ $customer->contact_number }}</td>

                                        <td>
                                            @if($expire)
                                                {{ $expire->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if(!$expire)
                                                <span class="badge badge-secondary">Unknown</span>

                                            @elseif($isExpired)
                                                <span class="badge badge-danger">Expired</span>

                                            @else
                                                <span class="badge badge-success">Active</span>
                                            @endif
                                        </td>

                                        <td>

                                            <div class="btn-group">

                                                <a href="{{ route('customers.show', $customer->id) }}"
                                                   class="btn btn-sm btn-secondary">
                                                    Show
                                                </a>

                                                @can('customers.edit')
                                                    <a href="{{ route('customers.edit', $customer->id) }}"
                                                       class="btn btn-sm btn-warning">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('customers.delete')
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
