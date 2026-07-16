<!-- Sidebar -->
<div class="sidebar">

    <!-- Sidebar Menu -->
    <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            @foreach(\App\Models\Menu::where('parent_id', null)->with('children')->get() as $menu)
                <li class="nav-item {{ $menu->children->isNotEmpty() ? 'has-treeview' : '' }}">
                    <a href="{{ is_string($menu->url) ? url($menu->url) : '#' }}" class="nav-link">
                        <i class="nav-icon fas {{ $menu->icon }}"></i>
                        <p>
                            {{ $menu->title }}
                            @if ($menu->children->isNotEmpty())
                                <i class="fas fa-angle-left right"></i>
                            @endif
                        </p>
                    </a>

                    @if ($menu->children->isNotEmpty())
                        <ul class="nav nav-treeview pl-3">
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
</div>
