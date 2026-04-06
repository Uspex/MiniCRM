@extends('admin.layouts.app')

@section('content')
    <div class="nk-content ">
        <div class="container-fluid">
            <div class="nk-content-inner">
                <div class="nk-content-body">
                    <div class="nk-block-head nk-block-head-sm">
                        <div class="nk-block-between g-3">
                            <div class="nk-block-head-content">
                                <h3 class="nk-block-title page-title">{{ __('setting.shifts.edit_title') }}</h3>
                            </div>
                            <div class="nk-block-head-content">
                                <a href="{{ route('admin.setting.index') }}" class="btn btn-outline-light bg-white d-none d-sm-inline-flex"><em class="icon ni ni-arrow-left"></em><span>{{ __('common.back') }}</span></a>
                            </div>
                        </div>
                    </div>

                    <div class="nk-block">
                        <div class="card card-bordered">
                            <div class="card-content">
                                <form method="POST" action="{{ route('admin.setting.update', 'shifts') }}" id="shifts-form">
                                    @csrf

                                    <div class="card-inner">
                                        <div class="nk-block" id="shifts-container">
                                            @foreach($setting->value as $index => $shift)
                                                <div class="row g-4 mb-3 shift-row align-items-end">
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('setting.shifts.shift') }}</label>
                                                            <input name="shifts[{{ $index }}][shift]" value="{{ $shift['shift'] }}" type="number" min="1" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('setting.shifts.name') }}</label>
                                                            <input name="shifts[{{ $index }}][name]" value="{{ $shift['name'] }}" type="text" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('setting.shifts.start') }}</label>
                                                            <input name="shifts[{{ $index }}][start]" value="{{ $shift['start'] }}" type="time" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <div class="form-group">
                                                            <label class="form-label">{{ __('setting.shifts.end') }}</label>
                                                            <input name="shifts[{{ $index }}][end]" value="{{ $shift['end'] }}" type="time" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2">
                                                        <button type="button" class="btn btn-sm btn-danger remove-row"><em class="icon ni ni-trash"></em> {{ __('setting.shifts.remove') }}</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-secondary" id="add-shift"><em class="icon ni ni-plus"></em> {{ __('setting.shifts.add') }}</button>
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
            const container = document.getElementById('shifts-container');
            const addBtn = document.getElementById('add-shift');
            let index = container.querySelectorAll('.shift-row').length;

            addBtn.addEventListener('click', function () {
                const row = document.createElement('div');
                row.className = 'row g-4 mb-3 shift-row align-items-end';
                row.innerHTML = `
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('setting.shifts.shift') }}</label>
                            <input name="shifts[${index}][shift]" value="${index + 1}" type="number" min="1" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('setting.shifts.name') }}</label>
                            <input name="shifts[${index}][name]" value="" type="text" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('setting.shifts.start') }}</label>
                            <input name="shifts[${index}][start]" value="" type="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-label">{{ __('setting.shifts.end') }}</label>
                            <input name="shifts[${index}][end]" value="" type="time" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <button type="button" class="btn btn-sm btn-danger remove-row"><em class="icon ni ni-trash"></em> {{ __('setting.shifts.remove') }}</button>
                    </div>
                `;
                container.appendChild(row);
                index++;
            });

            container.addEventListener('click', function (e) {
                if (e.target.closest('.remove-row')) {
                    e.target.closest('.shift-row').remove();
                }
            });
        });
    </script>
@endsection
