@extends('layouts.app')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <h4 class="mb-0">Discontinued Customers</h4>
            <div>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> All Customers
                </a>
            </div>
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-user-slash text-danger mr-2"></i>Discontinued List</h5>
                        <span class="badge badge-secondary">{{ $customers->total() }} total</span>
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-sm table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Name</th>
                                    <th>Plan</th>
                                    <th>Contact</th>
                                    <th>Expiry</th>
                                    <th>Discontinued At</th>
                                    <th width="150">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($customers as $index => $customer)
                                    <tr>
                                        <td>{{ $customers->firstItem() + $index }}</td>
                                        <td>
                                            <a href="{{ route('customers.show', $customer->id) }}">
                                                {{ $customer->username }}
                                            </a>
                                        </td>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->internetPlan->bandwidth_name ?? 'N/A' }}</td>
                                        <td>{{ $customer->contact_number }}</td>
                                        <td>{{ optional($customer->expire_date)->format('Y-m-d') }}</td>
                                        <td>
                                            {{ $customer->updated_at ? $customer->updated_at->format('Y-m-d H:i') : 'N/A' }}
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('customers.show', $customer->id) }}" class="btn btn-sm btn-secondary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @can('continue customers')
                                                    <form action="{{ route('customer.continue', $customer->id) }}" method="POST"
                                                          onsubmit="return confirm('Reactivate this customer?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            <i class="fas fa-play"></i> Continue
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-user-slash fa-2x d-block mb-2"></i>
                                            No discontinued customers found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        {{ $customers->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
