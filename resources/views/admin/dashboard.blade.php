@extends('admin.layouts.app')

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/js/libs/daterangepicker/daterangepicker.css') }}">
@endpush

@section('content')

    <div class="nk-content-body">
        <div class="nk-block-head nk-block-head-sm">
            <div class="nk-block-between g-3">
                <div class="nk-block-head-content">
                    <h3 class="nk-block-title page-title">{{ __('dashboard.title') }}</h3>
                </div>
            </div>
        </div>

        <div class="nk-block">
            <div class="card card-bordered">
                <div class="card-inner">

                    {{-- Фильтры --}}
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="mb-4">
                        <div class="row gx-3 gy-2 align-items-end">
                            @if($isRoot)
                            <div class="col-md-3">
                                <label class="form-label">{{ __('dashboard.filter.users') }}</label>
                                <select class="form-select" name="user_id[]" multiple size="1" id="filterUsers">
                                    @foreach($allUsers as $user)
                                        <option value="{{ $user->id }}"
                                            @if(in_array($user->id, $selectedUserIds)) selected @endif>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="{{ $isRoot ? 'col-md-3' : 'col-md-4' }}">
                                <label class="form-label">{{ __('dashboard.filter.activities') }}</label>
                                <select class="form-select" name="activity_id[]" multiple size="1" id="filterActivities">
                                    @foreach($allActivities as $activity)
                                        <option value="{{ $activity->id }}"
                                            @if(in_array($activity->id, $selectedActivityIds)) selected @endif>
                                            {{ $activity->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="{{ $isRoot ? 'col-md-4' : 'col-md-5' }}">
                                <label class="form-label">{{ __('dashboard.filter.period') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="dateRangePicker" autocomplete="off"
                                        value="{{ request('date_from', $dateFrom->format('d.m.Y')) }} - {{ request('date_to', $dateTo->format('d.m.Y')) }}">
                                    <input type="hidden" name="date_from" id="inputDateFrom" value="{{ request('date_from', $dateFrom->format('d.m.Y')) }}">
                                    <input type="hidden" name="date_to" id="inputDateTo" value="{{ request('date_to', $dateTo->format('d.m.Y')) }}">
                                    <span class="input-group-text"><em class="icon ni ni-calendar"></em></span>
                                </div>
                            </div>
                            <div class="{{ $isRoot ? 'col-md-2' : 'col-md-3' }}">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-icon btn-primary me-2" title="{{ __('common.btn_search_apply') }}"><em class="icon ni ni-search"></em></button>
                                    <a href="{{ route('admin.dashboard') }}" class="btn btn-icon btn-warning" title="{{ __('common.btn_search_reset') }}"><em class="icon ni ni-reload"></em></a>
                                </div>
                            </div>
                        </div>
                    </form>

                    {{-- График --}}
                    <div class="card-title-group mb-3">
                        <div class="card-title">
                            <h6 class="title">{{ __('dashboard.chart_title') }}</h6>
                        </div>
                    </div>

                    @if(empty($chartData['datasets']))
                        <p class="text-soft">{{ __('dashboard.chart_no_data') }}</p>
                    @else
                        <div class="chart-container" style="position: relative; height: 360px;">
                            <canvas id="employeeChart"></canvas>
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

    $('#dateRangePicker').daterangepicker({
        locale: locale,
        ranges: ranges,
        startDate: moment($('#inputDateFrom').val(), 'DD.MM.YYYY'),
        endDate: moment($('#inputDateTo').val(), 'DD.MM.YYYY'),
        opens: 'left',
    }, function (start, end) {
        $('#inputDateFrom').val(start.format('DD.MM.YYYY'));
        $('#inputDateTo').val(end.format('DD.MM.YYYY'));
    });

    // Select2 для множественного выбора сотрудников и типов работ
    if (typeof $.fn.select2 !== 'undefined') {
        $('#filterUsers').select2({
            placeholder: '{{ __('dashboard.filter.users') }}',
            allowClear: true,
            width: '100%',
        });
        $('#filterActivities').select2({
            placeholder: '{{ __('dashboard.filter.activities') }}',
            allowClear: true,
            width: '100%',
        });
    } else {
        $('#filterUsers').attr('size', 1).css('height', 'auto');
        $('#filterActivities').attr('size', 1).css('height', 'auto');
    }

    // Chart.js
    var chartData = @json($chartData ?? ['labels' => [], 'datasets' => []]);

    if (!chartData.datasets || chartData.datasets.length === 0) return;

    var colors = ['#798bff','#e85347','#1ee0ac','#f4bd0e','#09c2de','#6576ff','#ff63a5','#364a63'];

    var datasets = chartData.datasets.map(function (ds, i) {
        var color = ds.color || colors[i % colors.length];
        return {
            label: ds.label,
            data: ds.data,
            borderColor: color,
            backgroundColor: color + '33',
            borderWidth: 2,
            tension: 0.3,
            fill: false,
            pointRadius: 3,
            pointHoverRadius: 5,
        };
    });

    var ctx = document.getElementById('employeeChart');
    if (!ctx) return;

    new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
            },
            scales: {
                x: {
                    display: true,
                    grid: { color: 'rgba(82,100,132,.1)' },
                    ticks: { color: '#8094ae', font: { size: 11 } },
                },
                y: {
                    display: true,
                    beginAtZero: true,
                    grid: { color: 'rgba(82,100,132,.1)' },
                    ticks: { color: '#8094ae', font: { size: 11 }, precision: 0 },
                },
            },
        },
    });

}());
</script>
@endpush
