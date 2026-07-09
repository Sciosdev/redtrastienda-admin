@extends('layouts.admin.app')

@section('title', translate('afiliados_ANP'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('afiliados_ANP') }}
                <span class="badge badge-soft-dark radius-50">{{ $totalAffiliates }}</span>
            </h2>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ route('admin.afiliados.settings') }}" method="post">
                    @csrf
                    <div class="d-flex flex-wrap align-items-center gap-3 justify-content-between">
                        <div>
                            <h5 class="mb-1">{{ translate('numero_ANP_obligatorio_en_registro') }}</h5>
                            <small class="text-muted">{{ translate('si_esta_activo_el_registro_exige_un_numero_ANP_valido') }}</small>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <label class="switcher">
                                <input type="checkbox" class="switcher_input" name="numero_anp_obligatorio" value="1" {{ $numeroAnpObligatorio == 1 ? 'checked' : '' }}>
                                <span class="switcher_control"></span>
                            </label>
                            <button type="submit" class="btn btn-primary">{{ translate('guardar') }}</button>
                        </div>
                    </div>
                </form>
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
                                    <option value="pendiente" {{ request('estatus') == 'pendiente' ? 'selected' : '' }}>{{ translate('pendiente') }}</option>
                                    <option value="activo" {{ request('estatus') == 'activo' ? 'selected' : '' }}>{{ translate('activo') }}</option>
                                    <option value="rechazado" {{ request('estatus') == 'rechazado' ? 'selected' : '' }}>{{ translate('rechazado') }}</option>
                                    <option value="bloqueado" {{ request('estatus') == 'bloqueado' ? 'selected' : '' }}>{{ translate('bloqueado') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-md-block">&nbsp;</label>
                            <div class="d-flex gap-3 justify-content-start">
                                <a href="{{ route('admin.afiliados.list') }}" class="btn btn-secondary btn-block">{{ translate('reset') }}</a>
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
                        {{ translate('afiliados_ANP') }}
                        <span class="badge badge-info text-bg-info">{{ $affiliates->total() }}</span>
                    </h3>
                    <div class="flex-grow-1 max-w-300 min-w-100-mobile">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group">
                                <input type="hidden" name="estatus" value="{{ request('estatus') }}">
                                <input type="search" name="searchValue" class="form-control" placeholder="{{ translate('buscar_por_nombre_negocio_o_numero_ANP') }}" value="{{ request('searchValue') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit"><i class="fi fi-rr-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive datatable-custom">
                    <table class="table table-hover table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100">
                        <thead class="thead-light thead-50 text-capitalize">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('afiliado') }}</th>
                                <th>{{ translate('contact_info') }}</th>
                                <th>{{ translate('nombre_negocio') }}</th>
                                <th>{{ translate('numero_ANP') }}</th>
                                <th>{{ translate('status') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($affiliates as $key => $affiliate)
                            <tr>
                                <td>{{ $affiliates->firstItem() + $key }}</td>
                                <td>{{ $affiliate->customer ? $affiliate->customer->f_name . ' ' . $affiliate->customer->l_name : '-' }}</td>
                                <td>
                                    <div class="mb-1"><a class="text-dark text-hover-primary" href="mailto:{{ $affiliate->customer?->email }}">{{ $affiliate->customer?->email }}</a></div>
                                    <a class="text-dark text-hover-primary" href="tel:{{ $affiliate->customer?->phone }}">{{ $affiliate->customer?->phone }}</a>
                                </td>
                                <td>{{ $affiliate->nombre_negocio ?: '-' }}</td>
                                <td><strong>{{ $affiliate->numero_anp }}</strong></td>
                                <td>
                                    @php
                                        $badge = ['pendiente' => 'warning', 'activo' => 'success', 'rechazado' => 'secondary', 'bloqueado' => 'danger'][$affiliate->estatus] ?? 'secondary';
                                    @endphp
                                    <span class="badge badge-soft-{{ $badge }}">{{ translate($affiliate->estatus) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        <a title="{{ translate('view') }}" class="btn btn-outline-info icon-btn" href="{{ route('admin.afiliados.view', [$affiliate->id]) }}">
                                            <i class="fi fi-rr-eye"></i>
                                        </a>
                                        @if(in_array($affiliate->estatus, ['pendiente', 'rechazado']))
                                            <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_aprobar_afiliado') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                                <input type="hidden" name="estatus" value="activo">
                                                <button type="submit" class="btn btn-outline-success btn-sm">{{ translate('aprobar') }}</button>
                                            </form>
                                        @endif
                                        @if($affiliate->estatus === 'pendiente')
                                            <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_rechazar_afiliado') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                                <input type="hidden" name="estatus" value="rechazado">
                                                <button type="submit" class="btn btn-outline-secondary btn-sm">{{ translate('rechazar') }}</button>
                                            </form>
                                        @endif
                                        @if($affiliate->estatus === 'bloqueado')
                                            <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_desbloquear_afiliado') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                                <input type="hidden" name="estatus" value="activo">
                                                <button type="submit" class="btn btn-outline-success btn-sm">{{ translate('desbloquear') }}</button>
                                            </form>
                                        @elseif($affiliate->estatus !== 'bloqueado')
                                            <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_bloquear_afiliado') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                                <input type="hidden" name="estatus" value="bloqueado">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">{{ translate('bloquear') }}</button>
                                            </form>
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
                        {!! $affiliates->links() !!}
                    </div>
                </div>
                @if(count($affiliates) == 0)
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>
@endsection
