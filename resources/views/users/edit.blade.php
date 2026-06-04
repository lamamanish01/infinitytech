@extends('layouts.app')

@section('content')

<div class="content-header">
    <div class="container-fluid">
        <h1>Edit User</h1>
    </div>
</div>

<div class="content">
    <div class="container-fluid">

        <div class="card">

            <div class="card-body">

                <form method="POST"
                      action="{{ route('users.update', $user->id) }}">

                    @csrf
                    @method('PATCH')

                    {{-- NAME --}}
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $user->name) }}"
                               required>
                    </div>

                    {{-- EMAIL --}}
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               value="{{ old('email', $user->email) }}"
                               required>
                    </div>

                    {{-- ROLES --}}
                    <div class="form-group">

                        <label>Roles</label>

                        <div>

                            @foreach($roles as $role)

                                <label class="mr-3">

                                    <input type="checkbox"
                                           name="role[]"
                                           value="{{ $role->name }}"
                                           {{ $user->hasRole($role->name) ? 'checked' : '' }}>

                                    {{ $role->name }}

                                </label>

                            @endforeach

                        </div>

                    </div>

                    {{-- PASSWORD --}}
                    <div class="form-group">

                        <label>Password</label>

                        <input type="password"
                               name="password"
                               class="form-control"
                               placeholder="Leave blank to keep current"
                               required>

                    </div>

                    <button class="btn btn-primary">
                        Update User
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

@endsection
