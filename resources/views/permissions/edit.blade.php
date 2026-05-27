@extends('layouts.app')

@section('content')

<!-- Content Header -->
<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">
                <h1 class="m-0">Edit Permission</h1>
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

                        <form action="{{ route('permissions.update', $permission->id) }}"
                              method="POST">

                            @csrf
                            @method('PATCH')

                            {{-- NAME --}}
                            <div class="form-group">

                                <label>Permission Name</label>

                                <input type="text"
                                       name="name"
                                       class="form-control"
                                       value="{{ old('name', $permission->name) }}"
                                       required>

                                @error('name')
                                    <small class="text-danger">
                                        {{ $message }}
                                    </small>
                                @enderror

                            </div>

                            {{-- BUTTONS --}}
                            <div class="btn-group">

                                <button type="submit"
                                        class="btn btn-primary">

                                    Update

                                </button>

                                <a href="{{ route('permissions.index') }}"
                                   class="btn btn-secondary">

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
