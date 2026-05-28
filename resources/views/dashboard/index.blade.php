@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="mb-3">
        <h4 class="mb-0">Dashboard</h4>
        <small class="text-muted">System overview</small>
    </div>

    {{-- ROW 1 --}}
    <div class="row">

        {{-- ONLINE --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary text-white">

                <div class="inner">
                    <h3>{{ $onlineCustomers ?? 0 }}</h3>
                    <p>Online Customers</p>
                </div>

                <div class="icon">
                    <i class="bi bi-wifi"></i>
                </div>

                <a href="{{ url('/customers/online') }}" class="small-box-footer text-white">
                    More info <i class="bi bi-arrow-right"></i>
                </a>

            </div>
        </div>

        {{-- TOTAL --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success text-white">

                <div class="inner">
                    <h3>{{ $totalCustomers ?? 0 }}</h3>
                    <p>Total Customers</p>
                </div>

                <div class="icon">
                    <i class="bi bi-people"></i>
                </div>

                <a href="{{ route('customers.index') }}" class="small-box-footer text-white">
                    More info <i class="bi bi-arrow-right"></i>
                </a>

            </div>
        </div>

        {{-- PLANS --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning text-dark">

                <div class="inner">
                    <h3>{{ $totalPlans ?? 0 }}</h3>
                    <p>Internet Plans</p>
                </div>

                <div class="icon">
                    <i class="bi bi-speedometer2"></i>
                </div>

                <a href="{{ route('internetplan.index') }}" class="small-box-footer text-dark">
                    More info <i class="bi bi-arrow-right"></i>
                </a>

            </div>
        </div>

        {{-- EXPIRED --}}
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger text-white">

                <div class="inner">
                    <h3>{{ $expiredCustomers ?? 0 }}</h3>
                    <p>Expired Customers</p>
                </div>

                <div class="icon">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>

                <a href="{{ url('/customers?status=expired') }}" class="small-box-footer text-white">
                    More info <i class="bi bi-arrow-right"></i>
                </a>

            </div>
        </div>

    </div>

    {{-- ROW 2 --}}
    <div class="row mt-3">

        <div class="col-lg-4 col-12">
            <div class="small-box bg-info text-white">
                <div class="inner">
                    <h3>Rs {{ number_format($branchBalance ?? 0, 2) }}</h3>
                    <p>Branch Balance</p>
                </div>
                <div class="icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-primary text-white">
                <div class="inner">
                    <h3>{{ $activeSessions ?? 0 }}</h3>
                    <p>Active Sessions</p>
                </div>
                <div class="icon">
                    <i class="bi bi-router"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-secondary text-white">
                <div class="inner">
                    <h3>{{ $nasCount ?? 0 }}</h3>
                    <p>NAS Devices</p>
                </div>
                <div class="icon">
                    <i class="bi bi-hdd-network"></i>
                </div>
            </div>
        </div>

    </div>

</div>

@endsection
