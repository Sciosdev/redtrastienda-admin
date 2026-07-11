@extends('anpec.layouts.base')

@section('title', translate('conectate_meta_titulo'))
@section('meta_description', translate('conectate_meta_descripcion'))

@push('styles')
    <style>
        .hero {
            background: linear-gradient(160deg, var(--rojo) 0%, var(--rojo-oscuro) 100%);
            color: #fff;
            text-align: center;
            padding: 56px 0;
        }
        .hero h1 { color: #fff; }
        .hero .subtitulo { margin: 16px auto 0; max-width: 640px; font-size: 1.05rem; opacity: .95; }
        .chips { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-top: 24px; }
        .chip {
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .35);
            border-radius: 999px;
            padding: 8px 16px;
            font-size: .85rem;
            font-weight: 600;
        }
        .boton {
            display: inline-block;
            border-radius: 10px;
            padding: 14px 28px;
            font-weight: 700;
            text-decoration: none;
        }
        .boton-claro { background: #fff; color: var(--rojo); margin-top: 28px; }
        .boton-rojo { background: var(--rojo); color: #fff; }
        .boton-borde { background: #fff; color: var(--rojo); border: 2px solid var(--rojo); }
        .seccion-suave { background: var(--fondo-suave); }
        .intro-seccion { margin-top: 12px; max-width: 640px; color: var(--tinta-suave); }
        .tarjetas { display: grid; gap: 16px; margin-top: 28px; }
        @media (min-width: 768px) { .tarjetas-3 { grid-template-columns: repeat(3, 1fr); } }
        .tarjeta {
            background: var(--fondo);
            border: 1px solid var(--borde);
            border-radius: 14px;
            padding: 24px 20px;
        }
        .tarjeta h3 { font-size: 1.05rem; margin-bottom: 8px; }
        .tarjeta p { font-size: .95rem; color: var(--tinta-suave); }
        .paso-numero {
            display: inline-flex; align-items: center; justify-content: center;
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--rojo); color: #fff; font-weight: 700; margin-bottom: 14px;
        }
        .diagrama { display: flex; flex-direction: column; align-items: center; gap: 8px; margin-top: 32px; }
        .diagrama .nodo {
            background: var(--fondo);
            border: 2px solid var(--rojo);
            border-radius: 12px;
            padding: 14px 22px;
            font-weight: 700;
            font-size: .95rem;
            text-align: center;
            min-width: 210px;
        }
        .diagrama .nodo-acento { background: var(--rojo); color: #fff; }
        .diagrama .flecha { width: 26px; height: 26px; fill: var(--rojo); transform: rotate(90deg); }
        @media (min-width: 768px) {
            .diagrama { flex-direction: row; justify-content: center; gap: 14px; }
            .diagrama .flecha { transform: none; }
            .diagrama .nodo { min-width: 0; }
        }
        .tabla-campos { width: 100%; border-collapse: collapse; margin-top: 28px; background: var(--fondo); }
        .tabla-campos th, .tabla-campos td { border: 1px solid var(--borde); padding: 12px 16px; text-align: left; font-size: .95rem; }
        .tabla-campos th { background: var(--rojo); color: #fff; }
        .tabla-campos tr:nth-child(even) td { background: var(--fondo-suave); }
        .nota { margin-top: 18px; font-size: .9rem; color: var(--tinta-suave); }
        @media (min-width: 768px) { .tarjetas-4 { grid-template-columns: repeat(2, 1fr); } }
        .contacto { text-align: center; }
        .contacto .acciones { display: flex; flex-direction: column; gap: 12px; align-items: center; margin-top: 28px; }
        @media (min-width: 768px) { .contacto .acciones { flex-direction: row; justify-content: center; } }
        .contacto .telefono { margin-top: 18px; color: var(--tinta-suave); font-size: .95rem; }
    </style>
@endpush

@section('content')
    <section class="hero">
        <div class="container">
            <h1>{{ translate('conectate_hero_titulo') }}</h1>
            <p class="subtitulo">{{ translate('conectate_hero_subtitulo') }}</p>
            <div class="chips">
                <span class="chip">{{ translate('conectate_chip_comercios') }}</span>
                <span class="chip">{{ translate('conectate_chip_estados') }}</span>
                <span class="chip">{{ translate('conectate_chip_pedidos') }}</span>
            </div>
            <a href="#contacto" class="boton boton-claro">{{ translate('conectate_hero_cta') }}</a>
        </div>
    </section>

    <section>
        <div class="container">
            <h2>{{ translate('conectate_como_funciona_titulo') }}</h2>
            <div class="tarjetas tarjetas-3">
                <div class="tarjeta">
                    <span class="paso-numero">1</span>
                    <h3>{{ translate('conectate_paso_1_titulo') }}</h3>
                    <p>{{ translate('conectate_paso_1_texto') }}</p>
                </div>
                <div class="tarjeta">
                    <span class="paso-numero">2</span>
                    <h3>{{ translate('conectate_paso_2_titulo') }}</h3>
                    <p>{{ translate('conectate_paso_2_texto') }}</p>
                </div>
                <div class="tarjeta">
                    <span class="paso-numero">3</span>
                    <h3>{{ translate('conectate_paso_3_titulo') }}</h3>
                    <p>{{ translate('conectate_paso_3_texto') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="seccion-suave">
        <div class="container">
            <h2>{{ translate('conectate_api_titulo') }}</h2>
            <p class="intro-seccion">{{ translate('conectate_api_texto') }}</p>
            <div class="diagrama">
                <div class="nodo">{{ translate('conectate_diagrama_app') }}</div>
                <svg class="flecha" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                <div class="nodo nodo-acento">{{ translate('conectate_diagrama_api') }}</div>
                <svg class="flecha" viewBox="0 0 24 24" aria-hidden="true"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
                <div class="nodo">{{ translate('conectate_diagrama_marca') }}</div>
            </div>
        </div>
    </section>

    <section>
        <div class="container">
            <h2>{{ translate('conectate_campos_titulo') }}</h2>
            <p class="intro-seccion">{{ translate('conectate_campos_intro') }}</p>
            <table class="tabla-campos">
                <thead>
                <tr>
                    <th>{{ translate('conectate_campos_col_rt') }}</th>
                    <th>{{ translate('conectate_campos_col_marca') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td>{{ translate('conectate_campo_producto') }}</td>
                    <td>{{ translate('conectate_campo_producto_eq') }}</td>
                </tr>
                <tr>
                    <td>{{ translate('conectate_campo_presentacion') }}</td>
                    <td>{{ translate('conectate_campo_presentacion_eq') }}</td>
                </tr>
                <tr>
                    <td>{{ translate('conectate_campo_precio') }}</td>
                    <td>{{ translate('conectate_campo_precio_eq') }}</td>
                </tr>
                <tr>
                    <td>{{ translate('conectate_campo_promocion') }}</td>
                    <td>{{ translate('conectate_campo_promocion_eq') }}</td>
                </tr>
                <tr>
                    <td>{{ translate('conectate_campo_fotografia') }}</td>
                    <td>{{ translate('conectate_campo_fotografia_eq') }}</td>
                </tr>
                <tr>
                    <td>{{ translate('conectate_campo_inventario') }}</td>
                    <td>{{ translate('conectate_campo_inventario_eq') }}</td>
                </tr>
                </tbody>
            </table>
            <p class="nota">{{ translate('conectate_campos_nota') }}</p>
        </div>
    </section>

    <section class="seccion-suave">
        <div class="container">
            <h2>{{ translate('conectate_beneficios_titulo') }}</h2>
            <div class="tarjetas tarjetas-4">
                <div class="tarjeta">
                    <h3>{{ translate('conectate_beneficio_1_titulo') }}</h3>
                    <p>{{ translate('conectate_beneficio_1_texto') }}</p>
                </div>
                <div class="tarjeta">
                    <h3>{{ translate('conectate_beneficio_2_titulo') }}</h3>
                    <p>{{ translate('conectate_beneficio_2_texto') }}</p>
                </div>
                <div class="tarjeta">
                    <h3>{{ translate('conectate_beneficio_3_titulo') }}</h3>
                    <p>{{ translate('conectate_beneficio_3_texto') }}</p>
                </div>
                <div class="tarjeta">
                    <h3>{{ translate('conectate_beneficio_4_titulo') }}</h3>
                    <p>{{ translate('conectate_beneficio_4_texto') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="contacto" id="contacto">
        <div class="container">
            <h2>{{ translate('conectate_contacto_titulo') }}</h2>
            <p class="intro-seccion" style="margin-left:auto;margin-right:auto;">{{ translate('conectate_contacto_texto') }}</p>
            <div class="acciones">
                @if($whatsappNumber !== '')
                    <a class="boton boton-rojo"
                       href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode(translate('conectate_whatsapp_prefill')) }}">
                        {{ translate('conectate_contacto_whatsapp') }}
                    </a>
                @endif
                @if($companyEmail !== '')
                    <a class="boton boton-borde" href="mailto:{{ $companyEmail }}">{{ translate('conectate_contacto_correo') }}</a>
                @endif
            </div>
            @if($companyPhone !== '')
                <p class="telefono">{{ translate('conectate_contacto_telefono') }}: {{ $companyPhone }}</p>
            @endif
        </div>
    </section>
@endsection

@section('footer_links')
    <p><a href="{{ route('politica-de-privacidad') }}">{{ translate('conectate_footer_privacidad') }}</a></p>
@endsection
