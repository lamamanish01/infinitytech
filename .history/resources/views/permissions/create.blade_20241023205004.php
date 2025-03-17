@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">

            <div class="alert alert-info">
                Sample table
            </div>  

            <div class="card">
                <div class="card-body p-0">

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                        {{--  @foreach($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                        @endforeach  --}}
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->

                <div class="card-footer clearfix">
                    {{--  {{ $users->links() }}  --}}
                </div>
            </div>

        </div>
    </div>
    <!-- /.row -->
</div><!-- /.container-fluid -->

@endsection
