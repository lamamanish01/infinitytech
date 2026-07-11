@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

            <div>
                <h4 class="mb-0">All Customers</h4>
            </div>

            <div class="d-flex flex-wrap gap-2 align-items-center">

                {{-- SEARCH FORM --}}
                <form action="{{ route('customers.index') }}" method="GET" class="d-flex">
                    <input type="text"
                           name="q"
                           class="form-control form-control-sm"
                           placeholder="Search by username or contact..."
                           value="{{ request('q') }}"
                           style="width: 250px; margin-right: 4px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    @if(request('q'))
                        <a href="{{ route('customers.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    @endif
                </form>

                @can('create customers')
                    <a class="btn btn-sm btn-primary" href="{{ route('customers.create') }}">
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

                    <div class="card-body table-responsive p-0 ">

                        <table class="table table-sm table-striped table-hover text-nowrap">

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
                                        <td
                                            @if($customer->is_online)
                                                style="background-color: lightgreen; color: #333;"
                                            @endif>
                                                <a href="{{ route('customers.show', $customer->id) }}"
                                                    @if($customer->is_online)
                                                        style="color: #333; text-decoration: none;"
                                                    @endif>
                                                    {{ $customer->username }}
                                                </a>
                                        </td>
                                        <td>{{ $customer->internetPlan->bandwidth_name ?? 'N/A' }}</td>
                                        <td>{{ $customer->name }}</td>
                                        <td>
                                            <a href="tel:{{ $customer->contact_number }}">{{ $customer->contact_number }}</a>
                                        </td>
                                        <td>{{ $customer->address }}</td>
                                        <td>{{ $customer->expire_date->timezone('Asia/Kathmandu')->format('Y-m-d') }}</td>
                                        <td>
                                            @php
                                                $isOnline = $customer->is_online ?? false;

                                                if ($isOnline) {
                                                    $displayText = 'ONLINE';
                                                    $badgeClass = 'bg-success';
                                                } else {
                                                    $status = $customer->status;
                                                    $displayText = strtoupper($status);

                                                    if ($status == 'active') {
                                                        $badgeClass = 'bg-primary';
                                                    } elseif ($status == 'grace') {
                                                        $badgeClass = 'bg-warning text-dark';
                                                    } else {
                                                        $badgeClass = 'bg-danger';
                                                    }
                                                }
                                            @endphp

                                            <span class="badge {{ $badgeClass }}">
                                                {{ $displayText }}
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

                        {{-- PAGINATION – preserves search query --}}
                        <div class="mt-3">
                            {{ $customers->appends(request()->query())->links() }}
                        </div>

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

@endsection
