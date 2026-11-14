<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <Link :href="'/'" aria-label="Many Champs, ir al inicio"
        class="flex items-center gap-3 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50 focus-visible:ring-offset-4 focus-visible:ring-offset-slate-950">
        <span class="w-11 h-11 rounded-lg bg-emerald-500 flex items-center justify-center text-sm font-bold tracking-tight text-white" aria-hidden="true">5IV</span>
        <span class="leading-tight text-left">
            <span class="block text-base font-semibold tracking-tight text-white">Many Champs</span>
            <span class="block text-[10px] font-semibold tracking-wider text-slate-500">SPORT MANAGER</span>
        </span>
    </Link>
</template>
