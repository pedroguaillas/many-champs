<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    menu: Boolean
});
</script>

<template>
    <aside class="w-[16em] h-[100vh] p-4 flex flex-col fixed z-40 bg-slate-800 ease-in-out duration-300"
        :class="{ 'left-0': menu, '-left-[16em]': !menu }">
        <header class="p-2 [&>a]:w-full">
            <Link class="rounded hover:bg-slate-500 p-2 text-xl text-white" :href="route('dashboard')"><strong>5IV</strong>
            Sport</Link>
        </header>
        <nav class="p-2">
            <ul class="[&>li>a]:text-white [&>li>a]:inline-block [&>li>a]:w-full [&>li>a]:p-2 [&>li>a>i]:stroke-white">
                <li>
                    <Link class="rounded hover:bg-slate-500" :href="route('seleccionar.categoria', 'clubs')">
                    <i class="fa fa-medal"></i> Clubes
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" :href="route('seleccionar.categoria', 'games')">
                    <i class="fa fa-futbol-ball"></i> Partidos
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" href='business'>
                    <i class="fa fa-calendar"></i> Calendario
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" href='/business'>
                    <i class="fa fa-align-center"></i> Tabla de posiciones
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" :href="route('dashboard')"
                        :class="route().current('dashboard') ? 'bg-slate-500' : null">
                    <i class="fa fa-bell"></i> Sanciones
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" href='/business'>
                    <i class="fa fa-money-bill"></i> Económico
                    </Link>
                </li>
                <li>
                    <Link class="rounded hover:bg-slate-500" :href="route('categories.index')"
                        :class="route().current('categories.index') ? 'bg-slate-500' : null">
                    <i class="fa fa-cog"></i> Categorias
                    </Link>
                </li>
            </ul>
        </nav>
    </aside>
</template>