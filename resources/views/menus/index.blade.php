@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of Menus') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create roles')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('menus.create') }}"></i> Create </a>
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
                                        <th>Title</th>
                                        <th>URL</th>
                                        <th>Icon</th>
                                        <th>Parent Name</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menus as $menu)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$menu->title}}</td>
                                            <td>{{$menu->url}}</td>
                                            <td>{{$menu->icon}}</td>
                                            <td>
                                                @if ($menu->parent_id)
                                                    {{$menu->parent->title}}
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit roles')
                                                        <a href="{{route('menus.edit', $menu->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    @endcan
                                                    @can('delete roles')
                                                        <form action="{{route('menus.destroy', $menu->id)}}" method="post">
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
                    {{$menus->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
