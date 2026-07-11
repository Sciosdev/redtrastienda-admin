<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('meta_description')">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title')">
    <meta property="og:description" content="@yield('meta_description')">
    <meta property="og:url" content="{{ url()->current() }}">
    @if($companyLogoPath !== '')
        <meta property="og:image" content="{{ $companyLogoPath }}">
    @endif
    <meta name="theme-color" content="#A1262B">
    @if($companyFavIconPath !== '')
        <link rel="icon" href="{{ $companyFavIconPath }}">
    @endif
    <style>
        :root {
            --rojo: #A1262B;
            --rojo-oscuro: #7D1D21;
            --tinta: #2B2B2B;
            --tinta-suave: #5C5C5C;
            --fondo: #FFFFFF;
            --fondo-suave: #F7F4F2;
            --borde: #E6DEDC;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: var(--tinta);
            background: var(--fondo);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        img { max-width: 100%; }
        a { color: var(--rojo); }
        .container { max-width: 960px; margin: 0 auto; padding: 0 20px; }
        .site-header { border-bottom: 1px solid var(--borde); background: var(--fondo); }
        .header-inner { display: flex; align-items: center; gap: 12px; padding-top: 14px; padding-bottom: 14px; }
        .header-inner .logo { height: 40px; width: auto; }
        .header-inner .brand-name { font-weight: 700; font-size: 1rem; color: var(--tinta); }
        .site-footer { border-top: 1px solid var(--borde); background: var(--fondo-suave); margin-top: 48px; }
        .site-footer .container { padding-top: 20px; padding-bottom: 24px; font-size: .85rem; color: var(--tinta-suave); }
        .site-footer a { color: var(--tinta-suave); }
        h1 { font-size: 1.75rem; line-height: 1.25; }
        h2 { font-size: 1.35rem; line-height: 1.3; color: var(--tinta); }
        section { padding: 40px 0; }
        @media (min-width: 768px) {
            h1 { font-size: 2.4rem; }
            h2 { font-size: 1.6rem; }
            section { padding: 56px 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        @if($companyLogoPath !== '')
            <img src="{{ $companyLogoPath }}" alt="{{ $companyName }}" class="logo">
        @endif
        <span class="brand-name">{{ $companyName }}</span>
    </div>
</header>
<main>
    @yield('content')
</main>
<footer class="site-footer">
    <div class="container">
        <p>&copy; {{ date('Y') }} {{ $companyName }}</p>
        @yield('footer_links')
    </div>
</footer>
</body>
</html>
