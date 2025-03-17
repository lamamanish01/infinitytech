@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of NAS') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create nas')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('nas.create') }}"></i> Create </a>
                </div>
            @endcan
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card card-info">
                        {{--  <div class="card-header">
                            <h3 class="card-title">Color &amp; Time Picker</h3>
                        </div>  --}}

                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Short Name</th>
                                        <th>IP Address</th>
                                        <th>Secret</th>
                                        <th>Type</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($nases as $nas)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$nas->shortname}}</td>
                                            <td>{{$nas->nasname}}</td>
                                            <td>{{$nas->secret}}</td>
                                            <td>{{$nas->type}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit nas')
                                                        <a href="{{route('nas.edit', $nas->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    @endcan
                                                    @can('delete nas')
                                                        <form action="{{route('nas.destroy', $nas->id)}}" method="post">
                                                            @method('Delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this NAS ?')">Delete</button>
                                                        </form>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">No Data Found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{$nases->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
