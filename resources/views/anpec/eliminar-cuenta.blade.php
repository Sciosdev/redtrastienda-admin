@extends('anpec.layouts.base')

@section('title', translate('eliminar_meta_titulo'))
@section('meta_description', translate('eliminar_meta_descripcion'))

@push('styles')
    <style>
        .documento { max-width: 760px; }
        .documento h2 { margin-top: 32px; font-size: 1.2rem; }
        .documento p, .documento li { font-size: .98rem; }
        .documento p { margin-top: 12px; }
        .documento ol, .documento ul { margin: 12px 0 0 22px; }
        .documento li { margin-top: 6px; }
        .documento .contacto-correo {
            margin-top: 16px;
            background: var(--fondo-suave);
            border: 1px solid var(--borde);
            border-radius: 12px;
            padding: 16px 20px;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="container documento">
            <h1>{{ translate('eliminar_titulo') }}</h1>
            <p><strong>{{ translate('eliminar_subtitulo') }}</strong></p>

            <h2>{{ translate('eliminar_desde_app_titulo') }}</h2>
            <ol>
                <li>{{ translate('eliminar_paso_1') }}</li>
                <li>{{ translate('eliminar_paso_2') }}</li>
                <li>{{ translate('eliminar_paso_3') }}</li>
                <li>{{ translate('eliminar_paso_4') }}</li>
            </ol>
            <p>{{ translate('eliminar_pedidos_nota') }}</p>

            <h2>{{ translate('eliminar_que_se_borra_titulo') }}</h2>
            <p>{{ translate('eliminar_que_se_borra_texto') }}</p>
            <p>{{ translate('eliminar_afiliacion_texto') }}</p>

            <h2>{{ translate('eliminar_por_correo_titulo') }}</h2>
            <p>{{ translate('eliminar_por_correo_texto') }}</p>
            @if($companyEmail !== '')
                <div class="contacto-correo">
                    <p><strong>{{ translate('eliminar_por_correo_contacto') }}</strong>
                        <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a></p>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('footer_links')
    <p><a href="{{ route('politica-de-privacidad') }}">{{ translate('privacidad_titulo') }}</a></p>
@endsection
