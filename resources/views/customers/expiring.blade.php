@extends('layouts.app')

@section('title', 'Expiring Customers')

@section('content')

<div class="container-fluid">

    <div class="card">

        <div class="card-header">
            <h3 class="card-title">Expiring Customers (Next 3 Days)</h3>

            <div class="card-tools">
                <span class="badge badge-warning">
                    {{ $customersExpiring->total() }}
                </span>
            </div>
        </div>

        <div class="card-body table-responsive p-0">

            <table class="table table-sm text-center table-striped table-hover text-nowrap">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Plan</th>
                        <th>Expire Date</th>
                        <th>Days Left</th>
                        <th>Status</th>

                        {{-- VIEW BUTTON HEADER --}}
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                @forelse($customersExpiring as $customer)

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
                            @php
                                $daysLeft = now()->diffInDays($customer->expire_date, false);
                            @endphp

                            {{ $daysLeft > 0 ? ceil($daysLeft) : 0 }}
                        </td>

                        <td>
                            @if($customer->expire_date <= now()->addDay())
                                <span class="badge badge-danger">Very Soon</span>
                            @else
                                <span class="badge badge-warning">Expiring</span>
                            @endif
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
                        <td colspan="8" class="text-center">
                            No expiring customers found
                        </td>
                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

        <div class="card-footer clearfix">
            {{ $customersExpiring->links() }}
        </div>

    </div>

</div>

@endsection
