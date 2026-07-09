@extends('layouts.app')

@section('title', 'Add TR-069 Server')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add New ACS Server</h3>
                </div>
                <form action="{{ route('tr069server.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Name *</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="acs_url">ACS URL *</label>
                            <input type="url" name="acs_url" id="acs_url" class="form-control @error('acs_url') is-invalid @enderror" value="{{ old('acs_url') }}" required>
                            @error('acs_url')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <div class="form-group">
                            <label for="acs_username">Username (optional)</label>
                            <input type="text" name="acs_username" id="acs_username" class="form-control" value="{{ old('acs_username') }}">
                        </div>
                        <div class="form-group">
                            <label for="acs_password">Password (optional)</label>
                            <input type="password" name="acs_password" id="acs_password" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select name="status" id="status" class="form-control">
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                        <a href="{{ route('tr069server.index') }}" class="btn btn-sm btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
