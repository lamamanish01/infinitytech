@php $grace = $customer->activeGrace(); @endphp

<div class="row">
    {{-- CARD 1: Customer Information --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body pt-0">
                {{-- Single‑column info list --}}
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-user mr-2"></i>Username</span>
                        <strong><span class="badge badge-secondary">{{ $customer->username ?? '-' }}</span></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-wifi mr-2"></i>Plan</span>
                        <strong><span class="badge badge-primary">{{ $customer->internetPlan->bandwidth_name ?? '-' }}</span></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-calendar-check mr-2"></i>Registered</span>
                        <strong><span class="badge badge-info">{{ $customer->registered_at->format('Y-m-d') }}</span></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-calendar-times mr-2"></i>Expires</span>
                        <strong><span class="badge badge-danger">{{ optional($customer->expire_date)->format('Y-m-d') }}</span></strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-network-wired mr-2"></i>MAC</span>
                        <strong>
                            @if($customer->mac_address)
                                <span class="badge badge-primary">{{ $customer->mac_address }}</span>
                            @else
                                <span class="badge badge-danger">Not Bound</span>
                            @endif
                        </strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted"><i class="fas fa-power-off mr-2"></i>Termination</span>
                        <strong>
                            @if($lastSession && $lastSession->acctterminatecause)
                                <span class="badge badge-danger">{{ $lastSession->acctterminatecause }}</span>
                            @else
                                <span class="badge badge-success">N/A</span>
                            @endif
                        </strong>
                    </li>
                </ul>

                {{-- Grace Period Section (tiles) --}}
                <div class="mt-4 pt-3 border-top">
                    <h6 class="text-muted"><i class="fas fa-hourglass-half mr-2"></i>Grace Period</h6>
                    <div class="row mt-2">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-2 rounded d-flex justify-content-between">
                                <span class="text-muted">Status</span>
                                <strong>
                                    @if($grace)
                                        <span class="badge badge-warning text-dark">Active</span>
                                    @else
                                        <span class="badge badge-secondary">None</span>
                                    @endif
                                </strong>
                            </div>
                        </div>
                        @if($grace)
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-2 rounded d-flex justify-content-between">
                                    <span class="text-muted">Days</span>
                                    <strong><span class="badge badge-warning text-dark">{{ $grace->grace_days }}</span></strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-2 rounded d-flex justify-content-between">
                                    <span class="text-muted">Start</span>
                                    <strong><span class="badge badge-info">{{ $grace->grace_start->format('Y-m-d') }}</span></strong>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light p-2 rounded d-flex justify-content-between">
                                    <span class="text-muted">End</span>
                                    <strong><span class="badge badge-danger">{{ $grace->grace_end->format('Y-m-d') }}</span></strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CARD 2: Quick Actions --}}
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="fas fa-bolt text-warning mr-2"></i>Quick Actions</h5>
            </div>
            <div class="card-body">
                {{-- Primary actions – solid buttons --}}
                <div class="d-flex flex-wrap">
                    @can('recharge customers')
                        <a href="{{ route('recharges.create', $customer->id) }}" class="btn btn-warning btn-sm mb-2 mr-2">
                            <i class="fas fa-plus-circle"></i> Recharge
                        </a>
                    @endcan
                    @can('change expiry customers')
                        <a href="{{ route('customers.expiry-form', $customer->id) }}" class="btn btn-danger btn-sm mb-2 mr-2">
                            <i class="fas fa-calendar-edit"></i> Change Expiry
                        </a>
                    @endcan
                    @can('grace customers')
                        <form action="{{ route('provide-grace', $customer->id) }}" method="POST" class="mb-2 mr-2">
                            @csrf
                            <button class="btn btn-info btn-sm" type="submit">
                                <i class="fas fa-hourglass-start"></i> +3 Days Grace
                            </button>
                        </form>
                    @endcan
                    @can('disconnect customers')
                        <form action="{{ route('customer.disconnect', $customer->id) }}" method="POST" class="mb-2 mr-2">
                            @csrf
                            <button class="btn btn-dark btn-sm" type="submit">
                                <i class="fas fa-plug"></i> Disconnect
                            </button>
                        </form>
                    @endcan

                    @if($customer->status === 'discontinued')
                        @can('continue customers')
                            <form action="{{ route('customer.continue', $customer->id) }}" method="POST" class="mb-2 mr-2">
                                @csrf
                                <button class="btn btn-success btn-sm" type="submit" onclick="return confirm('Reactivate this customer? They will be set to active status.')">
                                    <i class="fas fa-play"></i> Continue
                                </button>
                            </form>
                        @endcan
                    @else
                        @can('discontinue customers')
                            <form action="{{ route('customer.discontinue', $customer->id) }}" method="POST" class="mb-2 mr-2">
                                @csrf
                                <button class="btn btn-danger btn-sm" type="submit" onclick="return confirm('Are you sure you want to discontinue this customer? This action may be irreversible.')">
                                    <i class="fas fa-ban"></i> Discontinue
                                </button>
                            </form>
                        @endcan
                    @endif
                </div>

                {{-- Secondary actions – outline buttons --}}
                <div class="border-top pt-2 mt-1">
                    <div class="d-flex flex-wrap">
                        @if($customer->mac_address)
                            @can('unbind mac customers')
                                <form action="{{ route('customer.unbind-mac', $customer->id) }}" method="POST" class="mb-2 mr-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-unlink"></i> Unbind MAC
                                    </button>
                                </form>
                            @endcan
                        @else
                            @can('bind mac customers')
                                <form action="{{ route('customer.bind-mac', $customer->id) }}" method="POST" class="mb-2 mr-2">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-link"></i> Bind MAC
                                    </button>
                                </form>
                            @endcan
                        @endif

                        @if($customer->status === 'active')
                            @can('disable customers')
                                <form action="{{ route('customer.disable', $customer->id) }}" method="POST" class="mb-2 mr-2"
                                      onsubmit="return confirm('Are you sure you want to disable this customer?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-pause"></i> Disable
                                    </button>
                                </form>
                            @endcan
                        @else
                            @can('enable customers')
                                <form action="{{ route('customer.enable', $customer->id) }}" method="POST" class="mb-2 mr-2"
                                      onsubmit="return confirm('Are you sure you want to enable this customer?')">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-play"></i> Enable
                                    </button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
