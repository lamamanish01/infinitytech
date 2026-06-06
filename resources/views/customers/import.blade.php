@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h4>Import Customers</h4>
        </div>
        <div class="card-body">

            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Excel File (.xlsx, .xls)</label>
                    <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept=".xlsx,.xls" required>
                    <small class="text-muted">Max size: 2MB</small>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <a href="{{ route('customers.import.template') }}" class="btn btn-sm btn-secondary">
                        Download Template (.xlsx)
                    </a>
                </div>

                <button type="submit" class="btn btn-primary">Import Customers</button>
                <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection
