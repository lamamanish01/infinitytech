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
                    {{ $customers->total() }}
                </span>
            </div>

        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-bordered table-hover">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Plan</th>
                        <th>Expire Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($customers as $customer)

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

                    </tr>

                @empty

                    <tr>
                        <td colspan="6" class="text-center">
                            No expired customers found.
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer clearfix">
            {{ $customers->links() }}
        </div>

    </div>

</div>

@endsection
