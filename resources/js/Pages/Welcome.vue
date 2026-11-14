<script setup>

// Imports
import { Head, Link } from '@inertiajs/vue3';

// Props
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    // Campeonatos que su organizador compartió
    championships: { type: Array, default: () => [] },
})

const plural = (n, one, many) => `${n} ${Number(n) === 1 ? one : many}`

// Cancha ilustrativa del hero: posición en % (x de arco a arco, y de banda a banda)
const homeTokens = [
    { n: 1, x: 8, y: 50 },
    { n: 4, x: 24, y: 26 },
    { n: 6, x: 24, y: 74 },
    { n: 9, x: 41, y: 34, goals: 2, scored: true },
    { n: 10, x: 41, y: 68, card: true },
]
const awayTokens = [
    { n: 12, x: 92, y: 50 },
    { n: 3, x: 76, y: 26 },
    { n: 5, x: 76, y: 74 },
    { n: 7, x: 60, y: 38, goals: 1 },
    { n: 11, x: 60, y: 66 },
]

// Es una secuencia real: el orden en que se arma y se juega un campeonato
const steps = [
    {
        title: 'Crea tu campeonato y sus categorías',
        desc: 'Senior, femenino, sub 12. Cada categoría tiene sus grupos y su valor de inscripción.',
    },
    {
        title: 'Registra clubes y jugadores',
        desc: 'Cada club con su color de camiseta. Cada jugador con su cédula, dorsal y foto.',
    },
    {
        title: 'Programa los partidos',
        desc: 'Créalos uno por uno o genera toda la fase de grupos de una sola vez.',
    },
    {
        title: 'Lleva el partido en vivo',
        desc: 'Elige a los titulares, toca al jugador y marca gol, tarjeta o cambio. Si te equivocas, lo deshaces.',
    },
    {
        title: 'Las tablas se calculan solas',
        desc: 'Al finalizar el partido ya están las posiciones, los goleadores y las sanciones por cobrar.',
    },
]

