@extends('layouts.app')

@section('title', 'Expired Customers')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h3 class="card-title">
                Expired Customers
            </h3>

            <div class="card-tools">
                <span class="badge badge-danger">
                    {{ $customersExpired->total() }}
                </span>
            </div>

        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-sm table-striped table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Plan</th>
                        <th>Expire Date</th>
                        <th>Status</th>

                        {{-- VIEW BUTTON --}}
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($customersExpired as $customer)

                    <tr>

                        <td>{{ $customer->id }}</td>

                        <td>
                            {{ $customer->full_name ?? $customer->name }}
                        </td>

                        <td>
                            {{ $customer->username }}
                        </td>

                        <td>
                            {{ $customer->internetPlan?->name ?? '-' }}
                        </td>

                        <td>
                            {{ $customer->expire_date }}
                        </td>

                        <td>
                            <span class="badge badge-danger">
                                Expired
                            </span>
                        </td>

                        {{-- VIEW BUTTON --}}
                        <td>
                            <a href="{{ url('/customers/'.$customer->id) }}"
                               class="btn btn-sm btn-primary">
                                View
                            </a>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center">
                            No expired customers found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer clearfix">
            {{ $customersExpired->links() }}
        </div>

    </div>

</div>

@endsection
