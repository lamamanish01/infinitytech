@extends('layouts.app')

@section('content')

<!-- HEADER -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                {{-- Changed to "Create" since this is the create page --}}
                <h1 class="m-0">Create SMS Gateway</h1>
            </div>
        </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">

                <div class="card card-info">
                    <div class="card-body">
                        <form action="{{ route('sms.store') }}" method="POST">
                            @csrf

                            <!-- NAME -->
                            <div class="form-group mb-3">
                                <label>Gateway Name</label>
                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name') }}"
                                       required>
                                {{-- Optional: Show validation error --}}
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- API URL -->
                            <div class="form-group mb-3">
                                <label>API URL</label>
                                <input type="text"
                                       name="api_url"
                                       class="form-control"
                                       value="{{ old('api_url') }}"
                                       required>
                                @error('api_url')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- AUTH TOKEN -->
                            <div class="form-group mb-3">
                                <label>Auth Token</label>
                                <input type="text"
                                       name="auth_token"
                                       class="form-control"
                                       value="{{ old('auth_token') }}"
                                       required>
                                @error('auth_token')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- STATUS -->
                            <div class="form-group mb-3">
                                <label>Status</label>
                                <select name="is_active" class="form-control">
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>
                                        Active
                                    </option>
                                    <option value="0" {{ old('is_active', 1) == 0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                </select>
                                @error('is_active')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <!-- BUTTONS -->
                            <div class="mt-3">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-save"></i>
                                    Save
                                </button>

                                <a href="{{ route('sms.index') }}" class="btn btn-sm btn-secondary">
                                    Cancel
                                </a>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@endsection
