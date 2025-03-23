@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Users') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create users')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('users.create') }}"></i> Create </a>
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
                                        <th>Email</th>
                                        <th>Branch</th>
                                        <th>Roles</th>
                                        <th>Created</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$user->name}}</td>
                                            <td>{{$user->email}}</td>
                                            <td>{{$user->branch->name ?? ''}}</td>
                                            <td>
                                                <span class="badge badge-primary">
                                                    {{$user->roles->pluck('name')->implode(', ')}}
                                                </span>
                                            </td>
                                            <td>{{\Carbon\Carbon::parse($user->created_at)->format('d M, Y')}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit users')
                                                        <a href="{{route('users.edit', $user->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    @endcan
                                                    @can('delete users')
                                                        <form action="{{route('users.destroy', $user->id)}}" method="post">
                                                            @method('Delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this users ?')">Delete</button>
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
                    {{$users->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
