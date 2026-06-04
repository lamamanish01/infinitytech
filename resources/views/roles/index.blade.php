@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <h4 class="mb-0">List of Roles</h4>

        @can('create roles')
            <a class="btn btn-primary" href="{{ route('roles.create') }}">
                Create Role
            </a>
        @endcan

    </div>

    <!-- TABLE -->
    <div class="card card-info">

        <div class="card-body table-responsive p-0">

            <table class="table table-hover text-nowrap">

                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Permissions</th>
                        <th>Created</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($roles as $role)

                        @php
                            $permissions = $role->permissions ?? collect();
                            $visible = $permissions->take(3);
                            $remaining = max($permissions->count() - 3, 0);
                        @endphp

                        <tr>

                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $role->name }}</td>

                            <td>

                                @forelse($visible as $permission)
                                    <span class="badge badge-info mb-1">
                                        {{ $permission->name }}
                                    </span>
                                @empty
                                    <span class="text-muted">No Permissions</span>
                                @endforelse

                                @if($remaining > 0)
                                    <span class="badge badge-secondary mb-1">
                                        +{{ $remaining }} more
                                    </span>
                                @endif

                            </td>

                            <td>
                                {{ optional($role->created_at)->format('d M, Y') }}
                            </td>

                            <td>

                                <div class="btn-group">

                                    @can('edit roles')
                                        <a href="{{ route('roles.edit', $role->id) }}"
                                           class="btn btn-sm btn-secondary">
                                            Edit
                                        </a>
                                    @endcan

                                    @can('delete roles')
                                        <form action="{{ route('roles.destroy', $role->id) }}"
                                              method="POST"
                                              onsubmit="return confirm('Are you sure?')">

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
                            <td colspan="5" class="text-center text-muted">
                                No Roles Found
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <!-- PAGINATION -->
    <div class="mt-3">
        {{ $roles->links() }}
    </div>

</div>

@endsection
