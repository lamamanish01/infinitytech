@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Role') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create roles')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('roles.create') }}"></i> Create </a>
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
                                        <th>Permissions</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$role->name}}</td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{$role->permissions->pluck('name')->implode(', ')}}
                                                </span>
                                            </td>
                                            <td>{{\Carbon\Carbon::parse($role->created_at)->format('d M, Y')}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit roles')
                                                        <a href="{{route('roles.edit', $role->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    @endcan
                                                    @can('delete roles')
                                                        <form action="{{route('roles.destroy', $role->id)}}" method="post">
                                                            @method('Delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this role ?')">Delete</button>
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
                    {{$roles->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
