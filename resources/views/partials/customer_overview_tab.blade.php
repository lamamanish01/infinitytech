@php $grace = $customer->activeGrace(); @endphp
<div class="row">
    <div class="col-md-6">
        <ul class="list-group">
            <li class="list-group-item d-flex justify-content-between"><strong>Username</strong><strong><span class="badge badge-success">{{ $customer->username ?? '-' }}</span></strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Internet Plan</strong><strong><span class="badge badge-primary">{{ $customer->internetPlan->bandwidth_name ?? '-' }}</span></strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Status</strong><strong><span class="badge @if($customer->status == 'active') badge-success @elseif($customer->status == 'grace') badge-warning text-dark @else badge-danger @endif">{{ strtoupper($customer->status) }}</span></strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Registered Date</strong><strong><span class="badge badge-primary">{{ $customer->registered_at->format('Y-m-d') }}</span></strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Expire Date</strong><strong><span class="badge badge-danger">{{ optional($customer->expire_date)->format('Y-m-d') }}</span></strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Grace</strong><strong>@if($grace)<span class="badge bg-warning text-dark">{{ $grace->grace_days }} Days</span>@else<span class="badge bg-info text-muted">No Grace</span>@endif</strong></li>
            @if($grace)
                <li class="list-group-item d-flex justify-content-between"><strong>Grace Start</strong><strong><span class="badge badge-primary">{{ $grace->grace_start->format('Y-m-d') }}</span></strong></li>
                <li class="list-group-item d-flex justify-content-between"><strong>Grace End</strong><strong><span class="badge badge-danger">{{ $grace->grace_end->format('Y-m-d') }}</span></strong></li>
            @endif
            <li class="list-group-item d-flex justify-content-between"><strong>MAC Address</strong><strong>@if($customer->mac_address)<span class="badge badge-primary">{{ $customer->mac_address }}</span>@else<span class="badge badge-danger">Not Bound</span>@endif</strong></li>
            <li class="list-group-item d-flex justify-content-between"><strong>Termination Cause</strong>
                <strong>
                    @if($lastSession && $lastSession->acctterminatecause)
                        <span class="badge badge-danger">{{ $lastSession->acctterminatecause }}</span>
                    @else
                        <span class="badge badge-success">N/A</span>
                    @endif
                </strong>
            </li>
        </ul>
    </div>
</div>

{{-- Quick Actions --}}
<div class="card mt-3 shadow-sm">
    <div class="card-header bg-white"><strong>Quick Actions</strong></div>
    <div class="card-body d-flex flex-wrap gap-2">
        @can('recharge customers')
            <a href="{{ route('recharges.create', $customer->id) }}" class="btn btn-warning btn-sm">Recharge</a>
        @endcan
        @can('change expiry customers')
            <a href="{{ route('customers.expiry-form', $customer->id) }}" class="btn btn-danger btn-sm">Change Expiry</a>
        @endcan
        @can('grace customers')
            <form action="{{ route('provide-grace', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-info btn-sm">+3 Days Grace</button>
            </form>
        @endcan
        @can('disconnect customers')
            <form action="{{ route('customer.disconnect', $customer->id) }}" method="POST">
                @csrf
                <button class="btn btn-dark btn-sm">Disconnect</button>
            </form>
        @endcan
        @if($customer->mac_address)
            @can('unbind mac customers')
                <form action="{{ route('customer.unbind-mac', $customer->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger btn-sm">Unbind MAC</button>
                </form>
            @endcan
        @else
            @can('bind mac customers')
                <form action="{{ route('customer.bind-mac', $customer->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm">Bind MAC</button>
                </form>
            @endcan
        @endif
        @if($customer->status === 'active')
            @can('disable customers')
                <form action="{{ route('customer.disable', $customer->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to disable this customer?')">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="bi bi-x-circle"></i> Disable</button>
                </form>
            @endcan
        @else
            @can('enable customers')
                <form action="{{ route('customer.enable', $customer->id) }}" method="POST"
                    onsubmit="return confirm('Are you sure you want to enable this customer?')">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Enable</button>
                </form>
            @endcan
        @endif
    </div>
</div>
