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
                                        <td>
                                            @if($affiliate->numero_anp)
                                                {{ $affiliate->numero_anp }}
                                            @else
                                                <span class="badge badge-soft-warning">{{ translate('lead_sin_numero') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('cuenta_activada') }}</th>
                                        <td>
                                            @if($affiliate->reclamada)
                                                <span class="badge badge-soft-success">{{ translate('si') }}</span>
                                                @if($affiliate->fecha_reclamo)
                                                    <small class="text-muted">{{ $affiliate->fecha_reclamo }}</small>
                                                @endif
                                            @else
                                                <span class="badge badge-soft-secondary">{{ translate('sin_activar') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>{{ translate('telefono_de_contacto_importado') }}</th>
                                        <td>{{ $affiliate->telefono_contacto ?: '-' }}</td>
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

                @if(is_null($affiliate->numero_anp))
                    <div class="card mt-3">
                        <div class="card-body">
                            <h5 class="mb-1">{{ translate('asignar_numero_ANP') }}</h5>
                            <small class="text-muted d-block mb-3">{{ translate('este_interesado_aun_no_tiene_numero_al_asignarle_uno_podra_iniciar_sesion_con_sus_credenciales') }}</small>
                            <form action="{{ route('admin.afiliados.asignar-numero') }}" method="post" onsubmit="return confirm('{{ translate('confirmar_asignar_numero_ANP_a_este_afiliado') }}')">
                                @csrf
                                <input type="hidden" name="id" value="{{ $affiliate->id }}">
                                <div class="row g-2 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label">{{ translate('numero_ANP') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="numero_anp" class="form-control" maxlength="50" required placeholder="{{ translate('Ex') }}: ANP12345">
                                        <small class="text-muted">{{ translate('puede_ser_un_numero_disponible_existente_o_uno_nuevo_del_sistema_de_ANPEC') }}</small>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-primary btn-block">{{ translate('asignar') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
