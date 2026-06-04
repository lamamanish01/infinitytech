@extends('layouts.app')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">List of Users</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('create users')
                    <a class="btn btn-primary" href="{{ route('users.create') }}">
                        Create User
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

                    <div class="card-header">
                        <h3 class="card-title">Users</h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Branch</th>
                                    <th>Roles</th>
                                    <th>Created</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($users as $user)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $user->name }}</td>

                                        <td>{{ $user->email }}</td>

                                        {{-- BRANCH --}}
                                        <td>
                                            {{ $user->branch->name ?? 'N/A' }}
                                        </td>

                                        {{-- ROLES --}}
                                        <td>
                                            @forelse($user->roles as $role)
                                                <span class="badge badge-primary">
                                                    {{ $role->name }}
                                                </span>
                                            @empty
                                                <span class="text-muted">No Role</span>
                                            @endforelse
                                        </td>

                                        {{-- CREATED --}}
                                        <td>
                                            {{ optional($user->created_at)->format('d M, Y') }}
                                        </td>

                                        {{-- ACTIONS --}}
                                        <td>
                                            <div class="btn-group">

                                                @can('edit users')
                                                    <a href="{{ route('users.edit', $user->id) }}"
                                                       class="btn btn-sm btn-secondary">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('delete users')
                                                    <form action="{{ route('users.destroy', $user->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Delete this user?')">

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
                                        <td colspan="7" class="text-center text-muted">
                                            No Users Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="mt-3">
                    {{ $users->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
