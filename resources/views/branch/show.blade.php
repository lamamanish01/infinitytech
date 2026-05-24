@extends('layouts.app')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ __('Show Branch Details') }}</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body table-responsive p-3">
                            <h2>{{ $branch->name }}</h2>
                            <p>
                                <b>Address:</b>
                                {{ $branch->address }}
                            </p>
                            <p>
                                <b>Contact:</b>
                                {{ $branch->contact_number }}
                            </p>

                            <hr>

                            {{-- WALLET --}}

                            <h3>Wallet Balance</h3>

                            <h2 class="text-success">
                                {{ number_format($branch->balance,2) }}
                            </h2>

                            <hr>

                            {{-- ADD BALANCE --}}

                            <h4>Add Balance</h4>
                            <form method="POST" action="{{ route('branch.addBalance') }}">
                                @csrf
                                <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                                <div class="mb-3">
                                    <label>Amount</label>
                                    <input type="number" name="amount" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Remarks</label>
                                    <input type="text" name="remarks" class="form-control">
                                </div>
                                <button class="btn btn-success">
                                    Add Balance
                                </button>
                            </form>
                            <hr>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h4>Transaction History</h4>
                                <table class="table table-bordered">
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
                                        @foreach($branch->transactions as $txn)
                                            <tr>
                                                <td>{{ $txn->id }}</td>
                                                {{-- TYPE --}}
                                                <td>
                                                    @if($txn->type == 'credit')
                                                        <span class="text-success">Credit</span>
                                                    @else
                                                        <span class="text-danger">Debit</span>
                                                    @endif
                                                </td>
                                                <td>{{ number_format($txn->amount,2) }}</td>
                                                <td>{{ $txn->source }}</td>

                                                {{-- STATUS --}}
                                                <td>
                                                    @if($txn->is_void)
                                                        <span class="badge bg-secondary">
                                                            Voided
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success">
                                                            Active
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- ACTION --}}
                                                <td>
                                                    @if(!$txn->is_void)
                                                    <form method="POST" action="{{ route('branchTransaction.delete',$txn->id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                            <button class="btn btn-danger btn-sm">
                                                                Reverse
                                                            </button>
                                                    </form>
                                                    @else
                                                        <span class="text-muted">No Action</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                    </tbody>

                                </table>
                        </div>
                    </div>
                </div>
                </div>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
@endsection
