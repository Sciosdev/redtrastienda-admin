@extends('layouts.admin.app')

@section('title', translate('Deploy_desde_GitHub'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-20">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('Deploy_desde_GitHub') }}
            </h2>
        </div>

        {{-- Repository status --}}
        <div class="card mb-3 mb-sm-20">
            <div class="card-body">
                <h3 class="mb-3">{{ translate('estado_del_repositorio') }}</h3>

                @if (!$status['is_git_repo'])
                    <div class="bg-danger bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                        <i class="fi fi-sr-cross-circle text-danger"></i>
                        <span>{{ translate('no_es_un_repositorio_git') }} — {{ $status['message'] }}</span>
                    </div>
                @else
                    <div class="row g-3">
                        <div class="col-sm-6 col-lg-4">
                            <div class="bg-section rounded-8 p-12">
                                <div class="fs-12 text-muted">{{ translate('rama_actual') }}</div>
                                <div class="fw-semibold">{{ $status['branch'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="bg-section rounded-8 p-12">
                                <div class="fs-12 text-muted">{{ translate('ultimo_commit_local') }}</div>
                                <div class="fw-semibold">{{ $status['local_commit'] }}</div>
                            </div>
                        </div>
                        <div class="col-sm-6 col-lg-4">
                            <div class="bg-section rounded-8 p-12">
                                <div class="fs-12 text-muted">{{ translate('estado') }}</div>
                                @if (!$status['fetch_ok'])
                                    <span class="badge badge-danger">{{ translate('no_se_pudo_contactar_el_remoto') }}</span>
                                @elseif ($status['behind_count'] > 0)
                                    <span class="badge badge-warning">
                                        {{ $status['behind_count'] }} {{ translate('commits_nuevos_en_origin') }}
                                    </span>
                                @else
                                    <span class="badge badge-success">{{ translate('sistema_actualizado') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (!empty($status['behind_commits']))
                        <div class="mt-3">
                            <div class="fs-12 text-muted mb-1">{{ translate('commits_pendientes') }}</div>
                            <pre class="bg-dark text-white rounded-8 p-12 fs-12 mb-0" style="max-height:220px;overflow:auto;">{{ implode("\n", $status['behind_commits']) }}</pre>
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Destructive deploy action --}}
        <div class="card">
            <div class="card-body">
                <h3 class="mb-2">{{ translate('Actualizar_desde_GitHub') }}</h3>

                <div class="bg-warning bg-opacity-10 fs-12 px-12 py-10 text-dark rounded-8 mb-3">
                    <div class="d-flex gap-2">
                        <i class="fi fi-sr-triangle-warning text-warning"></i>
                        <div>
                            <strong>{{ translate('accion_destructiva') }}</strong>
                            <p class="mb-0 mt-1">{{ translate('advertencia_deploy_descripcion') }}</p>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="max-width:360px;">
                    <label class="form-label" for="deploy-confirm-input">
                        {{ translate('escribe_ACTUALIZAR_para_confirmar') }}
                    </label>
                    <input type="text" class="form-control" id="deploy-confirm-input" autocomplete="off"
                           placeholder="ACTUALIZAR">
                </div>

                <button type="button" id="deploy-run-btn" class="btn btn-danger min-w-120" disabled>
                    <span class="deploy-btn-label">{{ translate('Actualizar_desde_GitHub') }}</span>
                    <span class="deploy-btn-spinner d--none">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        {{ translate('ejecutando_deploy') }}...
                    </span>
                </button>

                <div id="deploy-output-wrap" class="mt-3 d--none">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="fs-12 text-muted">{{ translate('salida_del_deploy') }}</span>
                        <span id="deploy-result-badge"></span>
                    </div>
                    <pre id="deploy-output" class="bg-dark text-white rounded-8 p-12 fs-12 mb-0" style="max-height:420px;overflow:auto;"></pre>
                    <div class="fs-12 text-muted mt-2">{{ translate('registro_guardado_en') }} <code>storage/logs/deploy.log</code></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        (function () {
            const CONFIRM_WORD = 'ACTUALIZAR';
            const input = document.getElementById('deploy-confirm-input');
            const btn = document.getElementById('deploy-run-btn');
            const label = btn.querySelector('.deploy-btn-label');
            const spinner = btn.querySelector('.deploy-btn-spinner');
            const outputWrap = document.getElementById('deploy-output-wrap');
            const output = document.getElementById('deploy-output');
            const badge = document.getElementById('deploy-result-badge');
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const runUrl = "{{ route('admin.system-setup.deploy.run') }}";

            const messages = {
                confirm: "{{ translate('confirmar_ejecucion_deploy') }}",
                requestFailed: "{{ translate('no_se_pudo_completar_la_solicitud') }}",
                success: "{{ translate('deploy_completado_correctamente') }}",
                failed: "{{ translate('el_deploy_termino_con_errores') }}"
            };

            input.addEventListener('input', function () {
                btn.disabled = input.value.trim().toUpperCase() !== CONFIRM_WORD;
            });

            function setRunning(running) {
                btn.disabled = running || input.value.trim().toUpperCase() !== CONFIRM_WORD;
                label.classList.toggle('d--none', running);
                spinner.classList.toggle('d--none', !running);
            }

            function renderSteps(steps) {
                return steps.map(function (step) {
                    const flag = step.success ? 'OK ' : 'ERR';
                    let block = '[' + flag + '] ' + step.name + ' (exit ' + step.exit_code + ')';
                    (step.output || []).forEach(function (line) {
                        block += '\n    ' + line;
                    });
                    return block;
                }).join('\n\n');
            }

            btn.addEventListener('click', function () {
                if (input.value.trim().toUpperCase() !== CONFIRM_WORD) {
                    return;
                }
                if (!window.confirm(messages.confirm)) {
                    return;
                }

                setRunning(true);
                outputWrap.classList.remove('d--none');
                output.textContent = '';
                badge.innerHTML = '';

                fetch(runUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('HTTP ' + response.status);
                        }
                        return response.json();
                    })
                    .then(function (data) {
                        output.textContent = renderSteps(data.steps || []);
                        badge.innerHTML = data.success
                            ? '<span class="badge badge-success">' + messages.success + '</span>'
                            : '<span class="badge badge-danger">' + messages.failed + '</span>';
                    })
                    .catch(function (error) {
                        output.textContent = messages.requestFailed + ' (' + error.message + ')';
                        badge.innerHTML = '<span class="badge badge-danger">' + messages.failed + '</span>';
                    })
                    .finally(function () {
                        setRunning(false);
                    });
            });
        })();
    </script>
@endpush
