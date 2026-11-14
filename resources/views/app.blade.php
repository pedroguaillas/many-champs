<!DOCTYPE html>
{{-- Sin sesión (inicio y acceso) las páginas son siempre oscuras; con sesión el tema lo pone el script de abajo --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ! auth()->check()])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
        <meta name="robots" content="index, follow">
        <meta name="author" content="5IV SPORT">
        <link rel="canonical" href="{{ url()->current() }}" />

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Default Meta Tags (overridden by Inertia Head per page) -->
        <meta name="description" content="5IV SPORT — Plataforma de gestión de torneos de fútbol y futsal. Administra categorías, equipos, jugadores, partidos en vivo, estadísticas, tabla de posiciones y goleadores.">
        <meta name="keywords" content="gestión torneos fútbol, futsal, campeonato, tabla de posiciones, goleadores, partidos en vivo, administrar torneo, plataforma deportiva">

        <!-- Open Graph -->
        <meta property="og:type" content="website">
        <meta property="og:locale" content="es_ES">
        <meta property="og:site_name" content="5IV SPORT">

        @if ($og = $page['props']['og'] ?? null)
        <!-- Vista previa al compartir el enlace (WhatsApp, etc. no ejecutan JavaScript) -->
        <meta property="og:title" content="{{ $og['title'] }}">
        <meta property="og:description" content="{{ $og['description'] }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:image" content="{{ url('/icons/icon-512.png') }}">
        <meta name="twitter:title" content="{{ $og['title'] }}">
        <meta name="twitter:description" content="{{ $og['description'] }}">
        @endif

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">

        <!-- Theme Color: la app cambia tema por clase; Header.vue lo sincroniza al cambiar -->
        <meta name="theme-color" content="#020617">

        <!-- PWA -->
        <link rel="manifest" href="/manifest.webmanifest">
        <!-- Favicon: balón con fondo transparente (SVG nítido en navegadores modernos, ICO de respaldo) -->
        <link rel="icon" href="/favicon.ico" sizes="16x16 32x32 48x48">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Many Champs">
        <!-- App instalada en iOS: oscuro = contenido bajo la barra de estado (el header la pinta con su safe-area) -->
        <!-- Inicio y acceso son oscuros: barra negra con texto claro -->
        <meta name="apple-mobile-web-app-status-bar-style" content="{{ auth()->check() ? 'black-translucent' : 'black' }}">

        @auth
        <!-- Tema antes del primer pintado: evita el destello claro al abrir la app -->
        <script>
            (function () {
                var dark = true;
                try {
                    var t = localStorage.getItem('theme') || 'dark';
                    dark = t === 'dark' || (t === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
                } catch (e) {}
                document.documentElement.classList.toggle('dark', dark);
                document.querySelector('meta[name="theme-color"]').setAttribute('content', dark ? '#020617' : '#ffffff');
                // En tema claro el texto blanco de black-translucent no se leería
                document.querySelector('meta[name="apple-mobile-web-app-status-bar-style"]').setAttribute('content', dark ? 'black-translucent' : 'default');
            })();
        </script>
        @endauth

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=roboto:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
