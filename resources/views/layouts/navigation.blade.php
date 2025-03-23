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
            @foreach(\App\Models\Menu::where('parent_id', null)->get() as $menu)
                <li class="nav-item">
                    <a href="{{ is_string($menu->url) ? url($menu->url) : '#'  }}" class="nav-link">
                        <i class="nav-icon fas {{ $menu->icon }} nav-icon"></i>
                        <p>
                            {{ $menu->title }}
                            @if ($menu->children->isNotEmpty())
                                <i class="fas fa-angle-left right"></i>
                            @endif
                        </p>
                    </a>

                    @if ($menu->children->isNotEmpty())
                        <ul class="nav nav-treeview" style="display: none;">
                            @foreach($menu->children as $child)
                                <li class="nav-item">
                                    <a href="{{ url($child->url) }}" class="nav-link">
                                        <i class="far fa-arrow-alt-circle-right nav-icon"></i>
                                        <p>{{ $child->title }}</p>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>
    <!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
