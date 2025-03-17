<!-- Sidebar -->
<div class="sidebar">
    <!-- Sidebar user panel (optional) -->
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="info">
            <a href="{{ route('profile.show') }}" class="d-block">{{ Auth::user()->name }}</a>
        </div>
    </div>

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            @foreach(\App\Models\Menu::where('parent_id', '=', null)->get() as $menu)
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon fas {{$menu->icon}} nav-icon"></i>
                        <p>
                            {{$menu->title}}
                            @if ($menu->children->isNotEmpty())
                                <i class="fas fa-angle-left right"></i>
                            @endif
                            {{--  <i class="fas fa-angle-left right"></i>  --}}
                        </p>
                    </a>
                    @if ($menu->children->isNotEmpty())

                    <ul class="nav nav-treeview" style="display: none;">
                            @foreach($menu->children as $child)
                                <li class="nav-item">
                                    <a href="{{route('$child->url')}}" class="nav-link">
                                        <i class="far {{$child->icon}} nav-icon"></i>
                                        <p>{{$child->title}}</p>
                                    </a>
                                </li>
                            @endforeach
                    </ul>
                </li>
            @endforeach








            <li class="nav-item">
                <a href="{{ route('home') }}" class="nav-link">
                    <i class="nav-icon fas fa-tachometer-alt"></i>
                    <p>
                        {{ __('Dashboard') }}
                    </p>
                </a>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link">
                    <i class="nav-icon fas fa-users nav-icon"></i>
                    <p>
                        Operators
                        <i class="fas fa-angle-left right"></i>
                    </p>
                </a>

                <ul class="nav nav-treeview" style="display: none;">
                    <li class="nav-item">
                        <a href="{{route('users.index')}}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Users</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('roles.index')}}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Roles</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{route('permissions.index')}}" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Permission</p>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
