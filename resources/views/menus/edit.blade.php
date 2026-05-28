@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="mb-3">
        <h4>Edit Menu</h4>
        <small class="text-muted">Update navigation item</small>
    </div>

    <!-- CARD -->
    <div class="card">

        <div class="card-body">

            <form action="{{ route('menus.update', $menu->id) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row">

                    <!-- TITLE -->
                    <div class="col-md-6 mb-3">
                        <label>Title</label>
                        <input type="text"
                               name="title"
                               class="form-control"
                               value="{{ old('title', $menu->title) }}"
                               required>
                    </div>

                    <!-- URL -->
                    <div class="col-md-6 mb-3">
                        <label>URL</label>
                        <input type="text"
                               name="url"
                               class="form-control"
                               value="{{ old('url', $menu->url) }}">
                    </div>

                    <!-- ICON -->
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        <input type="text"
                               name="icon"
                               class="form-control"
                               value="{{ old('icon', $menu->icon) }}">
                    </div>

                    <!-- ORDER -->
                    <div class="col-md-3 mb-3">
                        <label>Order</label>
                        <input type="number"
                               name="order"
                               class="form-control"
                               value="{{ old('order', $menu->order) }}">
                    </div>

                    <!-- ROLE (simple safe version) -->
                    <div class="col-md-6 mb-3">
                        <label>Role</label>
                         <input type="text"
                               name="role"
                               value="{{ old('role', $menu->role) }}"
                               class="form-control">
                    </div>

                    <!-- PARENT -->
                    <div class="col-md-6 mb-3">
                        <label>Parent Menu</label>

                        <select name="parent_id" class="form-control">

                            <option value="">None</option>

                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                    {{ $menu->parent_id == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach

                        </select>

                    </div>

                </div>

                <!-- ACTIONS -->
                <div class="d-flex gap-2 mt-3">

                    <button type="submit" class="btn btn-primary">
                        Update
                    </button>

                    <a href="{{ route('menus.index') }}" class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
