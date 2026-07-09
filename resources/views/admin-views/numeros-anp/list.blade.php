@extends('layouts.admin.app')

@section('title', translate('numeros_ANP'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('numeros_ANP') }}
                <span class="badge badge-soft-dark radius-50">{{ $totalNumeros }}</span>
            </h2>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="mb-3">{{ translate('generar_lote') }}</h5>
                        <form action="{{ route('admin.numeros-anp.generate') }}" method="post">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">{{ translate('cantidad') }} <span class="text-danger">*</span></label>
                                    <input type="number" name="cantidad" class="form-control" min="1" max="1000" required placeholder="{{ translate('Ex') }}: 100">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ translate('prefijo') }} ({{ translate('optional') }})</label>
                                    <input type="text" name="prefijo" class="form-control" maxlength="20" placeholder="{{ translate('Ex') }}: ANP">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-primary btn-block">{{ translate('generar') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="mb-3">{{ translate('importar_CSV_Excel') }}</h5>
                        <form action="{{ route('admin.numeros-anp.import') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row g-3 align-items-end">
                                <div class="col-md-8">
                                    <label class="form-label">{{ translate('archivo') }} <span class="text-danger">*</span></label>
                                    <input type="file" name="anp_file" class="form-control" accept=".csv,.xlsx,.xls,.txt" required>
                                    <small class="text-muted">{{ translate('columna_1_numero_anp_columna_2_observaciones_opcional') }}</small>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">{{ translate('importar') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <div class="row align-items-end g-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('status') }}</label>
                            <div class="select-wrapper">
                                <select class="form-select set-filter" name="estatus">
                                    <option value="" {{ request('estatus') == '' ? 'selected' : '' }}>{{ translate('All') }}</option>
                                    <option value="disponible" {{ request('estatus') == 'disponible' ? 'selected' : '' }}>{{ translate('disponible') }}</option>
                                    <option value="usado" {{ request('estatus') == 'usado' ? 'selected' : '' }}>{{ translate('usado') }}</option>
                                    <option value="bloqueado" {{ request('estatus') == 'bloqueado' ? 'selected' : '' }}>{{ translate('bloqueado') }}</option>
                                    <option value="cancelado" {{ request('estatus') == 'cancelado' ? 'selected' : '' }}>{{ translate('cancelado') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-md-block">&nbsp;</label>
                            <div class="d-flex gap-3 justify-content-start">
                                <a href="{{ route('admin.numeros-anp.list') }}" class="btn btn-secondary btn-block">{{ translate('reset') }}</a>
                                <button type="submit" class="btn btn-primary btn-block">{{ translate('Filter') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex flex-wrap justify-content-between gap-3 align-items-center mb-4">
                    <h3 class="mb-0">
                        {{ translate('numeros_ANP') }}
                        <span class="badge badge-info text-bg-info">{{ $numeros->total() }}</span>
                    </h3>
                    <div class="d-flex flex-wrap gap-3 align-items-center justify-content-md-end min-w-100-mobile">
                        <div class="flex-grow-1 max-w-300 min-w-100-mobile">
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group">
                                    <input type="hidden" name="estatus" value="{{ request('estatus') }}">
                                    <input type="search" name="searchValue" class="form-control" placeholder="{{ translate('buscar_por_numero_o_observaciones') }}" value="{{ request('searchValue') }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit"><i class="fi fi-rr-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <a class="btn btn-outline-primary" href="{{ route('admin.numeros-anp.export', ['estatus' => request('estatus'), 'searchValue' => request('searchValue')]) }}">
                            <i class="fi fi-sr-inbox-in"></i>
                            <span class="fs-12">{{ translate('export') }}</span>
                        </a>
                    </div>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('numero_ANP') }}</th>
                                <th>{{ translate('status') }}</th>
                                <th>{{ translate('afiliado_asignado') }}</th>
                                <th>{{ translate('observaciones') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($numeros as $key => $numero)
                            <tr>
                                <td>{{ $numeros->firstItem() + $key }}</td>
                                <td><strong>{{ $numero->numero_anp }}</strong></td>
                                <td>
                                    @php
                                        $badge = ['disponible' => 'success', 'usado' => 'info', 'bloqueado' => 'danger', 'cancelado' => 'secondary'][$numero->estatus] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-soft-{{ $badge }}">{{ translate($numero->estatus) }}</span>
                                </td>
                                <td>{{ $numero->afiliado ? $numero->afiliado->f_name . ' ' . $numero->afiliado->l_name : '-' }}</td>
                                <td title="{{ $numero->observaciones }}">{{ \Illuminate\Support\Str::limit($numero->observaciones, 30) ?: '-' }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        @if($numero->estatus === 'disponible')
                                            <form action="{{ route('admin.numeros-anp.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_bloquear_numero_ANP') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $numero->id }}">
                                                <input type="hidden" name="estatus" value="bloqueado">
                                                <button type="submit" class="btn btn-outline-warning btn-sm">{{ translate('bloquear') }}</button>
                                            </form>
                                            <form action="{{ route('admin.numeros-anp.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_cancelar_numero_ANP') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $numero->id }}">
                                                <input type="hidden" name="estatus" value="cancelado">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">{{ translate('cancelar') }}</button>
                                            </form>
                                        @elseif($numero->estatus === 'bloqueado')
                                            <form action="{{ route('admin.numeros-anp.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_desbloquear_numero_ANP') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $numero->id }}">
                                                <input type="hidden" name="estatus" value="disponible">
                                                <button type="submit" class="btn btn-outline-success btn-sm">{{ translate('desbloquear') }}</button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <div class="px-4 d-flex justify-content-center justify-content-md-end">
                        {!! $numeros->links() !!}
                    </div>
                </div>
                @if(count($numeros) == 0)
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>
@endsection