const solves = [
    {
        icon: 'fa-futbol',
        title: 'El partido, en la cancha',
        desc: 'Ves a los dos equipos en pantalla, cada jugador en su puesto. Arrástralo para moverlo y toca para anotar lo que pasó.',
    },
    {
        icon: 'fa-ranking-star',
        title: 'Tablas sin hoja de cálculo',
        desc: 'Posiciones, goleadores y sanciones salen de los partidos que registras. Nadie suma puntos a mano.',
    },
    {
        icon: 'fa-coins',
        title: 'Cobros claros',
        desc: 'Inscripciones por club y tarjetas por jugador. Quien debe una sanción no entra al partido hasta pagarla.',
    },
    {
        icon: 'fa-mobile-screen',
        title: 'En tu celular',
        desc: 'Se abre en el navegador y se instala como una app, sin pasar por la tienda. También funciona en la computadora.',
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

const focusRing = 'focus:outline-none focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-400'

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

    <div class="min-h-[100svh] bg-slate-950 text-white overflow-x-hidden">

        <!-- ─── Cabecera ─────────────────────────────────────────── -->
        <header class="border-b border-white/[0.06] pt-[env(safe-area-inset-top)]">
            <nav class="max-w-6xl mx-auto flex items-center justify-between px-4 sm:px-6 h-16" aria-label="Navegación principal">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center text-[13px] font-bold tracking-tight text-white" aria-hidden="true">5IV</div>
                    <div class="leading-tight">
                        <p class="text-[15px] font-semibold tracking-tight">Many Champs</p>
                        <p class="text-[10px] font-semibold tracking-wider text-slate-500">SPORT MANAGER</p>
                    </div>
                </div>
                <Link :href="route('login')"
                    class="inline-flex items-center min-h-[44px] px-4 rounded-lg text-sm font-semibold text-slate-200 border border-white/[0.1] hover:border-emerald-500/50 hover:text-white active:scale-[0.97] transition duration-150"
                    :class="focusRing">
                    Ingresar
                </Link>
            </nav>
        </header>

        <main>
            <!-- ─── Hero: el partido en vivo ─────────────────────────── -->
            <section class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-14 lg:py-0 lg:min-h-[calc(100svh-4rem)] grid lg:grid-cols-[5fr_6fr] gap-10 lg:gap-14 items-center" aria-labelledby="hero-heading">

                <div>
                    <h1 id="hero-heading" class="text-[2.5rem] sm:text-5xl lg:text-6xl font-black leading-[1.02] tracking-tight">
                        Lleva tu campeonato desde la cancha
                    </h1>
                    <p class="mt-5 max-w-md text-base sm:text-lg leading-relaxed text-slate-300">
                        Anota goles, tarjetas y cambios tocando al jugador. Las posiciones, los goleadores y las sanciones se actualizan solos.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                        <Link :href="route('login')"
                            class="inline-flex items-center justify-center min-h-[48px] px-7 rounded-lg bg-emerald-500 text-base font-bold text-white hover:bg-emerald-400 active:scale-[0.97] transition duration-150"
                            :class="focusRing">
                            Ingresar
                        </Link>
                        <a href="#como-funciona"
                            class="inline-flex items-center min-h-[44px] text-sm font-medium text-slate-300 underline decoration-slate-600 underline-offset-4 hover:text-white hover:decoration-emerald-400 transition-colors duration-150"
                            :class="focusRing">
                            Ver cómo funciona
                        </a>
                    </div>
                    <p class="mt-6 text-[13px] text-slate-500">Para campeonatos de fútbol y futsal. Funciona en el celular.</p>
                </div>

                <!-- Ilustración del partido en vivo (decorativa) -->
                <div class="w-full max-w-xl mx-auto lg:max-w-none" aria-hidden="true">
                    <div class="rounded-2xl bg-slate-900 border border-white/[0.08] overflow-hidden select-none">

                        <!-- Marcador -->
                        <div class="px-4 py-3 border-b border-white/[0.06]">
                            <div class="flex justify-center mb-1.5">
                                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-red-500/10 text-[10px] font-semibold text-red-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>En vivo
                                </span>
                            </div>
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                <span class="truncate text-[13px] sm:text-sm font-semibold">Leones</span>
                                <span class="text-2xl sm:text-3xl font-bold tabular-nums">2 <span class="text-slate-600">–</span> 1</span>
                                <span class="truncate text-right text-[13px] sm:text-sm font-semibold">Tigres</span>
                            </div>
                        </div>

                        <!-- Cancha -->
                        <div class="pitch relative aspect-[16/10]">
                            <svg viewBox="0 0 160 100" preserveAspectRatio="none" class="absolute inset-0 w-full h-full">
                                <g fill="none" stroke="rgba(255,255,255,0.45)" stroke-width="0.6">
                                    <rect x="3" y="3" width="154" height="94" rx="1" />
                                    <line x1="80" y1="3" x2="80" y2="97" />
                                    <circle cx="80" cy="50" r="12" />
                                    <path d="M3 25 Q 26 25 26 50 Q 26 75 3 75" />
                                    <path d="M157 25 Q 134 25 134 50 Q 134 75 157 75" />
                                </g>
                                <circle cx="80" cy="50" r="0.9" fill="rgba(255,255,255,0.5)" />
                            </svg>

                            <template v-for="(team, side) in [homeTokens, awayTokens]" :key="side">
                                <div v-for="t in team" :key="`${side}-${t.n}`"
                                    class="absolute -translate-x-1/2 -translate-y-1/2 w-[9.5%] aspect-square"
                                    :style="{ left: `${t.x}%`, top: `${t.y}%` }">
                                    <span v-if="t.scored" class="goal-ring absolute inset-0 rounded-full border-2 border-white"></span>
                                    <span class="relative w-full h-full rounded-full flex items-center justify-center text-[11px] sm:text-sm font-bold tabular-nums shadow-md ring-2"
                                        :class="side === 0 ? 'bg-white text-emerald-800 ring-emerald-950/30' : 'bg-blue-500 text-white ring-blue-950/30'">
                                        {{ t.n }}
                                        <span v-if="t.card" class="absolute -top-1 -left-1 w-2 h-3 sm:w-2.5 sm:h-3.5 rounded-[2px] bg-amber-400 shadow"></span>
                                        <span v-if="t.goals"
                                            class="absolute -top-1.5 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-slate-950 text-white text-[9px] font-bold flex items-center justify-center gap-0.5 ring-2 ring-white/90">
                                            <i class="fa-solid fa-futbol text-[7px]"></i>{{ t.goals }}
                                        </span>
                                    </span>
                                    <span v-if="t.scored"
                                        class="goal-label absolute left-1/2 bottom-full mb-2 whitespace-nowrap px-2 py-0.5 rounded-full bg-white text-slate-900 text-[11px] sm:text-xs font-extrabold shadow-lg">
                                        <i class="fa-solid fa-futbol mr-1"></i>¡GOL!
                                    </span>
                                </div>
                            </template>
                        </div>

                        <!-- Acciones del jugador tocado -->
                        <div class="px-3 sm:px-4 py-3 border-t border-white/[0.06] flex items-center gap-2 sm:gap-3">
                            <span class="shrink-0 w-9 h-9 rounded-full bg-white text-emerald-800 text-xs font-bold flex items-center justify-center tabular-nums">9</span>
                            <div class="flex-1 min-w-0 leading-tight">
                                <p class="truncate text-[13px] font-semibold">Kevin Paucar</p>
                                <p class="truncate text-[11px] text-slate-500">Leones, 2 goles</p>
                            </div>
                            <span class="shrink-0 inline-flex items-center gap-1.5 h-9 px-3 rounded-lg bg-emerald-500 text-xs font-bold">
                                <i class="fa-solid fa-futbol text-[10px]"></i>Gol
                            </span>
                            <span class="shrink-0 w-9 h-9 rounded-lg border border-white/[0.08] flex items-center justify-center">
                                <span class="w-3 h-4 rounded-[2px] bg-amber-400"></span>
                            </span>
                            <span class="shrink-0 w-9 h-9 rounded-lg border border-white/[0.08] flex items-center justify-center">
                                <span class="w-3 h-4 rounded-[2px] bg-red-500"></span>
                            </span>
                            <span class="shrink-0 hidden sm:inline-flex items-center gap-1.5 h-9 px-3 rounded-lg border border-white/[0.08] text-xs font-semibold text-slate-200">
                                <i class="fa-solid fa-arrow-right-arrow-left text-[10px]"></i>Cambio
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ─── Campeonatos compartidos ──────────────────────────── -->
            <section v-if="championships.length" class="border-t border-white/[0.06]" aria-labelledby="championships-heading">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
                    <h2 id="championships-heading" class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">Campeonatos</h2>
                    <p class="mt-3 max-w-lg text-base text-slate-400">Entra a uno para ver sus partidos, resultados, posiciones y goleadores. No necesitas cuenta.</p>

                    <ul class="mt-8 grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
                        <li v-for="item in championships" :key="item.slug">
                            <Link :href="route('public.championship', item.slug)"
                                class="group flex items-center gap-3 h-full min-h-[72px] rounded-xl bg-slate-900 border px-4 py-3.5 hover:border-emerald-500/40 active:scale-[0.99] transition duration-150"
                                :class="[focusRing, item.live ? 'border-red-500/30' : 'border-white/[0.08]']">
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-base font-semibold">{{ item.name }}</p>
                                    <p class="mt-0.5 text-[13px] text-slate-400">
                                        <span v-if="item.live" class="inline-flex items-center gap-1.5 font-semibold text-red-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500" aria-hidden="true"></span>{{ plural(item.live, 'partido en vivo', 'partidos en vivo') }}
                                        </span>
                                        <span v-else class="tabular-nums">{{ plural(item.categories, 'categoría', 'categorías') }}, {{ plural(item.games, 'partido', 'partidos') }}</span>
                                    </p>
                                </div>
                                <i class="fa-solid fa-chevron-right text-[11px] text-slate-600 group-hover:text-emerald-400 transition-colors duration-150" aria-hidden="true"></i>
                            </Link>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- ─── Cómo funciona ────────────────────────────────────── -->
            <section id="como-funciona" class="scroll-mt-6 border-t border-white/[0.06] bg-slate-900/40" aria-labelledby="steps-heading">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20 grid lg:grid-cols-[5fr_6fr] gap-8 lg:gap-14">
                    <div>
                        <h2 id="steps-heading" class="text-3xl sm:text-4xl font-black tracking-tight leading-tight">Cómo funciona</h2>
                        <p class="mt-3 max-w-sm text-base leading-relaxed text-slate-400">
                            Cinco pasos, en el orden en que se arma y se juega un campeonato.
                        </p>
                    </div>

                    <ol class="relative">
                        <li v-for="(step, i) in steps" :key="step.title" class="relative flex gap-4 sm:gap-5 pb-8 last:pb-0">
                            <!-- Línea que une los pasos -->
                            <span v-if="i < steps.length - 1" class="absolute left-5 top-10 bottom-0 w-px bg-white/[0.08]" aria-hidden="true"></span>
                            <span class="relative shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold tabular-nums"
                                :class="i === 3 ? 'bg-emerald-500 text-white' : 'bg-slate-800 text-emerald-400 border border-white/[0.08]'"
                                aria-hidden="true">
                                {{ i + 1 }}
                            </span>
                            <div class="pt-1.5">
                                <h3 class="text-base sm:text-lg font-semibold leading-snug">{{ step.title }}</h3>
                                <p class="mt-1 max-w-md text-sm leading-relaxed text-slate-400">{{ step.desc }}</p>
                            </div>
                        </li>
                    </ol>
                </div>
            </section>

            <!-- ─── Lo que resuelve ──────────────────────────────────── -->
            <section class="border-t border-white/[0.06]" aria-labelledby="solves-heading">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-20">
                    <h2 id="solves-heading" class="max-w-xl text-3xl sm:text-4xl font-black tracking-tight leading-tight">
                        Menos cuaderno, más fútbol
                    </h2>

                    <dl class="mt-10 grid sm:grid-cols-2 gap-x-14">
                        <div v-for="item in solves" :key="item.title" class="flex gap-4 py-6 border-t border-white/[0.08]">
                            <i class="fa-solid mt-1 w-5 text-center text-emerald-400" :class="item.icon" aria-hidden="true"></i>
                            <div>
                                <dt class="text-base font-semibold">{{ item.title }}</dt>
                                <dd class="mt-1 max-w-md text-sm leading-relaxed text-slate-400">{{ item.desc }}</dd>
                            </div>
                        </div>
                    </dl>
                </div>
            </section>

            <!-- ─── Cierre ───────────────────────────────────────────── -->
            <section class="border-t border-white/[0.06] bg-emerald-950/30" aria-labelledby="cta-heading">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 py-14 sm:py-16 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
                    <div>
                        <h2 id="cta-heading" class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">¿Ya tienes tu campeonato?</h2>
                        <p class="mt-2 text-base text-slate-400">Ingresa con tu cuenta y sigue donde lo dejaste.</p>
                    </div>
                    <Link :href="route('login')"
                        class="self-start sm:self-auto shrink-0 inline-flex items-center justify-center min-h-[48px] px-7 rounded-lg bg-emerald-500 text-base font-bold text-white hover:bg-emerald-400 active:scale-[0.97] transition duration-150"
                        :class="focusRing">
                        Ingresar
                    </Link>
                </div>
            </section>
        </main>

        <!-- ─── Pie ──────────────────────────────────────────────── -->
        <footer class="border-t border-white/[0.06] pb-[env(safe-area-inset-bottom)]" role="contentinfo">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 flex flex-wrap items-center justify-between gap-x-6 gap-y-1 text-[13px] text-slate-500">
                <p>Many Champs, por 5IV SPORT</p>
                <p>Gestión de campeonatos de fútbol y futsal</p>
            </div>
        </footer>

    </div>
</template>

<style scoped>
/* Césped: las mismas franjas de la cancha del partido en vivo */
.pitch {
    background: repeating-linear-gradient(90deg, #047857 0 10%, #065f46 10% 20%);
}

/* Único momento de animación: el gol, una vez al cargar */
.goal-label {
    translate: -50% 0;
    animation: goal-label 600ms cubic-bezier(0.23, 1, 0.32, 1) 700ms both;
}

@keyframes goal-label {
    from { transform: translateY(8px) scale(0.85); opacity: 0; }
    to { transform: translateY(0) scale(1); opacity: 1; }
}

.goal-ring {
    opacity: 0;
    animation: goal-ring 900ms cubic-bezier(0.23, 1, 0.32, 1) 700ms;
}

@keyframes goal-ring {
    from { transform: scale(0.8); opacity: 0.9; }
    to { transform: scale(2.4); opacity: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .goal-label { animation: none; }
    .goal-ring { display: none; }
}
</style>
