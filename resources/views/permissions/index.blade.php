@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Permission') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create permissions')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('permissions.create') }}"></i> Create </a>
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
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($permissions as $permission)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$permission->name}}</td>
                                            <td>{{\Carbon\Carbon::parse($permission->created_at)->format('d M, Y')}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="{{route('permissions.edit', $permission->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    <form action="{{route('permissions.destroy', $permission->id)}}" method="post">
                                                        @method('Delete')
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger" >Delete</button>
                                                    </form>
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
                    {{$permissions->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
