
@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Create Role</h1>
            </div>

        </div>

    </div>

</div>

<!-- Main Content -->
<div class="content">

    <div class="container-fluid">

        <div class="row">

            <div class="col-lg-12">

                <div class="card card-info">

                    <div class="card-body">

                        <form action="{{ route('roles.store') }}"
                              method="POST">

                            @csrf

                            {{-- ROLE NAME --}}
                            <div class="form-group">

                                <label>Role Name</label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       placeholder="Enter role name"
                                       value="{{ old('name') }}"
                                       required>

                                @error('name')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            <hr>

                            {{-- PERMISSIONS --}}
                            <div class="form-group">

                                <label>Permissions</label>

                                <div class="mb-2">

                                    <label>
                                        <input type="checkbox"
                                               id="selectAll">

                                        Select All
                                    </label>

                                </div>

                                <div class="row">

                                    @foreach ($permissions as $permission)

                                        <div class="col-md-3">

                                            <label>

                                                <input type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $permission->name }}">

                                                {{ $permission->name }}

                                            </label>

                                        </div>

                                    @endforeach

                                </div>

                            </div>

                            <hr>

                            {{-- BUTTON --}}
                            <button type="submit" class="btn btn-sm btn-primary">
                                Save
                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

{{-- SELECT ALL SCRIPT --}}
<script>
    document.getElementById('selectAll').addEventListener('change', function () {

        let checkboxes = document.querySelectorAll('input[name="permissions[]"]');

        checkboxes.forEach(cb => cb.checked = this.checked);

    });
</script>

@endsection
