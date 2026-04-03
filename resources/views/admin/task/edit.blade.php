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
                                                            <div class="col-md-4 col-lg-3">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="created_at">{{ __('task.form.fields.created_at') }}</label>
                                                                    <input name="created_at" value="{{ old('created_at', $task->created_at->format('Y-m-d\TH:i')) }}"
                                                                           id="created_at" type="datetime-local" class="form-control"
                                                                           @cannot(\App\Models\Permission::PERMISSION_TASK_EDIT_DATE) disabled readonly @endcannot>
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
                </div>
            </div>
        </div>
    </div>
@endsection
