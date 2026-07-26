<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice – {{ $invoice->invoice_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 13px;
            line-height: 1.4;
            color: #333;
            margin: 30px;
        }
        .invoice-box {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        .company-name {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 0;
        }
        .company-address {
            font-size: 13px;
            color: #555;
            margin-top: 2px;
            margin-bottom: 20px;
        }
        .address-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .address-col-left {
            width: 48%;
            text-align: left;
        }
        .address-col-right {
            width: 48%;
            text-align: right;
        }
        .address-col .label {
            font-weight: bold;
            margin-bottom: 3px;
        }
        .address-col .value {
            line-height: 1.6;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }
        .meta-table td:first-child {
            font-weight: bold;
            width: 30%;
        }
        .meta-table td:last-child {
            width: 70%;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .items-table th {
            background: #f0f0f0;
            font-weight: bold;
            text-align: left;
            padding: 8px 5px;
            border-bottom: 1px solid #ccc;
        }
        .items-table td {
            padding: 8px 5px;
            border-bottom: 1px solid #eee;
        }
        .items-table .text-right {
            text-align: right;
        }
        .totals-table {
            width: 100%;
            margin: 10px 0 20px;
            text-align: right;
        }
        .totals-table td {
            padding: 4px 0;
        }
        .totals-table .label {
            font-weight: normal;
            padding-right: 20px;
        }
        .totals-table .amount {
            font-weight: bold;
        }
        .totals-table .grand-total {
            font-size: 18px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 8px;
        }
        .payment-details {
            margin: 15px 0;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 12px;
        }
        .terms {
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 15px;
            font-size: 12px;
            color: #555;
        }
        .terms p {
            margin: 4px 0;
        }
        .status-badge-paid {
            background: #d4edda;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: #155724;
        }
        .status-badge-unpaid {
            background: #f8d7da;
            padding: 2px 10px;
            border-radius: 4px;
            font-weight: bold;
            color: #721c24;
        }
    </style>
</head>
<body>
<div class="invoice-box">

    {{-- ============================================================ --}}
    {{-- COMPANY HEADER (from branch / head office)                    --}}
    {{-- ============================================================ --}}
    <div class="company-name">{{ $settings['company_name'] }}</div>
    <div class="company-address">
        {{ $settings['company_address'] }}
        @if($settings['company_phone'])
            <br>Phone: {{ $settings['company_phone'] }}
        @endif
    </div>

    {{-- ============================================================ --}}
    {{-- BILL TO (left)  /  SHIP TO (right)  – same line              --}}
    {{-- ============================================================ --}}
    <div class="address-row">
        {{-- Bill To (left) --}}
        <div class="address-col address-col-left">
            <div class="label">Bill To</div>
            <div class="value">
                {{ $customer->name }}<br>
                {{ $customer->address ?? 'No address provided' }}<br>
                Phone: {{ $customer->contact_number ?? 'N/A' }}<br>
                Email: {{ $customer->email ?? 'N/A' }}
            </div>
        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- INVOICE METADATA                                              --}}
    {{-- ============================================================ --}}
    <table class="meta-table">
        <tr>
            <td>Invoice #</td>
            <td>{{ $invoice->invoice_no }}</td>
        </tr>
        <tr>
            <td>Invoice Date</td>
            <td>{{ $invoice->invoice_date->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Recharge Date</td>
            <td>{{ $recharge ? $recharge->recharge_date->format('Y-m-d') : 'N/A' }}</td>
        </tr>
        <tr>
            <td>Expire Date</td>
            <td>{{ $recharge ? $recharge->expire_date->format('Y-m-d') : 'N/A' }}</td>
        </tr>
        <tr>
            <td>Due Date</td>
            <td>{{ $invoice->invoice_date->addDays(15)->format('Y-m-d') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>
                <span class="{{ $invoice->status == 'paid' ? 'status-badge-paid' : 'status-badge-unpaid' }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </td>
        </tr>
    </table>

    {{-- ============================================================ --}}
    {{-- LINE ITEMS                                                   --}}
    {{-- ============================================================ --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:10%;">QTY</th>
                <th style="width:40%;">DESCRIPTION</th>
                <th style="width:25%; text-align:right;">UNIT PRICE</th>
                <th style="width:25%; text-align:right;">AMOUNT</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>
                    <strong>{{ $recharge ? $recharge->internetPlan->name ?? 'Internet Plan' : 'Internet Plan' }}</strong>
                    <br><small>
                        Invoice #{{ $invoice->invoice_no }} – {{ $invoice->invoice_date->format('d/m/Y') }}
                        @if($recharge)
                            <br>Recharge: {{ $recharge->recharge_date->format('Y-m-d') }}
                            – Expires: {{ $recharge->expire_date->format('Y-m-d') }}
                        @endif
                    </small>
                </td>
                <td class="text-right">NPR {{ number_format($invoice->amount, 2) }}</td>
                <td class="text-right">NPR {{ number_format($invoice->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ============================================================ --}}
    {{-- TOTALS (no tax)                                              --}}
    {{-- ============================================================ --}}
    @php
        $subtotal = $invoice->amount;
        $total = $subtotal;
    @endphp
    <table class="totals-table">
        <tr>
            <td></td>
            <td class="label">Subtotal</td>
            <td class="amount">NPR {{ number_format($subtotal, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td></td>
            <td class="label">TOTAL</td>
            <td class="amount">NPR {{ number_format($total, 2) }}</td>
        </tr>
    </table>

    {{-- ============================================================ --}}
    {{-- PAYMENT DETAILS (from recharge)                              --}}
    {{-- ============================================================ --}}
    @if($recharge && ($recharge->payment_method || $recharge->transaction_id))
    <div class="payment-details">
        <strong>Payment Details:</strong>
        @if($recharge->payment_method)
            <span class="me-3">Method: {{ ucfirst($recharge->payment_method) }}</span>
        @endif
        @if($recharge->transaction_id)
            <span>Transaction ID: {{ $recharge->transaction_id }}</span>
        @endif
    </div>
    @endif

    {{-- ============================================================ --}}
    {{-- TERMS & CONDITIONS                                           --}}
    {{-- ============================================================ --}}
    <div class="terms">
        <p><strong>Terms &amp; Conditions</strong></p>
        <p>Payment is due within 15 days</p>
        <p>Please make checks payable to: {{ $settings['company_name'] }}</p>
        <p style="margin-top:6px; font-size:11px; color:#888;">This is a computer-generated invoice.</p>
    </div>

</div>
</body>
</html>
