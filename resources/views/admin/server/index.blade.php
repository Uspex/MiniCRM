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
                                <button type="button" id="btnUpdate" class="btn btn-lg btn-primary">
                                    <em class="icon ni ni-upload-cloud"></em>
                                    <span>{{ __('server.btn_update') }}</span>
                                </button>

                                <div id="deployProgress" class="mt-4" style="display:none;">
                                    <h6>{{ __('server.output') }}</h6>
                                    <div class="deploy-steps">
                                        @foreach(['backup', 'git', 'migrate', 'seed', 'cache'] as $step)
                                        <div class="d-flex align-items-center py-2 border-bottom" id="step-{{ $step }}">
                                            <span class="step-icon me-2">
                                                <span class="spinner-border spinner-border-sm text-muted d-none" role="status"></span>
                                                <em class="icon ni ni-clock text-muted step-pending"></em>
                                                <em class="icon ni ni-check-circle-fill text-success d-none step-done"></em>
                                                <em class="icon ni ni-cross-circle-fill text-danger d-none step-fail"></em>
                                            </span>
                                            <span class="step-label fw-medium">{{ __('server.steps.' . $step) }}</span>
                                            <span class="step-output ms-3 text-muted small"></span>
                                        </div>
                                        @endforeach
                                    </div>
                                    <pre id="deployLog" class="bg-lighter p-3 rounded mt-3" style="max-height: 300px; overflow-y: auto; display:none;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(count($backups))
                    <div class="nk-block" id="backupsBlock">
                        <div class="card card-bordered">
                            <div class="card-inner">
                                <h6 class="title mb-3">{{ __('server.backups_title') }}</h6>
                                <table class="table table-bordered table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('server.backup_name') }}</th>
                                            <th>{{ __('server.backup_size') }}</th>
                                            <th>{{ __('server.backup_date') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($backups as $backup)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.server.backup.download', Str::replaceLast('.sql', '', $backup['name'])) }}">
                                                        <em class="icon ni ni-download me-1"></em> {{ $backup['name'] }}
                                                    </a>
                                                </td>
                                                <td>{{ $backup['size'] }}</td>
                                                <td>{{ $backup['date'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('btnUpdate');
    var progress = document.getElementById('deployProgress');
    var log = document.getElementById('deployLog');
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    var steps = [
        { key: 'backup',  url: '{{ route("admin.server.step.backup") }}' },
        { key: 'git',     url: '{{ route("admin.server.step.git") }}' },
        { key: 'migrate', url: '{{ route("admin.server.step.migrate") }}' },
        { key: 'seed',    url: '{{ route("admin.server.step.seed") }}' },
        { key: 'cache',   url: '{{ route("admin.server.step.cache") }}' },
    ];

    btn.addEventListener('click', function () {
        if (!confirm('{{ __("server.confirm_update") }}')) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> {{ __("server.btn_update") }}';
        progress.style.display = '';
        log.style.display = 'none';
        log.textContent = '';

        // Сбросить все шаги
        steps.forEach(function (s) {
            var el = document.getElementById('step-' + s.key);
            el.querySelector('.spinner-border').classList.add('d-none');
            el.querySelector('.step-pending').classList.remove('d-none');
            el.querySelector('.step-done').classList.add('d-none');
            el.querySelector('.step-fail').classList.add('d-none');
            el.querySelector('.step-output').textContent = '';
        });

        runSteps(0);
    });

    function setStepState(key, state, output) {
        var el = document.getElementById('step-' + key);
        el.querySelector('.spinner-border').classList.add('d-none');
        el.querySelector('.step-pending').classList.add('d-none');
        el.querySelector('.step-done').classList.add('d-none');
        el.querySelector('.step-fail').classList.add('d-none');

        if (state === 'running') {
            el.querySelector('.spinner-border').classList.remove('d-none');
        } else if (state === 'done') {
            el.querySelector('.step-done').classList.remove('d-none');
        } else if (state === 'fail') {
            el.querySelector('.step-fail').classList.remove('d-none');
        }

        if (output) {
            el.querySelector('.step-output').textContent = output;
        }
    }

    function appendLog(title, text) {
        log.style.display = '';
        log.textContent += '=== ' + title + ' ===\n' + text + '\n\n';
        log.scrollTop = log.scrollHeight;
    }

    function finishDeploy(success) {
        btn.disabled = false;
        btn.innerHTML = '<em class="icon ni ni-upload-cloud"></em> <span>{{ __("server.btn_update") }}</span>';
    }

    function runSteps(index) {
        if (index >= steps.length) {
            finishDeploy(true);
            return;
        }

        var step = steps[index];
        setStepState(step.key, 'running');

        fetch(step.url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
        })
        .then(function (res) {
            return res.text().then(function (text) {
                try {
                    return JSON.parse(text);
                } catch (e) {
                    return { success: false, output: 'Server error (HTTP ' + res.status + '):\n' + text.substring(0, 500) };
                }
            });
        })
        .then(function (data) {
            var summary = data.summary || data.output;
            var logText = data.log || data.output;

            if (data.success) {
                setStepState(step.key, 'done', summary);
                appendLog(step.key, logText);
                runSteps(index + 1);
            } else {
                setStepState(step.key, 'fail', summary);
                appendLog(step.key, 'ERROR: ' + logText);
                finishDeploy(false);
            }
        })
        .catch(function (err) {
            setStepState(step.key, 'fail', err.message);
            appendLog(step.key, 'ERROR: ' + err.message);
            finishDeploy(false);
        });
    }
}());
</script>
@endpush
