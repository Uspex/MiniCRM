@extends('admin.layouts.app')

@section('content')
    <div class="nk-content ">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between g-3">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">{{ __('server.title') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="nk-block">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <form method="POST" action="{{ route('admin.server.update') }}" onsubmit="return confirm('{{ __('server.confirm_update') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-lg btn-primary">
                                        <em class="icon ni ni-upload-cloud"></em>
                                        <span>{{ __('server.btn_update') }}</span>
                                    </button>
                                </form>

                                @if(session('deploy_output'))
                                    <div class="mt-4">
                                        <h6>{{ __('server.output') }}</h6>
                                        <pre class="bg-lighter p-3 rounded" style="max-height: 400px; overflow-y: auto;">{{ session('deploy_output') }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
