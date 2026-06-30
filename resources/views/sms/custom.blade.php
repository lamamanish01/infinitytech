{{-- resources/views/admin/sms/custom.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h3>📨 Send Custom SMS</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('sms.custom.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="recipient_type" class="form-label">Recipient Type</label>
                    <select name="recipient_type" id="recipient_type" class="form-select" required>
                        <option value="single">Single Customer</option>
                        <option value="expiring">Customers Expiring in X Days</option>
                        <option value="active">All Active Customers</option>
                    </select>
                </div>

                <div class="mb-3" id="single_group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username">
                    <small class="text-muted">Only used when Recipient Type is "Single Customer".</small>
                </div>

                <div class="mb-3" id="expiring_group" style="display:none;">
                    <label for="days" class="form-label">Days until expiry</label>
                    <input type="number" name="days" id="days" class="form-control" value="3" min="1">
                    <small class="text-muted">Customers whose expiry date is exactly this many days from today.</small>
                </div>

                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea name="message" id="message" rows="5" class="form-control" required></textarea>
                    <small class="text-muted">Max 500 characters.</small>
                </div>

                <button type="submit" class="btn btn-primary">Queue SMS</button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('recipient_type');
        const singleGroup = document.getElementById('single_group');
        const expiringGroup = document.getElementById('expiring_group');

        function toggleGroups() {
            const val = typeSelect.value;
            singleGroup.style.display = (val === 'single') ? 'block' : 'none';
            expiringGroup.style.display = (val === 'expiring') ? 'block' : 'none';
        }

        typeSelect.addEventListener('change', toggleGroups);
        toggleGroups(); // initial
    });
</script>
@endpush
@endsection
