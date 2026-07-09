@extends('layouts.admin.app')

@section('title', translate('detalle_afiliado_ANP'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h2 class="h1 mb-0">{{ translate('detalle_afiliado_ANP') }}</h2>
            <a href="{{ route('admin.afiliados.list') }}" class="btn btn-secondary">{{ translate('back') }}</a>
        </div>

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center">
                        @php
                            $badge = ['pendiente' => 'warning', 'activo' => 'success', 'rechazado' => 'secondary', 'bloqueado' => 'danger'][$affiliate->estatus] ?? 'secondary';
                        @endphp
                        @if($affiliate->foto_negocio)
                            <img src="{{ asset('storage/affiliate/' . $affiliate->foto_negocio) }}" class="img-fluid rounded mb-3" style="max-height: 220px; object-fit: cover;" alt="{{ translate('foto_negocio') }}">
                        @else
                            <div class="text-muted py-5 mb-3 border rounded">{{ translate('sin_foto_negocio') }}</div>
                        @endif
                        <h4 class="mb-1">{{ $affiliate->nombre_negocio ?: '-' }}</h4>
                        <span class="badge badge-soft-{{ $badge }} mb-3">{{ translate($affiliate->estatus) }}</span>

                        <div class="d-flex flex-wrap justify-content-center gap-2 mt-2">
                            @if(in_array($affiliate->estatus, ['pendiente', 'rechazado']))
                                <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_aprobar_afiliado') }}')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                    <input type="hidden" name="estatus" value="activo">
                                    <button type="submit" class="btn btn-success btn-sm">{{ translate('aprobar') }}</button>
                                </form>
                            @endif
                            @if($affiliate->estatus === 'pendiente')
                                <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_rechazar_afiliado') }}')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                    <input type="hidden" name="estatus" value="rechazado">
                                    <button type="submit" class="btn btn-secondary btn-sm">{{ translate('rechazar') }}</button>
                                </form>
                            @endif
                            @if($affiliate->estatus === 'bloqueado')
                                <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_desbloquear_afiliado') }}')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                    <input type="hidden" name="estatus" value="activo">
                                    <button type="submit" class="btn btn-success btn-sm">{{ translate('desbloquear') }}</button>
                                </form>
                            @else
                                <form action="{{ route('admin.afiliados.status-update') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_bloquear_afiliado') }}')">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                    <input type="hidden" name="estatus" value="bloqueado">
                                    <button type="submit" class="btn btn-danger btn-sm">{{ translate('bloquear') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">{{ translate('informacion_del_afiliado') }}</h5>
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                    <tr>
                                        <th class="w-40">{{ translate('numero_ANP') }}</th>
                                        <td>{{ $affiliate->numero_anp }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('nombre_afiliado') }}</th>
                                        <td>{{ $affiliate->customer ? $affiliate->customer->f_name . ' ' . $affiliate->customer->l_name : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('email') }}</th>
                                        <td>{{ $affiliate->customer?->email ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('phone') }}</th>
                                        <td>{{ $affiliate->customer?->phone ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('whatsapp') }}</th>
                                        <td>{{ $affiliate->whatsapp ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('direccion') }}</th>
                                        <td>{{ $affiliate->direccion ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('estado') }}</th>
                                        <td>{{ $affiliate->estado ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('municipio') }}</th>
                                        <td>{{ $affiliate->municipio ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('colonia') }}</th>
                                        <td>{{ $affiliate->colonia ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('approved_at') }}</th>
                                        <td>{{ $affiliate->approved_at ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('approved_by') }}</th>
                                        <td>{{ $affiliate->approved_by ?: '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
