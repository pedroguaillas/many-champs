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

</script>

<template>
    <Head title="Bienvenido" />

    <div class="min-h-screen bg-gradient-to-b from-blue-600 to-cyan-600">
        <header class="p-4">
            <h1 class="text-xl text-white font-semibold tracking-wider">
                <i class="fa fa-medal"></i>
                5IV <span class="text-cyan-400">SPORT</span>
            </h1>
        </header>
        <div class="p-4">
            <div class="flex justify-center my-4">
                <div class="text-8xl text-slate-100 text-center w-24 h-24 rounded-full">
                    <i class="fa fa-futbol-ball"></i>
                </div>
            </div>
            <p class="text-4xl text-slate-100 text-center tracking-wide uppercase font-semibold">Utiliza la Aplicación <span
                    class="text-cyan-400 font-bold">5IV SPORT</span> y controla todo tu
                campeonato</p>
        </div>
        <div class="flex justify-center">
            <Link :href="route('login')"
                class="bg-white px-2 py-1 w-[14em] text-center text-cyan-600 rounded font-semibold hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
            <i class="fa fa-medal mr-2"></i>
            Ingresar
            </Link>
        </div>
        <div class="p-4">
            <p class="text-slate-100 text-center tracking-wide">Cosas que vas a administrar en la
                aplicación: categorías, equipos, jugadores, juegos, goles, tarjetas, pago de inscripciones y puedes ver el
                resultado de los partidos, la tabla de posiciones, goleadores y muchas cosas más.</p>
        </div>
    </div>
</template>

<style>
.bg-dots-darker {
    background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E");
}

@media (prefers-color-scheme: dark) {
    .dark\:bg-dots-lighter {
        background-image: url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(255,255,255,0.07)'/%3E%3C/svg%3E");
    }
}
</style>
