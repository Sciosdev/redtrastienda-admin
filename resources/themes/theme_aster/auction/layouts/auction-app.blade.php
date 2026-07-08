<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ session()->get('direction') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="_token" content="{{ csrf_token() }}">
    <meta property="og:site_name" content="{{ $web_config['company_name'] }}" />

    <title>@yield('title')</title>

    @include("auction.layouts.partials._style-assets")
</head>
<body>

<script>
'use strict';
function setThemeMode() {
    if (localStorage.getItem('theme') === null) {
        document.body.setAttribute('theme', 'light');
    } else {
        document.body.setAttribute('theme', localStorage.getItem('theme'));
    }
}
setThemeMode();
</script>

    <div class="preloader d--none" id="loading">
        <img width="200" alt="" src="{{ getStorageImages(path: getWebConfig(name: 'loader_gif'), type: 'source', source: theme_asset('assets/img/loader.gif')) }}">
    </div>

    @include('theme-views.layouts.partials._header')
    @include('theme-views.layouts.partials._settings-sidebar')

    @yield('content')

    @include("auction.web-views.partials._image-modal")
    @include("auction.layouts.partials._recent-views")

    @if(!auth()->guard('customer')->check())
        @include('theme-views.layouts.partials.modal._register')
        @include('theme-views.layouts.partials.modal._login')
    @endif

    @include('theme-views.layouts.partials._feature')
    @include('theme-views.layouts.partials._footer')

    <a href="#" class="back-to-top">
        <i class="bi bi-arrow-up"></i>
    </a>

    <div class="app-bar d-xl-none" id="mobile_app_bar">
        @include('theme-views.layouts.partials._app-bar-auction')
    </div>

    @include("auction.layouts.partials._script-assets")
    @stack('script')
</body>
</html>
