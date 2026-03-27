<script setup>

// Imports
import { Head, Link } from '@inertiajs/vue3';

// Props
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
})

const features = [
    { icon: 'fa-layer-group',  label: 'Categorías',  desc: 'Torneos y ligas' },
    { icon: 'fa-shield',       label: 'Equipos',      desc: 'Clubes y plantillas' },
    { icon: 'fa-users',        label: 'Jugadores',    desc: 'Dorsales y registros' },
    { icon: 'fa-futbol',       label: 'Partidos',     desc: 'Seguimiento en vivo' },
    { icon: 'fa-bullseye',     label: 'Goles',        desc: 'Anotaciones y asist.' },
    { icon: 'fa-square',       label: 'Tarjetas',     desc: 'Amarillas y rojas' },
    { icon: 'fa-credit-card',  label: 'Inscripciones',desc: 'Pagos por equipo' },
    { icon: 'fa-list-ol',      label: 'Posiciones',   desc: 'Tabla actualizada' },
    { icon: 'fa-fire',         label: 'Goleadores',   desc: 'Mejores artilleros' },
    { icon: 'fa-ban',          label: 'Sanciones',    desc: 'Control disciplinario' },
]

const highlights = [
    {
        icon: 'fa-futbol',
        color: 'emerald',
        title: 'Partidos en Tiempo Real',
        desc: 'Registra goles, tarjetas y eventos minuto a minuto. El historial completo de cada encuentro queda documentado automáticamente.',
    },
    {
        icon: 'fa-list-ol',
        color: 'amber',
        title: 'Estadísticas Automáticas',
        desc: 'Tabla de posiciones, goleadores y sanciones se actualizan solos al finalizar cada partido. Sin trabajo manual.',
    },
    {
        icon: 'fa-trophy',
        color: 'sky',
        title: 'Gestión Completa',
        desc: 'Desde las inscripciones hasta la final: categorías, grupos, equipos, jugadores y pagos en una sola plataforma.',
    },
]

// JSON-LD structured data
const jsonLd = {
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    "name": "5IV SPORT",
    "applicationCategory": "SportsApplication",
    "operatingSystem": "Web",
    "description": "Plataforma de gestión de torneos de fútbol y futsal. Administra categorías, equipos, jugadores, partidos en vivo, estadísticas y tabla de posiciones.",
    "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "USD"
    },
    "featureList": [
        "Gestión de categorías y torneos",
        "Administración de equipos y jugadores",
        "Seguimiento de partidos en tiempo real",
        "Tabla de posiciones automática",
        "Tabla de goleadores",
        "Control de tarjetas y sanciones",
        "Gestión de inscripciones y pagos"
    ]
}

</script>

