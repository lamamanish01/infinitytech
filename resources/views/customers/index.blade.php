@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Customers</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('create customer')

                    <a class="btn btn-primary"
                       href="{{ route('customers.create') }}">

                        Create Customer

                    </a>

                @endcan

            </div>

        </div>

    </div>

</div>

<!-- Main Content -->
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
                                        $expire = $customer->expire_date;
                                        $isExpired = $expire && $expire->isPast();
                                    @endphp

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $customer->username }}</td>

                                        {{-- PLAN --}}
                                        <td>
                                            {{ $customer->internetPlan->bandwidth_name ?? 'N/A' }}
                                        </td>

                                        <td>{{ $customer->name }}</td>

                                        <td>{{ $customer->contact_number }}</td>

                                        {{-- EXPIRY --}}
                                        <td>
                                            @if($expire)
                                                {{ $expire->format('Y-m-d') }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>

                                        {{-- STATUS --}}
                                        <td>

                                            @if(!$expire)
                                                <span class="badge badge-secondary">
                                                    Unknown
                                                </span>

                                            @elseif($isExpired)
                                                <span class="badge badge-danger">
                                                    Expired
                                                </span>

                                            @else
                                                <span class="badge badge-success">
                                                    Active
                                                </span>
                                            @endif

                                        </td>

                                        {{-- ACTION --}}
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
                                                          onsubmit="return confirm('Delete this customer?')">

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

                                        <td colspan="8"
                                            class="text-center text-muted">

                                            No Customers Found

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $customers->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
