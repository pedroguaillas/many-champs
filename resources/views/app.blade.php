<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image">

        <!-- Theme Color: la app cambia tema por clase; Header.vue lo sincroniza al cambiar -->
        <meta name="theme-color" content="#020617">

        <!-- PWA -->
        <link rel="manifest" href="/manifest.webmanifest">
        <link rel="icon" href="/favicon.ico" sizes="32x32">
        <link rel="icon" href="/icons/icon-192.png" type="image/png">
        <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Many Champs">
        <meta name="apple-mobile-web-app-status-bar-style" content="default">

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
