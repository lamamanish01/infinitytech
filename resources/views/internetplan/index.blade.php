@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Internet Plans</h4>
            <small class="text-muted">Manage bandwidth packages & pricing</small>
        </div>

        <div>

            @can('create internet plans')
                <a href="{{ route('internetplan.create') }}"
                   class="btn btn-sm btn-primary">
                    + New Plan
                </a>
            @endcan

        </div>

    </div>

    {{-- TABLE CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-striped table-hover text-center text-nowrap">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Plan</th>
                            <th>Bandwidth</th>
                            <th>Price</th>
                            <th>Duration</th>
                            <th>Speed</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($internetplans as $plan)

                            <tr>

                                <td class="text-muted">
                                    {{ $internetplans->firstItem() + $loop->index }}
                                </td>

                                <td>
                                    <div class="fw-bold">{{ $plan->name }}</div>
                                    <small class="text-muted">Plan ID: #{{ $plan->id }}</small>
                                </td>

                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $plan->bandwidth_name }}
                                    </span>
                                </td>

                                <td>
                                    <div class="fw-bold text-success">
                                        Rs {{ number_format($plan->price, 2) }}
                                    </div>
                                </td>

                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $plan->duration }} {{ ucfirst($plan->type) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge bg-dark">
                                        {{ $plan->rate_limit }}
                                    </span>
                                </td>

                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        @can('edit internet plans')
                                            <a href="{{ route('internetplan.edit', $plan->id) }}"
                                               class="btn btn-primary btn-sm">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete internet plans')
                                            <form action="{{ route('internetplan.destroy', $plan->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this plan?')"
                                                  style="display:inline-block;">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>

                                            </form>
                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <h6>No Internet Plans Found</h6>
                                    <small>Create your first plan to get started</small>
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3 d-flex justify-content-end">
        {{ $internetplans->withQueryString()->links() }}
    </div>

</div>

@endsection
