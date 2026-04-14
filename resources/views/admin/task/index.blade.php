@extends('admin.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/js/libs/daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('task.title') }}</h3>
                </div>
                <div class="nk-block-head-content">
                    <div class="nk-block-head-content">
                        <a href="{{ route('admin.task.create') }}" class="btn btn-primary btn-icon d-sm-none"><em class="icon ni ni-plus"></em></a>
                        <a href="{{ route('admin.task.create') }}" class="btn btn-primary d-none d-sm-inline-flex"><em class="icon ni ni-plus"></em><span>{{ __('task.add') }}</span></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner position-relative card-tools-toggle">
                    <div class="d-sm-none text-end">
                        <a href="#" class="btn btn-sm btn-icon btn-trigger" data-bs-toggle="collapse" data-bs-target="#taskFilters" id="taskFiltersToggle">
                            <em class="icon ni ni-search"></em>
                        </a>
                    </div>
                    <div class="collapse d-sm-block{{ request()->hasAny(['user_id', 'activity_id', 'status', 'shift', 'date_from', 'date_to']) ? ' show' : '' }}" id="taskFilters">
                        <div class="card-tools w-100 pt-3 pt-sm-0">
                            <form method="GET" action="{{ route(request()->route()->getName(), [request()->get('page')]) }}">
                                <div class="row">
                                    <div class="col-12 col-md-9">
                                        <div class="row gx-6 gy-3">
                                            @if($canViewAll)
                                                <div class="col-12 col-sm-6 col-md-3">
                                                    <select class="form-select" name="user_id">
                                                        <option value="">{{ __('task.search.user') }}</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            @endif
                                            <div class="col-12 col-sm-6 col-md-3">
                                                <select class="form-select" name="activity_id">
                                                    <option value="">{{ __('task.search.activity') }}</option>
                                                    @foreach($activities as $activity)
                                                        <option value="{{ $activity->id }}" @selected(request('activity_id') == $activity->id)>{{ $activity->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-2">
                                                <select class="form-select" name="shift">
                                                    <option value="">{{ __('task.search.shift') }}</option>
                                                    @foreach($allShifts as $shift)
                                                        <option value="{{ $shift['id'] }}" @selected(request('shift') == $shift['id'])>{{ $shift['name'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12 col-sm-6 col-md-3">
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control" id="dateRangePicker" autocomplete="off" placeholder="{{ __('task.search.date') }}"
                                                        value="{{ $dateFrom && $dateTo ? $dateFrom->format('d.m.Y') . ' - ' . $dateTo->format('d.m.Y') : '' }}">
                                                    <input type="hidden" name="date_from" id="inputDateFrom" value="{{ $dateFrom ? $dateFrom->format('d.m.Y') : '' }}">
                                                    <input type="hidden" name="date_to" id="inputDateTo" value="{{ $dateTo ? $dateTo->format('d.m.Y') : '' }}">
                                                    <span class="input-group-text"><em class="icon ni ni-calendar"></em></span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-12 col-md-3 mt-3 mt-md-0">
                                        <div class="d-flex justify-content-end">
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
                    </div>
                </div><!-- .card-inner -->
                <div class="card-inner-group">
                    <div class="card-inner p-0">
                        <div class="nk-tb-list nk-tb-ulist">
                            <div class="nk-tb-item nk-tb-head">
                                <div class="nk-tb-col tb-col-sm"><span>#</span></div>
                                @if($canViewAll)
                                <div class="nk-tb-col tb-col-md"><span>{{ __('task.list.head.user') }}</span></div>
                                @endif
                                <div class="nk-tb-col"><span>{{ __('task.list.head.activity') }}</span></div>
                                <div class="nk-tb-col"><span>{{ __('task.list.head.product_count') }}</span></div>
                                <div class="nk-tb-col tb-col-sm"><span>{{ __('task.list.head.runtime') }}</span></div>
                                <div class="nk-tb-col"><span>{{ __('task.list.head.shift') }}</span></div>
                                <div class="nk-tb-col"><span>{{ __('task.list.head.work_day') }}</span></div>
                                <div class="nk-tb-col text-end"><em class="icon ni ni-setting"></em></div>
                            </div>

                            @foreach($paginator as $item)
                                <div class="nk-tb-item">
                                    <div class="nk-tb-col tb-col-sm">
                                        <span>{{ $loop->iteration }}</span>
                                    </div>
                                    @if($canViewAll)
                                    <div class="nk-tb-col tb-col-md">
                                        <a href="{{ route('admin.task.edit', $item->id) }}"><span>{{ $item->user->name ?? '—' }}</span></a>
                                    </div>
                                    @endif
                                    <div class="nk-tb-col">
                                        <a href="{{ route('admin.task.edit', $item->id) }}"><span>{{ $item->activity->name ?? '—' }}</span><em class="icon ni ni-edit"></em></a>
                                    </div>
                                    <div class="nk-tb-col">
                                        <span>{{ $item->product_count }}</span>
                                    </div>
                                    <div class="nk-tb-col tb-col-sm">
                                        <span>{{ $item->runtime }}</span>
                                    </div>
                                    <div class="nk-tb-col">
                                        <span data-bs-toggle="tooltip" data-bs-placement="top"
                                            title="{{ $item->work_start ? \Carbon\Carbon::parse($item->work_start)->format('H:i') : '' }} – {{ $item->work_finish ? \Carbon\Carbon::parse($item->work_finish)->format('H:i') : '' }}">
                                            {{ $item->shift ?? '—' }}
                                        </span>
                                    </div>
                                    <div class="nk-tb-col">
                                        <span>{{ $item->work_day ? \Carbon\Carbon::parse($item->work_day)->format('d.m.Y') : $item->created_at->format('d.m.Y') }}</span>
                                    </div>
                                    <div class="nk-tb-col nk-tb-col-tools">
                                        <ul class="nk-tb-actions gx-1">
                                            <li>
                                                <div class="drodown">
                                                    <a href="#" class="dropdown-toggle btn btn-icon btn-trigger" data-bs-toggle="dropdown"><em class="icon ni ni-more-h"></em></a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <ul class="link-list-opt no-bdr">
                                                            <li><a href="{{ route('admin.task.edit', $item->id) }}"><em class="icon ni ni-edit"></em><span>{{ __('common.item_edit') }}</span></a></li>
                                                            <li>
                                                                <form method="POST" action="{{ route('admin.task.destroy', $item->id) }}">
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

@push('scripts')
<script src="{{ asset('assets/js/libs/daterangepicker/moment.min.js') }}"></script>
<script src="{{ asset('assets/js/libs/daterangepicker/daterangepicker.js') }}"></script>
<script>
$(function () {
    var $filters = $('#taskFilters');
    var $icon = $('#taskFiltersToggle .icon');
    $filters.on('show.bs.collapse', function () {
        $icon.removeClass('ni-search').addClass('ni-cross');
    });
    $filters.on('hide.bs.collapse', function () {
        $icon.removeClass('ni-cross').addClass('ni-search');
    });
    if ($filters.hasClass('show')) {
        $icon.removeClass('ni-search').addClass('ni-cross');
    }

    // Daterangepicker
    var locale = {
        format: 'DD.MM.YYYY',
        applyLabel: '{{ __('common.daterangepicker.apply') }}',
        cancelLabel: '{{ __('common.daterangepicker.cancel') }}',
        fromLabel: '{{ __('common.daterangepicker.from') }}',
        toLabel: '{{ __('common.daterangepicker.to') }}',
        customRangeLabel: '{{ __('common.daterangepicker.custom_range') }}',
        daysOfWeek: @json(array_values(__('common.daterangepicker.days'))),
        monthNames: @json(array_values(__('common.daterangepicker.months'))),
        firstDay: 1,
    };

    var ranges = {};
    ranges['{{ __('common.daterangepicker.ranges.yesterday') }}'] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    ranges['{{ __('common.daterangepicker.ranges.week') }}'] = [moment().subtract(6, 'days'), moment()];
    ranges['{{ __('common.daterangepicker.ranges.month') }}'] = [moment().subtract(29, 'days'), moment()];
    ranges['{{ __('common.daterangepicker.ranges.quarter') }}'] = [moment().subtract(2, 'months').startOf('month'), moment()];
    ranges['{{ __('common.daterangepicker.ranges.year') }}'] = [moment().startOf('year'), moment()];

    var hasDate = $('#inputDateFrom').val() && $('#inputDateTo').val();

    $('#dateRangePicker').daterangepicker({
        locale: locale,
        ranges: ranges,
        startDate: hasDate ? moment($('#inputDateFrom').val(), 'DD.MM.YYYY') : moment(),
        endDate: hasDate ? moment($('#inputDateTo').val(), 'DD.MM.YYYY') : moment(),
        autoUpdateInput: false,
        opens: 'left',
    }, function (start, end) {
        $('#dateRangePicker').val(start.format('DD.MM.YYYY') + ' - ' + end.format('DD.MM.YYYY'));
        $('#inputDateFrom').val(start.format('DD.MM.YYYY'));
        $('#inputDateTo').val(end.format('DD.MM.YYYY'));
    });

    if (hasDate) {
        $('#dateRangePicker').val($('#inputDateFrom').val() + ' - ' + $('#inputDateTo').val());
    }
});
</script>
@endpush
