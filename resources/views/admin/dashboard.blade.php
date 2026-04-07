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
                            <div class="{{ $isRoot ? 'col-md-2' : 'col-md-3' }}">
                                <label class="form-label">{{ __('dashboard.filter.shifts') }}</label>
                                <select class="form-select" name="shift[]" multiple size="1" id="filterShifts">
                                    @foreach($allShifts as $shift)
                                        <option value="{{ $shift['id'] }}"
                                            @if(in_array($shift['id'], $selectedShifts)) selected @endif>
                                            {{ $shift['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="{{ $isRoot ? 'col-md-3' : 'col-md-3' }}">
                                <label class="form-label">{{ __('dashboard.filter.period') }}</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="dateRangePicker" autocomplete="off"
                                        value="{{ request('date_from', $dateFrom->format('d.m.Y')) }} - {{ request('date_to', $dateTo->format('d.m.Y')) }}">
                                    <input type="hidden" name="date_from" id="inputDateFrom" value="{{ request('date_from', $dateFrom->format('d.m.Y')) }}">
                                    <input type="hidden" name="date_to" id="inputDateTo" value="{{ request('date_to', $dateTo->format('d.m.Y')) }}">
                                    <span class="input-group-text"><em class="icon ni ni-calendar"></em></span>
                                </div>
                            </div>
                            <div class="col-md-1">
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

                        {{-- Таблица с данными --}}
                        <div class="mt-4 mb-4">
                            <div class="table-responsive pb-2">
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-nowrap">{{ __('task.list.head.activity') }}</th>
                                            @foreach($chartData['labels'] as $label)
                                                <th class="text-center text-nowrap">{{ $label }}</th>
                                            @endforeach
                                            <th class="text-center text-nowrap"><strong>{{ __('dashboard.table_total') }}</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $columnTotals = array_fill(0, count($chartData['labels']), 0);
                                            $columnPlanTotals = array_fill(0, count($chartData['labels']), 0);
                                            $factDatasets = array_filter($chartData['datasets'], fn($ds) => empty($ds['isPlan']));
                                            $planDataByLabel = [];
                                            foreach ($chartData['datasets'] as $ds) {
                                                if (!empty($ds['isPlan'])) {
                                                    $planDataByLabel[$ds['label']] = $ds['data'];
                                                }
                                            }
                                        @endphp
                                        @foreach($factDatasets as $dataset)
                                            @php
                                                $planKey = __('dashboard.plan_label', ['name' => $dataset['label']]);
                                                $planData = $planDataByLabel[$planKey] ?? null;
                                            @endphp
                                            <tr data-chart-row>
                                                <td class="text-nowrap"><span class="d-inline-block me-1" data-chart-color style="width:10px;height:10px;"></span> {{ $dataset['label'] }}</td>
                                                @foreach($dataset['data'] as $i => $value)
                                                    @php
                                                        $planVal = $planData[$i] ?? null;
                                                        $borderStyle = '';
                                                        $cellTitle = '';
                                                        if ($planVal && $value) {
                                                            $borderStyle = $value >= $planVal ? 'border-bottom: 2px solid #1ee0ac !important;' : 'border-bottom: 2px solid #f4bd0e !important;';
                                                            $cellTitle = __('dashboard.coefficient') . ': ' . round($value / $planVal, 2);
                                                        }
                                                    @endphp
                                                    <td class="text-center" @if($borderStyle) style="{{ $borderStyle }}" @endif @if($cellTitle) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $cellTitle }}" @endif>
                                                        @if($value || $planVal)
                                                            {{ $value ?: 0 }}@if($planVal)/{{ $planVal }}@endif
                                                        @endif
                                                    </td>
                                                    @php
                                                        $columnTotals[$i] += $value;
                                                        $columnPlanTotals[$i] += $planVal ?: 0;
                                                    @endphp
                                                @endforeach
                                                @php
                                                    $factTotal = array_sum($dataset['data']);
                                                    $planTotal = $planData ? array_sum($planData) : null;
                                                    $totalStyle = '';
                                                    $totalTitle = '';
                                                    if ($planTotal && $factTotal) {
                                                        $bgColor = $factTotal >= $planTotal ? 'rgba(30, 224, 172, 0.15)' : 'rgba(244, 189, 14, 0.15)';
                                                        $borderColor = $factTotal >= $planTotal ? '#1ee0ac' : '#f4bd0e';
                                                        $totalStyle = "background-color: {$bgColor}; border-bottom: 2px solid {$borderColor} !important;";
                                                        $totalTitle = __('dashboard.coefficient') . ': ' . round($factTotal / $planTotal, 2);
                                                    }
                                                @endphp
                                                <td class="text-center" @if($totalStyle) style="{{ $totalStyle }}" @endif @if($totalTitle) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $totalTitle }}" @endif>
                                                    <strong>{{ $factTotal }}@if($planTotal)/{{ $planTotal }}@endif</strong>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if(count($factDatasets) > 1)
                                    <tfoot>
                                        <tr>
                                            <td class="text-nowrap"><strong>{{ __('dashboard.table_total') }}</strong></td>
                                            @foreach($columnTotals as $ci => $colTotal)
                                                @php
                                                    $colPlan = $columnPlanTotals[$ci];
                                                    $colStyle = '';
                                                    $colTitle = '';
                                                    if ($colPlan && $colTotal) {
                                                        $colStyle = $colTotal >= $colPlan ? 'border-bottom: 2px solid #1ee0ac !important;' : 'border-bottom: 2px solid #f4bd0e !important;';
                                                        $colTitle = __('dashboard.coefficient') . ': ' . round($colTotal / $colPlan, 2);
                                                    }
                                                @endphp
                                                <td class="text-center" @if($colStyle) style="{{ $colStyle }}" @endif @if($colTitle) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $colTitle }}" @endif>
                                                    <strong>@if($colTotal || $colPlan){{ $colTotal ?: 0 }}@if($colPlan)/{{ $colPlan }}@endif @endif</strong>
                                                </td>
                                            @endforeach
                                            @php
                                                $grandFact = array_sum($columnTotals);
                                                $grandPlan = array_sum($columnPlanTotals);
                                                $grandStyle = '';
                                                $grandTitle = '';
                                                if ($grandPlan && $grandFact) {
                                                    $bgColor = $grandFact >= $grandPlan ? 'rgba(30, 224, 172, 0.15)' : 'rgba(244, 189, 14, 0.15)';
                                                    $borderColor = $grandFact >= $grandPlan ? '#1ee0ac' : '#f4bd0e';
                                                    $grandStyle = "background-color: {$bgColor}; border-bottom: 2px solid {$borderColor} !important;";
                                                    $grandTitle = __('dashboard.coefficient') . ': ' . round($grandFact / $grandPlan, 2);
                                                }
                                            @endphp
                                            <td class="text-center" @if($grandStyle) style="{{ $grandStyle }}" @endif @if($grandTitle) data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $grandTitle }}" @endif>
                                                <strong>{{ $grandFact }}@if($grandPlan)/{{ $grandPlan }}@endif</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                    @endif
                                </table>
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
            allowClear: false,
            width: '100%',
        });
        $('#filterActivities').select2({
            placeholder: '{{ __('dashboard.filter.activities') }}',
            allowClear: false,
            width: '100%',
        });
        $('#filterShifts').select2({
            placeholder: '{{ __('dashboard.filter.shifts') }}',
            allowClear: false,
            width: '100%',
        });
    } else {
        $('#filterUsers').attr('size', 1).css('height', 'auto');
        $('#filterActivities').attr('size', 1).css('height', 'auto');
        $('#filterShifts').attr('size', 1).css('height', 'auto');
    }

    // Chart.js
    var chartData = @json($chartData ?? ['labels' => [], 'datasets' => []]);

    if (!chartData.datasets || chartData.datasets.length === 0) return;

    function generateColor(i, total) {
        var hue = Math.round((i * 360) / total) % 360;
        return {
            border: 'hsl(' + hue + ', 75%, 62%)',
            bg:     'hsla(' + hue + ', 75%, 62%, 0.2)',
        };
    }

    // Считаем только факт-линии для генерации цветов
    var factCount = chartData.datasets.filter(function (ds) { return !ds.isPlan; }).length;
    var factIndex = 0;

    var datasets = chartData.datasets.map(function (ds) {
        if (ds.isPlan) {
            // Пунктирная линия плана — цвет предыдущей факт-линии, приглушённый
            var color = generateColor(factIndex - 1, factCount);
            return {
                label: ds.label,
                data: ds.data,
                borderColor: color.border,
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [6, 4],
                tension: 0,
                fill: false,
                pointRadius: 0,
                pointHoverRadius: 0,
                order: 1,
            };
        }

        var color = generateColor(factIndex, factCount);
        factIndex++;
        return {
            label: ds.label,
            data: ds.data,
            borderColor: color.border,
            backgroundColor: color.bg,
            borderWidth: 2,
            tension: 0.3,
            fill: false,
            pointRadius: 3,
            pointHoverRadius: 5,
            order: 0,
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
                mode: 'nearest',
                intersect: true,
            },
            plugins: {
                legend: {
                    display: false,
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

    // Раскрасить кружки в таблице цветами графика
    var rows = document.querySelectorAll('[data-chart-row]');
    for (var r = 0; r < rows.length; r++) {
        var dot = rows[r].querySelector('[data-chart-color]');
        if (dot) {
            var c = generateColor(r, rows.length);
            dot.style.backgroundColor = c.border;
        }
    }

}());
</script>
@endpush
