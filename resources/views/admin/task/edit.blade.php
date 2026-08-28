@extends('admin.layouts.app')

@section('content')
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
        // Поля недоступны: нет права на редактирование либо операция заблокирована запросом
        $readonly = $locked || ! $canUpdate;
    @endphp

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
                                                        @if($task->request_type)
                                                            @php
                                                                $requestAlert = match (true) {
                                                                    $task->hasPendingRequest() => 'alert-warning',
                                                                    $task->isCancelled()       => 'alert-danger',
                                                                    default                    => 'alert-light',
                                                                };
                                                            @endphp
                                                            <div class="alert {{ $requestAlert }}">
                                                                <h6 class="mb-2">
                                                                    {{ __('task.request.type.' . $task->request_type) }} — {{ __('task.request.status.' . $task->request_status) }}
                                                                </h6>
                                                                @if($locked)
                                                                    <div class="mb-2">
                                                                        {{ $task->isCancelled() ? __('task.request.cancelled_notice') : __('task.request.pending_notice') }}
                                                                    </div>
                                                                @endif
                                                                <ul class="list-unstyled mb-0 small">
                                                                    @if($task->request_reason)
                                                                        <li><strong>{{ __('task.request.reason') }}:</strong> {{ $task->request_reason }}</li>
                                                                    @endif
                                                                    <li><strong>{{ __('task.request.requested_by') }}:</strong> {{ $task->requester->name ?? '—' }}
                                                                        ({{ optional($task->request_requested_at)->format('d.m.Y H:i') ?: '—' }})</li>
                                                                    @if($task->request_processed_at)
                                                                        <li><strong>{{ __('task.request.processed_by') }}:</strong> {{ $task->processor->name ?? '—' }}
                                                                            ({{ optional($task->request_processed_at)->format('d.m.Y H:i') }})</li>
                                                                        @if($task->request_decision_comment)
                                                                            <li><strong>{{ __('task.request.comment') }}:</strong> {{ $task->request_decision_comment }}</li>
                                                                        @endif
                                                                    @endif
                                                                </ul>
                                                                @if($task->request_type === \App\Models\Task::REQUEST_TYPE_EDIT && $task->request_changes)
                                                                    <div class="small mt-2">
                                                                        @foreach($task->request_changes as $field => $pair)
                                                                            <div>
                                                                                <strong>{{ __('task.form.fields.' . $field) }}:</strong>
                                                                                <span class="text-soft">{{ __('task.history.from') }}</span>
                                                                                {{ $renderValue($field, $pair['old'] ?? null) }}
                                                                                <span class="text-soft">→ {{ __('task.history.to') }}</span>
                                                                                {{ $renderValue($field, $pair['new'] ?? null) }}
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        @endif

                                                        @unless($canUpdate)
                                                            <div class="alert alert-light">{{ __('task.request.no_permission_notice') }}</div>
                                                        @endunless

                                                        <div class="row g-3">
                                                            <div class="col-md-6 col-lg-3">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="activity_id">{{ __('task.form.fields.activity_id') }}</label>
                                                                    <select name="activity_id" id="activity_id" class="form-select js-select2" data-search="on" required @disabled($readonly)>
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
                                                                           id="product_count" type="number" min="0" class="form-control" @disabled($readonly)>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="runtime">{{ __('task.form.fields.runtime') }}</label>
                                                                    <input name="runtime" value="{{ old('runtime', $task->runtime) }}"
                                                                           id="runtime" type="number" min="0" step="0.01" class="form-control" @disabled($readonly)>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-4 col-lg-2">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="shift">{{ __('task.form.fields.shift') }}</label>
                                                                    <select name="shift" id="shift" class="form-select" @disabled($readonly)>
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
                                                                           id="work_day" type="date" class="form-control" @disabled($readonly)>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="message">{{ __('task.form.fields.message') }}</label>
                                                                    <textarea name="message" id="message" class="form-control" rows="3" @disabled($readonly)>{{ old('message', $task->message) }}</textarea>
                                                                </div>
                                                            </div>

                                                            @if($sendsForApproval && ! $readonly)
                                                                <div class="col-12">
                                                                    <div class="form-group">
                                                                        <label class="form-label" for="request_reason">{{ __('task.request.reason') }}</label>
                                                                        <textarea name="request_reason" id="request_reason" class="form-control" rows="2"
                                                                                  placeholder="{{ __('task.request.reason_hint') }}">{{ old('request_reason') }}</textarea>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div><!-- .nk-block -->
                                                </div><!-- .card-inner -->
                                            </div>

                                        </div>

                                        @if(! $readonly || $canCancelRequest)
                                            <div class="card-inner">
                                                <div class="nk-block d-flex flex-wrap justify-content-end align-items-center gap-2">
                                                    @if(! $readonly && $sendsForApproval)
                                                        <span class="text-soft small">{{ __('task.request.submit_hint') }}</span>
                                                    @endif
                                                    @if($canCancelRequest)
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                data-bs-toggle="modal" data-bs-target="#cancelRequestModal">
                                                            <em class="icon ni ni-na"></em><span>{{ __('task.cancel.request_btn') }}</span>
                                                        </button>
                                                    @endif
                                                    @unless($readonly)
                                                        <button type="submit" class="btn btn-sm btn-primary">
                                                            {{ $sendsForApproval ? __('task.request.submit') : __('common.save') }}
                                                        </button>
                                                    @endunless
                                                </div>
                                            </div>
                                        @endif
                                    </form>
                                </div><!-- .card-content -->
                            </div><!-- .card-aside-wrap -->
                        </div><!-- .card -->
                    </div><!-- .nk-block -->

                    @if($histories->isNotEmpty())
                        <div class="nk-block">
                            <div class="card card-bordered">
                                <div class="card-inner">
                                    <h5 class="title mb-3">{{ __('task.history.title') }}</h5>
                                    <div class="nk-tb-list nk-tb-ulist">
                                        <div class="nk-tb-item nk-tb-head">
                                            <div class="nk-tb-col"><span>{{ __('task.history.date') }}</span></div>
                                            <div class="nk-tb-col"><span>{{ __('task.history.event') }}</span></div>
                                            <div class="nk-tb-col"><span>{{ __('task.history.editor') }}</span></div>
                                            <div class="nk-tb-col" style="flex: 2 1 auto;"><span>{{ __('task.history.changes') }}</span></div>
                                            <div class="nk-tb-col tb-col-md"><span>{{ __('task.history.decision') }}</span></div>
                                        </div>
                                        @foreach($histories as $history)
                                            <div class="nk-tb-item">
                                                <div class="nk-tb-col">
                                                    <span>{{ optional($history->created_at)->format('d.m.Y H:i') }}</span>
                                                </div>
                                                <div class="nk-tb-col">
                                                    <span class="badge bg-light text-dark">{{ __('task.history.events.' . $history->event) }}</span>
                                                </div>
                                                <div class="nk-tb-col">
                                                    <span>{{ $history->editor->name ?? '—' }}</span>
                                                </div>
                                                <div class="nk-tb-col" style="flex: 2 1 auto;">
                                                    @foreach(($history->changes ?? []) as $field => $pair)
                                                        <div>
                                                            <strong>{{ __('task.form.fields.' . $field) }}:</strong>
                                                            <span class="text-soft">{{ __('task.history.from') }}</span>
                                                            {{ $renderValue($field, $pair['old'] ?? null) }}
                                                            <span class="text-soft">→ {{ __('task.history.to') }}</span>
                                                            {{ $renderValue($field, $pair['new'] ?? null) }}
                                                        </div>
                                                    @endforeach
                                                    @if($history->comment)
                                                        <div class="text-soft small">{{ $history->comment }}</div>
                                                    @endif
                                                    @if(empty($history->changes) && !$history->comment)
                                                        <span class="text-soft">—</span>
                                                    @endif
                                                </div>
                                                <div class="nk-tb-col tb-col-md">
                                                    @if($history->decided_at)
                                                        <span>{{ $history->decider->name ?? '—' }}</span>
                                                        <span class="text-soft d-block">{{ $history->decided_at->format('d.m.Y H:i') }}</span>
                                                        @if($history->decision_comment)
                                                            <span class="text-soft d-block">{{ $history->decision_comment }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-soft">—</span>
                                                    @endif
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
    @if($canCancelRequest)
        <div class="modal fade" id="cancelRequestModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form method="POST" action="{{ route('admin.task.cancel_request', $task->id) }}">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">{{ __('task.cancel.request_title') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="form-label" for="request_reason_cancel">{{ __('task.cancel.reason') }}</label>
                                <textarea name="request_reason" id="request_reason_cancel" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">{{ __('common.back') }}</button>
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('task.cancel.send_request') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
