@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h4 class="mb-0">Create NAS</h4>
            <small class="text-muted">Add new Radius NAS device</small>
        </div>

        <a href="{{ route('nas.index') }}"
           class="btn btn-outline-secondary btn-sm">
            ← Back
        </a>

    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body">

            <form action="{{ route('nas.store') }}" method="POST">
                @csrf

                {{-- NAS NAME --}}
                <div class="mb-3">
                    <label class="form-label">NAS Name</label>
                    <input type="text"
                           name="shortname"
                           class="form-control"
                           placeholder="Enter NAS name"
                           required>
                </div>

                {{-- IP ADDRESS --}}
                <div class="mb-3">
                    <label class="form-label">IP Address</label>
                    <input type="text"
                           name="nasname"
                           class="form-control"
                           placeholder="e.g. 192.168.1.1"
                           required>
                </div>

                {{-- SECRET --}}
                <div class="mb-3">
                    <label class="form-label">Secret</label>
                    <input type="text"
                           name="secret"
                           class="form-control"
                           placeholder="radius secret key"
                           required>
                </div>

                {{-- PORTS --}}
                <div class="mb-3">
                    <label class="form-label">Ports</label>
                    <input type="text"
                           name="ports"
                           class="form-control"
                           placeholder="e.g. 3799">
                </div>

                {{-- TYPE --}}
                <div class="mb-3">
                    <label class="form-label">Type</label>

                    <select name="type" class="form-control" required>
                        <option value="other">Other</option>
                        <option value="mikrotik">Mikrotik</option>
                        <option value="juniper">Juniper</option>
                    </select>

                </div>

                {{-- DESCRIPTION --}}
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3"
                              placeholder="Optional notes..."></textarea>
                </div>

                {{-- BUTTONS --}}
                <div class="d-flex gap-2">

                    <button type="submit" class="btn btn-sm btn-primary">
                        Save
                    </button>

                    <a href="{{ route('nas.index') }}"
                       class="btn btn-sm btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
