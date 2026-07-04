@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Billing Records</h4>
            <small class="text-muted">
                Manage all customer invoices and payments
            </small>
        </div>

        {{--  <div>
            @can('create billing')
                <a href="{{ route('billings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Billing
                </a>
            @endcan
        </div>  --}}

    </div>

    {{-- SEARCH / FILTER FORM --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form action="{{ route('billing.index') }}" method="GET" class="row g-3">

                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Billing No, Customer, Invoice..."
                           value="{{ request('search') }}">
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary w-100">
                        Reset
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- BILLINGS TABLE --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Billing No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($billings as $billing)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-bold">{{ $billing->billing_no }}</span>
                                </td>
                                <td> {{ $billing->customer->name ?? 'N/A' }} </td>
                                <td>
                                    {{ $billing->billing_date ? \Carbon\Carbon::parse($billing->billing_date)->format('Y-m-d') : '-' }}
                                </td>
                                <td>
                                    <span class="fw-bold">{{ number_format($billing->amount, 2) }}</span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'paid'      => 'success',
                                            'unpaid'    => 'danger',
                                            'partial'   => 'warning',
                                            'cancelled' => 'secondary',
                                        ];
                                        $color = $statusColors[$billing->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ ucfirst($billing->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        {{-- VIEW --}}
                                        <a href="{{ route('billing.show', $billing) }}"
                                           class="btn btn-sm btn-primary">
                                            View
                                        </a>
                                        {{-- EDIT --}}
                                        <a href="{{ route('billing.edit', $billing) }}"
                                           class="btn btn-sm btn-warning">
                                            Edit
                                        </a>
                                        {{-- DELETE --}}
                                        <form action="{{ route('billing.destroy', $billing) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this billing record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    No billing records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- NEW: TOTAL SUM FOOTER --}}
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total (current page)</td>
                            <td>{{ number_format($billings->sum('amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>

                </table>

            </div>

        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $billings->appends(request()->query())->links() }}
    </div>

</div>

@endsection
