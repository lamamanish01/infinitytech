@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Mikrotik Routers</h4>
            <small class="text-muted">Manage router API connections</small>
        </div>

        @can('create mikrotik')
            <a href="{{ route('mikrotik.create') }}" class="btn btn-primary">
                + Add Mikrotik
            </a>
        @endcan

    </div>

    {{-- TABLE --}}
    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-striped table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Username</th>
                            <th>Password</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($mikrotiks as $mikrotik)

                            <tr>

                                <td>{{ $loop->iteration }}</td>

                                <td class="fw-bold">
                                    {{ $mikrotik->name }}
                                </td>

                                <td>
                                    {{ $mikrotik->host }}
                                </td>

                                <td>
                                    <span class="badge bg-info">
                                        {{ $mikrotik->port }}
                                    </span>
                                </td>

                                <td>
                                    {{ $mikrotik->username }}
                                </td>

                                <td>
                                    <span class="text-muted">
                                        ••••••••
                                    </span>
                                </td>

                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        @can('edit mikrotik')
                                            <a href="{{ route('mikrotik.edit', $mikrotik->id) }}"
                                               class="btn btn-primary btn-sm">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete mikrotik')
                                            <form action="{{ route('mikrotik.destroy', $mikrotik->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this Mikrotik?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>

                                            </form>
                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No Mikrotik Found
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
        {{ $mikrotiks->links() }}
    </div>

</div>

@endsection
