@extends('layouts.app')

@section('content')

    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('List of InternetPlan') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
            @can('create roles')
                <div class="col-md-12 text-right">
                    <a class="btn btn-primary" href="{{ route('internetplan.create') }}"></i> Create </a>
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
                                        <th>Bandwidth Name</th>
                                        <th>Price</th>
                                        <th>Duration</th>
                                        <th>Rate Limit</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($internetplans as $internetplan)
                                        <tr>
                                            <td>{{$loop->iteration}}</td>
                                            <td>{{$internetplan->name}}</td>
                                            <td>{{$internetplan->bandwidth_name}}</td>
                                            <td>{{$internetplan->price}}</td>
                                            <td>{{$internetplan->duration}} {{$internetplan->type}}</td>
                                            <td>{{$internetplan->rate_limit}}</td>
                                            <td>
                                                <div class="btn-group">
                                                    @can('edit roles')
                                                        <a href="{{route('internetplan.edit', $internetplan->id)}}" class="btn btn-sm btn-secondary">Edit</a>
                                                    @endcan
                                                    @can('delete roles')
                                                        <form action="{{route('internetplan.destroy', $internetplan->id)}}" method="post">
                                                            @method('Delete')
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-danger" onclick="alert('Do you want to delete this internet plan ?')">Delete</button>
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
                    {{$internetplans->links()}}
                </div>
            </div>
        </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->

@endsection
