@extends('admin.layouts.app')

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('permission.title') }}</h3>
                </div>
                <div class="nk-block-head-content">
                    <div class="nk-block-head-content">
                        <a href="{{ route('admin.permission.create') }}" class="btn btn-primary"><em class="icon ni ni-plus"></em><span>{{ __('permission.add') }}</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                @if($paginator->count())
                    <div class="card-inner-group">
                        <div class="card-inner p-0">
                            <div class="nk-tb-list nk-tb-ulist">
                                <div class="nk-tb-item nk-tb-head">
                                    <div class="nk-tb-col"><span>#</span></div>
                                    <div class="nk-tb-col"><span>{{ __('permission.list.head.name') }}</span></div>
                                    <div class="nk-tb-col"><span>{{ __('permission.list.head.group') }}</span></div>
                                    <div class="nk-tb-col text-end"><em class="icon ni ni-setting"></em></div>
                                </div>

                                @foreach($paginator as $item)
                                    <div class="nk-tb-item">
                                        <div class="nk-tb-col tb-col-sm">
                                            <span>{{ $loop->iteration }}</span>
                                        </div>
                                        <div class="nk-tb-col tb-col-sm">
                                            <a href="{{ route('admin.permission.edit', $item->id) }}"><span>{{ $item->name  }}</span></a>
                                        </div>
                                        <div class="nk-tb-col tb-col-sm">
                                            <span>{{ $item->group  }}</span>
                                        </div>
                                        <div class="nk-tb-col nk-tb-col-tools">
                                            <ul class="nk-tb-actions gx-1">

                                                <li>
                                                    <div class="drodown">
                                                        <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                        <div class="dropdown-menu dropdown-menu-end">
                                                            <ul class="link-list-opt no-bdr">
                                                                <li><a href="{{ route('admin.permission.edit', $item->id) }}"><em class="icon ni ni-edit"></em><span>{{ __('common.item_edit') }}</span></a></li>
                                                                <li>
                                                                    <form method="POST" action="{{ route('admin.permission.destroy', $item->id) }}">
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

                @else
                    <div class="card-inner-group">
                        <div class="card-inner">
                            <div class="card-title-group">
                                <div class="card-title">
                                    <h5 class="title">{{ __('common.list_empty') }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                @endif
            </div>
        </div>
    </div>

@endsection



