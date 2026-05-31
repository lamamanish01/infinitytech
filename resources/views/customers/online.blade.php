{{-- resources/views/customers/online.blade.php --}}

@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Online Customers</h4>
            <small class="text-muted">
                Active PPPoE / FreeRADIUS Sessions
            </small>
        </div>

        <div>
            <span class="badge bg-success px-3 py-2">
                {{ $customers->total() }} Online
            </span>
        </div>

    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>
                            <th>#</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Session Time</th>
                            <th>Upload</th>
                            <th>Download</th>
                            <th>NAS</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        @forelse($customers as $customer)

                            @php
                                $session = $customer->activeSession;
                            @endphp

                            <tr>

                                {{-- ID --}}
                                <td class="text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- CUSTOMER --}}
                                <td>

                                    <div class="fw-bold">
                                        {{ $customer->name }}
                                    </div>

                                    <small class="text-muted">
                                        {{ $customer->username }}
                                    </small>

                                </td>

                                {{-- PACKAGE --}}
                                <td>

                                    <span class="badge bg-info text-dark">
                                        {{ $customer->internetPlan->bandwidth_name ?? '-' }}
                                    </span>

                                </td>

                                {{-- IP --}}
                                <td>

                                    <span class="fw-bold">
                                        {{ $session->framedipaddress ?? '-' }}
                                    </span>

                                </td>

                                {{-- MAC --}}
                                <td>

                                    <small>
                                        {{ $session->callingstationid ?? '-' }}
                                    </small>

                                </td>

                                {{-- SESSION --}}
                                <td>

                                    <span class="badge bg-secondary">
                                        {{ $session->session_time_human ?? '-' }}
                                    </span>

                                </td>

                                {{-- UPLOAD --}}
                                <td>

                                    <span class="text-success">
                                        {{ $session->upload_mb ?? 0 }} MB
                                    </span>

                                </td>

                                {{-- DOWNLOAD --}}
                                <td>

                                    <span class="text-primary">
                                        {{ $session->download_mb ?? 0 }} MB
                                    </span>

                                </td>

                                {{-- NAS --}}
                                <td>

                                    {{ $session->nasipaddress ?? '-' }}

                                </td>

                                {{-- STATUS --}}
                                <td>

                                    <span class="badge bg-success">
                                        ONLINE
                                    </span>

                                </td>

                                {{-- ACTION --}}
                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        {{-- VIEW --}}
                                        <a href="{{ route('customers.show', $customer) }}"
                                           class="btn btn-sm btn-primary">
                                            View
                                        </a>

                                        {{-- DISCONNECT --}}
                                        <form action="{{ route('customer.disconnect', $customer->id) }}"
                                              method="POST">

                                            @csrf

                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Disconnect this customer?')">

                                                Disconnect

                                            </button>

                                        </form>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="11"
                                    class="text-center py-5 text-muted">

                                    No Online Customers Found

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 d-flex justify-content-end">

        {{ $customers->links() }}

    </div>

</div>

@endsection
