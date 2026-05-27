@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">List of Roles</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('create roles')

                    <a class="btn btn-primary"
                       href="{{ route('roles.create') }}">

                        Create Role

                    </a>

                @endcan

            </div>

        </div>

    </div>

</div>

<!-- Main Content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-header">
                        <h3 class="card-title">
                            Roles
                        </h3>
                    </div>

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

                                    <tr>

                                        <td>
                                            {{ $loop->iteration }}
                                        </td>

                                        <td>
                                            {{ $role->name }}
                                        </td>

                                        {{-- PERMISSIONS (CLEAN VERSION) --}}
                                        <td>

                                            @php
                                                $permissions = $role->permissions;
                                                $visible = $permissions->take(3);
                                                $remaining = $permissions->count() - 3;
                                            @endphp

                                            {{-- FIRST 3 PERMISSIONS --}}
                                            @foreach($visible as $permission)

                                                <span class="badge badge-info mb-1">
                                                    {{ $permission->name }}
                                                </span>

                                            @endforeach

                                            {{-- +MORE BADGE --}}
                                            @if($remaining > 0)

                                                <span class="badge badge-secondary mb-1">

                                                    +{{ $remaining }} more

                                                </span>

                                            @endif

                                            {{-- EMPTY STATE --}}
                                            @if($permissions->count() == 0)

                                                <span class="text-muted">
                                                    No Permissions
                                                </span>

                                            @endif

                                        </td>

                                        {{-- CREATED --}}
                                        <td>
                                            {{ $role->created_at->format('d M, Y') }}
                                        </td>

                                        {{-- ACTION --}}
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
                                                          onsubmit="return confirm('Are you sure you want to delete this role?')">

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

                                        <td colspan="5"
                                            class="text-center text-muted">

                                            No Roles Found

                                        </td>

                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $roles->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
