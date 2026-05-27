@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-0">Create Internet Plan</h4>
            <small class="text-muted">Add new bandwidth package</small>
        </div>

        <a href="{{ route('internetplan.index') }}"
           class="btn btn-outline-secondary btn-sm">
            ← Back
        </a>

    </div>

    {{-- FORM CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form action="{{ route('internetplan.store') }}" method="POST">
                @csrf

                {{-- PLAN NAME --}}
                <div class="mb-3">
                    <label class="form-label">Plan Name</label>
                    <input type="text"
                           name="name"
                           class="form-control"
                           placeholder="Enter plan name"
                           value="{{ old('name') }}"
                           required>
                </div>

                {{-- PRICE --}}
                <div class="mb-3">
                    <label class="form-label">Price</label>
                    <input type="number"
                           step="0.01"
                           name="price"
                           class="form-control"
                           placeholder="Enter price"
                           value="{{ old('price') }}"
                           required>
                </div>

                {{-- DURATION + TYPE --}}
                <div class="row">

                    <div class="col-md-8 mb-3">
                        <label class="form-label">Duration</label>
                        <input type="number"
                               name="duration"
                               class="form-control"
                               placeholder="Enter duration"
                               value="{{ old('duration') }}"
                               required>
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Type</label>

                            <select name="type" class="form-control" required>
                                <option value="" disabled selected>Select Type</option>

                                @foreach ($plan_types as $plan_type)
                                    <option value="{{ $plan_type }}"
                                        {{ old('type') == $plan_type ? 'selected' : '' }}>
                                        {{ ucfirst($plan_type) }}
                                    </option>
                                @endforeach
                            </select>

                    </div>

                </div>

                {{-- RATE LIMIT --}}
                <div class="mb-4">
                    <label class="form-label">Rate Limit</label>
                    <input type="text"
                           name="rate_limit"
                           class="form-control"
                           placeholder="Example: 5M/5M"
                           value="{{ old('rate_limit') }}"
                           required>
                </div>

                {{-- BUTTONS --}}
                <div class="d-flex gap-2">

                    <button type="submit" class="btn btn-primary">
                        Save Plan
                    </button>

                    <a href="{{ route('internetplan.index') }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
