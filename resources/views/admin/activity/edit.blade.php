@extends('admin.layouts.app')

@section('content')
    <div class="nk-content ">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between g-3">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">{{ __('activity.form.edit_title') }}</h3>
                            </div>
                            <div class="nk-block-head-content">
                                <a href="{{ route('admin.activity.index') }}" class="btn btn-outline-light bg-white d-none d-sm-inline-flex"><em class="icon ni ni-arrow-left"></em><span>{{ __('common.back') }}</span></a>
                            </div>
                        </div>
                    </div><!-- .nk-block-head -->
                    <div class="nk-block">
                        <div class="card card-bordered">
                            <div class="card-aside-wrap">
                                <div class="card-content">
                                    <form method="POST" action="{{ route('admin.activity.update', $activity->id) }}">
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
                                                        <div class="row g-4">
                                                            <div class="col-lg-3">
                                                                <div class="form-group">
                                                                    <label class="form-label" for="name">{{ __('activity.form.fields.name') }}</label>
                                                                    <input name="name" value="{{ $activity->name }}"
                                                                           id="name"
                                                                           type="text"
                                                                           class="form-control"
                                                                           required>
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
