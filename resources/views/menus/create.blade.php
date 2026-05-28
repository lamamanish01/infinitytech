@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="mb-3">
        <h4>Create Menu</h4>
        <small class="text-muted">Add navigation item</small>
    </div>

    <div class="card">

        <div class="card-body">

            <form action="{{ route('menus.store') }}" method="POST">
                @csrf

                <div class="row">

                    <!-- TITLE -->
                    <div class="col-md-6 mb-3">
                        <label>Title *</label>
                        <input type="text"
                               name="title"
                               value="{{ old('title') }}"
                               class="form-control"
                               required>
                    </div>

                    <!-- URL -->
                    <div class="col-md-6 mb-3">
                        <label>URL</label>
                        <input type="text"
                               name="url"
                               value="{{ old('url') }}"
                               class="form-control">
                    </div>

                    <!-- ICON -->
                    <div class="col-md-6 mb-3">
                        <label>Icon</label>
                        <input type="text"
                               name="icon"
                               value="{{ old('icon') }}"
                               class="form-control">
                    </div>

                    <!-- ROLE -->
                    <div class="col-md-6 mb-3">
                        <label>Role</label>
                         <input type="text"
                               name="icon"
                               value="{{ old('role') }}"
                               class="form-control">
                    </div>

                    <!-- PARENT -->
                    <div class="col-md-6 mb-3">
                        <label>Parent Menu</label>
                        <select name="parent_id" class="form-control">

                            <option value="">None</option>

                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}"
                                    {{ old('parent_id') == $parent->id ? 'selected' : '' }}>
                                    {{ $parent->title }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <!-- ORDER -->
                    <div class="col-md-6 mb-3">
                        <label>Order</label>
                        <input type="number"
                               name="order"
                               value="{{ old('order', 0) }}"
                               class="form-control">
                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="d-flex gap-2 mt-3">

                    <button type="submit" class="btn btn-primary">
                        Save Menu
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
