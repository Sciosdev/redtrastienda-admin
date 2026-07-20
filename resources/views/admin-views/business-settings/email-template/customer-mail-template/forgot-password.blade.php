<div>
    <div class="text-center">
        <img width="160" class="mb-4" id="view-mail-icon" src="{{ $template->image_full_url['path'] ?? dynamicAsset(path: 'public/assets/back-end/img/email-template/change-pass.png')}}" alt="">
        <h3 class="mb-3 view-mail-title text-capitalize">
            {{$title}}
        </h3>
    </div>
    <div class="view-mail-body">
        {!! $body !!}
    </div>
    @php
        // ANPEC: el código de 6 dígitos viaja dentro del token del enlace; los
        // afiliados leen el correo en el teléfono y capturan el código en la app,
        // así que se muestra grande en el cuerpo (en la vista previa del panel no
        // hay passwordResetURL y el bloque simplemente no se pinta).
        $anpecResetToken = null;
        if (isset($data['passwordResetURL'])) {
            parse_str(parse_url($data['passwordResetURL'], PHP_URL_QUERY) ?? '', $anpecResetQuery);
            $anpecResetToken = $anpecResetQuery['token'] ?? null;
        }
    @endphp
    @if($anpecResetToken)
        <div class="text-center" style="margin: 18px 0;">
            <p style="margin-bottom: 6px;">{{ translate('tu_codigo_para_la_app') }}:</p>
            <div style="display: inline-block; background: #F5E8E7; border: 1px solid #A1262B; border-radius: 8px; padding: 10px 22px; font-size: 28px; font-weight: 700; letter-spacing: 6px; color: #A1262B;">{{ $anpecResetToken }}</div>
        </div>
    @endif
    <div>
        <p>{{translate('click_here')}} <br> <a class="{{ isset($data['passwordResetURL']) ? '' : 'cursor-default'}}" href="{{$data['passwordResetURL'] ?? 'javascript:'}}">{{translate('change_password')}}</a>
    </div>
    <hr>
    @include('admin-views.business-settings.email-template.partials-design.footer')
</div>
