@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Send Custom SMS</h4>
            <small class="text-muted">Send SMS manually to any number</small>
        </div>
    </div>

    <!-- FORM CARD -->
    <div class="card card-info">

        <div class="card-body">

            <form action="{{ route('sms.sendCustom') }}" method="POST">
                @csrf

                <!-- MOBILE -->
                <div class="form-group mb-3">
                    <label>Mobile Number</label>
                    <input type="text"
                           name="mobile"
                           class="form-control"
                           placeholder="e.g. 9800000000"
                           required>
                </div>

                <!-- OPTIONAL USERNAME -->
                <div class="form-group">
                    <label>Select Customer</label>

                    <select name="customer_id" class="form-control" required>
                        <option value="">-- Select Customer --</option>

                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->username }} ({{ $customer->contact_number }})
                            </option>
                        @endforeach

                    </select>
                </div>

                <!-- MESSAGE -->
                <div class="form-group mb-3">
                    <label>Message</label>
                    <textarea name="message"
                              rows="5"
                              class="form-control"
                              placeholder="Write your custom SMS message here..."
                              required></textarea>
                </div>

                <!-- BUTTON -->
                <button type="submit" class="btn btn-success">
                    Send SMS
                </button>

            </form>

        </div>

    </div>

</div>

@endsection
