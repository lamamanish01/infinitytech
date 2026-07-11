@extends('layouts.app')

@section('content')

{{-- Load Chart.js from CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Billing Records</h4>
            <small class="text-muted">Manage all customer invoices and payments</small>
        </div>
    </div>

    {{-- SEARCH / FILTER FORM --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form action="{{ route('billing.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search"
                           placeholder="Billing No, Customer, Invoice..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- MONTHLY CHART --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0">Billing Amount by Month (all filtered records)</h5>
        </div>
        <div class="card-body">
            <div style="position: relative; height: 250px; min-height: 250px; width: 100%;">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- BILLINGS TABLE --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover text-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Billing No</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($billings as $billing)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><span class="fw-bold">{{ $billing->billing_no }}</span></td>
                                <td>{{ $billing->customer->name ?? 'N/A' }}</td>
                                <td>{{ $billing->billing_date ? \Carbon\Carbon::parse($billing->billing_date)->format('Y-m-d') : '-' }}</td>
                                <td><span class="fw-bold">{{ number_format($billing->amount, 2) }}</span></td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'paid'      => 'success',
                                            'unpaid'    => 'danger',
                                            'partial'   => 'warning',
                                            'cancelled' => 'secondary',
                                        ];
                                        $color = $statusColors[$billing->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($billing->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('billing.show', $billing) }}" class="btn btn-sm btn-primary">View</a>
                                        <a href="{{ route('billing.edit', $billing) }}" class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('billing.destroy', $billing) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this billing record?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">No billing records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Total (current page)</td>
                            <td><strong>{{ number_format($billings->sum('amount'), 2) }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $billings->appends(request()->query())->links() }}
    </div>

</div>

{{-- Chart Initialization --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Chart === 'undefined') {
            console.error('Chart.js not loaded');
            return;
        }

        const ctx = document.getElementById('monthlyChart');
        if (!ctx) {
            console.error('Canvas element not found');
            return;
        }

        const labels = @json($monthLabels);
        const data = @json($monthValues);

        if (labels.length === 0) {
            ctx.parentElement.innerHTML = '<p class="text-muted text-center my-5">No monthly data available.</p>';
            return;
        }

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total Amount',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100000,
                        ticks: {
                            stepSize: 5000, // ← increments by 5000
                            callback: function(value) {
                                return value.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            }
                        }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Total: ' + context.parsed.y.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
                            }
                        }
                    }
                }
            },
            plugins: [{
                id: 'customLabels',
                afterDraw: function(chart) {
                    const ctx2 = chart.ctx;
                    chart.data.datasets.forEach(function(dataset, i) {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach(function(bar, index) {
                            const dataValue = dataset.data[index];
                            ctx2.fillStyle = '#333';
                            ctx2.font = '12px sans-serif';
                            ctx2.textAlign = 'center';
                            ctx2.textBaseline = 'bottom';
                            ctx2.fillText(
                                dataValue.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 }),
                                bar.x,
                                bar.y - 5
                            );
                        });
                    });
                }
            }]
        });
    });
</script>

@endsection
