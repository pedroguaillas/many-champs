<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
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

        <!-- Theme Color -->
        <meta name="theme-color" content="#020617">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
