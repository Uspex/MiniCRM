<div class="nk-sidebar nk-sidebar-fixed is-dark " data-content="sidebarMenu">
    <div class="nk-sidebar-element nk-sidebar-head">
        <div class="nk-menu-trigger">
            <a href="#" class="nk-nav-toggle nk-quick-nav-icon d-xl-none" data-target="sidebarMenu"><em class="icon ni ni-arrow-left"></em></a>
            <a href="#" class="nk-nav-compact nk-quick-nav-icon d-none d-xl-inline-flex" data-target="sidebarMenu"><em class="icon ni ni-menu"></em></a>
        </div>
        <div class="nk-sidebar-brand">
            <a href="{{ route('admin.esl.index') }}" class="logo-link nk-sidebar-logo">
                <img class="logo-light logo-img" src="{{ asset('images/logo.png') }}" srcset="{{ asset('images/logo2x.png 2x') }}" alt="logo">
            </a>
        </div>
    </div><!-- .nk-sidebar-element -->


    <div class="nk-sidebar-element nk-sidebar-body">
        <div class="nk-sidebar-content">
            <div class="nk-sidebar-menu" data-simplebar>
                <ul class="nk-menu">
                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_BASE_STATION_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.base_station.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-stepper"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.base_station') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_ESL_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.esl.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-stepper"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.esl') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif


                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_SHOP_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.shop.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-building"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.shop') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_SHOWCASE_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.showcase.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-view-x6-alt"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.showcase') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_PRODUCT_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.product.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-toasts"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.product') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_TEMPLATE_LIST))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.template.index') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-color-palette"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.template') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_IMPORT_CREATE))
                        <li class="nk-menu-item">
                            <a href="{{ route('admin.import.create') }}" class="nk-menu-link">
                                <span class="nk-menu-icon"><em class="icon ni ni-upload-cloud"></em></span>
                                <span class="nk-menu-text">{{ __('common.menu.import') }}</span>
                            </a>
                        </li><!-- .nk-menu-item -->
                    @endif

                    @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_COMPUTER_VISION))
                        <li class="nk-menu-item has-sub">
                            <a href="#" class="nk-menu-link nk-menu-toggle">
                                <span class="nk-menu-icon"><em class="icon ni ni-video"></em></span>
                                <span class="nk-menu-text">{{ __('computer_vision.title') }}</span>
                            </a>
                            <ul class="nk-menu-sub">
                                @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_COMPUTER_VISION_REPORT_LIST))
                                    <li class="nk-menu-item">
                                        <a href="{{ route('admin.computer_vision_report.index') }}" class="nk-menu-link"><em class="icon ni ni-calendar-alt-fill"></em><span class="nk-menu-text">{{ __('computer_vision.menu.report') }}</span></a>
                                    </li>
                                @endif
                                @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_COMPUTER_VISION_LOCATION_LIST))
                                    <li class="nk-menu-item">
                                        <a href="{{ route('admin.computer_vision_location.index') }}" class="nk-menu-link"><em class="icon ni ni-map-pin-fill"></em><span class="nk-menu-text">{{ __('computer_vision.menu.location') }}</span></a>
                                    </li>
                                @endif
                                @if(auth()->user()->hasPermissionTo(\App\Models\Permission::PERMISSION_COMPUTER_VISION_EVENT_RATING_LIST))
                                    <li class="nk-menu-item">
                                        <a href="{{ route('admin.computer_vision_event_rating.index') }}" class="nk-menu-link"><em class="icon ni ni-property"></em><span class="nk-menu-text">{{ __('computer_vision.menu.event_rating') }}</span></a>
                                    </li>
                                @endif
                            </ul><!-- .nk-menu-sub -->
                        </li><!-- .nk-menu-item -->
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
