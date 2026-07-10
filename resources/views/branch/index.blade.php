@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

            <div>
                <h4 class="mb-0">List of Branches</h4>
            </div>

            <div>
                @can('create branch')
                    <a class="btn btn-sm btn-primary" href="{{ route('branch.create') }}">
                        Create Branch
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
                        <h3 class="card-title">Branches</h3>
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-sm table-striped text-center table-hover text-nowrap">

                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Contact Number</th>
                                    <th>Balance</th>
                                    <th width="180">Action</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($branches as $branch)

                                    <tr>

                                        <td>
                                            {{ $branches->firstItem() + $loop->index }}
                                        </td>

                                        <td>{{ $branch->name }}</td>

                                        <td>{{ $branch->address }}</td>

                                        <td>{{ $branch->contact_number }}</td>

                                        <td>
                                            <span class="badge badge-success">
                                                Rs. {{ number_format($branch->balance, 2) }}
                                            </span>
                                        </td>

                                        <td>

                                            <div class="btn-group btn-group-sm">

                                                @can('view branch')
                                                    <a href="{{ route('branch.show', $branch->id) }}"
                                                       class="btn btn-sm btn-primary">
                                                        Show
                                                    </a>
                                                @endcan

                                                @can('edit branch')
                                                    <a href="{{ route('branch.edit', $branch->id) }}"
                                                       class="btn btn-sm btn-warning">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('delete branch')
                                                    <form action="{{ route('branch.destroy', $branch->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Do you want to delete this branch?')"
                                                          style="display:inline-block;">

                                                        @csrf
                                                        @method('DELETE')

                                                        <button type="submit"
                                                                class="btn tbn btn-sm btn-danger">
                                                            Delete
                                                        </button>

                                                    </form>
                                                @endcan

                                            </div>

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No Branch Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                <div class="mt-3">
                    {{ $branches->links() }}
                </div>

            </div>

        </div>

    </div>
</div>

@endsection
