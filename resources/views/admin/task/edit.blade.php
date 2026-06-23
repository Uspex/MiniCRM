@extends('admin.layouts.app')

@section('content')
    <div class="nk-content ">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between g-3">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">{{ __('task.form.edit_title') }}</h3>
                            </div>
                            <div class="nk-block-head-content">
                                <a href="{{ route('admin.task.index') }}" class="btn btn-outline-light bg-white"><em class="icon ni ni-arrow-left"></em><span>{{ __('common.back') }}</span></a>
                            </div>
                        </div>
                    </div><!-- .nk-block-head -->
                    <div class="nk-block">
                        <div class="card card-bordered">
                            <div class="card-aside-wrap">
                                <div class="card-content">
                                    <form method="POST" action="{{ route('admin.task.update', $task->id) }}">
                                        @method('PATCH')
                                        @csrf

                                        <ul class="nav nav-tabs nav-tabs-mb-icon nav-tabs-card">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#tab_general"><em class="icon ni ni-layer-fill"></em><span>{{ __('common.tab_common') }}</span></a>
                                            </li>
                                        </ul><!-- .nav-tabs -->
                                        <div class="tab-content">
                                            <div class="tab-pane active" id="tab_general">
                                                <div class="card-inner">
                                                    <div class="nk-block">
                                                        <div class="row g-3">
                                                            <div class="col-md-6 col-lg-3">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="activity_id">{{ __('task.form.fields.activity_id') }}</label>
                                                                    <select name="activity_id" id="activity_id" class="form-select js-select2" data-search="on" required>
                                                                        <option value=""></option>
                                                                        @foreach($activities as $activity)
                                                                            <option value="{{ $activity->id }}" @selected(old('activity_id', $task->activity_id) == $activity->id)>{{ $activity->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="product_count">{{ __('task.form.fields.product_count') }}</label>
                                                                    <input name="product_count" value="{{ old('product_count', $task->product_count) }}"
                                                                           id="product_count" type="number" min="0" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="runtime">{{ __('task.form.fields.runtime') }}</label>
                                                                    <input name="runtime" value="{{ old('runtime', $task->runtime) }}"
                                                                           id="runtime" type="number" min="0" step="0.01" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="shift">{{ __('task.form.fields.shift') }}</label>
                                                                    <select name="shift" id="shift" class="form-select">
                                                                        @foreach($allShifts as $shift)
                                                                            <option value="{{ $shift['id'] }}" @selected(old('shift', $task->shift) == $shift['id'])>{{ $shift['name'] }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="work_day">{{ __('task.form.fields.work_day') }}</label>
                                                                    <input name="work_day" value="{{ old('work_day', $task->work_day ? \Carbon\Carbon::parse($task->work_day)->format('Y-m-d') : '') }}"
                                                                           id="work_day" type="date" class="form-control">
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="message">{{ __('task.form.fields.message') }}</label>
                                                                    <textarea name="message" id="message" class="form-control" rows="3">{{ old('message', $task->message) }}</textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!-- .nk-block -->
                                                </div><!-- .card-inner -->
                                            </div>

                                        </div>

                                        <div class="card-inner">
                                            <div class="nk-block text-right">
                                                <button type="submit" class="btn btn-lg btn-primary">{{ __('common.save') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div><!-- .card-content -->
                            </div><!-- .card-aside-wrap -->
                        </div><!-- .card -->
                    </div><!-- .nk-block -->

                    @if($histories->isNotEmpty())
                        @php
                            $activityNames = $activities->pluck('name', 'id');
                            $shiftNames    = collect($allShifts)->mapWithKeys(fn($s) => [(int) $s['id'] => $s['name']]);
                            $renderValue = function ($field, $value) use ($activityNames, $shiftNames) {
                                if ($value === null || $value === '') {
                                    return '—';
                                }
                                return match ($field) {
                                    'activity_id' => $activityNames[(int) $value] ?? $value,
                                    'shift'       => $shiftNames[(int) $value] ?? $value,
                                    'work_day'    => \Carbon\Carbon::parse($value)->format('d.m.Y'),
                                    default       => $value,
                                };
                            };
                        @endphp
                        <div class="nk-block">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <h5 class="title mb-3">{{ __('task.history.title') }}</h5>
                                    <div class="nk-tb-list nk-tb-ulist">
                                        <div class="nk-tb-item nk-tb-head">
                                            <div class="nk-tb-col"><span>{{ __('task.history.date') }}</span></div>
                                            <div class="nk-tb-col"><span>{{ __('task.history.editor') }}</span></div>
                                            <div class="nk-tb-col"><span>{{ __('task.history.changes') }}</span></div>
                                        </div>
                                        @foreach($histories as $history)
                                            <div class="nk-tb-item">
                                                <div class="nk-tb-col">
                                                    <span>{{ optional($history->created_at)->format('d.m.Y H:i') }}</span>
                                                </div>
                                                <div class="nk-tb-col">
                                                    <span>{{ $history->editor->name ?? '—' }}</span>
                                                </div>
                                                <div class="nk-tb-col">
                                                    @foreach(($history->changes ?? []) as $field => $pair)
                                                        <div>
                                                            <strong>{{ __('task.form.fields.' . $field) }}:</strong>
                                                            <span class="text-soft">{{ __('task.history.from') }}</span>
                                                            {{ $renderValue($field, $pair['old'] ?? null) }}
                                                            <span class="text-soft">→ {{ __('task.history.to') }}</span>
                                                            {{ $renderValue($field, $pair['new'] ?? null) }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
