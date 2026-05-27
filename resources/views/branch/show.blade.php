@extends('layouts.app')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="row mb-2">

            <div class="col-sm-6">

                <h1 class="m-0">
                    Branch Details
                </h1>

            </div>

            <div class="col-sm-6 text-right">

                <a href="{{ route('branch.index') }}"
                   class="btn btn-secondary">

                    Back

                </a>

            </div>

        </div>

    </div>

</div>

<div class="content">

    <div class="container-fluid">

        <div class="row">

            {{-- LEFT SIDE --}}
            <div class="col-lg-4">

                <div class="card card-info">

                    <div class="card-header">
                        Branch Info
                    </div>

                    <div class="card-body">

                        <h4>{{ $branch->name }}</h4>

                        <p><strong>Address:</strong> {{ $branch->address }}</p>

                        <p><strong>Contact:</strong> {{ $branch->contact_number }}</p>

                    </div>

                </div>

                <div class="card card-success">

                    <div class="card-header">
                        Wallet Balance
                    </div>

                    <div class="card-body text-center">

                        <h2 class="text-success">
                            Rs. {{ number_format($branch->balance, 2) }}
                        </h2>

                    </div>

                </div>

                <div class="card card-primary">

                    <div class="card-header">
                        Add Balance
                    </div>

                    <div class="card-body">

                        <form method="POST"
                              action="{{ route('branch.addBalance') }}">

                            @csrf

                            <input type="hidden"
                                   name="branch_id"
                                   value="{{ $branch->id }}">

                            <div class="form-group">

                                <label>Amount</label>

                                <input type="number"
                                       step="0.01"
                                       name="amount"
                                       class="form-control"
                                       required>

                            </div>

                            <div class="form-group">

                                <label>Remarks</label>

                                <input type="text"
                                       name="remarks"
                                       class="form-control">

                            </div>

                            <button class="btn btn-success">
                                Add Balance
                            </button>

                        </form>

                    </div>

                </div>

            </div>

            {{-- RIGHT SIDE --}}
            <div class="col-lg-8">

                <div class="card card-info">

                    <div class="card-header">
                        Transaction History
                    </div>

                    <div class="card-body table-responsive p-0">

                        <table class="table table-hover text-nowrap">

                            <thead>

                                <tr>
                                    <th>ID</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>

                            <tbody>

                                @forelse($transactions as $txn)

                                    <tr>

                                        <td>{{ $txn->id }}</td>

                                        <td>

                                            @if($txn->type == 'credit')
                                                <span class="badge badge-success">
                                                    Credit
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    Debit
                                                </span>
                                            @endif

                                        </td>

                                        <td>
                                            Rs. {{ number_format($txn->amount, 2) }}
                                        </td>

                                        <td>
                                            {{ $txn->source }}
                                        </td>

                                        <td>

                                            @if($txn->is_void)
                                                <span class="badge badge-secondary">
                                                    Voided
                                                </span>
                                            @else
                                                <span class="badge badge-success">
                                                    Active
                                                </span>
                                            @endif

                                        </td>

                                        <td>

                                            @if(!$txn->is_void)

                                                <form method="POST"
                                                      action="{{ route('branchTransaction.delete', $txn->id) }}"
                                                      onsubmit="return confirm('Reverse this transaction?')">

                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-danger btn-sm">
                                                        Reverse
                                                    </button>

                                                </form>

                                            @else

                                                <span class="text-muted">
                                                    No Action
                                                </span>

                                            @endif

                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6"
                                            class="text-center text-muted">
                                            No Transactions Found
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

                {{-- PAGINATION --}}
                <div class="mt-3">
                    {{ $transactions->links() }}
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
