<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/fontawesome.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">

    @yield('styles')
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        {{--  search  --}}

        <form class="form-inline ml-3" action="{{ route('customers.index') }}" method="GET">
            <div class="input-group input-group-sm">
                <input class="form-control form-control-navbar"
                    type="search"
                    name="q"
                    placeholder="Search customers..."
                    value="{{ request('q') }}">

                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">

            {{--  notification  --}}

            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>

                    @if($unreadCount > 0)
                        <span class="badge badge-warning navbar-badge">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

                    <span class="dropdown-item dropdown-header">
                        {{ $unreadCount }} Notifications
                    </span>

                    <div class="dropdown-divider"></div>

                    <a href="{{ route('activities.markAllRead') }}"
                    class="dropdown-item text-center text-primary">
                        Mark all as read
                    </a>

                    <div class="dropdown-divider"></div>

                    @forelse($activities as $activity)

                        <a href="{{ route('activity.read', $activity->id) }}"
                        class="dropdown-item {{ $activity->is_read ? '' : 'bg-light' }}">

                            <i class="{{ $activity->icon ?? 'fas fa-bell mr-2' }}"></i>

                            {{ $activity->title }}

                            <span class="float-right text-muted text-sm">
                                {{ $activity->created_at->diffForHumans() }}
                            </span>
                        </a>

                        <div class="dropdown-divider"></div>

                    @empty
                        <span class="dropdown-item text-center text-muted">
                            No notifications
                        </span>
                    @endforelse

                    <a href="{{ route('activities.index') }}"
                    class="dropdown-item dropdown-footer">
                        View All
                    </a>

                </div>
            </li>


            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <i class="nav-icon fas fa-user"></i>
                    {{ Auth::user()->name }}
                </a>
                <div class="dropdown-menu dropdown-menu-right" style="left: inherit; right: 0px;">
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <i class="mr-2 fas fa-file"></i>
                        {{ __('My profile') }}
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="mr-2 fas fa-sign-out-alt"></i>
                            {{ __('Log Out') }}
                        </a>
                    </form>
                </div>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="#" class="brand-link">
            <img src="{{ asset('#') }}" alt=""
                 class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">InfinityTech</span>
        </a>

        @include('layouts.navigation')
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

    <div class="container-fluid pt-3">

        {{-- ERRORS --}}
        @if ($errors->any())
            <div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert">

                <strong class="d-block mb-2">Please fix the following errors:</strong>

                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">x</button>

            </div>
        @endif


        {{-- SUCCESS --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex justify-content-between align-items-start" role="alert">

                <div>
                    <i class="bi bi-check-circle me-1"></i>
                    {{ session('success') }}
                </div>

                <button type="button"
                        class="btn-close ms-3"
                        data-bs-dismiss="alert"
                        aria-label="Close">x</button>

            </div>
        @endif


        {{-- ERROR --}}
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex justify-content-between align-items-start" role="alert">

                <div>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    {{ session('error') }}
                </div>

                <button type="button"
                        class="btn-close ms-3"
                        data-bs-dismiss="alert"
                        aria-label="Close">x</button>

            </div>
        @endif

    </div>

    @yield('content')

</div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <!-- To the right -->
        <div class="float-right d-none d-sm-inline">
            {{--  Anything you want  --}}
        </div>
        <!-- Default to the left -->
        <strong>Copyright &copy; 2026 <a href="#">InfintyTech Communication Pvt Ltd</a>.
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

@vite('resources/js/app.js')
<!-- AdminLTE App -->
<script src="{{ asset('js/adminlte.min.js') }}" defer></script>

@yield('scripts')
</body>
</html>

