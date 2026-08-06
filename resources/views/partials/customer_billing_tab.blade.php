<div class="table-responsive">
    <table class="table table-sm table-striped table-hover text-nowrap">
        <thead class="table-light"><tr><th>#</th><th>Invoice</th><th>Package</th><th>Amount</th><th>Expire</th><th>Date</th></tr></thead>
        <tbody>
            @forelse($billings as $billing)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $billing->billing_no }}</td>
                    <td><span class="badge bg-primary">{{ optional($billing->customer->internetPlan)->bandwidth_name }}</span></td>
                    <td>{{ number_format($billing->amount, 2) }}</td>
                    <td>{{ optional($billing->recharge->expire_date ?? null)->format('Y-m-d') }}</td>
                    <td>{{ $billing->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">No Billing Found</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-3">{{ $billings->links() }}</div>
</div>
