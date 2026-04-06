@extends('admin.layouts.app')

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('activity.title') }}</h3>
                </div>
                <div class="nk-block-head-content">
                    <div class="nk-block-head-content">
                        <a href="{{ route('admin.activity.create') }}" class="btn btn-primary"><em class="icon ni ni-plus"></em><span>{{ __('activity.add') }}</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner position-relative card-tools-toggle">
                    <div class="card-title-group">
                        <div class="card-tools w-100">
                            <form method="GET" action="{{ route(request()->route()->getName(), [request()->get('page')]) }}">
                                <div class="row gx-6 gy-3">
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <input class="form-control" placeholder="{{ __('activity.search.name') }}" name="name" type="text" value="{{ request()->get('name') }}">
                                    </div>
                                    <div class="col-12 col-sm-6 col-md-3">
                                        <div class="d-flex g-2 justify-content-sm-end">
                                            <a href="{{ route(request()->route()->getName()) }}" class="btn btn-sm btn-warning me-2" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('common.btn_search_reset') }}">
                                                <em class="icon ni ni-reload-alt"></em>
                                            </a>
                                            <button type="submit" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ __('common.btn_search_apply') }}">
                                                <em class="icon ni ni-search"></em>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div><!-- .card-tools -->
                    </div><!-- .card-title-group -->
                </div><!-- .card-inner -->
                <div class="card-inner-group">
                    <div class="card-inner p-0">
                        <div class="nk-tb-list nk-tb-ulist">
                            <div class="nk-tb-item nk-tb-head">
                                <div class="nk-tb-col"><span>#</span></div>
                                <div class="nk-tb-col"><span>{{ __('activity.list.head.name') }}</span></div>
                                <div class="nk-tb-col tb-col-md"><span>{{ __('activity.list.head.plan_quantity') }}</span></div>
                                <div class="nk-tb-col tb-col-md"><span>{{ __('activity.list.head.plan_time') }}</span></div>
                                <div class="nk-tb-col tb-col-sm"><span>{{ __('activity.list.head.slug') }}</span></div>
                                <div class="nk-tb-col text-end"><em class="icon ni ni-setting"></em></div>
                            </div>

                            @foreach($paginator as $item)
                                <div class="nk-tb-item">
                                    <div class="nk-tb-col tb-col-sm">
                                        <span>{{ $loop->iteration }}</span>
                                    </div>
                                    <div class="nk-tb-col tb-col-sm">
                                        <a href="{{ route('admin.activity.edit', $item->id) }}"><span>{{ $item->name }}</span></a>
                                    </div>
                                    <div class="nk-tb-col tb-col-md">
                                        <span>{{ $item->plan_quantity }}</span>
                                    </div>
                                    <div class="nk-tb-col tb-col-md">
                                        <span>{{ $item->plan_time }}</span>
                                    </div>
                                    <div class="nk-tb-col tb-col-sm">
                                        <span>{{ $item->slug }}</span>
                                    </div>
                                    <div class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li><a href="{{ route('admin.activity.edit', $item->id) }}"><em class="icon ni ni-edit"></em><span>{{ __('common.item_edit') }}</span></a></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('admin.activity.destroy', $item->id) }}">
                                                                    @method('DELETE')
                                                                    @csrf
                                                                    <button type="submit" class="btn btn-link btn-link-light"><em class="icon ni ni-trash"></em><span>{{ __('common.item_delete') }}</span></button>
                                                                </form>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if($paginator->hasPages())
                        <div class="card-inner">
                            <div class="nk-block-between-md g-3">
                                {{ $paginator->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection
