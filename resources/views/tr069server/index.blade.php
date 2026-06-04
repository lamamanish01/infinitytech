@extends('layouts.app')

@section('content')

<div class="card mb-4">

    <div class="card-header">
        <h5>Servers</h5>
    </div>

    <div class="card-body table-responsive">

        <table class="table table-sm table-bordered">

            <thead>
                <tr>
                    <th>SN</th>
                    <th>IP</th>
                    <th>Web Port</th>
                    <th>API Port</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse ($tr069servers as $tr069server)

                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $tr069server->ip }}</td>

                        {{-- FIXED TYPO HERE --}}
                        <td>{{ $tr069server->web_port }}</td>

                        <td>{{ $tr069server->api_port }}</td>

                        <td>
                            <div class="btn-group">

                                @can('acsserver edit')
                                    <a href="{{ route('server.edit', $tr069server->id) }}"
                                       class="btn btn-sm btn-warning">
                                        Edit
                                    </a>
                                @endcan

                                @can('acsserver delete')
                                    <form action="{{ route('server.destroy', $tr069server->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Delete this server?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Delete
                                        </button>

                                    </form>
                                @endcan

                            </div>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            No TR069 servers found
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