<template>
    <Head>
        <title>5IV SPORT — Gestiona tu Campeonato de Fútbol y Futsal</title>
        <meta name="description" content="Plataforma de gestión de torneos de fútbol y futsal. Administra categorías, equipos, jugadores, partidos en vivo, goles, tarjetas, tabla de posiciones y goleadores en un solo lugar." />
        <meta name="keywords" content="gestión torneos fútbol, futsal, campeonato, tabla de posiciones, goleadores, partidos en vivo, administrar torneo, plataforma deportiva, organizar campeonato, tarjetas amarillas rojas, inscripciones equipos" />

        <!-- Open Graph -->
        <meta property="og:title" content="5IV SPORT — Gestiona tu Campeonato de Fútbol y Futsal" />
        <meta property="og:description" content="Plataforma completa para gestionar torneos de fútbol y futsal: equipos, jugadores, partidos en vivo, estadísticas, posiciones y goleadores." />
        <meta property="og:type" content="website" />
        <meta property="og:locale" content="es_ES" />
        <meta property="og:site_name" content="5IV SPORT" />

        <!-- Twitter Card -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" content="5IV SPORT — Gestiona tu Campeonato de Fútbol y Futsal" />
        <meta name="twitter:description" content="Plataforma completa para gestionar torneos de fútbol y futsal: equipos, jugadores, partidos en vivo, estadísticas y más." />
    </Head>

    <!-- JSON-LD Structured Data -->
    <component :is="'script'" type="application/ld+json" v-text="JSON.stringify(jsonLd)"></component>

    <div class="min-h-screen bg-slate-950 text-white overflow-x-hidden">

        <!-- ─── HEADER / NAV ──────────────────────────────────────── -->
        <header class="relative z-20 border-b border-white/5">
            <nav class="flex items-center justify-between px-6 py-5" aria-label="Navegación principal">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-400 rounded-lg flex items-center justify-center shadow-lg shadow-amber-500/30" aria-hidden="true">
                        <i class="fa fa-medal text-slate-950 text-sm"></i>
                    </div>
                    <span class="text-lg font-bold tracking-wider">
                        5IV <span class="text-amber-400">SPORT</span>
                    </span>
                </div>
                <Link :href="route('login')"
                    class="text-sm font-medium text-slate-400 hover:text-white transition-colors duration-150"
                    aria-label="Iniciar sesión en la plataforma">
                    Ingresar <span aria-hidden="true">&rarr;</span>
                </Link>
            </nav>
        </header>

        <main>
            <!-- ─── HERO ────────────────────────────────────────────── -->
            <section class="relative flex items-center justify-center min-h-[92vh] overflow-hidden" aria-labelledby="hero-heading">

                <!-- Pitch grid texture -->
                <div class="pitch-grid absolute inset-0 opacity-[0.04]" aria-hidden="true"></div>

                <!-- Ambient glow orbs -->
                <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[700px] h-[500px] bg-emerald-600/10 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>
                <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-amber-500/6 rounded-full blur-3xl pointer-events-none" aria-hidden="true"></div>

                <!-- Content -->
                <div class="relative z-10 text-center px-6 max-w-4xl mx-auto py-20">

                    <!-- Live badge -->
                    <div class="inline-flex items-center gap-2 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-widest px-4 py-2 rounded-full mb-10" role="status">
                        <span class="live-dot w-2 h-2 bg-emerald-400 rounded-full" aria-hidden="true"></span>
                        Plataforma de Gestión Deportiva
                    </div>

                    <!-- Headline -->
                    <h1 id="hero-heading" class="text-5xl sm:text-6xl lg:text-7xl font-black leading-[0.95] tracking-tight mb-6">
                        <span class="block text-white">Controla</span>
                        <span class="block text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-300 py-1">
                            todo tu campeonato
                        </span>
                        <span class="block text-white">de fútbol</span>
                    </h1>

                    <!-- Sub -->
                    <p class="text-slate-400 text-lg sm:text-xl max-w-2xl mx-auto mb-12 leading-relaxed">
                        Categorías, equipos, jugadores, partidos, goles, tarjetas, inscripciones,
                        posiciones y goleadores — todo en un solo lugar.
                    </p>

                    <!-- CTA -->
                    <Link :href="route('login')"
                        class="group inline-flex items-center gap-3 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 text-slate-950 font-black px-9 py-4 rounded-xl transition-all duration-150 text-base shadow-2xl shadow-amber-500/25 hover:shadow-amber-400/40 hover:-translate-y-0.5"
                        aria-label="Ingresar a la plataforma de gestión deportiva">
                        <i class="fa fa-medal" aria-hidden="true"></i>
                        Ingresar a la Plataforma
                        <i class="fa fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-150" aria-hidden="true"></i>
                    </Link>

                </div>

                <!-- Scroll hint -->
                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 text-slate-700" aria-hidden="true">
                    <i class="fa fa-chevron-down text-xs bounce-slow"></i>
                </div>
            </section>

            <!-- ─── FEATURES GRID ───────────────────────────────────── -->
            <section class="py-20 px-6 border-t border-white/5" aria-labelledby="features-heading">
                <div class="max-w-5xl mx-auto">

                    <div class="text-center mb-12">
                        <h2 id="features-heading" class="text-2xl sm:text-3xl font-black text-white mb-3">
                            Todo lo que necesitas para gestionar tu torneo
                        </h2>
                        <p class="text-slate-500 text-sm">Diez módulos integrados, una sola plataforma</p>
                    </div>

                    <ul class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5" role="list">
                        <li
                            v-for="f in features" :key="f.label"
                            class="group bg-slate-900 hover:bg-slate-800/80 border border-white/[0.06] hover:border-amber-500/25 rounded-xl p-4 flex flex-col items-center gap-3 text-center transition-all duration-200 cursor-default"
                        >
                            <div class="w-10 h-10 bg-slate-800 group-hover:bg-amber-500/10 border border-white/5 group-hover:border-amber-500/20 rounded-lg flex items-center justify-center transition-all duration-200 flex-shrink-0" aria-hidden="true">
                                <i :class="['fa text-sm text-slate-400 group-hover:text-amber-400 transition-colors duration-200', f.icon]"></i>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm leading-snug">{{ f.label }}</p>
                                <p class="text-slate-600 text-xs mt-0.5 leading-snug">{{ f.desc }}</p>
                            </div>
                        </li>
                    </ul>

                </div>
            </section>

            <!-- ─── STATS BAR ───────────────────────────────────────── -->
            <section class="py-12 px-6 bg-slate-900/40 border-y border-white/5" aria-label="Estadísticas de la plataforma">
                <div class="max-w-3xl mx-auto grid grid-cols-2 sm:grid-cols-4 gap-8 text-center">
                    <div>
                        <p class="text-3xl font-black text-amber-400 tabular-nums">10+</p>
                        <p class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Módulos de gestión</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-emerald-400">360°</p>
                        <p class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Control del torneo</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-amber-400">Live</p>
                        <p class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Seguimiento de partidos</p>
                    </div>
                    <div>
                        <p class="text-3xl font-black text-emerald-400">Auto</p>
                        <p class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Estadísticas en tiempo real</p>
                    </div>
                </div>
            </section>

            <!-- ─── HIGHLIGHTS ──────────────────────────────────────── -->
            <section class="py-20 px-6" aria-labelledby="highlights-heading">
                <h2 id="highlights-heading" class="sr-only">Características destacadas de 5IV SPORT</h2>
                <div class="max-w-5xl mx-auto">
                    <div class="grid sm:grid-cols-3 gap-5">
                        <article
                            v-for="h in highlights" :key="h.title"
                            class="bg-slate-900/60 border border-white/[0.06] rounded-2xl p-7"
                        >
                            <div
                                class="w-12 h-12 rounded-xl flex items-center justify-center mb-5 text-lg"
                                :class="{
                                    'bg-emerald-500/10 text-emerald-400 border border-emerald-500/15': h.color === 'emerald',
                                    'bg-amber-500/10  text-amber-400  border border-amber-500/15':  h.color === 'amber',
                                    'bg-sky-500/10    text-sky-400    border border-sky-500/15':    h.color === 'sky',
                                }"
                                aria-hidden="true"
                            >
                                <i :class="['fa', h.icon]"></i>
                            </div>
                            <h3 class="text-white font-bold text-lg mb-2 leading-snug">{{ h.title }}</h3>
                            <p class="text-slate-500 text-sm leading-relaxed">{{ h.desc }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <!-- ─── FINAL CTA ───────────────────────────────────────── -->
            <section class="py-24 px-6 text-center relative overflow-hidden border-t border-white/5" aria-labelledby="cta-heading">
                <div class="absolute inset-0 bg-gradient-to-t from-emerald-950/20 to-transparent pointer-events-none" aria-hidden="true"></div>
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-transparent to-slate-950/80 pointer-events-none" aria-hidden="true"></div>

                <div class="relative z-10 max-w-xl mx-auto">
                    <div class="w-16 h-16 bg-amber-400/10 border border-amber-400/20 rounded-2xl flex items-center justify-center mx-auto mb-7 shadow-inner" aria-hidden="true">
                        <i class="fa fa-trophy text-amber-400 text-xl"></i>
                    </div>
                    <h2 id="cta-heading" class="text-3xl sm:text-4xl font-black text-white mb-4 leading-tight">
                        Empieza a gestionar<br>tu campeonato hoy
                    </h2>
                    <p class="text-slate-500 mb-9 text-base leading-relaxed">
                        Ingresa a tu plataforma y lleva el control total de tu torneo de fútbol.
                    </p>
                    <Link :href="route('login')"
                        class="group inline-flex items-center gap-3 bg-amber-400 hover:bg-amber-300 active:bg-amber-500 text-slate-950 font-black px-10 py-4 rounded-xl transition-all duration-150 text-base shadow-2xl shadow-amber-500/25 hover:shadow-amber-400/40 hover:-translate-y-0.5"
                        aria-label="Ingresar a 5IV SPORT para gestionar tu campeonato">
                        <i class="fa fa-medal" aria-hidden="true"></i>
                        Ingresar a 5IV SPORT
                        <i class="fa fa-arrow-right text-sm group-hover:translate-x-1 transition-transform duration-150" aria-hidden="true"></i>
                    </Link>
                </div>
            </section>
        </main>

        <!-- ─── FOOTER ──────────────────────────────────────────── -->
        <footer class="px-6 py-8 border-t border-white/5 text-center" role="contentinfo">
            <p class="text-slate-700 text-sm">
                <i class="fa fa-medal text-amber-600/60 mr-2" aria-hidden="true"></i>
                5IV <span class="text-amber-600/60">SPORT</span>
                <span class="mx-2 text-slate-800" aria-hidden="true">&middot;</span>
                Plataforma de Gestión Deportiva
            </p>
        </footer>

    </div>
</template>

<style scoped>
/* Pitch grid texture — subtle stadium floor lines */
.pitch-grid {
    background-image:
        repeating-linear-gradient(
            0deg,
            transparent,
            transparent 79px,
            rgba(16, 185, 129, 1) 79px,
            rgba(16, 185, 129, 1) 80px
        ),
        repeating-linear-gradient(
            90deg,
            transparent,
            transparent 79px,
            rgba(16, 185, 129, 1) 79px,
            rgba(16, 185, 129, 1) 80px
        );
}

/* Live dot pulse */
.live-dot {
    animation: pulse-glow 2s ease-in-out infinite;
}

@keyframes pulse-glow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50%       { opacity: 0.4; transform: scale(0.8); }
}

/* Scroll chevron */
.bounce-slow {
    animation: bounce-slow 2.5s ease-in-out infinite;
}

@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(6px); }
}

/* Screen reader only — for hidden headings */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
}
</style>
