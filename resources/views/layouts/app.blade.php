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

    {{-- SweetAlert2 CDN (latest v11) --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Custom styles for SweetAlert2 toasts --}}
    <style>
        /* Base toast styling */
        .swal2-toast {
            border-radius: 8px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12), 0 5px 12px rgba(0,0,0,0.08) !important;
            padding: 12px 16px !important;
            border-left: 5px solid #ccc;
            font-family: 'Source Sans Pro', sans-serif;
        }

        /* Success toast border */
        .swal2-toast.swal2-success {
            border-left-color: #28a745;
        }

        /* Error toast border */
        .swal2-toast.swal2-error {
            border-left-color: #dc3545;
        }

        /* Warning toast border */
        .swal2-toast.swal2-warning {
            border-left-color: #ffc107;
        }

        /* Title and content inside toast */
        .swal2-toast .swal2-title {
            font-size: 0.95rem !important;
            font-weight: 600;
            margin: 0 !important;
            padding: 0 !important;
            color: #1a1a2e;
        }

        .swal2-toast .swal2-html-container {
            font-size: 0.85rem !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #4a4a6a;
        }

        /* Progress bar color matches the accent */
        .swal2-timer-progress-bar {
            background: rgba(0,0,0,0.08) !important;
        }

        /* Custom animation: slide in from right */
        .swal2-show {
            animation: swal2-slide-in 0.3s ease-out forwards !important;
        }

        .swal2-hide {
            animation: swal2-slide-out 0.2s ease-in forwards !important;
        }

        @keyframes swal2-slide-in {
            0% {
                transform: translateX(100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes swal2-slide-out {
            0% {
                transform: translateX(0);
                opacity: 1;
            }
            100% {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Adjust icon inside toast */
        .swal2-toast .swal2-icon {
            width: 2em !important;
            height: 2em !important;
            margin: 0 10px 0 0 !important;
        }
        .swal2-toast .swal2-icon-content {
            font-size: 1.4em !important;
        }

        /* Validation error list styling */
        .swal2-toast .swal2-html-container ul {
            margin: 0.25rem 0 0 0 !important;
            padding-left: 1.2rem !important;
        }
        .swal2-toast .swal2-html-container li {
            margin-bottom: 0.1rem;
        }
    </style>

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
                <a class="nav-link d-flex align-items-center" data-bs-toggle="dropdown" href="#">
                    <i class="nav-icon fas fa-user me-1"></i>
                    <span class="text-truncate d-inline-block" style="max-width:120px;">
                        {{ Auth::user()->name }}
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a href="{{ route('profile.show') }}" class="dropdown-item">
                        <i class="fas fa-user me-2"></i> My Profile
                    </a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                            <i class="fas fa-sign-out-alt me-2"></i> Log Out
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

        {{-- Page content starts here --}}
        <div class="container-fluid pt-3">
            @yield('content')
        </div>

    </div>
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside>
    <!-- /.control-sidebar -->

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline">
            {{--  Anything you want  --}}
        </div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">InfinityTech Communication Pvt Ltd</a>.</strong>
    </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->
@vite('resources/js/app.js')
<!-- AdminLTE App -->
<script src="{{ asset('js/adminlte.min.js') }}" defer></script>

{{-- SweetAlert2 toasts with custom UI --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {

        // Base config for all toasts
        const toastConfig = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast',
            },
            showClass: {
                popup: 'swal2-show',
            },
            hideClass: {
                popup: 'swal2-hide',
            },
        };

        // ---- Success flash ----
        @if (session('success'))
            Swal.fire({
                ...toastConfig,
                icon: 'success',
                title: "{{ session('success') }}",
            });
        @endif

        // ---- Error flash ----
        @if (session('error'))
            Swal.fire({
                ...toastConfig,
                icon: 'error',
                title: "{{ session('error') }}",
            });
        @endif

        // ---- Validation errors ----
        @if ($errors->any())
            let errorHtml = '<ul style="text-align: left; margin: 0; padding-left: 1.2rem;">';
            @foreach ($errors->all() as $error)
                errorHtml += '<li>{{ $error }}</li>';
            @endforeach
            errorHtml += '</ul>';

            Swal.fire({
                ...toastConfig,
                icon: 'warning',
                title: 'Please fix the following:',
                html: errorHtml,
                timer: 5000,
            });
        @endif

    });
</script>

@yield('scripts')
</body>
</html>
