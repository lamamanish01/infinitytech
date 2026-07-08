@extends('layouts.app')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">List of Permissions</h1>
            </div>

            <div class="col-sm-6 text-right">

                @can('create permissions')
                    <a class="btn btn-primary"
                       href="{{ route('permissions.create') }}">
                        Create Permission
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
                        <h3 class="card-title">Permissions</h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-sm table-striped table-hover text-nowrap">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Created</th>
                                    <th width="120">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($permissions as $permission)

                                    <tr>

                                        <td>{{ $loop->iteration }}</td>

                                        <td>{{ $permission->name }}</td>

                                        <td>
                                            {{ optional($permission->created_at)->format('d M, Y') ?? 'N/A' }}
                                        </td>

                                        <td>

                                            <div class="btn-group btn-group-sm">

                                                <a href="{{ route('permissions.edit', $permission->id) }}"
                                                   class="btn btn-secondary btn-sm">
                                                    Edit
                                                </a>

                                                <form action="{{ route('permissions.destroy', $permission->id) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Delete this permission?')">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-danger btn-sm">
                                                        Delete
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">
                                            No Permissions Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $permissions->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
