@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="content-header">
        <h4 class="m-0">Recharge Customer</h4>
    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <form action="{{ route('recharges.store') }}" method="POST">
                @csrf

                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                {{-- USERNAME --}}
                <div class="form-group mb-3">
                    <label>Username</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $customer->username }}"
                           readonly>
                </div>

                {{-- INTERNET PLAN --}}
                <div class="form-group mb-3">
                    <label>Internet Plan</label>

                    <select name="internet_plan_id" class="custom-select" required>
                        <option value="">
                            Select Plan
                        </option>

                        @if($customer->internetPlan)
                            <option value="{{ $customer->internetPlan->id }}" selected>
                                {{ $customer->internetPlan->bandwidth_name }}
                            </option>
                        @endif

                        @if(isset($plans))
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}">
                                    {{ $plan->bandwidth_name }}
                                </option>
                            @endforeach
                        @endif

                    </select>
                </div>

                {{-- PRICE --}}
                <div class="form-group mb-3">
                    <label>Plan Price</label>
                    <input type="text"
                           class="form-control"
                           value="{{ $customer->internetPlan->price ?? '0' }}"
                           readonly>
                </div>

                {{-- PAYMENT --}}
                <div class="form-group mb-3">
                    <label>Payment Method</label>

                    <select name="payment_method" class="custom-select" required>
                        <option value="Cash">Cash</option>
                        <option value="eSewa">eSewa</option>
                        <option value="Khalti">Khalti</option>
                        <option value="Bank">Bank</option>
                    </select>
                </div>

                {{-- TRANSACTION --}}
                <div class="form-group mb-3">
                    <label>Transaction ID</label>
                    <input type="text"
                           class="form-control"
                           name="transaction_id"
                           placeholder="Optional">
                </div>

                {{-- BUTTONS --}}
                <div class="d-flex gap-2">

                    {{-- SUBMIT --}}
                    <button type="submit" class="btn btn-primary">
                        Recharge
                    </button>

                    {{-- CANCEL --}}
                    <a href="{{ url()->previous() }}"
                       class="btn btn-secondary">
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

@endsection
