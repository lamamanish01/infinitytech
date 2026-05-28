@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="mb-3">
        <h4>Edit NAS</h4>
        <small class="text-muted">Update network access server details</small>
    </div>

    <!-- CARD -->
    <div class="card shadow-sm">

        <div class="card-body">

            <form action="{{ route('nas.update', $na->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row">

                    <!-- NAME -->
                    <div class="col-md-6 mb-3">
                        <label>NAS Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $na->shortname) }}">
                    </div>

                    <!-- IP -->
                    <div class="col-md-6 mb-3">
                        <label>IP Address</label>
                        <input type="text"
                               name="ipaddress"
                               class="form-control"
                               value="{{ old('ipaddress', $na->nasname) }}">
                    </div>

                    <!-- SECRET -->
                    <div class="col-md-6 mb-3">
                        <label>Secret</label>
                        <input type="text"
                               name="secret"
                               class="form-control"
                               value="{{ old('secret', $na->secret) }}">
                    </div>

                    <!-- PORTS -->
                    <div class="col-md-6 mb-3">
                        <label>Ports</label>
                        <input type="text"
                               name="ports"
                               class="form-control"
                               value="{{ old('ports', $na->ports) }}">
                    </div>

                    <!-- TYPE -->
                    <div class="col-md-6 mb-3">
                        <label>Type</label>
                        <select name="type" class="form-control">

                            <option value="other" {{ $na->type == 'other' ? 'selected' : '' }}>Other</option>
                            <option value="mikrotik" {{ $na->type == 'mikrotik' ? 'selected' : '' }}>Mikrotik</option>
                            <option value="juniper" {{ $na->type == 'juniper' ? 'selected' : '' }}>Juniper</option>

                        </select>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="col-md-12 mb-3">
                        <label>Description</label>
                        <textarea name="description"
                                  class="form-control"
                                  rows="3">{{ old('description', $na->description ?? '') }}</textarea>
                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="d-flex gap-2 mt-3">

                    <button type="submit" class="btn btn-primary">
                        Update NAS
                    </button>

                    <a href="{{ route('nas.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
