@extends('layouts.admin.app')

@section('title', translate('mercado_entre_tiendas'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('mercado_entre_tiendas') }}
                <span class="badge badge-soft-dark radius-50">{{ $publicaciones->total() }}</span>
            </h2>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form action="{{ url()->current() }}" method="GET">
                    <div class="row align-items-end g-4">
                        <div class="col-md-4">
                            <label class="form-label">{{ translate('visibilidad') }}</label>
                            <div class="select-wrapper">
                                <select class="form-select set-filter" name="visibilidad">
                                    <option value="" {{ request('visibilidad') == '' ? 'selected' : '' }}>{{ translate('todas') }}</option>
                                    <option value="visibles" {{ request('visibilidad') == 'visibles' ? 'selected' : '' }}>{{ translate('visibles') }}</option>
                                    <option value="ocultas" {{ request('visibilidad') == 'ocultas' ? 'selected' : '' }}>{{ translate('ocultas') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="d-md-block">&nbsp;</label>
                            <div class="d-flex gap-3 justify-content-start">
                                <a href="{{ route('admin.mercado.list') }}" class="btn btn-secondary btn-block">{{ translate('reset') }}</a>
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
                        {{ translate('publicaciones') }}
                        <span class="badge badge-info text-bg-info">{{ $publicaciones->total() }}</span>
                    </h3>
                    <div class="flex-grow-1 max-w-300 min-w-100-mobile">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group">
                                <input type="hidden" name="visibilidad" value="{{ request('visibilidad') }}">
                                <input type="search" name="searchValue" class="form-control" placeholder="{{ translate('buscar_por_titulo_o_dueno') }}" value="{{ request('searchValue') }}">
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
                                <th>{{ translate('publicacion') }}</th>
                                <th>{{ translate('tipo') }}</th>
                                <th>{{ translate('precio') }}</th>
                                <th>{{ translate('dueno') }}</th>
                                <th>{{ translate('estatus') }}</th>
                                <th>{{ translate('fecha') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($publicaciones as $key => $publicacion)
                            <tr>
                                <td>{{ $publicaciones->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($publicacion->foto)
                                            <img src="{{ asset('storage/mercado/' . $publicacion->foto) }}" class="rounded" width="40" height="40" style="object-fit: cover;" alt="{{ translate('foto') }}">
                                        @endif
                                        <div>
                                            <div class="fw-bold">{{ $publicacion->titulo }}</div>
                                            @if($publicacion->es_oferta)
                                                <span class="badge badge-soft-warning">{{ translate('oferta') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>{{ translate($publicacion->tipo) }}</td>
                                <td>{{ $publicacion->precio !== null ? '$' . $publicacion->precio . ($publicacion->unidad ? ' / ' . $publicacion->unidad : '') : '-' }}</td>
                                <td>
                                    <div>{{ $publicacion->dueno ? $publicacion->dueno->f_name . ' ' . $publicacion->dueno->l_name : '-' }}</div>
                                    <small class="text-muted">{{ $publicacion->perfilDueno?->nombre_negocio ?: '-' }}</small>
                                </td>
                                <td>
                                    @if($publicacion->oculto_por_admin)
                                        <span class="badge badge-soft-danger">{{ translate('oculta_por_admin') }}</span>
                                    @elseif(!$publicacion->activo)
                                        <span class="badge badge-soft-secondary">{{ translate('pausada_por_dueno') }}</span>
                                    @else
                                        <span class="badge badge-soft-success">{{ translate('visible') }}</span>
                                    @endif
                                </td>
                                <td>{{ $publicacion->created_at->format('Y-m-d') }}</td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                                        @if($publicacion->oculto_por_admin)
                                            <form action="{{ route('admin.mercado.visibilidad') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_restaurar_publicacion') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $publicacion->id }}">
                                                <input type="hidden" name="oculto" value="0">
                                                <button type="submit" class="btn btn-outline-success btn-sm">{{ translate('restaurar') }}</button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.mercado.visibilidad') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_ocultar_publicacion') }}')">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $publicacion->id }}">
                                                <input type="hidden" name="oculto" value="1">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">{{ translate('ocultar') }}</button>
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
                        {!! $publicaciones->links() !!}
                    </div>
                </div>
                @if(count($publicaciones) == 0)
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>
@endsection
