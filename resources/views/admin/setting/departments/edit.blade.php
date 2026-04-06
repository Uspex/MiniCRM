@extends('admin.layouts.app')

@section('content')
    <div class="nk-content ">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between g-3">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">{{ __('setting.departments.edit_title') }}</h3>
                            </div>
                            <div class="nk-block-head-content">
                                <a href="{{ route('admin.setting.index') }}" class="btn btn-outline-light bg-white d-none d-sm-inline-flex"><em class="icon ni ni-arrow-left"></em><span>{{ __('common.back') }}</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="nk-block">
                        <div class="card card-bordered">
                            <div class="card-content">
                                <form method="POST" action="{{ route('admin.setting.update', 'departments') }}" id="departments-form">
                                    @csrf

                                    <div class="card-inner">
                                        <div class="nk-block" id="departments-container">
                                            @foreach($setting->value as $index => $department)
                                                <div class="row g-4 mb-3 department-row align-items-end">
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('setting.departments.name') }}</label>
                                                            <input name="departments[{{ $index }}][name]" value="{{ $department['name'] }}" type="text" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-sm btn-danger remove-row"><em class="icon ni ni-trash"></em> {{ __('setting.departments.remove') }}</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-secondary" id="add-department"><em class="icon ni ni-plus"></em> {{ __('setting.departments.add') }}</button>
                                        </div>
                                    </div>

                                    <div class="card-inner">
                                        <div class="nk-block text-right">
                                            <button type="submit" class="btn btn-lg btn-primary">{{ __('common.save') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const container = document.getElementById('departments-container');
            const addBtn = document.getElementById('add-department');
            let index = container.querySelectorAll('.department-row').length;

            addBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'row g-4 mb-3 department-row align-items-end';
                row.innerHTML = `
                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ __('setting.departments.name') }}</label>
                            <input name="departments[${index}][name]" value="" type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-sm btn-danger remove-row"><em class="icon ni ni-trash"></em> {{ __('setting.departments.remove') }}</button>
                    </div>
                `;
                container.appendChild(row);
                index++;
            });

            container.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    e.target.closest('.department-row').remove();
                }
            });
        });
    </script>
@endsection
