@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Branch') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create roles')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('branch.create') }}"></i> Create </a>
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
                                        <th>Name</th>
                                        <th>Address</th>
                                        <th>Contact Number</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($branches as $branch)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$branch->name}}</td>
                                            <td>{{$branch->address}}</td>
                                            <td>{{$branch->contact_number}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit roles')
                                                        <a href="{{route('branch.edit', $branch->id)}}" class="btn btn-sm btn-warning">Edit</a>
                                                    @endcan
                                                    @can('delete roles')
                                                        <form action="{{route('branch.destroy', $branch->id)}}" method="post">
                                                            @method('Delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this branch ?')">Delete</button>
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
                    {{$branches->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
