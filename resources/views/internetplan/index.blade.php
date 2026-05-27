@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Internet Plans</h4>
            <small class="text-muted">Manage bandwidth packages & pricing</small>
        </div>

        <div class="d-flex gap-2">

            @can('create internetplans')
                <a href="{{ route('internetplan.create') }}"
                   class="btn btn-primary btn-sm">
                    + New Plan
                </a>
            @endcan

        </div>

    </div>

    {{-- CARD --}}
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

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

                                {{-- ID --}}
                                <td class="text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                {{-- NAME --}}
                                <td>
                                    <div class="fw-bold">
                                        {{ $plan->name }}
                                    </div>
                                    <small class="text-muted">
                                        Plan ID: #{{ $plan->id }}
                                    </small>
                                </td>

                                {{-- BANDWIDTH --}}
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $plan->bandwidth_name }}
                                    </span>
                                </td>

                                {{-- PRICE --}}
                                <td>
                                    <div class="fw-bold text-success">
                                        Rs {{ number_format($plan->price, 2) }}
                                    </div>
                                </td>

                                {{-- DURATION --}}
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ $plan->duration }} {{ ucfirst($plan->type) }}
                                    </span>
                                </td>

                                {{-- RATE LIMIT --}}
                                <td>
                                    <span class="badge bg-dark">
                                        {{ $plan->rate_limit }}
                                    </span>
                                </td>

                                {{-- ACTION --}}
                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        @can('edit internetplans')
                                            <a href="{{ route('internetplan.edit', $plan->id) }}"
                                               class="btn btn-sm btn-primary">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete internetplans')
                                            <form action="{{ route('internetplan.destroy', $plan->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Delete this plan? This action cannot be undone.')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-sm btn-danger">
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

                                    <div>
                                        <h6>No Internet Plans Found</h6>
                                        <small>Create your first plan to get started</small>
                                    </div>

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
