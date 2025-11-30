@extends('layouts.app')

@section('content')
    <div class="card mb-4">
        <div class="card-header">
            <span style="float: right;">
                {{--  {{link_to_route('tr069server.create', "New Sever", null, ['class' => 'btn btn-sm btn-primary'])}}  --}}
            </span>
            <h5>Servers</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-sm">
                <thead>
                <tr>
                    <th>SN</th>
                    <th>Ip</th>
                    <th>Web Port</th>
                    <th>API Port</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($tr069servers as $tr069server)
                    <tr>
                        <td>{{$loop->iteration}}</td>
                        <td>{{$tr069server->ip}}</td>
                        <td>{{$sertr069serverver->web_port}}</td>
                        <td>{{$tr069server->api_port}}</td>
                        <td>
                            <div class="btn btn-group">
                                @can('acsserver edit')
                                    {!! link_to_route('server.edit', 'Edit', $tr069server->id, ['class' => 'btn btn-sm btn-warning']) !!}
                                @endcan
                                @can('acsserver delete')
                                    {!! Form::open(['route' => ['server.destroy', $tr069server->id], 'method' => 'delete']) !!}
                                    {!! Form::submit('Delete', ['class' => 'btn btn-sm btn-danger']) !!}
                                    {!! Form::close() !!}
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty

                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
