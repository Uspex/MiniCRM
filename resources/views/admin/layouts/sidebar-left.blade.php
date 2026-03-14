<div class="nk-sidebar nk-sidebar-fixed is-dark " data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-menu-trigger">
            <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
            <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
        </div>
        <div class="nk-sidebar-brand">
            <a href="{{ route('admin.dashboard') }}" class="logo-link nk-sidebar-logo">
                <img class="logo-light logo-img" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo2x.png 2x') }}" alt="logo">
            </a>
        </div>
    </div><!-- .nk-sidebar-element -->


    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content">
            <div class="nk-sidebar-menu" data-simplebar>
                <ul class="nk-menu">
                    <li class="nk-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin.dashboard') }}" class="nk-menu-link">
                            <span class="nk-menu-icon"><em class="icon ni ni-growth-fill"></em></span>
                            <span class="nk-menu-text">{{ __('common.menu.dashboard') }}</span>
                        </a>
                    </li><!-- .nk-menu-item -->

                    @if(auth()->user()->hasAnyPermission([\App\Models\Permission::PERMISSION_TASK_LIST, \App\Models\Permission::PERMISSION_ACTIVITY_LIST]))
                        <li class="nk-menu-heading">
                            <h6 class="overline-title text-primary-alt">{{ __('common.menu.tasks') }}</h6>
                        </li><!-- .nk-menu-heading -->

                        @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_TASK_LIST))
                            <li class="nk-menu-item {{ request()->routeIs('admin.task.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.task.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-todo"></em></span>
                                    <span class="nk-menu-text">{{ __('common.menu.task') }}</span>
                                </a>
                            </li><!-- .nk-menu-item -->
                        @endif

                        @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_ACTIVITY_LIST))
                            <li class="nk-menu-item {{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
                                <a href="{{ route('admin.activity.index') }}" class="nk-menu-link">
                                    <span class="nk-menu-icon"><em class="icon ni ni-setting"></em></span>
                                    <span class="nk-menu-text">{{ __('common.menu.activity') }}</span>
                                </a>
                            </li><!-- .nk-menu-item -->
                        @endif
                    @endif

                    @if(auth()->user()->hasRole(\App\Models\Role::ROLE_ROOT))
                        <li class="nk-menu-heading">
                            <h6 class="overline-title text-primary-alt">{{ __('common.menu.user') }}</h6>
                        </li><!-- .nk-menu-heading -->

                        <li class="nk-menu-item">
                            <a href="{{ route('admin.user.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-user-group"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.user') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->

                        <li class="nk-menu-item">
                            <a href="{{ route('admin.role.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-security"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.role') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->

                        <li class="nk-menu-item">
                            <a href="{{ route('admin.permission.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-policy"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.permission') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif



                </ul><!-- .nk-menu -->
            </div><!-- .nk-sidebar-menu -->
        </div><!-- .nk-sidebar-content -->
    </div><!-- .nk-sidebar-element -->
</div>
