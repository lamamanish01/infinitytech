@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ __('List of Bandwidth') }}</h1>
            </div>

            @can('create bandwidth')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('bandwidth.create') }}">
                        Create
                    </a>
                </div>
            @endcan
        </div>
    </div>
</div>

<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="card card-info">
                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Upload Rate</th>
                                    <th>Download Rate</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($bandwidths as $bandwidth)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $bandwidth->name }}</td>
                                        <td>{{ $bandwidth->upload_rate }}</td>
                                        <td>{{ $bandwidth->download_rate }}</td>

                                        <td>
                                            <div class="btn-group">

                                                @can('edit bandwidth')
                                                    <a href="{{ route('bandwidth.edit', $bandwidth->id) }}"
                                                       class="btn btn-sm btn-secondary">
                                                        Edit
                                                    </a>
                                                @endcan

                                                @can('delete bandwidth')
                                                    <form action="{{ route('bandwidth.destroy', $bandwidth->id) }}"
                                                          method="POST"
                                                          onsubmit="return confirm('Do you want to delete this bandwidth?')">

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
                                        <td colspan="5" class="text-center">
                                            No Data Found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>

                <div class="mt-3">
                    {{ $bandwidths->links() }}
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
