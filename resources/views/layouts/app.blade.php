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

    {{-- SweetAlert2 CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* ---------- Toast styling ---------- */
        .swal2-toast {
            border-radius: 8px !important;
            box-shadow: 0 10px 25px rgba(0,0,0,0.12), 0 5px 12px rgba(0,0,0,0.08) !important;
            padding: 12px 16px !important;
            border-left: 5px solid #ccc;
            font-family: 'Source Sans Pro', sans-serif;
            position: relative;
        }
        .swal2-toast.swal2-success { border-left-color: #28a745; }
        .swal2-toast.swal2-error   { border-left-color: #dc3545; }
        .swal2-toast.swal2-warning { border-left-color: #ffc107; }

        .swal2-toast .swal2-title {
            font-size: 0.95rem !important;
            font-weight: 600;
            margin: 0 !important;
            padding: 0 30px 0 0 !important; /* extra space for close button */
            color: #1a1a2e;
        }
        .swal2-toast .swal2-html-container {
            font-size: 0.85rem !important;
            margin: 0 !important;
            padding: 0 30px 0 0 !important;
            color: #4a4a6a;
        }

        /* ---- Close button: perfectly positioned ---- */
        .swal2-toast .swal2-close {
            position: absolute !important;
            top: 8px !important;
            right: 10px !important;
            width: auto !important;
            height: auto !important;
            font-size: 1.6rem !important;
            font-weight: 300;
            color: #6c757d !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 4px 10px !important;
            margin: 0 !important;
            cursor: pointer;
            transition: color 0.15s ease, transform 0.1s ease;
            line-height: 1;
            border-radius: 4px;
        }
        .swal2-toast .swal2-close:hover {
            color: #343a40 !important;
            background: rgba(0,0,0,0.05) !important;
            transform: scale(1.1);
        }
        .swal2-toast .swal2-close:active {
            transform: scale(0.95);
        }

        /* Progress bar */
        .swal2-timer-progress-bar { background: rgba(0,0,0,0.08) !important; }

        /* Animations */
        .swal2-show {
            animation: swal2-slide-in 0.3s ease-out forwards !important;
        }
        .swal2-hide {
            animation: swal2-slide-out 0.2s ease-in forwards !important;
        }
        @keyframes swal2-slide-in {
            0% { transform: translateX(100%); opacity: 0; }
            100% { transform: translateX(0); opacity: 1; }
        }
        @keyframes swal2-slide-out {
            0% { transform: translateX(0); opacity: 1; }
            100% { transform: translateX(100%); opacity: 0; }
        }

        .swal2-toast .swal2-icon {
            width: 2em !important;
            height: 2em !important;
            margin: 0 10px 0 0 !important;
        }
        .swal2-toast .swal2-icon-content { font-size: 1.4em !important; }
        .swal2-toast .swal2-html-container ul {
            margin: 0.25rem 0 0 0 !important;
            padding-left: 1.2rem !important;
        }
        .swal2-toast .swal2-html-container li { margin-bottom: 0.1rem; }

        /* ---------- Search results dropdown ---------- */
        .search-results-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            background: #fff;
            border-radius: 0 0 8px 8px;
            box-shadow: 0 12px 30px rgba(0,0,0,0.15), 0 4px 10px rgba(0,0,0,0.05);
            max-height: 380px;
            overflow-y: auto;
            margin-top: 2px;
            padding: 6px 0;
            min-width: 280px;
            border: 1px solid rgba(0,0,0,0.08);
            border-top: none;
        }
        .search-results-dropdown .result-item {
            display: block;
            padding: 8px 16px;
            color: #1a1a2e;
            text-decoration: none;
            transition: background 0.15s ease;
            border-left: 3px solid transparent;
            cursor: pointer;
        }
        .search-results-dropdown .result-item:hover {
            background: #f0f4ff;
            border-left-color: #3b7ddd;
        }
        .search-results-dropdown .result-item .info .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1a1a2e;
        }
        .search-results-dropdown .dropdown-empty,
        .search-results-dropdown .dropdown-loading {
            padding: 20px 16px;
            text-align: center;
            color: #6c757d;
            font-size: 0.9rem;
        }
        .search-results-dropdown .dropdown-loading .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #e9ecef;
            border-top-color: #3b7ddd;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            margin-right: 8px;
            vertical-align: middle;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .search-results-dropdown::-webkit-scrollbar {
            width: 5px;
        }
        .search-results-dropdown::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        .search-results-dropdown::-webkit-scrollbar-thumb {
            background: #c1c7cd;
            border-radius: 10px;
        }
        .search-results-dropdown::-webkit-scrollbar-thumb:hover {
            background: #a0a7ae;
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

        {{-- Search Form --}}
        <form class="form-inline ml-3" action="{{ route('customers.index') }}" method="GET" autocomplete="off">
            <div class="input-group input-group-sm" style="position: relative;">
                <input class="form-control form-control-navbar"
                       type="search"
                       name="q"
                       id="searchInput"
                       placeholder="Search customers..."
                       value="{{ request('q') }}"
                       autocomplete="off"
                       aria-label="Search customers">

                <div class="input-group-append">
                    <button class="btn btn-navbar" type="submit" id="searchButton">
                        <i class="fas fa-search"></i>
                    </button>
                </div>

                <div id="searchResults" class="search-results-dropdown" style="display: none;"></div>
            </div>
        </form>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">

            <!-- Notifications -->
            <li class="nav-item dropdown">
                <a class="nav-link" data-bs-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    @if($unreadCount > 0)
                        <span class="badge badge-warning navbar-badge">{{ $unreadCount }}</span>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <span class="dropdown-item dropdown-header">{{ $unreadCount }} Notifications</span>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('activities.markAllRead') }}" class="dropdown-item text-center text-primary">Mark all as read</a>
                    <div class="dropdown-divider"></div>
                    @forelse($activities as $activity)
                        <a href="{{ route('activity.read', $activity->id) }}"
                           class="dropdown-item {{ $activity->is_read ? '' : 'bg-light' }}">
                            <i class="{{ $activity->icon ?? 'fas fa-bell mr-2' }}"></i>
                            {{ $activity->title }}
                            <span class="float-right text-muted text-sm">{{ $activity->created_at->diffForHumans() }}</span>
                        </a>
                        <div class="dropdown-divider"></div>
                    @empty
                        <span class="dropdown-item text-center text-muted">No notifications</span>
                    @endforelse
                    <a href="{{ route('activities.index') }}" class="dropdown-item dropdown-footer">View All</a>
                </div>
            </li>

            <!-- User dropdown -->
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

    <!-- Main Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <a href="#" class="brand-link">
            <img src="{{ asset('#') }}" alt=""
                 class="brand-image img-circle elevation-3"
                 style="opacity: .8">
            <span class="brand-text font-weight-light">InfinityTech</span>
        </a>
        @include('layouts.navigation')
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <div class="container-fluid pt-3">
            @yield('content')
        </div>
    </div>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <div class="p-3">
            <h5>Title</h5>
            <p>Sidebar content</p>
        </div>
    </aside>

    <!-- Main Footer -->
    <footer class="main-footer">
        <div class="float-right d-none d-sm-inline"></div>
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">InfinityTech Communication Pvt Ltd</a>.</strong>
    </footer>
</div>

<!-- REQUIRED SCRIPTS -->
@vite('resources/js/app.js')
<script src="{{ asset('js/adminlte.min.js') }}" defer></script>

{{-- SweetAlert2 Toasts with Close Button --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toastConfig = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            showCloseButton: true,
            closeButtonHtml: '&times;',
            timer: 4000,
            timerProgressBar: true,
            customClass: {
                popup: 'swal2-toast',
                closeButton: 'swal2-toast-close'
            },
            showClass: { popup: 'swal2-show' },
            hideClass: { popup: 'swal2-hide' },
        };

        @if (session('success'))
            Swal.fire({ ...toastConfig, icon: 'success', title: "{{ session('success') }}" });
        @endif
        @if (session('error'))
            Swal.fire({ ...toastConfig, icon: 'error', title: "{{ session('error') }}" });
        @endif
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

{{-- AJAX Search Script --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const resultsContainer = document.getElementById('searchResults');

        let debounceTimer = null;
        let abortController = null;
        let currentQuery = '';
        let selectedIndex = -1;

        function getSearchUrl(query) {
            const url = new URL('{{ route("customers.search") }}', window.location.origin);
            url.searchParams.set('q', query.trim());
            return url.toString();
        }

        function showLoading() {
            resultsContainer.innerHTML = `
                    <div class="dropdown-loading">
                        <span class="spinner"></span> Searching...
                    </div>
                `;
            resultsContainer.style.display = 'block';
        }

        function showEmpty(query) {
            resultsContainer.innerHTML = `
                    <div class="dropdown-empty">
                        <i class="fas fa-search" style="opacity:0.4;margin-right:6px;"></i>
                        No customers found for "<strong>${escapeHtml(query)}</strong>"
                    </div>
                `;
            resultsContainer.style.display = 'block';
        }

        function showError(message) {
            resultsContainer.innerHTML = `
                    <div class="dropdown-empty text-danger">
                        <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i>
                        ${escapeHtml(message)}
                    </div>
                `;
            resultsContainer.style.display = 'block';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function renderResults(data) {
            if (!data || data.length === 0) {
                showEmpty(currentQuery);
                return;
            }

            let html = '';
            data.forEach((customer, index) => {
                let displayName = customer.name;
                if (customer.username) {
                    displayName += ` (${customer.username})`;
                }

                html += `
                        <a href="${customer.url || '#'}" class="result-item" data-index="${index}">
                            <div class="info">
                                <div class="name">${escapeHtml(displayName)}</div>
                            </div>
                        </a>
                    `;
            });

            resultsContainer.innerHTML = html;
            resultsContainer.style.display = 'block';
            selectedIndex = -1;
        }

        function performSearch(query) {
            const trimmed = query.trim();
            if (trimmed.length < 2) {
                resultsContainer.style.display = 'none';
                return;
            }

            currentQuery = trimmed;

            if (abortController) abortController.abort();
            abortController = new AbortController();

            showLoading();

            fetch(getSearchUrl(trimmed), {
                    signal: abortController.signal,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            let message = 'Server error ' + response.status;
                            try {
                                const json = JSON.parse(text);
                                if (json.message) message = json.message;
                                else if (json.error) message = json.error;
                            } catch (e) {
                                message = text.substring(0, 100);
                            }
                            throw new Error(message);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        showError(data.error);
                        return;
                    }
                    const results = data.data || data;
                    renderResults(results);
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Search error:', error);
                    showError(error.message || 'Something went wrong. Please try again.');
                });
        }

        function handleInput(e) {
            const query = e.target.value;
            if (query.trim().length < 2) {
                resultsContainer.style.display = 'none';
                clearTimeout(debounceTimer);
                return;
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => performSearch(query), 300);
        }

        function handleKeydown(e) {
            const items = resultsContainer.querySelectorAll('.result-item');
            if (items.length === 0) return;

            const key = e.key;
            if (key === 'ArrowDown' || key === 'ArrowUp') {
                e.preventDefault();
                items.forEach(el => el.style.background = '');
                if (key === 'ArrowDown') {
                    selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                } else {
                    selectedIndex = Math.max(selectedIndex - 1, 0);
                }
                items[selectedIndex].style.background = '#f0f4ff';
                items[selectedIndex].scrollIntoView({ block: 'nearest' });
            }

            if (key === 'Enter') {
                if (selectedIndex >= 0 && selectedIndex < items.length) {
                    e.preventDefault();
                    const link = items[selectedIndex];
                    if (link) window.location.href = link.getAttribute('href');
                }
            }

            if (key === 'Escape') {
                resultsContainer.style.display = 'none';
                searchInput.blur();
            }
        }

        function handleBlur() {
            setTimeout(() => {
                const active = document.activeElement;
                if (active && active.closest('.search-results-dropdown')) return;
                resultsContainer.style.display = 'none';
            }, 180);
        }

        function handleFocus() {
            const query = searchInput.value.trim();
            if (query.length >= 2) performSearch(query);
        }

        function handleClickOutside(e) {
            const wrapper = searchInput.closest('.input-group');
            if (wrapper && !wrapper.contains(e.target)) {
                resultsContainer.style.display = 'none';
            }
        }

        function handleResultClick(e) {
            const item = e.target.closest('.result-item');
            if (item) {
                const href = item.getAttribute('href');
                if (href && href !== '#') window.location.href = href;
            }
        }

        searchInput.addEventListener('input', handleInput);
        searchInput.addEventListener('keydown', handleKeydown);
        searchInput.addEventListener('blur', handleBlur);
        searchInput.addEventListener('focus', handleFocus);
        resultsContainer.addEventListener('click', handleResultClick);
        document.addEventListener('click', handleClickOutside);

        window.addEventListener('beforeunload', function() {
            if (abortController) abortController.abort();
            clearTimeout(debounceTimer);
        });

        const initialQuery = searchInput.value.trim();
        if (initialQuery.length >= 2) performSearch(initialQuery);
    });
</script>

@yield('scripts')
</body>
</html>
