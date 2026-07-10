@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">NAS Devices</h4>
            <small class="text-muted">Manage Radius NAS servers</small>
        </div>

        @can('create nas')
            <a href="{{ route('nas.create') }}"
               class="btn btn-primary btn-sm">
                + Add NAS
            </a>
        @endcan

    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-hover text-center text-nowrap">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Short Name</th>
                            <th>IP Address</th>
                            <th>Secret</th>
                            <th>Type</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($nases as $nas)

                            <tr>

                                <td class="text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <strong>{{ $nas->shortname }}</strong>
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $nas->nasname }}
                                    </span>
                                </td>

                                <td>
                                    <span class="text-muted">
                                        ••••••••
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ ucfirst($nas->type) ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        @can('edit nas')
                                            <a href="{{ route('nas.edit', $nas->id) }}"
                                               class="btn btn-primary btn-sm">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete nas')
                                            <form action="{{ route('nas.destroy', $nas->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this NAS?')">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>

                                            </form>
                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No NAS Devices Found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $nases->links() }}
    </div>

</div>

@endsection
