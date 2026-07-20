@extends('anpec.layouts.base')

@section('title', translate('privacidad_meta_titulo'))
@section('meta_description', translate('privacidad_meta_descripcion'))

@push('styles')
    <style>
        .documento { max-width: 760px; }
        .documento .fecha { color: var(--tinta-suave); font-size: .9rem; margin-top: 8px; }
        .documento h2 { margin-top: 32px; font-size: 1.2rem; }
        .documento p, .documento li { font-size: .98rem; }
        .documento p { margin-top: 12px; }
        .documento ul { margin: 12px 0 0 22px; }
        .documento li { margin-top: 6px; }
        .documento .contacto-arco {
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
            <h1>{{ translate('privacidad_titulo') }}</h1>
            <p><strong>{{ translate('privacidad_subtitulo') }}</strong></p>
            <p class="fecha">{{ translate('privacidad_actualizacion') }}</p>

            <h2>{{ translate('privacidad_responsable_titulo') }}</h2>
            <p>{{ translate('privacidad_responsable_texto') }}</p>

            <h2>{{ translate('privacidad_datos_titulo') }}</h2>
            <p>{{ translate('privacidad_datos_intro') }}</p>
            <ul>
                <li>{{ translate('privacidad_dato_1') }}</li>
                <li>{{ translate('privacidad_dato_2') }}</li>
                <li>{{ translate('privacidad_dato_3') }}</li>
                <li>{{ translate('privacidad_dato_4') }}</li>
                <li>{{ translate('privacidad_dato_5') }}</li>
                <li>{{ translate('privacidad_dato_6') }}</li>
                <li>{{ translate('privacidad_dato_7') }}</li>
                <li>{{ translate('privacidad_dato_8') }}</li>
            </ul>
            <p>{{ translate('privacidad_datos_nota') }}</p>

            <h2>{{ translate('privacidad_finalidad_titulo') }}</h2>
            <p>{{ translate('privacidad_finalidad_intro') }}</p>
            <ul>
                <li>{{ translate('privacidad_finalidad_1') }}</li>
                <li>{{ translate('privacidad_finalidad_2') }}</li>
                <li>{{ translate('privacidad_finalidad_3') }}</li>
                <li>{{ translate('privacidad_finalidad_4') }}</li>
                <li>{{ translate('privacidad_finalidad_5') }}</li>
            </ul>

            <h2>{{ translate('privacidad_noventa_titulo') }}</h2>
            <p>{{ translate('privacidad_noventa_texto') }}</p>

            <h2>{{ translate('privacidad_resguardo_titulo') }}</h2>
            <p>{{ translate('privacidad_resguardo_texto') }}</p>

            <h2>{{ translate('privacidad_arco_titulo') }}</h2>
            <p>{{ translate('privacidad_arco_texto') }}</p>
            @if($companyEmail !== '')
                <div class="contacto-arco">
                    <p><strong>{{ translate('privacidad_arco_contacto') }}</strong>
                        <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a></p>
                </div>
            @endif

            <h2>{{ translate('privacidad_cambios_titulo') }}</h2>
            <p>{{ translate('privacidad_cambios_texto') }}</p>
        </div>
    </section>
@endsection

@section('footer_links')
    <p><a href="{{ route('conectate') }}">{{ translate('conectate_hero_titulo') }}</a></p>
    <p><a href="{{ route('eliminar-cuenta') }}">{{ translate('eliminar_titulo') }}</a></p>
@endsection
