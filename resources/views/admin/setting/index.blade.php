@extends('admin.layouts.app')

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('setting.title') }}</h3>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner-group">
                    <div class="card-inner p-0">
                        <div class="nk-tb-list nk-tb-ulist">
                            <div class="nk-tb-item nk-tb-head">
                                <div class="nk-tb-col"><span>#</span></div>
                                <div class="nk-tb-col"><span>{{ __('setting.title') }}</span></div>
                                <div class="nk-tb-col text-end"><em class="icon ni ni-setting"></em></div>
                            </div>

                            @foreach($types as $type)
                                <div class="nk-tb-item">
                                    <div class="nk-tb-col">
                                        <span>{{ $loop->iteration }}</span>
                                    </div>
                                    <div class="nk-tb-col">
                                        <a href="{{ route('admin.setting.edit', $type) }}">
                                            <span>{{ __('setting.types.' . $type) }}</span>
                                        </a>
                                    </div>
                                    <div class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <a href="{{ route('admin.setting.edit', $type) }}" class="btn btn-icon btn-trigger" data-bs-toggle="tooltip" title="{{ __('common.item_edit') }}">
                                                    <em class="icon ni ni-edit"></em>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
