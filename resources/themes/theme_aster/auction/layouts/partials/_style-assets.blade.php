<link rel="shortcut icon" href="{{ $web_config['fav_icon']['path'] }}"/>

<link rel="stylesheet" href="{{ theme_asset('assets/css/fonts-init.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset('assets/css/bootstrap.min.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset('assets/css/bootstrap-icons.min.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset('assets/plugins/magnific-popup-1.1.0/magnific-popup.css') }}" />
<link rel="stylesheet" href="{{ theme_asset('assets/plugins/swiper/swiper-bundle.min.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset('assets/plugins/sweet_alert/sweetalert2.css') }}">
<link rel="stylesheet" href="{{ theme_asset('assets/css/toastr.css') }}"/>

<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-regular-rounded.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/webfonts/uicons-solid-rounded.css') }}">

<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/select2/select2.min.css') }}">
<link rel="stylesheet" href="{{ theme_asset('assets/css/style.css') }}"/>
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/css/intlTelInput.css') }}">
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/libs/google-recaptcha/google-recaptcha-init.css') }}">


<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/css/open-sans-font.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/plugin/quill-editor/quill-editor.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/plugin/daterangepicker/daterangepicker.css') }}"/>
<link rel="stylesheet" href="{{ dynamicAsset(path: 'public/assets/backend/admin/css/product-add-auto-fill.css') }}">
<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/scss/helpers.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/scss/auction.css') }}"/>
<link rel="stylesheet" href="{{ theme_asset(path: 'assets/auction/plugin/owl-carousel/owl.carousel.min.css') }}"/>

@stack('css_or_js')

<link rel="stylesheet" href="{{ theme_asset('assets/css/custom.css') }}"/>
<style>
    :root {
        --bs-primary: {{ $web_config['primary_color'] }};
        --bs-primary-rgb: {{ getHexToRGBColorCode($web_config['primary_color']) }};
        --primary-dark: {{ $web_config['primary_color'] }};
        --primary-light: {{ !empty($web_config['primary_color_light']) ? $web_config['primary_color_light'] : '#FFFFFF' }};
        --bs-secondary: {{ $web_config['secondary_color'] }};
        --bs-secondary-rgb: {{ getHexToRGBColorCode($web_config['secondary_color']) }};
    }

    .announcement-color {
        background-color: {{ $web_config['announcement']['color'] }};
        color: {{$web_config['announcement']['text_color']}};
    }
    .btn-outline-success {
        --bs-btn-hover-bg: {{ $web_config['primary_color'] }} !important;
        --bs-btn-active-bg: {{ $web_config['primary_color'] }} !important;
        --bs-btn-border-color: {{ $web_config['primary_color'] }} !important;
    }
    .btn-outline-success:active {
        background-color: var(--bg-color) !important;
        color: {{ $web_config['primary_color'] }} !important;
        --bs-btn-border-color: {{ $web_config['primary_color'] }} !important;
    }
</style>
