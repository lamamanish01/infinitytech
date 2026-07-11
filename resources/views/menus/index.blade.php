@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h4 class="mb-0">Menus</h4>
            <small class="text-muted">Manage sidebar navigation items</small>
        </div>

        @can('create menus')
            <a href="{{ route('menus.create') }}" class="btn btn-primary btn-sm">
                + Create Menu
            </a>
        @endcan

    </div>

    <!-- CARD -->
    <div class="card shadow-sm border-0">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-sm table-striped text-nowrap table-hover">

                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>URL</th>
                            <th>Icon</th>
                            <th>Parent</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($menus as $menu)

                            <tr>

                                <td class="text-muted">
                                    {{ $loop->iteration }}
                                </td>

                                <td class="fw-bold">
                                    {{ $menu->title }}
                                </td>

                                <td>
                                    <code>{{ $menu->url }}</code>
                                </td>

                                <td>
                                    <i class="{{ $menu->icon }}"></i>
                                    <span class="text-muted ms-1">{{ $menu->icon }}</span>
                                </td>

                                <td>
                                    @if($menu->parent)
                                        <span class="badge bg-info text-dark">
                                            {{ $menu->parent->title }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <!-- ACTION -->
                                <td class="text-end">

                                    <div class="btn-group btn-group-sm">

                                        @can('edit menus')
                                            <a href="{{ route('menus.edit', $menu->id) }}"
                                               class="btn btn-sm btn-primary">
                                                Edit
                                            </a>
                                        @endcan

                                        @can('delete menus')
                                            <form action="{{ route('menus.destroy', $menu->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('Are you sure you want to delete this menu?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-sm btn-danger"
                                                        type="submit">
                                                    Delete
                                                </button>

                                            </form>
                                        @endcan

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No Menus Found
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    <!-- PAGINATION -->
    <div class="mt-3 d-flex justify-content-end">
        {{ $menus->links() }}
    </div>

</div>

@endsection
