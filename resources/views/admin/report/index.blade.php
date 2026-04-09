@extends('admin.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/js/libs/daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('report.title') }}</h3>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">

                    {{-- Форма генерации --}}
                    @can(App\Models\Permission::PERMISSION_REPORT_GENERATE)
                    <form method="POST" action="{{ route('admin.report.generate') }}" class="mb-4">
                        @csrf
                        <div class="row gx-3 gy-2 align-items-end">
                            <div class="col-11">
                                <div class="row gx-3 gy-2 align-items-end">
                                    @if($isRoot)
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('dashboard.filter.users') }}</label>
                                        <select class="form-select" name="user_id[]" multiple size="1" id="filterUsers">
                                            @foreach($allUsers as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @endif
                                    <div class="{{ $isRoot ? 'col-md-2' : 'col-md-3' }}">
                                        <label class="form-label">{{ __('dashboard.filter.activities') }}</label>
                                        <select class="form-select" name="activity_id[]" multiple size="1" id="filterActivities">
                                            @foreach($allActivities as $activity)
                                                <option value="{{ $activity->id }}">{{ $activity->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="{{ $isRoot ? 'col-md-2' : 'col-md-3' }}">
                                        <label class="form-label">{{ __('dashboard.filter.shifts') }}</label>
                                        <select class="form-select" name="shift[]" multiple size="1" id="filterShifts">
                                            @foreach($allShifts as $shift)
                                                <option value="{{ $shift['id'] }}">{{ $shift['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="{{ $isRoot ? 'col-md-2' : 'col-md-3' }}">
                                        <label class="form-label">{{ __('dashboard.filter.departments') }}</label>
                                        <select class="form-select" name="department[]" multiple size="1" id="filterDepartments">
                                            @foreach($allDepartments as $dept)
                                                <option value="{{ $dept }}">{{ $dept }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">{{ __('report.period') }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="dateRangePicker" autocomplete="off">
                                            <input type="hidden" name="date_from" id="inputDateFrom">
                                            <input type="hidden" name="date_to" id="inputDateTo">
                                            <span class="input-group-text"><em class="icon ni ni-calendar"></em></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-1 text-center">
                                <button type="submit" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" title="{{ __('report.generate') }}">
                                    <em class="icon ni ni-download"></em>
                                </button>
                            </div>
                        </div>
                    </form>
                    @endcan

                    <div class="alert alert-info alert-dim">
                        <em class="icon ni ni-info"></em>
                        <span>{{ __('report.limit_message', ['count' => $maxReports]) }}</span>
                    </div>

                    {{-- Список отчётов --}}
                    @if($reports->isEmpty())
                        <p class="text-soft mt-3">{{ __('report.empty') }}</p>
                    @else
                        <div class="card-inner p-0 mt-3">
                            <div class="nk-tb-list nk-tb-ulist">
                                <div class="nk-tb-item nk-tb-head">
                                    <div class="nk-tb-col"><span>#</span></div>
                                    <div class="nk-tb-col"><span>{{ __('report.list.period') }}</span></div>
                                    <div class="nk-tb-col"><span>{{ __('report.list.status') }}</span></div>
                                    <div class="nk-tb-col tb-col-md"><span>{{ __('report.list.created_by') }}</span></div>
                                    <div class="nk-tb-col tb-col-md"><span>{{ __('report.list.created_at') }}</span></div>
                                    <div class="nk-tb-col text-end"><em class="icon ni ni-setting"></em></div>
                                </div>

                                @foreach($reports as $report)
                                    <div class="nk-tb-item">
                                        <div class="nk-tb-col">
                                            <span>{{ $loop->iteration }}</span>
                                        </div>
                                        <div class="nk-tb-col">
                                            <span>{{ $report->date_from->format('d.m.Y') }} &mdash; {{ $report->date_to->format('d.m.Y') }}</span>
                                        </div>
                                        <div class="nk-tb-col">
                                            @switch($report->status)
                                                @case(\App\Models\Report::STATUS_PENDING)
                                                    <span class="badge bg-warning">{{ __('report.status.pending') }}</span>
                                                    @break
                                                @case(\App\Models\Report::STATUS_PROCESSING)
                                                    <span class="badge bg-info">{{ __('report.status.processing') }}</span>
                                                    @break
                                                @case(\App\Models\Report::STATUS_COMPLETED)
                                                    <span class="badge bg-success">{{ __('report.status.completed') }}</span>
                                                    @break
                                                @case(\App\Models\Report::STATUS_FAILED)
                                                    <span class="badge bg-danger">{{ __('report.status.failed') }}</span>
                                                    @break
                                            @endswitch
                                        </div>
                                        <div class="nk-tb-col tb-col-md">
                                            <span>{{ $report->user->name ?? '—' }}</span>
                                        </div>
                                        <div class="nk-tb-col tb-col-md">
                                            <span>{{ $report->created_at->format('d.m.Y H:i') }}</span>
                                        </div>
                                        <div class="nk-tb-col nk-tb-col-tools">
                                            <ul class="nk-tb-actions gx-1">
                                                @if($report->status === \App\Models\Report::STATUS_COMPLETED)
                                                <li>
                                                    <a href="{{ route('admin.report.download', $report->id) }}" class="btn btn-icon btn-trigger" data-bs-toggle="tooltip" title="{{ __('report.download') }}">
                                                        <em class="icon ni ni-download"></em>
                                                    </a>
                                                </li>
                                                @endif
                                                @if($report->status === \App\Models\Report::STATUS_FAILED && $report->file_path)
                                                <li>
                                                    <a href="{{ route('admin.report.download', $report->id) }}" class="btn btn-icon btn-trigger" data-bs-toggle="tooltip" title="{{ __('report.download_error') }}">
                                                        <em class="icon ni ni-alert text-danger"></em>
                                                    </a>
                                                </li>
                                                @endif
                                                @if($report->status !== \App\Models\Report::STATUS_PROCESSING)
                                                <li>
                                                    <form method="POST" action="{{ route('admin.report.destroy', $report->id) }}" class="d-inline" onsubmit="return confirm('{{ __('report.confirm_delete') }}')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-icon btn-trigger" data-bs-toggle="tooltip" title="{{ __('common.item_delete') }}">
                                                            <em class="icon ni ni-trash"></em>
                                                        </button>
                                                    </form>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endforeach
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
(function () {
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

    var startDate = moment().subtract(29, 'days');
    var endDate = moment();

    $('#dateRangePicker').daterangepicker({
        locale: locale,
        ranges: ranges,
        startDate: startDate,
        endDate: endDate,
        opens: 'left',
    }, function (start, end) {
        $('#inputDateFrom').val(start.format('DD.MM.YYYY'));
        $('#inputDateTo').val(end.format('DD.MM.YYYY'));
    });

    // Установить начальные значения
    $('#inputDateFrom').val(startDate.format('DD.MM.YYYY'));
    $('#inputDateTo').val(endDate.format('DD.MM.YYYY'));

    // Select2 для фильтров
    if (typeof $.fn.select2 !== 'undefined') {
        $('#filterUsers').select2({ placeholder: '{{ __('dashboard.filter.users') }}', allowClear: false, width: '100%' });
        $('#filterActivities').select2({ placeholder: '{{ __('dashboard.filter.activities') }}', allowClear: false, width: '100%' });
        $('#filterShifts').select2({ placeholder: '{{ __('dashboard.filter.shifts') }}', allowClear: false, width: '100%' });
        $('#filterDepartments').select2({ placeholder: '{{ __('dashboard.filter.departments') }}', allowClear: false, width: '100%' });
    } else {
        $('#filterUsers, #filterActivities, #filterShifts, #filterDepartments').attr('size', 1).css('height', 'auto');
    }
}());
</script>
@endpush
